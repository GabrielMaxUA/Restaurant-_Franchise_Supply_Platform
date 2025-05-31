# Restaurant Franchise Supply Platform - Mobile App

A React Native mobile application designed for restaurant franchise owners to manage their supply orders efficiently. The app provides a comprehensive e-commerce experience with real-time inventory management, order tracking, and seamless integration with the supply platform backend.

## Table of Contents
- [Features](#features)
- [Setup Guide](#setup-guide)
- [Environment Configuration](#environment-configuration)
- [Running the Application](#running-the-application)
- [Troubleshooting](#troubleshooting)

## Features

### 🔐 Authentication & Security
- **Secure Login System** - Email/password authentication with JWT token management
- **Session Management** - Automatic session timeout handling with renewal prompts
- **Deep Link Support** - Handle email links to navigate directly to specific screens (e.g., order details)
- **Password Management** - Secure password change functionality with validation requirements

### 📊 Dashboard & Analytics
- **Interactive Dashboard** - Quick overview of business metrics and activities
- **Visual Analytics** - Weekly/monthly spending charts with bar graph visualization
- **Key Metrics Display**:
  - Monthly spending with percentage change indicators
  - Pending orders count with trend analysis
  - Low stock items alerts
  - Incoming deliveries tracking
- **Recent Orders** - Quick view of latest orders with status indicators
- **Popular Products** - Most ordered items with quick add-to-cart functionality

### 🛍️ Product Catalog & Shopping
- **Grid View Catalog** - Browse all products with images and pricing
- **Real-time Search** - Find products by name or description instantly
- **Advanced Filtering**:
  - Category-based filtering
  - Favorites filter
  - Stock status filter
- **Sorting Options** - Sort by name (A-Z/Z-A), price (low/high), or popularity
- **Favorites Management** - Mark/unmark products as favorites for quick access
- **Stock Indicators** - Visual badges for in-stock, low stock, or out of stock items
- **Product Variants** - Support for multiple sizes, types, or configurations

### 📱 Product Details
- **Comprehensive Product View** - Large images with detailed descriptions
- **Variant Selection** - Easy switching between product variants
- **Smart Quantity Management** - Increment/decrement with real-time inventory validation
- **Live Inventory Updates** - Shows exact available stock
- **Intelligent Cart Addition** - Prevents over-ordering based on stock levels

### 🛒 Shopping Cart
- **Dynamic Cart Management** - Real-time updates of items and quantities
- **Inline Quantity Editing** - Adjust quantities with inventory validation
- **Quick Item Removal** - Remove items with swipe or button tap
- **Automatic Calculations** - Real-time subtotal, tax, and total calculations
- **Stock Validation** - Prevents checkout if items exceed available inventory
- **Persistent Cart Badge** - Shows item count across all screens

### 💳 Checkout Process
- **Flexible Shipping Options**:
  - Use franchise address (default)
  - Enter custom delivery address
- **Delivery Methods**:
  - Standard delivery (3-5 days, free)
  - Express delivery (1-2 days, $15)
  - Scheduled delivery (select specific date)
- **Order Customization** - Add special instructions or notes
- **Transparent Pricing** - Automatic 8% tax calculation with itemized breakdown
- **Order Review** - Complete summary before placing order

### 📦 Order Management
- **Visual Order Tracking** - Progress tracker showing current order status
- **Advanced Filtering** - Filter orders by status (pending, processing, packed, shipped, delivered)
- **Complete Order History** - View all past orders including rejected ones
- **Detailed Order Views**:
  - All ordered items with quantities and prices
  - Shipping information and tracking
  - Delivery estimates and contact details
  - Status timeline with timestamps
- **One-Click Reorder** - Repeat previous orders with current inventory validation
- **Pull-to-Refresh** - Get latest order updates instantly

### 👤 Profile Management
- **Profile Viewing** - Display user and company information with logo
- **Profile Editing**:
  - Update personal information (username, email, phone)
  - Modify company details (name, address, contact)
  - Upload or remove company logo
- **Real-time Sync** - Always fetches latest data from server

### 🔔 Notifications & Deep Linking
- **Push Notifications** - Firebase Cloud Messaging integration for order updates
- **Universal Deep Links** - Navigate to specific screens from external links
- **Background Handling** - Works whether app is in foreground, background, or closed

### 📱 User Experience
- **Intuitive Navigation** - Slide-out drawer menu with all main sections
- **Persistent Header** - Always-visible cart badge and navigation
- **Pull-to-Refresh** - Available on all data screens for latest updates
- **Loading States** - Clear activity indicators during operations
- **Error Handling** - User-friendly error messages with retry options
- **Toast Notifications** - Non-intrusive success/error feedback
- **Responsive Design** - Adapts to different screen sizes and orientations

## Setup Guide

### Prerequisites
- **Node.js**: Version 22.x or higher (check with `node --version` or run `nvm use` to use node verson asigned to this app)
- **npm**: Version 10.x or higher (check with `npm --version`)
- **React Native CLI**: Install globally with `npm install -g react-native-cli`
- **Platform-specific requirements**:
  - **iOS**: macOS with Xcode 15+ and CocoaPods
  - **Android**: Android Studio with SDK 34+ and configured emulator

### Initial Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd Restaurant-_Franchise_Supply_Platform/franchiseeMobile
   ```

2. **Install Node version**
   ```bash
   # If using nvm (recommended)
   nvm install 22
   nvm use 22
   
   # Verify Node version
   node --version  # Should show v22.x.x
   ```

3. **Install dependencies**
   ```bash
   npm install
   ```

4. **iOS-specific setup** (macOS only)
   ```bash
   cd ios
   pod install
   cd ..
   ```

5. **Android-specific setup**
   ```bash
   # Make the setup script executable
   chmod +x android-setup.sh
   
   # Run the setup script (optional, for troubleshooting)
   ./android-setup.sh
   ```

## Environment Configuration

### 1. Create Environment File
Create a `.env` file in the project root:

```bash
touch .env
```

### 2. Environment Variables Template
Add the following to your `.env` file:

```env
# API Configuration
API_BASE_URL=http://localhost:8000/api
# For physical device testing, use your machine's IP:
# API_BASE_URL=http://192.168.1.100:8000/api

# Deep Linking Configuration
DEEP_LINK_SCHEME=restaurantfranchise
DEEP_LINK_DOMAIN=yourdomain.com

# Push Notification Configuration (if using Firebase)
FIREBASE_API_KEY=your_firebase_api_key_here
FIREBASE_AUTH_DOMAIN=your_firebase_auth_domain_here
FIREBASE_PROJECT_ID=your_firebase_project_id_here
FIREBASE_STORAGE_BUCKET=your_firebase_storage_bucket_here
FIREBASE_MESSAGING_SENDER_ID=your_sender_id_here
FIREBASE_APP_ID=your_app_id_here

# Optional: Development Settings
DEV_MODE=true
LOG_LEVEL=debug
```

### 3. Update API Configuration
Edit `src/services/axiosInstance.js` to match your backend URL:

```javascript
// For local development
export const BASE_URL = 'http://localhost:8000/api';  // iOS simulator
// export const BASE_URL = 'http://10.0.2.2:8000/api';  // Android emulator

// For physical device testing (replace with your machine's IP or ngrock address)
// export const BASE_URL = 'http://192.168.1.100:8000/api';

// For production
// export const BASE_URL = 'https://api.yourdomain.com/api';
```

### 4. Configure Deep Linking

**iOS** - Update `ios/franchiseeMobile/Info.plist`:
- The URL scheme is already configured as `restaurantfranchise`
- To change it, modify the `CFBundleURLSchemes` array

**Android** - Update `android/app/src/main/AndroidManifest.xml`:
- The URL scheme is already configured as `restaurantfranchise`
- Replace `yourdomain.com` with your actual domain for HTTPS deep links

## Running the Application

### Development Mode

1. **Start Metro bundler**
   ```bash
   npm start
   # or
   npx react-native start --reset-cache  # If you encounter caching issues
   ```

2. **Run on iOS** (macOS only)
   ```bash
   npm run ios
   # or for specific simulator
   npx react-native run-ios --simulator="iPhone 15"
   ```

3. **Run on Android**
   ```bash
   # Start Android emulator first, then:
   npm run android
   # or
   npx react-native run-android
   ```

### Building for Release

**iOS**:
1. Open `ios/franchiseeMobile.xcworkspace` in Xcode
2. Select Generic iOS Device
3. Product → Archive
4. Follow the distribution wizard

**Android**:
```bash
cd android
./gradlew assembleRelease
# APK will be in android/app/build/outputs/apk/release/
```

## Troubleshooting

### Common Issues

1. **Metro bundler issues**
   ```bash
   npx react-native start --reset-cache
   ```

2. **iOS build failures**
   ```bash
   cd ios
   pod deintegrate
   pod install
   cd ..
   ```

3. **Android build failures**
   ```bash
   cd android
   ./gradlew clean
   cd ..
   npm run android
   ```

4. **Node version mismatch**
   ```bash
   nvm use 22
   ```

5. **Permission issues on Android**
   - Ensure all permissions are granted in device settings
   - Check `AndroidManifest.xml` for required permissions

### Network Configuration

**For iOS Simulator**:
- Use `http://localhost:8000` for local backend

**For Android Emulator**:
- Use `http://10.0.2.2:8000` for local backend - your computes ip address

**For Physical Devices**:
- Use your machine's IP address (e.g., `http://192.168.1.100:8000` use ngrock ip address)
- Ensure your device is on the same network as your development machine
- May need to disable firewall temporarily for testing

### API Connection Issues

The app includes built-in diagnostics for API troubleshooting:

1. **Content-Type Verification** - Detects HTML responses instead of JSON
2. **Multi-Endpoint Fallback** - Tries multiple common endpoint patterns
3. **Robust Error Handling** - Detailed error reporting with stack traces
4. **Adaptive Response Parsing** - Handles different Laravel response structures
5. **Comprehensive Diagnostics** - Built-in tools for connection testing

### Debugging

1. **Enable Debug Menu**:
   - iOS: Cmd + D
   - Android: Cmd + M (Mac) or Ctrl + M (Windows/Linux)

2. **React Native Debugger**:
   ```bash
   # Install globally
   brew install react-native-debugger  # macOS
   ```

3. **Check logs**:
   ```bash
   # iOS
   npx react-native log-ios
   
   # Android
   npx react-native log-android
   ```

## Additional Resources

- [React Native Documentation](https://reactnative.dev/docs/getting-started)
- [React Navigation Documentation](https://reactnavigation.org/docs/getting-started)
- [Troubleshooting Guide](https://reactnative.dev/docs/troubleshooting)

## Support

For issues specific to this application, please check:
1. The backend API is running and accessible
2. All environment variables are correctly set
3. Database migrations are up to date
4. Required permissions are granted on the device

For further assistance, please refer to the project documentation or contact the development team.


users available:
1. username - admin@example.com, password - password
2. username - user@franchisee.com, password - password

or simply login as admin and create/add user with REAL EMAIL in order to test email notifications and deep link testing
