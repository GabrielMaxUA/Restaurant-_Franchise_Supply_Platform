import React, { useState, useEffect } from 'react';
import { View, StyleSheet, TouchableOpacity, Text, StatusBar } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import FallbackIcon from './icon/FallbackIcon';
import { logout as apiLogout } from '../services/api';
import EventEmitter from 'eventemitter3';

export const cartEventEmitter = new EventEmitter();

const HeaderBar = ({ title, cartCount = 0, onLogout }) => {
  const navigation = useNavigation();
  
  return (
    <View style={styles.header}>
      <TouchableOpacity style={styles.iconButton} onPress={() => navigation.navigate('Cart')}>
        <View style={styles.cartContainer}>
          <FallbackIcon name="shoppingcart" iconType="AntDesign" size={24} color="#fff" />
          {cartCount > 0 && (
            <View style={styles.badge}>
              <Text style={styles.badgeText}>{cartCount > 99 ? '99+' : cartCount}</Text>
            </View>
          )}
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

  useEffect(() => {
    const getUserData = async () => {
      try {
        const userDataString = await AsyncStorage.getItem('userData');
        if (userDataString) setUserData(JSON.parse(userDataString));
      } catch (error) {
        console.error('Error getting user data:', error);
      }
    };

    const getCartCount = async () => {
      try {
        const cartData = await AsyncStorage.getItem('cartData');
        if (cartData) {
          const parsed = JSON.parse(cartData);
          setCartCount(parsed.items_count || 0);
        }
      } catch (error) {
        console.error('Error getting cart count:', error);
      }
    };

    getUserData();
    getCartCount();

    const checkWelcomeState = async () => {
      try {
        const closed = await AsyncStorage.getItem('welcomeBannerClosed');
        if (closed === 'true') setShowWelcome(false);
      } catch (error) {
        console.error('Error checking welcome state:', error);
      }
    };

    checkWelcomeState();

    const updateCartCount = (count) => {
      setCartCount(count);
    };

    cartEventEmitter.on('cartUpdated', updateCartCount);

    return () => {
      cartEventEmitter.off('cartUpdated', updateCartCount);
    };
  }, []);

  const handleLogout = async () => {
    try {
      await apiLogout();
      await AsyncStorage.multiRemove(['userToken', 'userData']);
      navigation.reset({ index: 0, routes: [{ name: 'Login' }] });
    } catch (error) {
      console.error('Logout error:', error);
    }
  };

  const toggleMenu = () => setShowMenu(!showMenu);

  return (
    <View style={styles.wrapper}>
      <StatusBar barStyle="light-content" backgroundColor="#0066cc" />
      <View style={styles.headerContainer}>
        <HeaderBar title={title} cartCount={cartCount} onLogout={handleLogout} />
        <TouchableOpacity style={styles.arrowContainer} onPress={toggleMenu}>
          <View style={styles.arrowBackground}>
            <FallbackIcon name={showMenu ? "up" : "down"} iconType="AntDesign" size={20} color="#fff" />
          </View>
        </TouchableOpacity>
      </View>

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

      <View style={styles.content}>
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
              <Text style={styles.welcomeSubtext}>Check the dashboard for more insights.</Text>
            </View>
            <TouchableOpacity
              style={styles.closeButton}
              onPress={async () => {
                setShowWelcome(false);
                await AsyncStorage.setItem('welcomeBannerClosed', 'true');
              }}
            >
              <FallbackIcon name="close" iconType="AntDesign" size={20} color="#888" />
            </TouchableOpacity>
          </View>
        ) : (
          <View style={styles.spacer} />
        )}

        {children}
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  wrapper: { flex: 1, backgroundColor: '#f5f5f5' },
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
