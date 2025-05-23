import React, { useEffect, useState } from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { NavigationContainer } from '@react-navigation/native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { View, Text, ActivityIndicator, Platform } from 'react-native';
import { setupTokenRefreshInterval, hasValidToken } from '../services/authService';
import { navigationRef } from './NavigationService';
// Import screens
import LoginScreen from '../screens/LoginScreen';
import DashboardScreen from '../screens/DashboardScreen';
import ProfileEditScreen from '../screens/ProfileEditScreen';
import ProfileViewScreen from '../screens/ProfileViewScreen';
import ChangePasswordScreen from '../screens/ChangePasswordScreen';
import OrdersScreen from '../screens/OrdersScreen';
import OrderHistoryScreen from '../screens/OrderHistoryScreen';
import CartScreen from '../screens/CartScreen';
import CatalogScreen from '../screens/CatalogScreen';
import ProductDetailScreen from '../screens/ProductDetailScreen';
import OrderDetailsScreen from '../screens/OrderDetailsScreen';
import CheckoutScreen from '../screens/CheckoutScreen';

const Stack = createNativeStackNavigator();

const AppNavigator = () => {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  const checkAuthentication = async () => {
    try {
      // Use the auth service to check if we have a valid token
      const isValid = await hasValidToken();
      setIsAuthenticated(isValid);
    } catch (error) {
      console.error('Auth check error:', error);
      setIsAuthenticated(false);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    // Initial authentication check
    checkAuthentication();

    // Set up token refresh interval (runs every 5 minutes)
    const cleanupTokenRefresh = setupTokenRefreshInterval(5);

    // Also periodically check authentication status (every 30 seconds)
    const interval = setInterval(() => {
      checkAuthentication();
    }, 30000);

    // Clean up on unmount
    return () => {
      clearInterval(interval);
      cleanupTokenRefresh();
    };
  }, []);

  if (isLoading) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
        <ActivityIndicator size="large" color="#0066cc" />
        <Text style={{ marginTop: 10 }}>Loading...</Text>
      </View>
    );
  }

  return (
    <NavigationContainer ref={navigationRef}>
      <Stack.Navigator
        initialRouteName={isAuthenticated ? "Dashboard" : "Login"}
        screenOptions={{ headerShown: false }}
      >
        {/* Public screen */}
        <Stack.Screen name="Login" component={LoginScreen} />

        {/* Authenticated screens */}
        <Stack.Screen name="Dashboard" component={DashboardScreen} />
        <Stack.Screen name="Profile" component={ProfileViewScreen} />
        <Stack.Screen name="ProfileEdit" component={ProfileEditScreen} />
        <Stack.Screen name="ChangePassword" component={ChangePasswordScreen} />
        <Stack.Screen name="Cart" component={CartScreen} />
        <Stack.Screen name="Catalog" component={CatalogScreen} />
        <Stack.Screen name="OrdersScreen" component={OrdersScreen} />
        <Stack.Screen name="OrderHistory" component={OrderHistoryScreen} />
        <Stack.Screen name="Checkout" component={CheckoutScreen} />
        <Stack.Screen 
          name="ProductDetail" 
          component={ProductDetailScreen} 
          options={{
            presentation: 'modal', // This makes it appear as a modal
            cardOverlayEnabled: true,
            ...Platform.select({
              ios: {
                gestureEnabled: true,
                gestureResponseDistance: { vertical: 800 }
              }
            })
          }}
        />
        <Stack.Screen name="OrderDetails" component={OrderDetailsScreen} /> 
      </Stack.Navigator>
    </NavigationContainer>
  );
};

export default AppNavigator;