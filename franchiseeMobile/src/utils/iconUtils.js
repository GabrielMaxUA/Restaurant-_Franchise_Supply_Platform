import { Platform } from 'react-native';
import Ionicons from 'react-native-vector-icons/Ionicons';
import FontAwesome from 'react-native-vector-icons/FontAwesome';
import FontAwesome5 from 'react-native-vector-icons/FontAwesome5';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import Entypo from 'react-native-vector-icons/Entypo';
import AntDesign from 'react-native-vector-icons/AntDesign';
import Feather from 'react-native-vector-icons/Feather';

/**
 * Utility to help preload icon fonts programmatically
 * Attempts to work around issues with vector icons in React Native
 */
export const preloadIconFonts = async () => {
  try {
    console.log('🔠 Preloading icon fonts...');
    
    // Force load each icon font by requesting a small icon from each
    const iconPromises = [
      Ionicons.getImageSource('checkmark', 1),
      FontAwesome.getImageSource('check', 1),
      FontAwesome5.getImageSource('check', 1), 
      MaterialIcons.getImageSource('check', 1),
      MaterialCommunityIcons.getImageSource('check', 1),
      Entypo.getImageSource('check', 1),
      AntDesign.getImageSource('check', 1),
      Feather.getImageSource('check', 1),
    ];
    
    // Wait for all icon fonts to load
    await Promise.all(iconPromises);
    
    console.log('✅ All icon fonts preloaded successfully');
    return true;
  } catch (error) {
    console.error('❌ Error preloading icon fonts:', error);
    return false;
  }
};

// Helper to get appropriate emoji for common icon names
export const getEmojiForIcon = (iconName) => {
  const iconMap = {
    'home': '🏠',
    'settings': '⚙️',
    'user': '👤',
    'person': '👤',
    'search': '🔍',
    'cart': '🛒',
    'shopping-cart': '🛒',
    'menu': '☰',
    'close': '✖️',
    'heart': '❤️',
    'star': '⭐',
    'check': '✓',
    'arrow-up': '↑',
    'arrow-down': '↓',
    'calendar': '📅',
    'mail': '📧',
    'phone': '📱',
    'camera': '📷',
    'file': '📄',
    'folder': '📁',
    'location': '📍',
    'bookmark': '🔖',
    'bell': '🔔',
    'clock': '🕒',
    'time': '⏰',
    'play': '▶️',
    'pause': '⏸️',
    'stop': '⏹️',
    'refresh': '🔄',
    'download': '⬇️',
    'upload': '⬆️',
    'trash': '🗑️',
    'delete': '🗑️',
    'add': '➕',
    'remove': '➖',
    'edit': '✏️',
    'share': '📤',
  };
  
  return iconMap[iconName] || '•';
};

export default {
  preloadIconFonts,
  getEmojiForIcon,
};