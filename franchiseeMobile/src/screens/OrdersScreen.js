import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  ActivityIndicator,
  Image,
  RefreshControl,
  Platform
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import FontAwesome from 'react-native-vector-icons/FontAwesome';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import FranchiseeLayout, { sessionEventEmitter } from '../components/FranchiseeLayout';
import { getPendingOrders } from '../services/api';
import OrderProgressTracker from '../components/OrderProgressTracker';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { BASE_URL } from '../services/axiosInstance'; // Adjust the import based on your project structure
// Import FallbackIcon if it exists in your project
import FallbackIcon from '../components/icon/FallbackIcon';

const OrdersScreen = () => {
  const [orders, setOrders] = useState([]);
  const [orderCounts, setOrderCounts] = useState({
    pending: 0,
    processing: 0, // This will now include both 'processing' and 'approved' orders
    packed: 0,
    shipped: 0,
    delivered: 0,
    rejected: 0
  });
  const [status, setStatus] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [imageLoadErrors, setImageLoadErrors] = useState({});
  const navigation = useNavigation();

  // Fetch orders when component mounts or status changes
  useEffect(() => {
    fetchOrders();
  }, [status]);

  // Function to handle image errors - SAME AS DASHBOARD
  const handleImageError = (itemId) => {
    console.log(`Image loading error for item ${itemId}`);
    setImageLoadErrors(prev => ({
      ...prev,
      [itemId]: true
    }));
  };

  // Process images using EXACTLY the same approach as Dashboard
  const processOrdersImages = (ordersData) => {
    if (!ordersData || !Array.isArray(ordersData)) return [];

    console.log(`Processing images for ${ordersData.length} orders`);



    return ordersData.map(order => {
      const processedOrder = { ...order };

      // Only process if order has items
      if (processedOrder.items && processedOrder.items.length > 0) {
        console.log(`Order #${order.id || order.order_number}: Processing ${order.items.length} items`);

        processedOrder.items = processedOrder.items.map(item => {
          const processedItem = { ...item };

          // Log the original image URL
          console.log(`Item ID ${processedItem.id} - Original image URL: ${processedItem.image_url}`);

          // Check if the image URL is valid
          if (!processedItem.image_url) {
            console.log(`Item ID ${processedItem.id} has no image URL`);
            // Provide a default placeholder image
            processedItem.image_url = 'https://via.placeholder.com/150';
            return processedItem;
          }

          // If already an absolute URL, keep it
          if (processedItem.image_url.startsWith('http')) {
            console.log(`Item ID ${processedItem.id} already has absolute URL: ${processedItem.image_url}`);
            return processedItem;
          }

          // DIRECT COPY FROM DASHBOARD APPROACH
          console.log(`Item ID ${processedItem.id} has a relative image URL: ${processedItem.image_url}`);

          // If path starts with /storage (Laravel public storage)
          if (processedItem.image_url.includes('/storage/') || processedItem.image_url.includes('storage/')) {
            let storagePath = processedItem.image_url;

            // Clean up the path to ensure proper format
            if (storagePath.startsWith('/')) {
              processedItem.image_url = `${BASE_URL}${storagePath}`;
            } else {
              processedItem.image_url = `${BASE_URL}/${storagePath}`;
            }
          }
          // If path is a direct product-images path
          else if (processedItem.image_url.includes('product-images')) {
            // Add the /storage/ prefix that Laravel's asset() would add
            processedItem.image_url = `${BASE_URL}/storage/${processedItem.image_url.replace('product-images', 'product-images/')}`;
          }
          // For other relative URLs
          else {
            processedItem.image_url = `${BASE_URL}/${processedItem.image_url}`;
          }

          console.log(`Converted to absolute URL: ${processedItem.image_url}`);
          return processedItem;
        });
      }

      return processedOrder;
    });
  };

  const fetchOrders = async () => {
    try {
      setLoading(true);

      // Signal user activity to extend session
      sessionEventEmitter.emit('userActivity');

      console.log(`Fetching orders with status: ${status || 'all'}`);

      // When filtering by 'processing', we need to show only 'approved' orders
      let requestStatus = status;
      if (status === 'processing') {
        requestStatus = 'approved'; // Only fetch approved orders for processing filter
      }

      const response = await getPendingOrders(requestStatus);

      if (response.success) {
        console.log(`Received ${response.orders?.length || 0} orders`);

        // Log the response to see what data we're getting
        console.log('Orders Response Structure:', JSON.stringify(response.orders[0], null, 2));

        // Check if we have estimated_delivery in the orders
        if (response.orders && response.orders.length > 0) {
          console.log('Orders with estimated_delivery:', response.orders.map(order => ({
            id: order.id,
            has_estimated_delivery: 'estimated_delivery' in order,
            estimated_delivery_value: order.estimated_delivery,
          })));
        }

        // Check if orders have items
        let hasItems = false;
        if (response.orders && response.orders.length > 0) {
          response.orders.forEach(order => {
            if (order.items && order.items.length > 0) {
              hasItems = true;
              console.log(`Order #${order.id || order.order_number} has ${order.items.length} items`);
            }
          });
        }

        if (!hasItems) {
          console.warn('No orders have items array or items are empty');
        }

        // Process image URLs before setting state - USING DASHBOARD APPROACH
        const processedOrders = processOrdersImages(response.orders);

        // Reset image errors when loading new orders
        setImageLoadErrors({});

        // Set the processed orders in state
        setOrders(processedOrders);

        // Update order counts - show only approved orders count for processing
        if (response.order_counts) {
          const updatedCounts = { ...response.order_counts };

          // For processing count, show only approved orders count
          if (response.order_counts.approved) {
            updatedCounts.processing = response.order_counts.approved;
          } else {
            updatedCounts.processing = 0;
          }

          setOrderCounts(updatedCounts);
        }
      } else {
        console.warn('Failed to load orders:', response);
      }
    } catch (error) {
      console.error('Error loading orders:', error);

      // Check if it's an authentication error
      if (error.message && error.message.includes('Authentication error')) {
        // The FranchiseeLayout component will handle the session expiration
        console.log('Authentication error detected in fetchOrders');
      }
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    fetchOrders();
  };

  // Function to render product image - SIMPLER VERSION FROM DASHBOARD
  const renderProductImage = (item) => {
    // If there's an error loading this image, show fallback
    if (imageLoadErrors[item.id]) {
      return (
        <View style={styles.productImageContainer}>
          {FallbackIcon ? (
            <FallbackIcon name="picture" iconType="AntDesign" size={24} color="#ccc" />
          ) : (
            <Text style={{ textAlign: 'center', color: '#ccc' }}>No Image</Text>
          )}
        </View>
      );
    }

    // If no image URL, show fallback
    if (!item.image_url) {
      return (
        <View style={styles.productImageContainer}>
          {FallbackIcon ? (
            <FallbackIcon name="picture" iconType="AntDesign" size={24} color="#ccc" />
          ) : (
            <Text style={{ textAlign: 'center', color: '#ccc' }}>No Image</Text>
          )}
        </View>
      );
    }

    // Log the image URL being rendered
    console.log(`Rendering image for item ${item.id}: ${item.image_url}`);

    // Otherwise show the image with error handling
    return (
      <View style={styles.productImageContainer}>
        <Image
          source={{ uri: item.image_url }}
          style={styles.itemImage}
          onError={() => {
            console.log(`Image load error for ${item.id}: ${item.image_url}`);
            handleImageError(item.id);
          }}
          onLoad={() => console.log(`Image loaded successfully for item ${item.id}`)}
          resizeMode="cover"
        />
      </View>
    );
  };

  const renderStatusCard = (label, key, color, textColor = '#000') => (
    <TouchableOpacity
      key={key}
      style={[styles.statusCard, status === key && styles.activeCard]}
      onPress={() => setStatus(status === key ? null : key)}
    >
      <Text style={[styles.statusCount, { color }]}>{orderCounts[key] ?? 0}</Text>
      <Text style={[styles.statusLabel, { color: textColor }]}>{label}</Text>
    </TouchableOpacity>
  );

  // Updated getStatusBadge to handle approved orders as processing
  const getStatusBadge = (status) => {
    switch (status) {
      case 'pending':
        return <View style={[styles.statusBadge, { backgroundColor: '#ffc107' }]}><Text style={{ color: '#212529', fontWeight: '500' }}>Pending</Text></View>;
      case 'approved':
        // Show approved orders as "Processing" in the UI
        return <View style={[styles.statusBadge, { backgroundColor: '#17a2b8' }]}><Text style={{ color: '#fff', fontWeight: '500' }}>Processing</Text></View>;
      case 'processing':
        return <View style={[styles.statusBadge, { backgroundColor: '#17a2b8' }]}><Text style={{ color: '#fff', fontWeight: '500' }}>Processing</Text></View>;
      case 'packed':
        return <View style={[styles.statusBadge, { backgroundColor: '#6c757d' }]}><Text style={{ color: '#fff', fontWeight: '500' }}>Packed</Text></View>;
      case 'shipped':
        return <View style={[styles.statusBadge, { backgroundColor: '#007bff' }]}><Text style={{ color: '#fff', fontWeight: '500' }}>Shipped</Text></View>;
      default:
        return <View style={[styles.statusBadge, { backgroundColor: '#6c757d' }]}><Text style={{ color: '#fff', fontWeight: '500' }}>{status?.charAt(0).toUpperCase() + status?.slice(1) || 'Unknown'}</Text></View>;
    }
  };

  // Get delivery date from an order object - prioritize delivery_date over estimated_delivery
  const getDeliveryDate = (order) => {
    // For debugging purposes only
    console.log('OrdersScreen - Order delivery properties:', {
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

  const handleViewDetails = (orderId) => {
    // Signal user activity before navigation
    sessionEventEmitter.emit('userActivity');
    navigation.navigate('OrderDetails', { orderId });
  };

  const renderOrderItem = ({ item }) => {
    const itemsCount = item.items?.length || 0;

    return (
      <View style={styles.orderCard}>
        <View style={styles.orderHeader}>
          <View>
            <Text style={styles.orderNumber}>Order #{item.order_number || item.id}</Text>
            <Text style={styles.orderDate}>{new Date(item.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</Text>
          </View>
          <View style={styles.headerRight}>
            {getStatusBadge(item.status)}
            <TouchableOpacity style={styles.viewButton} onPress={() => handleViewDetails(item.id)}>
              <FontAwesome name="eye" size={14} color="#28a745" style={{ marginRight: 4 }} />
              <Text style={styles.viewButtonText}>View Details</Text>
            </TouchableOpacity>
          </View>
        </View>

        <OrderProgressTracker status={item.status === 'approved' ? 'processing' : item.status} />

        <View style={styles.orderSummarySection}>
          <Text style={styles.sectionTitle}>Order Summary</Text>
          <View style={styles.summaryContainer}>
            <View style={styles.summaryColumn}>
              <View style={styles.summaryItem}>
                <View style={styles.summaryLabel}>
                  <FontAwesome name="cube" size={14} color="#007bff" style={{ marginRight: 5 }} />
                  <Text style={styles.summaryLabelText}>Total Items</Text>
                </View>
                <Text style={styles.summaryValue}>{item.items_count || itemsCount}</Text>
              </View>

              <View style={styles.summaryItem}>
                <View style={styles.summaryLabel}>
                  <FontAwesome name="money" size={14} color="#28a745" style={{ marginRight: 5 }} />
                  <Text style={styles.summaryLabelText}>Total Amount</Text>
                </View>
                <Text style={styles.summaryValue}>${Number(item.total_amount).toFixed(2)}</Text>
              </View>
            </View>

            <View style={styles.divider} />

            <View style={styles.summaryColumn}>
              <View style={styles.summaryItem}>
                <View style={styles.summaryLabel}>
                  <FontAwesome name="calendar" size={14} color="#dc3545" style={{ marginRight: 5 }} />
                  <Text style={styles.summaryLabelText}>Est. Delivery</Text>
                </View>
                <Text style={styles.summaryValue}>{getDeliveryDate(item) ? formatEstimatedDelivery(getDeliveryDate(item)) : 'Not available'}</Text>
              </View>

              <View style={styles.summaryItem}>
                <View style={styles.summaryLabel}>
                  <FontAwesome name="map-marker" size={14} color="#007bff" style={{ marginRight: 5 }} />
                  <Text style={styles.summaryLabelText}>Shipping Address</Text>
                </View>
                <Text style={styles.summaryValue} numberOfLines={1}>
                  {item.shipping_address?.split(',')[0] || '478 Mortimer Ave'}
                </Text>
              </View>
            </View>
          </View>
        </View>

        {item.items && item.items.length > 0 && (
          <View style={styles.orderItemsSection}>
            <Text style={styles.sectionTitle}>Order Items</Text>
            <FlatList
              data={item.items.slice(0, 2)}
              horizontal
              showsHorizontalScrollIndicator={false}
              keyExtractor={(itemData) => `${item.id}-${itemData.id}`}
              renderItem={({ item: orderItem }) => (
                <View style={styles.orderItemCard}>
                  {/* Using the simplified direct image render approach */}
                  {renderProductImage(orderItem)}
                  <Text style={styles.itemName} numberOfLines={1}>
                    {orderItem.product_name || (orderItem.product && orderItem.product.name)}
                  </Text>
                  <Text style={styles.itemPrice}>${Number(orderItem.price).toFixed(2)} × {orderItem.quantity}</Text>
                </View>
              )}
              ListFooterComponent={
                item.items.length > 2 ? (
                  <TouchableOpacity
                    style={styles.moreItemsButton}
                    onPress={() => handleViewDetails(item.id)}
                  >
                    <Text style={styles.moreItemsText}>+{item.items.length - 2} more</Text>
                  </TouchableOpacity>
                ) : null
              }
            />
          </View>
        )}

        {item.notes && (
          <View style={styles.notesSection}>
            <FontAwesome name="sticky-note" size={14} color="#ffc107" style={{ marginRight: 5 }} />
            <Text style={styles.notesText} numberOfLines={2}>{item.notes}</Text>
          </View>
        )}
      </View>
    );
  };

  const renderEmptyList = () => (
    <View style={styles.emptyContainer}>
      <Image
        source={{ uri: 'https://via.placeholder.com/100x100.png?text=Empty' }}
        style={{ width: 100, height: 100, marginBottom: 10 }}
      />
      <Text style={styles.emptyText}>
        {status ? `No ${status} orders found` : 'You don\'t have any pending orders at the moment'}
      </Text>
      <TouchableOpacity
        style={styles.browseCatalogButton}
        onPress={() => navigation.navigate('Catalog')}
      >
        <Text style={styles.browseCatalogText}>Browse Products</Text>
      </TouchableOpacity>
    </View>
  );

  if (loading && !refreshing) {
    return (
      <FranchiseeLayout title="Pending Orders">
        <View style={styles.centered}>
          <ActivityIndicator size="large" color="#4CAF50" />
          <Text style={styles.loadingText}>Loading orders...</Text>
        </View>
      </FranchiseeLayout>
    );
  }

  return (
    <FranchiseeLayout title="Pending Orders">
      <ScrollView
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
      >
        <View style={styles.headerActions}>
          <TouchableOpacity
            style={styles.historyButton}
            onPress={() => navigation.navigate('OrderHistory')}
          >
            <FontAwesome name="history" size={16} color="#fff" style={{ marginRight: 6 }} />
            <Text style={styles.historyButtonText}>Order History</Text>
          </TouchableOpacity>
        </View>

        <View style={styles.statusRow}>
          {renderStatusCard('Pending', 'pending', '#ffc107', '#212529')}
          {renderStatusCard('Processing', 'processing', '#17a2b8', '#212529')}
          {renderStatusCard('Packed', 'packed', '#6c757d', '#212529')}
          {renderStatusCard('Shipped', 'shipped', '#007bff', '#212529')}
        </View>

        {status && (
          <View style={styles.filterIndicator}>
            <FontAwesome name="filter" size={14} color="#495057" style={{ marginRight: 8 }} />
            <Text style={styles.filterText}>Filtered by: <Text style={styles.filterValue}>{status.charAt(0).toUpperCase() + status.slice(1)}</Text></Text>
            <TouchableOpacity style={styles.clearFilterButton} onPress={() => setStatus(null)}>
              <FontAwesome name="times-circle" size={14} color="#dc3545" />
              <Text style={styles.clearFilterText}>Clear</Text>
            </TouchableOpacity>
          </View>
        )}

        {orders.length === 0 ? (
          renderEmptyList()
        ) : (
          <FlatList
            data={orders}
            renderItem={renderOrderItem}
            keyExtractor={(item) => item.id.toString()}
            contentContainerStyle={styles.listContainer}
            scrollEnabled={false} // Disable scrolling as it's inside ScrollView
          />
        )}
      </ScrollView>
    </FranchiseeLayout>
  );
};

// Styles remain the same
const styles = StyleSheet.create({
  headerActions: {
    padding: 10,
    flexDirection: 'row',
    justifyContent: 'flex-end',
    marginHorizontal: 10,
    marginBottom: 10,
  },
  historyButton: {
    backgroundColor: '#007bff',
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 8,
    paddingHorizontal: 15,
    borderRadius: 20,
    elevation: 2,
  },
  historyButtonText: {
    color: '#fff',
    fontWeight: '600',
    fontSize: 14,
  },
  statusRow: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    padding: 16,
    backgroundColor: '#fff',
    marginBottom: 10,
    borderRadius: 8,
    elevation: 2,
    marginHorizontal: 10,
  },
  statusCard: {
    alignItems: 'center',
    padding: 10,
    borderRadius: 8,
    borderColor: '#dee2e6',
    borderWidth: 1,
    width: 80,
    backgroundColor: '#f8f9fa',
  },
  activeCard: {
    borderColor: '#4CAF50',
    backgroundColor: '#f0fff0',
  },
  statusCount: {
    fontSize: 20,
    fontWeight: 'bold',
  },
  statusLabel: {
    fontSize: 13,
    marginTop: 4,
  },
  filterIndicator: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f8f9fa',
    padding: 10,
    marginHorizontal: 16,
    borderRadius: 20,
    marginBottom: 15,
  },
  filterText: {
    fontSize: 14,
    color: '#495057',
    flex: 1,
  },
  filterValue: {
    fontWeight: 'bold',
  },
  clearFilterButton: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  clearFilterText: {
    color: '#dc3545',
    marginLeft: 5,
    fontSize: 14,
  },
  orderCard: {
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
  viewButton: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 6,
    borderRadius: 5,
    borderWidth: 1,
    borderColor: '#28a745',
  },
  viewButtonText: {
    color: '#28a745',
    fontSize: 13,
    marginLeft: 3,
  },
  sectionTitle: {
    fontSize: 15,
    fontWeight: '600',
    marginBottom: 10,
    color: '#343a40',
  },
  orderSummarySection: {
    marginVertical: 15,
  },
  summaryContainer: {
    flexDirection: 'row',
    backgroundColor: '#f8f9fa',
    borderRadius: 8,
    padding: 10,
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
  orderItemsSection: {
    marginVertical: 10,
  },
  orderItemCard: {
    width: 120,
    marginRight: 10,
    backgroundColor: '#f8f9fa',
    borderRadius: 8,
    padding: 8,
    borderWidth: 1,
    borderColor: '#eee',
  },
  itemImage: {
    width: '100%',
    height: 70,
    borderRadius: 6,
    marginBottom: 5,
    backgroundColor: '#eee',
  },
  productImageContainer: {
    width: '100%',
    height: 70,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#f8f9fa',
    borderRadius: 6,
    marginBottom: 5,
  },
  itemName: {
    fontSize: 13,
    fontWeight: '500',
    marginBottom: 3,
  },
  itemPrice: {
    fontSize: 12,
    color: '#6c757d',
  },
  moreItemsButton: {
    width: 80,
    height: 120,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'rgba(0,123,255,0.1)',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#dee2e6',
  },
  moreItemsText: {
    color: '#007bff',
    fontSize: 13,
    fontWeight: '500',
  },
  notesSection: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: '#fff9e6',
    padding: 10,
    borderRadius: 8,
    marginTop: 5,
  },
  notesText: {
    fontSize: 13,
    color: '#6c757d',
    flex: 1,
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
  emptyContainer: {
    padding: 30,
    alignItems: 'center',
    justifyContent: 'center',
  },
  emptyText: {
    fontSize: 16,
    color: '#6c757d',
    textAlign: 'center',
    marginVertical: 15,
  },
  browseCatalogButton: {
    backgroundColor: '#4CAF50',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 5,
    marginTop: 10,
  },
  browseCatalogText: {
    color: '#fff',
    fontWeight: '500',
  },
  listContainer: {
    padding: 16,
  },
});

export default OrdersScreen;