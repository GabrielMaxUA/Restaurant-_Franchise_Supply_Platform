import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Image,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  SafeAreaView,
  TextInput,
  Alert,
  Platform
} from 'react-native';
import FallbackIcon from '../components/icon/FallbackIcon';
import { getProductDetails, addToCart, getCart } from '../services/api';
import { cartEventEmitter } from '../components/FranchiseeLayout';
import AsyncStorage from '@react-native-async-storage/async-storage';

const ProductDetailScreen = ({ route, navigation }) => {
  const { productId } = route.params;
  const [loading, setLoading] = useState(true);
  const [product, setProduct] = useState(null);
  const [selectedVariant, setSelectedVariant] = useState(null);
  const [quantity, setQuantity] = useState(1);
  const [cartItems, setCartItems] = useState([]);
  const [error, setError] = useState('');
  const [addingToCart, setAddingToCart] = useState(false);
  
  // New state for variant swapping
  const [currentView, setCurrentView] = useState('main'); // 'main' or 'variant'
  const [currentVariantId, setCurrentVariantId] = useState(null);
  const [alternativeItems, setAlternativeItems] = useState([]); // Items to show in the variants section
  const [mainImage, setMainImage] = useState(null);

  // Fetch product details when screen loads
  useEffect(() => {
    loadProductDetails();
  }, [productId]);

  const loadProductDetails = async () => {
    setLoading(true);
    setError('');
    
    try {
      const token = await AsyncStorage.getItem('userToken');
      if (!token) {
        setError('Not authenticated');
        setLoading(false);
        return;
      }

      // Load product details
      const response = await getProductDetails(token, productId);
      console.log('Product details response:', response);
      
      if (response.success && response.product) {
        setProduct(response.product);
        setQuantity(1); // Reset quantity
        
        // Reset view state
        setCurrentView('main');
        setSelectedVariant(null);
        setCurrentVariantId(null);
        
        // Set main product image
        if (response.product.image_url) {
          setMainImage(response.product.image_url);
        }
        
        // Initialize alternative items to be all variants
        if (response.product.variants && response.product.variants.length > 0) {
          setAlternativeItems([...response.product.variants]);
        }
        
        // Get current items in cart
        const cartResponse = await getCart(token);
        
        if (cartResponse.success) {
          // Extract cart items from the response
          let cartItems = [];
          
          if (cartResponse.cart && Array.isArray(cartResponse.cart.items)) {
            cartItems = cartResponse.cart.items;
          } else if (cartResponse.cart_items && Array.isArray(cartResponse.cart_items)) {
            cartItems = cartResponse.cart_items;
          } else if (cartResponse.items && Array.isArray(cartResponse.items)) {
            cartItems = cartResponse.items;
          }
          
          // Set cart items state
          setCartItems(cartItems);
          
          // Check if the current product or any of its variants are already in the cart
          if (cartItems.length > 0) {
            console.log('Product in cart check - Product ID:', productId);
            console.log('Cart items:', cartItems);
          }
        } else {
          console.error('Failed to get cart:', cartResponse.message);
        }
      } else {
        setError(response.message || 'Failed to load product details');
      }
    } catch (error) {
      console.error('Error loading product details:', error);
      setError('Network error');
    } finally {
      setLoading(false);
    }
  };

  // Function to switch to variant view
  const switchToVariant = (variant) => {
    // If we're already viewing this variant, switch back to main product
    if (currentVariantId === variant.id) {
      switchToMainProduct();
      return;
    }
    
    // Set the selected variant
    setSelectedVariant(variant);
    
    // Update current view state
    setCurrentView('variant');
    setCurrentVariantId(variant.id);
    
    // Update main image to variant image
    if (variant.image_url) {
      setMainImage(variant.image_url);
    }
    
    // Reset quantity when switching variants
    setQuantity(1);
    
    // Update alternative items list to include main product and exclude the selected variant
    if (product && product.variants) {
      const mainProductItem = {
        id: product.id,
        name: product.name,
        description: product.description,
        price: product.price,
        inventory_count: product.inventory_count,
        image_url: product.image_url,
        isMainProduct: true
      };
      
      const alternativeItemsList = [mainProductItem];
      
      // Add all other variants except the selected one
      product.variants.forEach(v => {
        if (v.id !== variant.id) {
          alternativeItemsList.push(v);
        }
      });
      
      setAlternativeItems(alternativeItemsList);
    }
  };

  // Function to switch back to main product view
  const switchToMainProduct = () => {
    // Reset to main product view
    setCurrentView('main');
    setSelectedVariant(null);
    setCurrentVariantId(null);
    
    // Reset main image to product image
    if (product && product.image_url) {
      setMainImage(product.image_url);
    }
    
    // Reset quantity
    setQuantity(1);
    
    // Alternative items should be all variants
    if (product && product.variants) {
      setAlternativeItems([...product.variants]);
    }
  };

  const handleAddToCart = async () => {
    if (!product) return;
    
    try {
      setAddingToCart(true);
      
      const token = await AsyncStorage.getItem('userToken');
      if (!token) {
        Alert.alert('Error', 'Not authenticated');
        setAddingToCart(false);
        return;
      }
      
      // Determine if we're adding the main product or a variant
      const productToAdd = selectedVariant ? selectedVariant.id : product.id;
      const variantId = selectedVariant ? selectedVariant.id : null;
      
      console.log(`Adding to cart: Product=${product.id}, Variant=${variantId}, Quantity=${quantity}`);
      
      const response = await addToCart(token, product.id, variantId, quantity);
      
      if (response.success) {
        // Determine cart count from response
        let cartCount = 0;
        
        if (response.cart && typeof response.cart.items_count === 'number') {
          cartCount = response.cart.items_count;
        } else if (response.cart && Array.isArray(response.cart.items)) {
          cartCount = response.cart.items.length;
        } else if (response.cart_items && Array.isArray(response.cart_items)) {
          cartCount = response.cart_items.length;
        } else if (typeof response.items_count === 'number') {
          cartCount = response.items_count;
        }
        
        // Emit cart update event
        if (cartCount > 0) {
          cartEventEmitter.emit('cartUpdated', cartCount);
        } else {
          // If we can't determine cart count, refetch cart
          const cartResponse = await getCart(token);
          if (cartResponse.success && cartResponse.cart) {
            const updatedCount = cartResponse.cart.items_count || 
                              (Array.isArray(cartResponse.cart.items) ? cartResponse.cart.items.length : 0);
            cartEventEmitter.emit('cartUpdated', updatedCount);
          }
        }
        
        // Success message
        Alert.alert(
          'Added to Cart',
          selectedVariant 
            ? `${quantity} x ${product.name} - ${selectedVariant.name} added to cart` 
            : `${quantity} x ${product.name} added to cart`,
          [
            { 
              text: 'Continue Shopping', 
              onPress: () => navigation.goBack(), 
              style: 'cancel'
            }
          ]
        );
      } else {
        Alert.alert('Error', response.message || 'Failed to add item to cart');
      }
    } catch (error) {
      console.error('Error adding to cart:', error);
      Alert.alert('Error', 'Failed to add item to cart');
    } finally {
      setAddingToCart(false);
    }
  };

  const incrementQuantity = () => {
    // Check if increasing quantity would exceed inventory
    const maxQuantity = selectedVariant 
      ? selectedVariant.inventory_count 
      : (product ? product.inventory_count : 0);
      
    // Find item in cart if it exists
    const cartItem = cartItems.find(item => {
      if (selectedVariant) {
        return item.variant_id === selectedVariant.id;
      } else {
        return item.product_id === product.id && !item.variant_id;
      }
    });
    
    // Calculate how many more items can be added
    const inCartQuantity = cartItem ? cartItem.quantity : 0;
    const availableToAdd = maxQuantity - inCartQuantity;
    
    if (quantity < availableToAdd) {
      setQuantity(quantity + 1);
    } else {
      Alert.alert('Maximum Quantity', 'You cannot add more of this item to your cart due to inventory limitations.');
    }
  };

  const decrementQuantity = () => {
    if (quantity > 1) {
      setQuantity(quantity - 1);
    }
  };

  const getStockStatusInfo = (item) => {
    const inventoryCount = item.inventory_count || 0;
    
    if (inventoryCount <= 0) {
      return { label: 'Out of Stock', color: '#dc3545', icon: 'times-circle' };
    } else if (inventoryCount <= 10) {
      return { label: `Low Stock (${inventoryCount} left)`, color: '#ffc107', icon: 'exclamation-circle' };
    } else {
      return { label: `In Stock (${inventoryCount} available)`, color: '#198754', icon: 'check-circle' };
    }
  };

  const getCurrentCartInfo = () => {
    if (!product) return null;
    
    // Find main product in cart
    const mainProductInCart = cartItems.find(item => 
      item.product_id === product.id && (!item.variant_id || item.variant_id === null)
    );
    
    // Find selected variant in cart
    const selectedVariantInCart = selectedVariant 
      ? cartItems.find(item => item.variant_id === selectedVariant.id)
      : null;
    
    const cartItem = selectedVariant ? selectedVariantInCart : mainProductInCart;
    
    if (cartItem) {
      return {
        inCart: true,
        quantity: cartItem.quantity,
        total: cartItem.total || (cartItem.price * cartItem.quantity)
      };
    }
    
    return { inCart: false };
  };

  // Updated variant selector to support swapping
  const renderVariantSelector = () => {
    if (!product) return null;
    
    // If there are no variants and we're in main view, don't show this section
    if (currentView === 'main' && (!alternativeItems || alternativeItems.length === 0)) {
      return null;
    }
    
    return (
      <View style={styles.variantsSection}>
        <Text style={styles.sectionTitle}>
          {currentView === 'main' 
            ? 'Available Variants' 
            : 'Other Available Options'}
        </Text>
        
        {/* Show "Back to main product" button when viewing a variant */}
        {currentView === 'variant' && (
          <TouchableOpacity 
            style={styles.backToMainButton}
            onPress={switchToMainProduct}
          >
            <FallbackIcon name="arrow-left" iconType="FontAwesome" size={16} color="#0066cc" />
            <Text style={styles.backToMainText}>Back to main product</Text>
          </TouchableOpacity>
        )}
        
        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.variantList}>
          {alternativeItems.map(item => {
            const isMainProduct = item.isMainProduct;
            const isOutOfStock = item.inventory_count <= 0;
            const cartItem = cartItems.find(cartItem => 
              isMainProduct 
                ? (cartItem.product_id === product.id && !cartItem.variant_id)
                : (cartItem.variant_id === item.id)
            );
            const inCartQuantity = cartItem ? cartItem.quantity : 0;
            
            return (
              <TouchableOpacity
                key={isMainProduct ? 'main-' + product.id : item.id}
                style={[
                  styles.variantCard,
                  isOutOfStock && styles.outOfStockVariant
                ]}
                onPress={() => {
                  if (!isOutOfStock) {
                    isMainProduct ? switchToMainProduct() : switchToVariant(item);
                  }
                }}
                disabled={isOutOfStock}
              >
                {item.image_url ? (
                  <Image 
                    source={{ uri: item.image_url }} 
                    style={styles.variantImage} 
                    resizeMode="cover"
                  />
                ) : (
                  <View style={styles.variantNoImage}>
                    <FallbackIcon name="image" iconType="FontAwesome" size={20} color="#ccc" />
                  </View>
                )}
                
                <View style={styles.variantInfo}>
                  <Text style={styles.variantName} numberOfLines={2}>
                    {item.name}
                    {isMainProduct && (
                      <Text style={styles.mainProductBadge}> (Main Product)</Text>
                    )}
                  </Text>
                  <Text style={styles.variantPrice}>${item.price.toFixed(2)}</Text>
                  
                  {inCartQuantity > 0 && (
                    <Text style={styles.inCartTag}>{inCartQuantity} in cart</Text>
                  )}
                  
                  {isOutOfStock ? (
                    <Text style={styles.outOfStockText}>Out of Stock</Text>
                  ) : (
                    <Text style={styles.variantStock}>
                      {item.inventory_count} available
                    </Text>
                  )}
                </View>
              </TouchableOpacity>
            );
          })}
        </ScrollView>
      </View>
    );
  };

  // Calculate max quantity that can be added
  const calculateMaxQuantity = () => {
    if (!product) return 0;
    
    const inventoryCount = selectedVariant 
      ? selectedVariant.inventory_count 
      : product.inventory_count;
      
    // Find item in cart if it exists
    const cartItem = cartItems.find(item => {
      if (selectedVariant) {
        return item.variant_id === selectedVariant.id;
      } else {
        return item.product_id === product.id && (!item.variant_id || item.variant_id === null);
      }
    });
    
    const inCartQuantity = cartItem ? cartItem.quantity : 0;
    const availableToAdd = inventoryCount - inCartQuantity;
    
    return availableToAdd;
  };

  const cartInfo = getCurrentCartInfo();
  const maxQuantity = calculateMaxQuantity();

  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.header}>
          <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backButton}>
            <FallbackIcon name="arrow-left" iconType="FontAwesome" size={20} color="#000" />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Product Details</Text>
          <View style={styles.placeholder} />
        </View>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#0066cc" />
          <Text style={styles.loadingText}>Loading product details...</Text>
        </View>
      </SafeAreaView>
    );
  }

  if (error) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.header}>
          <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backButton}>
            <FallbackIcon name="arrow-left" iconType="FontAwesome" size={20} color="#000" />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Product Details</Text>
          <View style={styles.placeholder} />
        </View>
        <View style={styles.errorContainer}>
          <FallbackIcon name="exclamation-circle" iconType="FontAwesome" size={40} color="#dc3545" />
          <Text style={styles.errorText}>{error}</Text>
          <TouchableOpacity style={styles.retryButton} onPress={loadProductDetails}>
            <Text style={styles.retryText}>Retry</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  if (!product) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.header}>
          <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backButton}>
            <FallbackIcon name="arrow-left" iconType="FontAwesome" size={20} color="#000" />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Product Details</Text>
          <View style={styles.placeholder} />
        </View>
        <View style={styles.errorContainer}>
          <Text style={styles.errorText}>Product not found</Text>
        </View>
      </SafeAreaView>
    );
  }

  // Helper function to render stock status
  function renderStockStatus(item) {
    const status = getStockStatusInfo(item);
    
    return (
      <View style={[styles.stockStatusBadge, { backgroundColor: `${status.color}20` }]}>
        <FallbackIcon name={status.icon} iconType="FontAwesome" size={14} color={status.color} />
        <Text style={[styles.stockStatusText, { color: status.color }]}>{status.label}</Text>
      </View>
    );
  }

  // Get the currently displayed item (either main product or selected variant)
  const currentDisplayItem = selectedVariant || product;

  return (
    <SafeAreaView style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backButton}>
          <FallbackIcon name="arrow-left" iconType="FontAwesome" size={20} color="#000" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Product Details</Text>
        <View style={styles.placeholder} />
      </View>
      
      <ScrollView style={styles.scrollContent}>
        {/* Product Image */}
        <View style={styles.imageContainer}>
          {mainImage ? (
            <Image 
              source={{ uri: mainImage }} 
              style={styles.productImage}
              resizeMode="contain"
            />
          ) : (
            <View style={styles.noImageContainer}>
              <FallbackIcon name="image" iconType="FontAwesome" size={60} color="#ccc" />
              <Text style={styles.noImageText}>No Image Available</Text>
            </View>
          )}
        </View>
        
        {/* Variant specific info banner */}
        {currentView === 'variant' && (
          <View style={styles.variantBadge}>
            <Text style={styles.variantBadgeText}>
              Currently viewing variant: {selectedVariant.name}
            </Text>
          </View>
        )}
        
        {/* Product Info */}
        <View style={styles.productInfo}>
          <Text style={styles.productName}>
            {currentDisplayItem.name}
            {currentView === 'variant' && (
              <Text style={styles.variantLabel}> (Variant)</Text>
            )}
          </Text>
          
          {currentDisplayItem.unit_size && currentDisplayItem.unit_type && (
            <Text style={styles.unitInfo}>{currentDisplayItem.unit_size} {currentDisplayItem.unit_type}</Text>
          )}
          
          <Text style={styles.productPrice}>
            ${currentDisplayItem.price.toFixed(2)}
          </Text>
          
          {/* Stock Status */}
          <View style={styles.stockStatus}>
            {renderStockStatus(currentDisplayItem)}
          </View>
          
          {/* Current Cart Info */}
          {cartInfo && cartInfo.inCart && (
            <View style={styles.currentCartInfo}>
              <FallbackIcon name="shopping-cart" iconType="FontAwesome" size={16} color="#0066cc" />
              <Text style={styles.currentCartText}>
                {cartInfo.quantity} already in cart (${cartInfo.total.toFixed(2)})
              </Text>
            </View>
          )}
          
          {/* Description */}
          <Text style={styles.descriptionLabel}>Description:</Text>
          <Text style={styles.description}>{currentDisplayItem.description || 'No description available'}</Text>
        </View>
        
        {/* Variants */}
        {renderVariantSelector()}
        
        {/* Quantity Selector */}
        <View style={styles.quantityContainer}>
          <Text style={styles.quantityLabel}>Quantity:</Text>
          <View style={styles.quantitySelectorContainer}>
            <TouchableOpacity 
              style={styles.quantityButton} 
              onPress={decrementQuantity}
              disabled={quantity <= 1}
            >
              <FallbackIcon 
                name="minus" 
                iconType="AntDesign" 
                size={20} 
                color={quantity <= 1 ? "#ccc" : "#000"} 
              />
            </TouchableOpacity>
            
            <TextInput
              style={styles.quantityInput}
              value={quantity.toString()}
              onChangeText={(text) => {
                const val = parseInt(text);
                if (!isNaN(val) && val > 0 && val <= maxQuantity) {
                  setQuantity(val);
                } else if (text === '') {
                  setQuantity(1);
                }
              }}
              keyboardType="number-pad"
              maxLength={3}
            />
            
            <TouchableOpacity 
              style={styles.quantityButton} 
              onPress={incrementQuantity}
              disabled={quantity >= maxQuantity}
            >
              <FallbackIcon 
                name="plus" 
                iconType="AntDesign" 
                size={20} 
                color={quantity >= maxQuantity ? "#ccc" : "#000"} 
              />
            </TouchableOpacity>
          </View>
          
          {/* Available inventory info */}
          <Text style={styles.availableText}>
            {maxQuantity > 0
              ? `${maxQuantity} available to add`
              : 'Out of stock'}
          </Text>
        </View>
        
        {/* Total Price */}
        <View style={styles.totalContainer}>
          <Text style={styles.totalLabel}>Total:</Text>
          <Text style={styles.totalPrice}>
            ${(currentDisplayItem.price * quantity).toFixed(2)}
          </Text>
        </View>
      </ScrollView>
      
      {/* Footer with Add To Cart button */}
      <View style={styles.footer}>
        <TouchableOpacity 
          style={[
            styles.addButton,
            (!maxQuantity || addingToCart) && styles.disabledButton
          ]} 
          onPress={handleAddToCart}
          disabled={!maxQuantity || addingToCart}
        >
          {addingToCart ? (
            <ActivityIndicator size="small" color="#fff" />
          ) : (
            <>
              <FallbackIcon name="shopping-cart" iconType="FontAwesome" size={20} color="#fff" />
              <Text style={styles.addButtonText}>Add to Cart</Text>
            </>
          )}
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between', // This ensures even spacing
    padding: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
    backgroundColor: '#fff',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    flex: 1,    // Add this to allow the title to take available space
    textAlign: 'center', // Ensure text is centered
  },
  backButton: {
    padding: 5,
    width: 30,  // Fixed width to match the placeholder
  },
  placeholder: {
    width: 30,  // Same width as the back button for balance
    opacity: 0, // Make it invisible but maintain layout
  },
  scrollContent: {
    flex: 1,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  loadingText: {
    marginTop: 10,
    color: '#666',
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  errorText: {
    marginTop: 10,
    color: '#dc3545',
    textAlign: 'center',
  },
  retryButton: {
    marginTop: 15,
    backgroundColor: '#0066cc',
    paddingVertical: 8,
    paddingHorizontal: 20,
    borderRadius: 5,
  },
  retryText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  imageContainer: {
    height: 250,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f9f9f9',
  },
  productImage: {
    width: '100%',
    height: '100%',
  },
  noImageContainer: {
    width: '100%',
    height: '100%',
    justifyContent: 'center',
    alignItems: 'center',
  },
  noImageText: {
    marginTop: 10,
    color: '#999',
  },
  productInfo: {
    padding: 15,
  },
  productName: {
    fontSize: 22,
    fontWeight: 'bold',
    marginBottom: 5,
  },
  unitInfo: {
    fontSize: 14,
    color: '#666',
    marginBottom: 5,
  },
  productPrice: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#0066cc',
    marginBottom: 10,
  },
  stockStatus: {
    marginBottom: 10,
  },
  stockStatusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 5,
    borderRadius: 4,
    alignSelf: 'flex-start',
  },
  stockStatusText: {
    marginLeft: 5,
    fontSize: 14,
  },
  currentCartInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 15,
    padding: 10,
    backgroundColor: '#e6f2ff',
    borderRadius: 5,
  },
  currentCartText: {
    marginLeft: 10,
    color: '#0066cc',
  },
  descriptionLabel: {
    fontSize: 16,
    fontWeight: 'bold',
    marginTop: 10,
    marginBottom: 5,
  },
  description: {
    color: '#666',
    lineHeight: 20,
  },
  variantsSection: {
    padding: 15,
    borderTopWidth: 1,
    borderTopColor: '#eee',
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 10,
  },
  variantList: {
    flexDirection: 'row',
  },
  variantCard: {
    width: 120,
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    marginRight: 10,
    overflow: 'hidden',
  },
  selectedVariant: {
    borderColor: '#0066cc',
    borderWidth: 2,
  },
  outOfStockVariant: {
    opacity: 0.6,
  },
  variantImage: {
    width: '100%',
    height: 80,
  },
  variantNoImage: {
    width: '100%',
    height: 80,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f9f9f9',
  },
  variantInfo: {
    padding: 8,
  },
  variantName: {
    fontSize: 14,
    fontWeight: '500',
    marginBottom: 2,
  },
  variantPrice: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#0066cc',
  },
  variantStock: {
    fontSize: 12,
    color: '#198754',
    marginTop: 2,
  },
  outOfStockText: {
    fontSize: 12,
    color: '#dc3545',
    fontWeight: '500',
    marginTop: 2,
  },
  inCartTag: {
    fontSize: 12,
    backgroundColor: '#e6f2ff',
    color: '#0066cc',
    padding: 2,
    paddingHorizontal: 4,
    borderRadius: 4,
    alignSelf: 'flex-start',
    marginTop: 4,
    marginBottom: 2,
  },
  quantityContainer: {
    padding: 15,
    borderTopWidth: 1,
    borderTopColor: '#eee',
  },
  quantityLabel: {
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 10,
  },
  quantitySelectorContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  quantityButton: {
    width: 40,
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f0f0f0',
    borderRadius: 4,
  },
  quantityInput: {
    width: 60,
    height: 40,
    textAlign: 'center',
    marginHorizontal: 10,
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 4,
    backgroundColor: '#fff',
  },
  availableText: {
    marginTop: 5,
    fontSize: 12,
    color: '#666',
  },
  totalContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 15,
    borderTopWidth: 1,
    borderTopColor: '#eee',
  },
  totalLabel: {
    fontSize: 16,
    fontWeight: 'bold',
  },
  totalPrice: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#0066cc',
  },
  footer: {
    padding: 15,
    borderTopWidth: 1,
    borderTopColor: '#eee',
    backgroundColor: '#fff',
  },
  addButton: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#28a745',
    borderRadius: 4,
    paddingVertical: 12,
  },
  disabledButton: {
    backgroundColor: '#ccc',
  },
  addButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
    marginLeft: 10,
  },
  // New styles for variant swapping
  backToMainButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#e6f2ff',
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 4,
    marginBottom: 10,
    alignSelf: 'flex-start',
  },
  backToMainText: {
    color: '#0066cc',
    marginLeft: 6,
    fontWeight: '500',
  },
  mainProductBadge: {
    fontSize: 12,
    color: '#666',
    fontStyle: 'italic',
  },
  variantLabel: {
    fontSize: 14,
    color: '#666',
    fontStyle: 'italic',
    fontWeight: 'normal',
  },
  variantBadge: {
    backgroundColor: '#f8f9fa',
    borderWidth: 1,
    borderColor: '#dee2e6',
    borderRadius: 4,
    padding: 10,
    margin: 10,
  },
  variantBadgeText: {
    fontSize: 13,
    color: '#495057',
  },
});

export default ProductDetailScreen;