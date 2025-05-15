// api.js - API service layer for communicating with the backend

import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Base URL - Update this with your actual Laravel backend URL
// If testing on emulator, use 10.0.2.2 instead of localhost for Android
// For iOS simulator, use localhost
// For real device testing, use your computer's IP address on the same network
const API_BASE_URL = 'http://localhost:8000/api';

// Create axios instance with default config
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 10000, // 10 second timeout
});

// Request interceptor for adding token to requests
api.interceptors.request.use(
  async (config) => {
    const token = await AsyncStorage.getItem('userToken');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor for handling token refresh and errors
api.interceptors.response.use(
  (response) => {
    return response;
  },
  async (error) => {
    const originalRequest = error.config;
    
    // If error is 401 and we haven't tried to refresh the token yet
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;
      
      try {
        // Try to refresh the token
        const token = await AsyncStorage.getItem('userToken');
        if (!token) {
          throw new Error('No token found');
        }
        
        const response = await axios.post(`${API_BASE_URL}/auth/refresh`, null, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (response.data && response.data.success) {
          // Store the new token
          await AsyncStorage.setItem('userToken', response.data.token);
          
          // Update the failed request with new token and retry
          originalRequest.headers.Authorization = `Bearer ${response.data.token}`;
          return api(originalRequest);
        } else {
          // Refresh failed, user needs to login again
          await AsyncStorage.removeItem('userToken');
          await AsyncStorage.removeItem('userProfile');
          throw new Error('Token refresh failed');
        }
      } catch (refreshError) {
        // Clear tokens and reject promise
        await AsyncStorage.removeItem('userToken');
        await AsyncStorage.removeItem('userProfile');
        return Promise.reject(refreshError);
      }
    }
    
    return Promise.reject(error);
  }
);

// Auth API Services
export const authService = {
  // Login with email and password
  login: async (email, password) => {
    try {
      const response = await api.post('/auth/login', { email, password });
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Logout and invalidate token
  logout: async () => {
    try {
      const response = await api.post('/auth/logout');
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Get current user profile
  getCurrentUser: async () => {
    try {
      const response = await api.get('/auth/me');
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Refresh token
  refreshToken: async () => {
    try {
      const response = await api.post('/auth/refresh');
      return response.data;
    } catch (error) {
      throw error;
    }
  }
};

// Dashboard API Services
export const dashboardService = {
  // Get dashboard data
  getDashboardData: async () => {
    try {
      const response = await api.get('/franchisee/dashboard');
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Get dashboard stats
  getStats: async () => {
    try {
      const response = await api.get('/franchisee/dashboard/stats');
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Get spending data
  getSpending: async () => {
    try {
      const response = await api.get('/franchisee/dashboard/spending');
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Get chart data
  getCharts: async () => {
    try {
      const response = await api.get('/franchisee/dashboard/charts');
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Get recent orders
  getRecentOrders: async () => {
    try {
      const response = await api.get('/franchisee/dashboard/recent-orders');
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Get popular products
  getPopularProducts: async () => {
    try {
      const response = await api.get('/franchisee/dashboard/popular-products');
      return response.data;
    } catch (error) {
      throw error;
    }
  }
};

// Cart API Services
export const cartService = {
  // Get cart items
  getCart: async () => {
    try {
      const response = await api.get('/franchisee/cart');
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Add item to cart
  addToCart: async (productId, quantity, variantId = null) => {
    try {
      const response = await api.post('/franchisee/cart/add', {
        product_id: productId,
        quantity: quantity,
        variant_id: variantId
      });
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Update cart item
  updateCart: async (cartItemId, quantity) => {
    try {
      const response = await api.post('/franchisee/cart/update', {
        cart_item_id: cartItemId,
        quantity: quantity
      });
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Remove item from cart
  removeFromCart: async (cartItemId) => {
    try {
      const response = await api.post('/franchisee/cart/remove', {
        cart_item_id: cartItemId
      });
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Clear the cart
  clearCart: async () => {
    try {
      const response = await api.get('/franchisee/cart/clear');
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Get checkout information
  getCheckout: async () => {
    try {
      const response = await api.get('/franchisee/cart/checkout');
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Place an order
  placeOrder: async (notes = null) => {
    try {
      const response = await api.post('/franchisee/cart/place-order', {
        notes: notes
      });
      return response.data;
    } catch (error) {
      throw error;
    }
  }
};

// Order API Services
export const orderService = {
  // Get pending orders
  getPendingOrders: async () => {
    try {
      const response = await api.get('/franchisee/orders/pending');
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Get order history
  getOrderHistory: async () => {
    try {
      const response = await api.get('/franchisee/orders/history');
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Get order details
  getOrderDetails: async (orderId) => {
    try {
      const response = await api.get(`/franchisee/orders/${orderId}/details`);
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Repeat an order
  repeatOrder: async (orderId) => {
    try {
      const response = await api.get(`/franchisee/orders/${orderId}/repeat`);
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Generate invoice
  generateInvoice: async (orderId) => {
    try {
      const response = await api.get(`/franchisee/orders/${orderId}/invoice`);
      return response.data;
    } catch (error) {
      throw error;
    }
  }
};

// Catalog API Services
export const catalogService = {
  // Get catalog items
  getCatalog: async (params = {}) => {
    try {
      const response = await api.get('/franchisee/catalog', { params });
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Toggle favorite product
  toggleFavorite: async (productId) => {
    try {
      const response = await api.post('/franchisee/toggle-favorite', {
        product_id: productId
      });
      return response.data;
    } catch (error) {
      throw error;
    }
  }
};

// Profile API Services
export const profileService = {
  // Get user profile
  getProfile: async () => {
    try {
      const response = await api.get('/franchisee/profile');
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Update user profile
  updateProfile: async (profileData) => {
    try {
      const response = await api.put('/franchisee/profile', profileData);
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  // Get user address
  getAddress: async () => {
    try {
      const response = await api.get('/franchisee/address');
      return response.data;
    } catch (error) {
      throw error;
    }
  }
};

export default {
  authService,
  dashboardService,
  cartService,
  orderService,
  catalogService,
  profileService
};