import React, { useState, useEffect } from 'react';
import { View, StyleSheet, TouchableOpacity, Text, StatusBar } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import Ionicons from 'react-native-vector-icons/Ionicons';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import AntDesign from 'react-native-vector-icons/AntDesign';
import { logout as apiLogout } from '../services/api';
import FallbackIcon from './FallbackIcon';

const FranchiseeLayout = ({ title, children }) => {
  const navigation = useNavigation();
  const [showWelcome, setShowWelcome] = useState(true);
  const [userData, setUserData] = useState(null);

  // Get user data from AsyncStorage
  useEffect(() => {
    const getUserData = async () => {
      try {
        const userDataString = await AsyncStorage.getItem('userData');
        if (userDataString) {
          const data = JSON.parse(userDataString);
          setUserData(data);
        }
      } catch (error) {
        console.error('Error getting user data:', error);
      }
    };
    
    getUserData();
  }, []);

  const handleLogout = async () => {
    try {
      console.log('🚪 Logging out from header...');
      
      // Use the API logout function first
      const result = await apiLogout();
      console.log('🚪 Logout API result:', result);
      
      // Clear token
      await AsyncStorage.removeItem('userToken');
      await AsyncStorage.removeItem('userData');
      
      // Navigate to login - handle with try/catch to avoid errors
      try {
        if (navigation && navigation.reset) {
          navigation.reset({
            index: 0,
            routes: [{ name: 'Login' }],
          });
        } else {
          console.error('⚠️ Navigation missing or navigation.reset not available');
        }
      } catch (e) {
        console.error('⚠️ Error during navigation reset:', e);
      }
    } catch (error) {
      console.error('❌ Error logging out:', error);
      
      // Even if the API call fails, still try to force logout
      try {
        await AsyncStorage.removeItem('userToken');
        navigation.reset({
          index: 0,
          routes: [{ name: 'Login' }],
        });
      } catch (e) {
        console.error('❌ Critical error during logout:', e);
      }
    }
  };

  return (
    <View style={styles.wrapper}>
      {/* Set status bar color to match header */}
      <StatusBar barStyle="light-content" backgroundColor="#0066cc" />
      
      {/* Header with cart on left, title in center, menu on right */}
      <View style={styles.header}>
        {/* Cart icon on left */}
        <TouchableOpacity 
          style={styles.iconButton}
          onPress={() => navigation.navigate('Cart')}
        >
          <View style={styles.cartContainer}>
            <FallbackIcon name="shoppingcart" iconType="AntDesign" size={24} color="#fff" />
            <View style={styles.badge}>
              <Text style={styles.badgeText}>3</Text>
            </View>
          </View>
        </TouchableOpacity>
        
        {/* Title in center */}
        <Text style={styles.title}>{title || 'Franchisee Portal'}</Text>
        
        {/* Menu/user icon on right */}
        <View style={styles.rightContainer}>
          <TouchableOpacity
            style={styles.iconButton}
            onPress={() => {
              // In a real app, this would open a drawer or show options
              alert('User menu options');
            }}
          >
            <FallbackIcon name="menufold" iconType="AntDesign" size={24} color="#fff" />
          </TouchableOpacity>
        </View>
      </View>

      {/* Content with welcome banner when applicable */}
      <View style={styles.content}>
        {/* Welcome banner - only show if showWelcome is true */}
        {showWelcome && (
          <View style={styles.welcomeBanner}>
            <View style={styles.welcomeContent}>
              <View style={styles.welcomeTitleContainer}>
                <FallbackIcon name="star" iconType="AntDesign" size={18} color="#28a745" />
                <Text style={styles.welcomeTitle}>
                  Welcome back, {userData?.username || userData?.profile?.contact_name || 'Franchisee'}!
                </Text>
              </View>
              <Text style={styles.welcomeMessage}>Nice to see you back!</Text>
              <Text style={styles.welcomeSubtext}>
                Check the dashboard for more insights about your restaurant supply status.
              </Text>
            </View>
            
            {/* Close button */}
            <TouchableOpacity 
              style={styles.closeButton} 
              onPress={() => setShowWelcome(false)}
            >
              <FallbackIcon name="close" iconType="AntDesign" size={20} color="#888" />
            </TouchableOpacity>
          </View>
        )}
        
        {/* Main content */}
        {children}
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  wrapper: { 
    flex: 1, 
    backgroundColor: '#f5f5f5' 
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#0066cc',
    paddingTop: 50, // Add extra padding for status bar on iOS
    paddingBottom: 15,
    paddingHorizontal: 15,
    justifyContent: 'space-between',
  },
  title: { 
    color: '#fff', 
    fontSize: 20, 
    fontWeight: 'bold',
    textAlign: 'center',
    flex: 1, // Take up available space
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
    paddingRight: 25, // Space for close button if needed
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
