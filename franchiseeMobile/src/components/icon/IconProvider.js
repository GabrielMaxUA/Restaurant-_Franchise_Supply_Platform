import React, { createContext, useContext, useEffect, useState } from 'react';
import Ionicons from 'react-native-vector-icons/Ionicons';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import FontAwesome from 'react-native-vector-icons/FontAwesome';
import FontAwesome5 from 'react-native-vector-icons/FontAwesome5';
import Entypo from 'react-native-vector-icons/Entypo';

// Create a context for icon loading status
const IconContext = createContext({ iconsLoaded: false });

// Provider component that preloads all icon fonts
export const IconProvider = ({ children }) => {
  const [iconsLoaded, setIconsLoaded] = useState(false);

  useEffect(() => {
    const loadFonts = async () => {
      try {
        // Preload all icon fonts with explicit caching
        const iconFontPromises = [
          Ionicons.getImageSource('ios-checkmark', 1),
          MaterialIcons.getImageSource('check', 1),
          FontAwesome.getImageSource('check', 1),
          FontAwesome5.getImageSource('check', 1),
          Entypo.getImageSource('check', 1),
        ];
        
        // Load all icon sets concurrently
        await Promise.all(iconFontPromises);
        
        console.log('✓ All icon fonts loaded successfully');
        setIconsLoaded(true);
      } catch (error) {
        console.error('Error loading icon fonts:', error);
        // Even if there's an error, we mark as loaded to avoid blocking the app
        setIconsLoaded(true);
      }
    };

    loadFonts();
  }, []);

  return (
    <IconContext.Provider value={{ iconsLoaded }}>
      {children}
    </IconContext.Provider>
  );
};

// Hook to use icon loading status
export const useIcons = () => useContext(IconContext);

export default IconProvider;