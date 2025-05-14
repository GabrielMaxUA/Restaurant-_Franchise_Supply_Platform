/**
 * API Service for the Franchisee Mobile App
 * Handles all API requests to the backend server
 */

// Base API URL for local XAMPP server
// This should point to your Laravel API endpoint
const BASE_URL = 'http://localhost/Restaurant-_Franchise_Supply_Platform/franchise-supply-platform/public/api';

// Default headers for API requests
const DEFAULT_HEADERS = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
};

/**
 * Helper function to add authentication token to headers
 * @param {string} token - JWT authentication token
 * @returns {Object} - Headers object with Authorization header
 */
const authHeaders = (token) => {
  return {
    ...DEFAULT_HEADERS,
    'Authorization': `Bearer ${token}`,
  };
};

/**
 * Login user and get authentication token
 * @param {string} email - User email
 * @param {string} password - User password
 * @returns {Promise} - API response
 */
export const login = async (email, password) => {
  try {
    const response = await fetch(`${BASE_URL}/auth/login`, {
      method: 'POST',
      headers: DEFAULT_HEADERS,
      body: JSON.stringify({ email, password }),
    });

    return await response.json();
  } catch (error) {
    console.error('Login error:', error);
    throw error;
  }
};

/**
 * Get current user profile
 * @param {string} token - JWT authentication token
 * @returns {Promise} - API response
 */
export const getUserProfile = async (token) => {
  try {
    const response = await fetch(`${BASE_URL}/auth/me`, {
      method: 'GET',
      headers: authHeaders(token),
    });

    return await response.json();
  } catch (error) {
    console.error('Get user profile error:', error);
    throw error;
  }
};

/**
 * Get catalog products with optional filters
 * @param {string} token - JWT authentication token
 * @param {Object} filters - Optional filters like category, search, etc.
 * @returns {Promise} - API response
 */
export const getCatalog = async (token, filters = {}) => {
  try {
    // Build query string from filters
    const queryParams = new URLSearchParams();
    Object.keys(filters).forEach(key => {
      if (filters[key]) {
        queryParams.append(key, filters[key]);
      }
    });

    const queryString = queryParams.toString() ? `?${queryParams.toString()}` : '';
    
    const response = await fetch(`${BASE_URL}/franchisee/catalog${queryString}`, {
      method: 'GET',
      headers: authHeaders(token),
    });

    return await response.json();
  } catch (error) {
    console.error('Get catalog error:', error);
    throw error;
  }
};

/**
 * Toggle product favorite status
 * @param {string} token - JWT authentication token
 * @param {number} productId - Product ID to toggle favorite
 * @returns {Promise} - API response
 */
export const toggleFavorite = async (token, productId) => {
  try {
    const response = await fetch(`${BASE_URL}/franchisee/toggle-favorite`, {
      method: 'POST',
      headers: authHeaders(token),
      body: JSON.stringify({ product_id: productId }),
    });

    return await response.json();
  } catch (error) {
    console.error('Toggle favorite error:', error);
    throw error;
  }
};

/**
 * Get cart contents
 * @param {string} token - JWT authentication token
 * @returns {Promise} - API response
 */
export const getCart = async (token) => {
  try {
    const response = await fetch(`${BASE_URL}/franchisee/cart`, {
      method: 'GET',
      headers: authHeaders(token),
    });

    return await response.json();
  } catch (error) {
    console.error('Get cart error:', error);
    throw error;
  }
};

/**
 * Add product to cart
 * @param {string} token - JWT authentication token
 * @param {number} productId - Product ID to add
 * @param {number|null} variantId - Variant ID (optional)
 * @param {number} quantity - Quantity to add
 * @returns {Promise} - API response
 */
export const addToCart = async (token, productId, variantId, quantity) => {
  try {
    const payload = {
      product_id: productId,
      quantity: quantity,
    };
    
    if (variantId) {
      payload.variant_id = variantId;
    }
    
    const response = await fetch(`${BASE_URL}/franchisee/cart/add`, {
      method: 'POST',
      headers: authHeaders(token),
      body: JSON.stringify(payload),
    });

    return await response.json();
  } catch (error) {
    console.error('Add to cart error:', error);
    throw error;
  }
};

/**
 * Get pending orders
 * @param {string} token - JWT authentication token
 * @param {string|null} status - Optional status filter
 * @returns {Promise} - API response
 */
export const getPendingOrders = async (token, status = null) => {
  try {
    const queryString = status ? `?status=${status}` : '';
    
    const response = await fetch(`${BASE_URL}/franchisee/orders/pending${queryString}`, {
      method: 'GET',
      headers: authHeaders(token),
    });

    return await response.json();
  } catch (error) {
    console.error('Get pending orders error:', error);
    throw error;
  }
};

/**
 * Get order history
 * @param {string} token - JWT authentication token
 * @returns {Promise} - API response
 */
export const getOrderHistory = async (token) => {
  try {
    const response = await fetch(`${BASE_URL}/franchisee/orders/history`, {
      method: 'GET',
      headers: authHeaders(token),
    });

    return await response.json();
  } catch (error) {
    console.error('Get order history error:', error);
    throw error;
  }
};

/**
 * Get order details
 * @param {string} token - JWT authentication token
 * @param {number} orderId - Order ID
 * @returns {Promise} - API response
 */
export const getOrderDetails = async (token, orderId) => {
  try {
    const response = await fetch(`${BASE_URL}/franchisee/orders/${orderId}/details`, {
      method: 'GET',
      headers: authHeaders(token),
    });

    return await response.json();
  } catch (error) {
    console.error('Get order details error:', error);
    throw error;
  }
};

// Export all API functions
export default {
  login,
  getUserProfile,
  getCatalog,
  toggleFavorite,
  getCart,
  addToCart,
  getPendingOrders,
  getOrderHistory,
  getOrderDetails,
};