/**
 * API Diagnostics Utility
 * A helpful tool for diagnosing and testing API connections in development
 */

import AsyncStorage from '@react-native-async-storage/async-storage';
import { Platform } from 'react-native';
import { BASE_URL } from '../services/api';

/**
 * Test all critical API endpoints and return diagnostics information
 * @returns {Promise<Object>} Diagnostics results
 */
export const runApiDiagnostics = async () => {
  console.log('Running API diagnostics...');
  const results = {
    timestamp: new Date().toISOString(),
    baseUrl: BASE_URL,
    auth: { status: 'pending' },
    endpoints: {},
    device: {
      platform: Platform.OS,
      version: Platform.Version,
    },
    networkInfo: {},
  };

  try {
    // Get network info
    const NetInfo = require('@react-native-community/netinfo');
    const networkState = await NetInfo.fetch();
    results.networkInfo = {
      isConnected: networkState.isConnected,
      type: networkState.type,
      details: networkState.details,
    };

    // Check if authenticated
    const token = await AsyncStorage.getItem('userToken');
    if (token) {
      results.auth.status = 'authenticated';
      results.auth.tokenPreview = `${token.substring(0, 10)}...`;
    } else {
      results.auth.status = 'unauthenticated';
    }

    // Define critical endpoints to test
    const criticalEndpoints = [
      { name: 'auth', path: '/auth/login', method: 'OPTIONS' }, // Using OPTIONS to avoid authentication
      { name: 'profile', path: '/auth/me', method: 'GET', requiresAuth: true },
      { name: 'catalog', path: '/franchisee/catalog', method: 'GET', requiresAuth: true },
      { name: 'orders', path: '/franchisee/orders', method: 'GET', requiresAuth: true },
    ];

    // Test each endpoint
    for (const endpoint of criticalEndpoints) {
      if (endpoint.requiresAuth && results.auth.status !== 'authenticated') {
        results.endpoints[endpoint.name] = {
          status: 'skipped',
          reason: 'Authentication required but user is not logged in',
        };
        continue;
      }

      try {
        const headers = {};
        if (endpoint.requiresAuth) {
          headers['Authorization'] = `Bearer ${token}`;
        }

        // Perform fetch with timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 5000);

        const response = await fetch(`${BASE_URL}${endpoint.path}`, {
          method: endpoint.method,
          headers,
          signal: controller.signal,
        });

        clearTimeout(timeoutId);

        // Check response
        const contentType = response.headers.get('content-type');
        let responseData = 'No content';
        
        if (contentType && contentType.includes('application/json')) {
          try {
            responseData = await response.json();
          } catch (e) {
            responseData = 'Invalid JSON';
          }
        } else if (response.status !== 204) { // No Content status
          try {
            const text = await response.text();
            responseData = text.substring(0, 100) + (text.length > 100 ? '...' : '');
          } catch (e) {
            responseData = 'Failed to retrieve response body';
          }
        }

        results.endpoints[endpoint.name] = {
          status: response.status,
          ok: response.ok,
          contentType,
          responsePreview: typeof responseData === 'string' ? responseData : JSON.stringify(responseData).substring(0, 100) + '...',
        };
      } catch (error) {
        results.endpoints[endpoint.name] = {
          status: 'error',
          error: error.message,
        };
      }
    }

    return results;
  } catch (error) {
    console.error('API diagnostics error:', error);
    return {
      ...results,
      status: 'error',
      error: error.message,
    };
  }
};

/**
 * Generate a detailed report for API diagnostics
 * @param {Object} results - Results from runApiDiagnostics
 * @returns {string} - Formatted report text
 */
export const generateDiagnosticsReport = (results) => {
  const report = [
    '===== API DIAGNOSTICS REPORT =====',
    `Timestamp: ${results.timestamp}`,
    `Base URL: ${results.baseUrl}`,
    '',
    '--- AUTHENTICATION ---',
    `Status: ${results.auth.status}`,
    results.auth.tokenPreview ? `Token: ${results.auth.tokenPreview}` : '',
    '',
    '--- NETWORK INFO ---',
    `Connected: ${results.networkInfo.isConnected ? 'Yes' : 'No'}`,
    `Type: ${results.networkInfo.type || 'Unknown'}`,
    `Details: ${JSON.stringify(results.networkInfo.details || {})}`,
    '',
    '--- ENDPOINTS ---',
  ];

  Object.keys(results.endpoints).forEach(endpointName => {
    const endpoint = results.endpoints[endpointName];
    report.push(`${endpointName}:`);
    report.push(`  Status: ${endpoint.status}`);
    
    if (endpoint.ok !== undefined) {
      report.push(`  Success: ${endpoint.ok ? 'Yes' : 'No'}`);
    }
    
    if (endpoint.contentType) {
      report.push(`  Content-Type: ${endpoint.contentType}`);
    }
    
    if (endpoint.responsePreview) {
      report.push(`  Response: ${endpoint.responsePreview}`);
    }
    
    if (endpoint.error) {
      report.push(`  Error: ${endpoint.error}`);
    }
    
    if (endpoint.reason) {
      report.push(`  Reason: ${endpoint.reason}`);
    }
    
    report.push('');
  });

  report.push('================================');
  return report.join('\n');
};

export default {
  runApiDiagnostics,
  generateDiagnosticsReport,
};