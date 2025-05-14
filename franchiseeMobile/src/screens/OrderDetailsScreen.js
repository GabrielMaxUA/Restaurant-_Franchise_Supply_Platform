import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  SafeAreaView,
  Image,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { getOrderDetails } from '../services/api';

const OrderDetailsScreen = ({ route, navigation }) => {
  const { orderId } = route.params;
  const [loading, setLoading] = useState(true);
  const [order, setOrder] = useState(null);
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
    if (userToken && orderId) {
      loadOrderDetails();
    }
  }, [userToken, orderId]);

  const loadOrderDetails = async () => {
    if (!userToken || !orderId) return;

    try {
      setLoading(true);
      setError('');

      const orderDetailsResponse = await getOrderDetails(userToken, orderId);
      console.log('Order details response (full):', JSON.stringify(orderDetailsResponse));
      
      // Adapt response format if needed (for Laravel standard responses)
      let processedResponse = { ...orderDetailsResponse };
      
      // Check if we have a Laravel style response with data property
      if (orderDetailsResponse.data && !orderDetailsResponse.success) {
        console.log('Detected Laravel response format for order details, adapting...');
        processedResponse.success = true;
        
        // Handle different possible Laravel response structures
        if (orderDetailsResponse.data.order) {
          // If data contains an order property
          processedResponse.order = orderDetailsResponse.data.order;
        } else if (typeof orderDetailsResponse.data === 'object' && !Array.isArray(orderDetailsResponse.data)) {
          // If data is directly the order object
          processedResponse.order = orderDetailsResponse.data;
        } else if (orderDetailsResponse.data.data && !Array.isArray(orderDetailsResponse.data.data)) {
          // If data contains a nested data property (common in Laravel resources)
          processedResponse.order = orderDetailsResponse.data.data;
        } else {
          // Fallback - if we can't identify a clear order object
          processedResponse.order = null;
          console.warn('Could not identify order object in response:', orderDetailsResponse);
        }
      }

      if (!processedResponse.success) {
        throw new Error(processedResponse.error || orderDetailsResponse.message || 'Failed to load order details');
      }

      // Make sure we have an order object
      if (processedResponse.order) {
        console.log('Order details loaded successfully:', processedResponse.order.id || 'ID not found');
        
        // Ensure order has expected properties
        const orderData = {
          ...processedResponse.order,
          items: processedResponse.order.items || 
                processedResponse.order.order_items || 
                [],
          shipping_address: processedResponse.order.shipping_address || 
                           processedResponse.order.shipping || 
                           {},
          total_amount: processedResponse.order.total_amount || 
                        processedResponse.order.total || 
                        0
        };
        
        setOrder(orderData);
      } else {
        setOrder(null);
        throw new Error('Order details not found');
      }
    } catch (error) {
      console.error('Order details loading error:', error);
      setError('Failed to load order details. Please try again.');
    } finally {
      setLoading(false);
    }
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

  const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
  };

  if (loading) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color="#0066cc" />
        <Text style={styles.loadingText}>Loading order details...</Text>
      </View>
    );
  }

  if (error) {
    return (
      <View style={styles.centered}>
        <Text style={styles.errorText}>{error}</Text>
        <TouchableOpacity
          style={styles.tryAgainButton}
          onPress={loadOrderDetails}
        >
          <Text style={styles.tryAgainButtonText}>Try Again</Text>
        </TouchableOpacity>
      </View>
    );
  }

  if (!order) {
    return (
      <View style={styles.centered}>
        <Text style={styles.errorText}>Order not found</Text>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
        >
          <Text style={styles.backButtonText}>Go Back</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView>
        {/* Order Header */}
        <View style={styles.orderHeader}>
          <View>
            <Text style={styles.orderNumber}>Order #{order.id}</Text>
            <Text style={styles.orderDate}>
              {formatDate(order.created_at)}
            </Text>
          </View>
          <View style={[
            styles.statusBadge,
            { backgroundColor: getStatusColor(order.status) }
          ]}>
            <Text style={styles.statusText}>
              {order.status.charAt(0).toUpperCase() + order.status.slice(1)}
            </Text>
          </View>
        </View>

        {/* Order Items */}
        <View style={styles.sectionContainer}>
          <Text style={styles.sectionTitle}>Order Items</Text>
          
          {order.items && order.items.map((item, index) => (
            <View key={index} style={styles.orderItem}>
              {item.product.image_url ? (
                <Image 
                  source={{ uri: item.product.image_url }} 
                  style={styles.productImage}
                  resizeMode="cover"
                />
              ) : (
                <View style={styles.productImagePlaceholder}>
                  <Text style={styles.productImagePlaceholderText}>No Image</Text>
                </View>
              )}
              
              <View style={styles.productDetails}>
                <Text style={styles.productName}>{item.product.name}</Text>
                
                {item.variant && (
                  <Text style={styles.productVariant}>
                    {item.variant.name}
                  </Text>
                )}
                
                <View style={styles.productFooter}>
                  <Text style={styles.productPrice}>
                    {formatCurrency(item.price)}
                  </Text>
                  
                  <Text style={styles.productQuantity}>
                    Qty: {item.quantity}
                  </Text>
                </View>
              </View>
            </View>
          ))}
        </View>

        {/* Shipping Address */}
        {order.shipping_address && (
          <View style={styles.sectionContainer}>
            <Text style={styles.sectionTitle}>Shipping Address</Text>
            <View style={styles.addressContainer}>
              <Text style={styles.addressName}>{order.shipping_address.recipient_name}</Text>
              <Text style={styles.addressLine}>{order.shipping_address.street1}</Text>
              {order.shipping_address.street2 && (
                <Text style={styles.addressLine}>{order.shipping_address.street2}</Text>
              )}
              <Text style={styles.addressLine}>
                {order.shipping_address.city}, {order.shipping_address.state} {order.shipping_address.zip}
              </Text>
              {order.shipping_address.phone && (
                <Text style={styles.addressLine}>{order.shipping_address.phone}</Text>
              )}
            </View>
          </View>
        )}

        {/* Order Summary */}
        <View style={styles.sectionContainer}>
          <Text style={styles.sectionTitle}>Order Summary</Text>
          
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Subtotal</Text>
            <Text style={styles.summaryValue}>{formatCurrency(order.subtotal || 0)}</Text>
          </View>
          
          {order.shipping_amount > 0 && (
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Shipping</Text>
              <Text style={styles.summaryValue}>{formatCurrency(order.shipping_amount)}</Text>
            </View>
          )}
          
          {order.tax_amount > 0 && (
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Tax</Text>
              <Text style={styles.summaryValue}>{formatCurrency(order.tax_amount)}</Text>
            </View>
          )}
          
          {order.discount_amount > 0 && (
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Discount</Text>
              <Text style={styles.summaryValue}>-{formatCurrency(order.discount_amount)}</Text>
            </View>
          )}
          
          <View style={[styles.summaryRow, styles.totalRow]}>
            <Text style={styles.totalLabel}>Total</Text>
            <Text style={styles.totalValue}>{formatCurrency(order.total_amount)}</Text>
          </View>
        </View>

        {/* Tracking Information */}
        {order.tracking_number && (
          <View style={styles.sectionContainer}>
            <Text style={styles.sectionTitle}>Tracking Information</Text>
            <View style={styles.trackingContainer}>
              <Text style={styles.trackingLabel}>Tracking Number:</Text>
              <Text style={styles.trackingNumber}>{order.tracking_number}</Text>
              
              {order.carrier && (
                <Text style={styles.trackingCarrier}>
                  Carrier: {order.carrier}
                </Text>
              )}
              
              <TouchableOpacity style={styles.trackButton}>
                <Text style={styles.trackButtonText}>Track Package</Text>
              </TouchableOpacity>
            </View>
          </View>
        )}
        
        {/* Re-order Button */}
        <View style={styles.actionContainer}>
          <TouchableOpacity style={styles.reorderButton}>
            <Text style={styles.reorderButtonText}>Reorder</Text>
          </TouchableOpacity>
          
          {order.status === 'pending' && (
            <TouchableOpacity style={styles.cancelButton}>
              <Text style={styles.cancelButtonText}>Cancel Order</Text>
            </TouchableOpacity>
          )}
        </View>
      </ScrollView>
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
  errorText: {
    color: '#c62828',
    fontSize: 16,
    marginBottom: 20,
    textAlign: 'center',
  },
  tryAgainButton: {
    backgroundColor: '#0066cc',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 5,
  },
  tryAgainButtonText: {
    color: '#fff',
    fontSize: 16,
  },
  backButton: {
    backgroundColor: '#0066cc',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 5,
  },
  backButtonText: {
    color: '#fff',
    fontSize: 16,
  },
  orderHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 15,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  orderNumber: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
  },
  orderDate: {
    fontSize: 14,
    color: '#666',
    marginTop: 5,
  },
  statusBadge: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
  },
  statusText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: 'bold',
  },
  sectionContainer: {
    margin: 15,
    backgroundColor: '#fff',
    borderRadius: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
    overflow: 'hidden',
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    padding: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  orderItem: {
    flexDirection: 'row',
    padding: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  productImage: {
    width: 60,
    height: 60,
    borderRadius: 5,
    backgroundColor: '#f0f0f0',
  },
  productImagePlaceholder: {
    width: 60,
    height: 60,
    borderRadius: 5,
    backgroundColor: '#f0f0f0',
    justifyContent: 'center',
    alignItems: 'center',
  },
  productImagePlaceholderText: {
    color: '#999',
    fontSize: 10,
  },
  productDetails: {
    flex: 1,
    marginLeft: 15,
  },
  productName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 4,
  },
  productVariant: {
    fontSize: 14,
    color: '#666',
    marginBottom: 8,
  },
  productFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  productPrice: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  productQuantity: {
    fontSize: 14,
    color: '#666',
  },
  addressContainer: {
    padding: 15,
  },
  addressName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 5,
  },
  addressLine: {
    fontSize: 14,
    color: '#666',
    marginBottom: 3,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    padding: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  summaryLabel: {
    fontSize: 14,
    color: '#666',
  },
  summaryValue: {
    fontSize: 14,
    color: '#333',
    fontWeight: '500',
  },
  totalRow: {
    borderBottomWidth: 0,
  },
  totalLabel: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  totalValue: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
  },
  trackingContainer: {
    padding: 15,
  },
  trackingLabel: {
    fontSize: 14,
    color: '#666',
    marginBottom: 5,
  },
  trackingNumber: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 10,
  },
  trackingCarrier: {
    fontSize: 14,
    color: '#666',
    marginBottom: 15,
  },
  trackButton: {
    backgroundColor: '#f0f0f0',
    padding: 10,
    borderRadius: 5,
    alignItems: 'center',
    marginTop: 5,
  },
  trackButtonText: {
    color: '#0066cc',
    fontSize: 14,
    fontWeight: '500',
  },
  actionContainer: {
    margin: 15,
    marginBottom: 30,
  },
  reorderButton: {
    backgroundColor: '#0066cc',
    padding: 15,
    borderRadius: 5,
    alignItems: 'center',
    marginBottom: 10,
  },
  reorderButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  cancelButton: {
    backgroundColor: '#fff',
    padding: 15,
    borderRadius: 5,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#e74c3c',
  },
  cancelButtonText: {
    color: '#e74c3c',
    fontSize: 16,
    fontWeight: 'bold',
  },
});

export default OrderDetailsScreen;