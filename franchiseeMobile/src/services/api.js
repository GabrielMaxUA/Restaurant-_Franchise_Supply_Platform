
import AsyncStorage from '@react-native-async-storage/async-storage';



// Determine correct base URL based on platform
import { Platform } from 'react-native';

export const BASE_URL = Platform.OS === 'ios' 
  ? 'http://localhost:8000/api'   // For iOS simulator
  : 'http://10.0.2.2:8000/api';   // For Android emulator 

// For physical device testing, use your computer's actual IP address
// export const BASE_URL = 'http://172.20.10.2:8000/api';

// export const login = async (email, password) => {
//   try {
//     console.log('🔐 Attempting login with:', { email, password: '****' });
//     console.log('🌐 Login API URL:', `${BASE_URL}/auth/login`);
    
//     const response = await fetch(`${BASE_URL}/auth/login`, {
//       method: 'POST',
//       headers: {
//         'Content-Type': 'application/json',
//         'Accept': 'application/json',
//       },
//       body: JSON.stringify({ email, password }),
//     });

//     console.log('🔐 Login response status:', response.status);
    
//     const data = await response.json();
//     console.log('🔐 Login response data:', JSON.stringify(data, null, 2));

//     // Check for token in various possible locations in response
//     const token = data.token || data.access_token || 
//                  (data.data && (data.data.token || data.data.access_token));
                 
//     if (response.ok && token) {
//       console.log('✅ Login successful, token found');
      
//       // Extract user data which might be at different paths
//       const user = data.user || 
//                   (data.data && data.data.user) || 
//                   { id: 1, name: 'Franchisee User' };
                  
//       return {
//         success: true,
//         token: token,
//         user: user,
//       };
//     } else {
//       console.error('❌ Login failed:', data.message || data.error || 'Unknown error');
//       return {
//         success: false,
//         error: data.message || data.error || 'Login failed - invalid credentials',
//       };
//     }
//   } catch (error) {
//     console.error('❌ Login error:', error);
//     return {
//       success: false,
//       error: error.message || 'Network error',
//     };
//   }
// };

export const login = async (email, password) => {
  try {
    console.log('🔐 Attempting login with:', { email, password: '****' });
    console.log('🌐 Login API URL:', `${BASE_URL}/auth/login`);
    
    // Add detailed error handling
    const response = await fetch(`${BASE_URL}/auth/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ email, password }),
    });

    console.log('🔐 Login response status:', response.status);
    
    let rawResponse = '';
    try {
      rawResponse = await response.text();
      console.log('🔄 Raw response:', rawResponse.substring(0, 150) + '...');
      
      // Parse the response text into JSON
      const data = JSON.parse(rawResponse);
      console.log('📊 Parsed response structure:', Object.keys(data));
      
      if (response.ok && data.success) {
        console.log('✅ Login successful, token found');
        
        // Store the JWT token
        await AsyncStorage.setItem('userToken', data.token);
        
        // Store user data
        await AsyncStorage.setItem('userData', JSON.stringify(data.user));
        
        return {
          success: true,
          token: data.token,
          user: data.user,
        };
      } else {
        const errorMsg = data.error || 'Login failed - invalid credentials';
        console.error('❌ Login failed:', errorMsg);
        return {
          success: false,
          error: errorMsg,
        };
      }
    } catch (jsonError) {
      console.error('❌ JSON parse error. Raw response:', rawResponse);
      return {
        success: false,
        error: 'Server returned invalid format. Please try again.',
      };
    }
  } catch (error) {
    console.error('❌ Login network error:', error);
    return {
      success: false,
      error: 'Network error: ' + error.message,
    };
  }
};

// Add this to your API service
export const testCorsConnection = async () => {
  try {
    console.log(`🧪 Testing CORS connection to: ${BASE_URL}/cors-test`);
    
    const response = await fetch(`${BASE_URL}/cors-test`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
      },
    });
    
    console.log(`📊 CORS test status: ${response.status}`);
    
    // Log all response headers
    console.log('📋 Response headers:');
    response.headers.forEach((value, key) => {
      console.log(`  ${key}: ${value}`);
    });
    
    const text = await response.text();
    console.log(`📝 Response body: ${text}`);
    
    if (response.ok) {
      return {
        success: true,
        status: response.status,
        data: JSON.parse(text)
      };
    } else {
      return {
        success: false,
        status: response.status,
        error: text
      };
    }
  } catch (error) {
    console.error('❌ CORS test error:', error);
    return {
      success: false,
      error: error.message
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
    
    const token = await AsyncStorage.getItem('userToken');
    if (!token) {
      console.error('⛔ No auth token found in AsyncStorage!');
      return { 
        success: false, 
        error: 'Authentication token missing'
      };
    }
    
    console.log('🔑 Auth token found:', token.substring(0, 15) + '...');
    console.log('🌐 Making request to:', `${BASE_URL}/franchisee/dashboard`);
    
    // Set headers with authorization
    const headers = {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    };
    
    console.log('📨 Request headers:', JSON.stringify(headers));

    // Make API request
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
        // Second alternative: Using X-Authorization header
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
          // Third alternative: Using URL param
          console.log('🔄 Trying alt format #3 (URL param)...');
          const urlWithToken = `${BASE_URL}/franchisee/dashboard?token=${encodeURIComponent(token)}`;
          
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
    
    // Parse response
    let data;
    try {
      data = await response.json();
      console.log('📡 Dashboard API Response received');
      
      // Log only summary data to avoid console flood
      if (data && data.data) {
        console.log('📊 API Response Summary:');
        console.log('- Stats available:', data.data.stats ? 'Yes' : 'No');
        console.log('- Charts available:', data.data.charts ? 'Yes' : 'No');
        console.log('- Products count:', data.data.popular_products?.length || 0);
        console.log('- Orders count:', data.data.recent_orders?.length || 0);
        console.log('- Cart items count:', data.data.cart?.items_count || 0);
      }
    } catch (err) {
      console.error('❌ Error parsing response JSON:', err);
      return { 
        success: false, 
        error: 'Error parsing API response'
      };
    }

    if (response.ok && data.success) {
      // Process data types for consistency
      const apiData = data.data || {};
      
      // Process stats - ensure numbers have proper types
      const statsData = apiData.stats || {};
      const processedStats = {
        pending_orders: parseInt(statsData.pending_orders || 0, 10),
        monthly_spending: parseFloat(statsData.monthly_spending || 0),
        spending_change: parseInt(statsData.spending_change || 0, 10),
        low_stock_items: parseInt(statsData.low_stock_items || 0, 10),
        incoming_deliveries: parseInt(statsData.incoming_deliveries || 0, 10),
        pending_orders_change: parseInt(statsData.pending_orders_change || 0, 10)
      };
      
      // Process chart data - ensure arrays have proper types
      const chartsData = apiData.charts || {};
      
      // Helper function to normalize chart arrays
      const processChartArray = (array, expectedLength) => {
        if (!array || !Array.isArray(array)) {
          return Array(expectedLength).fill(0);
        }
        
        // Convert values to numbers
        const processedArray = array.map(val => 
          typeof val === 'string' ? parseFloat(val) : (typeof val === 'number' ? val : 0)
        );
        
        // Ensure correct length
        if (processedArray.length < expectedLength) {
          return [...processedArray, ...Array(expectedLength - processedArray.length).fill(0)];
        }
        
        if (processedArray.length > expectedLength) {
          return processedArray.slice(0, expectedLength);
        }
        
        return processedArray;
      };
      
      const processedCharts = {
        weekly_orders: processChartArray(chartsData.weekly_orders, 7),
        weekly_spending: processChartArray(chartsData.weekly_spending, 7),
        monthly_orders: processChartArray(chartsData.monthly_orders, 12),
        monthly_spending: processChartArray(chartsData.monthly_spending, 12),
        step_sizes: chartsData.step_sizes || {
          orders: 50,
          spending: 10000
        }
      };
      
      // Process recent orders
      const recentOrders = (apiData.recent_orders || []).map(order => ({
        id: order.id,
        order_number: order.order_number || `ORDER-${order.id}`,
        status: order.status || 'pending',
        total: parseFloat(order.total || order.total_amount || 0),
        total_amount: parseFloat(order.total_amount || order.total || 0),
        shipping_address: order.shipping_address || '',
        shipping_city: order.shipping_city || '',
        shipping_state: order.shipping_state || '',
        shipping_zip: order.shipping_zip || '',
        delivery_date: order.delivery_date || null,
        delivery_time: order.delivery_time || '',
        delivery_preference: order.delivery_preference || 'standard',
        shipping_cost: parseFloat(order.shipping_cost || 0),
        notes: order.notes || '',
        manager_name: order.manager_name || '',
        contact_phone: order.contact_phone || '',
        purchase_order: order.purchase_order || null,
        created_at: order.created_at || '',
        updated_at: order.updated_at || '',
        approved_at: order.approved_at || null,
        invoice_number: order.invoice_number || null,
        items_count: parseInt(order.items_count || 0, 10)
      }));
      
      // Process popular products
      const popularProducts = (apiData.popular_products || []).map(product => ({
        id: product.id,
        name: product.name || 'Product',
        price: parseFloat(product.price || 0),
        unit_size: product.unit_size || null,
        unit_type: product.unit_type || null,
        image_url: product.image_url || null,
        inventory_count: parseInt(product.inventory_count || 0, 10),
        in_stock: Boolean(product.in_stock),
        has_in_stock_variants: Boolean(product.in_stock || product.has_in_stock_variants || false)
      }));
      
      // Process cart data
      const cartData = apiData.cart || {};
      const processedCart = {
        items_count: parseInt(cartData.items_count || 0, 10),
        total: parseFloat(cartData.total || 0),
        items: Array.isArray(cartData.items) ? cartData.items.map(item => ({
          id: item.id,
          product_id: item.product_id,
          name: item.name || 'Product',
          quantity: parseInt(item.quantity || 0, 10),
          price: parseFloat(item.price || 0),
          total: parseFloat(item.total || 0),
          image_url: item.image_url || null
        })) : []
      };
      
      // Process user data
      const userData = apiData.user || {};
      const processedUser = {
        id: userData.id || 0,
        name: userData.name || null,
        email: userData.email || null
      };
      
      // Return processed data
      return { 
        success: true, 
        data: {
          stats: processedStats,
          charts: processedCharts,
          recent_orders: recentOrders,
          popular_products: popularProducts,
          cart: processedCart,
          user: processedUser
        }
      };
    } else {
      console.error('❌ API request failed:', data.message || data.error || 'Unknown error');
      return { 
        success: false, 
        error: data.message || data.error || 'Unknown error'
      };
    }
  } catch (err) {
    console.error('🔥 Exception in getDashboardData:', err);
    return { 
      success: false, 
      error: err.message
    };
  }
};

/**
 * Fetch the franchisee profile data
 * @returns {Promise<Object>} Response containing profile data or error
 */
export const getProfileData = async () => {
  try {
    console.log('🚀 getProfileData - Function called');
    
    const token = await AsyncStorage.getItem('userToken');
    if (!token) {
      console.error('⛔ No auth token found in AsyncStorage!');
      return { 
        success: false, 
        error: 'Authentication token missing'
      };
    }
    
    console.log('🔑 Auth token found:', token.substring(0, 15) + '...');
    console.log('🌐 Making request to:', `${BASE_URL}/franchisee/profile`);
    
    // Set headers with authorization
    const headers = {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    };
    
    // Try different auth formats if needed (based on your existing pattern)
    let response = await fetch(`${BASE_URL}/franchisee/profile`, {
      method: 'GET',
      headers: headers,
    });
    
    console.log('📊 Profile API Status:', response.status);
    
    // Try alt auth formats if needed (same pattern as dashboard)
    if (response.status === 401) {
      console.log('🔄 First attempt failed with 401, trying alt format #1...');
      const altHeaders1 = {
        'Authorization': token,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      };
      
      const altResponse1 = await fetch(`${BASE_URL}/franchisee/profile`, {
        method: 'GET',
        headers: altHeaders1,
      });
      
      if (altResponse1.status !== 401) {
        console.log('✅ Alt format #1 worked!');
        response = altResponse1;
      } else {
        const altHeaders2 = {
          'X-Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        };
        
        const altResponse2 = await fetch(`${BASE_URL}/franchisee/profile`, {
          method: 'GET',
          headers: altHeaders2,
        });
        
        if (altResponse2.status !== 401) {
          console.log('✅ Alt format #2 worked!');
          response = altResponse2;
        } else {
          const urlWithToken = `${BASE_URL}/franchisee/profile?token=${encodeURIComponent(token)}`;
          
          const altResponse3 = await fetch(urlWithToken, {
            method: 'GET',
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            },
          });
          
          if (altResponse3.status !== 401) {
            console.log('✅ Alt format #3 worked!');
            response = altResponse3;
          }
        }
      }
    }
    
    // Parse response
    const data = await response.json();
    console.log('📡 Profile API Response received');
    
    if (response.ok && data.success) {
      console.log('✅ Profile data retrieved successfully');
      
      // Process and normalize data if needed
      const user = data.user || {};
      const profile = data.profile || {};
      
      return { 
        success: true, 
        user: user,
        profile: profile
      };
    } else {
      console.error('❌ API request failed:', data.message || data.error || 'Unknown error');
      return { 
        success: false, 
        error: data.message || data.error || 'Unknown error'
      };
    }
  } catch (err) {
    console.error('🔥 Exception in getProfileData:', err);
    return { 
      success: false, 
      error: err.message
    };
  }
};

/**
 * Update the franchisee profile
 * @param {Object} formData - FormData object containing profile data and logo
 * @returns {Promise<Object>} Response containing updated profile data or error
 */
export const updateProfile = async (formData) => {
  try {
    console.log('🚀 updateProfile - Function called');
    
    // Get token from storage
    const token = await AsyncStorage.getItem('userToken');
    if (!token) {
      console.error('⛔ No auth token found in AsyncStorage!');
      return { 
        success: false, 
        error: 'Authentication token missing'
      };
    }
    
    console.log('🔑 Auth token found for profile update');
    console.log('🌐 Making request to:', `${BASE_URL}/franchisee/profile/update`);
    
    // Log the BASE_URL to verify it's correct
    console.log('🌐 Current BASE_URL is:', BASE_URL);
    
    // For debugging FormData structure
    try {
      console.log('📦 FormData contents in updateProfile:');
      if (formData._parts) {
        formData._parts.forEach((part, i) => {
          if (typeof part[1] === 'object' && part[1].uri) {
            console.log(`[${i}] ${part[0]}: {uri: ${part[1].uri}, type: ${part[1].type}, name: ${part[1].name}}`);
          } else {
            console.log(`[${i}] ${part[0]}: ${part[1]}`);
          }
        });
      } else {
        console.log('FormData structure not accessible');
      }
    } catch (e) {
      console.log('Error logging FormData:', e);
    }
    
    // Make API request with detailed error tracking
    console.log('📡 Starting fetch request...');
    let response;
    
    try {
      response = await fetch(`${BASE_URL}/franchisee/profile/update`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          // DO NOT set Content-Type for multipart/form-data - React Native handles this
        },
        body: formData
      });
      
      console.log('📊 Profile Update API Status:', response.status);
      console.log('📊 Response headers:', [...response.headers.entries()]);
    } catch (fetchError) {
      console.error('💥 Fetch operation failed with error:', fetchError);
      return {
        success: false,
        error: `Network error: ${fetchError.message}`
      };
    }
    
    // Try to get response text first before parsing JSON
    let rawResponseText = '';
    let data;
    
    try {
      rawResponseText = await response.text();
      console.log('📄 Raw API response text:', rawResponseText.substring(0, 200) + (rawResponseText.length > 200 ? '...' : ''));
      
      // Only try to parse if we have content
      if (rawResponseText.trim()) {
        try {
          data = JSON.parse(rawResponseText);
          console.log('📊 Parsed JSON response:', data);
        } catch (jsonError) {
          console.error('💥 JSON parsing error:', jsonError);
          return {
            success: false,
            error: `Failed to parse response: ${jsonError.message}`,
            rawResponse: rawResponseText
          };
        }
      } else {
        console.error('💥 Empty response from server');
        return {
          success: false,
          error: 'Empty response from server'
        };
      }
    } catch (textError) {
      console.error('💥 Error getting response text:', textError);
      return {
        success: false,
        error: `Error reading response: ${textError.message}`
      };
    }
    
    if (response.ok && data && data.success) {
      console.log('✅ Profile updated successfully on server');
      return {
        success: true,
        user: data.user || {},
        profile: data.profile || {}
      };
    } else {
      console.error('❌ Profile update failed:', data ? data.message : 'Unknown error');
      return {
        success: false,
        error: data ? data.message : `HTTP error: ${response.status}`,
        errors: data && data.errors ? data.errors : null,
        httpStatus: response.status
      };
    }
  } catch (err) {
    console.error('🔥 Exception in updateProfile:', err);
    return { 
      success: false, 
      error: `Unhandled error in updateProfile: ${err.message}`
    };
  }
};

/**
 * Update user password
 * @param {Object} passwordData - Object containing current_password, new_password, and new_password_confirmation
 * @returns {Promise<Object>} Response indicating success or failure
 */
export const updatePassword = async (passwordData) => {
  try {
    console.log('🚀 updatePassword - Function called');
    
    const token = await AsyncStorage.getItem('userToken');
    if (!token) {
      console.error('⛔ No auth token found in AsyncStorage!');
      return { 
        success: false, 
        error: 'Authentication token missing'
      };
    }
    
    console.log('🔑 Auth token found for password update');
    console.log('🌐 Making request to:', `${BASE_URL}/franchisee/profile/password`);
    
    // Make API request
    const response = await fetch(`${BASE_URL}/franchisee/profile/password`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(passwordData)
    });
    
    console.log('📊 Password Update API Status:', response.status);
    
    // Parse response
    const data = await response.json();
    
    if (response.ok && data.success) {
      console.log('✅ Password updated successfully');
      return {
        success: true,
        message: data.message || 'Password updated successfully'
      };
    } else {
      console.error('❌ Password update failed:', data.message || 'Unknown error');
      return {
        success: false,
        error: data.message || 'Failed to update password',
        errors: data.errors || {}
      };
    }
  } catch (err) {
    console.error('🔥 Exception in updatePassword:', err);
    return { 
      success: false, 
      error: err.message
    };
  }
};

/**
 * Delete the company logo
 * @returns {Promise<Object>} Response indicating success or failure
 */
export const deleteLogo = async () => {
  try {
    console.log('🚀 deleteLogo - Function called');
    
    const token = await AsyncStorage.getItem('userToken');
    if (!token) {
      console.error('⛔ No auth token found in AsyncStorage!');
      return { 
        success: false, 
        error: 'Authentication token missing'
      };
    }
    
    console.log('🔑 Auth token found for logo deletion');
    console.log('🌐 Making request to:', `${BASE_URL}/franchisee/profile/logo`);
    
    // Make API request
    const response = await fetch(`${BASE_URL}/franchisee/profile/logo`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });
    
    console.log('📊 Logo Delete API Status:', response.status);
    
    // Parse response
    const data = await response.json();
    
    if (response.ok && data.success) {
      console.log('✅ Logo deleted successfully');
      return {
        success: true,
        message: data.message || 'Logo deleted successfully'
      };
    } else {
      console.error('❌ Logo deletion failed:', data.message || 'Unknown error');
      return {
        success: false,
        error: data.message || 'Failed to delete logo'
      };
    }
  } catch (err) {
    console.error('🔥 Exception in deleteLogo:', err);
    return { 
      success: false, 
      error: err.message
    };
  }
};

/**
 * Get common auth headers with token
 * @returns {Object} Headers object with Authorization
 */
export const getAuthHeaders = async () => {
  try {
    const token = await AsyncStorage.getItem('userToken');
    if (!token) {
      return { 
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      };
    }
    
    return {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    };
  } catch (error) {
    console.error('Error getting auth headers:', error);
    return { 
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    };
  }
};


