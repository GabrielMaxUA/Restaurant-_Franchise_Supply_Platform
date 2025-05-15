import React, { useState, useContext } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Image,
  KeyboardAvoidingView,
  Platform,
  Alert,
  ActivityIndicator
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { login } from '../services/api';
import { CommonActions } from '@react-navigation/native';

const LoginScreen = ({ navigation }) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');

  const handleLogin = async () => {
    // Basic validation
    if (!email || !password) {
      setError('Email and password are required');
      return;
    }

    setIsLoading(true);
    setError('');

    try {
      // Make login request using the API service
      const response = await login(email, password);

      // Check response
      if (!response.success) {
        setError(response.error || 'Login failed. Please check your credentials.');
        setIsLoading(false);
        return;
      }

      // Store authentication token in AsyncStorage
      console.log('🔑 Login successful - Token received:', response.token ? 'YES' : 'NO');
      console.log('👤 User data received:', response.user ? 'YES' : 'NO');
      
      // Make sure we have a token before proceeding
      if (!response.token) {
        setError('No authentication token received from server.');
        setIsLoading(false);
        return;
      }
      
      await AsyncStorage.setItem('userToken', response.token);
      
      if (response.user) {
        await AsyncStorage.setItem('userData', JSON.stringify(response.user));
      }
      
      console.log('💾 Login successful, token stored in AsyncStorage');
      console.log('🚀 Navigating to Dashboard...');
      
      // Force navigation to Dashboard screen
      console.log('🔄 Attempting navigation to Dashboard...');
      setIsLoading(false);
      
      try {
        // Use CommonActions for more reliable navigation
        navigation.dispatch(
          CommonActions.reset({
            index: 0,
            routes: [
              { name: 'Dashboard' },
            ],
          })
        );
        
        // As a fallback, also try regular navigation
        setTimeout(() => {
          try {
            console.log('🔄 Attempting fallback navigation...');
            if (navigation.canGoBack()) {
              navigation.popToTop();
            }
            navigation.navigate('Dashboard');
          } catch (navError) {
            console.error('⚠️ Fallback navigation failed:', navError);
          }
        }, 500);
      } catch (navError) {
        console.error('⚠️ Navigation error:', navError);
      }
    } catch (error) {
      setError('Network error. Please try again.');
      console.error('Login error:', error);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      style={styles.container}
    >
      <View style={styles.logoContainer}>
        <Text style={styles.title}>Restaurant Franchise Supply</Text>
        <Text style={styles.subtitle}>Franchisee Portal</Text>
      </View>

      <View style={styles.formContainer}>
        {error ? <Text style={styles.errorText}>{error}</Text> : null}

        <View style={styles.inputContainer}>
          <Text style={styles.label}>Email</Text>
          <TextInput
            style={styles.input}
            value={email}
            onChangeText={setEmail}
            placeholder="Enter your email"
            keyboardType="email-address"
            autoCapitalize="none"
            autoCorrect={false}
          />
        </View>

        <View style={styles.inputContainer}>
          <Text style={styles.label}>Password</Text>
          <TextInput
            style={styles.input}
            value={password}
            onChangeText={setPassword}
            placeholder="Enter your password"
            secureTextEntry
          />
        </View>

        <TouchableOpacity
          style={styles.loginButton}
          onPress={handleLogin}
          disabled={isLoading}
        >
          {isLoading ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <Text style={styles.loginButtonText}>Login</Text>
          )}
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.testApiButton}
          onPress={() => navigation.navigate('TestAPI')}
        >
          <Text style={styles.testApiButtonText}>Simple API Test</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.testApiButton, styles.comprehensiveButton]}
          onPress={() => navigation.navigate('ComprehensiveAPITest')}
        >
          <Text style={styles.testApiButtonText}>Comprehensive API Test</Text>
        </TouchableOpacity>
      </View>

      <View style={styles.footer}>
        <Text style={styles.footerText}>
          © {new Date().getFullYear()} Restaurant Franchise Supply
        </Text>
      </View>
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  logoContainer: {
    alignItems: 'center',
    marginTop: 80,
    marginBottom: 40,
  },
  logo: {
    width: 120,
    height: 120,
    marginBottom: 20,
  },
  title: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#333',
  },
  subtitle: {
    fontSize: 16,
    color: '#666',
    marginTop: 5,
  },
  formContainer: {
    paddingHorizontal: 30,
  },
  errorText: {
    color: 'red',
    marginBottom: 15,
    textAlign: 'center',
  },
  inputContainer: {
    marginBottom: 20,
  },
  label: {
    fontSize: 14,
    color: '#333',
    marginBottom: 8,
    fontWeight: '500',
  },
  input: {
    backgroundColor: '#fff',
    paddingHorizontal: 15,
    paddingVertical: 12,
    borderRadius: 5,
    borderWidth: 1,
    borderColor: '#ddd',
    fontSize: 16,
  },
  loginButton: {
    backgroundColor: '#0066cc',
    paddingVertical: 15,
    borderRadius: 5,
    alignItems: 'center',
    marginTop: 10,
  },
  loginButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  testApiButton: {
    backgroundColor: 'transparent',
    paddingVertical: 15,
    borderRadius: 5,
    alignItems: 'center',
    marginTop: 10,
    borderWidth: 1,
    borderColor: '#0066cc',
  },
  comprehensiveButton: {
    backgroundColor: '#e8f4fc',
    marginTop: 5,
  },
  testApiButtonText: {
    color: '#0066cc',
    fontSize: 16,
  },
  footer: {
    position: 'absolute',
    bottom: 20,
    left: 0,
    right: 0,
    alignItems: 'center',
  },
  footerText: {
    color: '#999',
    fontSize: 12,
  },
});

export default LoginScreen;