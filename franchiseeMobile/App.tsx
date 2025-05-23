import React, { useEffect } from 'react';
import AppNavigator from './src/navigation/AppNavigator';
import IconProvider from './src/components/icon/IconProvider';
import { preloadIconFonts } from './src/utils/iconUtils';
//import PushNotificationService from './src/services/PushNotificationService';

const App = () => {
  // Attempt to preload icon fonts when app starts
  useEffect(() => {
    preloadIconFonts()
      .then(success => console.log('Icon preloading result:', success))
      .catch(error => console.warn('Icon preloading error:', error));
  }, []);


  //Initialize push notifications

  // useEffect(() => {
  //   PushNotificationService.initialize();

  //   // Cleanup on unmount
  //   return () => {
  //     PushNotificationService.cleanup();
  //   };
  // }, []);


  return (
    <IconProvider>
      <AppNavigator />
    </IconProvider>
  );
};

export default App;
