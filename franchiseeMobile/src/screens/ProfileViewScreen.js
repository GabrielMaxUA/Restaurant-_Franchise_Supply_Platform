import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Image,
  ActivityIndicator,
  RefreshControl
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import Icon from 'react-native-vector-icons/Ionicons';
import Toast from 'react-native-toast-message';
import { useFocusEffect } from '@react-navigation/native';

// Import your API functions
import { getProfileData } from '../services/api';

// Import layout component
import FranchiseeLayout from '../components/FranchiseeLayout';

const ProfileViewScreen = ({ navigation, route }) => {
  // State variables
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [userData, setUserData] = useState({
    username: '',
    email: '',
    phone: '',
    updated_at: null
  });
  const [profileData, setProfileData] = useState({
    contact_name: '',
    company_name: '',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    logo_url: null
  });
  
  // Placeholder for cart count
  const [cartCount, setCartCount] = useState(0);
  
  // Function to fetch profile data DIRECTLY from API
  const fetchProfileData = async () => {
    try {
      setLoading(true);
      console.log('🚀 Fetching profile data directly from API...');
      
      // First check for token
      const token = await AsyncStorage.getItem('userToken');
      if (!token) {
        console.error('⛔ No auth token found!');
        Toast.show({
          type: 'error',
          text1: 'Authentication Error',
          text2: 'Please log in again'
        });
        navigation.navigate('Login');
        return;
      }
      
      // Use the API service function to get profile data
      const result = await getProfileData();
      
      if (result.success) {
        console.log('✅ Profile data retrieved successfully from API');
        
        // Set user data
        setUserData({
          username: result.user.username || result.user.name || '',
          email: result.user.email || '',
          phone: result.user.phone || '',
          updated_at: result.user.updated_at || null
        });
        
        // Set profile data
        if (result.profile) {
          setProfileData({
            contact_name: result.profile.contact_name || '',
            company_name: result.profile.company_name || '',
            address: result.profile.address || '',
            city: result.profile.city || '',
            state: result.profile.state || '',
            postal_code: result.profile.postal_code || '',
            logo_url: result.profile.logo_url || null
          });
        } else {
          console.log('⚠️ No profile data in API response');
        }
        
        // Optionally update cache with fresh data
        // This is just for performance, app will always fetch from API first
        await AsyncStorage.setItem('userData', JSON.stringify({
          ...result.user,
          profile: result.profile
        }));
        console.log('💾 Updated local cache with fresh API data');
      } else {
        console.error('❌ API fetch error:', result.error);
        Toast.show({
          type: 'error',
          text1: 'API Error',
          text2: 'Failed to load profile data: ' + result.error
        });
      }
    } catch (error) {
      console.error('🔥 Exception in fetchProfileData:', error);
      Toast.show({
        type: 'error',
        text1: 'Error',
        text2: 'Failed to load profile data'
      });
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };
  
  // Handle pull-to-refresh
  const onRefresh = useCallback(() => {
    setRefreshing(true);
    fetchProfileData();
  }, []);
  
  // Fetch profile data when screen focuses
  useFocusEffect(
    useCallback(() => {
      fetchProfileData();
      
      return () => {
        // Cleanup if needed
      };
    }, [])
  );
  
  // Also check for refresh parameter from navigation
  useEffect(() => {
    if (route.params?.refresh) {
      console.log('🔄 Detected refresh parameter, fetching fresh data');
      fetchProfileData();
      // Clear the parameter to prevent repeated refreshes
      navigation.setParams({ refresh: undefined });
    }
  }, [route.params?.refresh]);
  
  // Function to navigate to edit screen
  const handleEditProfile = () => {
    navigation.navigate('ProfileEdit');
  };
  
  // Function to navigate to change password screen
  const handleChangePassword = () => {
    navigation.navigate('ChangePassword');
  };
  
  // Show loading indicator
  if (loading && !refreshing) {
    return (
      <FranchiseeLayout title="My Profile">
        <View style={styles.loaderContainer}>
          <ActivityIndicator size="large" color="#0066cc" />
          <Text style={styles.loaderText}>Loading profile from server...</Text>
        </View>
      </FranchiseeLayout>
    );
  }
  
  return (
    <FranchiseeLayout title="My Profile">
      <ScrollView 
        style={styles.scrollView}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            colors={['#0066cc']}
            tintColor="#0066cc"
          />
        }
      >
        <View style={styles.container}>
          {/* Header with action buttons */}
          <View style={styles.header}>
            <View style={styles.buttonContainer}>
              <TouchableOpacity 
                style={styles.editButton}
                onPress={handleEditProfile}
              >
                <Icon name="create-outline" size={16} color="#fff" />
                <Text style={styles.buttonText}>Edit Profile</Text>
              </TouchableOpacity>
              
              <TouchableOpacity 
                style={styles.passwordButton}
                onPress={handleChangePassword}
              >
                <Icon name="key-outline" size={16} color="#0066cc" />
                <Text style={styles.passwordButtonText}>Change Password</Text>
              </TouchableOpacity>
            </View>
          </View>
          
          {/* Profile card with logo and details */}
          <View style={styles.profileCard}>
            <View style={styles.logoContainer}>
              {profileData.logo_url ? (
                <Image
                  source={{ uri: profileData.logo_url }}
                  style={styles.logo}
                  resizeMode="contain"
                />
              ) : (
                <View style={styles.logoPlaceholder}>
                  <Icon name="business-outline" size={50} color="#6c757d" />
                </View>
              )}
            </View>
            
            <View style={styles.divider} />
            
            {/* User information section */}
            <View style={styles.infoSection}>
              <Text style={styles.sectionTitle}>User Information</Text>
              
              <View style={styles.infoRow}>
                <View style={styles.infoLabelContainer}>
                  <Icon name="person-outline" size={18} color="#0066cc" />
                  <Text style={styles.infoLabel}>Username</Text>
                </View>
                <Text style={styles.infoValue}>{userData.username}</Text>
              </View>
              
              <View style={styles.infoRow}>
                <View style={styles.infoLabelContainer}>
                  <Icon name="mail-outline" size={18} color="#0066cc" />
                  <Text style={styles.infoLabel}>Email</Text>
                </View>
                <Text style={styles.infoValue}>{userData.email}</Text>
              </View>
              
              <View style={styles.infoRow}>
                <View style={styles.infoLabelContainer}>
                  <Icon name="call-outline" size={18} color="#0066cc" />
                  <Text style={styles.infoLabel}>Phone</Text>
                </View>
                <Text style={styles.infoValue}>{userData.phone || 'Not provided'}</Text>
              </View>
            </View>
            
            <View style={styles.divider} />
            
            {/* Company information section */}
            <View style={styles.infoSection}>
              <Text style={styles.sectionTitle}>Company Information</Text>
              
              <View style={styles.infoRow}>
                <View style={styles.infoLabelContainer}>
                  <Icon name="business-outline" size={18} color="#0066cc" />
                  <Text style={styles.infoLabel}>Company Name</Text>
                </View>
                <Text style={styles.infoValue}>{profileData.company_name}</Text>
              </View>
              
              <View style={styles.infoRow}>
                <View style={styles.infoLabelContainer}>
                  <Icon name="person-outline" size={18} color="#0066cc" />
                  <Text style={styles.infoLabel}>Contact Person</Text>
                </View>
                <Text style={styles.infoValue}>{profileData.contact_name || 'Not provided'}</Text>
              </View>
              
              <View style={styles.infoRow}>
                <View style={styles.infoLabelContainer}>
                  <Icon name="location-outline" size={18} color="#0066cc" />
                  <Text style={styles.infoLabel}>Address</Text>
                </View>
                <Text style={styles.infoValue}>{profileData.address}</Text>
              </View>
              
              <View style={styles.infoRow}>
                <View style={styles.infoLabelContainer}>
                  <Icon name="location-outline" size={18} color="#0066cc" />
                  <Text style={styles.infoLabel}>City</Text>
                </View>
                <Text style={styles.infoValue}>{profileData.city || 'Not provided'}</Text>
              </View>
              
              <View style={styles.infoRow}>
                <View style={styles.infoLabelContainer}>
                  <Icon name="location-outline" size={18} color="#0066cc" />
                  <Text style={styles.infoLabel}>State/Province</Text>
                </View>
                <Text style={styles.infoValue}>{profileData.state || 'Not provided'}</Text>
              </View>
              
              <View style={styles.infoRow}>
                <View style={styles.infoLabelContainer}>
                  <Icon name="location-outline" size={18} color="#0066cc" />
                  <Text style={styles.infoLabel}>Postal Code</Text>
                </View>
                <Text style={styles.infoValue}>{profileData.postal_code || 'Not provided'}</Text>
              </View>
            </View>
          </View>
          
          {/* Footer with last updated info */}
          <View style={styles.footer}>
            <Text style={styles.footerText}>
              <Icon name="time-outline" size={12} color="#6c757d" />{' '}
              Last updated: {userData.updated_at ? 
                new Date(userData.updated_at).toLocaleString() : 'Never'}
            </Text>
            <TouchableOpacity onPress={onRefresh}>
              <Text style={styles.refreshText}>
                <Icon name="refresh-outline" size={12} color="#0066cc" />{' '}
                Refresh
              </Text>
            </TouchableOpacity>
          </View>
        </View>
      </ScrollView>
    </FranchiseeLayout>
  );
};

const styles = StyleSheet.create({
  // Your existing styles here
  scrollView: {
    flex: 1,
  },
  container: {
    padding: 16,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#212529',
  },
  buttonContainer: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  editButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#0066cc',
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 4,
    marginRight: 8,
  },
  buttonText: {
    color: '#fff',
    marginLeft: 4,
    fontWeight: '500',
  },
  passwordButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'transparent',
    borderWidth: 1,
    borderColor: '#0066cc',
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 4,
  },
  passwordButtonText: {
    color: '#0066cc',
    marginLeft: 4,
    fontWeight: '500',
  },
  profileCard: {
    backgroundColor: '#fff',
    borderRadius: 8,
    padding: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    marginBottom: 16,
  },
  logoContainer: {
    alignItems: 'center',
    marginBottom: 20,
  },
  logo: {
    width: 150,
    height: 150,
    borderRadius: 8,
  },
  logoPlaceholder: {
    width: 150,
    height: 150,
    borderRadius: 8,
    backgroundColor: '#e9ecef',
    justifyContent: 'center',
    alignItems: 'center',
  },
  divider: {
    height: 1,
    backgroundColor: '#dee2e6',
    marginVertical: 16,
  },
  infoSection: {
    marginBottom: 16,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 12,
    color: '#212529',
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f1f1',
  },
  infoLabelContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  infoLabel: {
    fontSize: 14,
    color: '#495057',
    marginLeft: 8,
  },
  infoValue: {
    fontSize: 14,
    color: '#212529',
    fontWeight: '500',
    flex: 1,
    textAlign: 'right',
  },
  footer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginVertical: 8,
  },
  footerText: {
    fontSize: 12,
    color: '#6c757d',
  },
  refreshText: {
    fontSize: 12,
    color: '#0066cc',
  },
  loaderContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  loaderText: {
    marginTop: 12,
    fontSize: 14,
    color: '#495057',
  },
});

export default ProfileViewScreen;