import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  Modal,
  Image,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  ActivityIndicator,
  SafeAreaView,
  TextInput,
  Alert,
  Platform
} from 'react-native';
import FallbackIcon from './icon/FallbackIcon';
import { getProductDetails, addToCart, getCart } from '../services/api';
import { cartEventEmitter } from './FranchiseeLayout';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { 
  addToCartWithQuantityManagement, 
  incrementQuantityWithCheck, 
  calculateMaxQuantity, 
  getInventoryStatus,
  getCartItemInfo
} from '../utils/quantityManagement';

const AddToCartModal = ({ visible, productId, onClose }) => {
  const [loading, setLoading] = useState(true);
  const [product, setProduct] = useState(null);
  const [selectedVariant, setSelectedVariant] = useState(null);
  const [quantity, setQuantity] = useState(1);
  const [cartItems, setCartItems] = useState([]);
  const [error, setError] = useState('');
  const [addingToCart, setAddingToCart] = useState(false);

  // Fetch product details when modal opens
  useEffect(() => {
    if (visible && productId) {
      loadProductDetails();
    }
  }, [visible, productId]);

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
          
          console.log('Cart items loaded:', cartItems.length);
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

  const handleAddToCart = async () => {
    if (!product) return;
    
    setAddingToCart(true);
    
    const token = await AsyncStorage.getItem('userToken');
    if (!token) {
      Alert.alert('Error', 'Not authenticated');
      setAddingToCart(false);
      return;
    }
    
    // Use centralized quantity management
    await addToCartWithQuantityManagement({
      product,
      selectedVariant,
      quantity,
      cartItems,
      userToken: token,
      addToCartAPI: addToCart,
      onSuccess: ({ response, actualQuantityAdded, productName, successMessage, wasAdjusted }) => {
        // Handle cart count update from various response formats
        let cartCount = 0;
        if (response.cart && typeof response.cart.items_count === 'number') {
          cartCount = response.cart.items_count;
        } else if (response.cart && Array.isArray(response.cart.items)) {
          cartCount = response.cart.items.length;
        } else if (response.cart_items && Array.isArray(response.cart_items)) {
          cartCount = response.cart_items.length;
        } else if (typeof response.items_count === 'number') {
          cartCount = response.items_count;
        } else if (typeof response.cart_count === 'number') {
          cartCount = response.cart_count;
        }
        
        // Emit cart update event
        if (cartCount > 0) {
          cartEventEmitter.emit('cartUpdated', cartCount);
        } else {
          // If we can't determine cart count, refetch cart
          getCart(token).then(cartResponse => {
            if (cartResponse.success) {
              let updatedCount = 0;
              if (cartResponse.cart && typeof cartResponse.cart.items_count === 'number') {
                updatedCount = cartResponse.cart.items_count;
              } else if (cartResponse.cart_items && Array.isArray(cartResponse.cart_items)) {
                updatedCount = cartResponse.cart_items.length;
              } else if (Array.isArray(cartResponse.cart?.items)) {
                updatedCount = cartResponse.cart.items.length;
              }
              cartEventEmitter.emit('cartUpdated', updatedCount);
            }
          });
        }
        
        // Show success alert with appropriate message
        const alertTitle = wasAdjusted ? 'Added with Adjustment' : 'Added to Cart';
        Alert.alert(
          alertTitle,
          successMessage,
          [{ text: 'Continue Shopping', onPress: () => onClose() }]
        );
        
        setAddingToCart(false);
      },
      onError: (errorMessage, title = 'Error') => {
        Alert.alert(title, errorMessage);
        setAddingToCart(false);
      }
    });
  };

  const incrementQuantity = () => {
    // Use centralized quantity increment logic
    incrementQuantityWithCheck({
      product,
      selectedVariant,
      currentQuantity: quantity,
      cartItems,
      onSuccess: (newQuantity) => {
        setQuantity(newQuantity);
      },
      onError: (errorMessage, title) => {
        Alert.alert(title, errorMessage, [{ text: 'OK' }]);
      }
    });
  };

  const decrementQuantity = () => {
    if (quantity > 1) {
      setQuantity(quantity - 1);
    }
  };

  const renderVariantSelector = () => {
    if (!product || !product.variants || product.variants.length === 0) {
      return null;
    }
    
    return (
      <View style={styles.variantsSection}>
        <Text style={styles.sectionTitle}>Available Variants</Text>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.variantList}>
          {product.variants.map(variant => {
            const isSelected = selectedVariant && selectedVariant.id === variant.id;
            const inventoryStatus = getInventoryStatus(variant);
            const isOutOfStock = inventoryStatus.status === 'out_of_stock';
            const cartItemInfo = getCartItemInfo(product, variant, cartItems);
            
            return (
              <TouchableOpacity
                key={variant.id}
                style={[
                  styles.variantCard,
                  isSelected && styles.selectedVariant,
                  isOutOfStock && styles.outOfStockVariant
                ]}
                onPress={() => {
                  if (!isOutOfStock) {
                    setSelectedVariant(isSelected ? null : variant);
                    setQuantity(1); // Reset quantity when variant changes
                  }
                }}
                disabled={isOutOfStock}
              >
                {variant.image_url ? (
                  <Image 
                    source={{ uri: variant.image_url }} 
                    style={styles.variantImage} 
                    resizeMode="cover"
                  />
                ) : (
                  <View style={styles.variantNoImage}>
                    <FallbackIcon name="image" iconType="FontAwesome" size={20} color="#ccc" />
                  </View>
                )}
                
                <View style={styles.variantInfo}>
                  <Text style={styles.variantName} numberOfLines={2}>{variant.name}</Text>
                  <Text style={styles.variantPrice}>
                    ${(variant.price_adjustment || variant.price || 0).toFixed(2)}
                  </Text>
                  
                  {cartItemInfo.inCart && (
                    <Text style={styles.inCartTag}>{cartItemInfo.quantity} in cart</Text>
                  )}
                  
                  <Text style={[
                    styles.variantStock,
                    { color: inventoryStatus.color }
                  ]}>
                    {inventoryStatus.label}
                  </Text>
                </View>
              </TouchableOpacity>
            );
          })}
        </ScrollView>
      </View>
    );
  };

  // Get current cart information
  const cartInfo = getCartItemInfo(product, selectedVariant, cartItems);
  const maxQuantity = calculateMaxQuantity(product, selectedVariant, cartItems);
  
  // Get inventory status for the current item (product or selected variant)
  const currentItem = selectedVariant || product;
  const inventoryStatus = currentItem ? getInventoryStatus(currentItem) : null;

  return (
    <Modal
      visible={visible}
      animationType="slide"
      transparent={true}
      onRequestClose={onClose}
    >
      <SafeAreaView style={styles.container}>
        <View style={styles.modalContent}>
          {/* Header */}
          <View style={styles.header}>
            <Text style={styles.headerTitle}>Add to Cart</Text>
            <TouchableOpacity onPress={onClose} style={styles.closeButton}>
              <FallbackIcon name="close" iconType="AntDesign" size={24} color="#000" />
            </TouchableOpacity>
          </View>
          
          {/* Content */}
          {loading ? (
            <View style={styles.loadingContainer}>
              <ActivityIndicator size="large" color="#0066cc" />
              <Text style={styles.loadingText}>Loading product details...</Text>
            </View>
          ) : error ? (
            <View style={styles.errorContainer}>
              <FallbackIcon name="exclamation-circle" iconType="FontAwesome" size={40} color="#dc3545" />
              <Text style={styles.errorText}>{error}</Text>
              <TouchableOpacity style={styles.retryButton} onPress={loadProductDetails}>
                <Text style={styles.retryText}>Retry</Text>
              </TouchableOpacity>
            </View>
          ) : product ? (
            <ScrollView style={styles.scrollContent}>
              {/* Product Image */}
              <View style={styles.imageContainer}>
                {product.image_url ? (
                  <Image 
                    source={{ uri: product.image_url }} 
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
              
              {/* Product Info */}
              <View style={styles.productInfo}>
                <Text style={styles.productName}>{product.name}</Text>
                
                {product.unit_size && product.unit_type && (
                  <Text style={styles.unitInfo}>{product.unit_size} {product.unit_type}</Text>
                )}
                
                <Text style={styles.productPrice}>
                  ${selectedVariant 
                    ? (selectedVariant.price_adjustment || selectedVariant.price || 0).toFixed(2)
                    : (product.price || product.base_price || 0).toFixed(2)
                  }
                </Text>
                
                {/* Stock Status */}
                {inventoryStatus && (
                  <View style={styles.stockStatus}>
                    <View style={[styles.stockStatusBadge, { backgroundColor: `${inventoryStatus.color}20` }]}>
                      <FallbackIcon name={inventoryStatus.icon} iconType="FontAwesome" size={14} color={inventoryStatus.color} />
                      <Text style={[styles.stockStatusText, { color: inventoryStatus.color }]}>
                        {inventoryStatus.label}
                      </Text>
                    </View>
                  </View>
                )}
                
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
                <Text style={styles.description}>{product.description || 'No description available'}</Text>
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
                  ${(
                    (selectedVariant 
                      ? (selectedVariant.price_adjustment || selectedVariant.price || 0)
                      : (product.price || product.base_price || 0)
                    ) * quantity
                  ).toFixed(2)}
                </Text>
              </View>
            </ScrollView>
          ) : (
            <View style={styles.errorContainer}>
              <Text style={styles.errorText}>Product not found</Text>
            </View>
          )}
          
          {/* Footer with Add To Cart button */}
          <View style={styles.footer}>
            <TouchableOpacity 
              style={[
                styles.addButton,
                (!product || !maxQuantity || addingToCart) && styles.disabledButton
              ]} 
              onPress={handleAddToCart}
              disabled={!product || !maxQuantity || addingToCart}
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
        </View>
      </SafeAreaView>
    </Modal>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: '#fff',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    height: '90%',
    paddingBottom: Platform.OS === 'ios' ? 20 : 0,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: 'bold',
  },
  closeButton: {
    padding: 5,
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
    height: 200,
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
    fontSize: 20,
    fontWeight: 'bold',
    marginBottom: 5,
  },
  unitInfo: {
    fontSize: 14,
    color: '#666',
    marginBottom: 5,
  },
  productPrice: {
    fontSize: 18,
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
    paddingTop: 0,
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
});

export default AddToCartModal;