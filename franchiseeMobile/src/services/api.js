
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

