import React, { useState, useEffect } from 'react';
import { View, StyleSheet, TouchableOpacity, Text, StatusBar } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import FallbackIcon from './FallbackIcon';
import { logout as apiLogout } from '../services/api';

// HeaderBar component to be nested within FranchiseeLayout
const HeaderBar = ({ title, cartCount = 0, onLogout }) => {
  const navigation = useNavigation();
  
  return (
    <View style={styles.header}>
      {/* Cart icon on left - now using dynamic cartCount */}
      <TouchableOpacity
        style={styles.iconButton}
        onPress={() => navigation.navigate('Cart')}
      >
        <View style={styles.cartContainer}>
          <FallbackIcon name="shoppingcart" iconType="AntDesign" size={24} color="#fff" />
          {/* Only show badge if cart has items */}
          {cartCount > 0 && (
            <View style={styles.badge}>
              <Text style={styles.badgeText}>
                {cartCount > 99 ? '99+' : cartCount}
              </Text>
            </View>
          )}
        </View>
      </TouchableOpacity>
      
      {/* Title in center */}
      <Text style={styles.title}>{title || 'Dashboard'}</Text>
      
      {/* Logout icon on right */}
      <View style={styles.rightContainer}>
        <TouchableOpacity
          style={styles.iconButton}
          onPress={onLogout}
        >
          {/* Simple logout icon that definitely exists */}
          <FallbackIcon name="logout" iconType="MaterialIcons" size={24} color="#fff" />
        </TouchableOpacity>
      </View>
    </View>
  );
};

// Modified FranchiseeLayout with nested HeaderBar
const FranchiseeLayout = ({ title, children, cartCount = 0 }) => {
  const navigation = useNavigation();
  const [showWelcome, setShowWelcome] = useState(true);
  const [userData, setUserData] = useState(null);
  const [showMenu, setShowMenu] = useState(false);

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
    
    // Set initial welcome banner state
    const checkWelcomeState = async () => {
      try {
        const welcomeState = await AsyncStorage.getItem('welcomeBannerClosed');
        if (welcomeState === 'true') {
          setShowWelcome(false);
        }
      } catch (error) {
        console.error('Error checking welcome banner state:', error);
      }
    };
    
    checkWelcomeState();
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

  const toggleMenu = () => {
    setShowMenu(!showMenu);
  };

  return (
    <View style={styles.wrapper}>
      {/* Set status bar color to match header */}
      <StatusBar barStyle="light-content" backgroundColor="#0066cc" />
      
      {/* Header with HeaderBar component */}
      <View style={styles.headerContainer}>
        <HeaderBar 
          title={title} 
          cartCount={cartCount} 
          onLogout={handleLogout} 
        />
        
        {/* Down arrow centered at bottom edge of header */}
        <TouchableOpacity 
          style={styles.arrowContainer}
          onPress={toggleMenu}
        >
          <View style={styles.arrowBackground}>
            <FallbackIcon 
              name={showMenu ? "up" : "down"} 
              iconType="AntDesign" 
              size={20} 
              color="#fff" 
            />
          </View>
        </TouchableOpacity>
      </View>
      
      {/* Slide-down menu */}
      {showMenu && (
        <View style={styles.slideMenu}>
          <View style={styles.menuGrid}>
            <TouchableOpacity style={styles.menuItem} onPress={() => navigation.navigate('Profile')}>
              <FallbackIcon name="user" iconType="AntDesign" size={24} color="#fff" />
              <Text style={styles.menuText}>Profile</Text>
            </TouchableOpacity>
            
            <TouchableOpacity style={styles.menuItem} onPress={() => navigation.navigate('Orders')}>
              <FallbackIcon name="inbox" iconType="AntDesign" size={24} color="#fff" />
              <Text style={styles.menuText}>Orders</Text>
            </TouchableOpacity>
            
            <TouchableOpacity style={styles.menuItem} onPress={() => navigation.navigate('Catalog')}>
              <FallbackIcon name="appstore-o" iconType="AntDesign" size={24} color="#fff" />
              <Text style={styles.menuText}>Catalog</Text>
            </TouchableOpacity>
            
            <TouchableOpacity style={styles.menuItem} onPress={() => navigation.navigate('Dashboard')}>
              <FallbackIcon name="dashboard" iconType="MaterialIcons" size={24} color="#fff" />
              <Text style={styles.menuText}>Dashboard</Text>
            </TouchableOpacity>
          </View>
        </View>
      )}

      {/* Content with conditional welcome banner */}
      <View style={styles.content}>
        {/* Either welcome banner or spacer */}
        {showWelcome ? (
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
              onPress={async () => {
                setShowWelcome(false);
                // Save state to AsyncStorage
                try {
                  await AsyncStorage.setItem('welcomeBannerClosed', 'true');
                } catch (error) {
                  console.error('Error saving welcome banner state:', error);
                }
              }}
            >
              <FallbackIcon name="close" iconType="AntDesign" size={20} color="#888" />
            </TouchableOpacity>
          </View>
        ) : (
          <View style={styles.spacer} />
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
  headerContainer: {
    backgroundColor: '#0066cc',
    position: 'relative',
    paddingBottom: 12, // Added space at bottom for the arrow
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#0066cc',
    paddingTop: 70, // Increased top padding to lower elements further
    paddingBottom: 30, // Increased bottom padding to lower the content
    paddingHorizontal: 15,
    justifyContent: 'space-between',
  },
  arrowContainer: {
    position: 'absolute',
    bottom: -10, // Position the arrow to overlap the edge
    left: '50%', // Center the arrow
    marginLeft: -15, // Offset for the arrow width to center it
    zIndex: 10, // Ensure arrow is on top
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
    backgroundColor: '#005cb8', // Slightly darker than header
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
    paddingRight: 25, // Space for close button
  },
  spacer: {
    height: 8, // Space between header and content when welcome banner is closed
    marginTop: 8,
    marginBottom: 8, // Keep consistent spacing below
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