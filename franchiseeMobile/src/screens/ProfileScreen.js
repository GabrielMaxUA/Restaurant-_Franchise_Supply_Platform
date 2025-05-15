import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  TextInput,
  ActivityIndicator,
  SafeAreaView,
  Image,
  Alert,
  Switch,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { getUserProfile } from '../services/api';

const ProfileScreen = ({ navigation }) => {
  const [loading, setLoading] = useState(true);
  const [profile, setProfile] = useState(null);
  const [error, setError] = useState('');
  const [userToken, setUserToken] = useState('');
  const [isEditing, setIsEditing] = useState(false);
  const [editedProfile, setEditedProfile] = useState({});
  const [notifications, setNotifications] = useState(true);

  useEffect(() => {
    const getTokenFromStorage = async () => {
      try {
        const token = await AsyncStorage.getItem('userToken');
        if (token) {
          setUserToken(token);
        } else {
          setError('Authentication token not found. Please login again.');
        }
      } catch (e) {
        console.error('Failed to get token from storage:', e);
        setError('Failed to authenticate. Please login again.');
      }
    };

    getTokenFromStorage();
  }, []);

  useEffect(() => {
    if (userToken) {
      loadProfile();
    }
  }, [userToken]);

  const loadProfile = async () => {
    if (!userToken) return;

    try {
      setLoading(true);
      setError('');

      const profileResponse = await getUserProfile(userToken);
      console.log('Profile response (full):', JSON.stringify(profileResponse));
      
      // Adapt response format if needed (for Laravel standard responses)
      let processedResponse = { ...profileResponse };
      
      // Check if we have a Laravel style response with data property
      if (profileResponse.data && !profileResponse.success) {
        console.log('Detected Laravel response format for profile, adapting...');
        processedResponse.success = true;
        
        // Handle different possible Laravel response structures
        if (profileResponse.data.user) {
          // If data contains a user property
          processedResponse.user = profileResponse.data.user;
        } else if (typeof profileResponse.data === 'object' && !Array.isArray(profileResponse.data)) {
          // If data is directly the user object
          processedResponse.user = profileResponse.data;
        } else if (profileResponse.data.data && !Array.isArray(profileResponse.data.data)) {
          // If data contains a nested data property (common in Laravel resources)
          processedResponse.user = profileResponse.data.data;
        }
      }
      
      // Additional check for common Laravel API pattern returning the user directly
      if (!processedResponse.success && !processedResponse.error && 
          typeof profileResponse === 'object' && profileResponse.id && profileResponse.email) {
        console.log('Detected direct user object response');
        processedResponse.success = true;
        processedResponse.user = profileResponse;
      }

      if (!processedResponse.success) {
        throw new Error(processedResponse.error || profileResponse.message || 'Failed to load profile');
      }

      // Make sure we have a user object
      if (processedResponse.user) {
        console.log('Profile loaded successfully:', processedResponse.user.username || processedResponse.user.name || processedResponse.user.email || 'Username not found');
        
        // Ensure profile has expected structure
        const profileData = {
          ...processedResponse.user,
          // If profile field doesn't exist, create it
          profile: processedResponse.user.profile || 
                  processedResponse.user.franchisee_profile || 
                  {}
        };
        
        setProfile(profileData);
        setEditedProfile(profileData);
      } else {
        setProfile(null);
        setEditedProfile({});
        throw new Error('User profile not found in response');
      }
    } catch (error) {
      console.error('Profile loading error:', error);
      setError('Failed to load profile. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = async () => {
    try {
      Alert.alert(
        'Confirm Logout',
        'Are you sure you want to log out?',
        [
          {
            text: 'Cancel',
            style: 'cancel'
          },
          {
            text: 'Logout',
            onPress: async () => {
              await AsyncStorage.removeItem('userToken');
              await AsyncStorage.removeItem('userData');
              
              // Call the global function if it exists
              if (global.checkAuthState) {
                await global.checkAuthState();
              } else {
                alert('Logged out successfully. Please restart the app.');
              }
            }
          }
        ]
      );
    } catch (error) {
      console.error('Logout error:', error);
      alert('Failed to log out. Please try again.');
    }
  };

  const toggleEditMode = () => {
    if (isEditing) {
      // Save changes
      Alert.alert(
        'Save Changes',
        'Do you want to save your profile changes?',
        [
          {
            text: 'Cancel',
            style: 'cancel',
            onPress: () => {
              // Revert changes
              setEditedProfile(profile);
              setIsEditing(false);
            }
          },
          {
            text: 'Save',
            onPress: saveProfile
          }
        ]
      );
    } else {
      // Enter edit mode
      setIsEditing(true);
    }
  };

  const saveProfile = async () => {
    // This function would call an API to update the profile
    // For now, just show a message and update local state
    setProfile(editedProfile);
    setIsEditing(false);
    alert('Profile updated successfully!');
  };

  const handleProfileChange = (field, value) => {
    setEditedProfile(prev => ({
      ...prev,
      [field]: value,
      profile: {
        ...prev.profile,
        ...(field.startsWith('profile.') && {
          [field.split('.')[1]]: value
        })
      }
    }));
  };

  if (loading) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color="#0066cc" />
        <Text style={styles.loadingText}>Loading profile...</Text>
      </View>
    );
  }

  if (error) {
    return (
      <View style={styles.centered}>
        <Text style={styles.errorText}>{error}</Text>
        <TouchableOpacity
          style={styles.tryAgainButton}
          onPress={loadProfile}
        >
          <Text style={styles.tryAgainButtonText}>Try Again</Text>
        </TouchableOpacity>
      </View>
    );
  }

  if (!profile) {
    return (
      <View style={styles.centered}>
        <Text style={styles.errorText}>Profile not found</Text>
        <TouchableOpacity
          style={styles.logoutButton}
          onPress={handleLogout}
        >
          <Text style={styles.logoutButtonText}>Logout</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView>
        {/* Profile Header */}
        <View style={styles.profileHeader}>
          <View style={styles.profileImageContainer}>
            {profile.profile?.logo_url ? (
              <Image
                source={{ uri: profile.profile.logo_url }}
                style={styles.profileImage}
                resizeMode="cover"
              />
            ) : (
              <View style={styles.profileImagePlaceholder}>
                <Text style={styles.profileImagePlaceholderText}>
                  {profile.profile?.company_name?.charAt(0) || profile.username?.charAt(0) || 'F'}
                </Text>
              </View>
            )}
          </View>
          
          <View style={styles.profileInfo}>
            {isEditing ? (
              <TextInput
                style={styles.nameInput}
                value={editedProfile.username}
                onChangeText={(value) => handleProfileChange('username', value)}
                placeholder="Username"
              />
            ) : (
              <Text style={styles.profileName}>{profile.username}</Text>
            )}
            
            <Text style={styles.profileEmail}>{profile.email}</Text>
            
            <TouchableOpacity
              style={styles.editButton}
              onPress={toggleEditMode}
            >
              <Text style={styles.editButtonText}>
                {isEditing ? 'Save Profile' : 'Edit Profile'}
              </Text>
            </TouchableOpacity>
          </View>
        </View>

        {/* Franchise Information */}
        <View style={styles.sectionContainer}>
          <Text style={styles.sectionTitle}>Franchise Information</Text>
          
          <View style={styles.formGroup}>
            <Text style={styles.label}>Company Name</Text>
            {isEditing ? (
              <TextInput
                style={styles.input}
                value={editedProfile.profile?.company_name}
                onChangeText={(value) => handleProfileChange('profile.company_name', value)}
                placeholder="Company Name"
              />
            ) : (
              <Text style={styles.value}>{profile.profile?.company_name || 'Not provided'}</Text>
            )}
          </View>
          
          <View style={styles.formGroup}>
            <Text style={styles.label}>Franchise ID</Text>
            <Text style={styles.value}>{profile.profile?.franchise_id || 'Not provided'}</Text>
          </View>
          
          <View style={styles.formGroup}>
            <Text style={styles.label}>Registration Date</Text>
            <Text style={styles.value}>
              {profile.created_at 
                ? new Date(profile.created_at).toLocaleDateString() 
                : 'Not available'}
            </Text>
          </View>
        </View>
        
        {/* Contact Information */}
        <View style={styles.sectionContainer}>
          <Text style={styles.sectionTitle}>Contact Information</Text>
          
          <View style={styles.formGroup}>
            <Text style={styles.label}>Phone Number</Text>
            {isEditing ? (
              <TextInput
                style={styles.input}
                value={editedProfile.profile?.phone}
                onChangeText={(value) => handleProfileChange('profile.phone', value)}
                placeholder="Phone Number"
                keyboardType="phone-pad"
              />
            ) : (
              <Text style={styles.value}>{profile.profile?.phone || 'Not provided'}</Text>
            )}
          </View>
          
          <View style={styles.formGroup}>
            <Text style={styles.label}>Address</Text>
            {isEditing ? (
              <>
                <TextInput
                  style={styles.input}
                  value={editedProfile.profile?.address_line1}
                  onChangeText={(value) => handleProfileChange('profile.address_line1', value)}
                  placeholder="Address Line 1"
                />
                <TextInput
                  style={[styles.input, { marginTop: 10 }]}
                  value={editedProfile.profile?.address_line2}
                  onChangeText={(value) => handleProfileChange('profile.address_line2', value)}
                  placeholder="Address Line 2"
                />
                <View style={styles.addressRow}>
                  <TextInput
                    style={[styles.input, { flex: 2, marginRight: 10 }]}
                    value={editedProfile.profile?.city}
                    onChangeText={(value) => handleProfileChange('profile.city', value)}
                    placeholder="City"
                  />
                  <TextInput
                    style={[styles.input, { flex: 1, marginRight: 10 }]}
                    value={editedProfile.profile?.state}
                    onChangeText={(value) => handleProfileChange('profile.state', value)}
                    placeholder="State"
                  />
                  <TextInput
                    style={[styles.input, { flex: 1 }]}
                    value={editedProfile.profile?.zip}
                    onChangeText={(value) => handleProfileChange('profile.zip', value)}
                    placeholder="ZIP"
                    keyboardType="numeric"
                  />
                </View>
              </>
            ) : (
              <>
                <Text style={styles.value}>{profile.profile?.address_line1 || 'Not provided'}</Text>
                {profile.profile?.address_line2 && (
                  <Text style={styles.value}>{profile.profile.address_line2}</Text>
                )}
                {(profile.profile?.city || profile.profile?.state || profile.profile?.zip) && (
                  <Text style={styles.value}>
                    {[
                      profile.profile?.city,
                      profile.profile?.state,
                      profile.profile?.zip
                    ].filter(Boolean).join(', ')}
                  </Text>
                )}
              </>
            )}
          </View>
        </View>
        
        {/* Settings */}
        <View style={styles.sectionContainer}>
          <Text style={styles.sectionTitle}>Settings</Text>
          
          <View style={styles.settingRow}>
            <Text style={styles.settingLabel}>Push Notifications</Text>
            <Switch
              value={notifications}
              onValueChange={setNotifications}
              trackColor={{ false: '#d1d1d1', true: '#81b0ff' }}
              thumbColor={notifications ? '#0066cc' : '#f4f3f4'}
            />
          </View>
          
          <View style={styles.settingRow}>
            <Text style={styles.settingLabel}>Change Password</Text>
            <TouchableOpacity>
              <Text style={styles.linkText}>Change &rsaquo;</Text>
            </TouchableOpacity>
          </View>
          
          <View style={styles.settingRow}>
            <Text style={styles.settingLabel}>Terms and Conditions</Text>
            <TouchableOpacity>
              <Text style={styles.linkText}>View &rsaquo;</Text>
            </TouchableOpacity>
          </View>
          
          <View style={styles.settingRow}>
            <Text style={styles.settingLabel}>Privacy Policy</Text>
            <TouchableOpacity>
              <Text style={styles.linkText}>View &rsaquo;</Text>
            </TouchableOpacity>
          </View>
        </View>
        
        {/* Logout Button */}
        <TouchableOpacity 
          style={styles.logoutButton}
          onPress={handleLogout}
        >
          <Text style={styles.logoutButtonText}>Logout</Text>
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  centered: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  loadingText: {
    marginTop: 10,
    fontSize: 16,
    color: '#666',
  },
  errorText: {
    color: '#c62828',
    fontSize: 16,
    marginBottom: 20,
    textAlign: 'center',
  },
  tryAgainButton: {
    backgroundColor: '#0066cc',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 5,
  },
  tryAgainButtonText: {
    color: '#fff',
    fontSize: 16,
  },
  profileHeader: {
    flexDirection: 'row',
    padding: 20,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  profileImageContainer: {
    marginRight: 20,
  },
  profileImage: {
    width: 80,
    height: 80,
    borderRadius: 40,
  },
  profileImagePlaceholder: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: '#0066cc',
    justifyContent: 'center',
    alignItems: 'center',
  },
  profileImagePlaceholderText: {
    color: '#fff',
    fontSize: 32,
    fontWeight: 'bold',
  },
  profileInfo: {
    flex: 1,
    justifyContent: 'center',
  },
  profileName: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 4,
  },
  profileEmail: {
    fontSize: 14,
    color: '#666',
    marginBottom: 10,
  },
  editButton: {
    backgroundColor: '#f0f0f0',
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 5,
    alignSelf: 'flex-start',
  },
  editButtonText: {
    color: '#0066cc',
    fontSize: 14,
    fontWeight: '500',
  },
  sectionContainer: {
    margin: 15,
    backgroundColor: '#fff',
    borderRadius: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
    overflow: 'hidden',
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    padding: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  formGroup: {
    padding: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  label: {
    fontSize: 14,
    color: '#666',
    marginBottom: 8,
  },
  value: {
    fontSize: 16,
    color: '#333',
  },
  nameInput: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    borderBottomWidth: 1,
    borderBottomColor: '#ccc',
    paddingVertical: 4,
    marginBottom: 4,
  },
  input: {
    fontSize: 16,
    color: '#333',
    borderWidth: 1,
    borderColor: '#ccc',
    borderRadius: 5,
    paddingHorizontal: 10,
    paddingVertical: 8,
  },
  addressRow: {
    flexDirection: 'row',
    marginTop: 10,
  },
  settingRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  settingLabel: {
    fontSize: 16,
    color: '#333',
  },
  linkText: {
    color: '#0066cc',
    fontSize: 14,
    fontWeight: '500',
  },
  logoutButton: {
    margin: 15,
    marginBottom: 30,
    backgroundColor: '#e74c3c',
    padding: 15,
    borderRadius: 5,
    alignItems: 'center',
  },
  logoutButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
});

export default ProfileScreen;