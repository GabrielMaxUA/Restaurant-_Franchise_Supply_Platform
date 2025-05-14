import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TextInput,
  TouchableOpacity,
  ScrollView,
  ActivityIndicator,
  SafeAreaView,
  Share,
  Platform
} from 'react-native';
import { testApiConnections, testApiConnection } from '../utils/ApiTest';
import { runApiDiagnostics, generateDiagnosticsReport } from '../utils/ApiDiagnostics';

const TestScreen = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [results, setResults] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [connectionStatus, setConnectionStatus] = useState(null);
  const [diagnosticsResults, setDiagnosticsResults] = useState(null);
  const [runningDiagnostics, setRunningDiagnostics] = useState(false);

  // Test basic API connection first
  const checkApiConnection = async () => {
    setConnectionStatus(null);
    setError('');
    setLoading(true);
    
    try {
      const response = await testApiConnection();
      console.log('Basic API connection test response:', response);
      
      // testApiConnection now returns a structured response with success property
      if (response.success) {
        setConnectionStatus({
          success: true,
          message: response.message || 'API connection successful',
          data: response
        });
      } else {
        console.warn('API connection test failed:', response.message);
        setConnectionStatus({
          success: false,
          message: response.message || 'API connection failed',
          error: response.error
        });
        
        if (response.error) {
          setError(`API connection error: ${response.error}`);
        }
      }
    } catch (error) {
      // This should rarely happen since the test function catches errors
      console.error('API connection test unexpected error:', error);
      setConnectionStatus({
        success: false,
        message: `API connection failed: ${error.message}`,
        error: error
      });
      setError(`API connection error: ${error.message}`);
    } finally {
      setLoading(false);
    }
  };

  // Run API diagnostics
  const runDiagnostics = async () => {
    setRunningDiagnostics(true);
    try {
      const diagnostics = await runApiDiagnostics();
      setDiagnosticsResults(diagnostics);
      console.log('Diagnostics completed:', diagnostics);
    } catch (error) {
      console.error('Diagnostics error:', error);
      setError(`Diagnostics failed: ${error.message}`);
    } finally {
      setRunningDiagnostics(false);
    }
  };

  // Share diagnostics report
  const shareDiagnosticsReport = async () => {
    if (!diagnosticsResults) return;
    
    try {
      const report = generateDiagnosticsReport(diagnosticsResults);
      await Share.share({
        message: report,
        title: 'API Diagnostics Report',
      });
    } catch (error) {
      console.error('Failed to share diagnostics:', error);
      setError(`Failed to share report: ${error.message}`);
    }
  };

  // Run on component mount
  useEffect(() => {
    checkApiConnection();
    runDiagnostics();
  }, []);

  const runTests = async () => {
    if (!email || !password) {
      setError('Please enter both email and password');
      return;
    }

    setLoading(true);
    setError('');
    setResults(null);

    try {
      console.log('Starting API test with:', email);
      const testResults = await testApiConnections({ email, password });
      console.log('API test complete:', testResults);
      
      // Process test results - ensure each test has needed properties
      Object.keys(testResults).forEach(key => {
        const result = testResults[key];
        
        // Make sure success is a boolean
        if (typeof result.success !== 'boolean') {
          result.success = !!result.success;
        }
        
        // Ensure there's a message
        if (!result.message) {
          result.message = result.success ? `${key} test passed` : `${key} test failed`;
        }
        
        // Format data for display
        if (result.data && typeof result.data === 'object') {
          try {
            // Clean up data for display
            const cleanData = {...result.data};
            
            // Convert nested objects to strings
            Object.keys(cleanData).forEach(dataKey => {
              if (typeof cleanData[dataKey] === 'object' && cleanData[dataKey] !== null) {
                cleanData[dataKey] = JSON.stringify(cleanData[dataKey]).substring(0, 100) + '...';
              }
            });
            
            result.data = cleanData;
          } catch (e) {
            console.warn(`Error formatting test result data for ${key}:`, e);
          }
        }
      });
      
      setResults(testResults);
    } catch (error) {
      setError(`Test failed: ${error.message}`);
      console.error('Test error:', error);
    } finally {
      setLoading(false);
    }
  };

  const renderResultItem = (title, result) => (
    <View style={styles.resultItem}>
      <View style={styles.resultHeader}>
        <Text style={styles.resultTitle}>{title}</Text>
        <View style={[
          styles.statusBadge,
          { backgroundColor: result.success ? '#4CAF50' : '#F44336' }
        ]}>
          <Text style={styles.statusText}>
            {result.success ? 'SUCCESS' : 'FAILED'}
          </Text>
        </View>
      </View>
      
      <Text style={styles.resultMessage}>{result.message}</Text>
      
      {result.data && (
        <View style={styles.resultData}>
          {Object.entries(result.data).map(([key, value]) => (
            <Text key={key} style={styles.resultDataItem}>
              <Text style={styles.resultDataLabel}>{key}: </Text>
              {typeof value === 'object' ? JSON.stringify(value) : value.toString()}
            </Text>
          ))}
        </View>
      )}
    </View>
  );

  const renderDiagnosticsSection = () => {
    if (!diagnosticsResults && !runningDiagnostics) return null;
    
    return (
      <View style={styles.diagnosticsContainer}>
        <Text style={styles.diagnosticsTitle}>API Diagnostics</Text>
        
        {runningDiagnostics ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="small" color="#0066cc" />
            <Text style={styles.loadingText}>Running diagnostics...</Text>
          </View>
        ) : (
          <>
            <View style={styles.diagnosticsSummary}>
              <Text style={styles.summaryText}>
                <Text style={styles.label}>Base URL: </Text>
                {diagnosticsResults.baseUrl}
              </Text>
              
              <Text style={styles.summaryText}>
                <Text style={styles.label}>Auth: </Text>
                {diagnosticsResults.auth.status}
              </Text>
              
              <Text style={styles.summaryText}>
                <Text style={styles.label}>Network: </Text>
                {diagnosticsResults.networkInfo.isConnected ? 'Connected' : 'Disconnected'} 
                ({diagnosticsResults.networkInfo.type})
              </Text>
              
              <Text style={styles.summaryText}>
                <Text style={styles.label}>Endpoints Tested: </Text>
                {Object.keys(diagnosticsResults.endpoints).length}
              </Text>
            </View>
            
            <View style={styles.endpointsContainer}>
              <Text style={styles.endpointsTitle}>Endpoint Status</Text>
              
              {Object.entries(diagnosticsResults.endpoints).map(([name, data]) => (
                <View key={name} style={styles.endpointItem}>
                  <View style={styles.endpointHeader}>
                    <Text style={styles.endpointName}>{name}</Text>
                    <View style={[
                      styles.statusBadge,
                      { 
                        backgroundColor: 
                          data.skipped ? '#9e9e9e' : 
                          (data.ok ? '#4CAF50' : '#F44336') 
                      }
                    ]}>
                      <Text style={styles.statusText}>
                        {data.skipped ? 'SKIPPED' : 
                         (data.ok ? 'OK' : data.status)}
                      </Text>
                    </View>
                  </View>
                  
                  {data.error && (
                    <Text style={styles.endpointError}>{data.error}</Text>
                  )}
                </View>
              ))}
            </View>
            
            <TouchableOpacity
              style={styles.reportButton}
              onPress={shareDiagnosticsReport}
            >
              <Text style={styles.reportButtonText}>Share Detailed Report</Text>
            </TouchableOpacity>
          </>
        )}
      </View>
    );
  };

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>API Connection Test</Text>
        <Text style={styles.headerSubtitle}>Test your backend API connections</Text>
      </View>

      <ScrollView style={styles.content}>
        {/* API Connection Status */}
        <View style={styles.statusContainer}>
          <Text style={styles.statusTitle}>API Connection Status</Text>
          
          {connectionStatus === null ? (
            <ActivityIndicator size="small" color="#0066cc" />
          ) : (
            <View style={[
              styles.statusBadge, 
              { backgroundColor: connectionStatus.success ? '#4CAF50' : '#F44336' }
            ]}>
              <Text style={styles.statusText}>
                {connectionStatus.success ? 'CONNECTED' : 'FAILED'}
              </Text>
            </View>
          )}
          
          {connectionStatus && (
            <Text style={styles.statusMessage}>
              {connectionStatus.message}
              {connectionStatus.data && connectionStatus.data.message && 
                ` (${connectionStatus.data.message})`}
            </Text>
          )}
          
          <View style={styles.buttonRow}>
            <TouchableOpacity
              style={styles.retryButton}
              onPress={checkApiConnection}
              disabled={loading}
            >
              <Text style={styles.retryButtonText}>Test Connection</Text>
            </TouchableOpacity>
            
            <TouchableOpacity
              style={styles.diagnosticsButton}
              onPress={runDiagnostics}
              disabled={runningDiagnostics}
            >
              <Text style={styles.diagnosticsButtonText}>Run Diagnostics</Text>
            </TouchableOpacity>
          </View>
        </View>
        
        {/* Diagnostics Section */}
        {renderDiagnosticsSection()}

        {/* Error Display */}
        {error ? (
          <View style={styles.errorContainer}>
            <Text style={styles.errorText}>{error}</Text>
          </View>
        ) : null}

        <View style={styles.formContainer}>
          <Text style={styles.formTitle}>API Authentication Test</Text>
          
          <View style={styles.inputContainer}>
            <Text style={styles.label}>Email</Text>
            <TextInput
              style={styles.input}
              value={email}
              onChangeText={setEmail}
              placeholder="Enter franchisee email"
              keyboardType="email-address"
              autoCapitalize="none"
            />
          </View>

          <View style={styles.inputContainer}>
            <Text style={styles.label}>Password</Text>
            <TextInput
              style={styles.input}
              value={password}
              onChangeText={setPassword}
              placeholder="Enter password"
              secureTextEntry
            />
          </View>

          <TouchableOpacity
            style={styles.button}
            onPress={runTests}
            disabled={loading}
          >
            {loading ? (
              <ActivityIndicator color="#fff" />
            ) : (
              <Text style={styles.buttonText}>Run API Tests</Text>
            )}
          </TouchableOpacity>
        </View>

        {results && (
          <View style={styles.resultsContainer}>
            <Text style={styles.resultsTitle}>Test Results</Text>
            {renderResultItem('Login', results.login)}
            {results.login.success && (
              <>
                {renderResultItem('User Profile', results.profile)}
                {renderResultItem('Catalog', results.catalog)}
                {renderResultItem('Orders', results.orders)}
              </>
            )}
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  header: {
    backgroundColor: '#0066cc',
    padding: 20,
    paddingBottom: 30,
  },
  headerTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#fff',
  },
  headerSubtitle: {
    fontSize: 14,
    color: 'rgba(255, 255, 255, 0.8)',
    marginTop: 5,
  },
  content: {
    flex: 1,
    padding: 20,
  },
  // Connection status styles
  statusContainer: {
    backgroundColor: '#fff',
    borderRadius: 10,
    padding: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
    marginBottom: 20,
    alignItems: 'center',
  },
  statusTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 15,
  },
  statusMessage: {
    color: '#666',
    marginTop: 10,
    marginBottom: 15,
    textAlign: 'center',
  },
  buttonRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    width: '100%',
    marginTop: 10,
  },
  retryButton: {
    backgroundColor: '#f5f5f5',
    paddingVertical: 10,
    paddingHorizontal: 15,
    borderRadius: 5,
    borderWidth: 1,
    borderColor: '#ddd',
    flex: 1,
    marginRight: 10,
    alignItems: 'center',
  },
  retryButtonText: {
    color: '#0066cc',
    fontSize: 14,
    fontWeight: '500',
  },
  diagnosticsButton: {
    backgroundColor: '#0066cc',
    paddingVertical: 10,
    paddingHorizontal: 15,
    borderRadius: 5,
    flex: 1,
    marginLeft: 10,
    alignItems: 'center',
  },
  diagnosticsButtonText: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '500',
  },
  // Diagnostics styles
  diagnosticsContainer: {
    backgroundColor: '#fff',
    borderRadius: 10,
    padding: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
    marginBottom: 20,
  },
  diagnosticsTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 15,
  },
  loadingContainer: {
    alignItems: 'center',
    padding: 20,
  },
  loadingText: {
    marginTop: 10,
    color: '#666',
  },
  diagnosticsSummary: {
    backgroundColor: '#f9f9f9',
    padding: 15,
    borderRadius: 5,
    marginBottom: 15,
  },
  summaryText: {
    fontSize: 14,
    marginBottom: 8,
    color: '#444',
  },
  endpointsContainer: {
    marginBottom: 15,
  },
  endpointsTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 10,
  },
  endpointItem: {
    borderWidth: 1,
    borderColor: '#e0e0e0',
    borderRadius: 5,
    padding: 12,
    marginBottom: 10,
  },
  endpointHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  endpointName: {
    fontSize: 15,
    fontWeight: '500',
    color: '#333',
  },
  endpointError: {
    color: '#F44336',
    fontSize: 13,
    marginTop: 5,
  },
  reportButton: {
    backgroundColor: '#f5f5f5',
    paddingVertical: 12,
    paddingHorizontal: 15,
    borderRadius: 5,
    borderWidth: 1,
    borderColor: '#ddd',
    alignItems: 'center',
  },
  reportButtonText: {
    color: '#0066cc',
    fontSize: 14,
    fontWeight: '500',
  },
  // Error container
  errorContainer: {
    backgroundColor: '#ffebee',
    borderRadius: 5,
    padding: 15,
    marginBottom: 20,
  },
  errorText: {
    color: '#c62828',
  },
  // Form styles
  formContainer: {
    backgroundColor: '#fff',
    borderRadius: 10,
    padding: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
    marginBottom: 20,
  },
  formTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 15,
  },
  inputContainer: {
    marginBottom: 15,
  },
  label: {
    fontSize: 14,
    color: '#333',
    marginBottom: 8,
    fontWeight: '500',
  },
  input: {
    backgroundColor: '#f9f9f9',
    paddingHorizontal: 15,
    paddingVertical: 12,
    borderRadius: 5,
    borderWidth: 1,
    borderColor: '#ddd',
    fontSize: 16,
  },
  button: {
    backgroundColor: '#0066cc',
    paddingVertical: 15,
    borderRadius: 5,
    alignItems: 'center',
    marginTop: 10,
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  resultsContainer: {
    backgroundColor: '#fff',
    borderRadius: 10,
    padding: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
    marginBottom: 20,
  },
  resultsTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 15,
  },
  resultItem: {
    borderWidth: 1,
    borderColor: '#e0e0e0',
    borderRadius: 5,
    padding: 15,
    marginBottom: 15,
  },
  resultHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  resultTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  statusText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: 'bold',
  },
  resultMessage: {
    color: '#666',
    marginBottom: 10,
  },
  resultData: {
    backgroundColor: '#f9f9f9',
    padding: 10,
    borderRadius: 5,
  },
  resultDataItem: {
    marginBottom: 5,
    fontSize: 14,
  },
  resultDataLabel: {
    fontWeight: 'bold',
  },
});

export default TestScreen;