import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TextInput,
  TouchableOpacity,
  ActivityIndicator,
  SafeAreaView,
  Alert,
  Share,
  FlatList,
  Modal,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { runFranchiseeApiTests, generateApiTestReport } from '../utils/FranchiseeApiTest';
import { BASE_URL } from '../services/api';

const ApiTestScreen = ({ navigation }) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [results, setResults] = useState(null);
  const [expandedSections, setExpandedSections] = useState({});
  const [error, setError] = useState('');
  const [selectedEndpoint, setSelectedEndpoint] = useState(null);
  const [modalVisible, setModalVisible] = useState(false);
  
  useEffect(() => {
    // Try to get saved credentials if available
    const getStoredCredentials = async () => {
      try {
        const storedEmail = await AsyncStorage.getItem('testEmail');
        const storedPassword = await AsyncStorage.getItem('testPassword');
        
        if (storedEmail) setEmail(storedEmail);
        if (storedPassword) setPassword(storedPassword);
      } catch (e) {
        console.warn('Failed to get stored credentials:', e);
      }
    };
    
    getStoredCredentials();
  }, []);

  const runTests = async () => {
    if (!email || !password) {
      setError('Please enter both email and password');
      return;
    }

    setLoading(true);
    setError('');
    setResults(null);
    setExpandedSections({});

    try {
      // Save credentials for future tests
      await AsyncStorage.setItem('testEmail', email);
      await AsyncStorage.setItem('testPassword', password);
      
      console.log('Starting comprehensive API test with:', email);
      const testResults = await runFranchiseeApiTests({ email, password });
      console.log('API test complete');
      setResults(testResults);
      
      // Auto-expand failing tests
      const newExpandedSections = {};
      Object.keys(testResults.endpoints).forEach(key => {
        const endpoint = testResults.endpoints[key];
        if (!endpoint.success && !endpoint.skipped) {
          newExpandedSections[key] = true;
        }
      });
      setExpandedSections(newExpandedSections);
    } catch (error) {
      setError(`Test failed: ${error.message}`);
      console.error('Test error:', error);
    } finally {
      setLoading(false);
    }
  };

  const toggleSection = (sectionKey) => {
    setExpandedSections(prev => ({
      ...prev,
      [sectionKey]: !prev[sectionKey],
    }));
  };

  const shareReport = async () => {
    if (!results) return;
    
    try {
      const report = generateApiTestReport(results);
      await Share.share({
        message: report,
        title: 'API Test Report',
      });
    } catch (error) {
      console.error('Error sharing report:', error);
      Alert.alert('Share Error', 'Failed to share the report');
    }
  };

  const renderStatus = (status) => {
    if (status === 'skipped') {
      return <Text style={[styles.statusBadge, styles.statusSkipped]}>SKIPPED</Text>;
    } else if (status) {
      return <Text style={[styles.statusBadge, styles.statusSuccess]}>PASSED</Text>;
    } else {
      return <Text style={[styles.statusBadge, styles.statusFailed]}>FAILED</Text>;
    }
  };

  const renderEndpointItem = ({ item: [key, endpoint] }) => {
    const isExpanded = expandedSections[key];
    
    return (
      <View style={styles.endpointCard}>
        <TouchableOpacity 
          style={styles.endpointHeader}
          onPress={() => toggleSection(key)}
        >
          <Text style={styles.endpointName}>{endpoint.name || key}</Text>
          {renderStatus(endpoint.skipped ? 'skipped' : endpoint.success)}
        </TouchableOpacity>
        
        <View style={styles.endpointMessage}>
          <Text style={endpoint.success ? styles.successText : styles.errorText}>
            {endpoint.message}
          </Text>
        </View>
        
        {isExpanded && (
          <View style={styles.expandedContent}>
            {endpoint.endpoint && (
              <Text style={styles.detailText}>
                <Text style={styles.detailLabel}>Endpoint: </Text>
                {endpoint.endpoint}
              </Text>
            )}
            
            {endpoint.status && (
              <Text style={styles.detailText}>
                <Text style={styles.detailLabel}>Status: </Text>
                {endpoint.status}
              </Text>
            )}
            
            {endpoint.validation && !endpoint.skipped && (
              <>
                {!endpoint.validation.hasExpectedKeys && endpoint.validation.missingKeys.length > 0 && (
                  <Text style={styles.errorText}>
                    <Text style={styles.detailLabel}>Missing Keys: </Text>
                    {endpoint.validation.missingKeys.join(', ')}
                  </Text>
                )}
                
                {!endpoint.validation.hasRequiredFields && endpoint.validation.missingFields.length > 0 && (
                  <Text style={styles.errorText}>
                    <Text style={styles.detailLabel}>Missing Fields: </Text>
                    {endpoint.validation.missingFields.join(', ')}
                  </Text>
                )}
              </>
            )}
            
            {/* HTML Response Warning */}
            {endpoint.isHtmlResponse && (
              <View style={styles.errorContainer}>
                <Text style={styles.errorText}>
                  <Text style={styles.warningLabel}>⚠️ HTML Response Detected: </Text>
                  {endpoint.htmlTitle || 'Non-JSON response received'}
                </Text>
              </View>
            )}
            
            {/* Data Section */}
            {endpoint.data && typeof endpoint.data === 'object' && (
              <View style={styles.dataSection}>
                <Text style={styles.sectionTitle}>Data Summary</Text>
                
                {endpoint.data.productCount !== undefined && (
                  <Text style={styles.detailText}>
                    <Text style={styles.detailLabel}>Products: </Text>
                    {endpoint.data.productCount}
                  </Text>
                )}
                
                {endpoint.data.orderCount !== undefined && (
                  <Text style={styles.detailText}>
                    <Text style={styles.detailLabel}>Orders: </Text>
                    {endpoint.data.orderCount}
                  </Text>
                )}
                
                {endpoint.data.orderCounts && (
                  <View style={styles.statsContainer}>
                    <Text style={styles.detailLabel}>Order Status Counts:</Text>
                    {Object.entries(endpoint.data.orderCounts).map(([key, value]) => (
                      <Text key={key} style={styles.statItem}>
                        {key}: {value}
                      </Text>
                    ))}
                  </View>
                )}
                
                {endpoint.data.sample && (
                  <View style={styles.sampleContainer}>
                    <Text style={styles.detailLabel}>Sample Data:</Text>
                    <Text style={styles.sampleText}>
                      ID: {endpoint.data.sample.id}{'\n'}
                      {endpoint.data.sample.name && `Name: ${endpoint.data.sample.name}\n`}
                      {endpoint.data.sample.status && `Status: ${endpoint.data.sample.status}\n`}
                      {endpoint.data.sample.price && `Price: $${endpoint.data.sample.price}`}
                    </Text>
                  </View>
                )}
                
                {endpoint.data.structure && (
                  <Text style={styles.detailText}>
                    <Text style={styles.detailLabel}>Data Structure: </Text>
                    {JSON.stringify(endpoint.data.structure)}
                  </Text>
                )}
              </View>
            )}
            
            {/* Table Data from Controller */}
            {endpoint.tableData && (
              <View style={styles.dataSection}>
                <Text style={styles.sectionTitle}>Controller Data</Text>
                
                {/* Show view full data button */}
                <TouchableOpacity 
                  style={styles.viewDataButton}
                  onPress={() => {
                    setSelectedEndpoint({ key, endpoint });
                    setModalVisible(true);
                  }}
                >
                  <Text style={styles.viewDataButtonText}>View Complete Data</Text>
                </TouchableOpacity>
                
                {/* Catalog Data */}
                {endpoint.tableData.type === 'catalog' && endpoint.tableData.products && (
                  <View style={styles.tableDataContainer}>
                    <Text style={styles.tableTitle}>Products ({endpoint.tableData.products.length})</Text>
                    {endpoint.tableData.products.slice(0, 3).map((product, idx) => (
                      <View key={idx} style={styles.tableRow}>
                        <Text style={styles.tableItemText}>#{product.id}: {product.name}</Text>
                        <Text style={styles.tableItemText}>Price: ${product.price}</Text>
                        <Text style={styles.tableItemText}>Stock: {product.inventory}</Text>
                      </View>
                    ))}
                    {endpoint.tableData.products.length > 3 && (
                      <Text style={styles.moreItemsText}>
                        + {endpoint.tableData.products.length - 3} more products
                      </Text>
                    )}
                  </View>
                )}
                
                {/* Orders Data */}
                {endpoint.tableData.type === 'orders' && endpoint.tableData.orders && (
                  <View style={styles.tableDataContainer}>
                    <Text style={styles.tableTitle}>Orders ({endpoint.tableData.orders.length})</Text>
                    {endpoint.tableData.orders.slice(0, 3).map((order, idx) => (
                      <View key={idx} style={styles.tableRow}>
                        <Text style={styles.tableItemText}>#{order.id} - {order.status}</Text>
                        <Text style={styles.tableItemText}>Total: ${order.total}</Text>
                        <Text style={styles.tableItemText}>Items: {order.items_count}</Text>
                      </View>
                    ))}
                    {endpoint.tableData.orders.length > 3 && (
                      <Text style={styles.moreItemsText}>
                        + {endpoint.tableData.orders.length - 3} more orders
                      </Text>
                    )}
                  </View>
                )}
                
                {/* Dashboard Data */}
                {endpoint.tableData.type === 'dashboard' && endpoint.tableData.stats && (
                  <View style={styles.tableDataContainer}>
                    <Text style={styles.tableTitle}>Dashboard Stats</Text>
                    {Object.entries(endpoint.tableData.stats).map(([key, value], idx) => (
                      <View key={idx} style={styles.statRow}>
                        <Text style={styles.statKey}>{key.replace(/_/g, ' ')}:</Text>
                        <Text style={styles.statValue}>{value}</Text>
                      </View>
                    ))}
                  </View>
                )}
                
                {/* Cart Data */}
                {endpoint.tableData.type === 'cart' && endpoint.tableData.items && (
                  <View style={styles.tableDataContainer}>
                    <Text style={styles.tableTitle}>Cart Items ({endpoint.tableData.items.length})</Text>
                    {endpoint.tableData.items.slice(0, 3).map((item, idx) => (
                      <View key={idx} style={styles.tableRow}>
                        <Text style={styles.tableItemText}>{item.product_name}</Text>
                        <Text style={styles.tableItemText}>Qty: {item.quantity} × ${item.price}</Text>
                        <Text style={styles.tableItemText}>Subtotal: ${item.subtotal}</Text>
                      </View>
                    ))}
                    {endpoint.tableData.items.length > 3 && (
                      <Text style={styles.moreItemsText}>
                        + {endpoint.tableData.items.length - 3} more items
                      </Text>
                    )}
                    <Text style={styles.cartTotal}>Cart Total: ${endpoint.tableData.total}</Text>
                  </View>
                )}
                
                {/* Profile Data */}
                {endpoint.tableData.type === 'profile' && endpoint.tableData.user && (
                  <View style={styles.tableDataContainer}>
                    <Text style={styles.tableTitle}>User Profile</Text>
                    <View style={styles.profileRow}>
                      <Text style={styles.profileField}>User ID:</Text>
                      <Text style={styles.profileValue}>{endpoint.tableData.user.id}</Text>
                    </View>
                    <View style={styles.profileRow}>
                      <Text style={styles.profileField}>Name:</Text>
                      <Text style={styles.profileValue}>{endpoint.tableData.user.name}</Text>
                    </View>
                    <View style={styles.profileRow}>
                      <Text style={styles.profileField}>Email:</Text>
                      <Text style={styles.profileValue}>{endpoint.tableData.user.email}</Text>
                    </View>
                    <View style={styles.profileRow}>
                      <Text style={styles.profileField}>Role:</Text>
                      <Text style={styles.profileValue}>{endpoint.tableData.user.role}</Text>
                    </View>
                  </View>
                )}
              </View>
            )}
          </View>
        )}
      </View>
    );
  };

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Comprehensive API Test</Text>
        <Text style={styles.baseUrl}>{BASE_URL}</Text>
      </View>

      <ScrollView style={styles.content}>
        {error ? (
          <View style={styles.errorContainer}>
            <Text style={styles.errorText}>{error}</Text>
          </View>
        ) : null}

        <View style={styles.formContainer}>
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
              <Text style={styles.buttonText}>Run Comprehensive API Test</Text>
            )}
          </TouchableOpacity>
        </View>

        {loading && (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color="#0066cc" />
            <Text style={styles.loadingText}>Running API tests...</Text>
          </View>
        )}

        {results && (
          <View style={styles.resultsContainer}>
            <View style={styles.summaryContainer}>
              <Text style={styles.summaryTitle}>Test Results Summary</Text>
              
              <View style={styles.statsRow}>
                <View style={styles.statBox}>
                  <Text style={styles.statCount}>{results.summary.total}</Text>
                  <Text style={styles.statLabel}>Total</Text>
                </View>
                
                <View style={[styles.statBox, styles.passedBox]}>
                  <Text style={styles.statCount}>{results.summary.passed}</Text>
                  <Text style={styles.statLabel}>Passed</Text>
                </View>
                
                <View style={[styles.statBox, styles.failedBox]}>
                  <Text style={styles.statCount}>{results.summary.failed}</Text>
                  <Text style={styles.statLabel}>Failed</Text>
                </View>
                
                <View style={[styles.statBox, styles.skippedBox]}>
                  <Text style={styles.statCount}>{results.summary.skipped}</Text>
                  <Text style={styles.statLabel}>Skipped</Text>
                </View>
              </View>
              
              <Text style={styles.authStatus}>
                Authentication: 
                <Text style={results.authStatus === 'authenticated' ? styles.successText : styles.errorText}>
                  {' '}{results.authStatus}
                </Text>
              </Text>
            </View>
            
            <TouchableOpacity
              style={styles.shareButton}
              onPress={shareReport}
            >
              <Text style={styles.shareButtonText}>Share Detailed Report</Text>
            </TouchableOpacity>
            
            <Text style={styles.sectionTitle}>API Endpoint Results</Text>
            
            <FlatList
              data={Object.entries(results.endpoints)}
              renderItem={renderEndpointItem}
              keyExtractor={([key]) => key}
              scrollEnabled={false}
            />
          </View>
        )}
      </ScrollView>
      
      {/* Full Data Modal */}
      {renderDataModal()}
    </SafeAreaView>
  );
};

  // Modal display for full data
  const renderDataModal = () => {
    if (!selectedEndpoint) return null;
    
    const { endpoint } = selectedEndpoint;
    const fullData = endpoint.fullData;
    const tableData = endpoint.tableData;
    
    return (
      <Modal
        visible={modalVisible}
        transparent={true}
        animationType="slide"
        onRequestClose={() => setModalVisible(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>{endpoint.name} - Full Data</Text>
              <TouchableOpacity 
                style={styles.closeButton}
                onPress={() => setModalVisible(false)}
              >
                <Text style={styles.closeButtonText}>Close</Text>
              </TouchableOpacity>
            </View>
            
            <ScrollView style={styles.modalBody}>
              {/* Display data structure */}
              {endpoint.dataStructure && (
                <View style={styles.structureSection}>
                  <Text style={styles.sectionTitle}>Data Structure</Text>
                  {endpoint.dataStructure.type === 'object' && (
                    <View style={styles.keysList}>
                      <Text style={styles.structureLabel}>Keys:</Text>
                      {endpoint.dataStructure.keys.map((key, idx) => (
                        <Text key={idx} style={styles.keyItem}>{key}</Text>
                      ))}
                    </View>
                  )}
                </View>
              )}
              
              {/* Display table data in full */}
              {tableData && (
                <View style={styles.fullTableSection}>
                  <Text style={styles.sectionTitle}>Table Data</Text>
                  
                  {/* Catalog data */}
                  {tableData.type === 'catalog' && tableData.products && (
                    <View>
                      <Text style={styles.tableTitle}>Products ({tableData.products.length})</Text>
                      {tableData.products.map((product, idx) => (
                        <View key={idx} style={styles.fullTableRow}>
                          <Text style={styles.tableHeader}>Product #{product.id}</Text>
                          <Text style={styles.tableItemText}>Name: {product.name}</Text>
                          <Text style={styles.tableItemText}>Price: ${product.price}</Text>
                          <Text style={styles.tableItemText}>Stock: {product.inventory}</Text>
                          <Text style={styles.tableItemText}>Category: {product.category}</Text>
                        </View>
                      ))}
                    </View>
                  )}
                  
                  {/* Orders data */}
                  {tableData.type === 'orders' && tableData.orders && (
                    <View>
                      <Text style={styles.tableTitle}>Orders ({tableData.orders.length})</Text>
                      {tableData.orders.map((order, idx) => (
                        <View key={idx} style={styles.fullTableRow}>
                          <Text style={styles.tableHeader}>Order #{order.id}</Text>
                          <Text style={styles.tableItemText}>Status: {order.status}</Text>
                          <Text style={styles.tableItemText}>Total: ${order.total}</Text>
                          <Text style={styles.tableItemText}>Date: {order.date}</Text>
                          <Text style={styles.tableItemText}>Items: {order.items_count}</Text>
                          {order.shipping_address && (
                            <Text style={styles.tableItemText}>Ship to: {order.shipping_address}</Text>
                          )}
                          
                          {/* Order Items Section */}
                          {order.items && order.items.length > 0 && (
                            <View style={styles.itemsSection}>
                              <Text style={styles.itemsSectionTitle}>Order Items:</Text>
                              {order.items.map((item, itemIdx) => (
                                <View key={itemIdx} style={styles.itemRow}>
                                  <Text style={styles.itemName}>
                                    {item.product_name} {item.variant_name ? `(${item.variant_name})` : ''}
                                  </Text>
                                  <Text style={styles.itemDetail}>
                                    {item.quantity} x ${item.price} = ${item.subtotal}
                                  </Text>
                                </View>
                              ))}
                            </View>
                          )}
                        </View>
                      ))}
                    </View>
                  )}
                  
                  {/* Dashboard data */}
                  {tableData.type === 'dashboard' && (
                    <View>
                      {tableData.stats && (
                        <View style={styles.dashboardSection}>
                          <Text style={styles.tableTitle}>Dashboard Stats</Text>
                          {Object.entries(tableData.stats).map(([key, value], idx) => (
                            <View key={idx} style={styles.statRow}>
                              <Text style={styles.statKey}>{key.replace(/_/g, ' ')}:</Text>
                              <Text style={styles.statValue}>{value}</Text>
                            </View>
                          ))}
                        </View>
                      )}
                      
                      {tableData.recentOrders && tableData.recentOrders.length > 0 && (
                        <View style={styles.dashboardSection}>
                          <Text style={styles.tableTitle}>Recent Orders</Text>
                          {tableData.recentOrders.map((order, idx) => (
                            <View key={idx} style={styles.orderRow}>
                              <Text style={styles.orderHeader}>Order #{order.id}</Text>
                              <Text style={styles.orderDetail}>Status: {order.status}</Text>
                              <Text style={styles.orderDetail}>Total: ${order.total_amount || order.total}</Text>
                            </View>
                          ))}
                        </View>
                      )}
                      
                      {tableData.popularProducts && tableData.popularProducts.length > 0 && (
                        <View style={styles.dashboardSection}>
                          <Text style={styles.tableTitle}>Popular Products</Text>
                          {tableData.popularProducts.map((product, idx) => (
                            <View key={idx} style={styles.productRow}>
                              <Text style={styles.productHeader}>{product.name}</Text>
                              <Text style={styles.productDetail}>Price: ${product.price || product.base_price}</Text>
                            </View>
                          ))}
                        </View>
                      )}
                    </View>
                  )}
                  
                  {/* Cart data */}
                  {tableData.type === 'cart' && tableData.items && (
                    <View>
                      <Text style={styles.tableTitle}>Cart Items ({tableData.items.length})</Text>
                      <View style={styles.cartSummary}>
                        <Text style={styles.cartTotal}>Cart Total: ${tableData.total}</Text>
                        <Text style={styles.cartItemCount}>Total Items: {tableData.items.reduce((sum, item) => sum + item.quantity, 0)}</Text>
                      </View>
                      
                      {/* Cart Items Table */}
                      <View style={styles.cartItemsTable}>
                        <View style={styles.cartTableHeader}>
                          <Text style={styles.cartColumnHeader}>Product</Text>
                          <Text style={styles.cartColumnHeader}>Qty</Text>
                          <Text style={styles.cartColumnHeader}>Price</Text>
                          <Text style={styles.cartColumnHeader}>Subtotal</Text>
                        </View>
                        
                        {tableData.items.map((item, idx) => (
                          <View key={idx} style={styles.cartTableRow}>
                            <View style={styles.cartProductColumn}>
                              <Text style={styles.cartProductName}>{item.product_name}</Text>
                              {item.variant_name && (
                                <Text style={styles.cartVariantName}>Variant: {item.variant_name}</Text>
                              )}
                            </View>
                            <Text style={styles.cartQuantity}>{item.quantity}</Text>
                            <Text style={styles.cartPrice}>${item.price}</Text>
                            <Text style={styles.cartSubtotal}>${item.subtotal}</Text>
                          </View>
                        ))}
                      </View>
                    </View>
                  )}
                </View>
              )}
              
              {/* Raw JSON data */}
              {fullData && (
                <View style={styles.rawDataSection}>
                  <Text style={styles.sectionTitle}>Raw Response Data</Text>
                  <View style={styles.rawJsonContainer}>
                    <Text style={styles.rawJsonText}>
                      {JSON.stringify(fullData, null, 2)}
                    </Text>
                  </View>
                </View>
              )}
            </ScrollView>
          </View>
        </View>
      </Modal>
    );
  };

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  header: {
    backgroundColor: '#0066cc',
    padding: 15,
    paddingTop: 20,
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: 'white',
  },
  baseUrl: {
    fontSize: 14,
    color: 'rgba(255, 255, 255, 0.8)',
    marginTop: 5,
  },
  content: {
    flex: 1,
    padding: 15,
  },
  errorContainer: {
    backgroundColor: '#ffebee',
    padding: 12,
    borderRadius: 5,
    marginBottom: 15,
  },
  errorText: {
    color: '#c62828',
  },
  warningLabel: {
    fontWeight: 'bold',
    color: '#f57c00',
  },
  successText: {
    color: '#2e7d32',
  },
  formContainer: {
    backgroundColor: 'white',
    borderRadius: 8,
    padding: 15,
    marginBottom: 20,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
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
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 5,
    padding: 10,
    fontSize: 16,
  },
  button: {
    backgroundColor: '#0066cc',
    padding: 15,
    borderRadius: 5,
    alignItems: 'center',
    justifyContent: 'center',
  },
  buttonText: {
    color: 'white',
    fontSize: 16,
    fontWeight: 'bold',
  },
  loadingContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    padding: 20,
  },
  loadingText: {
    marginTop: 10,
    fontSize: 16,
    color: '#666',
  },
  resultsContainer: {
    marginBottom: 20,
  },
  summaryContainer: {
    backgroundColor: 'white',
    borderRadius: 8,
    padding: 15,
    marginBottom: 20,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
  },
  summaryTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 15,
  },
  statsRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 15,
  },
  statBox: {
    alignItems: 'center',
    padding: 10,
    borderRadius: 5,
    backgroundColor: '#f0f0f0',
    minWidth: '22%',
  },
  passedBox: {
    backgroundColor: 'rgba(46, 125, 50, 0.1)',
  },
  failedBox: {
    backgroundColor: 'rgba(198, 40, 40, 0.1)',
  },
  skippedBox: {
    backgroundColor: 'rgba(117, 117, 117, 0.1)',
  },
  statCount: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
  },
  statLabel: {
    fontSize: 12,
    color: '#666',
    marginTop: 5,
  },
  authStatus: {
    fontSize: 16,
    color: '#333',
  },
  shareButton: {
    backgroundColor: '#f0f0f0',
    padding: 12,
    borderRadius: 5,
    alignItems: 'center',
    marginBottom: 20,
    borderWidth: 1,
    borderColor: '#ddd',
  },
  shareButtonText: {
    color: '#0066cc',
    fontSize: 16,
    fontWeight: '500',
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 15,
  },
  endpointCard: {
    backgroundColor: 'white',
    borderRadius: 8,
    marginBottom: 15,
    elevation: 1,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 1,
    overflow: 'hidden',
  },
  endpointHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 15,
    backgroundColor: '#f9f9f9',
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  endpointName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    flex: 1,
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
    fontSize: 12,
    fontWeight: 'bold',
    overflow: 'hidden',
    color: 'white',
    textAlign: 'center',
  },
  statusSuccess: {
    backgroundColor: '#2e7d32',
  },
  statusFailed: {
    backgroundColor: '#c62828',
  },
  statusSkipped: {
    backgroundColor: '#757575',
  },
  endpointMessage: {
    padding: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  expandedContent: {
    padding: 15,
    backgroundColor: '#f9f9f9',
  },
  detailText: {
    fontSize: 14,
    color: '#333',
    marginBottom: 8,
  },
  detailLabel: {
    fontWeight: 'bold',
    color: '#555',
  },
  dataSection: {
    marginTop: 10,
    padding: 10,
    backgroundColor: 'white',
    borderRadius: 5,
  },
  statsContainer: {
    marginVertical: 10,
  },
  statItem: {
    fontSize: 14,
    color: '#555',
    marginLeft: 10,
    marginTop: 2,
  },
  sampleContainer: {
    marginVertical: 10,
    padding: 10,
    backgroundColor: '#f0f4f8',
    borderRadius: 5,
  },
  sampleText: {
    fontSize: 14,
    color: '#555',
    marginTop: 5,
  },
  
  // View Data Button styles
  viewDataButton: {
    backgroundColor: '#e1f5fe',
    padding: 10,
    borderRadius: 5,
    alignItems: 'center',
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#b3e5fc',
  },
  viewDataButtonText: {
    color: '#0277bd',
    fontSize: 14,
    fontWeight: '500',
  },
  
  // Table Data styles
  tableDataContainer: {
    marginVertical: 10,
    backgroundColor: '#f9f9f9',
    borderRadius: 5,
    padding: 10,
    borderWidth: 1,
    borderColor: '#eee',
  },
  tableTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 10,
  },
  tableRow: {
    paddingVertical: 8,
    paddingHorizontal: 10,
    backgroundColor: 'white',
    borderRadius: 5,
    marginBottom: 8,
    borderLeftWidth: 3,
    borderLeftColor: '#0066cc',
  },
  tableItemText: {
    fontSize: 14,
    color: '#333',
    marginBottom: 4,
  },
  moreItemsText: {
    fontSize: 14,
    color: '#666',
    textAlign: 'center',
    marginTop: 8,
    fontStyle: 'italic',
  },
  cartTotal: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    textAlign: 'right',
    marginTop: 10,
  },
  cartSummary: {
    backgroundColor: '#e8f5e9',
    padding: 10,
    borderRadius: 5,
    marginBottom: 15,
    borderLeftWidth: 3,
    borderLeftColor: '#43a047',
  },
  cartItemCount: {
    fontSize: 14,
    color: '#555',
    textAlign: 'right',
    marginTop: 5,
  },
  cartItemsTable: {
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 5,
    overflow: 'hidden',
  },
  cartTableHeader: {
    flexDirection: 'row',
    backgroundColor: '#f5f5f5',
    padding: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#ddd',
  },
  cartColumnHeader: {
    fontWeight: 'bold',
    fontSize: 14,
    color: '#333',
  },
  cartTableRow: {
    flexDirection: 'row',
    padding: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
    backgroundColor: 'white',
  },
  cartProductColumn: {
    flex: 2,
  },
  cartProductName: {
    fontSize: 14,
    fontWeight: '500',
    color: '#333',
  },
  cartVariantName: {
    fontSize: 12,
    color: '#666',
    fontStyle: 'italic',
  },
  cartQuantity: {
    flex: 0.5,
    fontSize: 14,
    color: '#333',
    textAlign: 'center',
  },
  cartPrice: {
    flex: 0.75,
    fontSize: 14,
    color: '#333',
    textAlign: 'right',
  },
  cartSubtotal: {
    flex: 0.75,
    fontSize: 14,
    fontWeight: '500',
    color: '#333',
    textAlign: 'right',
  },
  
  // Profile data styles
  profileRow: {
    flexDirection: 'row',
    paddingVertical: 6,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  profileField: {
    width: 80,
    fontWeight: 'bold',
    color: '#555',
  },
  profileValue: {
    flex: 1,
    color: '#333',
  },
  
  // Stats row styles
  statRow: {
    flexDirection: 'row',
    paddingVertical: 6,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  statKey: {
    width: 140,
    fontWeight: 'bold',
    color: '#555',
    textTransform: 'capitalize',
  },
  statValue: {
    flex: 1,
    color: '#333',
  },
  
  // Modal styles
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContent: {
    width: '90%',
    maxHeight: '80%',
    backgroundColor: 'white',
    borderRadius: 10,
    overflow: 'hidden',
    elevation: 5,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
  },
  modalHeader: {
    backgroundColor: '#0066cc',
    padding: 15,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  modalTitle: {
    color: 'white',
    fontSize: 18,
    fontWeight: 'bold',
  },
  closeButton: {
    padding: 5,
  },
  closeButtonText: {
    color: 'white',
    fontSize: 16,
    fontWeight: 'bold',
  },
  modalBody: {
    padding: 15,
    maxHeight: '90%',
  },
  
  // Structure section styles
  structureSection: {
    marginBottom: 20,
    padding: 10,
    backgroundColor: '#f0f4f8',
    borderRadius: 5,
  },
  keysList: {
    marginTop: 10,
  },
  structureLabel: {
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 5,
  },
  keyItem: {
    color: '#0066cc',
    marginLeft: 10,
    fontSize: 14,
    marginBottom: 2,
  },
  
  // Full table section styles
  fullTableSection: {
    marginVertical: 15,
  },
  fullTableRow: {
    marginBottom: 15,
    padding: 12,
    backgroundColor: 'white',
    borderRadius: 5,
    borderLeftWidth: 3,
    borderLeftColor: '#0066cc',
    elevation: 1,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 1,
  },
  tableHeader: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
    paddingBottom: 5,
  },
  itemsSection: {
    marginTop: 10,
    backgroundColor: '#f5f5f5',
    borderRadius: 5,
    padding: 10,
    borderLeftWidth: 3,
    borderLeftColor: '#4caf50',
  },
  itemsSectionTitle: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 8,
  },
  itemRow: {
    paddingVertical: 5,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  itemName: {
    fontSize: 14,
    fontWeight: '500',
    color: '#333',
  },
  itemDetail: {
    fontSize: 13,
    color: '#666',
    marginTop: 2,
  },
  
  // Dashboard specific styles
  dashboardSection: {
    marginBottom: 20,
    padding: 12,
    backgroundColor: '#f5f5f5',
    borderRadius: 8,
  },
  orderRow: {
    marginBottom: 10,
    padding: 10,
    backgroundColor: 'white',
    borderRadius: 5,
  },
  orderHeader: {
    fontSize: 15,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 5,
  },
  orderDetail: {
    fontSize: 14,
    color: '#555',
    marginLeft: 10,
  },
  productRow: {
    marginBottom: 10,
    padding: 10,
    backgroundColor: 'white',
    borderRadius: 5,
  },
  productHeader: {
    fontSize: 15,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 5,
  },
  productDetail: {
    fontSize: 14,
    color: '#555',
    marginLeft: 10,
  },
  
  // Raw JSON data styles
  rawDataSection: {
    marginTop: 20,
  },
  rawJsonContainer: {
    padding: 10,
    backgroundColor: '#263238',
    borderRadius: 5,
  },
  rawJsonText: {
    color: '#e0e0e0',
    fontFamily: 'monospace',
    fontSize: 12,
  },
});

export default ApiTestScreen;