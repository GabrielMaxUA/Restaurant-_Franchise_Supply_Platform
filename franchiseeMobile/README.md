# Franchisee Mobile App

This is the mobile application for franchisees to connect to the Restaurant Franchise Supply Platform. It allows franchisees to browse the product catalog, place orders, view order history, and manage their account.

## API Connection Overview

The mobile app connects to the Laravel backend via RESTful API endpoints. The API service is designed to be resilient and handle a variety of response formats and conditions.

### Key API Features

1. **Content-Type Verification**
   - All API responses are checked for proper content-type headers
   - HTML responses are detected and handled properly
   - Non-JSON responses are caught and reported with diagnostic information

2. **Multi-Endpoint Fallback**
   - The API service tries multiple common endpoint patterns when connecting
   - This allows for flexibility in backend routing structure
   - Endpoints are attempted in sequence until a successful one is found

3. **Robust Error Handling**
   - Detailed error reporting with both error codes and response bodies
   - Full stack traces in development mode
   - Fallback error handling when JSON parsing fails

4. **Adaptive Response Parsing**
   - Automatically detects and adapts to different Laravel response structures
   - Handles standard Laravel responses, resource collections, and direct data arrays
   - Normalizes response format for consistent data structure across the app

5. **Comprehensive Diagnostics**
   - Built-in diagnostic tools for API connection testing
   - Content-type and response format analysis
   - Detailed reporting for troubleshooting

## Running the App

### Prerequisites

- Node.js and npm
- React Native development environment
- Android Studio or Xcode
- Backend Laravel API server running

### Installation

1. Clone the repository
2. Install dependencies:
   ```
   npm install
   ```
3. Install pods for iOS:
   ```
   cd ios && pod install && cd ..
   ```

### Running on Android

```
npx react-native run-android
```

### Running on iOS

```
npx react-native run-ios
```

## API Configuration

The API base URL is configured in `src/services/api.js`. By default, it uses:

- Android emulator: `http://10.0.2.2:8000/api`
- iOS simulator: `http://localhost:8000/api`

## Troubleshooting API Connections

If you're experiencing issues with API connections:

1. Use the built-in "Test API Connection" feature in the app
2. Check the API diagnostics report for detailed information
3. Verify the API base URL in `src/services/api.js`
4. Check for proper content-type headers in the backend responses
5. Look for HTML responses that might indicate server errors
6. Verify authentication token handling in both frontend and backend

## Key Files

- `src/services/api.js` - Main API service with endpoint definitions
- `src/utils/ApiDiagnostics.js` - API diagnostics utilities
- `src/utils/ApiTest.js` - API testing functionality
- `src/screens/*.js` - Screen components that use the API service

## Common API Issues

1. **HTML instead of JSON responses**
   - Often indicates a server error or middleware issue
   - Check Laravel logs for details
   - Verify API routes and controllers

2. **Authentication token issues**
   - Token storage/retrieval problems
   - Token validation issues in Laravel
   - Middleware configuration problems

3. **Response format inconsistencies**
   - Laravel's response structure may vary
   - Resource collections vs. direct data arrays
   - Pagination structure differences

4. **Cross-origin issues**
   - CORS configuration in Laravel
   - Headers and allowed origins

5. **Base URL configuration**
   - Different requirements for simulators vs. physical devices
   - Network connectivity and firewall issues
   - HTTP vs. HTTPS requirements

---

## Original React Native Documentation

This is a [**React Native**](https://reactnative.dev) project, bootstrapped using [`@react-native-community/cli`](https://github.com/react-native-community/cli).

### Getting Started

> **Note**: Make sure you have completed the [Set Up Your Environment](https://reactnative.dev/docs/set-up-your-environment) guide before proceeding.

#### Step 1: Start Metro

First, you will need to run **Metro**, the JavaScript build tool for React Native.

To start the Metro dev server, run the following command from the root of your React Native project:

```sh
# Using npm
npm start

# OR using Yarn
yarn start
```

#### Step 2: Build and run your app

With Metro running, open a new terminal window/pane from the root of your React Native project, and use one of the following commands to build and run your Android or iOS app:

##### Android

```sh
# Using npm
npm run android

# OR using Yarn
yarn android
```

##### iOS

For iOS, remember to install CocoaPods dependencies (this only needs to be run on first clone or after updating native deps).

The first time you create a new project, run the Ruby bundler to install CocoaPods itself:

```sh
bundle install
```

Then, and every time you update your native dependencies, run:

```sh
bundle exec pod install
```

For more information, please visit [CocoaPods Getting Started guide](https://guides.cocoapods.org/using/getting-started.html).

```sh
# Using npm
npm run ios

# OR using Yarn
yarn ios
```

If everything is set up correctly, you should see your new app running in the Android Emulator, iOS Simulator, or your connected device.

This is one way to run your app — you can also build it directly from Android Studio or Xcode.