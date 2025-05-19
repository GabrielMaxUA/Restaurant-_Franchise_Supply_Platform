import React, { useEffect, useState } from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { NavigationContainer } from '@react-navigation/native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { View, Text, ActivityIndicator } from 'react-native';
// Import screens
import LoginScreen from '../screens/LoginScreen';
import DashboardScreen from '../screens/DashboardScreen';
import ProfileEditScreen from '../screens/ProfileEditScreen';
import ProfileViewScreen from '../screens/ProfileViewScreen';
import ChangePasswordScreen from '../screens/ChangePasswordScreen';

import CartScreen from '../screens/CartScreen';
import CatalogScreen from '../screens/CatalogScreen';
import ProductDetailScreen from '../screens/ProductDetailScreen';
const Stack = createNativeStackNavigator();

const AppNavigator = () => {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  const checkAuthentication = async () => {
    try {
      const token = await AsyncStorage.getItem('userToken');
      setIsAuthenticated(!!token);
    } catch (error) {
      console.error('Auth check error:', error);
      setIsAuthenticated(false);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    checkAuthentication();

    const interval = setInterval(() => {
      checkAuthentication();
    }, 10000); // every 10 seconds

    return () => clearInterval(interval);
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
    <NavigationContainer>
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
      </Stack.Navigator>
    </NavigationContainer>
  );
};

export default AppNavigator;
