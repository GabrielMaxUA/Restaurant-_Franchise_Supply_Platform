import React, { useState, useEffect, useContext } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  SafeAreaView,
  ActivityIndicator,
} from 'react-native';
import { FontAwesome5 } from 'react-native-vector-icons';
import Header from '../components/Header';
import { AuthContext } from '../contexts/AuthContext';
import { dashboardService } from '../services/api';

const StatCard = ({ icon, title, value, change, subtitle, iconColor, iconBgColor }) => {
  return (
    <View style={styles.statCard}>
      <View style={[styles.statCardIcon, { backgroundColor: iconBgColor }]}>
        <FontAwesome5 name={icon} size={18} color={iconColor} />
      </View>
      <Text style={styles.statCardTitle}>{title}</Text>
      <View style={styles.statCardValueRow}>
        <Text style={styles.statCardValue}>{value}</Text>
        {change !== null && (
          <Text style={[
            styles.statCardChange,
            change > 0 ? styles.statCardChangePositive : 
            change < 0 ? styles.statCardChangeNegative : styles.statCardChangeNeutral
          ]}>
            <FontAwesome5 
              name={change > 0 ? 'arrow-up' : change < 0 ? 'arrow-down' : 'minus'} 
              size={10} 
            /> {Math.abs(change)}%
          </Text>
        )}
      </View>
      <Text style={styles.statCardSubtitle}>{subtitle}</Text>
    </View>
  );
};

const DashboardScreen = ({ navigation }) => {
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState({
    pending_orders: 0,
    monthly_spending: 0,
    spending_change: 0,
    low_stock_items: 0,
    incoming_deliveries: 0,
    pending_orders_change: 0
  });
  const [recentOrders, setRecentOrders] = useState([]);
  const [popularProducts, setPopularProducts] = useState([]);
  
  const { state } = useContext(AuthContext);
  
  // Format currency
  const formatCurrency = (value) => {
    return '$' + parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
  };
  
  useEffect(() => {
    const loadDashboardData = async () => {
      try {
        setLoading(true);
        const response = await dashboardService.getStats();
        if (response) {
          setStats(response);
        }
        
        const ordersResponse = await dashboardService.getRecentOrders();
        if (ordersResponse) {
          setRecentOrders(ordersResponse);
        }
        
        const productsResponse = await dashboardService.getPopularProducts();
        if (productsResponse) {
          setPopularProducts(productsResponse);
        }
      } catch (error) {
        console.error('Error loading dashboard data:', error);
      } finally {
        setLoading(false);
      }
    };
    
    loadDashboardData();
  }, []);
  
  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <Header title="Dashboard" />
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#4e73df" />
          <Text style={styles.loadingText}>Loading dashboard data...</Text>
        </View>
      </SafeAreaView>
    );
  }
  
  return (
    <SafeAreaView style={styles.container}>
      <Header title="Dashboard" />
      
      <ScrollView style={styles.scrollView}>
        <View style={styles.welcomeBanner}>
          <Text style={styles.welcomeTitle}>
            Welcome back, {state.userProfile?.username || 'Franchisee'}!
          </Text>
          <Text style={styles.welcomeMessage}>
            Check your dashboard for insights about your restaurant supply status.
          </Text>
        </View>
        
        <View style={styles.statsGrid}>
          <StatCard 
            icon="dollar-sign" 
            title="Monthly Spending" 
            value={formatCurrency(stats.monthly_spending)}
            change={stats.spending_change}
            subtitle="Since last month"
            iconColor="#007bff"
            iconBgColor="rgba(0, 123, 255, 0.1)"
          />
          
          <StatCard 
            icon="shopping-cart" 
            title="Pending Orders" 
            value={stats.pending_orders}
            change={stats.pending_orders_change}
            subtitle="Since last month"
            iconColor="#28a745"
            iconBgColor="rgba(40, 167, 69, 0.1)"
          />
          
          <StatCard 
            icon="exclamation-triangle" 
            title="Low Stock Items" 
            value={stats.low_stock_items}
            change={null}
            subtitle="Items needing reorder"
            iconColor="#ffc107"
            iconBgColor="rgba(255, 193, 7, 0.1)"
          />
          
          <StatCard 
            icon="truck" 
            title="Incoming Deliveries" 
            value={stats.incoming_deliveries}
            change={null}
            subtitle="Expected this week"
            iconColor="#17a2b8"
            iconBgColor="rgba(23, 162, 184, 0.1)"
          />
        </View>
        
        <View style={styles.sectionCard}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Recent Orders</Text>
            <TouchableOpacity 
              style={styles.viewAllButton}
              onPress={() => navigation.navigate('PendingOrders')}
            >
              <Text style={styles.viewAllButtonText}>View All</Text>
            </TouchableOpacity>
          </View>
          
          {recentOrders.length === 0 ? (
            <Text style={styles.emptyStateText}>No recent orders found.</Text>
          ) : (
            <View style={styles.ordersList}>
              {recentOrders.map(order => (
                <TouchableOpacity 
                  key={order.id} 
                  style={styles.orderItem}
                  onPress={() => navigation.navigate('OrderDetails', { orderId: order.id })}
                >
                  <View style={styles.orderItemMain}>
                    <View>
                      <Text style={styles.orderNumber}>{order.order_number}</Text>
                      <Text style={styles.orderDate}>{order.formatted_date || order.created_at}</Text>
                    </View>
                    <View style={styles.orderSummary}>
                      <Text style={styles.orderItems}>{order.items_count} items</Text>
                      <Text style={styles.orderTotal}>{formatCurrency(order.total)}</Text>
                    </View>
                  </View>
                </TouchableOpacity>
              ))}
            </View>
          )}
        </View>
        
        <View style={styles.quickActions}>
          <Text style={styles.quickActionsTitle}>Quick Actions</Text>
          <View style={styles.quickActionsGrid}>
            <TouchableOpacity 
              style={[styles.quickActionButton, styles.quickActionPrimary]}
              onPress={() => navigation.navigate('Catalog')}
            >
              <FontAwesome5 name="shopping-cart" size={24} color="#fff" style={styles.quickActionIcon} />
              <Text style={styles.quickActionText}>Place Order</Text>
            </TouchableOpacity>
            
            <TouchableOpacity 
              style={[styles.quickActionButton, styles.quickActionSecondary]}
              onPress={() => navigation.navigate('PendingOrders')}
            >
              <FontAwesome5 name="shipping-fast" size={24} color="#17a2b8" style={styles.quickActionIcon} />
              <Text style={styles.quickActionText}>Track Orders</Text>
            </TouchableOpacity>
            
            <TouchableOpacity 
              style={[styles.quickActionButton, styles.quickActionSecondary]}
              onPress={() => navigation.navigate('OrderHistory')}
            >
              <FontAwesome5 name="history" size={24} color="#6c757d" style={styles.quickActionIcon} />
              <Text style={styles.quickActionText}>Order History</Text>
            </TouchableOpacity>
          </View>
        </View>
        
        <View style={styles.bottomPadding} />
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
    marginTop: 12,
    fontSize: 16,
    color: '#666',
  },
  welcomeBanner: {
    backgroundColor: '#d4edda',
    margin: 16,
    padding: 16,
    borderRadius: 8,
    borderLeftWidth: 3,
    borderLeftColor: '#28a745',
  },
  welcomeTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 8,
    color: '#333',
  },
  welcomeMessage: {
    color: '#555',
    fontSize: 14,
  },
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginHorizontal: 16,
    marginBottom: 16,
    justifyContent: 'space-between',
  },
  statCard: {
    backgroundColor: '#fff',
    width: '48.5%',
    marginBottom: 12,
    padding: 16,
    borderRadius: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  statCardIcon: {
    width: 40,
    height: 40,
    borderRadius: 20,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  statCardTitle: {
    fontSize: 14,
    color: '#555',
    marginBottom: 8,
  },
  statCardValueRow: {
    flexDirection: 'row',
    alignItems: 'baseline',
    marginBottom: 4,
  },
  statCardValue: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginRight: 8,
  },
  statCardChange: {
    fontSize: 12,
    fontWeight: '500',
  },
  statCardChangePositive: {
    color: '#28a745',
  },
  statCardChangeNegative: {
    color: '#dc3545',
  },
  statCardChangeNeutral: {
    color: '#6c757d',
  },
  statCardSubtitle: {
    fontSize: 12,
    color: '#777',
  },
  sectionCard: {
    backgroundColor: '#fff',
    marginHorizontal: 16,
    marginBottom: 16,
    borderRadius: 8,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  viewAllButton: {
    paddingVertical: 4,
    paddingHorizontal: 10,
    borderWidth: 1,
    borderColor: '#28a745',
    borderRadius: 4,
  },
  viewAllButtonText: {
    fontSize: 12,
    color: '#28a745',
  },
  emptyStateText: {
    textAlign: 'center',
    color: '#777',
    padding: 20,
  },
  ordersList: {
    marginTop: 8,
  },
  orderItem: {
    borderWidth: 1,
    borderColor: '#e0e0e0',
    borderRadius: 8,
    marginBottom: 12,
    backgroundColor: '#fff',
  },
  orderItemMain: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    padding: 12,
  },
  orderNumber: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 4,
  },
  orderDate: {
    fontSize: 12,
    color: '#666',
  },
  orderSummary: {
    alignItems: 'flex-end',
  },
  orderItems: {
    fontSize: 12,
    color: '#666',
    marginBottom: 4,
  },
  orderTotal: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#28a745',
  },
  quickActions: {
    marginHorizontal: 16,
    marginBottom: 16,
  },
  quickActionsTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 12,
  },
  quickActionsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  quickActionButton: {
    width: '48.5%',
    padding: 16,
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  quickActionPrimary: {
    backgroundColor: '#28a745',
  },
  quickActionSecondary: {
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#e0e0e0',
  },
  quickActionIcon: {
    marginBottom: 8,
  },
  quickActionText: {
    fontWeight: '500',
    color: '#333',
  },
  bottomPadding: {
    height: 20,
  },
});

export default DashboardScreen;