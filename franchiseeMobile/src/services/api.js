// /**
//  * API Service for the Franchisee Mobile App
//  * Handles all API requests to the backend server
//  */

// // Base API URL for local XAMPP server
// // This should point to your Laravel API endpoint
// // Using IP address rather than localhost for better device compatibility
// // Note: You may need to replace this with your actual machine's IP address
// export const BASE_URL = 'http://127.0.0.1:8000/api'; // Changed from 10.0.2.2 to 127.0.0.1 for direct access
// // For different environments:
// // - Use 'http://10.0.2.2:8000/api' for Android emulator access to host machine
// // - Use 'http://localhost:8000/api' for iOS simulator

// // Default headers for API requests
// const DEFAULT_HEADERS = {
//   'Content-Type': 'application/json',
//   'Accept': 'application/json',
// };

// /**
//  * Helper function to add authentication token to headers
//  * @param {string} token - JWT authentication token
//  * @returns {Object} - Headers object with Authorization header
//  */
// const authHeaders = (token) => {
//   // Debug log to verify token format
//   console.log('Auth Token used:', token ? `${token.substring(0, 10)}...` : 'No token provided');
  
//   if (!token) {
//     console.warn('No authentication token provided!');
//     return DEFAULT_HEADERS;
//   }
  
//   return {
//     ...DEFAULT_HEADERS,
//     'Authorization': `Bearer ${token}`,
//   };
// };

// /**
//  * Helper function to process API response
//  * Handles content-type checking and HTML response handling
//  * @param {Response} response - Fetch API response
//  * @param {string} endpoint - API endpoint for logging
//  * @returns {Promise} - Processed response (JSON or error)
//  */
// const processResponse = async (response, endpoint) => {
//   try {
//     if (response.ok) {
//       // Check content type before trying to parse as JSON
//       const contentType = response.headers.get('content-type');
//       console.log(`Content type for ${endpoint}:`, contentType);
      
//       if (contentType && contentType.includes('application/json')) {
//         try {
//           const jsonResponse = await response.json();
//           console.log(`Response from ${endpoint}:`, jsonResponse);
          
//           // Ensure the response has a success property
//           if (jsonResponse.success === undefined) {
//             jsonResponse.success = true; // Assume success for Laravel responses without explicit success flag
//           }
          
//           return jsonResponse;
//         } catch (error) {
//           console.error(`JSON parse error for ${endpoint}:`, error);
//           return { 
//             success: true,  // We still count this as a success because the response was OK
//             error: 'JSON parse error',
//             message: 'JSON parse error: ' + error.message,
//             rawResponse: true,
//             endpoint
//           };
//         }
//       } else {
//         // Handle non-JSON response
//         const textResponse = await response.text();
//         console.warn(`Non-JSON response from ${endpoint}:`, textResponse.substring(0, 150) + '...');
        
//         // For OK responses that aren't JSON, we'll still return a structured success response
//         return {
//           success: true,  // Count as success because the response was OK
//           message: 'API request succeeded but returned non-JSON response',
//           contentType: contentType || 'unknown',
//           textPreview: textResponse.substring(0, 200) + (textResponse.length > 200 ? '...' : ''),
//           rawResponse: true,
//           endpoint
//         };
//       }
//     }
    
//     // Handle error responses
//     try {
//       // Try to parse error as JSON
//       const contentType = response.headers.get('content-type');
      
//       if (contentType && contentType.includes('application/json')) {
//         const errorJson = await response.json();
//         console.error(`JSON error response for ${endpoint}:`, errorJson);
//         return {
//           success: false,
//           error: errorJson.message || `API error (${response.status})`,
//           status: response.status,
//           data: errorJson,
//           endpoint
//         };
//       } else {
//         // Try to get text for non-JSON errors
//         const errorText = await response.text();
//         console.error(`Text error response for ${endpoint}:`, errorText.substring(0, 150));
//         return {
//           success: false,
//           error: `API error (${response.status})`,
//           message: errorText.substring(0, 300),
//           status: response.status,
//           contentType: contentType || 'unknown',
//           endpoint
//         };
//       }
//     } catch (e) {
//       // If all else fails
//       console.error(`Failed to parse error response for ${endpoint}:`, e);
//       return {
//         success: false,
//         error: `API error (${response.status})`,
//         message: e.message,
//         status: response.status,
//         endpoint
//       };
//     }
//   } catch (unexpectedError) {
//     // Catch any unexpected errors during response processing
//     console.error(`Unexpected error processing response for ${endpoint}:`, unexpectedError);
//     return {
//       success: false,
//       error: 'Unexpected error processing response',
//       message: unexpectedError.message,
//       endpoint
//     };
//   }
// };

// /**
//  * Login user and get authentication token
//  * @param {string} email - User email
//  * @param {string} password - User password
//  * @returns {Promise} - API response
//  */
// export const login = async (email, password) => {
//   try {
//     const endpoint = `${BASE_URL}/auth/login`;
//     console.log(`Trying to login to: ${endpoint}`);
//     console.log('With credentials:', { email, password: '****' });
    
//     const response = await fetch(endpoint, {
//       method: 'POST',
//       headers: DEFAULT_HEADERS,
//       body: JSON.stringify({ email, password }),
//     });
    
//     console.log('Login response status:', response.status);
//     return await processResponse(response, endpoint);
//   } catch (error) {
//     console.error('Login error:', error);
//     throw error;
//   }
// };

// /**
//  * Get current user profile
//  * @param {string} token - JWT authentication token
//  * @returns {Promise} - API response
//  */
// export const getUserProfile = async (token) => {
//   try {
//     // Try alternate endpoints based on common Laravel API patterns
//     const endpoints = [
//       `${BASE_URL}/auth/me`,
//       `${BASE_URL}/user/profile`,
//       `${BASE_URL}/user`,
//       `${BASE_URL}/franchisee/profile`,
//     ];
    
//     let response = null;
//     let lastError = null;
    
//     // Try each endpoint until one works
//     for (const endpoint of endpoints) {
//       try {
//         console.log(`Trying profile endpoint: ${endpoint}`);
//         response = await fetch(endpoint, {
//           method: 'GET',
//           headers: authHeaders(token),
//         });
        
//         if (response.ok) {
//           console.log(`Found working profile endpoint: ${endpoint}`);
//           return await processResponse(response, endpoint);
//         }
//       } catch (e) {
//         lastError = e;
//         console.warn(`Endpoint ${endpoint} failed:`, e.message);
//       }
//     }
    
//     throw lastError || new Error('All profile endpoints failed');
//   } catch (error) {
//     console.error('Get user profile error:', error);
//     throw error;
//   }
// };

// /**
//  * Get catalog products with optional filters
//  * @param {string} token - JWT authentication token
//  * @param {Object} filters - Optional filters like category, search, etc.
//  * @returns {Promise} - API response
//  */
// export const getCatalog = async (token, filters = {}) => {
//   try {
//     // Build query string from filters
//     const queryParams = new URLSearchParams();
//     Object.keys(filters).forEach(key => {
//       if (filters[key]) {
//         queryParams.append(key, filters[key]);
//       }
//     });

//     const queryString = queryParams.toString() ? `?${queryParams.toString()}` : '';
    
//     // Common catalog endpoint patterns
//     const endpoints = [
//       `${BASE_URL}/franchisee/catalog${queryString}`,
//       `${BASE_URL}/catalog${queryString}`,
//       `${BASE_URL}/products${queryString}`, 
//       `${BASE_URL}/franchisee/products${queryString}`
//     ];
    
//     let response = null;
//     let lastError = null;
    
//     // Try each endpoint until one works
//     for (const endpoint of endpoints) {
//       try {
//         console.log(`Trying catalog endpoint: ${endpoint}`);
//         response = await fetch(endpoint, {
//           method: 'GET',
//           headers: authHeaders(token),
//         });
        
//         if (response.ok) {
//           console.log(`Found working catalog endpoint: ${endpoint}`);
//           return await processResponse(response, endpoint);
//         }
//       } catch (e) {
//         lastError = e;
//         console.warn(`Endpoint ${endpoint} failed:`, e.message);
//       }
//     }
    
//     throw lastError || new Error('All catalog endpoints failed');
//   } catch (error) {
//     console.error('Get catalog error:', error);
//     throw error;
//   }
// };

// /**
//  * Toggle product favorite status
//  * @param {string} token - JWT authentication token
//  * @param {number} productId - Product ID to toggle favorite
//  * @returns {Promise} - API response
//  */
// export const toggleFavorite = async (token, productId) => {
//   try {
//     const endpoint = `${BASE_URL}/franchisee/toggle-favorite`;
//     const response = await fetch(endpoint, {
//       method: 'POST',
//       headers: authHeaders(token),
//       body: JSON.stringify({ product_id: productId }),
//     });

//     return await processResponse(response, endpoint);
//   } catch (error) {
//     console.error('Toggle favorite error:', error);
//     throw error;
//   }
// };

// /**
//  * Get cart contents
//  * @param {string} token - JWT authentication token
//  * @returns {Promise} - API response
//  */
// export const getCart = async (token) => {
//   try {
//     const endpoint = `${BASE_URL}/franchisee/cart`;
//     const response = await fetch(endpoint, {
//       method: 'GET',
//       headers: authHeaders(token),
//     });

//     return await processResponse(response, endpoint);
//   } catch (error) {
//     console.error('Get cart error:', error);
//     throw error;
//   }
// };

// /**
//  * Add product to cart
//  * @param {string} token - JWT authentication token
//  * @param {number} productId - Product ID to add
//  * @param {number|null} variantId - Variant ID (optional)
//  * @param {number} quantity - Quantity to add
//  * @returns {Promise} - API response
//  */
// export const addToCart = async (token, productId, variantId, quantity) => {
//   try {
//     const payload = {
//       product_id: productId,
//       quantity: quantity,
//     };
    
//     if (variantId) {
//       payload.variant_id = variantId;
//     }
    
//     const endpoint = `${BASE_URL}/franchisee/cart/add`;
//     const response = await fetch(endpoint, {
//       method: 'POST',
//       headers: authHeaders(token),
//       body: JSON.stringify(payload),
//     });

//     return await processResponse(response, endpoint);
//   } catch (error) {
//     console.error('Add to cart error:', error);
//     throw error;
//   }
// };

// /**
//  * Get pending orders
//  * @param {string} token - JWT authentication token
//  * @param {string|null} status - Optional status filter
//  * @returns {Promise} - API response
//  */
// export const getPendingOrders = async (token, status = null) => {
//   try {
//     const queryString = status ? `?status=${status}` : '';
    
//     // Common orders endpoint patterns
//     const endpoints = [
//       `${BASE_URL}/franchisee/orders/pending${queryString}`,
//       `${BASE_URL}/orders/pending${queryString}`,
//       `${BASE_URL}/franchisee/orders${queryString}`,
//       `${BASE_URL}/orders${queryString}`
//     ];
    
//     console.log('With token:', token ? 'Token provided' : 'No token');
    
//     const headers = authHeaders(token);
//     console.log('Request headers:', headers);
    
//     let response = null;
//     let lastError = null;
    
//     // Try each endpoint until one works
//     for (const endpoint of endpoints) {
//       try {
//         console.log(`Trying pending orders endpoint: ${endpoint}`);
//         response = await fetch(endpoint, {
//           method: 'GET',
//           headers: headers,
//         });
        
//         console.log(`Endpoint ${endpoint} status:`, response.status);
        
//         if (response.ok) {
//           return await processResponse(response, endpoint);
//         }
//       } catch (e) {
//         lastError = e;
//         console.warn(`Endpoint ${endpoint} failed:`, e.message);
//       }
//     }
    
//     throw lastError || new Error('All pending orders endpoints failed');
//   } catch (error) {
//     console.error('Get pending orders error:', error);
//     return { 
//       success: false, 
//       error: `API error: ${error.message}`,
//       order_counts: { pending: 0, shipped: 0 } 
//     };
//   }
// };

// /**
//  * Get order history
//  * @param {string} token - JWT authentication token
//  * @returns {Promise} - API response
//  */
// export const getOrderHistory = async (token) => {
//   try {
//     // Common order history endpoint patterns
//     const endpoints = [
//       `${BASE_URL}/franchisee/orders/history`,
//       `${BASE_URL}/orders/history`,
//       `${BASE_URL}/franchisee/order-history`,
//       `${BASE_URL}/order-history`,
//       `${BASE_URL}/franchisee/orders?completed=1`,
//       `${BASE_URL}/orders?completed=1`,
//       `${BASE_URL}/franchisee/orders`
//     ];
    
//     console.log('With token:', token ? 'Token provided' : 'No token');
    
//     let response = null;
//     let lastError = null;
    
//     // Try each endpoint until one works
//     for (const endpoint of endpoints) {
//       try {
//         console.log(`Trying order history endpoint: ${endpoint}`);
//         response = await fetch(endpoint, {
//           method: 'GET',
//           headers: authHeaders(token),
//         });
        
//         console.log(`Endpoint ${endpoint} status:`, response.status);
        
//         if (response.ok) {
//           return await processResponse(response, endpoint);
//         }
//       } catch (e) {
//         lastError = e;
//         console.warn(`Endpoint ${endpoint} failed:`, e.message);
//       }
//     }
    
//     throw lastError || new Error('All order history endpoints failed');
//   } catch (error) {
//     console.error('Get order history error:', error);
//     return { 
//       success: false, 
//       error: `API error: ${error.message}`,
//       orders: [],
//       stats: { total_spent: 0, spending_change: 0 } 
//     };
//   }
// };

// /**
//  * Get order details
//  * @param {string} token - JWT authentication token
//  * @param {number} orderId - Order ID
//  * @returns {Promise} - API response
//  */
// export const getOrderDetails = async (token, orderId) => {
//   try {
//     const endpoint = `${BASE_URL}/franchisee/orders/${orderId}/details`;
//     const response = await fetch(endpoint, {
//       method: 'GET',
//       headers: authHeaders(token),
//     });

//     return await processResponse(response, endpoint);
//   } catch (error) {
//     console.error('Get order details error:', error);
//     throw error;
//   }
// };

// // Export all API functions
// export default {
//   login,
//   getUserProfile,
//   getCatalog,
//   toggleFavorite,
//   getCart,
//   addToCart,
//   getPendingOrders,
//   getOrderHistory,
//   getOrderDetails,
// };


import AsyncStorage from '@react-native-async-storage/async-storage';



// Determine correct base URL based on platform
import { Platform } from 'react-native';

export const BASE_URL = Platform.OS === 'ios' 
  ? 'http://localhost:8000/api'   // For iOS simulator
  : 'http://10.0.2.2:8000/api';   // For Android emulator 

// If testing on a physical device, you'll need to use your computer's actual IP address:
// export const BASE_URL = 'http://192.168.1.XXX:8000/api';

export const login = async (email, password) => {
  try {
    console.log('🔐 Attempting login with:', { email, password: '****' });
    console.log('🌐 Login API URL:', `${BASE_URL}/auth/login`);
    
    const response = await fetch(`${BASE_URL}/auth/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ email, password }),
    });

    console.log('🔐 Login response status:', response.status);
    
    const data = await response.json();
    console.log('🔐 Login response data:', JSON.stringify(data, null, 2));

    // Check for token in various possible locations in response
    const token = data.token || data.access_token || 
                 (data.data && (data.data.token || data.data.access_token));
                 
    if (response.ok && token) {
      console.log('✅ Login successful, token found');
      
      // Extract user data which might be at different paths
      const user = data.user || 
                  (data.data && data.data.user) || 
                  { id: 1, name: 'Franchisee User' };
                  
      return {
        success: true,
        token: token,
        user: user,
      };
    } else {
      console.error('❌ Login failed:', data.message || data.error || 'Unknown error');
      return {
        success: false,
        error: data.message || data.error || 'Login failed - invalid credentials',
      };
    }
  } catch (error) {
    console.error('❌ Login error:', error);
    return {
      success: false,
      error: error.message || 'Network error',
    };
  }
};

export const logout = async () => {
  try {
    const token = await AsyncStorage.getItem('userToken');
    
    // Optional: Call logout endpoint if it exists
    try {
      await fetch(`${BASE_URL}/auth/logout`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });
    } catch (e) {
      console.log('Server logout failed, continuing with local logout');
    }
    
    // Clear stored tokens regardless of server response
    await AsyncStorage.removeItem('userToken');
    await AsyncStorage.removeItem('userData');
    
    return { success: true };
  } catch (error) {
    console.error('Logout error:', error);
    return { success: false, error: error.message };
  }
};

export const getDashboardData = async () => {
  try {
    console.log('🚀 getDashboardData - Function called');
    console.log('🌐 API BASE_URL:', BASE_URL);
    
    const token = await AsyncStorage.getItem('userToken');
    if (!token) {
      console.error('⛔ No auth token found in AsyncStorage!');
      return { 
        success: false, 
        error: 'Authentication token missing',
        // Return mock data since we can't make the API call
        data: getMockDashboardData()
      };
    }
    
    console.log('🔑 Auth token found:', token.substring(0, 15) + '...');
    console.log('🌐 Making request to:', `${BASE_URL}/franchisee/dashboard`);
    
    // Set headers with authorization - note some Laravel/Sanctum APIs expect different formats
    // Try with plain Bearer format first
    const headers = {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    };
    
    console.log('📨 Request headers:', JSON.stringify(headers));

    // Try multiple auth formats
    console.log('🔄 Attempting dashboard request with standard Bearer token...');
    let response = await fetch(`${BASE_URL}/franchisee/dashboard`, {
      method: 'GET',
      headers: headers,
    });
    
    console.log('📊 Dashboard API Status:', response.status);
    
    // If we get an authentication error, try multiple alternate formats
    if (response.status === 401) {
      // First alternative: Just the token without 'Bearer'
      console.log('🔄 First attempt failed with 401, trying alt format #1...');
      const altHeaders1 = {
        'Authorization': token,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      };
      
      const altResponse1 = await fetch(`${BASE_URL}/franchisee/dashboard`, {
        method: 'GET',
        headers: altHeaders1,
      });
      
      console.log('📊 Alt format #1 Status:', altResponse1.status);
      
      if (altResponse1.status !== 401) {
        console.log('✅ Alt format #1 worked!');
        response = altResponse1;
      } else {
        // Second alternative: Using X-Authorization header (used by some Laravel setups)
        console.log('🔄 Trying alt format #2...');
        const altHeaders2 = {
          'X-Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        };
        
        const altResponse2 = await fetch(`${BASE_URL}/franchisee/dashboard`, {
          method: 'GET',
          headers: altHeaders2,
        });
        
        console.log('📊 Alt format #2 Status:', altResponse2.status);
        
        if (altResponse2.status !== 401) {
          console.log('✅ Alt format #2 worked!');
          response = altResponse2;
        } else {
          // Third alternative: Using custom query parameter for token (some APIs support this)
          console.log('🔄 Trying alt format #3 (URL param)...');
          const urlWithToken = `${BASE_URL}/franchisee/dashboard?token=${encodeURIComponent(token)}`;
          console.log('📌 URL with token:', urlWithToken);
          
          const altResponse3 = await fetch(urlWithToken, {
            method: 'GET',
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            },
          });
          
          console.log('📊 Alt format #3 Status:', altResponse3.status);
          
          if (altResponse3.status !== 401) {
            console.log('✅ Alt format #3 worked!');
            response = altResponse3;
          }
        }
      }
    }
    
    // Standard response handling continues here
    console.log('📊 Final Dashboard API Status:', response.status);
    console.log('📊 Dashboard API Status Text:', response.statusText || 'No status text');
    
    let data;
    try {
      // Some 401 errors might not return valid JSON
      data = await response.json();
      console.log('📡 Dashboard API Response:', JSON.stringify(data, null, 2));
    } catch (err) {
      console.error('❌ Error parsing response JSON:', err);
      // Return mock data if we can't parse the response
      return { 
        success: false, 
        error: 'Error parsing API response',
        data: getMockDashboardData() 
      };
    }

    if (response.ok) {
      // Transform the API response to match what the dashboard screen expects
      const transformedData = {
        stats: data.stats || {
          pending_orders: 0,
          monthly_spending: 0,
          spending_change: 0,
          low_stock_items: 0,
          incoming_deliveries: 0
        },
        charts: data.charts || {
          weekly_orders: [0, 0, 0, 0, 0, 0, 0],
          weekly_spending: [0, 0, 0, 0, 0, 0, 0],
          monthly_orders: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
          monthly_spending: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
        },
        recent_orders: (data.recent_orders || []).map(order => ({
          id: order.id,
          order_number: order.order_number || `ORDER-${order.id}`,
          total: order.total_amount || order.total || 0,
          created_at: order.created_at || new Date().toISOString(),
          status: order.status || 'pending',
          items_count: order.items_count || 0
        })),
        popular_products: (data.popular_products || []).map(product => ({
          id: product.id,
          name: product.name || 'Product',
          price: product.price || 0,
          unit_size: product.unit_size || '',
          unit_type: product.unit_type || '',
          inventory_count: product.inventory_count || 0,
          has_in_stock_variants: product.in_stock || false
        })),
        user: {
          id: data.user?.id || 1,
          name: data.user?.name || 'Franchisee User'
        }
      };

      return { success: true, data: transformedData };
    } else {
      return { success: false, error: data.message || data.error || 'Unknown error' };
    }
  } catch (err) {
    console.error('🔥 Exception in getDashboardData:', err);
    return { 
      success: false, 
      error: err.message,
      // Return mock data for testing or when API is unavailable
      data: getMockDashboardData()
    };
  }
};

// Function to provide consistent mock data when API is unavailable
const getMockDashboardData = () => {
  console.log('📊 Using mock dashboard data');
  return {
    stats: {
      pending_orders: 3,
      monthly_spending: 4250.75,
      spending_change: 12,
      low_stock_items: 5,
      incoming_deliveries: 2
    },
    charts: {
      weekly_spending: [230, 450, 280, 390, 520, 450, 300],
      monthly_spending: [2800, 3200, 3500, 2900, 3100, 3600, 3300, 2700, 3200, 3800, 4100, 4250]
    },
    recent_orders: [
      {id: 1, order_number: 'ORD-001', total: 450.75, created_at: '2023-05-10', status: 'delivered', items_count: 5},
      {id: 2, order_number: 'ORD-002', total: 325.50, created_at: '2023-05-08', status: 'shipped', items_count: 3},
      {id: 3, order_number: 'ORD-003', total: 180.25, created_at: '2023-05-05', status: 'processing', items_count: 2}
    ],
    popular_products: [
      {id: 1, name: 'Premium Coffee Beans', price: 24.99, unit_size: '1', unit_type: 'kg', inventory_count: 45, has_in_stock_variants: true},
      {id: 2, name: 'Organic Sugar', price: 8.99, unit_size: '2', unit_type: 'lb', inventory_count: 32, has_in_stock_variants: true},
      {id: 3, name: 'Vanilla Syrup', price: 12.50, unit_size: '750', unit_type: 'ml', inventory_count: 18, has_in_stock_variants: true}
    ],
    user: {
      id: 1,
      name: 'Demo Franchisee'
    }
  };
};

