import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { getOrderHistory } from '../services/api';
import FranchiseeLayout, { sessionEventEmitter } from '../components/FranchiseeLayout';
import FontAwesome from 'react-native-vector-icons/FontAwesome';

const OrderHistoryScreen = ({ navigation }) => {
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [orders, setOrders] = useState([]);
  const [error, setError] = useState('');
  const [userToken, setUserToken] = useState('');

  useEffect(() => {
    loadOrderHistory();
  }, []);

  const loadOrderHistory = async () => {
    try {
      setLoading(true);
      setError('');
      
      // Signal user activity to extend session
      sessionEventEmitter.emit('userActivity');

      const orderHistoryResponse = await getOrderHistory({status: 'completed,delivered,rejected'});
      console.log('Order history response (full):', JSON.stringify(orderHistoryResponse));
      
      // Check if we received a raw response (HTML or non-JSON)
      if (orderHistoryResponse.rawResponse) {
        console.warn('HTML or non-JSON response detected for order history');
        
        // Set a more detailed error message
        const contentType = orderHistoryResponse.contentType || 'unknown';
        let errorMessage = `Received non-JSON response (${contentType})`;
        
        // If we have an HTML title, include it in the error message
        if (orderHistoryResponse.htmlTitle) {
          errorMessage += `: ${orderHistoryResponse.htmlTitle}`;
        }
        
        setError(`${errorMessage}. Pull down to refresh or try again later.`);
        setOrders([]);
        return;
      }
      
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

  const renderOrderItem = ({ item }) => {
    // Extract number of items properly handling different data formats
    const itemsCount = item.items_count || 
                      (item.items ? item.items.length : 0) || 
                      (item.order_items ? item.order_items.length : 0) || 
                      '?';
    
    // Handle total amount with different possible field names
    const totalAmount = item.total_amount || item.total || 0;
    
    // Format date display based on order status
    const getDateDisplay = () => {
      const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('en-US', { 
          year: 'numeric', month: 'short', day: 'numeric' 
        });
      };

      if (item.status === 'delivered' && item.delivered_at) {
        return (
          <View style={styles.dateContainer}>
            <FontAwesome name="check-circle" size={14} color="#27ae60" style={{ marginRight: 5 }} />
            <Text style={styles.orderDate}>
              Delivered on {formatDate(item.delivered_at)}
            </Text>
          </View>
        );
      } else if (item.status === 'rejected' && item.rejected_at) {
        return (
          <View style={styles.dateContainer}>
            <FontAwesome name="times-circle" size={14} color="#e74c3c" style={{ marginRight: 5 }} />
            <Text style={styles.orderDate}>
              Rejected on {formatDate(item.rejected_at)}
            </Text>
          </View>
        );
      } else {
        // Default case for other statuses
        return (
          <Text style={styles.orderDate}>
            {formatDate(item.created_at)}
          </Text>
        );
      }
    };
    
    return (
      <TouchableOpacity
        style={styles.orderCard}
        onPress={() => navigation.navigate('OrderDetails', { orderId: item.id })}
      >
        <View style={styles.orderHeader}>
          <Text style={styles.orderNumber}>Order #{item.order_number || item.id}</Text>
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
          {getDateDisplay()}
          <Text style={styles.orderAmount}>
            {formatCurrency(totalAmount)}
          </Text>
        </View>
        
        <View style={styles.orderFooter}>
          <View style={styles.itemsCountContainer}>
            <FontAwesome name="shopping-cart" size={14} color="#666" style={{ marginRight: 5 }} />
            <Text style={styles.itemsCount}>
              {itemsCount} items
            </Text>
          </View>
          
          <View style={styles.viewDetailsContainer}>
            <Text style={styles.viewDetailsText}>View Details</Text>
            <FontAwesome name="chevron-right" size={12} color="#0066cc" style={{ marginLeft: 4 }} />
          </View>
        </View>
      </TouchableOpacity>
    );
  };

  if (loading && !refreshing) {
    return (
      <FranchiseeLayout title="Order History">
        <View style={styles.centered}>
          <ActivityIndicator size="large" color="#0066cc" />
          <Text style={styles.loadingText}>Loading order history...</Text>
        </View>
      </FranchiseeLayout>
    );
  }

  return (
    <FranchiseeLayout title="Order History">
      {error ? (
        <View style={styles.errorContainer}>
          <Text style={styles.errorText}>{error}</Text>
          {error.includes('non-JSON') && (
            <TouchableOpacity 
              style={styles.retryButton}
              onPress={onRefresh}
            >
              <Text style={styles.retryButtonText}>Retry</Text>
            </TouchableOpacity>
          )}
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
            <Text style={styles.emptyText}>You haven't placed any completed orders yet</Text>
            <TouchableOpacity 
              style={styles.browseCatalogButton}
              onPress={() => navigation.navigate('Catalog')}
            >
              <Text style={styles.browseCatalogButtonText}>Browse Catalog</Text>
            </TouchableOpacity>
          </View>
        }
      />
    </FranchiseeLayout>
  );
};

const styles = StyleSheet.create({
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
  errorContainer: {
    margin: 15,
    padding: 15,
    backgroundColor: '#ffebee',
    borderRadius: 5,
  },
  errorText: {
    color: '#c62828',
    marginBottom: 10,
  },
  retryButton: {
    backgroundColor: '#e53935',
    padding: 8,
    borderRadius: 5,
    alignSelf: 'flex-end',
  },
  retryButtonText: {
    color: 'white',
    fontSize: 14,
    fontWeight: '500',
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
  dateContainer: {
    flexDirection: 'row',
    alignItems: 'center',
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
  itemsCountContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  itemsCount: {
    fontSize: 14,
    color: '#666',
  },
  viewDetailsContainer: {
    flexDirection: 'row',
    alignItems: 'center',
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