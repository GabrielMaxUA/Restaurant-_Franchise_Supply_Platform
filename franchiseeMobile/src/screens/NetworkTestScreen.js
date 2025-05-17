import React, { useState, useEffect } from 'react';
import { View, Text, Button, ScrollView, StyleSheet, SafeAreaView, TouchableOpacity } from 'react-native';
import { testCorsConnection } from '../services/api';
import { useNavigation } from '@react-navigation/native';

const NetworkTestScreen = () => {
  const [result, setResult] = useState(null);
  const [loading, setLoading] = useState(false);
  const navigation = useNavigation();
  
  // Run the test automatically when the screen loads
  useEffect(() => {
    runTest();
  }, []);
  
  const runTest = async () => {
    setLoading(true);
    setResult(null);
    try {
      const testResult = await testCorsConnection();
      setResult(testResult);
    } catch (error) {
      setResult({
        success: false,
        error: error.message || 'Network test failed'
      });
    } finally {
      setLoading(false);
    }
  };
  
  const goToLogin = () => {
    navigation.navigate('Login');
  };
  
  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.container}>
        <Text style={styles.title}>Network Connectivity Test</Text>
        <Text style={styles.subtitle}>Testing connection to Laravel backend</Text>
        
        <Button 
          title={loading ? "Testing..." : "Test API Connection"} 
          onPress={runTest} 
          disabled={loading}
          color="#28a745"
        />
        
        {result && (
          <ScrollView style={styles.resultContainer}>
            <Text style={styles.resultTitle}>
              Test {result.success ? 'Succeeded ✅' : 'Failed ❌'}
            </Text>
            <Text style={styles.resultText}>
              {JSON.stringify(result, null, 2)}
            </Text>
          </ScrollView>
        )}
        
        {loading && (
          <Text style={styles.loadingText}>Testing connection to server...</Text>
        )}
        
        <View style={styles.buttonContainer}>
          <TouchableOpacity 
            style={styles.navButton} 
            onPress={goToLogin}
          >
            <Text style={styles.navButtonText}>Go to Login Screen</Text>
          </TouchableOpacity>
        </View>
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#f8f9fa',
  },
  container: {
    flex: 1,
    padding: 20,
    alignItems: 'center',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginTop: 40,
    marginBottom: 10,
    color: '#212529',
    textAlign: 'center',
  },
  subtitle: {
    fontSize: 16,
    color: '#6c757d',
    marginBottom: 30,
    textAlign: 'center',
  },
  resultContainer: {
    marginTop: 25,
    backgroundColor: '#f5f5f5',
    padding: 16,
    borderRadius: 8,
    width: '100%',
    maxHeight: 350,
    borderWidth: 1,
    borderColor: '#dee2e6',
  },
  resultTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 12,
    textAlign: 'center',
    color: '#212529',
  },
  resultText: {
    fontFamily: 'monospace',
    fontSize: 14,
    color: '#343a40',
  },
  loadingText: {
    marginTop: 20,
    fontSize: 16,
    color: '#6c757d',
    textAlign: 'center',
  },
  buttonContainer: {
    position: 'absolute',
    bottom: 30,
    width: '100%',
    alignItems: 'center',
  },
  navButton: {
    backgroundColor: '#007bff',
    paddingVertical: 12,
    paddingHorizontal: 24,
    borderRadius: 8,
    marginTop: 20,
    width: '80%',
  },
  navButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '500',
    textAlign: 'center',
  }
});

export default NetworkTestScreen;