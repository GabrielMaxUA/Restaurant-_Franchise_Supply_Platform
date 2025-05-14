import AsyncStorage from '@react-native-async-storage/async-storage';
import { login, getUserProfile, getCatalog, getPendingOrders } from '../services/api';
import { BASE_URL } from '../services/api';

// Function to test basic API connectivity
export const testApiConnection = async () => {
  try {
    // Try multiple endpoints that might work
    const testEndpoints = [
      '/test',
      '/ping',
      '/health',
      '/status',
      '' // Empty string to test the base API URL
    ];
    
    let lastError = null;
    
    for (const endpoint of testEndpoints) {
      try {
        console.log('Testing API connection to:', `${BASE_URL}${endpoint}`);
        
        // Create abort controller with timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 5000);
        
        const response = await fetch(`${BASE_URL}${endpoint}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
          },
          signal: controller.signal
        });
        
        // Clear the timeout
        clearTimeout(timeoutId);
        
        console.log(`API test response status for ${endpoint}:`, response.status);
        
        if (response.ok) {
          // Check content type before trying to parse as JSON
          const contentType = response.headers.get('content-type');
          console.log(`Content type for ${endpoint}:`, contentType);
          
          if (contentType && contentType.includes('application/json')) {
            try {
              const jsonData = await response.json();
              return {
                success: true,
                message: 'API connection successful',
                endpoint,
                contentType,
                data: jsonData
              };
            } catch (e) {
              // JSON parse failed but response was OK
              const textResponse = await response.text();
              return { 
                success: true,
                message: 'API connection successful but JSON parsing failed', 
                endpoint,
                contentType,
                text: textResponse.substring(0, 100) + (textResponse.length > 100 ? '...' : '')
              };
            }
          } else {
            // Handle non-JSON response
            const textResponse = await response.text();
            return { 
              success: true,
              message: 'API connection successful but returned non-JSON response', 
              endpoint,
              contentType: contentType || 'unknown',
              text: textResponse.substring(0, 100) + (textResponse.length > 100 ? '...' : '')
            };
          }
        }
      } catch (error) {
        console.warn(`API test to ${endpoint} failed:`, error.message);
        lastError = error;
        // Continue to try other endpoints
      }
    }
    
    // If we get here, all endpoints failed
    throw lastError || new Error('All API test endpoints failed');
  } catch (error) {
    console.error('API connection test failed:', error);
    
    // Return a structured error instead of throwing
    return {
      success: false,
      message: `API connection failed: ${error.message}`,
      error: error.message
    };
  }
};

/**
 * Simple utility to test API connections
 * This can be used during development to verify that API endpoints are working correctly
 * 
 * @param {Object} params - Parameters for the test
 * @param {string} params.email - User email for login
 * @param {string} params.password - User password for login
 * @returns {Promise<Object>} - Test results
 */
export const testApiConnections = async (params) => {
  const { email, password } = params;
  const results = {
    login: { success: false, message: '', data: null },
    profile: { success: false, message: '', data: null },
    catalog: { success: false, message: '', data: null },
    orders: { success: false, message: '', data: null },
  };

  try {
    console.log('🧪 Testing API connections...');
    console.log('API Base URL:', BASE_URL);
    
    // Test login
    console.log('Testing login API...');
    try {
      const loginResponse = await login(email, password);
      console.log('Login response:', JSON.stringify(loginResponse));
      results.login.success = loginResponse.success || false;
      results.login.message = loginResponse.success ? 'Login successful!' : (loginResponse.error || 'Login failed');
      results.login.data = loginResponse.success ? { 
        token: loginResponse.token ? 'Token received ✅' : 'No token returned ❌',
        user: loginResponse.user ? 'User data received ✅' : 'No user data ❌'
      } : null;

      // If login successful, store token for testing
      if (loginResponse.success && loginResponse.token) {
        console.log('Storing token in AsyncStorage for testing');
        try {
          await AsyncStorage.setItem('testUserToken', loginResponse.token);
          await AsyncStorage.setItem('testUserData', JSON.stringify(loginResponse.user || {}));
        } catch (storageError) {
          console.error('Failed to store test token:', storageError);
        }
      }
    } catch (loginError) {
      console.error('Login fetch error:', loginError);
      results.login.message = `Network error: ${loginError.message}`;
    }
    
    // If login successful, try other endpoints with this token directly
    if (results.login.success) {
      try {
        const testToken = await AsyncStorage.getItem('testUserToken');
        if (!testToken) {
          console.error('No test token available!');
        } else {
          console.log('Using test token for API calls');
          
          // Test profile with direct token
          try {
            console.log('Testing user profile API...');
            // Direct fetch instead of using the service
            const profileResponse = await fetch(`${BASE_URL}/auth/me`, {
              method: 'GET',
              headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${testToken}`
              },
            });
            
            console.log('Profile response status:', profileResponse.status);
            
            // Check content type
            const contentType = profileResponse.headers.get('content-type');
            console.log('Profile response content type:', contentType);
            
            let profileData;
            if (contentType && contentType.includes('application/json')) {
              profileData = await profileResponse.json();
            } else {
              const textResponse = await profileResponse.text();
              profileData = { 
                error: 'Non-JSON response', 
                preview: textResponse.substring(0, 100) + (textResponse.length > 100 ? '...' : '') 
              };
            }
            console.log('Profile response:', profileData);
            
            results.profile.success = profileResponse.status < 400;
            results.profile.message = profileResponse.status < 400 ? 
              'Profile retrieved successfully!' : `Failed with status ${profileResponse.status}`;
            results.profile.data = profileData;
            
          } catch (error) {
            console.error('Profile test error:', error);
            results.profile.message = `Error: ${error.message}`;
          }
          
          // Test catalog with direct token
          try {
            console.log('Testing catalog API...');
            
            // Try multiple catalog endpoints to find one that works
            const catalogEndpoints = [
              `${BASE_URL}/franchisee/catalog`,
              `${BASE_URL}/catalog`,
              `${BASE_URL}/products`,
              `${BASE_URL}/franchisee/products`
            ];
            
            let catalogResponse = null;
            let catalogData = null;
            let foundWorkingEndpoint = false;
            
            for (const endpoint of catalogEndpoints) {
              try {
                console.log(`Trying catalog endpoint: ${endpoint}`);
                catalogResponse = await fetch(endpoint, {
                  method: 'GET',
                  headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${testToken}`
                  },
                });
                
                console.log(`Catalog endpoint ${endpoint} status:`, catalogResponse.status);
                
                if (catalogResponse.ok) {
                  // Check content type
                  const contentType = catalogResponse.headers.get('content-type');
                  console.log(`Catalog endpoint ${endpoint} content type:`, contentType);
                  
                  if (contentType && contentType.includes('application/json')) {
                    catalogData = await catalogResponse.json();
                    console.log(`Catalog data from ${endpoint} (raw):`, catalogData);
                    
                    // Check for different possible data structures
                    let hasProducts = false;
                    
                    // Extract products using different possible structures
                    let extractedProducts = [];
                    if (Array.isArray(catalogData)) {
                      extractedProducts = catalogData;
                      hasProducts = true;
                    } else if (catalogData.data) {
                      if (Array.isArray(catalogData.data)) {
                        extractedProducts = catalogData.data;
                        hasProducts = true;
                      } else if (catalogData.data.data && Array.isArray(catalogData.data.data)) {
                        extractedProducts = catalogData.data.data;
                        hasProducts = true;
                      }
                    } else if (catalogData.products) {
                      extractedProducts = catalogData.products;
                      hasProducts = true;
                    }
                    
                    console.log(`Found ${extractedProducts.length} products from ${endpoint}`);
                    
                    if (hasProducts) {
                      foundWorkingEndpoint = true;
                      
                      // Store the results
                      results.catalog.success = true;
                      results.catalog.message = `Catalog retrieved successfully with ${extractedProducts.length} products!`;
                      results.catalog.data = {
                        endpoint,
                        productCount: extractedProducts.length,
                        sample: extractedProducts.length > 0 ? extractedProducts[0] : null,
                        structure: Object.keys(catalogData)
                      };
                      
                      break; // Exit the loop if we found products
                    }
                  } else {
                    const textResponse = await catalogResponse.text();
                    console.log(`Non-JSON response from ${endpoint}:`, textResponse.substring(0, 100));
                  }
                }
              } catch (e) {
                console.warn(`Error testing catalog endpoint ${endpoint}:`, e.message);
              }
            }
            
            if (!foundWorkingEndpoint) {
              // If we didn't find any working endpoint with products
              results.catalog.success = catalogResponse && catalogResponse.ok;
              results.catalog.message = results.catalog.success ? 
                'Catalog API responded successfully, but no products were found' : 
                'Failed to retrieve catalog data';
              results.catalog.data = catalogData || { error: 'No catalog data found' };
            }
            
          } catch (error) {
            console.error('Catalog test error:', error);
            results.catalog.message = `Error: ${error.message}`;
          }
          
          // Test orders with direct token
          try {
            console.log('Testing orders API...');
            
            // Try multiple order endpoints to find one that works
            const orderEndpoints = [
              `${BASE_URL}/franchisee/orders/pending`,
              `${BASE_URL}/franchisee/orders`,
              `${BASE_URL}/orders/pending`,
              `${BASE_URL}/orders`
            ];
            
            let ordersResponse = null;
            let ordersData = null;
            let foundWorkingEndpoint = false;
            
            for (const endpoint of orderEndpoints) {
              try {
                console.log(`Trying orders endpoint: ${endpoint}`);
                ordersResponse = await fetch(endpoint, {
                  method: 'GET',
                  headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${testToken}`
                  },
                });
                
                console.log(`Orders endpoint ${endpoint} status:`, ordersResponse.status);
                
                if (ordersResponse.ok) {
                  // Check content type
                  const contentType = ordersResponse.headers.get('content-type');
                  console.log(`Orders endpoint ${endpoint} content type:`, contentType);
                  
                  if (contentType && contentType.includes('application/json')) {
                    ordersData = await ordersResponse.json();
                    console.log(`Orders data from ${endpoint} (raw):`, ordersData);
                    
                    // Check for different possible data structures
                    let hasOrders = false;
                    
                    // Extract orders using different possible structures
                    let extractedOrders = [];
                    if (Array.isArray(ordersData)) {
                      extractedOrders = ordersData;
                      hasOrders = true;
                    } else if (ordersData.data) {
                      if (Array.isArray(ordersData.data)) {
                        extractedOrders = ordersData.data;
                        hasOrders = true;
                      } else if (ordersData.data.data && Array.isArray(ordersData.data.data)) {
                        extractedOrders = ordersData.data.data;
                        hasOrders = true;
                      }
                    } else if (ordersData.orders) {
                      extractedOrders = ordersData.orders;
                      hasOrders = true;
                    }
                    
                    // Also check for order_counts which is present in your expected data
                    const hasOrderCounts = ordersData.order_counts !== undefined;
                    
                    console.log(`Found ${extractedOrders.length} orders from ${endpoint}`);
                    
                    if (hasOrders || hasOrderCounts) {
                      foundWorkingEndpoint = true;
                      
                      // Store the results
                      results.orders.success = true;
                      results.orders.message = `Orders retrieved successfully! ${hasOrders ? `Found ${extractedOrders.length} orders.` : ''}`;
                      results.orders.data = {
                        endpoint,
                        orderCount: extractedOrders.length,
                        sample: extractedOrders.length > 0 ? extractedOrders[0] : null,
                        orderCounts: ordersData.order_counts,
                        structure: Object.keys(ordersData)
                      };
                      
                      break; // Exit the loop if we found orders
                    }
                  } else {
                    const textResponse = await ordersResponse.text();
                    console.log(`Non-JSON response from ${endpoint}:`, textResponse.substring(0, 100));
                  }
                }
              } catch (e) {
                console.warn(`Error testing orders endpoint ${endpoint}:`, e.message);
              }
            }
            
            if (!foundWorkingEndpoint) {
              // If we didn't find any working endpoint with orders
              results.orders.success = ordersResponse && ordersResponse.ok;
              results.orders.message = results.orders.success ? 
                'Orders API responded successfully, but no orders were found' : 
                'Failed to retrieve orders data';
              results.orders.data = ordersData || { error: 'No orders data found' };
            }
            
          } catch (error) {
            console.error('Orders test error:', error);
            results.orders.message = `Error: ${error.message}`;
          }
        }
      } catch (error) {
        console.error('Token retrieval error:', error);
      }
    }
    
  } catch (error) {
    console.error('API test error:', error);
    results.login.message = `Error: ${error.message}`;
  }
  
  console.log('📋 API Test Results:', results);
  return results;
};

// Test a specific API endpoint with auth token
export const testApiEndpoint = async (endpoint, method = 'GET', body = null) => {
  try {
    // Get stored token
    const token = await AsyncStorage.getItem('userToken');
    
    if (!token) {
      return {
        success: false,
        message: 'No authentication token found. Please login first.',
        endpoint,
      };
    }
    
    const headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
    };
    
    const requestOptions = {
      method,
      headers,
    };
    
    if (body && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
      requestOptions.body = JSON.stringify(body);
    }
    
    console.log(`Testing endpoint: ${BASE_URL}${endpoint}`);
    console.log('Request options:', requestOptions);
    
    const response = await fetch(`${BASE_URL}${endpoint}`, requestOptions);
    console.log(`Endpoint ${endpoint} response status:`, response.status);
    
    // Check content type
    const contentType = response.headers.get('content-type');
    console.log(`${endpoint} response content type:`, contentType);
    
    let responseData;
    if (contentType && contentType.includes('application/json')) {
      try {
        responseData = await response.json();
      } catch (e) {
        responseData = { error: 'Failed to parse response JSON' };
      }
    } else {
      try {
        const textResponse = await response.text();
        responseData = { 
          error: 'Non-JSON response', 
          preview: textResponse.substring(0, 200) + (textResponse.length > 200 ? '...' : ''),
          contentType: contentType || 'unknown'
        };
      } catch (e) {
        responseData = { error: 'Failed to parse response' };
      }
    }
    
    return {
      success: response.ok,
      status: response.status,
      statusText: response.statusText,
      data: responseData,
      endpoint,
    };
  } catch (error) {
    console.error(`API endpoint ${endpoint} test failed:`, error);
    return {
      success: false,
      error: error.message,
      endpoint,
    };
  }
};

export default testApiConnections;