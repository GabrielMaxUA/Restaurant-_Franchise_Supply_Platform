import React from 'react';
import { createStackNavigator } from '@react-navigation/stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Text, View, StyleSheet, ActivityIndicator } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Screens
import LoginScreen from '../screens/LoginScreen';
import DashboardScreen from '../screens/DashboardScreen';
import CatalogScreen from '../screens/CatalogScreen';
import CartScreen from '../screens/CartScreen';
import OrderHistoryScreen from '../screens/OrderHistoryScreen';
import OrderDetailsScreen from '../screens/OrderDetailsScreen';
import ProfileScreen from '../screens/ProfileScreen';
import TestScreen from '../screens/TestScreen';
import ApiTestScreen from '../screens/ApiTestScreen';

// Create the navigators
const Stack = createStackNavigator();
const Tab = createBottomTabNavigator();

// Custom tab bar icon component
const TabBarIcon = ({ focused, name }) => (
  <View style={styles.tabIconContainer}>
    <Text style={[styles.tabIcon, focused && styles.tabIconFocused]}>
      {name === 'Home' ? '🏠' : 
       name === 'Catalog' ? '📋' : 
       name === 'Cart' ? '🛒' : 
       name === 'Orders' ? '📦' : 
       name === 'Profile' ? '👤' : '⚙️'}
    </Text>
    <Text style={[styles.tabLabel, focused && styles.tabLabelFocused]}>
      {name}
    </Text>
  </View>
);

// Tab Navigator (Main App)
const AppTabs = () => {
  return (
    <Tab.Navigator
      screenOptions={{
        tabBarActiveTintColor: '#0066cc',
        tabBarInactiveTintColor: '#999',
        tabBarStyle: {
          backgroundColor: '#fff',
          borderTopWidth: 1,
          borderTopColor: '#eee',
          height: 60,
          paddingBottom: 5,
          paddingTop: 5,
        },
        headerShown: false,
      }}
    >
      <Tab.Screen 
        name="Home" 
        component={DashboardStack}
        options={{
          tabBarIcon: ({ focused }) => <TabBarIcon focused={focused} name="Home" />,
        }}
      />
      <Tab.Screen 
        name="Catalog" 
        component={CatalogStack}
        options={{
          tabBarIcon: ({ focused }) => <TabBarIcon focused={focused} name="Catalog" />,
        }}
      />
      <Tab.Screen 
        name="Cart" 
        component={CartStack}
        options={{
          tabBarIcon: ({ focused }) => <TabBarIcon focused={focused} name="Cart" />,
        }}
      />
      <Tab.Screen 
        name="Orders" 
        component={OrdersStack}
        options={{
          tabBarIcon: ({ focused }) => <TabBarIcon focused={focused} name="Orders" />,
        }}
      />
      <Tab.Screen 
        name="Profile" 
        component={ProfileStack}
        options={{
          tabBarIcon: ({ focused }) => <TabBarIcon focused={focused} name="Profile" />,
        }}
      />
    </Tab.Navigator>
  );
};

// Stack navigator for Dashboard section
const DashboardStack = () => (
  <Stack.Navigator>
    <Stack.Screen 
      name="DashboardScreen" 
      component={DashboardScreen} 
      options={{ 
        title: 'Dashboard',
        headerStyle: {
          backgroundColor: '#0066cc',
        },
        headerTintColor: '#fff',
        headerTitleStyle: {
          fontWeight: 'bold',
        },
      }}
    />
    <Stack.Screen 
      name="OrderDetails" 
      component={OrderDetailsScreen}
      options={{ 
        title: 'Order Details',
        headerStyle: {
          backgroundColor: '#0066cc',
        },
        headerTintColor: '#fff',
        headerTitleStyle: {
          fontWeight: 'bold',
        },
      }}
    />
    <Stack.Screen 
      name="OrderHistory" 
      component={OrderHistoryScreen}
      options={{ 
        title: 'Order History',
        headerStyle: {
          backgroundColor: '#0066cc',
        },
        headerTintColor: '#fff',
        headerTitleStyle: {
          fontWeight: 'bold',
        },
      }}
    />
  </Stack.Navigator>
);

// Stack navigator for Catalog section
const CatalogStack = () => (
  <Stack.Navigator>
    <Stack.Screen 
      name="CatalogScreen" 
      component={CatalogScreen}
      options={{ 
        title: 'Product Catalog',
        headerStyle: {
          backgroundColor: '#0066cc',
        },
        headerTintColor: '#fff',
        headerTitleStyle: {
          fontWeight: 'bold',
        },
      }}
    />
  </Stack.Navigator>
);

// Stack navigator for Cart section
const CartStack = () => (
  <Stack.Navigator>
    <Stack.Screen 
      name="CartScreen" 
      component={CartScreen}
      options={{ 
        title: 'Shopping Cart',
        headerStyle: {
          backgroundColor: '#0066cc',
        },
        headerTintColor: '#fff',
        headerTitleStyle: {
          fontWeight: 'bold',
        },
      }}
    />
  </Stack.Navigator>
);

// Stack navigator for Orders section
const OrdersStack = () => (
  <Stack.Navigator>
    <Stack.Screen 
      name="OrderHistoryScreen" 
      component={OrderHistoryScreen}
      options={{ 
        title: 'Order History',
        headerStyle: {
          backgroundColor: '#0066cc',
        },
        headerTintColor: '#fff',
        headerTitleStyle: {
          fontWeight: 'bold',
        },
      }}
    />
    <Stack.Screen 
      name="OrderDetails" 
      component={OrderDetailsScreen}
      options={{ 
        title: 'Order Details',
        headerStyle: {
          backgroundColor: '#0066cc',
        },
        headerTintColor: '#fff',
        headerTitleStyle: {
          fontWeight: 'bold',
        },
      }}
    />
  </Stack.Navigator>
);

// Stack navigator for Profile section
const ProfileStack = () => (
  <Stack.Navigator>
    <Stack.Screen 
      name="ProfileScreen" 
      component={ProfileScreen}
      options={{ 
        title: 'My Profile',
        headerStyle: {
          backgroundColor: '#0066cc',
        },
        headerTintColor: '#fff',
        headerTitleStyle: {
          fontWeight: 'bold',
        },
      }}
    />
  </Stack.Navigator>
);

// Root navigator
const AppNavigator = () => {
  const [userToken, setUserToken] = React.useState(null);
  const [isLoading, setIsLoading] = React.useState(true);

  // Check authentication state on component mount
  React.useEffect(() => {
    const checkAuth = async () => {
      try {
        const token = await AsyncStorage.getItem('userToken');
        setUserToken(token);
      } catch (e) {
        console.error('Failed to get token from storage:', e);
      } finally {
        setIsLoading(false);
      }
    };

    checkAuth();
  }, []);

  // Subscribe to auth state changes
  React.useEffect(() => {
    const checkAuthInterval = setInterval(async () => {
      try {
        const token = await AsyncStorage.getItem('userToken');
        setUserToken(token);
      } catch (e) {
        console.error('Failed to get token from storage:', e);
      }
    }, 2000); // Check every 2 seconds

    return () => clearInterval(checkAuthInterval);
  }, []);

  if (isLoading) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
        <Text>Loading...</Text>
      </View>
    );
  }

  return (
    <Stack.Navigator screenOptions={{ headerShown: false }}>
      {userToken == null ? (
        // No token found, user isn't signed in
        <>
          <Stack.Screen name="Login" component={LoginScreen} />
          <Stack.Screen 
            name="TestAPI" 
            component={TestScreen} 
            options={{ 
              headerShown: true,
              title: 'Simple API Test',
              headerStyle: { backgroundColor: '#0066cc' },
              headerTintColor: '#fff',
              headerTitleStyle: { fontWeight: 'bold' },
            }}
          />
          <Stack.Screen 
            name="ComprehensiveAPITest" 
            component={ApiTestScreen} 
            options={{ 
              headerShown: true,
              title: 'Comprehensive API Test',
              headerStyle: { backgroundColor: '#0066cc' },
              headerTintColor: '#fff',
              headerTitleStyle: { fontWeight: 'bold' },
            }}
          />
        </>
      ) : (
        // User is signed in
        <Stack.Screen name="Main" component={AppTabs} />
      )}
    </Stack.Navigator>
  );
};

const styles = StyleSheet.create({
  tabIconContainer: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  tabIcon: {
    fontSize: 20,
    marginBottom: 2,
  },
  tabIconFocused: {
    color: '#0066cc',
  },
  tabLabel: {
    fontSize: 10,
    color: '#999',
  },
  tabLabelFocused: {
    color: '#0066cc',
    fontWeight: 'bold',
  },
});

export default AppNavigator;