/**
 * Authentication Service for token management and refresh
 */
import AsyncStorage from '@react-native-async-storage/async-storage';
import { sessionEventEmitter } from '../components/FranchiseeLayout';
import { BASE_URL } from './api';

// Helper function to decode JWT without dependencies
export const decodeJWT = (token) => {
  try {
    const base64Url = token.split('.')[1];
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    
    // For React Native environment
    try {
      // React Native environment: use Buffer
      const { Buffer } = require('buffer');
      const decodedData = Buffer.from(base64, 'base64').toString('utf8');
      return JSON.parse(decodedData);
    } catch (e) {
      // Web environment: use atob
      console.error('Error using Buffer, trying atob:', e);
      const jsonPayload = decodeURIComponent(
        atob(base64)
          .split('')
          .map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
          .join('')
      );
      return JSON.parse(jsonPayload);
    }
  } catch (error) {
    console.error('JWT decode error:', error);
    return null;
  }
};

// Helper function for detecting if token is expired or about to expire
export const isTokenExpiredOrExpiring = (token, bufferSeconds = 300) => {
  try {
    if (!token) return true;
    
    const decoded = decodeJWT(token);
    if (!decoded || !decoded.exp) return true;
    
    // Check if token is expired or will expire within buffer (default 5 minutes)
    const currentTime = Math.floor(Date.now() / 1000);
    return decoded.exp <= (currentTime + bufferSeconds);
  } catch (error) {
    console.error('Token expiration check error:', error);
    return true; // Assume expired on error
  }
};

// Token refresh mechanism
let tokenRefreshInProgress = false;

/**
 * Refreshes the authentication token before it expires
 * @returns {Promise<string|null>} New token or null if refresh failed
 */
export const refreshToken = async () => {
  // Prevent multiple concurrent refresh attempts
  if (tokenRefreshInProgress) {
    console.log('🔄 Token refresh already in progress, skipping this request');
    return null;
  }
  
  try {
    tokenRefreshInProgress = true;
    console.log('🔄 Attempting to refresh authentication token');
    
    const oldToken = await AsyncStorage.getItem('userToken');
    if (!oldToken) {
      console.error('⛔ No token found for refresh');
      throw new Error('No token found');
    }
    
    // Add retry logic for token refresh attempts
    let retryCount = 0;
    const maxRetries = 2;
    let response = null;
    let data = null;
    
    while (retryCount <= maxRetries) {
      try {
        console.log(`🔄 Token refresh attempt ${retryCount + 1} of ${maxRetries + 1}`);
        
        // Make token refresh API call
        response = await fetch(`${BASE_URL}/auth/refresh`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${oldToken}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          // Shorter timeout for faster retry
          timeout: 5000
        });
        
        // Try to parse the response as JSON
        try {
          const responseText = await response.text();
          if (responseText) {
            data = JSON.parse(responseText);
          } else {
            data = { success: false, message: 'Empty response received' };
          }
        } catch (parseError) {
          console.error('❌ Error parsing refresh response:', parseError);
          data = { success: false, message: 'Invalid response format' };
        }
        
        // If successful, we can break the retry loop
        if (response.ok && data.success && data.token) {
          break;
        }
        
        // If not successful, log and retry
        console.warn(`❌ Token refresh attempt ${retryCount + 1} failed: ${data.message || 'Unknown error'}`);
        retryCount++;
        
        // Wait before retrying (exponential backoff)
        if (retryCount <= maxRetries) {
          const backoffMs = Math.min(1000 * Math.pow(2, retryCount), 5000);
          console.log(`⏱️ Waiting ${backoffMs}ms before retry ${retryCount + 1}`);
          await new Promise(resolve => setTimeout(resolve, backoffMs));
        }
      } catch (fetchError) {
        console.error(`❌ Fetch error during refresh attempt ${retryCount + 1}:`, fetchError);
        retryCount++;
        
        // Wait before retrying
        if (retryCount <= maxRetries) {
          const backoffMs = Math.min(1000 * Math.pow(2, retryCount), 5000);
          await new Promise(resolve => setTimeout(resolve, backoffMs));
        }
      }
    }
    
    // Process the final response
    if (response && response.ok && data && data.success && data.token) {
      console.log('✅ Token refreshed successfully');
      
      // Store the new token
      await AsyncStorage.setItem('userToken', data.token);
      
      // Broadcast token refresh event
      sessionEventEmitter.emit('tokenRefreshed', data.token);
      
      return data.token;
    } else {
      const errorMessage = data?.message || 'Unknown error';
      console.error('❌ Token refresh failed after all attempts:', errorMessage);
      throw new Error(errorMessage || 'Token refresh failed');
    }
  } catch (error) {
    console.error('🔥 Token refresh error:', error);
    return null;
  } finally {
    tokenRefreshInProgress = false;
  }
};

/**
 * Sets up a token refresh interval to maintain session
 * @param {number} intervalMinutes - Minutes between refresh checks
 * @returns {Function} A cleanup function to clear the interval
 */
export const setupTokenRefreshInterval = (intervalMinutes = 5) => {
  console.log(`⏱️ Setting up token refresh interval every ${intervalMinutes} minutes`);
  
  // Convert minutes to milliseconds
  const intervalMs = intervalMinutes * 60 * 1000;
  
  // Set up interval for token refresh
  const intervalId = setInterval(async () => {
    console.log('⏱️ Running scheduled token refresh check');
    
    try {
      const token = await AsyncStorage.getItem('userToken');
      if (!token) {
        console.log('⛔ No token found during scheduled refresh check');
        return;
      }
      
      // Check if token is about to expire (within 5 minutes)
      if (isTokenExpiredOrExpiring(token, 300)) {
        console.log('🔄 Token is expiring soon, initiating refresh');
        await refreshToken();
      } else {
        console.log('✅ Token still valid, no refresh needed');
      }
    } catch (error) {
      console.error('❌ Error during scheduled token check:', error);
    }
  }, intervalMs);
  
  // Return a cleanup function
  return () => {
    console.log('🧹 Cleaning up token refresh interval');
    clearInterval(intervalId);
  };
};

/**
 * Check if we have a valid token stored
 * @returns {Promise<boolean>} Whether we have a valid non-expired token
 */
export const hasValidToken = async () => {
  try {
    const token = await AsyncStorage.getItem('userToken');
    if (!token) return false;
    
    // Check if token is expired
    return !isTokenExpiredOrExpiring(token);
  } catch (error) {
    console.error('Error checking token validity:', error);
    return false;
  }
};

/**
 * Get current token with automatic refresh if needed
 * @returns {Promise<string|null>} Current token or null if unavailable
 */
export const getAuthToken = async () => {
  try {
    const token = await AsyncStorage.getItem('userToken');
    if (!token) return null;
    
    // If token is close to expiring, refresh it
    if (isTokenExpiredOrExpiring(token, 300)) {
      console.log('🔄 Token expiring soon, refreshing before use');
      const newToken = await refreshToken();
      return newToken || token; // Fall back to old token if refresh failed
    }
    
    return token;
  } catch (error) {
    console.error('Error getting auth token:', error);
    return null;
  }
};

/**
 * Get authorization headers for API requests
 * @returns {Promise<object>} Headers object with authorization
 */
export const getAuthHeaders = async () => {
  const token = await getAuthToken();
  
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
};

/**
 * Create axios request interceptor to handle authentication
 * @param {object} axiosInstance - Axios instance to add interceptor to
 */
export const setupAxiosInterceptors = (axiosInstance) => {
  // Request interceptor to add fresh token
  axiosInstance.interceptors.request.use(
    async (config) => {
      try {
        // Get a valid token (this will refresh if needed)
        const token = await getAuthToken();
        
        if (token) {
          config.headers['Authorization'] = `Bearer ${token}`;
        }
        
        return config;
      } catch (error) {
        console.error('Error in request interceptor:', error);
        return Promise.reject(error);
      }
    },
    (error) => {
      return Promise.reject(error);
    }
  );
  
  // Response interceptor to handle 401 errors
  axiosInstance.interceptors.response.use(
    (response) => response,
    async (error) => {
      if (error.response && error.response.status === 401) {
        console.log('🔐 401 Unauthorized response in axios interceptor');
        
        // Emit session expiring event
        sessionEventEmitter.emit('sessionExpiring');
      }
      
      return Promise.reject(error);
    }
  );
};