import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  ActivityIndicator,
  Image,
  TouchableOpacity,
  Alert,
  RefreshControl,
  Platform
} from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import FontAwesome from 'react-native-vector-icons/FontAwesome';
import FranchiseeLayout, { cartEventEmitter, sessionEventEmitter } from '../components/FranchiseeLayout';
import { getOrderDetails, repeatOrder } from '../services/api';
import OrderProgressTracker from '../components/OrderProgressTracker';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { API_BASE_URL } from '../services/axiosInstance';
const OrderDetailsScreen = () => {
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [imageLoadErrors, setImageLoadErrors] = useState({});
  const [userData, setUserData] = useState(null);
  const route = useRoute();
  const navigation = useNavigation();
  const { orderId } = route.params;
  
  // Fetch user data from AsyncStorage for fallback contact info
  useEffect(() => {
    const fetchUserData = async () => {
      try {
        const userDataJson = await AsyncStorage.getItem('userData');
        if (userDataJson) {
          const parsedUserData = JSON.parse(userDataJson);
          console.log('Loaded user data:', parsedUserData);
          setUserData(parsedUserData);
        }
      } catch (error) {
        console.error('Error fetching user data:', error);
      }
    };
    
    fetchUserData();
  }, []);
  
  // Log userData when it changes to debug
  useEffect(() => {
    console.log('userData state updated:', userData);
  }, [userData]);

  // Fetch order details when component mounts or orderId changes
  useEffect(() => {
    console.log('📱 Fetching order details for ID:', orderId);
    fetchOrderDetails();
  }, [orderId]);

  // Image error handling function
  const handleImageError = (itemId) => {
    console.log(`Image loading error for item ${itemId}`);
    setImageLoadErrors(prev => ({
      ...prev,
      [itemId]: true
    }));
  };

  // Process image URLs with improved handling based on backend response format
  const processImageUrls = (orderData) => {

    
    if (!orderData) return orderData;
    
    console.log('🖼️ Processing order item images with base URL:', API_BASE_URL);
    
    // Create a deep copy to avoid mutating the original
    const processedOrder = { ...orderData };
    
    if (processedOrder.items && processedOrder.items.length > 0) {
      // Process each item's image URL
      processedOrder.items = processedOrder.items.map(item => {
        const processedItem = { ...item };
        
        // Extract image URL from item structure - checking all possible locations
        // This is the improved part that handles different JSON response formats
        let imageUrl = null;
        
        // Check direct image_url property
        if (processedItem.image_url) {
          imageUrl = processedItem.image_url;
          console.log(`Item ID ${processedItem.id} has direct image_url: ${imageUrl}`);
        } 
        // Check nested product.images structure (from backend)
        else if (processedItem.product && 
                processedItem.product.images && 
                Array.isArray(processedItem.product.images) && 
                processedItem.product.images.length > 0) {
          imageUrl = processedItem.product.images[0].image_url;
          console.log(`Item ID ${processedItem.id} has image from product.images[0]: ${imageUrl}`);
        }
        // Check product property directly 
        else if (processedItem.product && processedItem.product.image_url) {
          imageUrl = processedItem.product.image_url;
          console.log(`Item ID ${processedItem.id} has image from product.image_url: ${imageUrl}`);
        }
        
        // If no image was found, set a placeholder
        if (!imageUrl) {
          console.log(`Item ID ${processedItem.id} has no image URL`);
          processedItem.image_url = 'https://via.placeholder.com/100x100.png?text=No+Image';
        }  
        // If image URL is not absolute, make it absolute
        else if (!imageUrl.startsWith('http')) {
          console.log(`Item ID ${processedItem.id} has a relative image URL: ${imageUrl}`);
          
          // If path starts with /storage (Laravel public storage)
          if (imageUrl.includes('/storage/') || imageUrl.includes('storage/')) {
            let storagePath = imageUrl;
            
            // Clean up the path to ensure proper format
            if (storagePath.startsWith('/')) {
              processedItem.image_url = `${API_BASE_URL}${storagePath}`;
            } else {
              processedItem.image_url = `${API_BASE_URL}/${storagePath}`;
            }
          } 
          // If path is a direct product-images path
          else if (imageUrl.includes('product-images')) {
            // Add the /storage/ prefix that Laravel's asset() would add
            processedItem.image_url = `${API_BASE_URL}/storage/${imageUrl.replace('product-images', 'product-images/')}`;
          }
          // For other relative URLs
          else {
            processedItem.image_url = `${API_BASE_URL}/${imageUrl}`;
          }
          
          console.log(`Converted to absolute URL: ${processedItem.image_url}`);
        } 
        // For already absolute URLs, just use them directly
        else {
          processedItem.image_url = imageUrl;
          console.log(`Using absolute URL directly: ${imageUrl}`);
        }
        
        return processedItem;
      });
    }
    
    return processedOrder;
  };

  const fetchOrderDetails = async () => {
    try {
      setLoading(true);
      console.log(`📦 Fetching order details for order ID: ${orderId}`);
      const response = await getOrderDetails(orderId);
      
      if (response.success) {
        console.log('📦 Order details fetched successfully');
        
        // Check if order has items for debugging
        if (response.order?.items) {
          console.log(`📦 Order has ${response.order.items.length} items`);
          
          // Log the first item structure to help with debugging
          if (response.order.items.length > 0) {
            console.log('📦 First item structure:', JSON.stringify(response.order.items[0], null, 2));
          }
        } else {
          console.log('📦 Order has no items or items array is undefined');
        }
        
        // Process image URLs before setting state
        const processedOrder = processImageUrls(response.order);
        
        // Reset image errors when loading new order
        setImageLoadErrors({});
        
        // Set the processed order in state
        setOrder(processedOrder);
      } else {
        console.error('❌ Failed to load order details:', response.error);
        Alert.alert('Error', response.error || 'Failed to load order details');
      }
    } catch (error) {
      console.error('❌ Error loading order details:', error);
      Alert.alert('Error', 'An error occurred while loading order details');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    fetchOrderDetails();
  };

  const handleRepeatOrder = async () => {
    try {
      setLoading(true);
      
      // Signal user activity to extend session
      sessionEventEmitter.emit('userActivity');
      
      const response = await repeatOrder(orderId);
      
      if (response.success) {
        // Just trigger cart count refresh in FranchiseeLayout 
        console.log('🛒 Triggering cart refresh after repeat order');
        cartEventEmitter.emit('cartUpdated');
        
        // Check if there are any inventory warnings
        if (response.warnings && response.warnings.length > 0) {
          // Format warnings for display
          const warningMessages = response.warnings.join('\n\n');
          
          Alert.alert(
            'Items Added with Limitations',
            `Some items had inventory limitations:\n\n${warningMessages}`,
            [
              {
                text: 'View Cart',
                onPress: () => navigation.navigate('Cart')
              },
              {
                text: 'OK',
                style: 'cancel'
              }
            ]
          );
        } else if (response.cart_items && response.cart_items.length === 0) {
          // No items could be added due to inventory issues
          Alert.alert(
            'No Items Added',
            'None of the items from this order could be added to your cart due to inventory limitations.',
            [
              {
                text: 'OK',
                style: 'cancel'
              }
            ]
          );
        } else {
          // All items added successfully
          Alert.alert(
            'Success',
            'Order items have been added to your cart',
            [
              {
                text: 'View Cart',
                onPress: () => navigation.navigate('Cart')
              },
              {
                text: 'OK',
                style: 'cancel'
              }
            ]
          );
        }
      } else {
        // Handle specific error cases
        if (response.warnings && response.warnings.length > 0) {
          const warningMessages = response.warnings.join('\n\n');
          Alert.alert('Error', `Failed to repeat order:\n\n${warningMessages}`);
        } else {
          Alert.alert('Error', response.error || 'Failed to repeat order');
        }
      }
    } catch (error) {
      console.error('Error repeating order:', error);
      
      // Check if it's an authentication error
      if (error.message && error.message.includes('Authentication error')) {
        // The FranchiseeLayout component will handle the session expiration
        console.log('Authentication error detected in handleRepeatOrder');
      } else {
        Alert.alert('Error', 'An error occurred while repeating the order');
      }
    } finally {
      setLoading(false);
    }
  };

  // Render manager name with fallbacks
  const renderManagerName = () => {
    // First try userData from AsyncStorage (prioritize the user's own name)
    if (userData) {
      return userData.name || userData.full_name || userData.first_name || userData.username || 'Current User';
    }
    
    // Then try user data from order
    if (order.user) {
      return order.user.name || order.user.full_name || order.user.username || 'Order User';
    }
    
    // Only use manager_name if other sources aren't available
    if (order.manager_name) {
      return order.manager_name;
    }
    
    return 'Not provided';
  };
  
  const getStatusBadge = (status) => {
    // Define status badge configs with colors and icons
    const statusConfigs = {
      'pending': { color: '#ffc107', textColor: '#212529', icon: 'clock-o' },
      'processing': { color: '#17a2b8', textColor: '#fff', icon: 'cogs' },
      'packed': { color: '#6c757d', textColor: '#fff', icon: 'cube' },
      'shipped': { color: '#007bff', textColor: '#fff', icon: 'truck' },
      'delivered': { color: '#28a745', textColor: '#fff', icon: 'check-circle' },
      'cancelled': { color: '#dc3545', textColor: '#fff', icon: 'times-circle' },
      'rejected': { color: '#dc3545', textColor: '#fff', icon: 'ban' },
      'default': { color: '#6c757d', textColor: '#fff', icon: 'circle' }
    };
    
    // Get config for current status or use default
    const config = statusConfigs[status] || statusConfigs.default;
    
    // Inline version of the status badge for the header - with icon and text on same line
    return (
      <View style={[styles.statusBadge, { backgroundColor: config.color }]}>
        <View style={{flexDirection: 'row', alignItems: 'center'}}>
          <FontAwesome name={config.icon} size={12} color={config.textColor} style={{ marginRight: 4 }} />
          <Text style={{ color: config.textColor, fontWeight: '600', fontSize: 14 }}>
            {status?.charAt(0).toUpperCase() + status?.slice(1) || 'Unknown'}
          </Text>
        </View>
      </View>
    );
  };

  const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'short', 
      day: 'numeric', 
      hour: '2-digit', 
      minute: '2-digit' 
    });
  };
  
  // Get delivery date from an order object - prioritize delivery_date over estimated_delivery
  const getDeliveryDate = (order) => {
    // For debugging purposes only
    console.log('OrderDetailsScreen - Order delivery properties:', {
      orderId: order.id || order.order_number,
      estimated_delivery: order.estimated_delivery,
      delivery_date: order.delivery_date,
      expected_delivery: order.expected_delivery,
      delivery_preference: order.delivery_preference
    });
    
    // Prioritize delivery_date over estimated_delivery
    if (order.delivery_date) {
      console.log(`Using 'delivery_date' value:`, order.delivery_date);
      return order.delivery_date;
    }
    
    // Fall back to other fields if delivery_date is not available
    const possibleProps = ['expected_delivery', 'estimated_delivery', 'estimated_delivery_date'];
    
    for (const prop of possibleProps) {
      if (order[prop]) {
        console.log(`Using '${prop}' value:`, order[prop]);
        return order[prop];
      }
    }
    
    return null;
  };
  
  // Standardize estimated delivery date formatting
  const formatEstimatedDelivery = (dateString) => {
    if (!dateString) return 'Not available';
    
    // Log the input date string for debugging
    console.log('Formatting date string:', dateString);
    
    // Try to parse the date string
    try {
      const date = new Date(dateString);
      
      // Check if it's a valid date
      if (isNaN(date.getTime())) {
        console.log('Invalid date, returning original string:', dateString);
        
        // If the date is not a valid date but looks like it might be formatted already (e.g., "Jan 15, 2024")
        // just return it as is
        if (typeof dateString === 'string' && 
            (dateString.includes(',') || 
             dateString.match(/[A-Za-z]+ \d+, \d{4}/))) {
          return dateString;
        }
        
        return 'Not available';
      }
      
      // Format date as Month Day, Year (e.g., Jan 1, 2024)
      const formatted = date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
      
      console.log('Formatted date:', formatted);
      return formatted;
    } catch (e) {
      console.log('Error parsing estimated delivery date:', e);
      return 'Not available';
    }
  };

  if (loading && !refreshing) {
    return (
      <FranchiseeLayout title="Order Details">
        <View style={styles.centered}>
          <ActivityIndicator size="large" color="#4CAF50" />
          <Text style={styles.loadingText}>Loading order details...</Text>
        </View>
      </FranchiseeLayout>
    );
  }

  if (!order) {
    return (
      <FranchiseeLayout title="Order Details">
        <View style={styles.centered}>
          <FontAwesome name="exclamation-circle" size={50} color="#dc3545" />
          <Text style={styles.errorText}>Order not found</Text>
          <TouchableOpacity 
            style={styles.button}
            onPress={() => navigation.goBack()}
          >
            <Text style={styles.buttonText}>Go Back</Text>
          </TouchableOpacity>
        </View>
      </FranchiseeLayout>
    );
  }

  return (
    <FranchiseeLayout title={`Order #${order?.order_number || order?.id || ''}`}>
      <ScrollView 
        style={styles.container}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
      >
        {/* Action Buttons */}
        <View style={styles.actionButtons}>
          <TouchableOpacity 
            style={[styles.actionButton, { backgroundColor: '#007bff' }]}
            onPress={handleRepeatOrder}
          >
            <View style={{ flexDirection: 'row', alignItems: 'center' }}>
              <FontAwesome name="refresh" size={16} color="#fff" style={{ marginRight: 5 }} />
              <Text style={styles.actionButtonText}>Repeat Order</Text>
            </View>
          </TouchableOpacity>
        </View>

        {/* Order Summary */}
        <View style={styles.card}>
          {/* Section header with title and status badge */}
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Order Summary</Text>
            {getStatusBadge(order.status)}
          </View>
          <View style={styles.summaryContainer}>
            <View style={styles.summaryColumn}>
              <View style={styles.summaryItem}>
                <View style={styles.summaryLabel}>
                  <FontAwesome name="cube" size={14} color="#007bff" style={{ marginRight: 5 }} />
                  <Text style={styles.summaryLabelText}>Total Items</Text>
                </View>
                <Text style={styles.summaryValue}>{order.items_count || order.items?.length || 0}</Text>
              </View>
              
              <View style={styles.summaryItem}>
                <View style={styles.summaryLabel}>
                  <FontAwesome name="money" size={14} color="#28a745" style={{ marginRight: 5 }} />
                  <Text style={styles.summaryLabelText}>Total Amount</Text>
                </View>
                <Text style={styles.summaryValue}>${Number(order.total_amount || 0).toFixed(2)}</Text>
              </View>
            </View>
            
            <View style={styles.divider} />
            
            <View style={styles.summaryColumn}>
              {/* Show different date information based on order status */}
              {order.status === 'delivered' && (
                <View style={styles.summaryItem}>
                  <View style={styles.summaryLabel}>
                    <FontAwesome name="check-circle" size={14} color="#28a745" style={{ marginRight: 5 }} />
                    <Text style={styles.summaryLabelText}>Delivered On</Text>
                  </View>
                  <Text style={styles.summaryValue}>
                    {order.delivered_at ? formatEstimatedDelivery(order.delivered_at) : 
                     (order.updated_at ? formatEstimatedDelivery(order.updated_at) : 'Date not recorded')}
                  </Text>
                </View>
              )}
              
              {order.status === 'rejected' && (
                <View style={styles.summaryItem}>
                  <View style={styles.summaryLabel}>
                    <FontAwesome name="times-circle" size={14} color="#dc3545" style={{ marginRight: 5 }} />
                    <Text style={styles.summaryLabelText}>Rejected On</Text>
                  </View>
                  <Text style={styles.summaryValue}>
                    {order.rejected_at ? formatEstimatedDelivery(order.rejected_at) : 
                     (order.updated_at ? formatEstimatedDelivery(order.updated_at) : 'Date not recorded')}
                  </Text>
                </View>
              )}
              
              {/* Show estimated delivery date for orders that are pending, processing, packed, or shipped */}
              {['pending', 'processing', 'packed', 'shipped'].includes(order.status) && (
                <View style={styles.summaryItem}>
                  <View style={styles.summaryLabel}>
                    <FontAwesome name="calendar" size={14} color="#dc3545" style={{ marginRight: 5 }} />
                    <Text style={styles.summaryLabelText}>Est. Delivery</Text>
                  </View>
                  <Text style={styles.summaryValue}>{getDeliveryDate(order) ? formatEstimatedDelivery(getDeliveryDate(order)) : 'Not available'}</Text>
                </View>
              )}
              
              <View style={styles.summaryItem}>
                <View style={styles.summaryLabel}>
                  <FontAwesome name="map-marker" size={14} color="#007bff" style={{ marginRight: 5 }} />
                  <Text style={styles.summaryLabelText}>Shipping Address</Text>
                </View>
                <Text style={styles.summaryValue} numberOfLines={1}>
                  {order.shipping_address || 'Not available'}
                </Text>
              </View>
            </View>
          </View>
        </View>

        {/* Order Items */}
        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Order Items</Text>
          {order?.items && order.items.length > 0 ? (
            order.items.map((item, index) => (
              <View key={`${order.id}-${item.id}-${index}`} style={styles.orderItem}>
                <View style={styles.itemImageContainer}>
                  {/* Enhanced Image component with better fallback and error handling */}
                  <Image
                    source={
                      item.image_url && !imageLoadErrors[item.id]
                        ? { uri: item.image_url }
                        : { uri: 'https://via.placeholder.com/100x100.png?text=No+Image' }
                    }
                    style={styles.itemImage}
                    onError={() => handleImageError(item.id)}
                    onLoad={() => console.log(`✅ Image loaded successfully for item ${item.id}`)}
                    resizeMode="cover"
                  />
                </View>
                <View style={styles.itemDetails}>
                  <Text style={styles.itemName}>
                    {item.product_name || 
                     (item.product && item.product.name) || 
                     'Product Name'}
                  </Text>
                  {item.variant_name || (item.variant && item.variant.name) ? (
                    <Text style={styles.itemVariant}>
                      Variant: {item.variant_name || (item.variant && item.variant.name)}
                    </Text>
                  ) : null}
                  <Text style={styles.itemQuantity}>Quantity: {item.quantity}</Text>
                  <Text style={styles.itemPrice}>
                    ${Number(item.price || 0).toFixed(2)} × {item.quantity} = ${Number((item.price || 0) * (item.quantity || 0)).toFixed(2)}
                  </Text>
                </View>
              </View>
            ))
          ) : (
            <Text style={styles.emptyText}>No items found in this order</Text>
          )}
        </View>

        {/* Customer Notes */}
        {order.notes && (
          <View style={styles.card}>
            <Text style={styles.sectionTitle}>Customer Notes</Text>
            <View style={styles.notesSection}>
              <FontAwesome name="sticky-note" size={14} color="#ffc107" style={{ marginRight: 5 }} />
              <Text style={styles.notesText}>{order.notes}</Text>
            </View>
          </View>
        )}

        {/* Shipping Information */}
        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Shipping Information</Text>
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Shipping Address:</Text>
            <Text style={styles.infoValue}>{order.shipping_address || 'Not provided'}</Text>
          </View>
          {order.shipping_city && (
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>City:</Text>
              <Text style={styles.infoValue}>{order.shipping_city}</Text>
            </View>
          )}
          {order.shipping_state && (
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>State:</Text>
              <Text style={styles.infoValue}>{order.shipping_state}</Text>
            </View>
          )}
          {order.shipping_zip && (
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>ZIP Code:</Text>
              <Text style={styles.infoValue}>{order.shipping_zip}</Text>
            </View>
          )}
          {order.shipping_cost && (
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Shipping Cost:</Text>
              <Text style={styles.infoValue}>${Number(order.shipping_cost).toFixed(2)}</Text>
            </View>
          )}
          {order.delivery_preference && (
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Delivery Preference:</Text>
              <Text style={styles.infoValue}>{order.delivery_preference.charAt(0).toUpperCase() + order.delivery_preference.slice(1)}</Text>
            </View>
          )}
        </View>

        {/* Contact Information */}
        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Contact Information</Text>
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>User Name:</Text>
            <Text style={styles.infoValue}>
              {renderManagerName()}
            </Text>
          </View>
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Contact Phone:</Text>
            <Text style={styles.infoValue}>
              {order.contact_phone || (order.user && order.user.phone) || (userData && userData.phone) || 'Not provided'}
            </Text>
          </View>
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Email:</Text>
            <Text style={styles.infoValue}>
              {order.email || (order.user && order.user.email) || (userData && userData.email) || 'Not provided'}
            </Text>
          </View>
        </View>

        {/* Additional Information */}
        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Additional Information</Text>
          
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Order Date:</Text>
            <Text style={styles.infoValue}>{formatDate(order.created_at)}</Text>
          </View>
          
          {order.approved_at && (
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Approved Date:</Text>
              <Text style={styles.infoValue}>{formatDate(order.approved_at)}</Text>
            </View>
          )}
          
          {order.updated_at && (
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Last Updated:</Text>
              <Text style={styles.infoValue}>{formatDate(order.updated_at)}</Text>
            </View>
          )}
          
          {order.purchase_order && (
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Purchase Order:</Text>
              <Text style={styles.infoValue}>{order.purchase_order}</Text>
            </View>
          )}
          
          {order.invoice_number && (
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Invoice Number:</Text>
              <Text style={styles.infoValue}>{order.invoice_number}</Text>
            </View>
          )}
        </View>
      </ScrollView>
    </FranchiseeLayout>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    paddingHorizontal: 10,
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
    marginTop: 10,
    fontSize: 18,
    color: '#dc3545',
    marginBottom: 20,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 15,
    paddingBottom: 5,
    borderBottomWidth: 1,
    borderBottomColor: '#f0f0f0',
  },
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 3,
    borderRadius: 12,
    height: 24,  // Slight adjustment to match the text better
  },
  card: {
    backgroundColor: '#fff',
    borderRadius: 10,
    marginBottom: 16,
    padding: 15,
    elevation: 3,
    borderWidth: 1,
    borderColor: '#eee',
    overflow: 'hidden',
  },
  orderHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 15,
  },
  orderNumber: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#212529',
  },
  orderDate: {
    fontSize: 13,
    color: '#6c757d',
    marginTop: 2,
  },
  headerRight: {
    alignItems: 'flex-end',
  },
  statusBadge: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 15,
    marginBottom: 8,
    minWidth: 100,
    alignItems: 'center',
  },
  actionButtons: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginTop: 15,
    marginBottom: 15,
  },
  actionButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 8,
    paddingHorizontal: 16,
    borderRadius: 5,
    marginHorizontal: 5,
  },
  actionButtonText: {
    color: '#fff',
    fontWeight: '500',
    fontSize: 14,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 12,
    color: '#343a40',
  },
  summaryContainer: {
    flexDirection: 'row',
    backgroundColor: '#f8f9fa',
    borderRadius: 8,
    padding: 12,
  },
  summaryColumn: {
    flex: 1,
  },
  divider: {
    width: 1,
    backgroundColor: '#dee2e6',
    marginHorizontal: 10,
  },
  summaryItem: {
    marginBottom: 10,
  },
  summaryLabel: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 3,
  },
  summaryLabelText: {
    fontSize: 13,
    color: '#6c757d',
  },
  summaryValue: {
    fontSize: 15,
    fontWeight: '600',
    color: '#212529',
  },
  orderItem: {
    flexDirection: 'row',
    backgroundColor: '#f8f9fa',
    borderRadius: 8,
    padding: 10,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: '#eee',
  },
  itemImageContainer: {
    marginRight: 12,
    width: 70,
    height: 70,
  },
  itemImage: {
    width: '100%',
    height: '100%',
    borderRadius: 6,
    backgroundColor: '#eee',
  },
  itemDetails: {
    flex: 1,
    justifyContent: 'center',
  },
  itemName: {
    fontSize: 14,
    fontWeight: '500',
    marginBottom: 4,
    color: '#212529',
  },
  itemVariant: {
    fontSize: 12,
    color: '#6c757d',
    marginBottom: 4,
    fontStyle: 'italic',
  },
  itemQuantity: {
    fontSize: 13,
    color: '#6c757d',
    marginBottom: 2,
  },
  itemPrice: {
    fontSize: 13,
    color: '#28a745',
    fontWeight: '500',
  },
  emptyText: {
    fontSize: 14,
    color: '#6c757d',
    textAlign: 'center',
    padding: 15,
    fontStyle: 'italic',
  },
  notesSection: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: '#fff9e6',
    padding: 10,
    borderRadius: 8,
  },
  notesText: {
    fontSize: 13,
    color: '#6c757d',
    flex: 1,
  },
  infoRow: {
    flexDirection: 'row',
    marginBottom: 8,
    paddingBottom: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#f0f0f0',
  },
  infoLabel: {
    fontSize: 14,
    fontWeight: '500',
    color: '#495057',
    width: 130,
  },
  infoValue: {
    fontSize: 14,
    color: '#212529',
    flex: 1,
  },
  button: {
    backgroundColor: '#4CAF50',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 5,
    marginTop: 20,
  },
  buttonText: {
    color: '#fff',
    fontWeight: '500',
  },
});

export default OrderDetailsScreen;