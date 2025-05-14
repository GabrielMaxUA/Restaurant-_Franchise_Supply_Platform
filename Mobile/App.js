import React, { useState, useEffect } from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createStackNavigator } from '@react-navigation/stack';
import AsyncStorage from '@react-native-async-storage/async-storage';
import LoginScreen from './LoginScreen';
import DashboardScreen from './DashboardScreen';

// Initialize the stack navigator
const Stack = createStackNavigator();

export default function App() {
  const [isLoading, setIsLoading] = useState(true);
  const [userToken, setUserToken] = useState(null);
  const [userData, setUserData] = useState(null);

  useEffect(() => {
    // Check if user is already logged in
    const bootstrapAsync = async () => {
      try {
        const token = await AsyncStorage.getItem('userToken');
        const userDataString = await AsyncStorage.getItem('userData');
        
        if (token && userDataString) {
          setUserToken(token);
          setUserData(JSON.parse(userDataString));
        }
      } catch (e) {
        console.error('Failed to load authentication token', e);
      } finally {
        setIsLoading(false);
      }
    };

    bootstrapAsync();
  }, []);

  if (isLoading) {
    return null; // Or a loading screen
  }

  return (
    <NavigationContainer>
      <Stack.Navigator>
        {userToken == null ? (
          // No token found, user isn't signed in
          <Stack.Screen 
            name="Login" 
            component={LoginScreen} 
            options={{ 
              headerShown: false,
            }}
          />
        ) : (
          // User is signed in
          <Stack.Screen 
            name="Dashboard" 
            component={DashboardScreen}
            initialParams={{ userToken, userData }}
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
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
}