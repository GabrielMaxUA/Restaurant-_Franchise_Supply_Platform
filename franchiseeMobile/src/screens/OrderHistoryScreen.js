import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  SafeAreaView,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { getOrderHistory } from '../services/api';

const OrderHistoryScreen = ({ navigation }) => {
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [orders, setOrders] = useState([]);
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
      loadOrderHistory();
    }
  }, [userToken]);

  const loadOrderHistory = async () => {
    if (!userToken) return;

    try {
      setLoading(true);
      setError('');

      const orderHistoryResponse = await getOrderHistory(userToken);
      console.log('Order history response (full):', JSON.stringify(orderHistoryResponse));
      
      // Adapt response format if needed (for Laravel standard responses)
      let processedResponse = { ...orderHistoryResponse };
      
      // Check if we have a Laravel style response with data property
      if (orderHistoryResponse.data && !orderHistoryResponse.success) {
        console.log('Detected Laravel response format for order history, adapting...');
        processedResponse.success = true;
        
        // Handle different possible Laravel response structures
        if (orderHistoryResponse.data.orders) {
          // If data contains an orders property
          processedResponse.orders = orderHistoryResponse.data.orders;
        } else if (Array.isArray(orderHistoryResponse.data)) {
          // If data is directly an array of orders
          processedResponse.orders = orderHistoryResponse.data;
        } else if (orderHistoryResponse.data.data && Array.isArray(orderHistoryResponse.data.data)) {
          // If data contains a nested data property (common in Laravel resources)
          processedResponse.orders = orderHistoryResponse.data.data;
        } else if (orderHistoryResponse.data.order_history) {
          // Another possible format
          processedResponse.orders = orderHistoryResponse.data.order_history;
        } else {
          // Fallback - if we can't identify a clear orders array
          processedResponse.orders = [];
          console.warn('Could not identify orders array in response:', orderHistoryResponse);
        }
      }
      
      // Additional check for standard Laravel pagination format
      if (orderHistoryResponse.data && Array.isArray(orderHistoryResponse.data.data)) {
        processedResponse.success = true;
        processedResponse.orders = orderHistoryResponse.data.data;
      }

      if (!processedResponse.success) {
        throw new Error(processedResponse.error || orderHistoryResponse.message || 'Failed to load order history');
      }

      // Make sure we have an array of orders
      const ordersArray = processedResponse.orders || [];
      console.log(`Found ${ordersArray.length} orders in history`);
      
      setOrders(ordersArray);
    } catch (error) {
      console.error('Order history loading error:', error);
      setError('Failed to load order history. Pull down to refresh.');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadOrderHistory();
  };

  const formatCurrency = (amount) => {
    return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'pending': return '#ffcc00';
      case 'processing': return '#3498db';
      case 'packed': return '#9b59b6';
      case 'shipped': return '#2ecc71';
      case 'delivered': return '#27ae60';
      case 'rejected': return '#e74c3c';
      default: return '#95a5a6';
    }
  };

  const renderOrderItem = ({ item }) => (
    <TouchableOpacity
      style={styles.orderCard}
      onPress={() => navigation.navigate('OrderDetails', { orderId: item.id })}
    >
      <View style={styles.orderHeader}>
        <Text style={styles.orderNumber}>Order #{item.id}</Text>
        <View style={[
          styles.statusBadge,
          { backgroundColor: getStatusColor(item.status) }
        ]}>
          <Text style={styles.statusText}>
            {item.status.charAt(0).toUpperCase() + item.status.slice(1)}
          </Text>
        </View>
      </View>

      <View style={styles.orderDetails}>
        <Text style={styles.orderDate}>
          {new Date(item.created_at).toLocaleDateString()}
        </Text>
        <Text style={styles.orderAmount}>
          {formatCurrency(item.total_amount)}
        </Text>
      </View>
      
      <View style={styles.orderFooter}>
        <Text style={styles.itemsCount}>
          {item.items_count || '?'} items
        </Text>
        
        <Text style={styles.viewDetailsText}>View Details &rsaquo;</Text>
      </View>
    </TouchableOpacity>
  );

  if (loading && !refreshing) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color="#0066cc" />
        <Text style={styles.loadingText}>Loading order history...</Text>
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Your Orders</Text>
        <Text style={styles.headerSubtitle}>View your order history</Text>
      </View>
      
      {error ? (
        <View style={styles.errorContainer}>
          <Text style={styles.errorText}>{error}</Text>
        </View>
      ) : null}
      
      <FlatList
        data={orders}
        renderItem={renderOrderItem}
        keyExtractor={(item) => item.id.toString()}
        contentContainerStyle={styles.ordersList}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>You haven't placed any orders yet</Text>
            <TouchableOpacity 
              style={styles.browseCatalogButton}
              onPress={() => navigation.navigate('Catalog')}
            >
              <Text style={styles.browseCatalogButtonText}>Browse Catalog</Text>
            </TouchableOpacity>
          </View>
        }
      />
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
  ordersList: {
    padding: 15,
  },
  orderCard: {
    backgroundColor: '#fff',
    borderRadius: 10,
    marginBottom: 15,
    padding: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  orderHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  orderNumber: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 3,
    borderRadius: 15,
  },
  statusText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: 'bold',
  },
  orderDetails: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
    paddingBottom: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  orderDate: {
    fontSize: 14,
    color: '#666',
  },
  orderAmount: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  orderFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  itemsCount: {
    fontSize: 14,
    color: '#666',
  },
  viewDetailsText: {
    fontSize: 14,
    color: '#0066cc',
    fontWeight: '500',
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
});

export default OrderHistoryScreen;