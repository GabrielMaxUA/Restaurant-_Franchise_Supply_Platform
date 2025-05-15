import React from 'react';
import { Text, View, StyleSheet } from 'react-native';
import Ionicons from 'react-native-vector-icons/Ionicons';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import FontAwesome from 'react-native-vector-icons/FontAwesome';
import AntDesign from 'react-native-vector-icons/AntDesign';
import { getEmojiForIcon } from '../utils/iconUtils';

// A component that attempts to use a vector icon with emoji fallback
const FallbackIcon = ({ 
  name, 
  size = 24, 
  color = '#000', 
  iconType = 'Ionicons' 
}) => {
  // Use the helper from iconUtils for emoji fallbacks
  
  // Try to render the vector icon first
  const renderVectorIcon = () => {
    try {
      switch (iconType) {
        case 'Ionicons':
          return <Ionicons name={name} size={size} color={color} />;
        case 'MaterialIcons':
          return <MaterialIcons name={name} size={size} color={color} />;
        case 'FontAwesome':
          return <FontAwesome name={name} size={size} color={color} />;
        case 'AntDesign':
          return <AntDesign name={name} size={size} color={color} />;
        default:
          // If icon type not supported, fall back to emoji
          return <Text style={[styles.fallbackText, { fontSize: size, color }]}>
            {getEmojiForIcon(name)}
          </Text>;
      }
    } catch (error) {
      // If error rendering vector icon, fall back to emoji
      console.warn(`Error rendering icon ${name} from ${iconType}:`, error);
      return <Text style={[styles.fallbackText, { fontSize: size, color }]}>
        {getEmojiForIcon(name)}
      </Text>;
    }
  };
  
  return (
    <View style={styles.container}>
      {renderVectorIcon()}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  fallbackText: {
    textAlign: 'center',
  },
});

export default FallbackIcon;