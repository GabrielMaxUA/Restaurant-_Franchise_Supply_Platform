/**
 * Utility functions for handling product quantity management and inventory checks
 * Updated to work with the corrected PHP API responses
 */

import { Alert } from 'react-native';

/**
 * Calculate the maximum quantity that can be added to cart
 * @param {Object} product - The product data object
 * @param {Object|null} selectedVariant - The selected variant (if any)
 * @param {Array} cartItems - Current cart items
 * @returns {number} Maximum quantity that can be added
 */
export const calculateMaxQuantity = (product, selectedVariant, cartItems) => {
  if (!product) return 0;
  
  // Get inventory count from the correct source
  let inventoryCount = 0;
  if (selectedVariant) {
    inventoryCount = selectedVariant.inventory_count || 0;
  } else {
    inventoryCount = product.inventory_count || 0;
  }
  
  // Find existing cart item
  const cartItem = cartItems.find(item => {
    if (selectedVariant) {
      return item.variant_id === selectedVariant.id;
    } else {
      return item.product_id === product.id && (!item.variant_id || item.variant_id === null);
    }
  });
  
  const currentCartQuantity = cartItem ? cartItem.quantity : 0;
  const maxCanAdd = Math.max(0, inventoryCount - currentCartQuantity);
  
  return maxCanAdd;
};

/**
 * Check if incrementing quantity is allowed
 * @param {Object} params - Parameters object
 * @param {Object} params.product - The product data
 * @param {Object|null} params.selectedVariant - Selected variant
 * @param {number} params.currentQuantity - Current quantity in input
 * @param {Array} params.cartItems - Current cart items
 * @param {Function} params.onSuccess - Success callback with new quantity
 * @param {Function} params.onError - Error callback with message and title
 */
export const incrementQuantityWithCheck = ({ product, selectedVariant, currentQuantity, cartItems, onSuccess, onError }) => {
  const maxQuantity = calculateMaxQuantity(product, selectedVariant, cartItems);
  
  if (currentQuantity < maxQuantity) {
    onSuccess(currentQuantity + 1);
  } else {
    const itemName = selectedVariant 
      ? `${product.name} (${selectedVariant.name})`
      : product.name;
    
    onError(
      `Cannot add more of ${itemName}. Maximum available: ${maxQuantity}`,
      'Inventory Limit'
    );
  }
};

/**
 * Centralized add to cart function with quantity management
 * @param {Object} params - Parameters object
 */
export const addToCartWithQuantityManagement = async ({
  product,
  selectedVariant,
  quantity,
  cartItems,
  userToken,
  addToCartAPI,
  onSuccess,
  onError
}) => {
  try {
    // Pre-validate quantity
    const maxQuantity = calculateMaxQuantity(product, selectedVariant, cartItems);
    
    if (quantity > maxQuantity) {
      const itemName = selectedVariant 
        ? `${product.name} (${selectedVariant.name})`
        : product.name;
      
      if (maxQuantity === 0) {
        onError(`${itemName} is out of stock or already at maximum quantity in your cart`, 'Out of Stock');
        return;
      } else {
        onError(
          `Only ${maxQuantity} more ${itemName} can be added to cart`, 
          'Inventory Limit'
        );
        return;
      }
    }
    
    // Make API call
    const response = await addToCartAPI(
      userToken, 
      product.id, 
      selectedVariant ? selectedVariant.id : null, 
      quantity,
      cartItems
    );
    
    if (response && response.success) {
      // Handle successful response
      const actualQuantityAdded = response.actual_quantity_added || quantity;
      const wasAdjusted = response.was_adjusted || false;
      const warnings = response.warnings || [];
      
      const productName = selectedVariant 
        ? `${product.name} (${selectedVariant.name})`
        : product.name;
      
      let successMessage = '';
      if (wasAdjusted) {
        successMessage = `Added ${actualQuantityAdded} ${productName} to cart (adjusted due to inventory limits)`;
        if (warnings.length > 0) {
          successMessage += `\n\n${warnings.join('\n')}`;
        }
      } else {
        successMessage = `Added ${actualQuantityAdded} ${productName} to cart`;
      }
      
      onSuccess({
        response,
        actualQuantityAdded,
        productName,
        successMessage,
        wasAdjusted
      });
    } else {
      // Handle API error response
      let errorMessage = response?.message || 'Failed to add item to cart';
      
      // Check if it's an inventory error
      if (response?.inventory_limited) {
        errorMessage = formatInventoryErrorMessage(response, product, selectedVariant, cartItems);
      }
      
      onError(errorMessage, 'Error');
    }
  } catch (error) {
    console.error('Error in addToCartWithQuantityManagement:', error);
    onError(error.message || 'Network error occurred', 'Error');
  }
};

/**
 * Format inventory error messages consistently
 * @param {Object} errorResponse - Error response from API
 * @param {Object} product - Product data
 * @param {Object|null} selectedVariant - Selected variant
 * @param {Array} cartItems - Current cart items
 * @returns {string} Formatted error message
 */
const formatInventoryErrorMessage = (errorResponse, product, selectedVariant, cartItems) => {
  const itemName = selectedVariant 
    ? `${product.name} (${selectedVariant.name})`
    : product.name;
  
  const maxAvailable = errorResponse.max_available || 0;
  const currentCartQuantity = errorResponse.current_cart_quantity || 0;
  const totalInventory = errorResponse.total_inventory;
  
  if (maxAvailable === 0) {
    if (currentCartQuantity > 0) {
      return `You already have all available stock (${currentCartQuantity}) of ${itemName} in your cart`;
    } else {
      return `${itemName} is out of stock`;
    }
  } else {
    if (totalInventory !== undefined) {
      return `Only ${totalInventory} units of ${itemName} available in stock. ` +
        (currentCartQuantity > 0 
          ? `Your cart already has ${currentCartQuantity}. Only ${maxAvailable} more can be added.`
          : `Only ${maxAvailable} can be added to cart.`);
    } else {
      return `Only ${maxAvailable} more ${itemName} can be added to cart`;
    }
  }
};

/**
 * Handle cart update response with proper error handling
 * @param {Object} response - API response
 * @param {string} itemName - Name of the item being updated
 * @param {number} requestedQuantity - Originally requested quantity
 * @returns {Object} Processed response with user-friendly messages
 */
export const handleCartUpdateResponse = (response, itemName, requestedQuantity) => {
  if (!response) {
    return {
      success: false,
      message: 'No response received from server',
      userFriendlyMessage: 'Failed to update cart. Please try again.'
    };
  }
  
  if (response.success) {
    let message = response.message || 'Cart updated successfully';
    
    // Check if quantity was adjusted
    if (response.was_adjusted) {
      const finalQuantity = response.final_quantity || 0;
      if (response.item_removed) {
        message = `${itemName} was removed from cart (out of stock)`;
      } else {
        message = `${itemName} quantity adjusted to ${finalQuantity} (maximum available)`;
      }
    }
    
    return {
      success: true,
      message,
      userFriendlyMessage: message,
      wasAdjusted: response.was_adjusted || false,
      finalQuantity: response.final_quantity,
      itemRemoved: response.item_removed || false
    };
  } else {
    // Handle error response
    let errorMessage = response.message || 'Failed to update cart';
    
    // Make API error messages more user-friendly
    if (errorMessage.includes('items field is required') || 
        errorMessage.includes('items.0.id field is required')) {
      errorMessage = 'There was an issue updating your cart. Please try again.';
    }
    
    return {
      success: false,
      message: errorMessage,
      userFriendlyMessage: errorMessage
    };
  }
};

/**
 * Format success message for add to cart operations
 * @param {number} addedQuantity - Quantity that was added
 * @param {string} productName - Name of the product
 * @param {boolean} wasAdjusted - Whether quantity was adjusted
 * @param {Array} warnings - Any warnings from the API
 * @returns {string} Formatted success message
 */
export const formatAddToCartSuccessMessage = (addedQuantity, productName, wasAdjusted = false, warnings = []) => {
  let message = `Added ${addedQuantity} ${productName} to cart`;
  
  if (wasAdjusted) {
    message += ' (quantity adjusted due to inventory limits)';
  }
  
  if (warnings && warnings.length > 0) {
    message += `\n\n${warnings.join('\n')}`;
  }
  
  return message;
};

/**
 * Get inventory status information for display
 * @param {Object} item - Product or variant object
 * @returns {Object} Status information with label, color, and icon
 */
export const getInventoryStatus = (item) => {
  const inventoryCount = item?.inventory_count || 0;
  
  if (inventoryCount <= 0) {
    return { 
      label: 'Out of Stock', 
      color: '#dc3545', 
      icon: 'times-circle',
      status: 'out_of_stock'
    };
  } else if (inventoryCount <= 10) {
    return { 
      label: `Low Stock (${inventoryCount} left)`, 
      color: '#ffc107', 
      icon: 'exclamation-circle',
      status: 'low_stock'
    };
  } else {
    return { 
      label: `In Stock (${inventoryCount} available)`, 
      color: '#28a745', 
      icon: 'check-circle',
      status: 'in_stock'
    };
  }
};

/**
 * Check if an item is currently in the cart
 * @param {Object} product - Product object
 * @param {Object|null} selectedVariant - Selected variant
 * @param {Array} cartItems - Current cart items
 * @returns {Object} Cart information
 */
export const getCartItemInfo = (product, selectedVariant, cartItems) => {
  if (!product || !cartItems) {
    return { inCart: false, quantity: 0, cartItem: null };
  }
  
  const cartItem = cartItems.find(item => {
    if (selectedVariant) {
      return item.variant_id === selectedVariant.id;
    } else {
      return item.product_id === product.id && (!item.variant_id || item.variant_id === null);
    }
  });
  
  if (cartItem) {
    const price = selectedVariant 
      ? (selectedVariant.price_adjustment || selectedVariant.price || 0)
      : (product.price || product.base_price || 0);
    
    return {
      inCart: true,
      quantity: cartItem.quantity,
      cartItem,
      total: price * cartItem.quantity
    };
  }
  
  return { inCart: false, quantity: 0, cartItem: null };
};