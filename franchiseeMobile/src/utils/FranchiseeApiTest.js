/**
 * Franchisee API Test Module
 * 
 * Comprehensive testing utility for all franchisee API endpoints
 * Used to verify connectivity, data structure, and content validation
 */

import AsyncStorage from '@react-native-async-storage/async-storage';
import { BASE_URL } from '../services/api';

// Default headers for unauthenticated requests
const DEFAULT_HEADERS = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
};

/**
 * Create auth headers with token
 * @param {string} token - JWT token
 * @returns {Object} - Headers with Authorization
 */
const authHeaders = (token) => {
  if (!token) {
    console.warn('⚠️ No authentication token provided!');
    return DEFAULT_HEADERS;
  }
  
  return {
    ...DEFAULT_HEADERS,
    'Authorization': `Bearer ${token}`,
  };
};

/**
 * Process API response with detailed data validation
 * @param {Response} response - Fetch API response
 * @param {Object} options - Processing options
 * @returns {Object} - Processed result with validation
 */
const processResponse = async (response, options = {}) => {
  const { endpoint, expectedKeys = [], requiredFields = [] } = options;
  
  try {
    // Initialize result
    const result = {
      status: response.status,
      statusText: response.statusText,
      ok: response.ok,
      contentType: response.headers.get('content-type') || 'unknown',
      endpoint,
      success: response.ok,
      message: response.ok ? 'Request successful' : `Request failed with status ${response.status}`,
      data: null,
      validation: {
        hasExpectedKeys: false,
        hasRequiredFields: false, 
        missingKeys: [],
        missingFields: [],
      },
      originalResponse: null,
      fullData: null, // Store the entire response data for complete viewing
      dataStructure: null, // Store the structure of the data
      tableData: null // Store formatted table data
    };
    
    // Check content type
    if (result.contentType.includes('application/json')) {
      try {
        // Parse JSON response
        const jsonData = await response.json();
        result.originalResponse = jsonData;
        result.fullData = jsonData; // Store the complete data
        console.log(`Response from ${endpoint}:`, jsonData);
        
        // Extract key/value structure for data inspection
        result.dataStructure = extractDataStructure(jsonData);
        
        // Extract appropriate table data if available
        result.tableData = extractTableData(jsonData, endpoint);
        
        // Extract the actual data based on common patterns
        if (jsonData.data) {
          // Laravel resource or API response with data field
          result.data = jsonData.data;
        } else if (jsonData.success !== undefined) {
          // Our custom API format with success flag
          result.success = jsonData.success;
          
          // Extract relevant fields from response
          result.data = { ...jsonData };
          delete result.data.success; // Remove success flag from data
          
          if (jsonData.message) {
            result.message = jsonData.message;
            delete result.data.message; // Remove message from data
          }
        } else {
          // Direct data response
          result.data = jsonData;
        }
        
        // Validate expected keys
        if (expectedKeys.length > 0) {
          const responseKeys = Object.keys(result.originalResponse);
          const missingKeys = expectedKeys.filter(key => !responseKeys.includes(key));
          
          result.validation.hasExpectedKeys = missingKeys.length === 0;
          result.validation.missingKeys = missingKeys;
        } else {
          result.validation.hasExpectedKeys = true;
        }
        
        // Validate required fields in the extracted data
        if (requiredFields.length > 0 && result.data) {
          const dataKeys = Object.keys(result.data);
          const missingFields = requiredFields.filter(field => !dataKeys.includes(field));
          
          result.validation.hasRequiredFields = missingFields.length === 0;
          result.validation.missingFields = missingFields;
        } else {
          result.validation.hasRequiredFields = true;
        }
        
      } catch (error) {
        // JSON parsing error
        console.error(`Error parsing JSON from ${endpoint}:`, error);
        result.success = false;
        result.message = `JSON parsing error: ${error.message}`;
        
        try {
          // Try to get raw text
          const text = await response.text();
          result.data = {
            preview: text.substring(0, 200) + (text.length > 200 ? '...' : '')
          };
          result.fullData = { raw: text };
          result.isHtmlResponse = text.trim().startsWith('<');
          
          // Include header information for debugging HTML responses
          if (result.isHtmlResponse) {
            // Extract title if possible
            const titleMatch = text.match(/<title>([^<]+)<\/title>/);
            if (titleMatch && titleMatch[1]) {
              result.htmlTitle = titleMatch[1];
            }
          }
        } catch (e) {
          result.data = { error: 'Failed to read response body' };
        }
      }
    } else {
      // Non-JSON response
      try {
        const text = await response.text();
        result.success = false;
        result.message = 'Non-JSON response received';
        result.data = {
          preview: text.substring(0, 200) + (text.length > 200 ? '...' : ''),
          isHtml: text.trim().startsWith('<')
        };
        result.fullData = { raw: text };
        result.isHtmlResponse = text.trim().startsWith('<');
        
        // Include header information for debugging HTML responses
        if (result.isHtmlResponse) {
          // Extract title if possible
          const titleMatch = text.match(/<title>([^<]+)<\/title>/);
          if (titleMatch && titleMatch[1]) {
            result.htmlTitle = titleMatch[1];
          }
        }
      } catch (e) {
        result.data = { error: 'Failed to read response body' };
      }
    }
    
    return result;
  } catch (error) {
    // Unexpected error during processing
    console.error(`Unexpected error processing response from ${endpoint}:`, error);
    return {
      status: response?.status || 0,
      success: false,
      message: `Error processing response: ${error.message}`,
      endpoint,
      error: error.message
    };
  }
};

/**
 * Extract table data from API response based on common patterns
 * @param {Object} data - The API response data
 * @param {string} endpoint - The endpoint URL (used for context)
 * @returns {Object|null} - Extracted table data or null
 */
const extractTableData = (data, endpoint) => {
  // Handle dashboard data
  if (endpoint.includes('/dashboard')) {
    const result = {
      type: 'dashboard',
      stats: null,
      charts: null,
      recentOrders: null,
      popularProducts: null
    };
    
    // Extract stats
    if (data.stats) {
      result.stats = data.stats;
    }
    
    // Extract chart data
    if (data.charts) {
      result.charts = data.charts;
    }
    
    // Extract recent orders
    if (data.recent_orders || data.recentOrders) {
      result.recentOrders = data.recent_orders || data.recentOrders;
    }
    
    // Extract popular products
    if (data.popular_products || data.popularProducts) {
      result.popularProducts = data.popular_products || data.popularProducts;
    }
    
    return result;
  }
  
  // Handle catalog/products data
  if (endpoint.includes('/catalog') || endpoint.includes('/products')) {
    const result = {
      type: 'catalog',
      products: [],
      categories: [],
      pagination: null,
      total: 0
    };
    
    // Extract products using different possible structures
    let extractedProducts = [];
    let hasProducts = false;
    
    if (Array.isArray(data)) {
      extractedProducts = data;
      hasProducts = true;
    } else if (data.data && Array.isArray(data.data)) {
      extractedProducts = data.data;
      hasProducts = true;
    } else if (data.products && Array.isArray(data.products)) {
      extractedProducts = data.products;
      hasProducts = true;
    } else if (data.data && data.data.data && Array.isArray(data.data.data)) {
      extractedProducts = data.data.data;
      hasProducts = true;
    }
    
    if (hasProducts) {
      // Normalize product structure for display
      result.products = extractedProducts.map(product => ({
        id: product.id,
        name: product.name,
        price: product.price || product.base_price || 0,
        inventory: product.inventory_count || product.stock_quantity || 0,
        category: product.category?.name || 'Uncategorized',
        image_url: product.image_url || (product.images && product.images[0] ? product.images[0].url : null)
      }));
      
      result.total = extractedProducts.length;
    }
    
    // Extract categories
    if (data.categories && Array.isArray(data.categories)) {
      result.categories = data.categories;
    }
    
    // Extract pagination info
    if (data.pagination) {
      result.pagination = data.pagination;
    } else if (data.current_page) {
      result.pagination = {
        current_page: data.current_page,
        last_page: data.last_page,
        per_page: data.per_page,
        total: data.total
      };
    }
    
    return result;
  }
  
  // Handle orders data
  if (endpoint.includes('/orders')) {
    const result = {
      type: 'orders',
      orders: [],
      stats: null,
      pagination: null,
      total: 0
    };
    
    // Extract orders using different possible structures
    let extractedOrders = [];
    let hasOrders = false;
    
    if (Array.isArray(data)) {
      extractedOrders = data;
      hasOrders = true;
    } else if (data.orders && Array.isArray(data.orders)) {
      extractedOrders = data.orders;
      hasOrders = true;
    } else if (data.data && Array.isArray(data.data)) {
      extractedOrders = data.data;
      hasOrders = true;
    } else if (data.data && data.data.data && Array.isArray(data.data.data)) {
      extractedOrders = data.data.data;
      hasOrders = true;
    }
    
    if (hasOrders) {
      // Normalize order structure for display
      result.orders = extractedOrders.map(order => {
        // Extract items if they exist
        let items = [];
        if (order.items && Array.isArray(order.items)) {
          items = order.items.map(item => ({
            id: item.id,
            product_id: item.product_id,
            product_name: item.product?.name || `Product #${item.product_id}`,
            quantity: item.quantity,
            price: item.price,
            subtotal: item.price * item.quantity,
            variant_id: item.variant_id,
            variant_name: item.variant?.name
          }));
        }
        
        return {
          id: order.id,
          status: order.status,
          total: order.total_amount || order.total || 0,
          date: order.created_at,
          items_count: order.items_count || (items.length > 0 ? items.length : 0),
          shipping_address: order.shipping_address,
          items: items
        };
      });
      
      result.total = extractedOrders.length;
    }
    
    // Extract order stats/counts
    if (data.stats) {
      result.stats = data.stats;
    }
    
    // Extract order counts by status
    if (data.order_counts) {
      result.orderCounts = data.order_counts;
    }
    
    // Extract pagination info
    if (data.pagination) {
      result.pagination = data.pagination;
    } else if (data.current_page) {
      result.pagination = {
        current_page: data.current_page,
        last_page: data.last_page,
        per_page: data.per_page,
        total: data.total
      };
    }
    
    return result;
  }
  
  // Handle cart data
  if (endpoint.includes('/cart')) {
    const result = {
      type: 'cart',
      items: [],
      total: 0,
      item_count: 0
    };
    
    // Extract cart items
    let cartItems = [];
    
    if (data.items && Array.isArray(data.items)) {
      cartItems = data.items;
    } else if (data.cart_items && Array.isArray(data.cart_items)) {
      cartItems = data.cart_items;
    } else if (Array.isArray(data)) {
      cartItems = data;
    }
    
    if (cartItems.length > 0) {
      // Normalize cart item structure
      result.items = cartItems.map(item => ({
        id: item.id,
        product_id: item.product_id,
        product_name: item.product?.name || `Product #${item.product_id}`,
        variant_id: item.variant_id,
        variant_name: item.variant?.name || null,
        quantity: item.quantity,
        price: item.price,
        subtotal: item.price * item.quantity
      }));
      
      result.item_count = cartItems.length;
    }
    
    // Extract cart total
    if (data.total) {
      result.total = data.total;
    } else if (result.items.length > 0) {
      result.total = result.items.reduce((sum, item) => sum + item.subtotal, 0);
    }
    
    return result;
  }
  
  // Handle profile data
  if (endpoint.includes('/profile') || endpoint.includes('/me')) {
    const result = {
      type: 'profile',
      user: null,
      franchisee: null
    };
    
    // Extract user data
    if (data.id && data.email) {
      result.user = {
        id: data.id,
        name: data.name,
        email: data.email,
        role: data.role || data.role_id
      };
    } else if (data.user) {
      result.user = {
        id: data.user.id,
        name: data.user.name,
        email: data.user.email,
        role: data.user.role || data.user.role_id
      };
    }
    
    // Extract franchisee profile data
    if (data.franchisee || data.franchiseeProfile) {
      result.franchisee = data.franchisee || data.franchiseeProfile;
    }
    
    return result;
  }
  
  // For endpoints we don't have specific patterns for, return generic data
  return {
    type: 'generic',
    raw: data
  };
};

/**
 * Extract a structured representation of the data
 * @param {Object|Array} data - API response data
 * @param {number} depth - Current recursion depth
 * @returns {Object} - Structure description
 */
const extractDataStructure = (data, depth = 0) => {
  // Limit recursion depth to prevent circular references
  if (depth > 3) return { type: 'truncated' };
  
  if (data === null) return { type: 'null' };
  
  if (Array.isArray(data)) {
    if (data.length === 0) return { type: 'array', isEmpty: true };
    
    const sampleItem = extractDataStructure(data[0], depth + 1);
    return {
      type: 'array',
      length: data.length,
      sample: sampleItem,
      items: data.slice(0, 3).map(item => typeof item === 'object' ? null : item)
    };
  }
  
  if (typeof data === 'object') {
    const keys = Object.keys(data);
    if (keys.length === 0) return { type: 'object', isEmpty: true };
    
    const structure = {};
    keys.forEach(key => {
      const value = data[key];
      if (value === null) {
        structure[key] = { type: 'null' };
      } else if (Array.isArray(value)) {
        structure[key] = {
          type: 'array',
          length: value.length,
          isEmpty: value.length === 0,
          items: value.length > 0 ? (typeof value[0] === 'object' ? '{...}' : String(value[0])) : null
        };
      } else if (typeof value === 'object') {
        structure[key] = depth < 2 ? 
          extractDataStructure(value, depth + 1) : 
          { type: 'object', depth: 'nested' };
      } else {
        structure[key] = {
          type: typeof value,
          preview: String(value).substring(0, 50)
        };
      }
    });
    
    return {
      type: 'object',
      keys,
      structure
    };
  }
  
  // Handle primitive values
  return {
    type: typeof data,
    value: data
  };
};

/**
 * Run a franchisee API test suite
 * @param {Object} credentials - Login credentials {email, password}
 * @returns {Object} - Test results for all endpoints
 */
export const runFranchiseeApiTests = async (credentials) => {
  const { email, password } = credentials;
  
  // Initialize results structure
  const results = {
    timestamp: new Date().toISOString(),
    baseUrl: BASE_URL,
    authStatus: 'not_tested',
    token: null,
    summary: {
      total: 0,
      passed: 0,
      failed: 0,
      skipped: 0
    },
    endpoints: {}
  };
  
  console.log(`🧪 Running franchisee API tests on ${BASE_URL}`);
  
  try {
    // Step 1: Test authentication
    const authResult = await testAuthentication(credentials);
    results.authStatus = authResult.success ? 'authenticated' : 'failed';
    results.token = authResult.token;
    
    // Record authentication results
    results.endpoints.authentication = authResult;
    updateSummary(results, authResult);
    
    // Exit early if authentication fails
    if (!authResult.success || !authResult.token) {
      console.error('❌ Authentication failed, cannot proceed with other tests');
      return results;
    }
    
    // Step 2: Test user profile endpoints
    const profileTests = await testProfileEndpoints(authResult.token);
    Object.assign(results.endpoints, profileTests);
    Object.keys(profileTests).forEach(key => updateSummary(results, profileTests[key]));
    
    // Step 3: Test catalog endpoints
    const catalogTests = await testCatalogEndpoints(authResult.token);
    Object.assign(results.endpoints, catalogTests);
    Object.keys(catalogTests).forEach(key => updateSummary(results, catalogTests[key]));
    
    // Step 4: Test cart endpoints
    const cartTests = await testCartEndpoints(authResult.token);
    Object.assign(results.endpoints, cartTests);
    Object.keys(cartTests).forEach(key => updateSummary(results, cartTests[key]));
    
    // Step 5: Test order endpoints
    const orderTests = await testOrderEndpoints(authResult.token);
    Object.assign(results.endpoints, orderTests);
    Object.keys(orderTests).forEach(key => updateSummary(results, orderTests[key]));
    
  } catch (error) {
    console.error('Test suite encountered an error:', error);
    results.error = error.message;
  }
  
  console.log('📊 Test Summary:', results.summary);
  return results;
};

/**
 * Update test summary statistics
 * @param {Object} results - Main results object
 * @param {Object} testResult - Individual test result
 */
const updateSummary = (results, testResult) => {
  results.summary.total++;
  
  if (testResult.skipped) {
    results.summary.skipped++;
  } else if (testResult.success) {
    results.summary.passed++;
  } else {
    results.summary.failed++;
  }
};

/**
 * Test authentication endpoints
 * @param {Object} credentials - {email, password}
 * @returns {Object} - Authentication test results
 */
const testAuthentication = async (credentials) => {
  const { email, password } = credentials;
  
  console.log('🔑 Testing authentication...');
  
  try {
    // Test login endpoint
    const endpoint = `${BASE_URL}/auth/login`;
    console.log(`Trying login at: ${endpoint}`);
    
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: DEFAULT_HEADERS,
      body: JSON.stringify({ email, password }),
    });
    
    const result = await processResponse(response, {
      endpoint,
      expectedKeys: ['success', 'token', 'user'],
      requiredFields: ['token']
    });
    
    // Extract token if login successful
    let token = null;
    if (result.success && result.originalResponse?.token) {
      token = result.originalResponse.token;
      console.log('✅ Successfully obtained authentication token');
      
      // Store token for other tests
      try {
        await AsyncStorage.setItem('testUserToken', token);
      } catch (e) {
        console.warn('Failed to store token in AsyncStorage:', e);
      }
    }
    
    return {
      ...result,
      name: 'Authentication',
      token
    };
  } catch (error) {
    console.error('Authentication test error:', error);
    return {
      success: false,
      message: `Authentication error: ${error.message}`,
      name: 'Authentication'
    };
  }
};

/**
 * Test profile-related endpoints
 * @param {string} token - Authentication token
 * @returns {Object} - Profile test results
 */
const testProfileEndpoints = async (token) => {
  console.log('👤 Testing profile endpoints...');
  const results = {};
  
  // Skip if no token
  if (!token) {
    return {
      'user_profile': {
        skipped: true,
        success: false,
        message: 'Skipped due to missing authentication token',
        name: 'User Profile'
      }
    };
  }
  
  try {
    // Test user profile endpoint
    const profileEndpoints = [
      `${BASE_URL}/auth/me`,
      `${BASE_URL}/franchisee/profile`,
      `${BASE_URL}/user/profile`
    ];
    
    let profileResult = null;
    
    // Try multiple profile endpoints
    for (const endpoint of profileEndpoints) {
      try {
        console.log(`Trying profile endpoint: ${endpoint}`);
        
        const response = await fetch(endpoint, {
          method: 'GET',
          headers: authHeaders(token)
        });
        
        if (response.ok) {
          profileResult = await processResponse(response, {
            endpoint,
            expectedKeys: ['id', 'name', 'email'],
            requiredFields: ['id', 'email']
          });
          
          profileResult.name = 'User Profile';
          
          if (profileResult.success) {
            console.log('✅ Successfully retrieved user profile');
            break;
          }
        }
      } catch (error) {
        console.warn(`Error testing ${endpoint}:`, error.message);
      }
    }
    
    if (!profileResult) {
      profileResult = {
        success: false,
        message: 'All profile endpoints failed',
        name: 'User Profile'
      };
    }
    
    results.user_profile = profileResult;
    
    // Test franchisee address endpoint
    try {
      const addressEndpoint = `${BASE_URL}/franchisee/address`;
      console.log(`Testing address endpoint: ${addressEndpoint}`);
      
      const response = await fetch(addressEndpoint, {
        method: 'GET',
        headers: authHeaders(token)
      });
      
      const addressResult = await processResponse(response, {
        endpoint: addressEndpoint,
        expectedKeys: ['address', 'city', 'state', 'zip'],
        requiredFields: ['address']
      });
      
      addressResult.name = 'Franchisee Address';
      
      if (addressResult.success) {
        console.log('✅ Successfully retrieved franchisee address');
      }
      
      results.franchisee_address = addressResult;
    } catch (error) {
      console.warn('Error testing address endpoint:', error.message);
      results.franchisee_address = {
        success: false,
        message: `Address endpoint error: ${error.message}`,
        name: 'Franchisee Address'
      };
    }
    
    return results;
  } catch (error) {
    console.error('Profile tests error:', error);
    return {
      'user_profile': {
        success: false,
        message: `Profile tests error: ${error.message}`,
        name: 'User Profile'
      }
    };
  }
};

/**
 * Test catalog-related endpoints
 * @param {string} token - Authentication token
 * @returns {Object} - Catalog test results
 */
const testCatalogEndpoints = async (token) => {
  console.log('📚 Testing catalog endpoints...');
  const results = {};
  
  // Skip if no token
  if (!token) {
    return {
      'product_catalog': {
        skipped: true,
        success: false,
        message: 'Skipped due to missing authentication token',
        name: 'Product Catalog'
      }
    };
  }
  
  try {
    // Test catalog listing endpoint
    const catalogEndpoints = [
      `${BASE_URL}/franchisee/catalog`,
      `${BASE_URL}/catalog`,
      `${BASE_URL}/products`,
      `${BASE_URL}/franchisee/products`
    ];
    
    let catalogResult = null;
    let productsData = null;
    let productId = null;
    
    // Try multiple catalog endpoints
    for (const endpoint of catalogEndpoints) {
      try {
        console.log(`Trying catalog endpoint: ${endpoint}`);
        
        const response = await fetch(endpoint, {
          method: 'GET',
          headers: authHeaders(token)
        });
        
        if (response.ok) {
          catalogResult = await processResponse(response, {
            endpoint,
          });
          
          // Look for products array in various locations
          let products = null;
          
          if (Array.isArray(catalogResult.data)) {
            products = catalogResult.data;
          } else if (catalogResult.data?.products) {
            products = catalogResult.data.products;
          } else if (catalogResult.originalResponse?.products) {
            products = catalogResult.originalResponse.products;
          } else if (catalogResult.originalResponse?.data && Array.isArray(catalogResult.originalResponse.data)) {
            products = catalogResult.originalResponse.data;
          }
          
          if (products && products.length > 0) {
            productsData = products;
            productId = products[0].id;
            catalogResult.success = true;
            catalogResult.productCount = products.length;
            catalogResult.sampleProduct = products[0];
            break;
          }
        }
      } catch (error) {
        console.warn(`Error testing ${endpoint}:`, error.message);
      }
    }
    
    if (!catalogResult) {
      catalogResult = {
        success: false,
        message: 'All catalog endpoints failed',
        name: 'Product Catalog'
      };
    }
    
    catalogResult.name = 'Product Catalog';
    results.product_catalog = catalogResult;
    
    if (catalogResult.success && productId) {
      console.log('✅ Successfully retrieved product catalog');
      
      // Test product details endpoint
      try {
        const detailsEndpoint = `${BASE_URL}/franchisee/catalog/${productId}`;
        console.log(`Testing product details endpoint: ${detailsEndpoint}`);
        
        const response = await fetch(detailsEndpoint, {
          method: 'GET',
          headers: authHeaders(token)
        });
        
        const detailsResult = await processResponse(response, {
          endpoint: detailsEndpoint,
          expectedKeys: ['id', 'name', 'price'],
        });
        
        detailsResult.name = 'Product Details';
        
        if (detailsResult.success) {
          console.log('✅ Successfully retrieved product details');
        }
        
        results.product_details = detailsResult;
      } catch (error) {
        console.warn('Error testing product details endpoint:', error.message);
        results.product_details = {
          success: false,
          message: `Product details endpoint error: ${error.message}`,
          name: 'Product Details'
        };
      }
      
      // Test favorite toggle endpoint
      try {
        const favoriteEndpoint = `${BASE_URL}/franchisee/toggle-favorite`;
        console.log(`Testing favorite toggle endpoint: ${favoriteEndpoint}`);
        
        const response = await fetch(favoriteEndpoint, {
          method: 'POST',
          headers: authHeaders(token),
          body: JSON.stringify({ product_id: productId }),
        });
        
        const favoriteResult = await processResponse(response, {
          endpoint: favoriteEndpoint,
        });
        
        favoriteResult.name = 'Toggle Favorite';
        
        if (favoriteResult.success) {
          console.log('✅ Successfully toggled product favorite status');
        }
        
        results.toggle_favorite = favoriteResult;
      } catch (error) {
        console.warn('Error testing favorite toggle endpoint:', error.message);
        results.toggle_favorite = {
          success: false,
          message: `Toggle favorite endpoint error: ${error.message}`,
          name: 'Toggle Favorite'
        };
      }
    }
    
    return results;
  } catch (error) {
    console.error('Catalog tests error:', error);
    return {
      'product_catalog': {
        success: false,
        message: `Catalog tests error: ${error.message}`,
        name: 'Product Catalog'
      }
    };
  }
};

/**
 * Test cart-related endpoints
 * @param {string} token - Authentication token
 * @returns {Object} - Cart test results
 */
const testCartEndpoints = async (token) => {
  console.log('🛒 Testing cart endpoints...');
  const results = {};
  
  // Skip if no token
  if (!token) {
    return {
      'view_cart': {
        skipped: true,
        success: false,
        message: 'Skipped due to missing authentication token',
        name: 'View Cart'
      }
    };
  }
  
  try {
    // Test view cart endpoint
    const cartEndpoint = `${BASE_URL}/franchisee/cart`;
    console.log(`Testing cart endpoint: ${cartEndpoint}`);
    
    const response = await fetch(cartEndpoint, {
      method: 'GET',
      headers: authHeaders(token)
    });
    
    const cartResult = await processResponse(response, {
      endpoint: cartEndpoint,
    });
    
    cartResult.name = 'View Cart';
    
    if (cartResult.success) {
      console.log('✅ Successfully retrieved cart contents');
    }
    
    results.view_cart = cartResult;
    
    // Test product catalog to get a product ID for cart operations
    const catalogEndpoints = [
      `${BASE_URL}/franchisee/catalog`,
      `${BASE_URL}/catalog`,
      `${BASE_URL}/products`
    ];
    
    let productId = null;
    
    // Find a product to add to cart
    for (const endpoint of catalogEndpoints) {
      try {
        const catalogResponse = await fetch(endpoint, {
          method: 'GET',
          headers: authHeaders(token)
        });
        
        if (catalogResponse.ok) {
          const catalogData = await catalogResponse.json();
          
          // Find products array
          let products = null;
          
          if (Array.isArray(catalogData)) {
            products = catalogData;
          } else if (catalogData.data && Array.isArray(catalogData.data)) {
            products = catalogData.data;
          } else if (catalogData.products && Array.isArray(catalogData.products)) {
            products = catalogData.products;
          }
          
          if (products && products.length > 0) {
            productId = products[0].id;
            break;
          }
        }
      } catch (error) {
        console.warn(`Error finding product from ${endpoint}:`, error.message);
      }
    }
    
    if (productId) {
      // Test add to cart endpoint
      try {
        const addToCartEndpoint = `${BASE_URL}/franchisee/cart/add`;
        console.log(`Testing add to cart endpoint: ${addToCartEndpoint}`);
        
        const addResponse = await fetch(addToCartEndpoint, {
          method: 'POST',
          headers: authHeaders(token),
          body: JSON.stringify({
            product_id: productId,
            quantity: 1
          }),
        });
        
        const addResult = await processResponse(addResponse, {
          endpoint: addToCartEndpoint,
        });
        
        addResult.name = 'Add to Cart';
        addResult.productId = productId;
        
        if (addResult.success) {
          console.log('✅ Successfully added product to cart');
        }
        
        results.add_to_cart = addResult;
        
        // Test update cart quantity endpoint
        try {
          const updateCartEndpoint = `${BASE_URL}/franchisee/cart/update`;
          console.log(`Testing update cart endpoint: ${updateCartEndpoint}`);
          
          const updateResponse = await fetch(updateCartEndpoint, {
            method: 'POST',
            headers: authHeaders(token),
            body: JSON.stringify({
              product_id: productId,
              quantity: 2
            }),
          });
          
          const updateResult = await processResponse(updateResponse, {
            endpoint: updateCartEndpoint,
          });
          
          updateResult.name = 'Update Cart';
          
          if (updateResult.success) {
            console.log('✅ Successfully updated cart quantity');
          }
          
          results.update_cart = updateResult;
        } catch (error) {
          console.warn('Error testing update cart endpoint:', error.message);
          results.update_cart = {
            success: false,
            message: `Update cart endpoint error: ${error.message}`,
            name: 'Update Cart'
          };
        }
        
        // Test remove from cart endpoint
        try {
          const removeCartEndpoint = `${BASE_URL}/franchisee/cart/remove`;
          console.log(`Testing remove from cart endpoint: ${removeCartEndpoint}`);
          
          const removeResponse = await fetch(removeCartEndpoint, {
            method: 'POST',
            headers: authHeaders(token),
            body: JSON.stringify({
              product_id: productId
            }),
          });
          
          const removeResult = await processResponse(removeResponse, {
            endpoint: removeCartEndpoint,
          });
          
          removeResult.name = 'Remove from Cart';
          
          if (removeResult.success) {
            console.log('✅ Successfully removed product from cart');
          }
          
          results.remove_from_cart = removeResult;
        } catch (error) {
          console.warn('Error testing remove from cart endpoint:', error.message);
          results.remove_from_cart = {
            success: false,
            message: `Remove from cart endpoint error: ${error.message}`,
            name: 'Remove from Cart'
          };
        }
      } catch (error) {
        console.warn('Error testing add to cart endpoint:', error.message);
        results.add_to_cart = {
          success: false,
          message: `Add to cart endpoint error: ${error.message}`,
          name: 'Add to Cart'
        };
      }
    }
    
    return results;
  } catch (error) {
    console.error('Cart tests error:', error);
    return {
      'view_cart': {
        success: false,
        message: `Cart tests error: ${error.message}`,
        name: 'View Cart'
      }
    };
  }
};

/**
 * Test order-related endpoints
 * @param {string} token - Authentication token
 * @returns {Object} - Order test results
 */
const testOrderEndpoints = async (token) => {
  console.log('📋 Testing order endpoints...');
  const results = {};
  
  // Skip if no token
  if (!token) {
    return {
      'pending_orders': {
        skipped: true,
        success: false,
        message: 'Skipped due to missing authentication token',
        name: 'Pending Orders'
      }
    };
  }
  
  try {
    // Test pending orders endpoint
    const pendingEndpoints = [
      `${BASE_URL}/franchisee/orders/pending`,
      `${BASE_URL}/orders/pending`,
      `${BASE_URL}/franchisee/orders?status=pending`
    ];
    
    let pendingResult = null;
    
    for (const endpoint of pendingEndpoints) {
      try {
        console.log(`Trying pending orders endpoint: ${endpoint}`);
        
        const response = await fetch(endpoint, {
          method: 'GET',
          headers: authHeaders(token)
        });
        
        if (response.ok) {
          pendingResult = await processResponse(response, {
            endpoint,
          });
          
          pendingResult.name = 'Pending Orders';
          
          if (pendingResult.success) {
            console.log('✅ Successfully retrieved pending orders');
            break;
          }
        }
      } catch (error) {
        console.warn(`Error testing ${endpoint}:`, error.message);
      }
    }
    
    if (!pendingResult) {
      pendingResult = {
        success: false,
        message: 'All pending orders endpoints failed',
        name: 'Pending Orders'
      };
    }
    
    results.pending_orders = pendingResult;
    
    // Test order history endpoint
    const historyEndpoints = [
      `${BASE_URL}/franchisee/orders/history`,
      `${BASE_URL}/orders/history`,
      `${BASE_URL}/franchisee/order-history`,
      `${BASE_URL}/franchisee/orders?completed=1`
    ];
    
    let historyResult = null;
    let orderId = null;
    
    for (const endpoint of historyEndpoints) {
      try {
        console.log(`Trying order history endpoint: ${endpoint}`);
        
        const response = await fetch(endpoint, {
          method: 'GET',
          headers: authHeaders(token)
        });
        
        if (response.ok) {
          historyResult = await processResponse(response, {
            endpoint,
          });
          
          // Try to find an order ID
          if (historyResult.originalResponse) {
            const resp = historyResult.originalResponse;
            
            // Check different possible data structures
            if (resp.orders && resp.orders.length > 0) {
              orderId = resp.orders[0].id;
            } else if (Array.isArray(resp) && resp.length > 0) {
              orderId = resp[0].id;
            } else if (resp.data) {
              if (Array.isArray(resp.data) && resp.data.length > 0) {
                orderId = resp.data[0].id;
              } else if (resp.data.orders && resp.data.orders.length > 0) {
                orderId = resp.data.orders[0].id;
              }
            }
            
            if (orderId) {
              console.log(`Found order ID for details test: ${orderId}`);
              historyResult.foundOrderId = true;
            }
          }
          
          historyResult.name = 'Order History';
          
          if (historyResult.success) {
            console.log('✅ Successfully retrieved order history');
            break;
          }
        }
      } catch (error) {
        console.warn(`Error testing ${endpoint}:`, error.message);
      }
    }
    
    if (!historyResult) {
      historyResult = {
        success: false,
        message: 'All order history endpoints failed',
        name: 'Order History'
      };
    }
    
    results.order_history = historyResult;
    
    // Test order details endpoint if we found an order ID
    if (orderId) {
      try {
        const detailsEndpoint = `${BASE_URL}/franchisee/orders/${orderId}/details`;
        console.log(`Testing order details endpoint: ${detailsEndpoint}`);
        
        const response = await fetch(detailsEndpoint, {
          method: 'GET',
          headers: authHeaders(token)
        });
        
        const detailsResult = await processResponse(response, {
          endpoint: detailsEndpoint,
        });
        
        detailsResult.name = 'Order Details';
        detailsResult.orderId = orderId;
        
        if (detailsResult.success) {
          console.log('✅ Successfully retrieved order details');
        }
        
        results.order_details = detailsResult;
      } catch (error) {
        console.warn('Error testing order details endpoint:', error.message);
        results.order_details = {
          success: false,
          message: `Order details endpoint error: ${error.message}`,
          name: 'Order Details'
        };
      }
    }
    
    return results;
  } catch (error) {
    console.error('Order tests error:', error);
    return {
      'pending_orders': {
        success: false,
        message: `Order tests error: ${error.message}`,
        name: 'Pending Orders'
      }
    };
  }
};

/**
 * Generate a detailed report of API test results
 * @param {Object} results - Test results
 * @returns {string} - Formatted report text
 */
export const generateApiTestReport = (results) => {
  const report = [
    '===== FRANCHISEE API TEST REPORT =====',
    `Timestamp: ${results.timestamp}`,
    `Base URL: ${results.baseUrl}`,
    '',
    `Authentication: ${results.authStatus}`,
    '',
    '--- SUMMARY ---',
    `Total Tests: ${results.summary.total}`,
    `Passed: ${results.summary.passed}`,
    `Failed: ${results.summary.failed}`,
    `Skipped: ${results.summary.skipped}`,
    `Success Rate: ${Math.round((results.summary.passed / (results.summary.total - results.summary.skipped)) * 100)}%`,
    '',
    '--- ENDPOINT RESULTS ---',
  ];
  
  Object.keys(results.endpoints).forEach(key => {
    const endpoint = results.endpoints[key];
    const status = endpoint.skipped ? '⏭️ SKIPPED' : (endpoint.success ? '✅ PASSED' : '❌ FAILED');
    
    report.push(`${endpoint.name || key}: ${status}`);
    report.push(`  Message: ${endpoint.message}`);
    
    if (endpoint.endpoint) {
      report.push(`  Endpoint: ${endpoint.endpoint}`);
    }
    
    if (endpoint.status) {
      report.push(`  Status: ${endpoint.status}`);
    }
    
    if (endpoint.validation && !endpoint.skipped) {
      const validation = endpoint.validation;
      
      if (!validation.hasExpectedKeys && validation.missingKeys.length > 0) {
        report.push(`  Missing Keys: ${validation.missingKeys.join(', ')}`);
      }
      
      if (!validation.hasRequiredFields && validation.missingFields.length > 0) {
        report.push(`  Missing Fields: ${validation.missingFields.join(', ')}`);
      }
    }
    
    if (endpoint.data && typeof endpoint.data === 'object') {
      if (endpoint.productCount) {
        report.push(`  Products: ${endpoint.productCount}`);
      }
      
      if (endpoint.orderCount) {
        report.push(`  Orders: ${endpoint.orderCount}`);
      }
      
      // Show sample data structure if available
      if (endpoint.data.structure) {
        report.push(`  Structure: ${JSON.stringify(endpoint.data.structure)}`);
      }
    }
    
    // Show table data summary if available
    if (endpoint.tableData) {
      const tableData = endpoint.tableData;
      report.push('  ---- Data Tables ----');
      
      if (tableData.type === 'catalog' && tableData.products) {
        report.push(`  Products: ${tableData.products.length}`);
        if (tableData.pagination) {
          report.push(`  Pagination: Page ${tableData.pagination.current_page} of ${tableData.pagination.last_page} (${tableData.pagination.total} total)`);
        }
      }
      
      if (tableData.type === 'orders' && tableData.orders) {
        report.push(`  Orders: ${tableData.orders.length}`);
        if (tableData.orderCounts) {
          report.push(`  Order Counts: ${JSON.stringify(tableData.orderCounts)}`);
        }
      }
      
      if (tableData.type === 'dashboard') {
        if (tableData.stats) {
          report.push(`  Dashboard Stats: ${Object.keys(tableData.stats).join(', ')}`);
        }
        if (tableData.recentOrders) {
          report.push(`  Recent Orders: ${tableData.recentOrders.length}`);
        }
        if (tableData.popularProducts) {
          report.push(`  Popular Products: ${tableData.popularProducts.length}`);
        }
      }
      
      if (tableData.type === 'cart' && tableData.items) {
        report.push(`  Cart Items: ${tableData.items.length}`);
        report.push(`  Cart Total: ${tableData.total}`);
      }
      
      if (tableData.type === 'profile' && tableData.user) {
        report.push(`  User: ${tableData.user.name} (${tableData.user.email})`);
        if (tableData.franchisee) {
          report.push(`  Franchisee: ${JSON.stringify(tableData.franchisee).substring(0, 100)}...`);
        }
      }
    }
    
    // HTML response debugging
    if (endpoint.isHtmlResponse) {
      report.push('  [Warning] HTML Response Received');
      if (endpoint.htmlTitle) {
        report.push(`  HTML Title: ${endpoint.htmlTitle}`);
      }
    }
    
    report.push('');
  });
  
  report.push('================================');
  return report.join('\n');
};

export default { runFranchiseeApiTests, generateApiTestReport };