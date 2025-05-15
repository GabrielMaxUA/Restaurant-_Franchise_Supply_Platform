// src/navigation/AppNavigator.js
import React from 'react';
import { createDrawerNavigator } from '@react-navigation/drawer';
import { createStackNavigator } from '@react-navigation/stack';
import { FontAwesome5 } from 'react-native-vector-icons'; // Add this import for icons
import CustomDrawer from '../components/CustomDrawer';
import DashboardScreen from '../screens/DashboardScreen';
import CatalogScreen from '../screens/CatalogScreen';
import CartScreen from '../screens/CartScreen';
import PendingOrdersScreen from '../screens/PendingOrdersScreen';
import OrderHistoryScreen from '../screens/OrderHistoryScreen';
import ProfileScreen from '../screens/ProfileScreen';
import OrderDetailsScreen from '../screens/OrderDetailsScreen';
import ProductDetailsScreen from '../screens/ProductDetailsScreen';
import CheckoutScreen from '../screens/CheckoutScreen';
import ApiTestScreen from '../screens/ApiTestScreen';
const Drawer = createDrawerNavigator();
const Stack = createStackNavigator();

// Main stack for screens that aren't in the drawer
const MainStack = () => {
  return (
    <Stack.Navigator
      screenOptions={{
        headerShown: false,
      }}
    >
      
      <Stack.Screen name="ApiTest" component={ApiTestScreen} />
      <Stack.Screen name="DrawerScreens" component={DrawerScreens} />
      <Stack.Screen name="OrderDetails" component={OrderDetailsScreen} />
      <Stack.Screen name="ProductDetails" component={ProductDetailsScreen} />
      <Stack.Screen name="Checkout" component={CheckoutScreen} />
    </Stack.Navigator>
  );
};

// Drawer navigator for main app sections
const DrawerScreens = () => {
  return (
    <Drawer.Navigator
      drawerContent={(props) => <CustomDrawer {...props} />}
      screenOptions={{
        headerShown: false,
        drawerStyle: {
          backgroundColor: '#343a40',
          width: 280,
        },
        drawerActiveTintColor: '#fff',
        drawerInactiveTintColor: 'rgba(255, 255, 255, 0.8)',
        drawerLabelStyle: {
          marginLeft: -20, // To align with icons better
        },
      }}
    >
      <Drawer.Screen 
        name="Dashboard" 
        component={DashboardScreen} 
        options={{
          drawerIcon: ({color}) => (
            <FontAwesome5 name="tachometer-alt" size={16} color={color} />
          ),
        }}
      />
      <Drawer.Screen 
        name="Catalog" 
        component={CatalogScreen} 
        options={{
          drawerIcon: ({color}) => (
            <FontAwesome5 name="box" size={16} color={color} />
          ),
        }}
      />
      <Drawer.Screen 
        name="Cart" 
        component={CartScreen} 
        options={{
          drawerIcon: ({color}) => (
            <FontAwesome5 name="shopping-basket" size={16} color={color} />
          ),
        }}
      />
      <Drawer.Screen 
        name="PendingOrders" 
        component={PendingOrdersScreen} 
        options={{
          title: "Pending Orders",
          drawerIcon: ({color}) => (
            <FontAwesome5 name="clock" size={16} color={color} />
          ),
        }}
      />
      <Drawer.Screen 
        name="OrderHistory" 
        component={OrderHistoryScreen} 
        options={{
          title: "Order History",
          drawerIcon: ({color}) => (
            <FontAwesome5 name="history" size={16} color={color} />
          ),
        }}
      />
      <Drawer.Screen 
        name="Profile" 
        component={ProfileScreen} 
        options={{
          drawerIcon: ({color}) => (
            <FontAwesome5 name="user" size={16} color={color} />
          ),
        }}
      />
    </Drawer.Navigator>
  );
};

const AppNavigator = () => {
  return <MainStack />;
};

export default AppNavigator;