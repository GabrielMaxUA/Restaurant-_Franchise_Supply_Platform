import React, { useState, useEffect, useCallback, useRef } from 'react';
import {
  View,
  StyleSheet,
  TouchableOpacity,
  Text,
  StatusBar,
  AppState,
  ScrollView,
  FlatList,
  Platform
} from 'react-native';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import FallbackIcon from './icon/FallbackIcon';
import { logout as apiLogout, getCart} from '../services/api';
import EventEmitter from 'eventemitter3';
import { Alert } from 'react-native';
import { BASE_URL } from '../services/axiosInstance';
export const cartEventEmitter = new EventEmitter();
export const sessionEventEmitter = new EventEmitter();

// ============================================================================
// GLOBAL ACTIVITY & TOKEN MANAGEMENT SYSTEM
// ============================================================================

// Activity tracking variables
let lastUserActivity = Date.now();
let lastActivityLog = 0; // Track when we last logged activity to prevent spam
const INACTIVITY_TIMEOUT = 15 * 60 * 1000; // 15 minutes in milliseconds
const ACTIVITY_LOG_THROTTLE = 5000; // Only log activity every 5 seconds to prevent spam
let inactivityLogoutTimer = null;
let tokenRefreshInProgress = false;
let logoutInProgress = false;

// User activity tracking function with throttling
const updateUserActivity = (source = 'unknown') => {
  const now = Date.now();
  const timeSinceLastActivity = now - lastUserActivity;
  const wasInactive = timeSinceLastActivity > INACTIVITY_TIMEOUT;
  const shouldLog = (now - lastActivityLog) > ACTIVITY_LOG_THROTTLE || wasInactive;
  
  lastUserActivity = now;
  
  // Only log if enough time has passed or user was inactive
  if (shouldLog) {
    lastActivityLog = now;
    console.log('🔄 User activity detected:', new Date(now).toLocaleTimeString());
    console.log(`📍 Activity source: ${source}`);
    console.log(`📊 USER STATE: ${wasInactive ? 'INACTIVE → ACTIVE' : 'ACTIVE (continued)'}`);
    console.log(`⏱️ Time since last activity: ${(timeSinceLastActivity / 1000).toFixed(1)}s`);
  }
  
  // Always clear and restart timer, but only log if we should
  if (inactivityLogoutTimer) {
    clearTimeout(inactivityLogoutTimer);
    if (shouldLog) {
      console.log('⏰ Cleared previous inactivity timer');
    }
  }
  
  // Start new inactivity timer
  scheduleInactivityLogout(shouldLog);
};

// Schedule inactivity logout
const scheduleInactivityLogout = (shouldLog = true) => {
  if (logoutInProgress) {
    if (shouldLog) console.log('🚫 Logout already in progress, skipping inactivity timer');
    return;
  }
  
  if (shouldLog) {
    const timeoutMinutes = INACTIVITY_TIMEOUT / (60 * 1000);
    console.log(`⏰ INACTIVITY TIMER: Scheduling logout in ${timeoutMinutes} minutes`);
    console.log(`📅 LOGOUT SCHEDULED FOR: ${new Date(Date.now() + INACTIVITY_TIMEOUT).toLocaleTimeString()}`);
  }
  
  inactivityLogoutTimer = setTimeout(() => {
    console.log('🕒 ===============================================');
    console.log('🕒 15 MINUTES OF INACTIVITY DETECTED - LOGGING OUT');
    console.log('🕒 ===============================================');
    handleInactivityLogout();
  }, INACTIVITY_TIMEOUT);
};

// Handle inactivity logout
const handleInactivityLogout = async () => {
  if (logoutInProgress) {
    console.log('🚫 Logout already in progress');
    return;
  }
  
  logoutInProgress = true;
  
  try {
    // Clear any timers
    if (inactivityLogoutTimer) {
      clearTimeout(inactivityLogoutTimer);
      inactivityLogoutTimer = null;
    }
    
    console.log('🕒 Performing inactivity logout');
    
    // Try to logout from server
    try {
      await apiLogout();
      console.log('✅ Server logout successful');
    } catch (e) {
      console.log('⚠️ Server logout failed, continuing with local logout:', e.message);
    }
    
    // Clear local storage
    await AsyncStorage.multiRemove(['userToken', 'userData']);
    
    // Emit session expiration event for other components
    sessionEventEmitter.emit('sessionExpiring');
    
    // Show inactivity alert and navigate to login
    Alert.alert(
      'Session Expired',
      'You have been automatically logged out due to 15 minutes of inactivity.',
      [
        {
          text: 'OK',
          onPress: () => {
            // Use the global navigation reference
            if (globalNavigationRef.current) {
              globalNavigationRef.current.reset({
                index: 0,
                routes: [{ name: 'Login' }],
              });
            }
          }
        }
      ],
      { cancelable: false }
    );
    
  } catch (error) {
    console.error('❌ Error during inactivity logout:', error);
    // Still navigate to login even if there was an error
    if (globalNavigationRef.current) {
      globalNavigationRef.current.reset({
        index: 0,
        routes: [{ name: 'Login' }],
      });
    }
  } finally {
    logoutInProgress = false;
  }
};

// Global navigation reference for logout function
let globalNavigationRef = { current: null };

// Helper function to decode JWT without dependencies
const decodeJWT = (token) => {
  try {
    const base64Url = token.split('.')[1];
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    
    try {
      const { Buffer } = require('buffer');
      const decodedData = Buffer.from(base64, 'base64').toString('utf8');
      return JSON.parse(decodedData);
    } catch (e) {
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

// Simplified token refresh function
const refreshToken = async () => {
  if (tokenRefreshInProgress) {
    console.log('🔄 Token refresh already in progress, skipping this request');
    return null;
  }
  
  if (logoutInProgress) {
    console.log('🚫 Logout in progress, skipping token refresh');
    return null;
  }
  
  try {
    tokenRefreshInProgress = true;
    console.log('🔄 ===============================================');
    console.log('🔄 STARTING TOKEN REFRESH PROCESS');
    console.log('🔄 ===============================================');
    
    const oldToken = await AsyncStorage.getItem('userToken');
    if (!oldToken) {
      console.error('⛔ No token found for refresh');
      throw new Error('No token found');
    }
    
    // Check current token info
    const decoded = decodeJWT(oldToken);
    if (decoded && decoded.exp) {
      const currentTime = Math.floor(Date.now() / 1000);
      const timeRemaining = decoded.exp - currentTime;
      const minutesRemaining = Math.floor(timeRemaining / 60);
      const secondsRemaining = timeRemaining % 60;
      
      console.log(`🕐 CURRENT TOKEN INFO:`);
      console.log(`   ⏱️ Time remaining: ${minutesRemaining}m ${secondsRemaining}s`);
      console.log(`   📅 Expires at: ${new Date(decoded.exp * 1000).toLocaleString()}`);
    }
    
    // Check if user is still active (within last 15 minutes)
    const timeSinceActivity = Date.now() - lastUserActivity;
    const minutesSinceActivity = Math.floor(timeSinceActivity / (60 * 1000));
    const secondsSinceActivity = Math.floor((timeSinceActivity % (60 * 1000)) / 1000);
    const userStillActive = timeSinceActivity < INACTIVITY_TIMEOUT;
    
    console.log(`👤 USER ACTIVITY CHECK:`);
    console.log(`   ⏱️ Time since last activity: ${minutesSinceActivity}m ${secondsSinceActivity}s`);
    console.log(`   📊 User state: ${userStillActive ? '✅ ACTIVE' : '❌ INACTIVE'}`);
    console.log(`   🔄 Refresh decision: ${userStillActive ? 'PROCEED' : 'ABORT (logout instead)'}`);
    
    if (!userStillActive) {
      console.log('🕒 User has been inactive too long, not refreshing token');
      console.log('🕒 Initiating inactivity logout instead...');
      handleInactivityLogout();
      return null;
    }
    
    // Make token refresh API call
    console.log(`🌐 Making API call to: ${BASE_URL}/auth/refresh`);
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
    
    try {
      const response = await fetch(`${BASE_URL}/auth/refresh`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${oldToken}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        signal: controller.signal
      });
      
      clearTimeout(timeoutId);
      
      const responseText = await response.text();
      let data;
      
      try {
        data = JSON.parse(responseText);
      } catch (jsonError) {
        console.error('❌ Response is not valid JSON:', responseText.substring(0, 100));
        throw new Error('Invalid JSON response from server');
      }
      
      console.log(`📡 API RESPONSE:`);
      console.log(`   🔢 Status: ${response.status}`);
      console.log(`   ✅ Success: ${data?.success || false}`);
      console.log(`   💬 Message: ${data?.message || 'No message'}`);
      
      if (response.ok && data && data.success && data.token) {
        // Check new token info
        const newDecoded = decodeJWT(data.token);
        if (newDecoded && newDecoded.exp) {
          const currentTime = Math.floor(Date.now() / 1000);
          const newTimeRemaining = newDecoded.exp - currentTime;
          const newMinutesRemaining = Math.floor(newTimeRemaining / 60);
          const newSecondsRemaining = newTimeRemaining % 60;
          
          console.log('✅ TOKEN REFRESH SUCCESSFUL!');
          console.log(`🆕 NEW TOKEN INFO:`);
          console.log(`   ⏱️ Time remaining: ${newMinutesRemaining}m ${newSecondsRemaining}s`);
          console.log(`   📅 Expires at: ${new Date(newDecoded.exp * 1000).toLocaleString()}`);
        }
        
        // Store the new token
        await AsyncStorage.setItem('userToken', data.token);
        
        // Update user activity since we successfully refreshed
        updateUserActivity('token_refresh');
        
        // Broadcast token refresh event
        sessionEventEmitter.emit('tokenRefreshed', data.token);
        
        return data.token;
      } else {
        const errorMessage = data?.message || `HTTP ${response.status}`;
        console.error('❌ TOKEN REFRESH FAILED:', errorMessage);
        
        // If token refresh failed with 401, likely due to inactivity on server side
        if (response.status === 401) {
          console.log('🕒 Token refresh failed with 401, likely due to server-side inactivity');
          console.log('🕒 Initiating inactivity logout...');
          handleInactivityLogout();
        }
        
        throw new Error(errorMessage);
      }
      
    } catch (fetchError) {
      clearTimeout(timeoutId);
      
      if (fetchError.name === 'AbortError') {
        console.error('❌ Token refresh request timed out (10s)');
        throw new Error('Token refresh request timed out');
      }
      
      console.error('❌ Network error during token refresh:', fetchError.message);
      throw fetchError;
    }
    
  } catch (error) {
    console.error('🔥 TOKEN REFRESH ERROR:', error.message);
    return null;
  } finally {
    tokenRefreshInProgress = false;
    console.log('🔄 ===============================================');
    console.log('🔄 TOKEN REFRESH PROCESS COMPLETED');
    console.log('🔄 ===============================================');
  }
};

// Check and refresh token if needed
const checkAndRefreshToken = async () => {
  if (logoutInProgress) {
    console.log('🚫 Logout in progress, skipping token check');
    return;
  }
  
  try {
    console.log('🔍 ===============================================');
    console.log('🔍 STARTING TOKEN VALIDATION CHECK');
    console.log('🔍 ===============================================');
    
    const token = await AsyncStorage.getItem('userToken');
    if (!token) {
      console.log('⚠️ No token found, redirecting to login');
      if (globalNavigationRef.current) {
        globalNavigationRef.current.reset({
          index: 0,
          routes: [{ name: 'Login' }],
        });
      }
      return;
    }
    
    // Decode token to check expiration
    const decoded = decodeJWT(token);
    if (!decoded || !decoded.exp) {
      console.log('⚠️ Invalid token format, redirecting to login');
      handleInactivityLogout();
      return;
    }
    
    // Calculate seconds until expiration
    const currentTime = Math.floor(Date.now() / 1000);
    const expiresInSeconds = decoded.exp - currentTime;
    const minutesRemaining = Math.floor(expiresInSeconds / 60);
    const secondsRemaining = expiresInSeconds % 60;
    
    // Check user activity status
    const timeSinceActivity = Date.now() - lastUserActivity;
    const minutesSinceActivity = Math.floor(timeSinceActivity / (60 * 1000));
    const secondsSinceActivityRemainder = Math.floor((timeSinceActivity % (60 * 1000)) / 1000);
    const userIsActive = timeSinceActivity < INACTIVITY_TIMEOUT;
    
    console.log(`🕐 TOKEN STATUS:`);
    console.log(`   ⏱️ Time remaining: ${minutesRemaining}m ${secondsRemaining}s`);
    console.log(`   📅 Expires at: ${new Date(decoded.exp * 1000).toLocaleString()}`);
    console.log(`   🔄 Issued at: ${new Date(decoded.iat * 1000).toLocaleString()}`);
    
    console.log(`👤 USER STATUS:`);
    console.log(`   ⏱️ Time since activity: ${minutesSinceActivity}m ${secondsSinceActivityRemainder}s`);
    console.log(`   📊 State: ${userIsActive ? '✅ ACTIVE' : '❌ INACTIVE'}`);
    console.log(`   📅 Last activity: ${new Date(lastUserActivity).toLocaleString()}`);
    
    // If token is expired or expiring very soon (less than 1 minute), handle expiration
    if (expiresInSeconds <= 60) {
      console.log('⚠️ ==========================================');
      console.log('⚠️ TOKEN EXPIRED OR EXPIRING VERY SOON (<1min)');
      console.log('⚠️ ==========================================');
      handleInactivityLogout();
      return;
    }
    
    // If token will expire in the next 10 minutes, refresh it proactively
    if (expiresInSeconds <= 600) {
      console.log('🔄 ==========================================');
      console.log('🔄 TOKEN EXPIRING SOON (<10min) - REFRESHING');
      console.log('🔄 ==========================================');
      const newToken = await refreshToken();
      
      if (!newToken) {
        // If refresh failed and token is expiring soon, logout
        if (expiresInSeconds <= 120) {
          console.log('⚠️ Token refresh failed and current token expiring very soon');
          handleInactivityLogout();
        }
      }
    } else {
      console.log('✅ ==========================================');
      console.log('✅ TOKEN IS VALID - NO ACTION NEEDED');
      console.log('✅ ==========================================');
    }
  } catch (error) {
    console.error('❌ Token validation error:', error);
  }
};

// Global API response interceptor for handling 401 errors consistently
export const handleApiResponse = async (response) => {
  const isFetchResponse = response && typeof response.headers?.get === 'function';
  const isAxiosResponse = response && response.headers && typeof response.headers['content-type'] === 'string';
  
  if (isFetchResponse) {
    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('text/html')) {
      console.error('Received HTML response instead of JSON (likely session expired)');
      sessionEventEmitter.emit('sessionExpiring');
      throw new Error('Authentication error: Your session has expired.');
    }
    
    if (response.status === 401) {
      console.error('401 Unauthorized response received');
      sessionEventEmitter.emit('sessionExpiring');
      throw new Error('Authentication error: Your session has expired.');
    }
  } else if (isAxiosResponse) {
    const contentType = response.headers['content-type'];
    if (contentType && contentType.includes('text/html')) {
      console.error('Received HTML response instead of JSON (likely session expired)');
      sessionEventEmitter.emit('sessionExpiring');
      throw new Error('Authentication error: Your session has expired.');
    }
    
    if (response.status === 401) {
      console.error('401 Unauthorized response received');
      sessionEventEmitter.emit('sessionExpiring');
      throw new Error('Authentication error: Your session has expired.');
    }
  }
  
  return response;
};

// ============================================================================
// HEADER BAR COMPONENT
// ============================================================================

const HeaderBar = ({ title, cartCount = 0, onLogout }) => {
  const navigation = useNavigation();
  
  console.log('🧮 HeaderBar rendering with cartCount:', cartCount);
  
  return (
    <View style={styles.header}>
      <TouchableOpacity 
        style={styles.iconButton} 
        onPress={() => {
          console.log('🛒 Cart icon clicked, navigating to Cart screen');
          updateUserActivity('cart_icon_click'); // Register user activity
          navigation.navigate('Cart');
        }}
      >
        <View style={styles.cartContainer}>
          <FallbackIcon name="shoppingcart" iconType="AntDesign" size={24} color="#fff" />
          {cartCount > 0 ? (
            <View style={styles.badge}>
              <Text style={styles.badgeText}>{cartCount > 99 ? '99+' : cartCount}</Text>
            </View>
          ) : null}
        </View>
      </TouchableOpacity>
      <Text style={styles.title}>{title || 'Dashboard'}</Text>
      <View style={styles.rightContainer}>
        <TouchableOpacity 
          style={styles.iconButton} 
          onPress={() => {
            updateUserActivity('logout_button_click'); // Register user activity
            onLogout();
          }}
        >
          <FallbackIcon name="logout" iconType="MaterialIcons" size={24} color="#fff" />
        </TouchableOpacity>
      </View>
    </View>
  );
};

// ============================================================================
// MAIN FRANCHISEE LAYOUT COMPONENT
// ============================================================================

const FranchiseeLayout = ({ title, children }) => {
  const navigation = useNavigation();
  const [showWelcome, setShowWelcome] = useState(true);
  const [userData, setUserData] = useState(null);
  const [showMenu, setShowMenu] = useState(false);
  const [cartCount, setCartCount] = useState(0);
  const [isUserActive, setIsUserActive] = useState(true);
  
  // Set global navigation reference
  globalNavigationRef.current = navigation;

  const fetchCartCount = useCallback(async () => {
    console.log('🚀 fetchCartCount: Fetching cart count data');
    try {
      updateUserActivity('cart_fetch'); // Register activity when fetching cart
      
      const token = await AsyncStorage.getItem('userToken');
      if (!token) {
        console.log('⛔ fetchCartCount: No token available');
        return;
      }

      const response = await getCart(token);
      console.log('📊 fetchCartCount: Cart response:', response);
      
      if (response.success && typeof response.items_count === 'number') {
        console.log(`✅ fetchCartCount: Setting cart count to ${response.items_count} from items_count`);
        setCartCount(response.items_count);
      } else if (response.success && response.cart?.items_count) {
        console.log(`✅ fetchCartCount: Setting cart count to ${response.cart.items_count} from cart.items_count`);
        setCartCount(response.cart.items_count);
      } else if (response.success && Array.isArray(response.cart_items)) {
        console.log(`✅ fetchCartCount: Setting cart count to ${response.cart_items.length} from cart_items.length`);
        setCartCount(response.cart_items.length);
      } else {
        console.log('⚠️ fetchCartCount: Unexpected cart response:', response);
      }
    } catch (error) {
      console.error('❌ fetchCartCount: Cart fetch error:', error);
    }
  }, []);

  // Manual logout function
  const handleLogout = async () => {
    if (logoutInProgress) {
      console.log('🚫 Logout already in progress');
      return;
    }
    
    logoutInProgress = true;
    
    try {
      // Clear inactivity timer since user is manually logging out
      if (inactivityLogoutTimer) {
        clearTimeout(inactivityLogoutTimer);
        inactivityLogoutTimer = null;
      }
      
      try {
        await apiLogout();
      } catch (e) {
        console.log('Server logout failed');
      }
      
      await AsyncStorage.multiRemove(['userToken', 'userData']);
      navigation.reset({ index: 0, routes: [{ name: 'Login' }] });
    } finally {
      logoutInProgress = false;
    }
  };

  const toggleMenu = () => {
    updateUserActivity('menu_toggle'); // Register activity when toggling menu
    setShowMenu(prev => !prev);
  };

  useEffect(() => {
    const fetchUser = async () => {
      const data = await AsyncStorage.getItem('userData');
      if (data) setUserData(JSON.parse(data));
    };

    const checkWelcome = async () => {
      try {
        console.log('🔍 Checking welcome banner status');
        const closed = await AsyncStorage.getItem('welcomeBannerClosed');
        const shouldShow = closed !== 'true';
        console.log(`${shouldShow ? '🎉 Will show' : '🚫 Will not show'} welcome banner`);
        setShowWelcome(shouldShow);
      } catch (error) {
        console.error('Error checking welcome banner status:', error);
        setShowWelcome(true);
      }
    };

    // Run initial setup
    fetchUser();
    fetchCartCount();
    checkWelcome();
    
    // Register initial user activity and start inactivity timer
    updateUserActivity('app_initialization');
    
    // Initial token check
    checkAndRefreshToken();
    
    // Set up periodic token checks (every 2 minutes)
    const backgroundTokenCheckInterval = setInterval(() => {
      const timeSinceActivity = Date.now() - lastUserActivity;
      const userActive = timeSinceActivity < INACTIVITY_TIMEOUT;
      const minutesSinceActivity = Math.floor(timeSinceActivity / (60 * 1000));
      const secondsSinceActivity = Math.floor((timeSinceActivity % (60 * 1000)) / 1000);
      
      console.log('⏰ ==========================================');
      console.log('⏰ BACKGROUND TOKEN CHECK');
      console.log('⏰ ==========================================');
      console.log(`👤 User state: ${userActive ? '✅ ACTIVE' : '❌ INACTIVE'}`);
      console.log(`⏱️ Time since activity: ${minutesSinceActivity}m ${secondsSinceActivity}s`);
      console.log(`🔄 Action: ${userActive ? 'Checking token' : 'Waiting for activity'}`);
      
      setIsUserActive(userActive);
      
      if (userActive) {
        console.log('✅ User still active - proceeding with token check');
        checkAndRefreshToken();
      } else {
        console.log('❌ User inactive - skipping token check (inactivity timer will handle logout)');
      }
      
      console.log('⏰ ==========================================');
    }, 2 * 60 * 1000); // Check every 2 minutes

    // Listen for app state changes
    const subscription = AppState.addEventListener('change', nextState => {
      if (nextState === 'active') {
        console.log('📱 ==========================================');
        console.log('📱 APP RETURNED TO FOREGROUND');
        console.log('📱 ==========================================');
        console.log('🔄 Registering user activity and checking token');
        updateUserActivity('app_foreground'); // This will reset the inactivity timer
        setIsUserActive(true);
        checkAndRefreshToken();
        fetchCartCount();
      } else if (nextState === 'background') {
        const timeSinceActivity = Date.now() - lastUserActivity;
        const minutesSinceActivity = Math.floor(timeSinceActivity / (60 * 1000));
        const secondsSinceActivity = Math.floor((timeSinceActivity % (60 * 1000)) / 1000);
        
        console.log('📱 ==========================================');
        console.log('📱 APP WENT TO BACKGROUND');
        console.log('📱 ==========================================');
        console.log(`⏱️ Time since last activity: ${minutesSinceActivity}m ${secondsSinceActivity}s`);
        console.log('⏰ Inactivity timer continues running in background');
      }
    });

    const updateCart = (count) => {
      console.log('🛒 Cart update event received - registering user activity');
      updateUserActivity('cart_update_event'); // Register activity when cart is updated
      if (count !== undefined) {
        console.log(`🛒 FranchiseeLayout cartEventEmitter received update with count: ${count}`);
        setCartCount(count);
      } else {
        console.log('🛒 FranchiseeLayout refreshing cart count from API');
        fetchCartCount();
      }
    };
    cartEventEmitter.on('cartUpdated', updateCart);
    
    // Listen for token refresh events
    const handleTokenRefreshed = () => {
      console.log('✅ Token refreshed event received - updating UI');
      updateUserActivity('token_refresh_event'); // Register activity on token refresh
      fetchCartCount();
    };
    sessionEventEmitter.on('tokenRefreshed', handleTokenRefreshed);

    // Listen for user activity events from other components
    const handleUserActivity = () => {
      console.log('📡 External user activity event received');
      updateUserActivity('external_event'); // This will reset the inactivity timer
      setIsUserActive(true);
    };
    sessionEventEmitter.on('userActivity', handleUserActivity);

    // Listen for session expiring events
    const handleSessionExpiring = () => {
      console.log('🕒 Session expiring event received');
      handleInactivityLogout();
    };
    sessionEventEmitter.on('sessionExpiring', handleSessionExpiring);

    return () => {
      // Clear all timers
      if (inactivityLogoutTimer) {
        clearTimeout(inactivityLogoutTimer);
      }
      clearInterval(backgroundTokenCheckInterval);
      
      // Remove event listeners
      cartEventEmitter.off('cartUpdated', updateCart);
      sessionEventEmitter.off('tokenRefreshed', handleTokenRefreshed);
      sessionEventEmitter.off('userActivity', handleUserActivity);
      sessionEventEmitter.off('sessionExpiring', handleSessionExpiring);
      subscription.remove();
    };
  }, [fetchCartCount, navigation]);

  useFocusEffect(
    useCallback(() => {
      console.log('🎯 Screen focused - registering user activity and fetching cart');
      updateUserActivity('screen_focus'); // Register activity when screen comes into focus
      fetchCartCount();
      return () => {};
    }, [fetchCartCount])
  );

  return (
    <View 
      style={styles.wrapper}
      onTouchStart={() => updateUserActivity('touch_start')}
      onMoveShouldSetResponder={() => {
        updateUserActivity('touch_move');
        return false; // Don't consume the touch event
      }}
    >
      <StatusBar barStyle="light-content" backgroundColor="#0066cc" />
      
      {/* Header section */}
      <View style={styles.headerContainer}>
        <HeaderBar title={title} cartCount={cartCount} onLogout={handleLogout} />
        <TouchableOpacity 
          style={styles.arrowContainer} 
          onPress={toggleMenu}
        >
          <View style={styles.arrowBackground}>
            <FallbackIcon name={showMenu ? "up" : "down"} iconType="AntDesign" size={20} color="#fff" />
          </View>
        </TouchableOpacity>
      </View>

      {/* Menu */}
      {showMenu && (
        <View style={styles.slideMenu}>
          <View style={styles.menuGrid}>
            <TouchableOpacity 
              style={styles.menuItem} 
              onPress={() => {
                updateUserActivity('profile_navigation'); // Register activity on navigation
                navigation.navigate('Profile');
              }}
            >
              <FallbackIcon name="user" iconType="AntDesign" size={24} color="#fff" />
              <Text style={styles.menuText}>Profile</Text>
            </TouchableOpacity>
            <TouchableOpacity 
              style={styles.menuItem}
              onPress={() => {
                updateUserActivity('orders_navigation'); // Register activity on navigation
                navigation.navigate('OrdersScreen');
              }}
            >
              <FallbackIcon name="inbox" iconType="AntDesign" size={24} color="#fff" />
              <Text style={styles.menuText}>Orders</Text>
            </TouchableOpacity>
            <TouchableOpacity 
              style={styles.menuItem}
              onPress={() => {
                updateUserActivity('catalog_navigation'); // Register activity on navigation
                navigation.navigate('Catalog');
              }}
            >
              <FallbackIcon name="appstore-o" iconType="AntDesign" size={24} color="#fff" />
              <Text style={styles.menuText}>Catalog</Text>
            </TouchableOpacity>
            <TouchableOpacity 
              style={styles.menuItem}
              onPress={() => {
                updateUserActivity('dashboard_navigation'); // Register activity on navigation
                navigation.navigate('Dashboard');
              }}
            >
              <FallbackIcon name="dashboard" iconType="MaterialIcons" size={24} color="#fff" />
              <Text style={styles.menuText}>Dashboard</Text>
            </TouchableOpacity>
          </View>
        </View>
      )}

      {/* Welcome banner */}
      {showWelcome ? (
        <View style={styles.welcomeBanner}>
          <View style={styles.welcomeContent}>
            <View style={styles.welcomeTitleContainer}>
              <FallbackIcon name="star" iconType="AntDesign" size={18} color="#28a745" />
              <Text style={styles.welcomeTitle}>
                Welcome, {userData?.username || userData?.profile?.contact_name || 'Franchisee'}!
              </Text>
            </View>
            <Text style={styles.welcomeMessage}>We're glad to see you today!</Text>
            <Text style={styles.welcomeSubtext}>Check the dashboard for your latest updates and insights.</Text>
          </View>
          <TouchableOpacity
            style={styles.closeButton}
            onPress={async () => {
              updateUserActivity('welcome_banner_close'); // Register activity on banner close
              setShowWelcome(false);
              await AsyncStorage.setItem('welcomeBannerClosed', 'true');
            }}
          >
            <FallbackIcon name="close" iconType="AntDesign" size={20} color="#888" />
          </TouchableOpacity>
        </View>
      ) : <View style={styles.spacer} />}
      
      {/* Content area */}
      <View style={styles.content} pointerEvents="box-none">
        {children}
      </View>
    </View>
  );
};

// ============================================================================
// STYLES
// ============================================================================

const styles = StyleSheet.create({
  wrapper: { 
    flex: 1, 
    backgroundColor: '#f5f5f5',
  },
  headerContainer: {
    backgroundColor: '#0066cc',
    position: 'relative',
    paddingBottom: 12,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#0066cc',
    paddingTop: 70,
    paddingBottom: 30,
    paddingHorizontal: 15,
    justifyContent: 'space-between',
  },
  arrowContainer: {
    position: 'absolute',
    bottom: -10,
    left: '50%',
    marginLeft: -15,
    zIndex: 10,
  },
  arrowBackground: {
    backgroundColor: '#0066cc',
    width: 30,
    height: 30,
    borderRadius: 15,
    alignItems: 'center',
    justifyContent: 'center',
  },
  slideMenu: {
    backgroundColor: '#005cb8',
    paddingVertical: 15,
    paddingHorizontal: 10,
  },
  menuGrid: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  menuItem: {
    alignItems: 'center',
    padding: 10,
  },
  menuText: {
    color: '#fff',
    marginTop: 5,
    fontSize: 12,
  },
  title: {
    color: '#fff',
    fontSize: 20,
    fontWeight: 'bold',
    textAlign: 'center',
    flex: 1,
  },
  content: {
    flex: 1,
    paddingHorizontal: 0,
    paddingVertical: 0,
  },
  iconButton: {
    padding: 8,
  },
  cartContainer: {
    position: 'relative',
  },
  badge: {
    position: 'absolute',
    top: -8,
    right: -8,
    backgroundColor: 'red',
    borderRadius: 10,
    height: 20,
    minWidth: 20,
    paddingHorizontal: 5,
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 10,
  },
  badgeText: {
    color: '#fff',
    fontSize: 10,
    fontWeight: 'bold',
    textAlign: 'center',
  },
  rightContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  welcomeBanner: {
    backgroundColor: '#e8f5e9',
    paddingVertical: 15,
    paddingHorizontal: 20,
    borderRadius: 8,
    marginHorizontal: 15,
    marginTop: 15,
    marginBottom: 15,
    borderLeftWidth: 3,
    borderLeftColor: '#28a745',
  },
  welcomeContent: {
    paddingRight: 25,
  },
  spacer: {
    height: 8,
    marginTop: 8,
    marginBottom: 8,
  },
  welcomeTitleContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  welcomeTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#212529',
    marginLeft: 5,
  },
  welcomeMessage: {
    fontSize: 14,
    color: '#212529',
    marginBottom: 8,
  },
  welcomeSubtext: {
    fontSize: 13,
    color: '#495057',
  },
  closeButton: {
    position: 'absolute',
    top: 10,
    right: 10,
    padding: 5,
  },
});

export default FranchiseeLayout;