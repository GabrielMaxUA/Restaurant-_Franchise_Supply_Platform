import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  Image,
  ActivityIndicator,
  RefreshControl,
  SafeAreaView,
  Alert,
  Platform,
  TextInput,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { getCart, updateCartItemQuantity, removeCartItem, logout } from '../services/api';
import { handleCartUpdateResponse, getInventoryStatus } from '../utils/quantityManagement';
import FranchiseeLayout, { cartEventEmitter } from '../components/FranchiseeLayout';

const CartScreen = ({ navigation }) => {
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [cartItems, setCartItems] = useState([]);
  const [cartTotal, setCartTotal] = useState(0);
  const [error, setError] = useState('');
  const [userToken, setUserToken] = useState('');
  const [processingItemId, setProcessingItemId] = useState(null);
  const [editingQuantities, setEditingQuantities] = useState({}); // Track editing state for each item

  useEffect(() => {
    const getTokenFromStorage = async () => {
      try {
        const token = await AsyncStorage.getItem('userToken');
        if (token) {
          setUserToken(token);
        } else {
          setError('Authentication token not found. Please login again.');
        }
      } catch (e) {
        console.error('Failed to get token from storage:', e);
        setError('Failed to authenticate. Please login again.');
        logout();
      }
    };

    getTokenFromStorage();
  }, []);

  useEffect(() => {
    if (userToken) {
      loadCart();
    }
  }, [userToken]);

  const loadCart = async () => {
    if (!userToken) return;

    try {
      setLoading(true);
      setError('');

      const cartResponse = await getCart(userToken);
      console.log('Cart response (full):', JSON.stringify(cartResponse));
      
      if (cartResponse.success) {
        // Handle the updated API response structure
        if (cartResponse.cart_items && Array.isArray(cartResponse.cart_items)) {
          console.log(`Found ${cartResponse.cart_items.length} items in cart`);
          setCartItems(cartResponse.cart_items);
          setCartTotal(cartResponse.total || 0);
          console.log(`Cart total: ${cartResponse.total || 0}`);
          
          // Emit cart update event with item count
          console.log(`🛒 Emitting cartUpdated event from loadCart with count: ${cartResponse.cart_items.length}`);
          cartEventEmitter.emit('cartUpdated', cartResponse.cart_items.length);
        } else {
          // Fallback for other response structures (keeping existing logic)
          if (cartResponse.data) {
            const data = cartResponse.data;
            let cartCount = 0;
            
            if (data.cart_items && Array.isArray(data.cart_items)) {
              setCartItems(data.cart_items);
              setCartTotal(data.total || 0);
              cartCount = data.cart_items.length;
            } else if (data.items && Array.isArray(data.items)) {
              setCartItems(data.items);
              setCartTotal(data.total || 0);
              cartCount = data.items.length;
            } else if (Array.isArray(data)) {
              setCartItems(data);
              setCartTotal(data.reduce((sum, item) => 
                sum + ((item.price || 0) * (item.quantity || 1)), 0));
              cartCount = data.length;
            }
            
            cartEventEmitter.emit('cartUpdated', cartCount);
          } else if (cartResponse.items_count !== undefined) {
            cartEventEmitter.emit('cartUpdated', cartResponse.items_count);
            throw new Error('Unexpected response format - missing items array');
          } else {
            throw new Error('Unexpected response format');
          }
        }
      } else {
        throw new Error(cartResponse.error || cartResponse.message || 'Failed to load cart');
      }
    } catch (error) {
      console.error('Cart loading error:', error);
      
      if (error.message && error.message.includes('Authentication error')) {
        console.log('Authentication error in loadCart, letting FranchiseeLayout handle it');
      } else {
        setError('Failed to load cart. Pull down to refresh.');
      }
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadCart();
  };

  const handleCheckout = () => {
    if (cartItems.length === 0) {
      Alert.alert('Cart Empty', 'Please add items to your cart before checking out.');
      return;
    }

    Alert.alert(
      'Confirm Checkout',
      'Do you want to proceed with checkout?',
      [
        {
          text: 'Cancel',
          style: 'cancel'
        },
        {
          text: 'Checkout',
          onPress: () => {
            navigation.navigate('Checkout');
          }
        }
      ]
    );
  };

  const formatCurrency = (amount) => {
    if (amount === undefined || amount === null) {
      return '$0.00';
    }
    
    const numericAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
    
    if (isNaN(numericAmount)) {
      console.warn('Invalid price value:', amount);
      return '$0.00';
    }
    
    return '$' + numericAmount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
  };

  // Handle quantity change with improved error handling
  const handleQuantityChange = async (itemId, newQuantity) => {
    if (processingItemId === itemId) {
      return; // Prevent multiple simultaneous operations
    }

    try {
      setProcessingItemId(itemId);

      // Get current item details
      const currentItem = cartItems.find(item => item.id === itemId);
      if (!currentItem) {
        throw new Error('Item not found in cart');
      }

      // If quantity is zero or negative, ask about removal
      if (newQuantity < 1) {
        Alert.alert(
          'Remove Item',
          'Do you want to remove this item from your cart?',
          [
            {
              text: 'Cancel',
              style: 'cancel',
              onPress: () => setProcessingItemId(null)
            },
            {
              text: 'Remove',
              onPress: () => removeItem(itemId),
              style: 'destructive'
            }
          ]
        );
        return;
      }
      
      // Save original state for rollback if needed
      const originalItems = [...cartItems];
      const originalTotal = cartTotal;

      // Optimistically update the UI
      const updatedItems = cartItems.map(item => {
        if (item.id === itemId) {
          const updatedItem = { ...item, quantity: newQuantity };
          if (item.price) {
            updatedItem.subtotal = item.price * newQuantity;
          }
          return updatedItem;
        }
        return item;
      });

      setCartItems(updatedItems);
      
      // Calculate new cart total
      const newTotal = updatedItems.reduce(
        (sum, item) => sum + ((item.price || 0) * (item.quantity || 1)), 
        0
      );
      setCartTotal(newTotal);
      
      // Make the API call
      const response = await updateCartItemQuantity(itemId, newQuantity);
      
      // Process the response using our utility function
      const itemName = currentItem.variant 
        ? `${currentItem.product.name} (${currentItem.variant.name})`
        : currentItem.product.name;
      
      const processedResponse = handleCartUpdateResponse(response, itemName, newQuantity);
      
      if (processedResponse.success) {
        // Handle successful update
        if (processedResponse.wasAdjusted) {
          // If quantity was adjusted, update the UI with the actual values
          if (processedResponse.itemRemoved) {
            // Item was removed due to no stock
            const filteredItems = cartItems.filter(item => item.id !== itemId);
            setCartItems(filteredItems);
            setCartTotal(filteredItems.reduce((sum, item) => 
              sum + ((item.price || 0) * (item.quantity || 1)), 0));
            
            // Update cart count
            cartEventEmitter.emit('cartUpdated', filteredItems.length);
          } else {
            // Quantity was adjusted
            const adjustedItems = cartItems.map(item => {
              if (item.id === itemId) {
                const adjustedItem = { ...item, quantity: processedResponse.finalQuantity };
                if (item.price) {
                  adjustedItem.subtotal = item.price * processedResponse.finalQuantity;
                }
                return adjustedItem;
              }
              return item;
            });
            
            setCartItems(adjustedItems);
            setCartTotal(adjustedItems.reduce((sum, item) => 
              sum + ((item.price || 0) * (item.quantity || 1)), 0));
          }
          
          // Show adjustment message
          Alert.alert(
            'Quantity Adjusted',
            processedResponse.userFriendlyMessage,
            [{ text: 'OK' }]
          );
        }
        
        // Update cart count if provided in response
        if (response.items_count !== undefined) {
          cartEventEmitter.emit('cartUpdated', response.items_count);
        }
      } else {
        // Revert optimistic update on error
        console.log('⚠️ API error, reverting to previous cart state');
        setCartItems(originalItems);
        setCartTotal(originalTotal);
        
        // Show error message
        Alert.alert(
          'Update Failed',
          processedResponse.userFriendlyMessage,
          [{ text: 'OK' }]
        );
      }
    } catch (error) {
      console.error('Error updating quantity:', error);
      
      // Revert to original state
      const originalItems = cartItems.filter(item => item.id !== itemId);
      if (originalItems.length !== cartItems.length) {
        setCartItems(originalItems);
        setCartTotal(originalItems.reduce((sum, item) => 
          sum + ((item.price || 0) * (item.quantity || 1)), 0));
      }
      
      Alert.alert(
        'Update Failed',
        'Failed to update item quantity. Please try again.',
        [{ text: 'OK' }]
      );
    } finally {
      setProcessingItemId(null);
    }
  };

  // Handle removing item from cart with improved error handling
  const removeItem = async (itemId) => {
    if (processingItemId === itemId) {
      console.log(`⚠️ Already processing item ${itemId}, ignoring duplicate request`);
      return;
    }

    console.log(`🗑️ Starting removeItem process for item ${itemId}`);
    setProcessingItemId(itemId);
    
    // Find the item to be removed
    const itemToRemove = cartItems.find(item => item.id === itemId);
    if (!itemToRemove) {
      console.error(`❌ Cannot find item ${itemId} in cart items`);
      setProcessingItemId(null);
      return;
    }
    
    // Store original cart state for rollback
    const originalItems = [...cartItems];
    const originalTotal = cartTotal;
    
    try {
      // Optimistically update UI
      const updatedItems = cartItems.filter(item => item.id !== itemId);
      setCartItems(updatedItems);
      
      // Update cart total
      const itemPrice = itemToRemove.price || 0;
      const itemQuantity = itemToRemove.quantity || 1;
      const itemTotal = itemPrice * itemQuantity;
      const newTotal = Math.max(0, cartTotal - itemTotal);
      setCartTotal(newTotal);
      
      // Update cart count in header
      cartEventEmitter.emit('cartUpdated', updatedItems.length);
      
      // Make API call
      console.log(`📞 Calling removeCartItem API for item ${itemId}`);
      const response = await removeCartItem(itemId);
      console.log(`📊 Remove response for item ${itemId}:`, response);
      
      if (response && response.success) {
        console.log(`✅ Successfully removed item ${itemId}`);
        
        // Update cart count if provided in response
        if (response.items_count !== undefined) {
          console.log(`📊 Emitting cartUpdated event with count: ${response.items_count}`);
          cartEventEmitter.emit('cartUpdated', response.items_count);
        }
      } else {
        // Revert optimistic update on error
        console.log(`⚠️ API returned error for item ${itemId}, reverting state`);
        setCartItems(originalItems);
        setCartTotal(originalTotal);
        cartEventEmitter.emit('cartUpdated', originalItems.length);
        
        const errorMessage = response?.message || 'Failed to remove item';
        Alert.alert('Error', errorMessage, [{ text: 'OK' }]);
      }
    } catch (error) {
      console.error(`❌ Error removing item ${itemId}:`, error);
      
      // Revert optimistic update
      console.log('↩️ Reverting to original cart state due to error');
      setCartItems(originalItems);
      setCartTotal(originalTotal);
      cartEventEmitter.emit('cartUpdated', originalItems.length);
      
      // Check if it's an authentication error
      if (error.message && error.message.includes('Authentication error')) {
        console.log(`🔒 Authentication error detected for item ${itemId}, letting FranchiseeLayout handle it`);
      } else {
        console.log(`⚠️ Showing error alert for item ${itemId}`);
        Alert.alert(
          'Error',
          'Failed to remove item. Please try again.',
          [{ text: 'OK' }]
        );
      }
    } finally {
      console.log(`🏁 Finishing removeItem process for item ${itemId}`);
      setProcessingItemId(null);
    }
  };

  const renderCartItem = ({ item }) => {
    // Get inventory status for the item
    const inventorySource = item.variant || item.product;
    const inventoryStatus = getInventoryStatus(inventorySource);
    const hasInventoryInfo = inventorySource && typeof inventorySource.inventory_count !== 'undefined';
    const inventoryCount = hasInventoryInfo ? inventorySource.inventory_count : null;
    const isAtMaxStock = hasInventoryInfo && item.quantity >= inventoryCount;
    const maxAllowedQuantity = hasInventoryInfo ? inventoryCount : 99;
    
    // Get current editing value or actual quantity
    const isEditing = editingQuantities.hasOwnProperty(item.id);
    const displayValue = isEditing ? editingQuantities[item.id] : item.quantity.toString();
    
    // Handler for when user starts editing
    const handleEditStart = (itemId, currentValue) => {
      setEditingQuantities(prev => ({
        ...prev,
        [itemId]: currentValue
      }));
    };
    
    // Handler for text changes during editing
    const handleTextChange = (text, itemId) => {
      setEditingQuantities(prev => ({
        ...prev,
        [itemId]: text
      }));
    };
    
    // Handler for when user finishes editing (keyboard done/blur)
    const handleQuantitySubmit = (text, itemId) => {
      // Remove from editing state
      setEditingQuantities(prev => {
        const newState = { ...prev };
        delete newState[itemId];
        return newState;
      });
      
      if (text === '' || text === '0') {
        // If empty or zero, reset to 1
        handleQuantityChange(itemId, 1);
        return;
      }
      
      const newQuantity = parseInt(text);
      
      if (isNaN(newQuantity) || newQuantity < 1) {
        // Invalid input, don't change anything
        return;
      }
      
      if (hasInventoryInfo && newQuantity > maxAllowedQuantity) {
        // Automatically adjust to maximum available
        handleQuantityChange(itemId, maxAllowedQuantity);
        Alert.alert(
          'Quantity Adjusted', 
          `Quantity set to maximum available: ${maxAllowedQuantity}`
        );
        return;
      }
      
      // Valid quantity, update
      handleQuantityChange(itemId, newQuantity);
    };
    
    // Handler for when editing is cancelled (onBlur without submit)
    const handleEditCancel = (itemId) => {
      // Remove from editing state without changing quantity
      setEditingQuantities(prev => {
        const newState = { ...prev };
        delete newState[itemId];
        return newState;
      });
    };
    
    return (
      <View style={styles.cartItemCard}>
        {item.product.image_url ? (
          <Image 
            source={{ uri: item.product.image_url }} 
            style={styles.cartItemImage}
            resizeMode="cover"
          />
        ) : (
          <View style={styles.cartItemImagePlaceholder}>
            <Text style={styles.cartItemImagePlaceholderText}>No Image</Text>
          </View>
        )}
        
        <View style={styles.cartItemDetails}>
          <Text style={styles.cartItemName}>{item.product.name}</Text>
          
          {item.variant && (
            <Text style={styles.cartItemVariant}>
              {item.variant.name}
            </Text>
          )}
          
          {/* Always show stock information */}
          <View style={styles.stockInfoContainer}>
            {hasInventoryInfo ? (
              <View style={styles.stockInfoRow}>
                <Text style={styles.stockAvailableText}>
                  Available: {inventoryCount}
                </Text>
                {inventoryStatus.status === 'out_of_stock' ? (
                  <Text style={styles.outOfStockText}>• Out of Stock</Text>
                ) : inventoryStatus.status === 'low_stock' ? (
                  <Text style={styles.lowStockText}>• Low Stock</Text>
                ) : isAtMaxStock ? (
                  <Text style={styles.maxStockText}>• At Maximum</Text>
                ) : null}
              </View>
            ) : (
              <Text style={styles.stockAvailableText}>Stock info unavailable</Text>
            )}
          </View>
          
          <View style={styles.cartItemFooter}>
            <View style={styles.priceAndQuantityContainer}>
              <Text style={styles.cartItemPrice}>
                {formatCurrency(item.price || item.product.base_price)}
              </Text>
              
              <View style={styles.cartItemQuantity}>
                <TouchableOpacity 
                  style={[
                    styles.quantityButton,
                    (processingItemId === item.id || item.quantity <= 1) && styles.disabledButton
                  ]}
                  disabled={processingItemId === item.id || item.quantity <= 1}
                  onPress={() => handleQuantityChange(item.id, item.quantity - 1)}
                >
                  <Text style={styles.quantityButtonText}>-</Text>
                </TouchableOpacity>
                
                {processingItemId === item.id ? (
                  <View style={styles.quantityInputContainer}>
                    <Text style={styles.quantityText}>...</Text>
                  </View>
                ) : (
                  <TextInput
                    style={styles.quantityInput}
                    value={displayValue}
                    onFocus={() => handleEditStart(item.id, item.quantity.toString())}
                    onChangeText={(text) => handleTextChange(text, item.id)}
                    onSubmitEditing={() => handleQuantitySubmit(displayValue, item.id)}
                    onBlur={() => {
                      if (isEditing) {
                        handleQuantitySubmit(displayValue, item.id);
                      }
                    }}
                    keyboardType="number-pad"
                    maxLength={3}
                    selectTextOnFocus={true}
                    editable={processingItemId !== item.id}
                    returnKeyType="done"
                    blurOnSubmit={true}
                  />
                )}
                
                <TouchableOpacity 
                  style={[
                    styles.quantityButton,
                    (processingItemId === item.id || isAtMaxStock) && styles.disabledButton
                  ]}
                  disabled={processingItemId === item.id || isAtMaxStock}
                  onPress={() => handleQuantityChange(item.id, item.quantity + 1)}
                >
                  <Text style={styles.quantityButtonText}>+</Text>
                </TouchableOpacity>
              </View>
            </View>
            
            {/* Show subtotal */}
            <Text style={styles.subtotalText}>
              Subtotal: {formatCurrency((item.price || item.product.base_price) * item.quantity)}
            </Text>
          </View>
        </View>
        
        <TouchableOpacity 
          style={styles.removeButton}
          disabled={processingItemId === item.id}
          onPress={() => {
            Alert.alert(
              'Remove Item',
              'Are you sure you want to remove this item from your cart?',
              [
                {
                  text: 'Cancel',
                  style: 'cancel'
                },
                {
                  text: 'Remove',
                  onPress: () => removeItem(item.id),
                  style: 'destructive'
                }
              ]
            );
          }}
        >
          <Text style={[
            styles.removeButtonText,
            processingItemId === item.id && styles.disabledText
          ]}>×</Text>
        </TouchableOpacity>
      </View>
    );
  };

  if (loading && !refreshing) {
    return (
      <FranchiseeLayout title="Your Cart">
        <View style={styles.centered}>
          <ActivityIndicator size="large" color="#0066cc" />
          <Text style={styles.loadingText}>Loading cart...</Text>
        </View>
      </FranchiseeLayout>
    );
  }

  return (
    <FranchiseeLayout title="Your Cart">
      <SafeAreaView style={styles.container}>
        <View style={styles.header}>
          <Text style={styles.headerTitle}>Your Cart</Text>
          <Text style={styles.headerSubtitle}>
            {cartItems.length} {cartItems.length === 1 ? 'item' : 'items'}
          </Text>
        </View>
        
        {error ? (
          <View style={styles.errorContainer}>
            <Text style={styles.errorText}>{error}</Text>
          </View>
        ) : null}
        
        <FlatList
          data={cartItems}
          renderItem={renderCartItem}
          keyExtractor={(item) => `${item.id}-${item.product.id}-${item.variant ? item.variant.id : 'no-variant'}`}
          contentContainerStyle={styles.cartItemsList}
          refreshControl={
            <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
          }
          ListEmptyComponent={
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyText}>Your cart is empty</Text>
              <TouchableOpacity 
                style={styles.browseCatalogButton}
                onPress={() => navigation.navigate('Catalog')}
              >
                <Text style={styles.browseCatalogButtonText}>Browse Catalog</Text>
              </TouchableOpacity>
            </View>
          }
        />
        
        {cartItems.length > 0 && (
          <View style={styles.checkoutContainer}>
            <View style={styles.totalContainer}>
              <Text style={styles.totalLabel}>Total:</Text>
              <Text style={styles.totalAmount}>{formatCurrency(cartTotal)}</Text>
            </View>
            
            <TouchableOpacity
              style={styles.checkoutButton}
              onPress={handleCheckout}
            >
              <Text style={styles.checkoutButtonText}>Checkout</Text>
            </TouchableOpacity>
          </View>
        )}
      </SafeAreaView>
    </FranchiseeLayout>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  centered: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  loadingText: {
    marginTop: 10,
    fontSize: 16,
    color: '#666',
  },
  stockInfoContainer: {
    marginBottom: 8,
  },
  stockInfoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    flexWrap: 'wrap',
  },
  stockAvailableText: {
    fontSize: 12,
    color: '#2196F3',
    fontWeight: '600',
    marginRight: 8,
  },
  outOfStockText: {
    fontSize: 12,
    color: '#e74c3c',
    fontWeight: 'bold',
  },
  lowStockText: {
    fontSize: 12,
    color: '#f39c12',
    fontWeight: 'bold',
  },
  maxStockText: {
    fontSize: 12,
    color: '#9e9e9e',
    fontWeight: 'bold',
  },
  header: {
    padding: 15,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  headerTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#333',
  },
  headerSubtitle: {
    fontSize: 14,
    color: '#666',
    marginTop: 5,
  },
  errorContainer: {
    margin: 15,
    padding: 10,
    backgroundColor: '#ffebee',
    borderRadius: 5,
  },
  errorText: {
    color: '#c62828',
  },
  cartItemsList: {
    padding: 15,
    paddingBottom: 130,
  },
  cartItemCard: {
    flexDirection: 'row',
    backgroundColor: '#fff',
    borderRadius: 10,
    marginBottom: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
    overflow: 'hidden',
  },
  cartItemImage: {
    width: 80,
    height: 80,
    backgroundColor: '#f0f0f0',
  },
  cartItemImagePlaceholder: {
    width: 80,
    height: 80,
    backgroundColor: '#f0f0f0',
    justifyContent: 'center',
    alignItems: 'center',
  },
  cartItemImagePlaceholderText: {
    color: '#999',
    fontSize: 12,
  },
  cartItemDetails: {
    flex: 1,
    padding: 10,
  },
  cartItemName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 4,
  },
  cartItemVariant: {
    fontSize: 14,
    color: '#666',
    marginBottom: 8,
  },
  cartItemFooter: {
    marginTop: 'auto',
  },
  priceAndQuantityContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 4,
  },
  cartItemPrice: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  subtotalText: {
    fontSize: 14,
    color: '#666',
    fontStyle: 'italic',
  },
  cartItemQuantity: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  quantityButton: {
    width: 28,
    height: 28,
    backgroundColor: '#f0f0f0',
    borderRadius: 14,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#ddd',
  },
  quantityInput: {
    width: 45,
    height: 32,
    textAlign: 'center',
    marginHorizontal: 8,
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 6,
    backgroundColor: '#fff',
    fontSize: 14,
    color: '#333',
    paddingVertical: 6,
    paddingHorizontal: 4,
  },
  quantityInputContainer: {
    width: 45,
    height: 32,
    justifyContent: 'center',
    alignItems: 'center',
    marginHorizontal: 8,
  },
  disabledButton: {
    opacity: 0.5,
  },
  disabledText: {
    opacity: 0.5,
  },
  quantityButtonText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  quantityText: {
    fontSize: 14,
    color: '#333',
    textAlign: 'center',
  },
  removeButton: {
    width: 30,
    height: 30,
    justifyContent: 'center',
    alignItems: 'center',
    position: 'absolute',
    top: 0,
    right: 0,
  },
  removeButtonText: {
    fontSize: 24,
    color: '#999',
  },
  emptyContainer: {
    padding: 30,
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 16,
    color: '#999',
    marginBottom: 20,
  },
  browseCatalogButton: {
    backgroundColor: '#0066cc',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 5,
  },
  browseCatalogButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '500',
  },
  checkoutContainer: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: '#fff',
    borderTopWidth: 1,
    borderTopColor: '#eee',
    padding: 15,
    paddingBottom: Platform.OS === 'ios' ? 30 : 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -3 },
    shadowOpacity: 0.1,
    shadowRadius: 3,
    elevation: 10,
  },
  totalContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 15,
  },
  totalLabel: {
    fontSize: 16,
    color: '#666',
  },
  totalAmount: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
  },
  checkoutButton: {
    backgroundColor: '#0066cc',
    padding: 15,
    borderRadius: 5,
    alignItems: 'center',
  },
  checkoutButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
});

export default CartScreen;