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
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { getCart } from '../services/api';

const CartScreen = ({ navigation }) => {
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [cartItems, setCartItems] = useState([]);
  const [cartTotal, setCartTotal] = useState(0);
  const [error, setError] = useState('');
  const [userToken, setUserToken] = useState('');

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
      
      // Adapt response format if needed (for Laravel standard responses)
      let processedResponse = { ...cartResponse };
      
      // Check if we have a Laravel style response with data property
      if (cartResponse.data && !cartResponse.success) {
        console.log('Detected Laravel response format for cart, adapting...');
        processedResponse.success = true;
        
        // Handle different possible Laravel response structures
        if (cartResponse.data.items) {
          // If data contains an items property
          processedResponse.items = cartResponse.data.items;
          processedResponse.total = cartResponse.data.total || 0;
        } else if (cartResponse.data.cart_items) {
          // Alternative item property name
          processedResponse.items = cartResponse.data.cart_items;
          processedResponse.total = cartResponse.data.total_amount || cartResponse.data.total || 0;
        } else if (Array.isArray(cartResponse.data)) {
          // If data is directly an array of cart items
          processedResponse.items = cartResponse.data;
          // Calculate total from items if not provided
          processedResponse.total = cartResponse.data.reduce((sum, item) => 
            sum + ((item.price || 0) * (item.quantity || 1)), 0);
        } else if (cartResponse.data.data && Array.isArray(cartResponse.data.data)) {
          // If data contains a nested data property (common in Laravel resources)
          processedResponse.items = cartResponse.data.data;
          processedResponse.total = cartResponse.data.meta?.total || 0;
        }
      }

      if (!processedResponse.success) {
        throw new Error(processedResponse.error || cartResponse.message || 'Failed to load cart');
      }

      // Make sure we have an array of cart items
      const cartItemsArray = processedResponse.items || [];
      console.log(`Found ${cartItemsArray.length} items in cart`);
      
      setCartItems(cartItemsArray);
      setCartTotal(processedResponse.total || 0);
      console.log(`Cart total: ${processedResponse.total || 0}`);
      
    } catch (error) {
      console.error('Cart loading error:', error);
      setError('Failed to load cart. Pull down to refresh.');
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
            alert('Checkout functionality will be implemented soon!');
          }
        }
      ]
    );
  };

  const formatCurrency = (amount) => {
    return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
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
            {formatCurrency(item.product.price)}
          </Text>
          
          <View style={styles.cartItemQuantity}>
            <TouchableOpacity style={styles.quantityButton}>
              <Text style={styles.quantityButtonText}>-</Text>
            </TouchableOpacity>
            
            <Text style={styles.quantityText}>{item.quantity}</Text>
            
            <TouchableOpacity style={styles.quantityButton}>
              <Text style={styles.quantityButtonText}>+</Text>
            </TouchableOpacity>
          </View>
        </View>
      </View>
      
      <TouchableOpacity style={styles.removeButton}>
        <Text style={styles.removeButtonText}>×</Text>
      </TouchableOpacity>
    </View>
  );

  if (loading && !refreshing) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color="#0066cc" />
        <Text style={styles.loadingText}>Loading cart...</Text>
      </View>
    );
  }

  return (
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
        keyExtractor={(item) => `${item.product.id}-${item.variant ? item.variant.id : 'no-variant'}`}
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
    paddingBottom: 100, // Extra padding for the checkout button
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