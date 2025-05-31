# Deep Linking Implementation Guide

This document describes the deep linking setup for the Franchisee Mobile app, enabling seamless navigation from emails and external sources directly into specific app screens.

## Overview

Deep linking allows users to tap on links in emails or web pages and be taken directly to specific content within the mobile app. This implementation supports both custom URL schemes and Universal/App Links.

## Supported URL Formats

### Custom URL Scheme
- `restaurantfranchise://[path]`
- Example: `restaurantfranchise://order/123`

### Universal Links (iOS) / App Links (Android)
- `https://yourdomain.com/app/[path]`
- `https://www.yourdomain.com/app/[path]`
- Example: `https://yourdomain.com/app/order/123`

## Supported Routes

| Route | Deep Link Path | Screen | Parameters |
|-------|---------------|---------|------------|
| Order Details | `order/:orderId` | OrderDetails | orderId |
| Product Detail | `product/:productId` | ProductDetail | productId |
| Shopping Cart | `cart` | Cart | - |
| Orders List | `orders` | OrdersScreen | - |
| Product Catalog | `catalog` | Catalog | - |
| User Profile | `profile` | Profile | - |
| Dashboard | `dashboard` or `/` | Dashboard | - |

## Implementation Details

### 1. Android Configuration

**File: `android/app/src/main/AndroidManifest.xml`**

Added intent filters to handle deep links:

```xml
<!-- Deep linking intent filter -->
<intent-filter>
    <action android:name="android.intent.action.VIEW" />
    <category android:name="android.intent.category.DEFAULT" />
    <category android:name="android.intent.category.BROWSABLE" />
    <!-- URL scheme for deep links -->
    <data android:scheme="restaurantfranchise" />
</intent-filter>

<!-- App Links intent filter for HTTPS URLs -->
<intent-filter android:autoVerify="true">
    <action android:name="android.intent.action.VIEW" />
    <category android:name="android.intent.category.DEFAULT" />
    <category android:name="android.intent.category.BROWSABLE" />
    <!-- Replace with your actual domain -->
    <data android:scheme="https" android:host="yourdomain.com" android:pathPrefix="/app" />
    <data android:scheme="https" android:host="www.yourdomain.com" android:pathPrefix="/app" />
</intent-filter>
```

### 2. iOS Configuration

**File: `ios/franchiseeMobile/Info.plist`**

Added URL scheme configuration:

```xml
<key>CFBundleURLTypes</key>
<array>
    <dict>
        <key>CFBundleURLSchemes</key>
        <array>
            <string>restaurantfranchise</string>
        </array>
    </dict>
</array>
```

**File: `ios/franchiseeMobile/AppDelegate.swift`**

Added methods to handle incoming URLs:

```swift
import React_RCTLinkingManager

// Handle URL scheme deep links
func application(_ app: UIApplication, open url: URL, options: [UIApplication.OpenURLOptionsKey : Any] = [:]) -> Bool {
    return RCTLinkingManager.application(app, open: url, options: options)
}

// Handle Universal Links
func application(_ application: UIApplication, continue userActivity: NSUserActivity, restorationHandler: @escaping ([UIUserActivityRestoring]?) -> Void) -> Bool {
    return RCTLinkingManager.application(application, continue: userActivity, restorationHandler: restorationHandler)
}
```

### 3. Navigation Configuration

**File: `src/navigation/AppNavigator.js`**

Added deep linking configuration to NavigationContainer:

```javascript
const linking = {
  prefixes: ['restaurantfranchise://', 'https://yourdomain.com/app', 'https://www.yourdomain.com/app'],
  config: {
    screens: {
      Login: 'login',
      Dashboard: 'dashboard',
      Profile: 'profile',
      ProfileEdit: 'profile/edit',
      ChangePassword: 'change-password',
      Cart: 'cart',
      Catalog: 'catalog',
      OrdersScreen: 'orders',
      OrderHistory: 'order-history',
      Checkout: 'checkout',
      ProductDetail: {
        path: 'product/:productId',
        parse: {
          productId: (productId) => productId,
        },
      },
      OrderDetails: {
        path: 'order/:orderId',
        parse: {
          orderId: (orderId) => orderId,
        },
      },
    },
  },
};

// Applied to NavigationContainer
<NavigationContainer 
  ref={navigationRef}
  linking={linking}
  fallback={<ActivityIndicator size="large" color="#0066cc" />}
>
```

### 4. Deep Link Service

**File: `src/services/DeepLinkService.js`**

Created a service to handle deep link logic:

- Listens for incoming deep links
- Parses URLs to extract routes and parameters
- Handles authentication state
- Stores pending deep links for unauthenticated users
- Navigates to appropriate screens

Key features:
- Authentication-aware navigation
- Pending deep link storage
- URL parsing for different route patterns
- Integration with React Navigation

### 5. App Integration

**File: `App.tsx`**

Initialize deep linking when app starts:

```javascript
import DeepLinkService from './src/services/DeepLinkService';

useEffect(() => {
  DeepLinkService.initialize();

  return () => {
    DeepLinkService.cleanup();
  };
}, []);
```

**File: `src/screens/LoginScreen.js`**

Handle pending deep links after successful login:

```javascript
import DeepLinkService from '../services/DeepLinkService';

// After successful login
const pendingDeepLink = DeepLinkService.getPendingDeepLink();

if (pendingDeepLink) {
  // Navigate to the deep link destination
  navigation.dispatch(
    CommonActions.reset({
      index: 1,
      routes: [
        { name: 'Dashboard' },
        { name: pendingDeepLink.screen, params: pendingDeepLink.params },
      ],
    })
  );
}
```

## Backend Integration

The Laravel backend generates deep links in emails using the following format:

```php
// Example from Laravel
$deepLink = "restaurantfranchise://order/{$orderId}";
$webLink = "https://yourdomain.com/app/order/{$orderId}";
```

When users click these links:
1. **Mobile with app installed**: Opens directly in the app
2. **Mobile without app**: Shows a page offering to download the app or continue in browser
3. **Desktop**: Redirects to web version

## Testing Deep Links

### iOS Simulator
```bash
xcrun simctl openurl booted "restaurantfranchise://order/123"
```

### Android Emulator
```bash
adb shell am start -W -a android.intent.action.VIEW -d "restaurantfranchise://order/123" com.franchiseemobile
```

### Physical Device
1. Send yourself an email with a deep link
2. Open the email on your device
3. Tap the link to test

## Production Setup

### 1. Update Domain References
Replace `yourdomain.com` in the following files with your actual domain:
- `android/app/src/main/AndroidManifest.xml`
- `src/navigation/AppNavigator.js`

### 2. iOS Universal Links
For production iOS Universal Links:

1. Create an `apple-app-site-association` file on your web server:
```json
{
  "applinks": {
    "apps": [],
    "details": [
      {
        "appID": "TEAMID.com.yourcompany.franchiseemobile",
        "paths": ["/app/*"]
      }
    ]
  }
}
```

2. Host this file at: `https://yourdomain.com/.well-known/apple-app-site-association`

3. Add Associated Domains capability in Xcode:
   - Open project in Xcode
   - Select your target
   - Go to "Signing & Capabilities"
   - Add "Associated Domains" capability
   - Add domain: `applinks:yourdomain.com`

### 3. Android App Links Verification
For Android App Links to work without showing the app chooser:

1. Generate SHA256 fingerprint of your signing certificate
2. Create assetlinks.json file:
```json
[{
  "relation": ["delegate_permission/common.handle_all_urls"],
  "target": {
    "namespace": "android_app",
    "package_name": "com.franchiseemobile",
    "sha256_cert_fingerprints": ["YOUR_SHA256_FINGERPRINT"]
  }
}]
```

3. Host at: `https://yourdomain.com/.well-known/assetlinks.json`

## Troubleshooting

### Deep links not working on iOS
- Ensure URL scheme is correctly set in Info.plist
- Check that AppDelegate methods are implemented
- Rebuild the app after configuration changes

### Deep links not working on Android
- Verify intent filters in AndroidManifest.xml
- Check that the app package name matches
- Use `adb shell dumpsys package domain-preferred-apps` to debug

### Navigation issues
- Check that screen names in linking config match navigator screen names
- Verify authentication state handling
- Check console logs for navigation errors

## Security Considerations

1. **Authentication**: The app checks authentication state before navigating to protected screens
2. **URL Validation**: The DeepLinkService validates and parses URLs safely
3. **HTTPS**: Use HTTPS URLs for production to prevent man-in-the-middle attacks

## Future Enhancements

1. Add support for more complex deep link patterns
2. Implement analytics tracking for deep link usage
3. Add support for deferred deep linking (for new app installs)
4. Implement custom in-app routing for special promotions or campaigns