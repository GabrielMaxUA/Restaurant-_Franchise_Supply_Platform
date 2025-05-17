import React from 'react';
import { TouchableOpacity, Text, StyleSheet, View } from 'react-native';
import Ionicons from 'react-native-vector-icons/Ionicons';
import FontAwesome from 'react-native-vector-icons/FontAwesome';
import FontAwesome5 from 'react-native-vector-icons/FontAwesome5';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';

// This component tries different approaches for vector icons
const IconButton = ({
  onPress,
  iconName,
  iconLibrary = 'Ionicons',
  iconSize = 24,
  iconColor = '#fff',
  text,
  style,
  textStyle,
}) => {
  const renderIcon = () => {
    // Approach 1: Standard icon rendering
    if (iconLibrary === 'Ionicons') {
      return <Ionicons name={iconName} size={iconSize} color={iconColor} />;
    } else if (iconLibrary === 'FontAwesome') {
      return <FontAwesome name={iconName} size={iconSize} color={iconColor} />;
    } else if (iconLibrary === 'FontAwesome5') {
      return <FontAwesome5 name={iconName} size={iconSize} color={iconColor} />;
    } else if (iconLibrary === 'MaterialIcons') {
      return <MaterialIcons name={iconName} size={iconSize} color={iconColor} />;
    }
    
    // Fallback to text if library not matched
    return <Text style={{ fontSize: iconSize, color: iconColor }}>?</Text>;
  };

  return (
    <TouchableOpacity style={[styles.button, style]} onPress={onPress}>
      <View style={styles.iconContainer}>
        {renderIcon()}
      </View>
      {text && <Text style={[styles.text, textStyle]}>{text}</Text>}
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  button: {
    padding: 8,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: 'transparent',
  },
  iconContainer: {
    marginBottom: 4,
  },
  text: {
    fontSize: 12,
    color: '#333',
    textAlign: 'center',
  },
});

export default IconButton;