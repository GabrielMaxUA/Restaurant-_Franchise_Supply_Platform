// src/navigation/index.js
import React, { useContext } from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createStackNavigator } from '@react-navigation/stack';
import { AuthContext } from '../contexts/AuthContext';
import AppNavigator from './AppNavigator';
import LoginScreen from '../screens/LoginScreen';
import SplashScreen from '../screens/SplashScreen';
import ApiTestScreen from '../screens/ApiTestScreen';

const Stack = createStackNavigator();

// Authentication stack with Login as first screen and ApiTest accessible
const AuthStack = () => {
  return (
    <Stack.Navigator
      screenOptions={{
        headerShown: false
      }}
    >
      <Stack.Screen name="Login" component={LoginScreen} />
      <Stack.Screen name="ApiTest" component={ApiTestScreen} />
    </Stack.Navigator>
  );
};

// Main navigation container
const AppNavigation = () => {
  const { state } = useContext(AuthContext);

  // Render loading, auth, or app stack based on auth state
  if (state.isLoading) {
    return <SplashScreen />;
  }

  return (
    <NavigationContainer>
      {state.userToken == null ? (
        // Show Auth stack (with Login as first screen) when not logged in
        <AuthStack />
      ) : (
        // Show App stack when logged in
        <Stack.Navigator screenOptions={{ headerShown: false }}>
          <Stack.Screen name="App" component={AppNavigator} />
          <Stack.Screen name="ApiTest" component={ApiTestScreen} />
        </Stack.Navigator>
      )}
    </NavigationContainer>
  );
};

export default AppNavigation;