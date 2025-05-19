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
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { getCart, updateCartItem, removeCartItem, logout } from '../services/api';
import FranchiseeLayout, { cartEventEmitter } from '../components/FranchiseeLayout';

const CartScreen = ({ navigation }) => {
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [cartItems, setCartItems] = useState([]);
  const [cartTotal, setCartTotal] = useState(0);
  const [error, setError] = useState('');
  const [userToken, setUserToken] = useState('');
  const [processingItemId, setProcessingItemId] = useState(null); // Track which item is being processed

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
      
      // Set cart items directly from the response structure
      if (cartResponse.success) {
        // Directly use cart_items from response if available
        if (cartResponse.cart_items && Array.isArray(cartResponse.cart_items)) {
          console.log(`Found ${cartResponse.cart_items.length} items in cart`);
          setCartItems(cartResponse.cart_items);
          setCartTotal(cartResponse.total || 0);
          console.log(`Cart total: ${cartResponse.total || 0}`);
          
          // Emit cart update event with item count
          console.log(`🛒 Emitting cartUpdated event from loadCart with count: ${cartResponse.cart_items.length}`);
          cartEventEmitter.emit('cartUpdated', cartResponse.cart_items.length);
        } 
        // Fallback to other possible structures if cart_items isn't found
        else if (cartResponse.data) {
          // Handle Laravel style response with data property
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
            // Calculate total from items if not provided
            setCartTotal(data.reduce((sum, item) => 
              sum + ((item.price || 0) * (item.quantity || 1)), 0));
            cartCount = data.length;
          } else if (data.data && Array.isArray(data.data)) {
            setCartItems(data.data);
            setCartTotal(data.meta?.total || 0);
            cartCount = data.data.length;
          }
          
          // Emit cart update event with item count
          cartEventEmitter.emit('cartUpdated', cartCount);
        } else if (cartResponse.items_count !== undefined) {
          // If we have a direct count but no items array
          cartEventEmitter.emit('cartUpdated', cartResponse.items_count);
          throw new Error('Unexpected response format - missing items array');
        } else {
          throw new Error('Unexpected response format');
        }
      } else {
        throw new Error(cartResponse.error || cartResponse.message || 'Failed to load cart');
      }
    } catch (error) {
      console.error('Cart loading error:', error);
      
      // If it's an authentication error, don't show any error message
      // as it will be handled by the FranchiseeLayout
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
            // Navigate to checkout screen or process checkout
            navigation.navigate('Checkout');
          }
        }
      ]
    );
  };

  const formatCurrency = (amount) => {
    // First check if amount is defined
    if (amount === undefined || amount === null) {
      return '$0.00';
    }
    
    // Convert string to number if needed
    const numericAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
    
    // Check if conversion resulted in a valid number
    if (isNaN(numericAmount)) {
      console.warn('Invalid price value:', amount);
      return '$0.00';
    }
    
    // Format with 2 decimal places and add commas for thousands
    return '$' + numericAmount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
  };

  // Handle quantity change with optimistic UI updates
  const handleQuantityChange = async (itemId, newQuantity) => {
    if (processingItemId === itemId) {
      return; // Prevent multiple simultaneous operations on the same item
    }

    try {
      setProcessingItemId(itemId);

      // Save original state for rollback if needed
      const originalItems = [...cartItems];
      const originalTotal = cartTotal;

      if (newQuantity < 1) {
        // If quantity is less than 1, ask if user wants to remove item
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

      // Find the item in the current cart items array
      const updatedItems = cartItems.map(item => {
        if (item.id === itemId) {
          // Create a new item with updated quantity (to maintain immutability)
          const updatedItem = { ...item, quantity: newQuantity };
          // Also update the item's total if we have a price
          if (item.price) {
            updatedItem.total = item.price * newQuantity;
          }
          return updatedItem;
        }
        return item;
      });

      // Optimistically update the UI
      setCartItems(updatedItems);
      
      // Calculate new cart total
      const newTotal = updatedItems.reduce(
        (sum, item) => sum + ((item.price || 0) * (item.quantity || 1)), 
        0
      );
      setCartTotal(newTotal);
      
      // Make the API call in the background
      const response = await updateCartItem(itemId, newQuantity);
      
      if (response && response.success) {
        // Check if the response includes cart count information for immediate update
        if (response.items_count !== undefined) {
          console.log(`📊 Emitting cartUpdated event with count: ${response.items_count}`);
          cartEventEmitter.emit('cartUpdated', response.items_count);
        } else if (response.cart?.items_count !== undefined) {
          console.log(`📊 Emitting cartUpdated event with count: ${response.cart.items_count}`);
          cartEventEmitter.emit('cartUpdated', response.cart.items_count);
        } else if (response.cart_items && Array.isArray(response.cart_items)) {
          console.log(`📊 Emitting cartUpdated event with count: ${response.cart_items.length}`);
          cartEventEmitter.emit('cartUpdated', response.cart_items.length);
        }
        
        // Silently fetch the latest data in the background to ensure everything is in sync
        // But don't update the UI to avoid flickering
        const cartResponse = await getCart(userToken);
        if (cartResponse.success) {
          // Handle different response formats
          if (cartResponse.cart_items && Array.isArray(cartResponse.cart_items)) {
            // Only update if there's a significant difference to avoid unnecessary re-renders
            const serverItemCount = cartResponse.cart_items.length;
            const localItemCount = cartItems.length;
            
            if (serverItemCount !== localItemCount || JSON.stringify(cartResponse.cart_items.map(i => i.id).sort()) !== 
                JSON.stringify(cartItems.map(i => i.id).sort())) {
              console.log('🔄 Silent background sync: Server data differs from local, updating UI');
              setCartItems(cartResponse.cart_items);
              setCartTotal(cartResponse.total || 0);
            }
          }
        }
      } else {
        // If there was an error, revert to the previous state
        console.log('⚠️ API error, reverting to previous cart state');
        
        // Restore original state from before optimistic update
        setCartItems(originalItems);
        setCartTotal(originalTotal);
        
        // Also get fresh state from server (but don't cause UI flicker)
        try {
          const cartResponse = await getCart(userToken);
          if (cartResponse.success && cartResponse.cart_items) {
            // Silently update state to match server
            setCartItems(cartResponse.cart_items);
            setCartTotal(cartResponse.total || 0);
          }
        } catch (syncError) {
          console.error('Error syncing with server after failed update:', syncError);
        }
        
        throw new Error((response && response.message) || 'Failed to update quantity');
      }
    } catch (error) {
      console.error('Error updating quantity:', error);
      
      // Extract a more user-friendly error message
      let errorMessage = 'Failed to update quantity. Please try again.';
      
      // If we have a specific error message from the API, use it
      if (error.message && !error.message.includes('Failed to update')) {
        // Make API error messages more user-friendly
        if (error.message.includes('items field is required') || 
            error.message.includes('items.0.id field is required')) {
          errorMessage = 'There was an issue updating your cart. Please try again.';
          // Log the detailed error for debugging
          console.log('🔍 Cart API payload format error:', error.message);
        } else {
          errorMessage = error.message;
        }
      }
      
      Alert.alert(
        'Cart Update Error',
        errorMessage,
        [{ text: 'OK' }]
      );
    } finally {
      setProcessingItemId(null);
    }
  };

  // Handle removing item from cart with optimistic UI updates
  const removeItem = async (itemId) => {
    if (processingItemId === itemId) {
      console.log(`⚠️ Already processing item ${itemId}, ignoring duplicate request`);
      return; // Prevent multiple simultaneous operations
    }

    console.log(`🗑️ Starting removeItem process for item ${itemId}`);
    setProcessingItemId(itemId);
    
    // Find the item to be removed to use in optimistic updates
    const itemToRemove = cartItems.find(item => item.id === itemId);
    if (!itemToRemove) {
      console.error(`❌ Cannot find item ${itemId} in cart items`);
      setProcessingItemId(null);
      return;
    }
    
    // Store original cart state for rollback if needed
    const originalItems = [...cartItems];
    const originalTotal = cartTotal;
    
    try {
      // Optimistically update UI by filtering out the item
      const updatedItems = cartItems.filter(item => item.id !== itemId);
      setCartItems(updatedItems);
      
      // Update cart total by subtracting the item's contribution
      const itemPrice = itemToRemove.price || 0;
      const itemQuantity = itemToRemove.quantity || 1;
      const itemTotal = itemPrice * itemQuantity;
      const newTotal = cartTotal - itemTotal;
      setCartTotal(newTotal >= 0 ? newTotal : 0); // Ensure we don't go negative
      
      // Update cart count in header
      const newCount = updatedItems.length;
      cartEventEmitter.emit('cartUpdated', newCount);
      
      // Make API call in background
      console.log(`📞 Calling removeCartItem API for item ${itemId}`);
      const response = await removeCartItem(itemId);
      console.log(`📊 Remove response for item ${itemId}:`, response);
      
      // Handle success case - the item was successfully removed
      if (response && response.success) {
        console.log(`✅ Successfully removed item ${itemId}`);
        
        // If the response includes cart count, use it to update the cart badge immediately
        if (response.items_count !== undefined) {
          console.log(`📊 Emitting cartUpdated event with count: ${response.items_count}`);
          cartEventEmitter.emit('cartUpdated', response.items_count);
        } else if (response.cart?.items_count !== undefined) {
          console.log(`📊 Emitting cartUpdated event with count: ${response.cart.items_count}`);
          cartEventEmitter.emit('cartUpdated', response.cart.items_count);
        } else if (response.cart_items && Array.isArray(response.cart_items)) {
          console.log(`📊 Emitting cartUpdated event with count: ${response.cart_items.length}`);
          cartEventEmitter.emit('cartUpdated', response.cart_items.length);
        }
        
        // Silently sync with server without full UI refresh
        const cartResponse = await getCart(userToken);
        if (cartResponse.success) {
          // Only update if something unexpected changed
          if (cartResponse.cart_items && Array.isArray(cartResponse.cart_items)) {
            const serverItemCount = cartResponse.cart_items.length;
            const localItemCount = updatedItems.length;
            
            if (serverItemCount !== localItemCount) {
              console.log('🔄 Silent background sync after removal: Server data differs from local');
              setCartItems(cartResponse.cart_items);
              setCartTotal(cartResponse.total || 0);
            }
          }
        }
        
        return; // Exit early on success
      }
      
      // Handle error case with response
      if (response && !response.success) {
        const errorMessage = response.message || 'Failed to remove item';
        console.log(`⚠️ API returned error for item ${itemId}: ${errorMessage}`);
        
        // Revert optimistic update
        console.log('↩️ Reverting to original cart state');
        setCartItems(originalItems);
        setCartTotal(originalTotal);
        
        Alert.alert(
          'Error',
          errorMessage,
          [{ text: 'OK' }]
        );
        return; // Exit after showing alert
      }
      
      // If we get here, we have an unexpected response format
      console.log(`❓ Unexpected response format for item ${itemId}:`, response);
      throw new Error('Unexpected response format');
      
    } catch (error) {
      console.error(`❌ Error removing item ${itemId}:`, error);
      
      // Revert optimistic update
      console.log('↩️ Reverting to original cart state due to error');
      setCartItems(originalItems);
      setCartTotal(originalTotal);
      
      // Check if it's an authentication error
      if (error.message && error.message.includes('Authentication error')) {
        // Let FranchiseeLayout handle the session expiration
        // No need for an alert as the Layout component will show one
        // and redirect to login screen
        console.log(`🔒 Authentication error detected for item ${itemId}, letting FranchiseeLayout handle it`);
      } else {
        // For non-authentication errors, show the regular error alert
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

  const renderCartItem = ({ item }) => (
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
        
        <View style={styles.cartItemFooter}>
          <Text style={styles.cartItemPrice}>
            {formatCurrency(item.price || item.product.base_price)}
          </Text>
          
          <View style={styles.cartItemQuantity}>
            <TouchableOpacity 
              style={[
                styles.quantityButton,
                processingItemId === item.id && styles.disabledButton
              ]}
              disabled={processingItemId === item.id}
              onPress={() => handleQuantityChange(item.id, item.quantity - 1)}
            >
              <Text style={styles.quantityButtonText}>-</Text>
            </TouchableOpacity>
            
            <Text style={styles.quantityText}>
              {processingItemId === item.id ? '...' : item.quantity}
            </Text>
            
            <TouchableOpacity 
              style={[
                styles.quantityButton,
                processingItemId === item.id && styles.disabledButton
              ]}
              disabled={processingItemId === item.id}
              onPress={() => handleQuantityChange(item.id, item.quantity + 1)}
            >
              <Text style={styles.quantityButtonText}>+</Text>
            </TouchableOpacity>
          </View>
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
    paddingBottom: 130, // Extra padding for the checkout button and safe area
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
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 'auto',
  },
  cartItemPrice: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  cartItemQuantity: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  quantityButton: {
    width: 24,
    height: 24,
    backgroundColor: '#eee',
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
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
    marginHorizontal: 8,
    minWidth: 20,
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
    paddingBottom: Platform.OS === 'ios' ? 30 : 15, // Add extra padding for iOS devices to account for the home indicator
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