# React Native App Migration Guide

This guide outlines the process for migrating the Restaurant Franchise Supply mobile app to a new environment.

## Prerequisites

- Node.js (v18 or later)
- Xcode (for iOS development)
- Android Studio (for Android development)
- React Native CLI

## Step 1: Create a New React Native Project

```bash
# Create a new React Native project with TypeScript template
npx react-native init NewFranchiseApp --template react-native-template-typescript

# Navigate to your new project
cd NewFranchiseApp
```

## Step 2: Install Required Dependencies

Update your package.json with these dependencies or install them directly:

```bash
# Core navigation packages
npm install @react-navigation/native @react-navigation/stack @react-navigation/bottom-tabs

# Essential supporting libraries
npm install @react-native-async-storage/async-storage
npm install react-native-safe-area-context
npm install react-native-screens
npm install react-native-gesture-handler
npm install react-native-reanimated

# Optional packages (as needed)
npm install react-native-chart-kit
```

## Step 3: Copy Source Files

Transfer the following key files and directories from the original project:

```
src/
├── assets/         # Images, fonts, etc.
├── navigation/     # Navigation configuration
├── screens/        # UI screens
└── services/       # API and other services
```

Don't forget to copy:
- `App.tsx` (Main application file)

## Step 4: Update API Configuration

Edit the API base URL in `src/services/api.js` to match your backend:

```javascript
// Update this to point to your backend server
export const BASE_URL = 'http://your-backend-server.com/api';
```

## Step 5: Configure Project

1. Update app name in `app.json`:
```json
{
  "name": "YourAppName",
  "displayName": "Your App Display Name"
}
```

2. If using TypeScript, ensure your `tsconfig.json` includes:
```json
{
  "compilerOptions": {
    "jsx": "react",
    "esModuleInterop": true,
    "resolveJsonModule": true
  }
}
```

## Step 6: Handle Global Functions

In `App.tsx`, we use `globalThis` to make functions globally available:

```typescript
// Make this function available globally
globalThis.checkAuthState = checkAuthState;
```

## Step 7: Run and Test

```bash
# For iOS
npx react-native run-ios

# For Android
npx react-native run-android
```

## Troubleshooting Common Issues

1. **Missing dependencies**: If you encounter errors about missing packages, check that all dependencies are properly installed.

2. **Navigation issues**: Ensure your navigation structure is correctly transferred and that all screens are properly imported.

3. **API connectivity**: Verify your backend API is accessible from the device or emulator with the configured URL.

4. **Build errors**:
   - iOS: Try cleaning the build folder with `cd ios && pod install && cd ..`
   - Android: Try `cd android && ./gradlew clean && cd ..`

5. **TypeScript errors**: Ensure all files are properly typed, especially when combining JS and TS files.

## Additional Notes

- Update any environment-specific configurations (API keys, etc.)
- Test all critical user flows after migration
- Consider setting up proper environment configurations for development/staging/production