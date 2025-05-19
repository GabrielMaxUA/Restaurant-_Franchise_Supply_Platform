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
import { logout as apiLogout, getCart, BASE_URL } from '../services/api';
import EventEmitter from 'eventemitter3';
import { Alert } from 'react-native';

export const cartEventEmitter = new EventEmitter();

// Add a new event emitter for session handling
export const sessionEventEmitter = new EventEmitter();

// Global API response interceptor for handling 401 errors consistently
export const handleApiResponse = async (response, navigation) => {
  // Check if it's a fetch Response object or an axios response object
  const isFetchResponse = response && typeof response.headers.get === 'function';
  const isAxiosResponse = response && response.headers && typeof response.headers['content-type'] === 'string';
  
  if (isFetchResponse) {
    // Handle HTML responses (often login redirects) for fetch API
    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('text/html')) {
      console.error('Received HTML response instead of JSON (likely session expired)');
      sessionEventEmitter.emit('sessionExpiring');
      throw new Error('Authentication error: Your session has expired.');
    }
    
    // Handle 401 Unauthorized responses for fetch API
    if (response.status === 401) {
      console.error('401 Unauthorized response received');
      sessionEventEmitter.emit('sessionExpiring');
      throw new Error('Authentication error: Your session has expired.');
    }
  } else if (isAxiosResponse) {
    // Handle HTML responses for axios
    const contentType = response.headers['content-type'];
    if (contentType && contentType.includes('text/html')) {
      console.error('Received HTML response instead of JSON (likely session expired)');
      sessionEventEmitter.emit('sessionExpiring');
      throw new Error('Authentication error: Your session has expired.');
    }
    
    // Handle 401 Unauthorized responses for axios
    if (response.status === 401) {
      console.error('401 Unauthorized response received');
      sessionEventEmitter.emit('sessionExpiring');
      throw new Error('Authentication error: Your session has expired.');
    }
  } else {
    console.warn('Unknown response type in handleApiResponse');
  }
  
  return response;
};

// Create a global session state management
let isSessionExpiring = false;
let isSessionExpired = false;
let tokenRefreshInProgress = false;

// Helper function to decode JWT without dependencies
const decodeJWT = (token) => {
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
const isTokenExpiredOrExpiring = (token, bufferSeconds = 300) => {
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

// Function to refresh token
const refreshToken = async (navigation) => {
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
      
      // Reset the inactivity timer since we have a fresh token
      clearTimeout(inactivityTimerRef.current);
      inactivityTimerRef.current = setTimeout(() => {
        handleSessionExpiration();
      }, 15 * 60 * 1000);
      
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

const HeaderBar = ({ title, cartCount = 0, onLogout }) => {
  const navigation = useNavigation();
  
  console.log('🧮 HeaderBar rendering with cartCount:', cartCount);
  
  return (
    <View style={styles.header}>
      <TouchableOpacity 
        style={styles.iconButton} 
        onPress={() => {
          console.log('🛒 Cart icon clicked, navigating to Cart screen');
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
        <TouchableOpacity style={styles.iconButton} onPress={onLogout}>
          <FallbackIcon name="logout" iconType="MaterialIcons" size={24} color="#fff" />
        </TouchableOpacity>
      </View>
    </View>
  );
};

const FranchiseeLayout = ({ title, children }) => {
  const navigation = useNavigation();
  const [showWelcome, setShowWelcome] = useState(true);
  const [userData, setUserData] = useState(null);
  const [showMenu, setShowMenu] = useState(false);
  const [cartCount, setCartCount] = useState(0);
  const inactivityTimerRef = useRef(null);
  const lastSessionCheckRef = useRef(Date.now());

  const fetchCartCount = useCallback(async () => {
    // Don't fetch cart if session is expired
    if (isSessionExpired) {
      console.log('👮 fetchCartCount: Session is expired, skipping cart fetch');
      return;
    }
    
    console.log('🚀 fetchCartCount: Fetching cart count data');
    try {
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

  // Function to handle session expiration
  const handleSessionExpiration = async () => {
    // Only proceed if we haven't already shown the alert
    if (isSessionExpiring || isSessionExpired) {
      return;
    }
    
    console.log('⏰ User inactive - auto logout');
    isSessionExpiring = true;
    
    // Notify all components about session expiration
    sessionEventEmitter.emit('sessionExpiring');
    
    Alert.alert(
      'Session Expired',
      'Your session has expired. Please log in again to continue.',
      [
        {
          text: 'OK',
          onPress: async () => {
            // Set global session expired flag
            isSessionExpired = true;
            
            // Clear all timers
            clearTimeout(inactivityTimerRef.current);
            
            // Perform logout
            try {
              await apiLogout();
            } catch (e) {
              console.log('Server logout failed during auto-logout');
            }
            
            // Clear user data
            await AsyncStorage.multiRemove(['userToken', 'userData']);
            
            // Reset to login screen
            navigation.reset({
              index: 0,
              routes: [{ name: 'Login' }],
            });
            
            // Reset status flags after navigation
            setTimeout(() => {
              isSessionExpiring = false;
              isSessionExpired = false;
            }, 1000);
          }
        }
      ],
      { cancelable: false }
    );
  };

  const startInactivityTimer = () => {
    // Don't set timer if session is expired or expiring
    if (isSessionExpiring || isSessionExpired) {
      console.log('⏱️ Not starting inactivity timer: session is already expiring or expired');
      return;
    }
    
    // Clear any existing timers first
    if (inactivityTimerRef.current) {
      console.log('⏱️ Clearing existing inactivity timer');
      clearTimeout(inactivityTimerRef.current);
    }
    
    // Set a longer timeout for inactivity (30 minutes)
    const inactivityTimeoutMs = 30 * 60 * 1000; // 30 minutes
    console.log(`⏱️ Starting new inactivity timer: ${inactivityTimeoutMs/60000} minutes`);
    
    inactivityTimerRef.current = setTimeout(() => {
      console.log('⏰ Inactivity timer expired - starting session expiration process');
      handleSessionExpiration();
    }, inactivityTimeoutMs); 
  };

  const handleUserActivity = async () => {
    // Don't restart timer if session is expired or expiring
    if (isSessionExpiring || isSessionExpired) {
      return;
    }
    
    // Throttle activity tracking to avoid excessive processing
    const now = Date.now();
    if (now - lastSessionCheckRef.current > 10000) { // Only update every 10 seconds
      console.log('👆 User activity detected - updating last activity timestamp');
      lastSessionCheckRef.current = now;
      
      // Reset the inactivity timer
      startInactivityTimer();
      
      // Check token expiration once per minute (less frequent than activity tracking)
      if (now % (60 * 1000) < 10000) {
        try {
          const token = await AsyncStorage.getItem('userToken');
          if (!token) {
            console.log('⚠️ No token found during activity check');
            return;
          }
          
          // ALWAYS refresh token proactively for active users rather than waiting for it to expire
          // This will prevent the 15-minute auto-logout for active users
          console.log('🔄 Proactively refreshing token for active user');
          
          const newToken = await refreshToken(navigation);
          if (newToken) {
            console.log('✅ Token refreshed successfully during user activity');
            // Since we have a fresh token, we can reset the inactivity timer
            startInactivityTimer();
          } else {
            console.warn('⚠️ Token refresh failed during user activity');
            // Check if the current token is expired or about to expire
            if (token && isTokenExpiredOrExpiring(token, 60)) { // 1 minute buffer
              console.error('🔥 Token refresh failed and token is expiring soon');
              // Only show the expiration alert if token is actually about to expire
              handleSessionExpiration();
            }
          }
        } catch (error) {
          console.error('Token check error during activity:', error);
        }
      }
    }
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
        // If welcomeBannerClosed is null/undefined or not 'true', we should show the welcome banner
        const shouldShow = closed !== 'true';
        console.log(`${shouldShow ? '🎉 Will show' : '🚫 Will not show'} welcome banner`);
        setShowWelcome(shouldShow);
      } catch (error) {
        console.error('Error checking welcome banner status:', error);
        // Default to showing the banner if there's an error
        setShowWelcome(true);
      }
    };

    // Function to check token validity and refresh if needed
    const checkAndRefreshToken = async () => {
      try {
        const token = await AsyncStorage.getItem('userToken');
        
        // If no token, redirect to login
        if (!token) {
          console.log('⚠️ No token found, redirecting to login');
          navigation.reset({
            index: 0,
            routes: [{ name: 'Login' }],
          });
          return;
        }
        
        // Check if token is expired or about to expire
        if (isTokenExpiredOrExpiring(token)) {
          console.log('⚠️ Token expired or expiring soon, attempting refresh');
          const newToken = await refreshToken(navigation);
          
          // If refresh failed, handle session expiration
          if (!newToken) {
            console.log('⚠️ Token refresh failed, user needs to login again');
            handleSessionExpiration();
          } else {
            console.log('✅ Token successfully refreshed');
          }
        } else {
          console.log('✅ Token is valid');
        }
      } catch (error) {
        console.error('Token validation error:', error);
      }
    };

    // Run initial setup
    fetchUser();
    fetchCartCount();
    checkWelcome();
    startInactivityTimer();
    checkAndRefreshToken();
    
    // Set up periodic token refresh checks (every 5 minutes)
    const tokenCheckInterval = setInterval(() => {
      checkAndRefreshToken();
    }, 5 * 60 * 1000);

    // Listen for app state changes
    const subscription = AppState.addEventListener('change', nextState => {
      if (nextState === 'active') {
        // When app becomes active again, check token and inactivity
        const now = Date.now();
        const timeInBackground = now - lastSessionCheckRef.current;
        
        console.log(`🔍 App returned to foreground after ${Math.round(timeInBackground/1000)} seconds in background`);
        
        // Extend the background time allowance to avoid unnecessary logouts
        // Only expire session after 30 minutes of inactivity instead of 15
        if (timeInBackground > 30 * 60 * 1000) {
          console.log('⏰ App was in background for more than 30 minutes - expiring session');
          handleSessionExpiration();
        } else {
          // Otherwise proactively refresh the token and reset activity timer
          console.log('🔄 App returned from background - refreshing token');
          checkAndRefreshToken();
          
          // Treat returning to the app as a user activity
          handleUserActivity();
        }
        
        lastSessionCheckRef.current = now;
      } else if (nextState === 'background') {
        // Store the timestamp when app goes to background
        console.log('📱 App moved to background - saving timestamp');
        lastSessionCheckRef.current = Date.now();
      }
    });

    const updateCart = (count) => {
      console.log(`🛒 FranchiseeLayout cartEventEmitter received update with count: ${count}`);
      setCartCount(count);
    };
    cartEventEmitter.on('cartUpdated', updateCart);
    
    // Listen for logout events from other components
    const handleGlobalSessionExpiration = () => {
      clearTimeout(inactivityTimerRef.current);
    };
    sessionEventEmitter.on('sessionExpiring', handleGlobalSessionExpiration);

    return () => {
      clearTimeout(inactivityTimerRef.current);
      clearInterval(tokenCheckInterval);
      cartEventEmitter.off('cartUpdated', updateCart);
      sessionEventEmitter.off('sessionExpiring', handleGlobalSessionExpiration);
      subscription.remove();
    };
  }, [fetchCartCount, navigation]);

  useFocusEffect(
    useCallback(() => {
      // Don't fetch cart if session is expired
      if (!isSessionExpired) {
        fetchCartCount();
      }
      return () => {};
    }, [fetchCartCount])
  );

  const handleLogout = async () => {
    try {
      await apiLogout();
    } catch (e) {
      console.log('Server logout failed');
    }
    await AsyncStorage.multiRemove(['userToken', 'userData']);
    navigation.reset({ index: 0, routes: [{ name: 'Login' }] });
  };

  const toggleMenu = () => setShowMenu(prev => !prev);

  return (
    <View style={styles.wrapper} onTouchStart={handleUserActivity}>
      <StatusBar barStyle="light-content" backgroundColor="#0066cc" />
      
      {/* Header section */}
      <View style={styles.headerContainer}>
        <HeaderBar title={title} cartCount={cartCount} onLogout={handleLogout} />
        <TouchableOpacity style={styles.arrowContainer} onPress={toggleMenu}>
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
                handleUserActivity();
                navigation.navigate('Profile');
              }}
            >
              <FallbackIcon name="user" iconType="AntDesign" size={24} color="#fff" />
              <Text style={styles.menuText}>Profile</Text>
            </TouchableOpacity>
            <TouchableOpacity 
              style={styles.menuItem}
              onPress={() => {
                handleUserActivity();
                navigation.navigate('Orders');
              }}
            >
              <FallbackIcon name="inbox" iconType="AntDesign" size={24} color="#fff" />
              <Text style={styles.menuText}>Orders</Text>
            </TouchableOpacity>
            <TouchableOpacity 
              style={styles.menuItem}
              onPress={() => {
                handleUserActivity();
                navigation.navigate('Catalog');
              }}
            >
              <FallbackIcon name="appstore-o" iconType="AntDesign" size={24} color="#fff" />
              <Text style={styles.menuText}>Catalog</Text>
            </TouchableOpacity>
            <TouchableOpacity 
              style={styles.menuItem}
              onPress={() => {
                handleUserActivity();
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
              handleUserActivity();
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