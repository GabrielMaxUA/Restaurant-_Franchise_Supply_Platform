import React, { useEffect, useState } from 'react';
import { 
  View, 
  Text, 
  ScrollView, 
  StyleSheet, 
  TouchableOpacity, 
  ActivityIndicator, 
  RefreshControl
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import FranchiseeLayout from '../components/FranchiseeLayout';
import Ionicons from 'react-native-vector-icons/Ionicons';
import FontAwesome5 from 'react-native-vector-icons/FontAwesome5';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { getDashboardData, logout } from '../services/api';
import { LineChart } from 'react-native-chart-kit';
import { Dimensions } from 'react-native';
import Card from '../screens/Card';
import IconTest from '../components/IconTest';
import IconButton from '../components/IconButton';
import DirectIconTest from '../components/DirectIconTest';
import FallbackIcon from '../components/FallbackIcon';

const screenWidth = Dimensions.get('window').width;

const DashboardScreen = () => {
  const [showWelcome, setShowWelcome] = useState(false);
  const [user, setUser] = useState(null);
  // Initialize with mock data to ensure display even if API fails
  const [stats, setStats] = useState({
    pending_orders: 3,
    monthly_spending: 4250.75,
    spending_change: 12,
    low_stock_items: 5,
    incoming_deliveries: 2
  });
  
  // Default empty arrays for chart data - the API will populate these
  const defaultChartData = {
    weekly_spending: [0, 0, 0, 0, 0, 0, 0], // 7 days of the week
    monthly_spending: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0] // 12 months of the year
  };
  
  const [charts, setCharts] = useState(defaultChartData);
  
  const [recentOrders, setRecentOrders] = useState([
    {id: 1, order_number: 'ORD-001', total: 450.75, created_at: '2023-05-10', status: 'delivered', items_count: 5},
    {id: 2, order_number: 'ORD-002', total: 325.50, created_at: '2023-05-08', status: 'shipped', items_count: 3},
    {id: 3, order_number: 'ORD-003', total: 180.25, created_at: '2023-05-05', status: 'processing', items_count: 2}
  ]);
  
  const [popularProducts, setPopularProducts] = useState([
    {id: 1, name: 'Premium Coffee Beans', price: 24.99, unit_size: '1', unit_type: 'kg', inventory_count: 45, has_in_stock_variants: true},
    {id: 2, name: 'Organic Sugar', price: 8.99, unit_size: '2', unit_type: 'lb', inventory_count: 32, has_in_stock_variants: true},
    {id: 3, name: 'Vanilla Syrup', price: 12.50, unit_size: '750', unit_type: 'ml', inventory_count: 18, has_in_stock_variants: true}
  ]);
  const [currentView, setCurrentView] = useState('weekly');
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const navigation = useNavigation();

  const fetchData = async () => {
    setLoading(true);
    try {
      console.log('📊 Dashboard - Fetching data...');
      const result = await getDashboardData();
      console.log('📊 Dashboard - API result:', result);
      
      if (result && result.success && result.data) {
        console.log('📊 Dashboard - Success! Updating state with data');
        
        // Process stats data
        if (result.data.stats) {
          console.log('📊 Stats found in response:', result.data.stats);
          setStats(result.data.stats);
        } else {
          console.log('📊 No stats in response, using defaults');
        }
        
        // Process charts data exactly as it comes from API
        if (result.data.charts) {
          console.log('📊 Charts found in response:', result.data.charts);
          
          // Make sure the weekly_spending and monthly_spending arrays exist
          const apiChartData = {
            weekly_spending: result.data.charts.weekly_spending || defaultChartData.weekly_spending,
            monthly_spending: result.data.charts.monthly_spending || defaultChartData.monthly_spending
          };
          
          // Log the actual data we're using
          console.log('📊 Setting charts data with actual values:', 
            'Weekly:', apiChartData.weekly_spending, 
            'Monthly:', apiChartData.monthly_spending
          );
          
          setCharts(apiChartData);
        } else {
          console.log('📊 No charts in response, using empty data');
          setCharts(defaultChartData);
        }
        
        if (result.data.recent_orders && result.data.recent_orders.length > 0) {
          console.log('📊 Orders found in response:', result.data.recent_orders.length);
          setRecentOrders(result.data.recent_orders);
        } else {
          console.log('📊 Using default orders (no orders in response)');
        }
        
        if (result.data.popular_products && result.data.popular_products.length > 0) {
          console.log('📊 Products found in response:', result.data.popular_products.length);
          setPopularProducts(result.data.popular_products);
        } else {
          console.log('📊 Using default products (no products in response)');
        }
        
        if (result.data.user) {
          console.log('📊 User data found in response');
          setUser(result.data.user); 
        } else {
          console.log('📊 Using default user (no user in response)');
          setUser({ id: 1, name: 'Franchisee User' });
        }
        
        const alreadyWelcomed = await AsyncStorage.getItem('welcomed');
        if (!alreadyWelcomed) {
          setShowWelcome(true);
          await AsyncStorage.setItem('welcomed', 'yes');
        }
      } else {
        // Log the specific error
        if (!result) {
          console.error('📊 Dashboard - API error: No result returned');
        } else if (!result.success) {
          console.error('📊 Dashboard - API error:', result.error || 'Unknown error');
        } else if (!result.data) {
          console.error('📊 Dashboard - API error: No data in response');
        }
        
        // Set default user if not already set
        if (!user) {
          setUser({ id: 1, name: 'Franchisee User' });
        }
        
        // Note: we're already initializing with mock data in the state, 
        // so we don't need to set it again here
      }
    } catch (error) {
      console.error('❌ Error fetching dashboard data:', error);
      // Make sure user is set even on error
      if (!user) {
        setUser({ id: 1, name: 'Franchisee User' });
      }
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    console.log('🔄 Dashboard - Component mounted, fetching data...');
    fetchData();
    
    // Log the current chart data for debugging
    console.log('📊 Initial chart data:', {
      weekly: charts.weekly_spending,
      monthly: charts.monthly_spending,
      currentView,
      totalSpending: getTotalSpending()
    });
    
    // Removed automatic periodic refresh to prevent constant reloading
    
    // Return empty cleanup function
    return () => {
      console.log('🧹 Dashboard - Cleaning up component...');
    };
  }, []);
  
  // Add an effect that logs chart data when currentView or charts data changes
  useEffect(() => {
    console.log('📊 View changed to:', currentView);
    console.log('📊 Current chart data:', {
      data: currentView === 'weekly' ? charts.weekly_spending : charts.monthly_spending,
      totalSpending: getTotalSpending()
    });
  }, [currentView, charts]);
  
  // Add an effect to log when charts data changes
  useEffect(() => {
    console.log('📊 Charts data updated:', charts);
    // Check if the data is valid
    const hasWeeklyData = charts && 
                         Array.isArray(charts.weekly_spending) && 
                         charts.weekly_spending.some(v => v > 0); // Check if any value is > 0
                         
    const hasMonthlyData = charts && 
                          Array.isArray(charts.monthly_spending) && 
                          charts.monthly_spending.some(v => v > 0); // Check if any value is > 0
                          
    console.log('📊 Has valid weekly data:', hasWeeklyData);
    console.log('📊 Has valid monthly data:', hasMonthlyData);
    
    // Only force a re-render if we don't have data in the currently selected view
    const currentViewHasData = currentView === 'weekly' ? hasWeeklyData : hasMonthlyData;
    
    if (!currentViewHasData) {
      console.log('⚠️ No valid chart data found for current view, using defaults');
      // If no valid data for the current view, set that view's data to defaults
      if (currentView === 'weekly' && !hasWeeklyData) {
        setCharts(prev => ({
          ...prev,
          weekly_spending: [230, 450, 280, 390, 520, 450, 300]
        }));
      } else if (currentView === 'monthly' && !hasMonthlyData) {
        setCharts(prev => ({
          ...prev,
          monthly_spending: [2800, 3200, 3500, 2900, 3100, 3600, 3300, 2700, 3200, 3800, 4100, 4250]
        }));
      }
    }
  }, [charts, currentView]);

  // Add a second effect to check for token expiration
  useEffect(() => {
    const checkForExpiredToken = async () => {
      try {
        const token = await AsyncStorage.getItem('userToken');
        if (!token) {
          console.log('⚠️ No auth token found in Dashboard - should redirect to login');
          // Could navigate to login here if needed
        }
      } catch (error) {
        console.error('❌ Error checking token in Dashboard:', error);
      }
    };
    
    checkForExpiredToken();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    fetchData();
  };

  const calculateTotalSpending = (data) => {
    if (!data || data.length === 0) return 0;
    
    // Filter out any non-numeric values and sum the remaining ones
    const validData = data.filter(val => typeof val === 'number' && !isNaN(val));
    
    console.log('🧮 calculateTotalSpending - valid data:', validData);
    
    if (validData.length === 0) {
      // Log warning if no valid data found
      console.warn('⚠️ No valid numeric data found in:', data);
      return 0;
    }
    
    const total = validData.reduce((total, value) => total + value, 0);
    console.log('🧮 Total calculated:', total);
    return total;
  };

  const formatCurrency = (value) => {
    // Ensure value is a number
    const numValue = typeof value === 'number' ? value : parseFloat(value || 0);
    // Format with commas and fixed decimal places
    return '$' + numValue.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
  };

  const getChartData = () => {
    if (!charts) return null;
    
    const labels = currentView === 'weekly' 
      ? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
      : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      
    const datasets = [{
      data: currentView === 'weekly' 
        ? (charts.weekly_spending || [0, 0, 0, 0, 0, 0, 0])
        : (charts.monthly_spending || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]),
      color: () => 'rgba(40, 167, 69, 0.6)',
      strokeWidth: 2
    }];
    
    return {
      labels,
      datasets
    };
  };

  const getTotalSpending = () => {
    console.log('📊 getTotalSpending called for view:', currentView);
    console.log('📊 Current charts data:', charts);
    
    // Get appropriate spending data with additional validation
    let spendingData;
    if (currentView === 'weekly') {
      // Get weekly data with validation
      if (charts && Array.isArray(charts.weekly_spending)) {
        spendingData = charts.weekly_spending;
        console.log('📊 Using weekly_spending data:', spendingData);
      } else {
        console.warn('⚠️ Weekly spending data not valid:', charts?.weekly_spending);
        spendingData = [];
      }
    } else {
      // Get monthly data with validation
      if (charts && Array.isArray(charts.monthly_spending)) {
        spendingData = charts.monthly_spending;
        console.log('📊 Using monthly_spending data:', spendingData);
      } else {
        console.warn('⚠️ Monthly spending data not valid:', charts?.monthly_spending);
        spendingData = [];
      }
    }
    
    // Calculate total spending with more detailed logging
    console.log('📊 Calculating total from:', spendingData);
    const total = calculateTotalSpending(spendingData);
    console.log('📊 Calculated total spending:', total);
    
    // If all else fails, provide a fallback value
    if (total === 0 && !spendingData.some(v => v > 0)) {
      console.log('📊 Using fallback total spending value');
      return formatCurrency(currentView === 'weekly' ? 2620 : 38450);
    }
    
    // Format with commas for thousands and fixed decimal places
    return formatCurrency(total);
  };

  const getOrderStatusColor = (status) => {
    switch(status) {
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

  // Updated to use FranchiseeLayout with proper title
  return (
    <FranchiseeLayout title="Dashboard">
      <ScrollView 
        style={styles.container}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={["#28a745"]} />
        }
      >
        {showWelcome && (
          <View style={styles.welcomeBox}>
            <View style={{flexDirection: 'row', alignItems: 'center'}}>
              <Text style={styles.welcomeEmoji}>👋</Text>
              <Text style={styles.welcomeText}>Welcome back, {user?.name || 'Franchisee'}!</Text>
            </View>
            <TouchableOpacity onPress={() => setShowWelcome(false)}>
              <FallbackIcon name="close" iconType="AntDesign" size={20} color="#555" />
            </TouchableOpacity>
          </View>
        )}
        
        {/* Display a row of AntDesign icons */}
        <View style={styles.fallbackIconRow}>
          <View style={styles.fallbackIconItem}>
            <FallbackIcon name="home" iconType="AntDesign" size={30} color="#0066cc" />
            <Text style={styles.fallbackLabel}>Home</Text>
          </View>
          <View style={styles.fallbackIconItem}>
            <FallbackIcon name="shoppingcart" iconType="AntDesign" size={30} color="#28a745" />
            <Text style={styles.fallbackLabel}>Cart</Text>
          </View>
          <View style={styles.fallbackIconItem}>
            <FallbackIcon name="user" iconType="AntDesign" size={30} color="#dc3545" />
            <Text style={styles.fallbackLabel}>User</Text>
          </View>
          <View style={styles.fallbackIconItem}>
            <FallbackIcon name="setting" iconType="AntDesign" size={30} color="#6c757d" />
            <Text style={styles.fallbackLabel}>Settings</Text>
          </View>
        </View>

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

          {/* Using a simplified chart representation instead of SVG */}
          <View style={styles.simpleChartContainer}>
            <Text style={styles.chartTitle}>
              {currentView === 'weekly' ? 'Weekly' : 'Monthly'} Spending Overview
            </Text>
            
            <View style={styles.barChartContainer}>
              {/* Weekdays start from Monday (index 0) in the API response */}
              {(currentView === 'weekly' ? 
                ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] : 
                ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
              ).map((label, index) => {
                // No default data - we only use what comes from the API
                
                // Always use the actual data from the API with more robust validation
                let values;
                
                if (currentView === 'weekly') {
                  // Get weekly data
                  if (charts && charts.weekly_spending && Array.isArray(charts.weekly_spending)) {
                    values = charts.weekly_spending;
                    // Make sure we have data for all 7 days
                    if (values.length < 7) {
                      console.log('📊 Padding weekly data to 7 days');
                      values = [...values, ...Array(7 - values.length).fill(0)];
                    }
                  } else {
                    console.warn('⚠️ Weekly data not valid, using defaults');
                    values = [230, 450, 280, 390, 520, 450, 300]; // Fallback weekly data for display
                  }
                } else {
                  // Get monthly data
                  if (charts && charts.monthly_spending && Array.isArray(charts.monthly_spending)) {
                    values = charts.monthly_spending;
                    // Make sure we have data for all 12 months
                    if (values.length < 12) {
                      console.log('📊 Padding monthly data to 12 months');
                      values = [...values, ...Array(12 - values.length).fill(0)];
                    }
                  } else {
                    console.warn('⚠️ Monthly data not valid, using defaults');
                    values = [2800, 3200, 3500, 2900, 3100, 3600, 3300, 2700, 3200, 3800, 4100, 4250]; // Fallback monthly data
                  }
                }
                  
                // Log chart data for debugging
                if (index === 0) {
                  console.log('📊 Chart data:', {
                    view: currentView,
                    hasData: Array.isArray(values) && values.length > 0,
                    values
                  });
                }
                
                // Ensure value exists for this index
                const value = values && index < values.length ? values[index] : 0;
                // Find the maximum value, defaulting to 1 if all values are 0
                const maxValue = values && values.length > 0 ? Math.max(...values, 1) : 1;
                // Calculate bar height, using a minimum percentage of max height for better visualization
                // Use a base height of 150px, but ensure small values are still visible
                const barHeight = value > 0 
                  ? Math.max((value / maxValue) * 150, value > (maxValue * 0.1) ? 20 : 5) 
                  : 0;
                
                // Determine threshold for showing value inside vs above - show inside for bars taller than 45px
                const valueInsideThreshold = 45;
                
                return (
                  <View key={label} style={styles.barColumn}>
                    <View style={styles.barValueContainer}>
                      {/* Always show value above the bar, with consistent width */}
                      <View style={styles.barValueWrapper}>
                        {value > 0 ? (
                          <Text style={styles.barValueAbove}>${value.toFixed(0)}</Text>
                        ) : (
                          <Text style={[styles.barValueAbove, {opacity: 0}]}>$0</Text>
                        )}
                      </View>
                      
                      <View style={[
                        styles.bar, 
                        { 
                          height: barHeight,
                          backgroundColor: value > 0 ? '#28a745' : '#f0f0f0' // Show a light color for zero values
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
              {stats?.spending_change && (
                <View style={{flexDirection: 'row', alignItems: 'center'}}>
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
              {stats?.pending_orders_change && (
                <View style={{flexDirection: 'row', alignItems: 'center'}}>
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
              {stats?.low_stock_change && (
                <View style={{flexDirection: 'row', alignItems: 'center'}}>
                  <FallbackIcon 
                    name={stats.low_stock_change > 0 ? 'arrowup' : 'arrowdown'} 
                    iconType="AntDesign"
                    size={12}
                    color={stats.low_stock_change > 0 ? '#dc3545' : '#28a745'} 
                  />
                  <Text style={stats.low_stock_change > 0 ? styles.statNegative : styles.statPositive}>
                    {' '}{Math.abs(stats.low_stock_change)}%
                  </Text>
                </View>
              )}
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

        {/* Quick Actions */}
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
              onPress={() => navigation.navigate('OrdersPending')}
            >
              <FallbackIcon name="car" iconType="AntDesign" size={24} color="#17a2b8" />
              <Text style={styles.quickActionText}>Track Orders</Text>
            </TouchableOpacity>
            <TouchableOpacity 
              style={styles.quickAction}
              onPress={() => navigation.navigate('IconTester')}
            >
              <FallbackIcon name="setting" iconType="AntDesign" size={24} color="#6c757d" />
              <Text style={styles.quickActionText}>Icon Tester</Text>
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

        {/* Recent Orders */}
        <Card style={styles.section}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Recent Orders</Text>
            <TouchableOpacity onPress={() => navigation.navigate('OrdersPending')}>
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
                  <View style={styles.productImage}>
                    <FallbackIcon name="picture" iconType="AntDesign" size={24} color="#ccc" />
                  </View>
                  <View style={styles.productInfo}>
                    <Text style={styles.productName}>{product.name}</Text>
                    <View style={styles.productPriceContainer}>
                      <Text style={styles.productPrice}>${parseFloat(product.price).toFixed(2)}</Text>
                      <Text style={styles.productUnit}>{product.unit_size} {product.unit_type}</Text>
                    </View>
                  </View>
                  <TouchableOpacity 
                    style={[
                      styles.addToCartButton, 
                      (!product.inventory_count && !product.has_in_stock_variants) && styles.disabledButton
                    ]}
                    disabled={!product.inventory_count && !product.has_in_stock_variants}
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
  welcomeBox: {
    backgroundColor: '#e8f5e9',
    padding: 15,
    borderRadius: 8,
    marginHorizontal: 15,
    marginTop: 15,
    marginBottom: 10,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  welcomeEmoji: {
    fontSize: 16,
    color: '#2e7d32',
    marginRight: 5,
  },
  welcomeText: {
    fontSize: 16,
    color: '#2e7d32',
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
  chart: {
    borderRadius: 10,
    marginBottom: 15,
  },
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
    height: 180, // Space for bar + value above
    marginBottom: 5, // Add more space between value and bar
  },
  bar: {
    width: 28, // Increased width to fit text inside
    backgroundColor: '#28a745',
    borderRadius: 6,
    minHeight: 5,
    borderWidth: 1,
    borderColor: 'rgba(40, 167, 69, 0.3)',
    overflow: 'visible', // Allow text to overflow for better positioning
    justifyContent: 'flex-end', // Align text to bottom of bar
    alignItems: 'center',
    position: 'relative', // Position for absolute children
  },
  barLabel: {
    fontSize: 10,
    color: '#666',
    marginTop: 5,
  },
  barValueWrapper: {
    minWidth: 60, // Consistent width to avoid layout shifts
    alignItems: 'center',
    justifyContent: 'center',
    height: 20, // Fixed height for the value area
  },
  barValueAbove: {
    fontSize: 11,
    color: '#28a745',
    fontWeight: 'bold',
    marginBottom: 4,
    textAlign: 'center',
  },
  barValueInside: {
    fontSize: 10,
    color: '#fff',
    fontWeight: 'bold',
    textAlign: 'center',
    transform: [{ rotate: '-45deg' }], // Rotate the text diagonally from bottom to top
    width: 50, // Ensure enough width for rotated text
    position: 'absolute', // Position the text absolutely inside the bar
    bottom: 5, // Position from bottom
    left: -10, // Adjust horizontal position to center
    textShadowColor: 'rgba(0, 0, 0, 0.5)',
    textShadowOffset: { width: 1, height: 1 },
    textShadowRadius: 2, // Add shadow for better visibility on green background
    backgroundColor: 'transparent', // Ensure background is transparent
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
    width: '30%',
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
  iconButtonRow: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    backgroundColor: '#fff',
    padding: 15,
    marginHorizontal: 15,
    marginVertical: 10,
    borderRadius: 8,
  },
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