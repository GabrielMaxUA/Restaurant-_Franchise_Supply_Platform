import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  Image, // Added Image import
  Alert // Added Alert import for testing
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { getDashboardData, logout } from '../services/api';
import FranchiseeLayout from '../components/FranchiseeLayout';
import Card from './Card';
import FallbackIcon from '../components/icon/FallbackIcon';
import { BASE_URL } from '../services/axiosInstance';

const DashboardScreen = () => {
  // State for dashboard data
  const [user, setUser] = useState(null);
  const [stats, setStats] = useState({
    pending_orders: 0,
    monthly_spending: 0,
    spending_change: 0,
    low_stock_items: 0,
    incoming_deliveries: 0,
    pending_orders_change: 0
  });

  // Chart data state
  const [charts, setCharts] = useState({
    weekly_spending: Array(7).fill(0),
    monthly_spending: Array(12).fill(0),
    weekly_orders: Array(7).fill(0),
    monthly_orders: Array(12).fill(0)
  });

  // Other state
  const [recentOrders, setRecentOrders] = useState([]);
  const [popularProducts, setPopularProducts] = useState([]);
  const [currentView, setCurrentView] = useState('weekly');
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [showWelcome, setShowWelcome] = useState(false);

  // Cart state
  const [cartCount, setCartCount] = useState(0);

  // Added image error state
  const [imageLoadErrors, setImageLoadErrors] = useState({});

  const navigation = useNavigation();

  // Function to handle image errors
  const handleImageError = (productId) => {
    console.log(`Image loading error for product ${productId}`);
    setImageLoadErrors(prev => ({
      ...prev,
      [productId]: true
    }));
  };

  // Function to fetch dashboard data from API
  const fetchData = async () => {
    setLoading(true);
    try {
      console.log('📊 Dashboard - Fetching data...');
      const result = await getDashboardData();
      console.log('📊 Dashboard - API result:', result?.success);

      if (result && result.success && result.data) {
        console.log('📊 Dashboard - Success! Updating state with data');

        // Process stats data
        if (result.data.stats) {
          console.log('📊 Stats found in response');
          setStats(result.data.stats);
        }

        // Process charts data
        if (result.data.charts) {
          console.log('📊 Charts found in response');
          setCharts(result.data.charts);
        }

        // Process recent orders
        if (result.data.recent_orders && result.data.recent_orders.length > 0) {
          console.log('📊 Orders found in response:', result.data.recent_orders.length);
          setRecentOrders(result.data.recent_orders);
        }

        // Process popular products - UPDATED FOR IMAGE HANDLING
        // Replace the product image handling section in your fetchData function

        // Process popular products - FIXED IMAGE HANDLING
        if (result.data.popular_products && result.data.popular_products.length > 0) {
          console.log('📊 Products found in response:', result.data.popular_products.length);

          // Log all products to see their structure
          console.log('📊 Products data structure sample:',
            JSON.stringify(result.data.popular_products[0], null, 2));

          // Add checking and debugging for image URLs
          const productsWithImages = result.data.popular_products.map(product => {
            // Log each product's image URL for debugging
            console.log(`Product ID ${product.id} - Image URL: ${product.image_url}`);

            // Check if the image URL is valid
            if (!product.image_url) {
              console.log(`Product ID ${product.id} has no image URL`);
              // Provide a default placeholder image instead of leaving it undefined
              product.image_url = 'https://via.placeholder.com/150';
            } else if (!product.image_url.startsWith('http')) {
              console.log(`Product ID ${product.id} has a relative image URL: ${product.image_url}`);


              // If path starts with /storage (Laravel public storage)
              if (product.image_url.includes('/storage/') || product.image_url.includes('storage/')) {
                let storagePath = product.image_url;

                // Clean up the path to ensure proper format
                if (storagePath.startsWith('/')) {
                  product.image_url = `${BASE_URL}${storagePath}`;
                } else {
                  product.image_url = `${BASE_URL}/${storagePath}`;
                }
              }
              // If path is a direct product-images path
              else if (product.image_url.includes('product-images')) {
                // Add the /storage/ prefix that Laravel's asset() would add
                product.image_url = `${BASE_URL}/storage/${product.image_url.replace('product-images', 'product-images/')}`;
              }
              // For other relative URLs
              else {
                product.image_url = `${BASE_URL}/${product.image_url}`;
              }

              console.log(`Converted to absolute URL: ${product.image_url}`);
            }

            // Return the product with possibly updated image URL
            return product;
          });


          // Reset image errors when loading new products
          setImageLoadErrors({});

          // Set the processed products in state
          setPopularProducts(productsWithImages);
        }

        // Process cart data
        if (result.data.cart) {
          console.log('🛒 Cart found in response - items:', result.data.cart.items_count);
          // Set cart count for badge
          setCartCount(result.data.cart.items_count);

          // Store cart data in AsyncStorage for other screens
          await AsyncStorage.setItem('cartData', JSON.stringify(result.data.cart));
        } else {
          console.log('🛒 No cart in response');
          setCartCount(0);
        }

        // Process user data
        if (result.data.user) {
          console.log('👤 User data found in response');
          setUser(result.data.user);
        }

        // Show welcome banner on first login
        const alreadyWelcomed = await AsyncStorage.getItem('welcomed');
        if (!alreadyWelcomed) {
          setShowWelcome(true);
          await AsyncStorage.setItem('welcomed', 'yes');
        }
      } else {
        // Handle error
        console.error('📊 Dashboard - API error:', result?.error || 'Unknown error');
        alert('Error loading dashboard data. Please try again.');
      }
    } catch (error) {
      console.error('❌ Error fetching dashboard data:', error);
      alert('Network error. Please check your connection and try again.');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  // Load data on component mount
  useEffect(() => {
    fetchData();

    // Listen for cart updates from other screens
    const unsubscribe = navigation.addListener('focus', () => {
      // Check for updated cart count when screen is focused
      const checkCartUpdate = async () => {
        try {
          const cartDataString = await AsyncStorage.getItem('cartData');
          if (cartDataString) {
            const cartData = JSON.parse(cartDataString);
            setCartCount(cartData.items_count || 0);
          }
        } catch (error) {
          console.error('Error checking cart updates:', error);
        }
      };

      checkCartUpdate();
    });

    return unsubscribe;
  }, [navigation]);

  // Pull to refresh
  const onRefresh = () => {
    setRefreshing(true);
    fetchData();
  };

  // Function to render product image with improved debugging
  const renderProductImage = (product) => {
    if (!product.image_url) {
      console.log(`No image URL for product ${product.id}`);
      return (
        <View style={styles.productImageContainer}>
          <FallbackIcon name="picture" iconType="AntDesign" size={24} color="#ccc" />
        </View>
      );
    }

    // Log the image URL being rendered
    console.log(`Rendering image for product ${product.id}: ${product.image_url}`);

    return (
      <View style={styles.productImageContainer}>
        <Image
          source={{ uri: product.image_url }}
          style={styles.productImage}
          onError={() => {
            console.log(`Image load error for ${product.id}`);
            handleImageError(product.id);
          }}
          onLoad={() => console.log(`Image loaded successfully for product ${product.id}`)}
          resizeMode="cover"
        />
      </View>
    );
  };

  // Calculate total spending for charts
  const calculateTotalSpending = (data) => {
    if (!data || data.length === 0) return 0;

    // Filter out any non-numeric values and sum the remaining ones
    const validData = data.filter(val => typeof val === 'number' && !isNaN(val));

    if (validData.length === 0) {
      return 0;
    }

    const total = validData.reduce((total, value) => total + value, 0);
    return total;
  };

  // Format currency values
  const formatCurrency = (value) => {
    // Ensure value is a number
    const numValue = typeof value === 'number' ? value : parseFloat(value || 0);
    // Format with commas and fixed decimal places
    return '$' + numValue.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
  };

  // Get total spending for current view
  const getTotalSpending = () => {
    let spendingData;

    if (currentView === 'weekly') {
      spendingData = charts.weekly_spending;
    } else {
      spendingData = charts.monthly_spending;
    }

    const total = calculateTotalSpending(spendingData);
    return formatCurrency(total);
  };

  // Get color for order status badge
  const getOrderStatusColor = (status) => {
    switch (status) {
      case 'pending': return '#ffc107';
      case 'processing': return '#17a2b8';
      case 'shipped': return '#007bff';
      case 'out_for_delivery':
      case 'delivered': return '#28a745';
      case 'cancelled': return '#6c757d';
      case 'rejected': return '#dc3545';
      default: return '#6c757d';
    }
  };

  // Loading state
  if (loading && !refreshing) {
    return (
      <FranchiseeLayout title="Dashboard">
        <View style={styles.centered}>
          <ActivityIndicator size="large" color="#28a745" />
          <Text style={styles.loadingText}>Loading dashboard...</Text>
        </View>
      </FranchiseeLayout>
    );
  }

  // Main dashboard
  return (
    <FranchiseeLayout title="Dashboard">
      <ScrollView
        style={styles.container}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={["#28a745"]} />
        }
      >
        <Card style={styles.section}>
          <Text style={styles.sectionTitle}>Quick Actions</Text>
          <View style={styles.quickActions}>
            <TouchableOpacity
              style={[styles.quickAction, styles.primaryAction]}
              onPress={() => navigation.navigate('Catalog')}
            >
              <FallbackIcon name="shoppingcart" iconType="AntDesign" size={24} color="#fff" />
              <Text style={styles.quickActionText}>Place Order</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.quickAction}
              onPress={() => navigation.navigate('OrdersScreen')}
            >
              <FallbackIcon name="car" iconType="AntDesign" size={24} color="#17a2b8" />
              <Text style={styles.quickActionText}>Track Orders</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.quickAction, styles.logoutAction]}
              onPress={async () => {
                try {
                  console.log('🚪 Logging out...');
                  await logout();
                  // Reset navigation to Login screen
                  navigation.reset({
                    index: 0,
                    routes: [{ name: 'Login' }],
                  });
                } catch (error) {
                  console.error('❌ Error logging out:', error);
                }
              }}
            >
              <FallbackIcon name="logout" iconType="AntDesign" size={24} color="#dc3545" />
              <Text style={styles.logoutText}>Logout</Text>
            </TouchableOpacity>
          </View>
        </Card>

        {/* Order Activity Chart */}
        <Card style={styles.chartCard}>
          <View style={styles.cardHeader}>
            <Text style={styles.cardTitle}>Order Activity</Text>
            <View style={styles.chartToggle}>
              <TouchableOpacity
                style={[styles.toggleButton, currentView === 'weekly' && styles.toggleActive]}
                onPress={() => setCurrentView('weekly')}
              >
                <Text style={[styles.toggleText, currentView === 'weekly' && styles.toggleActiveText]}>Weekly</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.toggleButton, currentView === 'monthly' && styles.toggleActive]}
                onPress={() => setCurrentView('monthly')}
              >
                <Text style={[styles.toggleText, currentView === 'monthly' && styles.toggleActiveText]}>Monthly</Text>
              </TouchableOpacity>
            </View>
          </View>

          <View style={styles.totalSpendingBox}>
            <Text style={styles.totalSpendingLabel}>Total Spending</Text>
            <Text style={styles.totalSpendingValue}>{getTotalSpending()}</Text>
            <Text style={styles.totalSpendingPeriod}>{currentView === 'weekly' ? 'This week' : 'This year'}</Text>
          </View>

          {/* Chart visualization */}
          <View style={styles.simpleChartContainer}>
            <Text style={styles.chartTitle}>
              {currentView === 'weekly' ? 'Weekly' : 'Monthly'} Spending Overview
            </Text>

            <View style={styles.barChartContainer}>
              {(currentView === 'weekly' ?
                ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] :
                ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
              ).map((label, index) => {
                // Get values based on current view
                let values = currentView === 'weekly'
                  ? charts.weekly_spending
                  : charts.monthly_spending;

                // Ensure value exists for this index
                const value = values && index < values.length ? values[index] : 0;

                // Find the maximum value for scaling
                const maxValue = values && values.length > 0 ? Math.max(...values, 1) : 1;

                // Calculate bar height with better visualization for small values
                const barHeight = value > 0
                  ? Math.max((value / maxValue) * 150, value > (maxValue * 0.1) ? 20 : 5)
                  : 0;

                return (
                  <View key={label} style={styles.barColumn}>
                    <View style={styles.barValueContainer}>
                      {/* Value above the bar */}
                      <View style={styles.barValueWrapper}>
                        {value > 0 ? (
                          <Text style={styles.barValueAbove}>${value.toFixed(0)}</Text>
                        ) : (
                          <Text style={[styles.barValueAbove, { opacity: 0 }]}>$0</Text>
                        )}
                      </View>

                      {/* The bar itself */}
                      <View style={[
                        styles.bar,
                        {
                          height: barHeight,
                          backgroundColor: value > 0 ? '#28a745' : '#f0f0f0'
                        }
                      ]} />
                    </View>
                    <Text style={styles.barLabel}>{label}</Text>
                  </View>
                );
              })}
            </View>
          </View>
        </Card>

        {/* Stats Grid */}
        <View style={styles.statsGrid}>
          {/* Monthly Spending */}
          <Card style={styles.statCard}>
            <View style={[styles.statIconContainer, styles.iconPrimary]}>
              <FallbackIcon name="pay-circle-o1" iconType="AntDesign" size={18} color="#007bff" />
            </View>
            <Text style={styles.statTitle}>Monthly Spending</Text>
            <View style={styles.statValueRow}>
              <Text style={styles.statValue}>${parseFloat(stats?.monthly_spending || 0).toFixed(2)}</Text>
              {stats?.spending_change !== undefined && (
                <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                  <FallbackIcon
                    name={stats.spending_change > 0 ? 'arrowup' : 'arrowdown'}
                    iconType="AntDesign"
                    size={12}
                    color={stats.spending_change > 0 ? '#dc3545' : '#28a745'}
                  />
                  <Text style={stats.spending_change > 0 ? styles.statNegative : styles.statPositive}>
                    {' '}{Math.abs(stats.spending_change)}%
                  </Text>
                </View>
              )}
            </View>
            <Text style={styles.statCaption}>Since last month</Text>
          </Card>

          {/* Pending Orders */}
          <Card style={styles.statCard}>
            <View style={[styles.statIconContainer, styles.iconSuccess]}>
              <FallbackIcon name="shoppingcart" iconType="AntDesign" size={18} color="#28a745" />
            </View>
            <Text style={styles.statTitle}>Pending Orders</Text>
            <View style={styles.statValueRow}>
              <Text style={styles.statValue}>{stats?.pending_orders || 0}</Text>
              {stats?.pending_orders_change !== undefined && (
                <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                  <FallbackIcon
                    name={stats.pending_orders_change > 0 ? 'arrowup' : 'arrowdown'}
                    iconType="AntDesign"
                    size={12}
                    color={stats.pending_orders_change > 0 ? '#28a745' : '#dc3545'}
                  />
                  <Text style={stats.pending_orders_change > 0 ? styles.statPositive : styles.statNegative}>
                    {' '}{Math.abs(stats.pending_orders_change)}%
                  </Text>
                </View>
              )}
            </View>
            <Text style={styles.statCaption}>Since last month</Text>
          </Card>

          {/* Low Stock Items */}
          <Card style={styles.statCard}>
            <View style={[styles.statIconContainer, styles.iconWarning]}>
              <FallbackIcon name="warning" iconType="AntDesign" size={18} color="#ffc107" />
            </View>
            <Text style={styles.statTitle}>Low Stock Items</Text>
            <View style={styles.statValueRow}>
              <Text style={styles.statValue}>{stats?.low_stock_items || 0}</Text>
            </View>
            <Text style={styles.statCaption}>Items needing reorder</Text>
          </Card>

          {/* Incoming Deliveries */}
          <Card style={styles.statCard}>
            <View style={[styles.statIconContainer, styles.iconInfo]}>
              <FallbackIcon name="car" iconType="AntDesign" size={18} color="#17a2b8" />
            </View>
            <Text style={styles.statTitle}>Incoming Deliveries</Text>
            <Text style={styles.statValue}>{stats?.incoming_deliveries || 0}</Text>
            <Text style={styles.statCaption}>Expected this week</Text>
          </Card>
        </View>


        {/* Recent Orders */}
        <Card style={styles.section}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Recent Orders</Text>
            <TouchableOpacity onPress={() => navigation.navigate('OrdersScreen')}>
              <Text style={styles.viewAll}>View All</Text>
            </TouchableOpacity>
          </View>
          {recentOrders.length > 0 ? (
            recentOrders.map(order => (
              <TouchableOpacity
                key={order.id}
                style={styles.orderItem}
                onPress={() => navigation.navigate('OrderDetails', { orderId: order.id })}
              >
                <View style={styles.orderInfo}>
                  <Text style={styles.orderNumber}>#{order.order_number}</Text>
                  <Text style={styles.orderDate}>{order.created_at}</Text>
                </View>
                <View style={styles.orderDetails}>
                  <Text style={styles.orderItemCount}>{order.items_count} items</Text>
                  <Text style={styles.orderTotal}>${parseFloat(order.total).toFixed(2)}</Text>
                </View>
                <View style={styles.orderStatusContainer}>
                  <View
                    style={[
                      styles.orderStatus,
                      { backgroundColor: getOrderStatusColor(order.status) }
                    ]}
                  >
                    <Text style={styles.orderStatusText}>
                      {order.status.charAt(0).toUpperCase() + order.status.slice(1).replace(/_/g, ' ')}
                    </Text>
                  </View>
                </View>
              </TouchableOpacity>
            ))
          ) : (
            <Text style={styles.emptyText}>No recent orders found.</Text>
          )}
        </Card>

        {/* Popular Products */}
        <Card style={styles.section}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Popular Products</Text>
            <TouchableOpacity onPress={() => navigation.navigate('Catalog', { sort: 'popular' })}>
              <Text style={styles.viewAll}>View All</Text>
            </TouchableOpacity>
          </View>
          <View style={styles.productsContainer}>
            {popularProducts.length > 0 ? (
              popularProducts.map(product => (
                <View key={product.id} style={styles.productItem}>
                  {product.image_url ? (
                    <Image
                      source={{ uri: product.image_url }}
                      style={styles.productImage}
                      resizeMode="cover"
                      onError={() => handleImageError(product.id)}
                    />
                  ) : (
                    <View style={styles.productImage}>
                      <FallbackIcon name="picture" iconType="AntDesign" size={24} color="#ccc" />
                    </View>
                  )}
                  <View style={styles.productInfo}>
                    <Text style={styles.productName}>{product.name}</Text>
                    <View style={styles.productPriceContainer}>
                      <Text style={styles.productPrice}>${parseFloat(product.price).toFixed(2)}</Text>
                      <Text style={styles.productUnit}>
                        {product.unit_size} {product.unit_type}
                      </Text>
                    </View>
                  </View>
                  <TouchableOpacity
                    style={[
                      styles.addToCartButton,
                      (!product.inventory_count && !product.has_in_stock_variants) && styles.disabledButton
                    ]}
                    disabled={!product.inventory_count && !product.has_in_stock_variants}
                    onPress={() => navigation.navigate('ProductDetail', { productId: product.id })}
                  >
                    <FallbackIcon name="shoppingcart" iconType="AntDesign" size={14} color="#fff" />
                  </TouchableOpacity>
                </View>
              ))
            ) : (
              <Text style={styles.emptyText}>No popular products found.</Text>
            )}
          </View>
        </Card>
      </ScrollView>
    </FranchiseeLayout>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8f9fa',
    paddingTop: 5,
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
    color: '#555',
  },

  // Chart Card
  chartCard: {
    marginHorizontal: 15,
    marginVertical: 10,
    borderRadius: 10,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 15,
    paddingTop: 15,
  },
  cardTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#212529',
  },
  chartToggle: {
    flexDirection: 'row',
    borderRadius: 4,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: '#28a745',
  },
  toggleButton: {
    paddingHorizontal: 10,
    paddingVertical: 6,
  },
  toggleActive: {
    backgroundColor: '#28a745',
  },
  toggleText: {
    fontSize: 12,
    color: '#28a745',
    fontWeight: '500',
  },
  toggleActiveText: {
    color: '#ffffff',
  },
  totalSpendingBox: {
    backgroundColor: 'rgba(40, 167, 69, 0.1)',
    borderWidth: 1,
    borderColor: 'rgba(40, 167, 69, 0.2)',
    borderRadius: 8,
    padding: 12,
    margin: 15,
  },
  totalSpendingLabel: {
    fontSize: 14,
    color: '#212529',
  },
  totalSpendingValue: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#28a745',
    marginVertical: 3,
  },
  totalSpendingPeriod: {
    fontSize: 12,
    color: '#6c757d',
  },

  // Chart Visualization
  simpleChartContainer: {
    padding: 15,
    marginBottom: 15,
  },
  chartTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#212529',
    marginBottom: 15,
    textAlign: 'center',
  },
  barChartContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-end',
    height: 200,
    paddingTop: 20,
  },
  barColumn: {
    alignItems: 'center',
    flex: 1,
  },
  barValueContainer: {
    alignItems: 'center',
    justifyContent: 'flex-end',
    height: 180,
    marginBottom: 5,
  },
  bar: {
    width: 28,
    backgroundColor: '#28a745',
    borderRadius: 6,
    minHeight: 5,
    borderWidth: 1,
    borderColor: 'rgba(40, 167, 69, 0.3)',
    overflow: 'visible',
    justifyContent: 'flex-end',
    alignItems: 'center',
    position: 'relative',
  },
  barLabel: {
    fontSize: 10,
    color: '#666',
    marginTop: 5,
  },
  barValueWrapper: {
    minWidth: 60,
    alignItems: 'center',
    justifyContent: 'center',
    height: 20,
  },
  barValueAbove: {
    fontSize: 11,
    color: '#28a745',
    fontWeight: 'bold',
    marginBottom: 4,
    textAlign: 'center',
  },

  // Stats Grid
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    marginHorizontal: 15,
    marginVertical: 10,
  },
  statCard: {
    width: '48%',
    padding: 15,
    marginBottom: 10,
    borderRadius: 10,
  },
  statIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 10,
  },
  iconPrimary: {
    backgroundColor: 'rgba(0, 123, 255, 0.1)',
  },
  iconSuccess: {
    backgroundColor: 'rgba(40, 167, 69, 0.1)',
  },
  iconWarning: {
    backgroundColor: 'rgba(255, 193, 7, 0.1)',
  },
  iconInfo: {
    backgroundColor: 'rgba(23, 162, 184, 0.1)',
  },
  statTitle: {
    fontSize: 13,
    color: '#212529',
    marginBottom: 5,
  },
  statValueRow: {
    flexDirection: 'row',
    alignItems: 'baseline',
  },
  statValue: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#212529',
    marginRight: 5,
  },
  statPositive: {
    fontSize: 12,
    color: '#28a745',
  },
  statNegative: {
    fontSize: 12,
    color: '#dc3545',
  },
  statCaption: {
    fontSize: 11,
    color: '#6c757d',
    marginTop: 2,
  },

  // Section common styles
  section: {
    marginHorizontal: 15,
    marginVertical: 10,
    borderRadius: 10,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 15,
    paddingTop: 15,
    paddingBottom: 5,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#212529',
    marginLeft: 15,
    marginTop: 15,
    marginBottom: 10,
  },
  viewAll: {
    fontSize: 14,
    color: '#28a745',
  },
  emptyText: {
    textAlign: 'center',
    padding: 15,
    color: '#6c757d',
  },

  // Quick Actions
  quickActions: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    flexWrap: 'wrap',
    padding: 10,
  },
  quickAction: {
    width: '22%',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 15,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#dee2e6',
  },
  primaryAction: {
    backgroundColor: '#28a745',
    borderColor: '#28a745',
  },
  logoutAction: {
    borderColor: '#dc3545',
    borderWidth: 1,
  },
  quickActionText: {
    fontSize: 12,
    marginTop: 8,
    textAlign: 'center',
    color: '#212529',
  },
  logoutText: {
    fontSize: 12,
    marginTop: 8,
    textAlign: 'center',
    color: '#dc3545',
    fontWeight: '500',
  },

  // Order Items
  orderItem: {
    padding: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#f0f0f0',
  },
  orderInfo: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 5,
  },
  orderNumber: {
    fontSize: 14,
    fontWeight: '500',
    color: '#212529',
  },
  orderDate: {
    fontSize: 12,
    color: '#6c757d',
  },
  orderDetails: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  orderItemCount: {
    fontSize: 13,
    color: '#212529',
  },
  orderTotal: {
    fontSize: 13,
    fontWeight: '500',
    color: '#28a745',
  },
  orderStatusContainer: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
  },
  orderStatus: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 4,
  },
  orderStatusText: {
    fontSize: 11,
    color: '#fff',
    fontWeight: '500',
  },

  // Product Items
  productsContainer: {
    padding: 10,
  },
  productItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 10,
    borderWidth: 1,
    borderColor: '#f0f0f0',
    borderRadius: 8,
    marginBottom: 10,
  },
  productImage: {
    width: 45,
    height: 45,
    backgroundColor: '#f8f9fa',
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 4,
    marginRight: 10,
    overflow: 'hidden', // Add this to make sure images don't overflow container
  },
  productInfo: {
    flex: 1,
  },
  productName: {
    fontSize: 14,
    fontWeight: '500',
    color: '#212529',
    marginBottom: 3,
  },
  productPriceContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  productPrice: {
    fontSize: 13,
    fontWeight: '500',
    color: '#28a745',
    marginRight: 5,
  },
  productUnit: {
    fontSize: 11,
    color: '#6c757d',
  },
  addToCartButton: {
    backgroundColor: '#28a745',
    width: 32,
    height: 32,
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
  },
  disabledButton: {
    backgroundColor: '#dee2e6',
  },

  // Icon row
  fallbackIconRow: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    backgroundColor: '#fff',
    padding: 15,
    marginHorizontal: 15,
    marginVertical: 10,
    borderRadius: 8,
  },
  fallbackIconItem: {
    alignItems: 'center',
  },
  fallbackLabel: {
    fontSize: 12,
    color: '#666',
    marginTop: 5,
  },
});

export default DashboardScreen;