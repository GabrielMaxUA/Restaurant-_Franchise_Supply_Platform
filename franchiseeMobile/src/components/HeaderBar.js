import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import FontAwesome5 from 'react-native-vector-icons/FontAwesome5';


const HeaderBar = ({ title, onCartPress, onMenuPress }) => {
  return (
    <View style={styles.header}>
        {/* Cart icon on left - now using dynamic cartCount */}
        <TouchableOpacity 
          style={styles.iconButton}
          onPress={() => navigation.navigate('Cart')}
        >
          <View style={styles.cartContainer}>
            <FallbackIcon name="shoppingcart" iconType="AntDesign" size={24} color="#fff" />
            {/* Only show badge if cart has items */}
            {cartCount > 0 && (
              <View style={styles.badge}>
                <Text style={styles.badgeText}>
                  {cartCount > 99 ? '99+' : cartCount}
                </Text>
              </View>
            )}
          </View>
        </TouchableOpacity>
        
        {/* Title in center */}
        <Text style={styles.title}>{title || 'Franchisee Portal'}</Text>
        
        {/* Menu/user icon on right */}
        <View style={styles.rightContainer}>
          <TouchableOpacity
            style={styles.iconButton}
            onPress={handleLogout}
          >
            <FallbackIcon name="logout" iconType="AntDesign" size={24} color="#fff" />
          </TouchableOpacity>
        </View>
      </View>
  );
};

const styles = StyleSheet.create({
  header: {
    backgroundColor: '#e8f4fc',
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 15,
    elevation: 4,
    borderBottomWidth: 1,
    borderColor: '#ccc',
  },
  title: {
    fontSize: 18,
    fontWeight: '600',
    color: '#333',
  },
});

export default HeaderBar;
