import HeaderBar from '../components/HeaderBar';
import { useNavigation } from '@react-navigation/native';
import { FranchiseeLayout } from '../components/FranchiseeLayout';
import React, { useEffect, useState } from 'react';
import { View, Text, ScrollView, StyleSheet } from 'react-native';
import { getDashboardData } from '../services/api';

  const [showWelcome, setShowWelcome] = useState(false);
  const [user, setUser] = useState(null);
  const DashboardScreen = () => {
  const [stats, setStats] = useState(null);
  const [charts, setCharts] = useState(null);
  const [recentOrders, setRecentOrders] = useState([]);
  const [popularProducts, setPopularProducts] = useState([]);
  const navigation = useNavigation();

  useEffect(() => {
    const fetchData = async () => {
      const result = await getDashboardData();
      if (result.success) {
        setStats(result.data.stats);
        setCharts(result.data.charts);
        setRecentOrders(result.data.recent_orders);
        setPopularProducts(result.data.popular_products);
        setUser(result.data.user); // assuming API includes user info
        const alreadyWelcomed = await AsyncStorage.getItem('welcomed');
        if (!alreadyWelcomed) {
          setShowWelcome(true);
          await AsyncStorage.setItem('welcomed', 'yes');
        }
      }
    };
    fetchData();
  }, []);

  if (!stats) {
    return (
      <View style={styles.centered}>
        <Text>Loading dashboard...</Text>
      </View>
    );
  }

  return (
    <FranchiseeLayout title="Dashboard">
  {showWelcome && (
    <View style={styles.welcomeBox}>
      <Text style={styles.welcomeText}>👋 Welcome back, {user?.username || 'Franchisee'}!</Text>
    </View>
  )}

  <ScrollView style={styles.container}>
    <Text style={styles.header}>Franchisee Dashboard</Text>

    <View style={styles.section}>
      <Text style={styles.sectionTitle}>Spending This Month</Text>
      <Text style={styles.statValue}>${parseFloat(stats.monthly_spending).toFixed(2)}</Text>
    </View>

    <View style={styles.section}>
      <Text style={styles.sectionTitle}>Pending Orders</Text>
      <Text style={styles.statValue}>{stats.pending_orders}</Text>
    </View>

    <View style={styles.section}>
      <Text style={styles.sectionTitle}>Popular Products</Text>
      {popularProducts.map((product) => (
        <Text key={product.id}>• {product.name} - ${product.price}</Text>
      ))}
    </View>

    <View style={styles.section}>
      <Text style={styles.sectionTitle}>Recent Orders</Text>
      {recentOrders.map((order) => (
        <Text key={order.id}>Order #{order.order_number} - {order.items_count} items</Text>
      ))}
    </View>
  </ScrollView>
</FranchiseeLayout>

  );
};

const styles = StyleSheet.create({
  container: { padding: 20 },
  header: { fontSize: 24, fontWeight: 'bold', marginBottom: 20 },
  section: { marginBottom: 20 },
  sectionTitle: { fontSize: 18, fontWeight: '600', marginBottom: 8 },
  statValue: { fontSize: 20, fontWeight: 'bold', color: '#0066cc' },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },
});

export default DashboardScreen;
