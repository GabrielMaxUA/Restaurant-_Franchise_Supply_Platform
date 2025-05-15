// contexts/AuthContext.js - Authentication context

import React, { createContext, useReducer, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { authService, cartService } from '../api';

// Create context
export const AuthContext = createContext();

// Define reducer
const authReducer = (state, action) => {
  switch (action.type) {
    case 'RESTORE_TOKEN':
      return {
        ...state,
        userToken: action.token,
        userProfile: action.userProfile,
        isLoading: false,
      };
    case 'SIGN_IN':
      return {
        ...state,
        isSignout: false,
        userToken: action.token,
        userProfile: action.userProfile,
      };
    case 'SIGN_OUT':
      return {
        ...state,
        isSignout: true,
        userToken: null,
        userProfile: null,
      };
    case 'UPDATE_PROFILE':
      return {
        ...state,
        userProfile: action.userProfile,
      };
    case 'UPDATE_CART':
      return {
        ...state,
        cartCount: action.count,
      };
    default:
      return state;
  }
};

// Provider component
export const AuthProvider = ({ children }) => {
  // Initial state
  const [state, dispatch] = useReducer(authReducer, {
    isLoading: true,
    isSignout: false,
    userToken: null,
    userProfile: null,
    cartCount: 0,
  });
  
  // Bootstrap async
  useEffect(() => {
    const bootstrapAsync = async () => {
      try {
        // Try to get token from storage
        const userToken = await AsyncStorage.getItem('userToken');
        let userProfile = null;
        
        try {
          const profileData = await AsyncStorage.getItem('userProfile');
          if (profileData) {
            userProfile = JSON.parse(profileData);
          }
        } catch (e) {
          console.log('Error parsing stored profile:', e);
        }
        
        if (userToken) {
          try {
            // Validate token by getting user info
            const response = await authService.getCurrentUser();
            
            if (response && response.success) {
              // Save user profile
              userProfile = response.user;
              await AsyncStorage.setItem('userProfile', JSON.stringify(userProfile));
              
              // Restore token and profile
              dispatch({ 
                type: 'RESTORE_TOKEN', 
                token: userToken, 
                userProfile 
              });
              
              // Also get cart count if possible
              try {
                const cartResponse = await cartService.getCart();
                if (cartResponse && cartResponse.success) {
                  const itemsCount = cartResponse.items_count || 0;
                  dispatch({ type: 'UPDATE_CART', count: itemsCount });
                }
              } catch (cartError) {
                console.log('Error fetching cart:', cartError);
              }
            } else {
              // Clear invalid token
              await AsyncStorage.removeItem('userToken');
              await AsyncStorage.removeItem('userProfile');
              dispatch({ type: 'RESTORE_TOKEN', token: null, userProfile: null });
            }
          } catch (error) {
            // Token validation failed
            console.log('Token validation error:', error);
            await AsyncStorage.removeItem('userToken');
            await AsyncStorage.removeItem('userProfile');
            dispatch({ type: 'RESTORE_TOKEN', token: null, userProfile: null });
          }
        } else {
          // No token found
          dispatch({ type: 'RESTORE_TOKEN', token: null, userProfile: null });
        }
      } catch (e) {
        console.log('Bootstrap error:', e);
        dispatch({ type: 'RESTORE_TOKEN', token: null, userProfile: null });
      }
    };
    
    bootstrapAsync();
  }, []);
  
  // Auth actions
  const authActions = {
    signIn: async (credentials) => {
      try {
        const { email, password } = credentials;
        const response = await authService.login(email, password);
        
        if (response && response.success) {
          // Store token and profile
          await AsyncStorage.setItem('userToken', response.token);
          await AsyncStorage.setItem('userProfile', JSON.stringify(response.user));
          
          // Update state
          dispatch({ 
            type: 'SIGN_IN', 
            token: response.token, 
            userProfile: response.user 
          });
          
          // Also fetch cart count
          try {
            const cartResponse = await cartService.getCart();
            if (cartResponse && cartResponse.success) {
              const count = cartResponse.items_count || 0;
              dispatch({ type: 'UPDATE_CART', count });
            }
          } catch (error) {
            console.log('Cart count fetch error:', error);
          }
          
          return true;
        } else {
          throw new Error(response.error || 'Login failed');
        }
      } catch (error) {
        console.log('Sign in error:', error);
        throw error;
      }
    },
    
    signOut: async () => {
      try {
        // Attempt to logout from server
        await authService.logout();
      } catch (error) {
        console.log('Logout error:', error);
      } finally {
        // Always clear local storage
        await AsyncStorage.removeItem('userToken');
        await AsyncStorage.removeItem('userProfile');
        dispatch({ type: 'SIGN_OUT' });
      }
    },
    
    updateProfile: async (profile) => {
      await AsyncStorage.setItem('userProfile', JSON.stringify(profile));
      dispatch({ type: 'UPDATE_PROFILE', userProfile: profile });
    },
    
    updateCartCount: (count) => {
      dispatch({ type: 'UPDATE_CART', count });
    }
  };
  
  return (
    <AuthContext.Provider value={{ state, ...authActions }}>
      {children}
    </AuthContext.Provider>
  );
};