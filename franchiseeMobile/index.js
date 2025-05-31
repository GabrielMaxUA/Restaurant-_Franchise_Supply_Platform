/**
 * @format
 */

// Import gesture handler first
import 'react-native-gesture-handler';

// Fix for EPERM: operation not permitted, uv_cwd
if (global && !global.__dirname) {
  global.__dirname = '/';
}

// Suppress Google logging warnings in development
if (__DEV__) {
  const originalWarn = console.warn;
  console.warn = (...args) => {
    if (
      args[0] && 
      (args[0].includes('InitGoogleLogging') || 
       args[0].includes('Scheduler::~Scheduler') ||
       args[0].includes('UIManagerBinding::~UIManagerBinding') ||
       args[0].includes('UIManager::~UIManager'))
    ) {
      return;
    }
    originalWarn.apply(console, args);
  };
}

import {AppRegistry} from 'react-native';
import App from './App';
import {name as appName} from './app.json';

AppRegistry.registerComponent(appName, () => App);