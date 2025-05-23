// services/PushNotificationService.js
import messaging from '@react-native-firebase/messaging';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Platform, Alert } from 'react-native';

class PushNotificationService {
  constructor() {
    this.messageListener = null;
  }

  // Initialize push notifications
  async initialize() {
    try {
      // Request permission (iOS)
      await this.requestPermission();
      
      // Get FCM token
      const token = await this.getFCMToken();
      if (token) {
        await this.sendTokenToServer(token);
      }

      // Set up listeners
      this.setupListeners();
      
      // Handle initial notification (app opened from notification)
      const initialNotification = await messaging().getInitialNotification();
      if (initialNotification) {
        this.handleNotificationOpen(initialNotification);
      }
    } catch (error) {
      console.error('Push notification initialization error:', error);
    }
  }

  // Request permission for notifications (iOS)
  async requestPermission() {
    if (Platform.OS === 'ios') {
      const authStatus = await messaging().requestPermission();
      const enabled =
        authStatus === messaging.AuthorizationStatus.AUTHORIZED ||
        authStatus === messaging.AuthorizationStatus.PROVISIONAL;

      if (!enabled) {
        console.log('Push notification permission denied');
      }
    }
  }

  // Get FCM token
  async getFCMToken() {
    try {
      const token = await messaging().getToken();
      console.log('FCM Token:', token);
      
      // Save token locally
      await AsyncStorage.setItem('fcm_token', token);
      
      return token;
    } catch (error) {
      console.error('Error getting FCM token:', error);
      return null;
    }
  }

  // Send token to your Laravel server
  async sendTokenToServer(token) {
    try {
      const authToken = await AsyncStorage.getItem('auth_token');
      
      const response = await fetch('https://your-laravel-api.com/api/profile/fcm-token', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${authToken}`,
        },
        body: JSON.stringify({
          fcm_token: token,
        }),
      });

      const data = await response.json();
      
      if (data.success) {
        console.log('FCM token sent to server successfully');
      } else {
        console.error('Failed to send FCM token to server:', data.message);
      }
    } catch (error) {
      console.error('Error sending FCM token to server:', error);
    }
  }

  // Set up notification listeners
  setupListeners() {
    // When app is in foreground
    this.messageListener = messaging().onMessage(async remoteMessage => {
      console.log('Foreground notification:', remoteMessage);
      this.displayLocalNotification(remoteMessage);
    });

    // When app is in background or quit state and notification is clicked
    messaging().onNotificationOpenedApp(remoteMessage => {
      console.log('Background notification clicked:', remoteMessage);
      this.handleNotificationOpen(remoteMessage);
    });

    // Token refresh listener
    messaging().onTokenRefresh(async token => {
      console.log('FCM token refreshed:', token);
      await AsyncStorage.setItem('fcm_token', token);
      await this.sendTokenToServer(token);
    });
  }

  // Display notification when app is in foreground
  displayLocalNotification(remoteMessage) {
    const { notification, data } = remoteMessage;
    
    // You can use a library like react-native-push-notification for local notifications
    // or simply show an in-app alert
    Alert.alert(
      notification.title,
      notification.body,
      [
        {
          text: 'View Order',
          onPress: () => this.handleNotificationOpen(remoteMessage),
        },
        {
          text: 'Dismiss',
          style: 'cancel',
        },
      ],
    );
  }

  // Handle notification tap
  handleNotificationOpen(remoteMessage) {
    const { data } = remoteMessage;
    
    if (data.notification_type === 'order_status_change') {
      // Navigate to order details screen
      this.navigateToOrderDetails(data.order_id);
    } else if (data.notification_type === 'invoice_ready') {
      // Navigate to invoice screen
      this.navigateToInvoice(data.order_id);
    }
  }

  // Navigation methods (implement based on your navigation setup)
  navigateToOrderDetails(orderId) {
    // Example with React Navigation
    // navigationRef.navigate('OrderDetails', { orderId: parseInt(orderId) });
    console.log('Navigate to order:', orderId);
  }

  navigateToInvoice(orderId) {
    // Example with React Navigation
    // navigationRef.navigate('Invoice', { orderId: parseInt(orderId) });
    console.log('Navigate to invoice:', orderId);
  }

  // Clean up listeners
  cleanup() {
    if (this.messageListener) {
      this.messageListener();
    }
  }

  // Remove token when user logs out
  async removeToken() {
    try {
      const authToken = await AsyncStorage.getItem('auth_token');
      
      await fetch('https://your-laravel-api.com/api/profile/fcm-token', {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${authToken}`,
        },
      });

      await AsyncStorage.removeItem('fcm_token');
    } catch (error) {
      console.error('Error removing FCM token:', error);
    }
  }
}

export default new PushNotificationService();