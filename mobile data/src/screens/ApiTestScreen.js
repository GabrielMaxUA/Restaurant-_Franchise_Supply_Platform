// src/screens/ApiTestScreen.js
import React, { useState, useEffect } from 'react';
import {
  SafeAreaView,
  View,
  Text,
  StyleSheet,
  ScrollView,
  TextInput,
  TouchableOpacity,
  ActivityIndicator,
  Alert, Platform
} from 'react-native';
import { useNavigation } from '@react-navigation/native'; 
import axios from 'axios';

const ApiTestScreen = () => {
  const navigation = useNavigation();
  const [url, setUrl] = useState('http://localhost:8000/routes/api/test');
  const [method, setMethod] = useState('GET');
  const [headers, setHeaders] = useState('{"Content-Type": "application/json"}');
  const [body, setBody] = useState('{}');
  const [response, setResponse] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [networkInfo, setNetworkInfo] = useState('Checking network...');
 const goToLogin = () => {
    navigation.navigate('Login');
  };
  useEffect(() => {
    // Test if network is available
    const checkNetwork = async () => {
      try {
        // Simple fetch to a reliable endpoint
        const response = await fetch('https://httpbin.org/get');
        if (response.ok) {
          setNetworkInfo('Network is available. External API connection successful.');
        } else {
          setNetworkInfo('Network is available but external API request failed.');
        }
      } catch (error) {
        setNetworkInfo(`Network error: ${error.message}`);
      }
    };

    checkNetwork();
  }, []);

 // Test predefined endpoints
const testEndpoints = [
  { name: 'Test API Connection', url: 'http://localhost:8000/api/test', method: 'GET' },
  { name: 'Test DB Connection', url: 'http://localhost:8000/api/db-test', method: 'GET' },
  { name: 'Login Test', 
    url: 'http://localhost:8000/api/auth/login', 
    method: 'POST',
    body: { 
      email: 'test@example.com', 
      password: 'password' 
    } 
  },
  { name: 'Routes List', url: 'http://localhost:8000/api/routes', method: 'GET' },
];

  const handleSendRequest = async () => {
    setLoading(true);
    setResponse('');
    setError('');

    try {
      let parsedHeaders = {};
      try {
        parsedHeaders = JSON.parse(headers);
      } catch (e) {
        setError('Invalid headers JSON');
        setLoading(false);
        return;
      }

      let parsedBody = {};
      if (method !== 'GET' && method !== 'HEAD') {
        try {
          parsedBody = JSON.parse(body);
        } catch (e) {
          setError('Invalid body JSON');
          setLoading(false);
          return;
        }
      }

      const config = {
        method,
        url,
        headers: parsedHeaders,
      };

      if (method !== 'GET' && method !== 'HEAD') {
        config.data = parsedBody;
      }

      console.log('Sending request with config:', config);
      
      const response = await axios(config);
      
      setResponse(JSON.stringify(response.data, null, 2));
    } catch (error) {
      console.error('API Error:', error);
      setError(
        `Error: ${error.message}\n` +
        (error.response ? `Status: ${error.response.status}\n` : '') +
        (error.response ? `Data: ${JSON.stringify(error.response.data, null, 2)}` : '')
      );
    } finally {
      setLoading(false);
    }
  };

  const handleTestEndpoint = async (endpoint) => {
    setUrl(endpoint.url);
    setMethod(endpoint.method);
    
    if (endpoint.body) {
      setBody(JSON.stringify(endpoint.body));
    } else {
      setBody('{}');
    }
    
    // Wait a moment for state updates
    setTimeout(() => {
      handleSendRequest();
    }, 100);
  };

  // For localhost in iOS simulator, use http://localhost
  // For localhost in Android emulator, use http://10.0.2.2
  // For actual device testing, use your computer's IP address
  const getLocalAddress = () => {
    Alert.alert(
      "Local Address Information",
      "For iOS simulator: Use http://localhost\n" +
      "For Android emulator: Use http://10.0.2.2\n" +
      "For physical device: Use your computer's IP address (e.g., http://192.168.1.10)"
    );
  };

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView style={styles.scrollView}>
        <View style={styles.header}>
  <TouchableOpacity style={styles.loginButton} onPress={goToLogin}>
    <Text style={styles.loginButtonText}>Go to Login Screen</Text>
  </TouchableOpacity>
          <Text style={styles.title}>API Test Tool</Text>
          <TouchableOpacity onPress={getLocalAddress} style={styles.infoButton}>
            <Text style={styles.infoButtonText}>ℹ️</Text>
          </TouchableOpacity>
        </View>

        <View style={styles.networkStatus}>
          <Text style={styles.networkText}>{networkInfo}</Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Quick Tests</Text>
          <View style={styles.quickTests}>
            {testEndpoints.map((endpoint, index) => (
              <TouchableOpacity
                key={index}
                style={styles.quickTestButton}
                onPress={() => handleTestEndpoint(endpoint)}
              >
                <Text style={styles.quickTestButtonText}>{endpoint.name}</Text>
              </TouchableOpacity>
            ))}
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Custom Request</Text>
          
          <Text style={styles.label}>URL:</Text>
          <TextInput
            style={styles.input}
            value={url}
            onChangeText={setUrl}
            placeholder="http://example.com/api"
          />
          
          <Text style={styles.label}>Method:</Text>
          <View style={styles.methodButtons}>
            {['GET', 'POST', 'PUT', 'DELETE'].map((m) => (
              <TouchableOpacity
                key={m}
                style={[
                  styles.methodButton,
                  method === m && styles.methodButtonActive
                ]}
                onPress={() => setMethod(m)}
              >
                <Text
                  style={[
                    styles.methodButtonText,
                    method === m && styles.methodButtonTextActive
                  ]}
                >
                  {m}
                </Text>
              </TouchableOpacity>
            ))}
          </View>
          
          <Text style={styles.label}>Headers (JSON):</Text>
          <TextInput
            style={[styles.input, styles.jsonInput]}
            value={headers}
            onChangeText={setHeaders}
            multiline
          />
          
          {method !== 'GET' && method !== 'HEAD' && (
            <>
              <Text style={styles.label}>Body (JSON):</Text>
              <TextInput
                style={[styles.input, styles.jsonInput]}
                value={body}
                onChangeText={setBody}
                multiline
              />
            </>
          )}
          
          <TouchableOpacity
            style={styles.sendButton}
            onPress={handleSendRequest}
            disabled={loading}
          >
            {loading ? (
              <ActivityIndicator color="#fff" />
            ) : (
              <Text style={styles.sendButtonText}>Send Request</Text>
            )}
          </TouchableOpacity>
        </View>
        
        {error ? (
          <View style={styles.responseSection}>
            <Text style={styles.responseTitle}>Error:</Text>
            <ScrollView style={styles.responseContainer}>
              <Text style={styles.errorText}>{error}</Text>
            </ScrollView>
          </View>
        ) : null}
        
        {response ? (
          <View style={styles.responseSection}>
            <Text style={styles.responseTitle}>Response:</Text>
            <ScrollView style={styles.responseContainer}>
              <Text style={styles.responseText}>{response}</Text>
            </ScrollView>
          </View>
        ) : null}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  scrollView: {
    flex: 1,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    backgroundColor: '#4e73df',
  },
  title: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#fff',
  },
  infoButton: {
    padding: 8,
  },
  infoButtonText: {
    fontSize: 20,
  },
  networkStatus: {
    padding: 16,
    backgroundColor: '#fff',
    marginBottom: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  networkText: {
    fontSize: 14,
    color: '#666',
  },
  section: {
    backgroundColor: '#fff',
    margin: 16,
    borderRadius: 8,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.1,
    shadowRadius: 3,
    elevation: 2,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 16,
    color: '#333',
  },
  quickTests: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  quickTestButton: {
    backgroundColor: '#f0f0f0',
    padding: 12,
    borderRadius: 8,
    marginBottom: 8,
    width: '48%',
  },
  quickTestButtonText: {
    textAlign: 'center',
    color: '#333',
    fontWeight: '500',
  },
  label: {
    fontSize: 16,
    marginBottom: 8,
    color: '#333',
  },
  input: {
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    padding: 12,
    marginBottom: 16,
    backgroundColor: '#fff',
  },
  jsonInput: {
    minHeight: 100,
    textAlignVertical: 'top',
    fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace',
    fontSize: 14,
  },
  methodButtons: {
    flexDirection: 'row',
    marginBottom: 16,
  },
  methodButton: {
    paddingVertical: 8,
    paddingHorizontal: 12,
    marginRight: 8,
    borderRadius: 6,
    backgroundColor: '#f0f0f0',
  },
  methodButtonActive: {
    backgroundColor: '#4e73df',
  },
  methodButtonText: {
    color: '#333',
  },
  methodButtonTextActive: {
    color: '#fff',
  },
  sendButton: {
    backgroundColor: '#28a745',
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  sendButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
  responseSection: {
    backgroundColor: '#fff',
    margin: 16,
    marginTop: 0,
    borderRadius: 8,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.1,
    shadowRadius: 3,
    elevation: 2,
  },
  responseTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 8,
    color: '#333',
  },
  responseContainer: {
    maxHeight: 300,
    borderWidth: 1,
    borderColor: '#eee',
    borderRadius: 8,
    padding: 12,
    backgroundColor: '#f9f9f9',
  },
  responseText: {
    fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace',
    fontSize: 14,
    color: '#333',
  },
  errorText: {
    fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace',
    fontSize: 14,
    color: '#dc3545',
  },
  // Add these styles
  loginButton: {
    backgroundColor: '#4e73df',
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: 'center',
    marginTop: 16,
  },
  loginButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
});

export default ApiTestScreen;