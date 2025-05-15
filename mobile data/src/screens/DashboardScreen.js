import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  FlatList,
  ActivityIndicator,
  RefreshControl,
  SafeAreaView,
  Image,
  Alert,
  Dimensions
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { getPendingOrders, getOrderHistory } from '../services/api';
import { LineChart } from 'react-native-chart-kit';

const DashboardScreen = ({ navigation, route }) => {
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [stats, setStats] = useState({
    pendingOrders: 0,
    monthlySpending: 0,
    spendingChange: 0,
    incomingDeliveries: 0,
    lowStockItems: 0
  });
  const [chartData, setChartData] = useState({
    weeklySpending: [0, 0, 0, 0, 0, 0, 0],
    weeklyOrders: [0, 0, 0, 0, 0, 0, 0],
    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
  });
  const [recentOrders, setRecentOrders] = useState([]);
  const [popularProducts, setPopularProducts] = useState([]);
  const [error, setError] = useState(null);
  const [userToken, setUserToken] = useState(route.params?.userToken || '');
  const [userData, setUserData] = useState(route.params?.userData || {});
  const [chartView, setChartView] = useState('weekly');
  
  const handleLogout = async () => {
    try {
      await AsyncStorage.removeItem('userToken');
      await AsyncStorage.removeItem('userData');
      
      if (global.checkAuthState) {
        await global.checkAuthState();
      } else {
        Alert.alert(
          "Logged Out",
          "Please restart the app.",
          [{ text: "OK" }]
        );
      }
    } catch (error) {
      console.error('Logout error:', error);
      Alert.alert("Error", "Failed to log out. Please try again.");
    }
  };

  // Get token from AsyncStorage if not provided in route params
  useEffect(() => {
    const getTokenFromStorage = async () => {
      if (!userToken) {
        try {
          const token = await AsyncStorage.getItem('userToken');
          if (token) {
            setUserToken(token);
            
            const userDataStr = await AsyncStorage.getItem('userData');
            if (userDataStr) {
              try {
                const parsedUserData = JSON.parse(userDataStr);
                setUserData(parsedUserData);
              } catch (e) {
                console.error('Failed to parse user data:', e);
              }
            }
          }
        } catch (e) {
          console.error('Failed to get token from AsyncStorage:', e);
        }
      }
    };
    
    getTokenFromStorage();
  }, []);

  useEffect(() => {
    if (userToken) {
      loadDashboardData();
    }
  }, [userToken]);

  const loadDashboardData = async () => {
    if (!userToken) {
      setError('Authentication token not found. Please login again.');
      setLoading(false);
      return;
    }

    try {
      setLoading(true);
      setError(null);

      // Create an array to store any error messages
      let errorMessages = [];

      // Fetch pending orders
      let pendingOrdersResponse = { success: false, order_counts: { pending: 0, shipped: 0 } };
      try {
        pendingOrdersResponse = await getPendingOrders(userToken);
        
        // Check for Laravel standard response format and adapt if needed
        if (pendingOrdersResponse.data && !pendingOrdersResponse.success) {
          pendingOrdersResponse.success = true;
          pendingOrdersResponse.order_counts = pendingOrdersResponse.data.counts || 
                                              { pending: pendingOrdersResponse.data.pending_count || 0, 
                                                shipped: pendingOrdersResponse.data.shipped_count || 0 };
        }
        
        if (!pendingOrdersResponse.success) {
          errorMessages.push('Failed to fetch pending orders');
        }
      } catch (err) {
        console.error('Error fetching pending orders:', err);
        errorMessages.push('Network error when fetching pending orders');
      }
      
      // Fetch order history (includes stats) - even if pending orders failed
      let orderHistoryResponse = { 
        success: false, 
        stats: { 
          total_spent: 0, 
          spending_change: 0,
          low_stock_items: 0
        }, 
        orders: [],
        charts: {
          weekly_spending: [0, 0, 0, 0, 0, 0, 0],
          weekly_orders: [0, 0, 0, 0, 0, 0, 0]
        },
        popular_products: []
      };
      
      try {
        orderHistoryResponse = await getOrderHistory(userToken);
        
        // Check for Laravel standard response format and adapt if needed
        if (orderHistoryResponse.data && !orderHistoryResponse.success) {
          orderHistoryResponse.success = true;
          orderHistoryResponse.orders = orderHistoryResponse.data.orders || [];
          orderHistoryResponse.stats = orderHistoryResponse.data.stats || 
                                      { total_spent: orderHistoryResponse.data.total_spent || 0, 
                                        spending_change: orderHistoryResponse.data.spending_change || 0,
                                        low_stock_items: orderHistoryResponse.data.low_stock_items || 0 };
          orderHistoryResponse.charts = orderHistoryResponse.data.charts || {
            weekly_spending: [0, 0, 0, 0, 0, 0, 0],
            weekly_orders: [0, 0, 0, 0, 0, 0, 0]
          };
          orderHistoryResponse.popular_products = orderHistoryResponse.data.popular_products || [];
        }
        
        if (!orderHistoryResponse.success) {
          errorMessages.push('Failed to fetch order history');
        }
      } catch (err) {
        console.error('Error fetching order history:', err);
        errorMessages.push('Network error when fetching order history');
      }
      
      // Update stats with whatever data we have
      setStats({
        pendingOrders: pendingOrdersResponse.order_counts?.pending || 0,
        monthlySpending: orderHistoryResponse.stats?.total_spent || 0,
        spendingChange: orderHistoryResponse.stats?.spending_change || 0,
        incomingDeliveries: pendingOrdersResponse.order_counts?.shipped || 0,
        lowStockItems: orderHistoryResponse.stats?.low_stock_items || 0
      });

      // Set chart data
      setChartData({
        weeklySpending: orderHistoryResponse.charts?.weekly_spending || [0, 0, 0, 0, 0, 0, 0],
        weeklyOrders: orderHistoryResponse.charts?.weekly_orders || [0, 0, 0, 0, 0, 0, 0],
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
      });

      // Show orders if available
      if (orderHistoryResponse.orders && Array.isArray(orderHistoryResponse.orders)) {
        setRecentOrders(orderHistoryResponse.orders.slice(0, 5));
      } else {
        setRecentOrders([]);
      }

      // Set popular products if available
      if (orderHistoryResponse.popular_products && Array.isArray(orderHistoryResponse.popular_products)) {
        setPopularProducts(orderHistoryResponse.popular_products.slice(0, 4));
      } else {
        setPopularProducts([]);
      }
      
      // Display any errors we've encountered
      if (errorMessages.length > 0) {
        setError('Some data could not be loaded: ' + errorMessages.join('. ') + '. Pull down to refresh.');
      } else {
        setError(null);
      }
    } catch (error) {
      console.error('Dashboard data loading error:', error);
      setError('Failed to load dashboard data. Pull down to refresh.');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadDashboardData();
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

  const getStatusText = (status) => {
    // Capitalize and replace underscores with spaces
    return status.charAt(0).toUpperCase() + status.slice(1).replace(/_/g, ' ');
  };
  
  // Toggle between weekly and monthly chart views
  const toggleChartView = () => {
    if (chartView === 'weekly') {
      setChartView('monthly');
      setChartData({
        ...chartData,
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
      });
    } else {
      setChartView('weekly');
      setChartData({
        ...chartData,
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
      });
    }
  };
  
  // Render loading state
  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#0066cc" />
        <Text style={styles.loadingText}>Loading dashboard...</Text>
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView 
        style={styles.scrollView}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
      >
        {/* Welcome Header */}
        <View style={styles.header}>
          <View>
            <Text style={styles.welcomeText}>Welcome back,</Text>
            <Text style={styles.usernameText}>{userData.username || 'Franchisee'}</Text>
            <Text style={styles.companyNameText}>{userData.profile?.company_name || 'Restaurant Franchise'}</Text>
          </View>
          {userData.profile?.logo_url ? (
            <Image 
              source={{ uri: userData.profile.logo_url }} 
              style={styles.logoImage} 
              resizeMode="contain"
            />
          ) : (
            <View style={styles.logoPlaceholder}>
              <Text style={styles.logoPlaceholderText}>
                {userData.profile?.company_name?.charAt(0) || 'F'}
              </Text>
            </View>
          )}
        </View>

        {/* Error message */}
        {error && (
          <View style={styles.errorContainer}>
            <Text style={styles.errorText}>{error}</Text>
          </View>
        )}

        {/* Order Activity Chart */}
        <View style={styles.chartContainer}>
          <View style={styles.chartHeader}>
            <Text style={styles.chartTitle}>Order Activity - Total Spending</Text>
            <TouchableOpacity onPress={toggleChartView} style={styles.chartToggleButton}>
              <Text style={styles.chartToggleText}>
                {chartView === 'weekly' ? 'Weekly' : 'Monthly'}
              </Text>
            </TouchableOpacity>
          </View>
          <View style={styles.totalSpendingBox}>
            <Text style={styles.totalSpendingLabel}>Total Spending</Text>
            <Text style={styles.totalSpendingValue}>
              {formatCurrency(chartData.weeklySpending.reduce((sum, val) => sum + val, 0))}
            </Text>
            <Text style={styles.totalSpendingPeriod}>
              {chartView === 'weekly' ? 'This week' : 'This year'}
            </Text>
          </View>
          {/* Line Chart */}
          <LineChart
            data={{
              labels: chartData.labels,
              datasets: [
                {
                  data: chartData.weeklySpending,
                  color: (opacity = 1) => `rgba(0, 102, 204, ${opacity})`,
                  strokeWidth: 2
                }
              ],
              legend: ['Spending']
            }}
            width={Dimensions.get('window').width - 40}
            height={220}
            chartConfig={{
              backgroundColor: '#fff',
              backgroundGradientFrom: '#fff',
              backgroundGradientTo: '#fff',
              decimalPlaces: 0,
              color: (opacity = 1) => `rgba(0, 102, 204, ${opacity})`,
              labelColor: (opacity = 1) => `rgba(0, 0, 0, ${opacity})`,
              style: {
                borderRadius: 16
              },
              propsForDots: {
                r: '6',
                strokeWidth: '2',
                stroke: '#0066cc'
              }
            }}
            bezier
            style={styles.chart}
          />
        </View>

        {/* Stats Grid */}
        <View style={styles.statsGrid}>
          <View style={styles.statCard}>
            <View style={[styles.statIconContainer, { backgroundColor: 'rgba(0, 102, 204, 0.1)' }]}>
              <Text style={styles.statIcon}>💰</Text>
            </View>
            <Text style={styles.statLabel}>Monthly Spending</Text>
            <Text style={styles.statValue}>{formatCurrency(stats.monthlySpending)}</Text>
            <View style={[
              styles.changeIndicator, 
              { backgroundColor: stats.spendingChange >= 0 ? '#e6f7ee' : '#ffebee' }
            ]}>
              <Text style={[
                styles.changeText,
                { color: stats.spendingChange >= 0 ? '#27ae60' : '#e74c3c' }
              ]}>
                {stats.spendingChange >= 0 ? '+' : ''}{stats.spendingChange}%
              </Text>
            </View>
          </View>

          <View style={styles.statCard}>
            <View style={[styles.statIconContainer, { backgroundColor: 'rgba(39, 174, 96, 0.1)' }]}>
              <Text style={styles.statIcon}>🛒</Text>
            </View>
            <Text style={styles.statLabel}>Pending Orders</Text>
            <Text style={styles.statValue}>{stats.pendingOrders}</Text>
          </View>

          <View style={styles.statCard}>
            <View style={[styles.statIconContainer, { backgroundColor: 'rgba(241, 196, 15, 0.1)' }]}>
              <Text style={styles.statIcon}>⚠️</Text>
            </View>
            <Text style={styles.statLabel}>Low Stock Items</Text>
            <Text style={styles.statValue}>{stats.lowStockItems}</Text>
          </View>

          <View style={styles.statCard}>
            <View style={[styles.statIconContainer, { backgroundColor: 'rgba(52, 152, 219, 0.1)' }]}>
              <Text style={styles.statIcon}>🚚</Text>
            </View>
            <Text style={styles.statLabel}>Incoming Deliveries</Text>
            <Text style={styles.statValue}>{stats.incomingDeliveries}</Text>
          </View>
        </View>

        {/* Recent Orders */}
        <View style={styles.sectionContainer}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Recent Orders</Text>
            <TouchableOpacity 
              onPress={() => navigation.navigate('OrderHistory')}
              style={styles.viewAllButton}
            >
              <Text style={styles.viewAllText}>View All</Text>
            </TouchableOpacity>
          </View>

          {recentOrders.length === 0 ? (
            <View style={styles.emptyStateContainer}>
              <Text style={styles.emptyStateText}>No recent orders found</Text>
            </View>
          ) : (
            <FlatList
              data={recentOrders}
              keyExtractor={(item) => item.id.toString()}
              scrollEnabled={false}
              renderItem={({ item }) => (
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
                        {getStatusText(item.status)}
                      </Text>
                    </View>
                  </View>
                  
                  <View style={styles.orderDetails}>
                    <Text style={styles.orderDate}>
                      {new Date(item.created_at).toLocaleDateString()}
                    </Text>
                    <Text style={styles.orderAmount}>
                      {formatCurrency(item.total_amount || item.total || 0)}
                    </Text>
                  </View>
                  
                  <Text style={styles.itemsCount}>
                    {item.items_count || '?'} items
                  </Text>
                </TouchableOpacity>
              )}
            />
          )}
        </View>

        {/* Popular Products */}
        {popularProducts.length > 0 && (
          <View style={styles.sectionContainer}>
            <View style={styles.sectionHeader}>
              <Text style={styles.sectionTitle}>Popular Products</Text>
              <TouchableOpacity 
                onPress={() => navigation.navigate('Catalog')}
                style={styles.viewAllButton}
              >
                <Text style={styles.viewAllText}>View All</Text>
              </TouchableOpacity>
            </View>

            <View style={styles.popularProductsGrid}>
              {popularProducts.map((product) => (
                <TouchableOpacity 
                  key={product.id} 
                  style={styles.popularProductCard}
                  onPress={() => navigation.navigate('Catalog', { highlightProductId: product.id })}
                >
                  <View style={styles.productImageContainer}>
                    {product.image_url ? (
                      <Image 
                        source={{ uri: product.image_url }} 
                        style={styles.productImage} 
                        resizeMode="cover"
                      />
                    ) : (
                      <View style={styles.imagePlaceholder}>
                        <Text>📦</Text>
                      </View>
                    )}
                  </View>
                  <Text numberOfLines={1} style={styles.productName}>{product.name}</Text>
                  <Text style={styles.productPrice}>{formatCurrency(product.price || 0)}</Text>
                </TouchableOpacity>
              ))}
            </View>
          </View>
        )}

        {/* Quick Actions */}
        <View style={styles.quickActionsContainer}>
          <Text style={styles.quickActionsTitle}>Quick Actions</Text>
          <View style={styles.quickActionsGrid}>
            <TouchableOpacity 
              style={styles.actionButton}
              onPress={() => navigation.navigate('Catalog')}
            >
              <Text style={styles.actionIcon}>🛍️</Text>
              <Text style={styles.actionText}>Browse Catalog</Text>
            </TouchableOpacity>
            
            <TouchableOpacity 
              style={styles.actionButton}
              onPress={() => navigation.navigate('Cart')}
            >
              <Text style={styles.actionIcon}>🛒</Text>
              <Text style={styles.actionText}>View Cart</Text>
            </TouchableOpacity>
            
            <TouchableOpacity 
              style={styles.actionButton}
              onPress={() => navigation.navigate('OrderHistory')}
            >
              <Text style={styles.actionIcon}>📜</Text>
              <Text style={styles.actionText}>Order History</Text>
            </TouchableOpacity>
            
            <TouchableOpacity 
              style={[styles.actionButton, styles.logoutButton]}
              onPress={handleLogout}
            >
              <Text style={styles.actionIcon}>🚪</Text>
              <Text style={styles.actionText}>Logout</Text>
            </TouchableOpacity>
          </View>
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
  scrollView: {
    flex: 1,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f5f5f5',
  },
  loadingText: {
    marginTop: 10,
    fontSize: 16,
    color: '#666',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  welcomeText: {
    fontSize: 14,
    color: '#666',
  },
  usernameText: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#333',
  },
  companyNameText: {
    fontSize: 16,
    color: '#666',
    marginTop: 4,
  },
  logoImage: {
    width: 50,
    height: 50,
    borderRadius: 25,
  },
  logoPlaceholder: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: '#0066cc',
    justifyContent: 'center',
    alignItems: 'center',
  },
  logoPlaceholderText: {
    color: '#fff',
    fontSize: 22,
    fontWeight: 'bold',
  },
  errorContainer: {
    margin: 15,
    padding: 15,
    backgroundColor: '#ffebee',
    borderRadius: 5,
  },
  errorText: {
    color: '#c62828',
  },
  // Chart styles
  chartContainer: {
    margin: 15,
    padding: 15,
    backgroundColor: '#fff',
    borderRadius: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  chartHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  chartTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  chartToggleButton: {
    backgroundColor: '#0066cc',
    padding: 6,
    borderRadius: 5,
  },
  chartToggleText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: 'bold',
  },
  totalSpendingBox: {
    backgroundColor: 'rgba(40, 167, 69, 0.1)',
    borderWidth: 1,
    borderColor: 'rgba(40, 167, 69, 0.2)',
    borderRadius: 8,
    padding: 10,
    marginBottom: 15,
  },
  totalSpendingLabel: {
    fontSize: 14,
    color: '#28a745',
  },
  totalSpendingValue: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#28a745',
  },
  totalSpendingPeriod: {
    fontSize: 12,
    color: '#666',
  },
  chart: {
    marginVertical: 8,
    borderRadius: 8,
  },
  // Stats Grid
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    padding: 10,
  },
  statCard: {
    width: '48%',
    backgroundColor: '#fff',
    padding: 15,
    borderRadius: 10,
    marginBottom: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  statIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 10,
  },
  statIcon: {
    fontSize: 20,
  },
  statLabel: {
    fontSize: 14,
    color: '#666',
    marginBottom: 5,
  },
  statValue: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
  },
  changeIndicator: {
    alignSelf: 'flex-start',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 10,
    marginTop: 5,
  },
  changeText: {
    fontSize: 10,
    fontWeight: 'bold',
  },
  sectionContainer: {
    margin: 15,
    backgroundColor: '#fff',
    borderRadius: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
    overflow: 'hidden',
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
  },
  viewAllButton: {
    padding: 5,
  },
  viewAllText: {
    color: '#0066cc',
    fontSize: 14,
  },
  emptyStateContainer: {
    padding: 30,
    alignItems: 'center',
  },
  emptyStateText: {
    color: '#999',
    fontSize: 16,
  },
  orderCard: {
    padding: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
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
    marginBottom: 5,
  },
  orderDate: {
    color: '#666',
    fontSize: 14,
  },
  orderAmount: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  itemsCount: {
    fontSize: 12,
    color: '#666',
  },
  // Popular Products
  popularProductsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    padding: 10,
  },
  popularProductCard: {
    width: '48%',
    backgroundColor: '#fff',
    borderRadius: 8,
    marginBottom: 10,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: '#eee',
  },
  productImageContainer: {
    width: '100%',
    height: 120,
    backgroundColor: '#f9f9f9',
  },
  productImage: {
    width: '100%',
    height: '100%',
  },
  imagePlaceholder: {
    width: '100%',
    height: '100%',
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f1f1f1',
  },
  productName: {
    fontSize: 14,
    fontWeight: 'bold',
    paddingHorizontal: 10,
    paddingTop: 8,
    color: '#333',
  },
  productPrice: {
    fontSize: 14,
    color: '#0066cc',
    paddingHorizontal: 10,
    paddingBottom: 8,
    paddingTop: 4,
  },
  // Quick Actions
  quickActionsContainer: {
    margin: 15,
    marginBottom: 30,
    backgroundColor: '#fff',
    borderRadius: 10,
    padding: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  quickActionsTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 15,
    color: '#333',
  },
  quickActionsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  actionButton: {
    width: '48%',
    backgroundColor: '#0066cc',
    borderRadius: 8,
    padding: 15,
    alignItems: 'center',
    marginBottom: 10,
  },
  actionIcon: {
    fontSize: 24,
    marginBottom: 8,
  },
  actionText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  logoutButton: {
    backgroundColor: '#e74c3c',
  },
});

export default DashboardScreen;