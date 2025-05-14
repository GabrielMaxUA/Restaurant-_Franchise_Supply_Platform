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
  Alert
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { getPendingOrders, getOrderHistory } from '../services/api';

const DashboardScreen = ({ navigation, route }) => {
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [stats, setStats] = useState({
    pendingOrders: 0,
    monthlySpending: 0,
    spendingChange: 0,
    incomingDeliveries: 0
  });
  const [recentOrders, setRecentOrders] = useState([]);
  const [error, setError] = useState(null);

  const handleLogout = async () => {
    try {
      // Clear the stored tokens
      await AsyncStorage.removeItem('userToken');
      await AsyncStorage.removeItem('userData');
      
      // Call the global function if it exists
      if (global.checkAuthState) {
        await global.checkAuthState();
      } else {
        // Fallback if global function doesn't exist
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

  // Try to get token from route.params first, then from AsyncStorage if not provided
  const [userToken, setUserToken] = useState(route.params?.userToken || '');
  const [userData, setUserData] = useState(route.params?.userData || {});
  
  // Try to get token from AsyncStorage if not provided in route params
  useEffect(() => {
    const getTokenFromStorage = async () => {
      if (!userToken) {
        try {
          console.log('Token not found in route params, checking AsyncStorage...');
          const token = await AsyncStorage.getItem('userToken');
          if (token) {
            console.log('Token found in AsyncStorage');
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
          } else {
            console.log('No token found in AsyncStorage');
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
      console.log('User token available, loading dashboard data...');
      loadDashboardData();
    }
  }, [userToken]);

  const loadDashboardData = async () => {
    if (!userToken) {
      console.error('No user token found in Dashboard');
      setError('Authentication token not found. Please login again.');
      setLoading(false);
      return;
    }

    try {
      setLoading(true);
      setError(null);
      console.log('Dashboard loading data with token:', userToken);

      // Create an array to store any error messages
      let errorMessages = [];

      // Fetch pending orders
      console.log('Fetching pending orders...');
      let pendingOrdersResponse = { success: false, order_counts: { pending: 0, shipped: 0 } };
      try {
        pendingOrdersResponse = await getPendingOrders(userToken);
        console.log('Pending orders response (full):', JSON.stringify(pendingOrdersResponse));
        
        // Check for Laravel standard response format and adapt if needed
        if (pendingOrdersResponse.data && !pendingOrdersResponse.success) {
          console.log('Detected Laravel standard response format, adapting...');
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
      console.log('Fetching order history...');
      let orderHistoryResponse = { success: false, stats: { total_spent: 0, spending_change: 0 }, orders: [] };
      try {
        orderHistoryResponse = await getOrderHistory(userToken);
        console.log('Order history response (full):', JSON.stringify(orderHistoryResponse));
        
        // Check for Laravel standard response format and adapt if needed
        if (orderHistoryResponse.data && !orderHistoryResponse.success) {
          console.log('Detected Laravel standard response format for order history, adapting...');
          orderHistoryResponse.success = true;
          orderHistoryResponse.orders = orderHistoryResponse.data.orders || [];
          orderHistoryResponse.stats = orderHistoryResponse.data.stats || 
                                      { total_spent: orderHistoryResponse.data.total_spent || 0, 
                                        spending_change: orderHistoryResponse.data.spending_change || 0 };
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
        incomingDeliveries: pendingOrdersResponse.order_counts?.shipped || 0
      });

      // Show orders if available
      if (orderHistoryResponse.orders && Array.isArray(orderHistoryResponse.orders)) {
        setRecentOrders(orderHistoryResponse.orders.slice(0, 5));
      } else {
        setRecentOrders([]);
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
            <Text style={styles.companyNameText}>{userData.profile?.company_name || ''}</Text>
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

        {/* Stats Overview */}
        <View style={styles.statsContainer}>
          <View style={styles.statCard}>
            <Text style={styles.statValue}>{stats.pendingOrders}</Text>
            <Text style={styles.statLabel}>Pending Orders</Text>
          </View>
          
          <View style={styles.statCard}>
            <Text style={styles.statValue}>{formatCurrency(stats.monthlySpending)}</Text>
            <Text style={styles.statLabel}>Monthly Spending</Text>
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
            <Text style={styles.statValue}>{stats.incomingDeliveries}</Text>
            <Text style={styles.statLabel}>Incoming Deliveries</Text>
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
                  
                  <Text style={styles.itemsCount}>
                    {item.items_count || '?'} items
                  </Text>
                </TouchableOpacity>
              )}
            />
          )}
        </View>

        {/* Actions */}
        <View style={styles.actionsContainer}>
          <TouchableOpacity 
            style={styles.actionButton}
            onPress={() => navigation.navigate('Catalog')}
          >
            <Text style={styles.actionButtonText}>Browse Catalog</Text>
          </TouchableOpacity>
          
          <TouchableOpacity 
            style={styles.actionButton}
            onPress={() => navigation.navigate('Cart')}
          >
            <Text style={styles.actionButtonText}>View Cart</Text>
          </TouchableOpacity>
          
          <TouchableOpacity 
            style={styles.actionButton}
            onPress={() => navigation.navigate('Profile')}
          >
            <Text style={styles.actionButtonText}>My Profile</Text>
          </TouchableOpacity>
          
          <TouchableOpacity 
            style={[styles.actionButton, { backgroundColor: '#e74c3c' }]}
            onPress={handleLogout}
          >
            <Text style={styles.actionButtonText}>Logout</Text>
          </TouchableOpacity>
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
  statsContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    padding: 15,
  },
  statCard: {
    flex: 1,
    backgroundColor: '#fff',
    padding: 15,
    borderRadius: 10,
    marginHorizontal: 5,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  statValue: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
  },
  statLabel: {
    fontSize: 12,
    color: '#666',
    marginTop: 5,
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
  actionsContainer: {
    padding: 15,
    marginBottom: 20,
  },
  actionButton: {
    backgroundColor: '#0066cc',
    paddingVertical: 12,
    borderRadius: 5,
    alignItems: 'center',
    marginBottom: 10,
  },
  actionButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
});

export default DashboardScreen;