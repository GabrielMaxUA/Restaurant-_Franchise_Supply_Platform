// components/CustomDrawer.js - Custom drawer content for app navigation

import React, { useContext } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ImageBackground,
  Image
} from 'react-native';
import {
  DrawerContentScrollView,
  DrawerItemList,
} from '@react-navigation/drawer';
import { FontAwesome5 } from 'react-native-vector-icons';
import { AuthContext } from '../contexts/AuthContext';

const CustomDrawer = (props) => {
  const { state, signOut } = useContext(AuthContext);
  const userProfile = state.userProfile || {};
  const cartCount = state.cartCount || 0;
  
  // Get company name or username
  const displayName = userProfile.profile?.company_name || userProfile.username || 'Franchisee';
  
  // Get logo if available
  const logoUrl = userProfile.profile?.logo_url;
  
  return (
    <View style={styles.container}>
      <DrawerContentScrollView {...props}>
        <View style={styles.drawerHeader}>
          <View style={styles.userInfo}>
            {logoUrl ? (
              <Image 
                source={{ uri: logoUrl }} 
                style={styles.logo} 
                resizeMode="contain"
              />
            ) : (
              <View style={styles.avatarPlaceholder}>
                <FontAwesome5 name="utensils" size={30} color="#fff" />
              </View>
            )}
            <Text style={styles.companyName}>{displayName}</Text>
            <Text style={styles.subtitle}>Franchisee Portal</Text>
          </View>
        </View>
        
        <View style={styles.drawerItemsContainer}>
          {/* Standard drawer items from DrawerItemList */}
          <DrawerItemList
            {...props}
            labelStyle={styles.drawerItemLabel}
          />
        </View>
      </DrawerContentScrollView>
      
      <View style={styles.drawerFooter}>
        <TouchableOpacity 
          style={styles.logoutButton}
          onPress={signOut}
        >
          <FontAwesome5 name="sign-out-alt" size={16} color="#fff" style={styles.logoutIcon} />
          <Text style={styles.logoutText}>Logout</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#343a40'
  },
  drawerHeader: {
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(255, 255, 255, 0.1)',
  },
  userInfo: {
    alignItems: 'center',
    marginVertical: 12,
  },
  logo: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: '#495057',
    marginBottom: 12,
  },
  avatarPlaceholder: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: '#495057',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
  },
  companyName: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold',
  },
  subtitle: {
    color: 'rgba(255, 255, 255, 0.7)',
    fontSize: 14,
    marginTop: 4,
  },
  drawerItemsContainer: {
    marginTop: 8,
  },
  drawerItemLabel: {
    color: 'rgba(255, 255, 255, 0.8)',
    fontSize: 15,
    marginLeft: -16,
  },
  drawerFooter: {
    padding: 16,
    borderTopWidth: 1,
    borderTopColor: 'rgba(255, 255, 255, 0.1)',
  },
  logoutButton: {
    backgroundColor: '#dc3545',
    padding: 12,
    borderRadius: 8,
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
  },
  logoutIcon: {
    marginRight: 8,
  },
  logoutText: {
    color: '#fff',
    fontWeight: 'bold',
  },
});

export default CustomDrawer;