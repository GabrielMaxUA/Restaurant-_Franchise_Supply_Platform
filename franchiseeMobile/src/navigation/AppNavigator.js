// import React from 'react';
// import { createStackNavigator } from '@react-navigation/stack';
// import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
// import { Text, View, StyleSheet, ActivityIndicator } from 'react-native';
// import AsyncStorage from '@react-native-async-storage/async-storage';

// // Screens
// import LoginScreen from '../screens/LoginScreen';
// import DashboardScreen from '../screens/DashboardScreen';
// import CatalogScreen from '../screens/CatalogScreen';
// import CartScreen from '../screens/CartScreen';
// import OrderHistoryScreen from '../screens/OrderHistoryScreen';
// import OrderDetailsScreen from '../screens/OrderDetailsScreen';
// import ProfileScreen from '../screens/ProfileScreen';

// // Create the navigators
// const Stack = createStackNavigator();
// const Tab = createBottomTabNavigator();

// // Custom tab bar icon component
// const TabBarIcon = ({ focused, name }) => (
//   <View style={styles.tabIconContainer}>
//     <Text style={[styles.tabIcon, focused && styles.tabIconFocused]}>
//       {name === 'Home' ? '🏠' : 
//        name === 'Catalog' ? '📋' : 
//        name === 'Cart' ? '🛒' : 
//        name === 'Orders' ? '📦' : 
//        name === 'Profile' ? '👤' : '⚙️'}
//     </Text>
//     <Text style={[styles.tabLabel, focused && styles.tabLabelFocused]}>
//       {name}
//     </Text>
//   </View>
// );

// // Tab Navigator (Main App)
// const AppTabs = () => {
//   return (
//     <Tab.Navigator
//       screenOptions={{
//         tabBarActiveTintColor: '#0066cc',
//         tabBarInactiveTintColor: '#999',
//         tabBarStyle: {
//           backgroundColor: '#fff',
//           borderTopWidth: 1,
//           borderTopColor: '#eee',
//           height: 60,
//           paddingBottom: 5,
//           paddingTop: 5,
//         },
//         headerShown: false,
//       }}
//     >
//       <Tab.Screen 
//         name="Home" 
//         component={DashboardStack}
//         options={{
//           tabBarIcon: ({ focused }) => <TabBarIcon focused={focused} name="Home" />,
//         }}
//       />
//       <Tab.Screen 
//         name="Catalog" 
//         component={CatalogStack}
//         options={{
//           tabBarIcon: ({ focused }) => <TabBarIcon focused={focused} name="Catalog" />,
//         }}
//       />
//       <Tab.Screen 
//         name="Cart" 
//         component={CartStack}
//         options={{
//           tabBarIcon: ({ focused }) => <TabBarIcon focused={focused} name="Cart" />,
//         }}
//       />
//       <Tab.Screen 
//         name="Orders" 
//         component={OrdersStack}
//         options={{
//           tabBarIcon: ({ focused }) => <TabBarIcon focused={focused} name="Orders" />,
//         }}
//       />
//       <Tab.Screen 
//         name="Profile" 
//         component={ProfileStack}
//         options={{
//           tabBarIcon: ({ focused }) => <TabBarIcon focused={focused} name="Profile" />,
//         }}
//       />
//     </Tab.Navigator>
//   );
// };

// // Header options that match the web app style
// const headerOptions = {
//   headerStyle: {
//     backgroundColor: '#0066cc',
//   },
//   headerTintColor: '#fff',
//   headerTitleStyle: {
//     fontWeight: 'bold',
//   },
// };

// // Stack navigator for Dashboard section
// const DashboardStack = () => (
//   <Stack.Navigator>
//     <Stack.Screen 
//       name="DashboardScreen" 
//       component={DashboardScreen} 
//       options={{ 
//         title: 'Dashboard',
//         ...headerOptions
//       }}
//     />
//     <Stack.Screen 
//       name="OrderDetails" 
//       component={OrderDetailsScreen}
//       options={{ 
//         title: 'Order Details',
//         ...headerOptions
//       }}
//     />
//     <Stack.Screen 
//       name="OrderHistory" 
//       component={OrderHistoryScreen}
//       options={{ 
//         title: 'Order History',
//         ...headerOptions
//       }}
//     />
//   </Stack.Navigator>
// );

// // Stack navigator for Catalog section
// const CatalogStack = () => (
//   <Stack.Navigator>
//     <Stack.Screen 
//       name="CatalogScreen" 
//       component={CatalogScreen}
//       options={{ 
//         title: 'Product Catalog',
//         ...headerOptions
//       }}
//     />
//   </Stack.Navigator>
// );

// // Stack navigator for Cart section
// const CartStack = () => (
//   <Stack.Navigator>
//     <Stack.Screen 
//       name="CartScreen" 
//       component={CartScreen}
//       options={{ 
//         title: 'Shopping Cart',
//         ...headerOptions
//       }}
//     />
//   </Stack.Navigator>
// );

// // Stack navigator for Orders section
// const OrdersStack = () => (
//   <Stack.Navigator>
//     <Stack.Screen 
//       name="OrderHistoryScreen" 
//       component={OrderHistoryScreen}
//       options={{ 
//         title: 'Order History',
//         ...headerOptions
//       }}
//     />
//     <Stack.Screen 
//       name="OrderDetails" 
//       component={OrderDetailsScreen}
//       options={{ 
//         title: 'Order Details',
//         ...headerOptions
//       }}
//     />
//   </Stack.Navigator>
// );

// // Stack navigator for Profile section
// const ProfileStack = () => (
//   <Stack.Navigator>
//     <Stack.Screen 
//       name="ProfileScreen" 
//       component={ProfileScreen}
//       options={{ 
//         title: 'My Profile',
//         ...headerOptions
//       }}
//     />
//   </Stack.Navigator>
// );

// // Root navigator
// const AppNavigator = () => {
//   const [userToken, setUserToken] = React.useState(null);
//   const [isLoading, setIsLoading] = React.useState(true);

//   // Check authentication state on component mount
//   React.useEffect(() => {
//     const checkAuth = async () => {
//       try {
//         const token = await AsyncStorage.getItem('userToken');
//         setUserToken(token);
//       } catch (e) {
//         console.error('Failed to get token from storage:', e);
//       } finally {
//         setIsLoading(false);
//       }
//     };

//     checkAuth();
//   }, []);

//   // Subscribe to auth state changes
//   React.useEffect(() => {
//     const checkAuthInterval = setInterval(async () => {
//       try {
//         const token = await AsyncStorage.getItem('userToken');
//         setUserToken(token);
//       } catch (e) {
//         console.error('Failed to get token from storage:', e);
//       }
//     }, 2000); // Check every 2 seconds

//     return () => clearInterval(checkAuthInterval);
//   }, []);

//   if (isLoading) {
//     return (
//       <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: '#f5f5f5' }}>
//         <ActivityIndicator size="large" color="#0066cc" />
//         <Text style={{ marginTop: 10, color: '#666' }}>Loading...</Text>
//       </View>
//     );
//   }

//   return (
//     <Stack.Navigator screenOptions={{ headerShown: false }}>
//       {userToken == null ? (
//         // No token found, user isn't signed in
//         <Stack.Screen name="Login" component={LoginScreen} />
//       ) : (
//         // User is signed in
//         <Stack.Screen name="Main" component={AppTabs} />
//       )}
//     </Stack.Navigator>
//   );
// };

// const styles = StyleSheet.create({
//   tabIconContainer: {
//     alignItems: 'center',
//     justifyContent: 'center',
//   },
//   tabIcon: {
//     fontSize: 20,
//     marginBottom: 2,
//   },
//   tabIconFocused: {
//     color: '#0066cc',
//   },
//   tabLabel: {
//     fontSize: 10,
//     color: '#999',
//   },
//   tabLabelFocused: {
//     color: '#0066cc',
//     fontWeight: 'bold',
//   },
// });

// export default AppNavigator;

import React, { useEffect, useState } from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { NavigationContainer } from '@react-navigation/native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import LoginScreen from '../screens/LoginScreen';
import DashboardScreen from '../screens/DashboardScreen';
import { View, Text, ActivityIndicator } from 'react-native';

const Stack = createNativeStackNavigator();

const AppNavigator = () => {
  // Default to not authenticated until we check
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  
  const checkToken = async () => {
    try {
      console.log('⚡ Checking authentication token...');
      const token = await AsyncStorage.getItem('userToken');
      console.log('🔑 Token exists:', !!token);
      if (token) {
        console.log('🔑 Token value (first 10 chars):', token.substring(0, 10) + '...');
        setIsAuthenticated(true);
      } else {
        console.log('⚠️ No auth token found');
        setIsAuthenticated(false);
      }
    } catch (error) {
      console.error('❌ Error checking token:', error);
      setIsAuthenticated(false);
    }
  };

  const [isLoading, setIsLoading] = useState(true);

  // Function to check authentication 
  const checkAuthentication = async () => {
    try {
      console.log('⚡ Checking authentication status...');
      const token = await AsyncStorage.getItem('userToken');
      
      if (token) {
        console.log('✅ Valid token found, setting authenticated state');
        setIsAuthenticated(true);
      } else {
        console.log('❌ No valid token found, setting unauthenticated state');
        setIsAuthenticated(false);
      }
    } catch (error) {
      console.error('❌ Error checking authentication:', error);
      setIsAuthenticated(false);
    } finally {
      // Always finish loading regardless of result
      setIsLoading(false);
    }
  };

  useEffect(() => {
    // Initial authentication check
    const initAuth = async () => {
      try {
        // Clear token on startup (can be removed in production)
        console.log('🧹 Clearing auth tokens on app start...');
        await AsyncStorage.removeItem('userToken');
        console.log('✅ Token cleared successfully');
        
        // Set initial state
        setIsAuthenticated(false);
        
        // Brief delay to ensure UI renders properly
        setTimeout(() => {
          setIsLoading(false);
        }, 500);
      } catch (error) {
        console.error('❌ Error during initialization:', error);
        // Ensure we still finish loading even on error
        setIsLoading(false);
      }
    };
    
    // Run the initialization
    initAuth();
    
    // Instead of using a storage listener (which is not available in all versions),
    // use a regular interval check but with a much longer interval to avoid performance issues
    const checkIntervalId = setInterval(() => {
      // Check for auth token changes without constantly refreshing
      checkAuthentication();
    }, 10000); // Check every 10 seconds
    
    // Clean up interval on unmount
    return () => {
      clearInterval(checkIntervalId);
    };
  }, []);

  // Show loading screen while checking authentication
  if (isLoading) {
    console.log('🔄 Auth check in progress...');
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: '#f5f5f5' }}>
        <ActivityIndicator size="large" color="#0066cc" />
        <Text style={{ marginTop: 10, color: '#666' }}>Loading...</Text>
      </View>
    );
  }

  console.log('🚀 Rendering Navigator, Authenticated:', isAuthenticated);

  // Simplify our navigation structure to be more predictable
  return (
    <NavigationContainer>
      <Stack.Navigator 
        initialRouteName="Login"
        screenOptions={{ headerShown: false }}
      >
        {/* Always include both screens in the navigator */}
        <Stack.Screen 
          name="Login" 
          component={LoginScreen} 
        />
        <Stack.Screen 
          name="Dashboard" 
          component={DashboardScreen} 
        />
        
        {/* You can add more authenticated screens here */}
      </Stack.Navigator>
    </NavigationContainer>
  );
};

export default AppNavigator;
