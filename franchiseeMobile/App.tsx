import React, { useState, useEffect } from 'react';
import { StatusBar, StyleSheet, ActivityIndicator, View, Text } from 'react-native';
import { NavigationContainer } from '@react-navigation/native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { SafeAreaProvider } from 'react-native-safe-area-context';

// Import navigation
import AppNavigator from './src/navigation/AppNavigator';

function App(): React.JSX.Element {
  const [isLoading, setIsLoading] = useState(true);
  const [userToken, setUserToken] = useState<string | null>(null);
  const [userData, setUserData] = useState<any | null>(null);

  // Create a function that can be exported and called from other components
  const checkAuthState = async () => {
    try {
      console.log('Checking authentication state...');
      const token = await AsyncStorage.getItem('userToken');
      const userDataString = await AsyncStorage.getItem('userData');
      
      console.log('Token found:', token ? 'Yes' : 'No');
      
      if (token && userDataString) {
        setUserToken(token);
        setUserData(JSON.parse(userDataString));
      } else {
        setUserToken(null);
        setUserData(null);
      }
    } catch (e) {
      console.error('Failed to load authentication token', e);
      setUserToken(null);
      setUserData(null);
    } finally {
      setIsLoading(false);
    }
  };

  // Make this function available globally
  global.checkAuthState = checkAuthState;

  useEffect(() => {
    // Check if user is already logged in when app starts
    checkAuthState();
  }, []);

  if (isLoading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#0066cc" />
        <Text style={styles.loadingText}>Loading...</Text>
      </View>
    );
  }

  return (
    <SafeAreaProvider>
      <StatusBar barStyle="dark-content" />
      <NavigationContainer>
        <AppNavigator />
      </NavigationContainer>
    </SafeAreaProvider>
  );
}

const styles = StyleSheet.create({
  container: {
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
});

export default App;