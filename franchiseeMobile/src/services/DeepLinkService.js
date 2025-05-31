import { Linking } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import NavigationService from '../navigation/NavigationService';

class DeepLinkService {
  constructor() {
    this.pendingDeepLink = null;
  }

  // Initialize deep link handling
  async initialize() {
    // Handle deep link when app is already open
    Linking.addEventListener('url', this.handleDeepLink);

    // Handle deep link when app is launched from closed state
    const initialUrl = await Linking.getInitialURL();
    if (initialUrl) {
      this.handleDeepLink({ url: initialUrl });
    }
  }

  // Clean up listeners
  cleanup() {
    Linking.removeAllListeners('url');
  }

  // Handle incoming deep links
  handleDeepLink = async (event) => {
    const { url } = event;
    if (!url) return;

    console.log('Handling deep link:', url);

    // Parse the URL to extract the path and parameters
    const route = this.parseDeepLink(url);
    if (!route) return;

    // Check if user is authenticated
    const token = await AsyncStorage.getItem('token');
    
    if (!token) {
      // Store the deep link to navigate after login
      this.pendingDeepLink = route;
      NavigationService.navigate('Login');
    } else {
      // Navigate directly to the route
      this.navigateToRoute(route);
    }
  };

  // Parse deep link URL to extract route information
  parseDeepLink(url) {
    try {
      // Remove the scheme prefix
      let path = url.replace(/^restaurantfranchise:\/\//, '');
      path = path.replace(/^https?:\/\/[^\/]+\/app\//, '');

      // Parse different route patterns
      if (path.startsWith('order/')) {
        const orderId = path.replace('order/', '');
        return { 
          screen: 'OrderDetails', 
          params: { orderId } 
        };
      } else if (path.startsWith('product/')) {
        const productId = path.replace('product/', '');
        return { 
          screen: 'ProductDetail', 
          params: { productId } 
        };
      } else if (path === 'cart') {
        return { screen: 'Cart' };
      } else if (path === 'orders') {
        return { screen: 'OrdersScreen' };
      } else if (path === 'catalog') {
        return { screen: 'Catalog' };
      } else if (path === 'profile') {
        return { screen: 'Profile' };
      } else if (path === 'dashboard' || path === '') {
        return { screen: 'Dashboard' };
      }

      return null;
    } catch (error) {
      console.error('Error parsing deep link:', error);
      return null;
    }
  }

  // Navigate to the specified route
  navigateToRoute(route) {
    if (!route) return;

    const { screen, params } = route;
    
    // Add a small delay to ensure navigation is ready
    setTimeout(() => {
      if (params) {
        NavigationService.navigate(screen, params);
      } else {
        NavigationService.navigate(screen);
      }
    }, 100);
  }

  // Handle pending deep link after login
  async handlePendingDeepLink() {
    if (this.pendingDeepLink) {
      const route = this.pendingDeepLink;
      this.pendingDeepLink = null;
      this.navigateToRoute(route);
    }
  }

  // Get pending deep link (for checking after login)
  getPendingDeepLink() {
    return this.pendingDeepLink;
  }

  // Clear pending deep link
  clearPendingDeepLink() {
    this.pendingDeepLink = null;
  }
}

export default new DeepLinkService();