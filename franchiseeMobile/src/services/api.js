
import AsyncStorage from '@react-native-async-storage/async-storage';
// Import session management from FranchiseeLayout
import { sessionEventEmitter } from '../components/FranchiseeLayout';
// Import axios for API requests
import axios from 'axios';
// Import the centralized axios instance
import axiosInstance, { BASE_URL } from './axiosInstance';
// Import auth service functions
import { getAuthToken, getAuthHeaders } from './authService';

// Helper function to extract cart count from various API response formats
export const extractCartCount = (response) => {
  if (!response || typeof response !== 'object') {
    console.log('❓ extractCartCount: Invalid response format', response);
    return 0;
  }
  
  console.log('🔍 extractCartCount: Examining response structure');
  
  // Direct items_count property
  if (typeof response.items_count === 'number') {
    console.log(`✅ Found direct items_count: ${response.items_count}`);
    return response.items_count;
  }
  
  // Count in cart object
  if (response.cart) {
    // Check for cart.items_count
    if (typeof response.cart.items_count === 'number') {
      console.log(`✅ Found cart.items_count: ${response.cart.items_count}`);
      return response.cart.items_count;
    }
    
    // Check for cart.items array
    if (Array.isArray(response.cart.items)) {
      console.log(`✅ Found cart.items array length: ${response.cart.items.length}`);
      return response.cart.items.length;
    }
  }
  
  // Check for cart_items array
  if (Array.isArray(response.cart_items)) {
    console.log(`✅ Found cart_items array length: ${response.cart_items.length}`);
    return response.cart_items.length;
  }
  
  // Check for data property with nested count or arrays
  if (response.data) {
    // Direct count in data
    if (typeof response.data.items_count === 'number') {
      console.log(`✅ Found data.items_count: ${response.data.items_count}`);
      return response.data.items_count;
    }
    
    // Nested cart object in data
    if (response.data.cart) {
      if (typeof response.data.cart.items_count === 'number') {
        console.log(`✅ Found data.cart.items_count: ${response.data.cart.items_count}`);
        return response.data.cart.items_count;
      }
      
      if (Array.isArray(response.data.cart.items)) {
        console.log(`✅ Found data.cart.items array length: ${response.data.cart.items.length}`);
        return response.data.cart.items.length;
      }
    }
    
    // Check for cart_items array in data
    if (Array.isArray(response.data.cart_items)) {
      console.log(`✅ Found data.cart_items array length: ${response.data.cart_items.length}`);
      return response.data.cart_items.length;
    }
    
    // Check if data itself is an array
    if (Array.isArray(response.data)) {
      console.log(`✅ Found data array length: ${response.data.length}`);
      return response.data.length;
    }
  }
  
  console.log('⚠️ Could not find cart count in response');
  return 0;
};



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

// getAuthHeaders is now imported from authService.js

/**Catalog data fetch */
export const getCatalog = async (token = null, page = 1, filters = {}) => {
  try {
    // Build query parameters from filters
    const queryParams = new URLSearchParams();
    queryParams.append('page', page);
    
    // Add any additional filters
    Object.keys(filters).forEach(key => {
      if (filters[key]) {
        queryParams.append(key, filters[key]);
      }
    });
    
    // Construct the URL with query parameters
    const url = `/franchisee/catalog?${queryParams.toString()}`;
    
    console.log('Fetching catalog from:', url);
    
    // Use axiosInstance with centralized token management
    const response = await axiosInstance.get(url);
    
    console.log('Catalog API response status:', response.status);
    console.log('Catalog API response structure:', Object.keys(response.data));
    
    // Return the response even if it doesn't match the expected structure
    // so we can see what we're getting in the console
    return {
      success: response.data.success || false,
      products: response.data.products || { data: [] },  // Ensure products is an object with data array
      categories: response.data.categories || [],
      message: response.data.message || '',
    };
  } catch (error) {
    console.error('getCatalog error details:', error.response?.data || error.message);
    
    // Return a structured error response
    return {
      success: false,
      message: error.response?.data?.message || error.message || 'Failed to fetch catalog',
      products: { data: [] },
      categories: [],
    };
  }
};

export const toggleFavorite = async (token = null, productId) => {
  try {
    console.log(`Toggling favorite for product ID: ${productId}`);
    
    // Use axiosInstance with centralized token management
    const response = await axiosInstance.post('/franchisee/toggle-favorite', {
      product_id: productId,
    });
    
    console.log('Toggle favorite response:', response.data);
    return response.data;
  } catch (error) {
    console.error('toggleFavorite error:', error);
    if (error.response) {
      console.error('Error response data:', error.response.data);
      console.error('Error response status:', error.response.status);
    }
    return { 
      success: false, 
      message: error.response?.data?.message || 'Failed to toggle favorite.' 
    };
  }
};

/**
 * Add an item to the cart with robust inventory checking
 * @param {string} token - Authorization token
 * @param {number|string} productId - The product ID to add
 * @param {number|string|null} variantId - Optional variant ID
 * @param {number} quantity - Quantity to add (default: 1)
 * @param {object} currentCartItems - Optional array of current cart items for local inventory checking
 * @returns {Promise<object>} Response with success status, warnings, and inventory information
 */
export const addToCart = async (token = null, productId, variantId = null, quantity = 1, currentCartItems = []) => {
  try {
    console.log(`🛒 Adding to cart - Product ID: ${productId}, Variant ID: ${variantId}, Quantity: ${quantity}`);
    
    // First, perform a local inventory check if we have current cart items and inventory data
    let adjustedQuantity = quantity;
    let localInventoryAdjusted = false;
    let maxAvailable = null;
    let cartItem = null;
    
    if (currentCartItems && currentCartItems.length > 0) {
      console.log('🔍 Performing local inventory check before API call');
      
      // Find the relevant item in the cart
      cartItem = currentCartItems.find(item => 
        (variantId && item.variant_id === variantId) || 
        (!variantId && item.product_id === productId && !item.variant_id)
      );
      
      // Find product inventory data
      const productInventory = cartItem?.product?.inventory_count;
      const variantInventory = cartItem?.variant?.inventory_count;
      
      if (productInventory !== undefined || variantInventory !== undefined) {
        // Calculate how much more can be added
        const inventoryCount = variantId ? variantInventory : productInventory;
        const currentQuantity = cartItem ? cartItem.quantity : 0;
        
        if (inventoryCount !== undefined) {
          maxAvailable = inventoryCount - currentQuantity;
          
          // If requested quantity exceeds available, adjust it
          if (quantity > maxAvailable) {
            console.log(`⚠️ Local inventory check: Requested ${quantity}, but only ${maxAvailable} available`);
            adjustedQuantity = Math.max(0, maxAvailable);
            localInventoryAdjusted = true;
          }
        }
      }
    }
    
    // If local inventory check determined we can't add any items, return early
    if (localInventoryAdjusted && adjustedQuantity <= 0) {
      console.log('❌ Cannot add item - no inventory available');
      return {
        success: false,
        inventory_limited: true,
        max_available: 0,
        message: 'This item is out of stock'
      };
    }
    
    // Prepare the payload with the potentially adjusted quantity
    const payload = {
      product_id: productId,
      quantity: adjustedQuantity,
      check_inventory: true   // Add inventory checking to prevent exceeding available stock
    };
    
    // Only include variant_id if it's provided
    if (variantId) {
      payload.variant_id = variantId;
    }
    
    // Log if quantity was adjusted
    if (localInventoryAdjusted) {
      console.log(`📝 Adjusted quantity from ${quantity} to ${adjustedQuantity} due to inventory limits`);
    }
    
    // Use axiosInstance with centralized token management
    const response = await axiosInstance.post('/franchisee/cart/add', payload);
    
    console.log('✅ Add to cart response:', response.data);
    
    // Process the API response
    if (response.data && typeof response.data === 'object') {
      // Make sure we have a success flag
      if (!('success' in response.data)) {
        response.data.success = true;
      }
      
      // Extract and add cart count to response
      const cartCount = extractCartCount(response.data);
      console.log(`📊 Extracted cart count from add response: ${cartCount}`);
      response.data.items_count = cartCount;
      
      // Add information about local inventory adjustments
      if (localInventoryAdjusted) {
        response.data.inventory_limited = true;
        response.data.requested_quantity = quantity;
        response.data.adjusted_quantity = adjustedQuantity;
        response.data.max_available = maxAvailable;
      }
    }
    
    // If successful, return the enhanced response
    return response.data;
  } catch (error) {
    console.error('❌ addToCart error details:', error);
    
    // Check if it's an authentication error (401)
    if (error.response?.status === 401) {
      console.log('🔐 Authentication error (401) detected in addToCart');
      sessionEventEmitter.emit('sessionExpiring');
      throw new Error('Authentication error: Your session has expired.');
    }
    
    // Check for HTML response (redirect to login page)
    const contentType = error.response?.headers?.['content-type'];
    if (contentType && contentType.includes('text/html')) {
      console.log('🔐 HTML response detected in addToCart (likely login redirect)');
      sessionEventEmitter.emit('sessionExpiring');
      throw new Error('Authentication error: Your session has expired.');
    }
    
    // Check for inventory-related error messages
    const errorMessage = error.response?.data?.message || 'Failed to add to cart.';
    console.error('addToCart error message:', errorMessage);
    
    // Detect inventory-related errors
    const isInventoryError = 
      errorMessage.includes('inventory') || 
      errorMessage.includes('stock') || 
      errorMessage.includes('available');
    
    // Pass through backend inventory data if available
    const errorResponse = {
      success: false,
      inventory_limited: isInventoryError,
      message: errorMessage
    };
    
    // Include backend inventory fields if they exist
    if (error.response?.data?.remaining_inventory !== undefined) {
      errorResponse.remaining_inventory = error.response.data.remaining_inventory;
    }
    if (error.response?.data?.product_cart_quantity !== undefined) {
      errorResponse.product_cart_quantity = error.response.data.product_cart_quantity;
    }
    if (error.response?.data?.requested_quantity !== undefined) {
      errorResponse.requested_quantity = error.response.data.requested_quantity;
    }
      
    return errorResponse;
  }
};

export const getCart = async (token = null) => {
  console.log('🛒 getCart: Starting fetching cart data');
  try {
    // If no token was provided, get one from auth service
    if (!token) {
      token = await getAuthToken();
      if (!token) {
        console.log('⛔ getCart: No token available');
        sessionEventEmitter.emit('sessionExpiring');
        throw new Error('Authentication error: No authentication token found');
      }
    }
    
    try {
      console.log('🚀 getCart: Making API request');
      // Use the axios instance with automatic token handling
      const response = await axiosInstance.get('/franchisee/cart');
      
      // Successfully got cart data
      console.log('✅ getCart: Successfully received data');
      
      // Ensure response has success flag
      if (response.data && typeof response.data === 'object') {
        if (!('success' in response.data)) {
          response.data.success = true;
        }
        
        // Use our helper to extract cart count consistently
        const cartCount = extractCartCount(response.data);
        console.log(`📊 getCart: Extracted cart count: ${cartCount}`);
        
        // Add consistent items_count property
        response.data.items_count = cartCount;
        
        return response.data;
      }
      
      return { 
        success: true,
        cart_items: [],
        items_count: 0,
        message: 'Empty cart'
      };
    } catch (axiosError) {
      // Handle axios errors - now handled by axios interceptors
      console.log('🛑 Axios error on getCart:', axiosError);
      
      // Return error data from API if available
      if (axiosError.response?.data) {
        return { 
          success: false,
          error: axiosError.response.data.message || 'Failed to get cart data'
        };
      }
      
      // Default error
      throw new Error('Failed to get cart data');
    }
  } catch (error) {
    console.error('getCart error:', error.message);
    
    // Propagate authentication errors
    if (error.message && error.message.includes('Authentication error')) {
      throw error;
    }
    
    // For other errors, return a structured error
    return { 
      success: false,
      error: error.message
    };
  }
};

export const updateCartItem = async (itemId, quantity) => {
  // Add a special debug flag to see what's happening
  console.log(`🧪 updateCartItem DEBUG START for item ID: ${itemId}, quantity: ${quantity}`);
  
  try {
    console.log(`🔄 Updating cart item ${itemId} to quantity ${quantity}`);
    
    // Get a valid token with automatic refresh if needed
    const token = await getAuthToken();
    if (!token) {
      console.log(`⛔ No token available for updating item ${itemId}`);
      sessionEventEmitter.emit('sessionExpiring');
      throw new Error('Authentication error: No authentication token found');
    }
    
    console.log(`🔑 Using token for updating item ${itemId}`);
    
    // Use axios instance with interceptors for authentication handling
    try {
      // Prepare the request payload with inventory checking
      const payload = { 
        items: [
          {
            id: itemId,           // Using 'id' instead of 'item_id' based on API error
            quantity: quantity
          }
        ],
        check_inventory: true     // Add inventory checking to prevent exceeding available stock
      };
      
      console.log(`🚀 Making API call to update item ${itemId}`);
      console.log('📦 Request payload:', JSON.stringify(payload, null, 2));
      
      // The API expects an "items" array with objects containing 'id' (not 'item_id') and 'quantity'
      // Based on error messages: "The items field is required" and "The items.0.id field is required"
      console.log(`📌 Using cart update endpoint: /franchisee/cart/update`);
      
      let response;
      try {
        // Use axiosInstance with centralized token management
        response = await axiosInstance.post(
          '/franchisee/cart/update', 
          payload,
          {
            timeout: 10000 // 10 second timeout
          }
        );
      } catch (requestError) {
        console.error(`❌ Request error in updateCartItem for ${itemId}:`, requestError);
        
        // Log the specific error response data if available
        if (requestError.response && requestError.response.data) {
          console.error('Error response data:', requestError.response.data);
        }
        
        throw requestError;
      }
      
      console.log(`✅ Update cart success for item ${itemId}:`, response.data);
      
      // Ensure we're returning a consistent structure
      if (response.data && typeof response.data === 'object') {
        if (!('success' in response.data)) {
          // Add success flag if not present in the response
          response.data.success = true;
        }
        
        // Use extractCartCount to get consistent cart count
        const cartCount = extractCartCount(response.data);
        if (cartCount > 0) {
          response.data.items_count = cartCount;
        }
        
        return response.data;
      } else {
        // If the response is not an object or is empty, create a proper response object
        return {
          success: true,
          message: 'Item updated successfully'
        };
      }
    } catch (axiosError) {
      // Handle axios errors
      console.log(`🛑 Axios error on updateCartItem for item ${itemId}:`, axiosError.message);
      
      // Log the full error response
      if (axiosError.response) {
        console.log(`🔍 Error response status: ${axiosError.response.status}`);
        console.log(`🔍 Error response headers:`, axiosError.response.headers);
        console.log(`🔍 Error response data:`, axiosError.response.data);
      }
      
      // Check if it's an authentication error (401)
      if (axiosError.response && axiosError.response.status === 401) {
        console.log(`🔐 Authentication error (401) detected for updating item ${itemId}`);
        sessionEventEmitter.emit('sessionExpiring');
        throw new Error('Authentication error: Your session has expired.');
      }
      
      // Check for HTML response (redirect to login page)
      const contentType = axiosError.response?.headers?.['content-type'];
      if (contentType && contentType.includes('text/html')) {
        console.log(`🔐 HTML response detected for updating item ${itemId} (likely login redirect)`);
        sessionEventEmitter.emit('sessionExpiring');
        throw new Error('Authentication error: Your session has expired.');
      }
      
      // Return the error message from the API if available
      if (axiosError.response?.data?.message) {
        console.log(`📝 API error message for updating item ${itemId}: ${axiosError.response.data.message}`);
        return {
          success: false,
          message: axiosError.response.data.message
        };
      }
      
      // Default error message
      console.log(`⚠️ Default error for updating item ${itemId}`);
    }
  } catch (error) {
    console.error(`❌ Error updating item ${itemId} in cart:`, error.message);
    
    // Special handling for authentication errors
    if (error.message && error.message.includes('Authentication error')) {
      // If it's an authentication error, let it bubble up so FranchiseeLayout can handle it
      console.log(`🔒 Authentication error will bubble up for updating item ${itemId}`);
      throw error;
    }
    
    // For other errors, return a structured error
    console.log(`⚠️ Returning structured error for updating item ${itemId}: ${error.message}`);
    return {
      success: false,
      message: error.message || `Failed to update item ${itemId}`
    };
  } finally {
    console.log(`🧪 updateCartItem DEBUG END for item ID: ${itemId}`);
  }
};

export const removeCartItem = async (itemId) => {
  // Add a special debug flag to see what's happening
  console.log(`🧪 removeCartItem DEBUG START for item ID: ${itemId}`);
  
  try {
    console.log(`🗑️ Removing cart item ${itemId}`);
    const token = await AsyncStorage.getItem('userToken');
    
    if (!token) {
      console.log(`⛔ No token available for item ${itemId}`);
      // Notify about token issue using the event system
      sessionEventEmitter.emit('sessionExpiring');
      throw new Error('Authentication error: No authentication token found');
    }
    
    console.log(`🔑 Using token for item ${itemId}: ${token.substring(0, 15)}...`);
    
    // Use axios instead of fetch for this specific call
    // Axios handles JSON content-type better, especially with Laravel responses
    try {
      console.log(`🚀 Making API call to remove item ${itemId}`);
      // Use axiosInstance with centralized token management
      const response = await axiosInstance.post('/franchisee/cart/remove', { item_id: itemId }
      );
      
      console.log(`✅ Remove from cart success for item ${itemId}:`, response.data);
      
      // Ensure we're returning a consistent structure
      if (response.data && typeof response.data === 'object') {
        if (!('success' in response.data)) {
          // Add success flag if not present in the response
          response.data.success = true;
        }
        
        // Use extractCartCount to get consistent cart count and add it to the response
        const cartCount = extractCartCount(response.data);
        console.log(`📊 Extracted cart count from remove response: ${cartCount}`);
        response.data.items_count = cartCount;
        
        return response.data;
      } else {
        // If the response is not an object or is empty, create a proper response object
        return {
          success: true,
          message: 'Item removed successfully'
        };
      }
    } catch (axiosError) {
      // Handle axios errors
      console.log(`🛑 Axios error on removeCartItem for item ${itemId}:`, axiosError.message);
      
      // Log the full error response
      if (axiosError.response) {
        console.log(`🔍 Error response status: ${axiosError.response.status}`);
        console.log(`🔍 Error response headers:`, axiosError.response.headers);
        console.log(`🔍 Error response data:`, axiosError.response.data);
      }
      
      // Check if it's an authentication error (401)
      if (axiosError.response && axiosError.response.status === 401) {
        console.log(`🔐 Authentication error (401) detected for item ${itemId}`);
        sessionEventEmitter.emit('sessionExpiring');
        throw new Error('Authentication error: Your session has expired.');
      }
      
      // Check for HTML response (redirect to login page)
      const contentType = axiosError.response?.headers?.['content-type'];
      if (contentType && contentType.includes('text/html')) {
        console.log(`🔐 HTML response detected for item ${itemId} (likely login redirect)`);
        sessionEventEmitter.emit('sessionExpiring');
        throw new Error('Authentication error: Your session has expired.');
      }
      
      // Return the error message from the API if available
      if (axiosError.response?.data?.message) {
        console.log(`📝 API error message for item ${itemId}: ${axiosError.response.data.message}`);
        return {
          success: false,
          message: axiosError.response.data.message
        };
      }
      
      // Default error message
      console.log(`⚠️ Default error for item ${itemId}`);
      throw new Error(`Failed to remove item ${itemId} from cart`);
    }
  } catch (error) {
    console.error(`❌ Error removing item ${itemId} from cart:`, error.message);
    
    // Special handling for authentication errors
    if (error.message && error.message.includes('Authentication error')) {
      // If it's an authentication error, let it bubble up so FranchiseeLayout can handle it
      console.log(`🔒 Authentication error will bubble up for item ${itemId}`);
      throw error;
    }
    
    // For other errors, return a structured error
    console.log(`⚠️ Returning structured error for item ${itemId}: ${error.message}`);
    return {
      success: false,
      message: error.message || `Failed to remove item ${itemId}`
    };
  } finally {
    console.log(`🧪 removeCartItem DEBUG END for item ID: ${itemId}`);
  }
};

export const getProductDetails = async (token = null, productId) => {
  try {
    console.log(`Fetching details for product ID: ${productId}`);
    
    // Use axiosInstance with centralized token management
    const response = await axiosInstance.get(`/franchisee/products/${productId}/details`);
    
    console.log('Product details response status:', response.status);
    
    // Check if we got a valid response
    if (response.data && response.data.success && response.data.product) {
      console.log('Successfully received product details data');
      
      // Return the product data directly as provided by the API
      return response.data;
    }
    
    return {
      success: false,
      message: response.data?.message || 'Invalid response format'
    };
  } catch (error) {
    console.error('getProductDetails error:', error);
    if (error.response) {
      console.error('Response data:', error.response.data);
      console.error('Response status:', error.response.status);
    }
    
    return {
      success: false,
      message: error.response?.data?.message || 'Failed to fetch product details'
    };
  }
};

/**
 * Get pending orders with optional status filtering
 * @param {string} status - Optional status filter (pending, processing, packed, shipped)
 * @param {number} page - Page number for pagination
 * @param {number} perPage - Items per page
 * @returns {Promise<Object>} API response
 */
export const getPendingOrders = async (status = null, page = 1, perPage = 10) => {
  try {
    console.log(`🚀 getPendingOrders - Function called with status: ${status}`);
    
    // Build params
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (page) params.append('page', page);
    if (perPage) params.append('per_page', perPage);
    
    const url = `/franchisee/orders/pending${params.toString() ? '?' + params.toString() : ''}`;
    console.log('🌐 Making request to:', url);
    
    // Use axiosInstance with centralized token management
    const response = await axiosInstance.get(url);
    
    console.log('📊 Pending Orders Status:', response.status);
    
    // Process the response
    if (response.data.success) {
      console.log('✅ Orders retrieved successfully');
      return response.data;
    } else {
      console.error('❌ API request failed:', response.data.message || 'Unknown error');
      return { 
        success: false, 
        error: response.data.message || 'Failed to retrieve orders'
      };
    }
  } catch (err) {
    console.error('🔥 Exception in getPendingOrders:', err);
    
    // Check if it's an authentication error (401)
    if (err.response?.status === 401) {
      console.log('🔐 Authentication error (401) detected');
      sessionEventEmitter.emit('sessionExpiring');
    }
    
    return { 
      success: false, 
      error: err.message || 'An error occurred while fetching orders'
    };
  }
};

/**
 * Get order history with optional filters
 * @param {Object} filters - Filter parameters
 * @param {number} page - Page number for pagination
 * @param {number} perPage - Items per page
 * @returns {Promise<Object>} API response
 */
export const getOrderHistory = async (filters = {}, page = 1, perPage = 15) => {
  try {
    console.log('🚀 getOrderHistory - Function called');
    
    // Build params
    const params = new URLSearchParams();
    if (page) params.append('page', page);
    if (perPage) params.append('per_page', perPage);
    
    // Add filters
    Object.keys(filters).forEach(key => {
      if (filters[key]) {
        params.append(key, filters[key]);
      }
    });
    
    const url = `/franchisee/orders/history${params.toString() ? '?' + params.toString() : ''}`;
    console.log('🌐 Making request to:', url);
    
    // Use axiosInstance with centralized token management
    const response = await axiosInstance.get(url);
    
    console.log('📊 Order History Status:', response.status);
    
    // Process the response
    if (response.data.success) {
      console.log('✅ Order history retrieved successfully');
      
      // Process date fields for each order if we have orders array
      if (response.data.orders && Array.isArray(response.data.orders)) {
        response.data.orders = response.data.orders.map(order => {
          // Process each order to ensure we have the right date fields
          let processedOrder = { ...order };
          
          // For delivered orders, check for delivered_at field or use updated_at as fallback
          if (processedOrder.status === 'delivered') {
            if (!processedOrder.delivered_at && processedOrder.updated_at) {
              processedOrder.delivered_at = processedOrder.updated_at;
              console.log(`Set delivered_at to updated_at for order ${processedOrder.id}`);
            }
          }
          
          // For rejected orders, check for rejected_at field or use updated_at as fallback
          if (processedOrder.status === 'rejected') {
            if (!processedOrder.rejected_at && processedOrder.updated_at) {
              processedOrder.rejected_at = processedOrder.updated_at;
              console.log(`Set rejected_at to updated_at for order ${processedOrder.id}`);
            }
          }
          
          return processedOrder;
        });
      }
      
      return response.data;
    } else {
      console.error('❌ API request failed:', response.data.message || 'Unknown error');
      return { 
        success: false, 
        error: response.data.message || 'Failed to retrieve order history'
      };
    }
  } catch (err) {
    console.error('🔥 Exception in getOrderHistory:', err);
    
    // Check if it's an authentication error (401)
    if (err.response?.status === 401) {
      console.log('🔐 Authentication error (401) detected');
      sessionEventEmitter.emit('sessionExpiring');
    }
    
    return { 
      success: false, 
      error: err.message || 'An error occurred while fetching order history'
    };
  }
};

/**
 * Get order details
 * @param {number} orderId - ID of the order to retrieve
 * @returns {Promise<Object>} API response
 */
export const getOrderDetails = async (orderId) => {
  try {
    console.log(`🚀 getOrderDetails - Function called for order ID: ${orderId}`);
    
    const url = `/franchisee/orders/${orderId}`;
    console.log('🌐 Making request to:', url);
    
    // Use axiosInstance with centralized token management
    const response = await axiosInstance.get(url);
    
    console.log('📊 Order Details Status:', response.status);
    
    // Process the response
    if (response.data.success) {
      console.log('✅ Order details retrieved successfully');
      return response.data;
    } else {
      console.error('❌ API request failed:', response.data.message || 'Unknown error');
      return { 
        success: false, 
        error: response.data.message || 'Failed to retrieve order details'
      };
    }
  } catch (err) {
    console.error('🔥 Exception in getOrderDetails:', err);
    
    // Check if it's an authentication error (401)
    if (err.response?.status === 401) {
      console.log('🔐 Authentication error (401) detected');
      sessionEventEmitter.emit('sessionExpiring');
    }
    
    return { 
      success: false, 
      error: err.message || 'An error occurred while fetching order details'
    };
  }
};

/**
 * Update order status
 * @param {number} orderId - ID of the order to update
 * @param {string} status - New status to set (pending, processing, packed, shipped, delivered, cancelled, rejected)
 * @returns {Promise<Object>} API response
 */
export const updateOrderStatus = async (orderId, status) => {
  try {
    console.log(`🚀 updateOrderStatus - Function called for order ID: ${orderId}, new status: ${status}`);
    
    const token = await AsyncStorage.getItem('userToken');
    if (!token) {
      console.error('⛔ No auth token found in AsyncStorage!');
      return { 
        success: false, 
        error: 'Authentication token missing'
      };
    }
    
    const url = `${BASE_URL}/franchisee/orders/${orderId}/status/${status}`;
    console.log('🌐 Making request to:', url);
    
    const response = await axios.patch(url, {}, {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    });
    
    console.log('📊 Update Order Status:', response.status);
    
    // Process the response
    if (response.data.success) {
      console.log('✅ Order status updated successfully');
      return response.data;
    } else {
      console.error('❌ API request failed:', response.data.message || 'Unknown error');
      return { 
        success: false, 
        error: response.data.message || 'Failed to update order status'
      };
    }
  } catch (err) {
    console.error('🔥 Exception in updateOrderStatus:', err);
    
    // Check if it's an authentication error (401)
    if (err.response?.status === 401) {
      console.log('🔐 Authentication error (401) detected');
      sessionEventEmitter.emit('sessionExpiring');
    }
    
    return { 
      success: false, 
      error: err.message || 'An error occurred while updating order status'
    };
  }
};

/**
 * Repeat a previous order
 * @param {number} orderId - ID of the order to repeat
 * @returns {Promise<Object>} API response
 */
export const repeatOrder = async (orderId) => {
  try {
    console.log(`🚀 repeatOrder - Function called for order ID: ${orderId}`);
    
    const token = await AsyncStorage.getItem('userToken');
    if (!token) {
      console.error('⛔ No auth token found in AsyncStorage!');
      return { 
        success: false, 
        error: 'Authentication token missing'
      };
    }
    
    // Use the dedicated API endpoint for mobile
    const url = `${BASE_URL}/franchisee/orders/${orderId}/repeat-api`;
    console.log('🌐 Making repeat order request to:', url);
    
    const response = await axios.post(url, 
      { 
        check_inventory: true,  // Add this parameter to request stock information
        return_cart_items: true // Request server to return the cart items directly
      },
      {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json'
        }
      }
    );
    
    console.log('📊 Repeat Order Status:', response.status);
    console.log('📊 Repeat Order Response:', response.data);
    
    // Process the response
    if (response.data.success) {
      console.log('✅ Order repeat API call successful');
      
      // Check if we have cart items in the response
      const itemsAdded = response.data.cart_items || [];
      console.log(`✅ ${itemsAdded.length} items were added to cart`);
      
      // Check if there are any inventory warnings
      if (response.data.warnings && response.data.warnings.length > 0) {
        // Log warnings for debugging
        console.log('⚠️ Inventory warnings:', response.data.warnings);
      }
      
      return {
        success: true,
        cart_items: itemsAdded,
        warnings: response.data.warnings || [],
        items_count: response.data.items_count || itemsAdded.length
      };
    } else {
      console.error('❌ API request failed:', response.data.error || 'Unknown error');
      return { 
        success: false, 
        error: response.data.error || 'Failed to repeat order',
        warnings: response.data.warnings || [],
        cart_items: []
      };
    }
  } catch (err) {
    console.error('🔥 Exception in repeatOrder:', err);
    
    // Check if it's an authentication error (401)
    if (err.response?.status === 401) {
      console.log('🔐 Authentication error (401) detected');
      sessionEventEmitter.emit('sessionExpiring');
    }
    
    // Check if we have additional error details in the response
    if (err.response && err.response.data) {
      console.log('❌ Server error details:', err.response.data);
      
      // Return with warnings if available
      return { 
        success: false, 
        error: err.response.data.message || err.message || 'An error occurred while repeating the order',
        warnings: err.response.data.warnings || []
      };
    }
    
    return { 
      success: false, 
      error: err.message || 'An error occurred while repeating the order'
    };
  }
};

/**
 * Get order invoice
 * @param {number} orderId - ID of the order to get invoice for
 * @returns {Promise<Object>} API response
 */
export const getOrderInvoice = async (orderId) => {
  try {
    console.log(`🚀 getOrderInvoice - Function called for order ID: ${orderId}`);
    
    const token = await AsyncStorage.getItem('userToken');
    if (!token) {
      console.error('⛔ No auth token found in AsyncStorage!');
      return { 
        success: false, 
        error: 'Authentication token missing'
      };
    }
    
    const url = `${BASE_URL}/franchisee/orders/${orderId}/invoice`;
    console.log('🌐 Making request to:', url);
    
    const response = await axios.get(url, {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    });
    
    console.log('📊 Order Invoice Status:', response.status);
    
    // Process the response
    if (response.data.success) {
      console.log('✅ Invoice retrieved successfully');
      return response.data;
    } else {
      console.error('❌ API request failed:', response.data.message || 'Unknown error');
      return { 
        success: false, 
        error: response.data.message || 'Failed to retrieve invoice'
      };
    }
  } catch (err) {
    console.error('🔥 Exception in getOrderInvoice:', err);
    
    // Check if it's an authentication error (401)
    if (err.response?.status === 401) {
      console.log('🔐 Authentication error (401) detected');
      sessionEventEmitter.emit('sessionExpiring');
    }
    
    return { 
      success: false, 
      error: err.message || 'An error occurred while retrieving the invoice'
    };
  }
};

/**
 * Dismiss welcome banner
 * @returns {Promise<Object>} API response
 */
export const dismissWelcomeBanner = async () => {
  try {
    console.log('🚀 dismissWelcomeBanner - Function called');
    
    const token = await AsyncStorage.getItem('userToken');
    if (!token) {
      console.error('⛔ No auth token found in AsyncStorage!');
      return { 
        success: false, 
        error: 'Authentication token missing'
      };
    }
    
    const url = `${BASE_URL}/franchisee/dismiss-welcome`;
    console.log('🌐 Making request to:', url);
    
    const response = await axios.post(url, {}, {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    });
    
    console.log('📊 Dismiss Welcome Banner Status:', response.status);
    
    // Process the response
    if (response.data.success) {
      console.log('✅ Welcome banner dismissed successfully');
      return response.data;
    } else {
      console.error('❌ API request failed:', response.data.message || 'Unknown error');
      return { 
        success: false, 
        error: response.data.message || 'Failed to dismiss welcome banner'
      };
    }
  } catch (err) {
    console.error('🔥 Exception in dismissWelcomeBanner:', err);
    
    // Check if it's an authentication error (401)
    if (err.response?.status === 401) {
      console.log('🔐 Authentication error (401) detected');
      sessionEventEmitter.emit('sessionExpiring');
    }
    
    return { 
      success: false, 
      error: err.message || 'An error occurred while dismissing welcome banner'
    };
  }
};

// Replace the updateCartItemQuantity function in your api.js file with this version:

export const updateCartItemQuantity = async (itemId, quantity) => {
  console.log(`🔄 updateCartItemQuantity - itemId: ${itemId}, quantity: ${quantity}`);
  
  try {
    // Get a valid token
    const token = await getAuthToken();
    if (!token) {
      console.log(`⛔ No token available for updating item ${itemId}`);
      sessionEventEmitter.emit('sessionExpiring');
      throw new Error('Authentication error: No authentication token found');
    }
    
    console.log(`🚀 Making API call to update single item ${itemId}`);
    
    try {
      // Use the new single item update endpoint
      const response = await axiosInstance.post('/franchisee/cart/update-item', {
        item_id: itemId,
        quantity: quantity
      });
      
      console.log(`📊 Response status: ${response.status}`);
      console.log(`📊 Response headers:`, response.headers);
      console.log(`📊 Response data type:`, typeof response.data);
      
      // Check if response is HTML (login redirect) - this is the key fix
      if (typeof response.data === 'string' && response.data.includes('<!DOCTYPE html>')) {
        console.log(`🔐 HTML response detected for item ${itemId} - session expired`);
        sessionEventEmitter.emit('sessionExpiring');
        throw new Error('Authentication error: Your session has expired.');
      }
      
      // Check if response is not an object or is empty
      if (!response.data || typeof response.data !== 'object') {
        console.log(`❌ Invalid response format for item ${itemId}:`, typeof response.data);
        console.log(`❌ Response data preview:`, String(response.data).substring(0, 100));
        throw new Error('Invalid response from server - expected JSON but got ' + typeof response.data);
      }
      
      console.log(`✅ Valid JSON response for item ${itemId}:`, response.data);
      
      // Ensure consistent response structure
      if (!('success' in response.data)) {
        response.data.success = true;
      }
      
      // Extract cart count consistently
      const cartCount = extractCartCount(response.data);
      if (cartCount >= 0) {
        response.data.items_count = cartCount;
      }
      
      return response.data;
      
    } catch (axiosError) {
      console.error(`🛑 Axios error for item ${itemId}:`, axiosError.message);
      
      // Check for network/connection errors
      if (!axiosError.response) {
        console.log(`🌐 Network error for item ${itemId}`);
        throw new Error('Network error: Unable to connect to server');
      }
      
      console.log(`🔍 Error response status: ${axiosError.response.status}`);
      console.log(`🔍 Error response headers:`, axiosError.response.headers);
      
      // Check if it's an authentication error (401)
      if (axiosError.response.status === 401) {
        console.log(`🔐 Authentication error (401) detected for item ${itemId}`);
        sessionEventEmitter.emit('sessionExpiring');
        throw new Error('Authentication error: Your session has expired.');
      }
      
      // Check for HTML response in error (redirect to login page)
      const contentType = axiosError.response.headers?.['content-type'];
      if (contentType && contentType.includes('text/html')) {
        console.log(`🔐 HTML content-type detected for item ${itemId} (likely login redirect)`);
        sessionEventEmitter.emit('sessionExpiring');
        throw new Error('Authentication error: Your session has expired.');
      }
      
      // Check if error response data is HTML string
      if (typeof axiosError.response.data === 'string' && axiosError.response.data.includes('<!DOCTYPE html>')) {
        console.log(`🔐 HTML error response data detected for item ${itemId} - session expired`);
        sessionEventEmitter.emit('sessionExpiring');
        throw new Error('Authentication error: Your session has expired.');
      }
      
      // Return API error message if available and it's a proper JSON response
      if (axiosError.response.data && typeof axiosError.response.data === 'object' && axiosError.response.data.message) {
        return {
          success: false,
          message: axiosError.response.data.message,
          was_adjusted: axiosError.response.data.was_adjusted || false,
          final_quantity: axiosError.response.data.final_quantity,
          item_removed: axiosError.response.data.item_removed || false
        };
      }
      
      // Default error for unhandled cases
      throw axiosError;
    }
    
  } catch (error) {
    console.error(`❌ Error updating single item ${itemId}:`, error.message);
    
    // Handle authentication errors
    if (error.message && error.message.includes('Authentication error')) {
      throw error; // Let this bubble up to be handled by the UI
    }
    
    // For other errors, return a structured error response
    return {
      success: false,
      message: error.message || `Failed to update item ${itemId}`
    };
  }
};

// Updated placeOrder method 

export const placeOrder = async (orderData, token = null) => {
  try {
    console.log('🚀 placeOrder - Function called');
    console.log('📦 Order data:', orderData);
    
    // Get token from storage or use provided token
    const authToken = token || await AsyncStorage.getItem('userToken');
    
    if (!authToken) {
      console.error('⛔ No auth token found for placing order!');
      return {
        success: false,
        error: 'Authentication token not found'
      };
    }

    console.log('🔑 Auth token found for placing order');
    console.log('🌐 Making request to:', `${BASE_URL}/franchisee/cart/place-order`);

    // Use the cart controller's placeOrder method endpoint
    const response = await fetch(`${BASE_URL}/franchisee/cart/place-order`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authToken}`,
        'Accept': 'application/json',
      },
      body: JSON.stringify(orderData),
    });

    console.log('📊 Place Order API Status:', response.status);

    // Get response text first to handle both JSON and potential error responses
    const responseText = await response.text();
    console.log('📄 Raw response:', responseText.substring(0, 200) + '...');

    let data;
    try {
      data = JSON.parse(responseText);
    } catch (jsonError) {
      console.error('❌ JSON parsing error:', jsonError);
      return {
        success: false,
        error: 'Invalid response from server',
        message: 'Server returned invalid format'
      };
    }

    if (response.ok && data.success) {
      console.log('✅ Order placed successfully');
      return {
        success: true,
        data: data,
        order_id: data.order_id,
        total: data.total,
        message: data.message || 'Order placed successfully'
      };
    } else {
      console.error('❌ Order placement failed:', data.message || 'Unknown error');
      return {
        success: false,
        message: data.message || `HTTP error! status: ${response.status}`,
        error: data.message || `Failed to place order (${response.status})`,
        details: data.details || null
      };
    }

  } catch (error) {
    console.error('🔥 Exception in placeOrder:', error);
    
    // Check if it's an authentication error (401)
    if (error.response?.status === 401) {
      console.log('🔐 Authentication error (401) detected');
      sessionEventEmitter.emit('sessionExpiring');
    }
    
    return {
      success: false,
      message: error.message || 'Failed to place order',
      error: error.message || 'Network or server error occurred'
    };
  }
};