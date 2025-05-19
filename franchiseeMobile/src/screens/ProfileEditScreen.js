import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TextInput,
  TouchableOpacity,
  Image,
  Alert,
  ActivityIndicator,
  Platform,
  KeyboardAvoidingView
} from 'react-native';
import { launchImageLibrary } from 'react-native-image-picker';
import { updateProfile, BASE_URL } from '../services/api'; // Adjust path as needed
import AsyncStorage from '@react-native-async-storage/async-storage';
import Icon from 'react-native-vector-icons/Ionicons';
import { PermissionsAndroid } from 'react-native';
import Toast from 'react-native-toast-message';
import FranchiseeLayout from '../components/FranchiseeLayout';

// Mock image picker function as fallback
const mockPickImage = () => {
  return new Promise((resolve) => {
    // Return a mock successful result
    setTimeout(() => {
      resolve({
        didCancel: false,
        assets: [
          {
            uri: 'https://picsum.photos/150',
            type: 'image/jpeg',
            fileName: 'placeholder.jpg',
            fileSize: 1024 * 50 // 50KB mock size
          }
        ]
      });
    }, 500);
  });
};

const ProfileEditScreen = ({ navigation }) => {
  // State variables
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
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
  const [errors, setErrors] = useState({});
  const [logoToUpload, setLogoToUpload] = useState(null);
  const [removeLogo, setRemoveLogo] = useState(false);
  const [useMock, setUseMock] = useState(false); // Flag to determine if mock should be used
    
  // Fetch cart count - you can modify this to fit your actual cart implementation
  useEffect(() => {

    //to delete later
    const checkApiConnection = async () => {
      try {
        // Check token
        const token = await AsyncStorage.getItem('userToken');
        console.log('🔑 Token check:', token ? 'Present' : 'Missing');
        
        if (token) {
          // Log JWT payload if possible
          const parts = token.split('.');
          if (parts.length === 3) {
            try {
              const payload = JSON.parse(atob(parts[1]));
              console.log('🔐 Token payload:', payload);
              
              // Check for expiration
              const now = Math.floor(Date.now() / 1000);
              if (payload.exp && payload.exp < now) {
                console.error('⏰ Token has expired!', {
                  expired: new Date(payload.exp * 1000).toLocaleString(),
                  now: new Date().toLocaleString()
                });
              }
            } catch (e) {
              console.error('Failed to decode token:', e);
            }
          }
        }
        
        // Check basic API connectivity
        console.log('🧪 Testing API connection...');
        const testResponse = await fetch(`${BASE_URL}/test`, {
          method: 'GET',
          headers: { 'Accept': 'application/json' }
        });
        
        console.log('📡 API connectivity test:', {
          status: testResponse.status,
          ok: testResponse.ok
        });
        
        // If we can reach the API, try an authenticated endpoint
        if (testResponse.ok && token) {
          console.log('🧪 Testing authenticated endpoint...');
          const authTest = await fetch(`${BASE_URL}/auth/me`, {
            method: 'GET',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Accept': 'application/json'
            }
          });
          
          console.log('🔒 Auth test:', {
            status: authTest.status,
            ok: authTest.ok
          });
        }
      } catch (error) {
        console.error('❌ API connection error:', error);
      }
    };
    
    checkApiConnection();
  }, []);

  // Fetch profile data on component mount
  useEffect(() => {
    fetchProfileData();
  }, []);

 // API-ONLY fetchProfileData function - NO local storage fallback

const fetchProfileData = async () => {
  try {
    setLoading(true);
    console.log('🚀 Starting API-ONLY profile data retrieval...');
    
    // Check for token
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
    
    console.log('🔑 Using token to fetch profile data from API');
    
    // ONLY fetch from API - no local storage fallback
    const response = await fetch(`${BASE_URL}/franchisee/profile`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    
    console.log('📊 API response status:', response.status);
    
    // Handle non-200 responses
    if (!response.ok) {
      throw new Error(`API error: ${response.status} ${response.statusText}`);
    }
    
    // Get response as text first for debugging
    const responseText = await response.text();
    console.log('📝 API response data:', responseText.substring(0, 150) + '...');
    
    // Parse response
    let data;
    try {
      data = JSON.parse(responseText);
    } catch (jsonError) {
      console.error('❌ Failed to parse API response as JSON:', jsonError);
      throw new Error('Invalid API response format');
    }
    
    console.log('✅ Successfully retrieved profile data from API');
    
    // Save latest data to state
    setUserData({
      username: data.user?.username || '',
      email: data.user?.email || '',
      phone: data.user?.phone || '',
      updated_at: data.user?.updated_at || null
    });
    
    // Set profile data if available
    if (data.profile) {
      setProfileData({
        contact_name: data.profile.contact_name || '',
        company_name: data.profile.company_name || '',
        address: data.profile.address || '',
        city: data.profile.city || '',
        state: data.profile.state || '',
        postal_code: data.profile.postal_code || '',
        logo_url: data.profile.logo_url || null
      });
    }
    
    // Optional: Update local storage with API data
    await AsyncStorage.setItem('userData', JSON.stringify(data));
    console.log('💾 Updated local cache with fresh API data');
    
  } catch (error) {
    console.error('🔥 API fetch error:', error.message);
    Toast.show({
      type: 'error',
      text1: 'API Error',
      text2: 'Failed to load profile from server: ' + error.message
    });
    
    // DO NOT FALLBACK TO LOCAL STORAGE
    console.log('⛔ Not using local storage fallback per requirements');
  } finally {
    setLoading(false);
  }
};

  // Handle form input changes for user data
  const handleUserChange = (field, value) => {
    setUserData({
      ...userData,
      [field]: value
    });
    
    // Clear validation error when user types
    if (errors[field]) {
      setErrors({
        ...errors,
        [field]: null
      });
    }
  };

  // Handle form input changes for profile data
  const handleProfileChange = (field, value) => {
    setProfileData({
      ...profileData,
      [field]: value
    });
    
    // Clear validation error when user types
    if (errors[field]) {
      setErrors({
        ...errors,
        [field]: null
      });
    }
  };

  // Request permission for Android
  const requestStoragePermission = async () => {
    if (Platform.OS !== 'android') return true;
    
    try {
      if (parseInt(Platform.Version, 10) >= 33) {
        // For Android 13+ (API 33+)
        const granted = await PermissionsAndroid.request(
          PermissionsAndroid.PERMISSIONS.READ_MEDIA_IMAGES
        );
        return granted === PermissionsAndroid.RESULTS.GRANTED;
      } else {
        // For Android 12 and below
        const granted = await PermissionsAndroid.request(
          PermissionsAndroid.PERMISSIONS.READ_EXTERNAL_STORAGE
        );
        return granted === PermissionsAndroid.RESULTS.GRANTED;
      }
    } catch (err) {
      console.warn('❌ Permission request error:', err);
      return false;
    }
  };

  // Function to pick logo - now tries real picker first, falls back to mock if needed
  const pickLogo = async () => {
    try {
      // Request permission for Android
      const hasPermission = await requestStoragePermission();
      if (!hasPermission) {
        Alert.alert('Permission Denied', 'We need permission to access your photos');
        return;
      }
      
      let result;
      
      // Try to use the real image picker first
      if (!useMock) {
        try {
          console.log('📷 Attempting to use real image picker...');
          
          // Launch image picker
          const pickerResult = await launchImageLibrary({
            mediaType: 'photo',
            quality: 0.8,
            maxWidth: 800,
            maxHeight: 800,
            includeBase64: false,
          });
          
          // If successful, use this result
          if (pickerResult && !pickerResult.didCancel) {
            result = pickerResult;
            console.log('📷 Real image picker used successfully');
          } else if (pickerResult.didCancel) {
            console.log('📷 Image picker cancelled by user');
            return; // User cancelled, just return
          } else {
            // Something went wrong, fall back to mock
            console.log('⚠️ Real picker failed, falling back to mock');
            setUseMock(true);
            result = await mockPickImage();
            Toast.show({
              type: 'info',
              text1: 'Using Placeholder Image',
              text2: 'Your device image picker is unavailable'
            });
          }
        } catch (error) {
          console.error('❌ Real image picker error:', error);
          // Fall back to mock picker
          console.log('⚠️ Real picker threw error, falling back to mock');
          setUseMock(true);
          result = await mockPickImage();
          Toast.show({
            type: 'info',
            text1: 'Using Placeholder Image',
            text2: 'Using fallback due to image picker error'
          });
        }
      } else {
        // Use mock picker directly if needed
        console.log('📷 Using mock image picker (as set by flag)...');
        result = await mockPickImage();
        Toast.show({
          type: 'info',
          text1: 'Using Placeholder Image',
          text2: 'This is a mock image for development'
        });
      }
      
      // Process the result
      if (!result.didCancel && result.assets && result.assets.length > 0) {
        // Image picked successfully
        const selectedImage = result.assets[0];
        
        console.log('📷 Selected image:', {
          uri: selectedImage.uri,
          type: selectedImage.type,
          size: selectedImage.fileSize,
        });
        
        // Check file size (limit to 2MB)
        if (selectedImage.fileSize > 2 * 1024 * 1024) {
          Alert.alert('File too large', 'Please select an image under 2MB');
          return;
        }
        
        // Set the logo to upload and display a preview
        setLogoToUpload(selectedImage.uri);
        // If we were previously removing the logo, cancel that
        setRemoveLogo(false);
        
        console.log('✅ Logo selected for upload');
      }
    } catch (error) {
      console.error('🔥 Exception in pickLogo:', error);
      Toast.show({
        type: 'error',
        text1: 'Error',
        text2: 'Failed to pick image'
      });
    }
  };

  // Function to handle logo removal
  const handleRemoveLogo = () => {
    console.log('🗑️ Removing logo');
    setRemoveLogo(true);
    setLogoToUpload(null);
  };

// API-ONLY saveProfile function - No local storage fallback

const saveProfile = async () => {
  try {
    setSaving(true);
    console.log('💾 Starting API-ONLY profile save process...');
    
    // Validate form data first
    const validateForm = () => {
      const newErrors = {};
      
      // Add validation for required fields
      if (!userData.email) newErrors.email = ['Email is required'];
      if (!userData.username) newErrors.username = ['Username is required'];
      if (!profileData.company_name) newErrors.company_name = ['Company name is required'];
      if (!profileData.address) newErrors.address = ['Address is required'];
      
      setErrors(newErrors);
      return Object.keys(newErrors).length === 0;
    };
    
    // Stop if validation fails
    if (!validateForm()) {
      console.log('❌ Validation failed - cannot save');
      setSaving(false);
      
      Toast.show({
        type: 'error',
        text1: 'Validation Error',
        text2: 'Please check the highlighted fields'
      });
      
      return;
    }
    
    console.log('✅ Validation passed, proceeding with API save');
    
    // Create FormData object for API request
    const formData = new FormData();
    
    // Add user data fields - ENSURE ALL REQUIRED FIELDS
    formData.append('username', userData.username);
    formData.append('email', userData.email);
    formData.append('phone', userData.phone || '');
    
    // Add profile data fields - ENSURE ALL REQUIRED FIELDS
    formData.append('contact_name', profileData.contact_name || '');
    formData.append('company_name', profileData.company_name);
    formData.append('address', profileData.address); // REQUIRED!
    formData.append('city', profileData.city || '');
    formData.append('state', profileData.state || '');
    formData.append('postal_code', profileData.postal_code || '');
    
    // Handle logo upload
    if (logoToUpload) {
      // Get file name from URI
      const fileName = logoToUpload.split('/').pop();
      
      // Determine file type
      const match = /\.(\w+)$/.exec(fileName);
      const type = match ? `image/${match[1]}` : 'image/jpeg';
      
      // Append logo to form data
      formData.append('logo', {
        uri: Platform.OS === 'ios' ? logoToUpload.replace('file://', '') : logoToUpload,
        type: type,
        name: fileName,
      });
      
      console.log('📎 Adding logo to form data:', {
        uri: logoToUpload,
        type,
        name: fileName
      });
    } else if (removeLogo) {
      // If logo should be removed
      formData.append('remove_logo', '1');
      console.log('🗑️ Setting logo to be removed');
    }
    
    // Use the existing updateProfile function from your API service
    console.log('🌐 Calling updateProfile API function');
    const result = await updateProfile(formData);
    
    console.log('📊 updateProfile result:', result);
    
    if (result.success) {
      console.log('✅ Profile update successful on API');
      
      // Clear local userData storage to force fresh API fetch on next screen
      await AsyncStorage.removeItem('userData');
      console.log('🧹 Cleared local userData cache to force fresh fetch');
      
      // Show success message
      Toast.show({
        type: 'success',
        text1: 'Success',
        text2: 'Profile updated successfully'
      });
      
      // Navigate back and force refresh
      setTimeout(() => {
        navigation.navigate('Profile', { refresh: true });
      }, 1000);
    } else {
      console.error('❌ API update failed:', result.error || 'Unknown error');
      
      // Handle validation errors from API
      if (result.errors) {
        setErrors(result.errors);
        console.log('⚠️ Server validation errors:', result.errors);
        
        Toast.show({
          type: 'error',
          text1: 'Validation Error',
          text2: result.error || 'Please check the highlighted fields'
        });
        
        // Stay on form to fix errors
        setSaving(false);
        return;
      }
      
      // Show error message
      Toast.show({
        type: 'error',
        text1: 'Server Error',
        text2: result.error || 'Failed to update profile on server'
      });
      
      // Stay on screen
      setSaving(false);
    }
  } catch (error) {
    console.error('🔥 Exception in saveProfile:', error);
    
    Toast.show({
      type: 'error',
      text1: 'Error',
      text2: 'Failed to update profile: ' + error.message
    });
    
    setSaving(false);
  }
};
  // Function to navigate to password change screen
  const navigateToPasswordChange = () => {
    navigation.navigate('ChangePassword');
  };

  // Show loading indicator while fetching data
  if (loading) {
    return (
      <FranchiseeLayout title="Edit Profile">
        <View style={styles.loaderContainer}>
          <ActivityIndicator size="large" color="#0066cc" />
          <Text style={styles.loaderText}>Loading profile...</Text>
        </View>
      </FranchiseeLayout>
    );
  }

  return (
    <FranchiseeLayout title="Edit Profile">
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : null}
        style={styles.keyboardAvoidingView}
      >
        <ScrollView style={styles.scrollView}>
          {/* Header with Change Password button */}
          <View style={styles.header}>
            <View style={styles.headerTitleContainer}>
              <Icon name="create-outline" size={24} color="#0066cc" />
              <Text style={styles.headerTitle}>Edit Profile</Text>
            </View>
            <TouchableOpacity 
              style={styles.passwordButton} 
              onPress={navigateToPasswordChange}
            >
              <Icon name="key-outline" size={16} color="#0066cc" />
              <Text style={styles.passwordButtonText}>Change Password</Text>
            </TouchableOpacity>
          </View>

          {/* Basic Information Section */}
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Basic Information</Text>
            
            <View style={styles.inputRow}>
              <View style={styles.inputContainer}>
                <Text style={styles.label}>Username</Text>
                <TextInput
                  style={[styles.input, errors.username && styles.inputError]}
                  value={userData.username}
                  onChangeText={(text) => handleUserChange('username', text)}
                  placeholder="Enter username"
                />
                {errors.username && (
                  <Text style={styles.errorText}>{errors.username[0]}</Text>
                )}
              </View>
              
              <View style={styles.inputContainer}>
                <Text style={styles.label}>Email Address</Text>
                <TextInput
                  style={[styles.input, errors.email && styles.inputError]}
                  value={userData.email}
                  onChangeText={(text) => handleUserChange('email', text)}
                  placeholder="Enter email"
                  keyboardType="email-address"
                  autoCapitalize="none"
                />
                {errors.email && (
                  <Text style={styles.errorText}>{errors.email[0]}</Text>
                )}
              </View>
            </View>
            
            <View style={styles.inputRow}>
              <View style={styles.inputContainer}>
                <Text style={styles.label}>Phone Number</Text>
                <TextInput
                  style={[styles.input, errors.phone && styles.inputError]}
                  value={userData.phone || ''}
                  onChangeText={(text) => handleUserChange('phone', text)}
                  placeholder="Enter phone number"
                  keyboardType="phone-pad"
                />
                {errors.phone && (
                  <Text style={styles.errorText}>{errors.phone[0]}</Text>
                )}
              </View>
              
              <View style={styles.inputContainer}>
                <Text style={styles.label}>Contact Person Name</Text>
                <TextInput
                  style={[styles.input, errors.contact_name && styles.inputError]}
                  value={profileData.contact_name || ''}
                  onChangeText={(text) => handleProfileChange('contact_name', text)}
                  placeholder="Enter contact name"
                />
                {errors.contact_name && (
                  <Text style={styles.errorText}>{errors.contact_name[0]}</Text>
                )}
              </View>
            </View>
          </View>

          {/* Company Information Section */}
          <View style={styles.divider}>
            <View style={styles.dividerLine} />
            <Text style={styles.dividerText}>Company Information</Text>
          </View>
          
          <View style={styles.section}>
            <View style={styles.inputRow}>
              <View style={styles.inputContainer}>
                <Text style={styles.label}>Company Name</Text>
                <TextInput
                  style={[styles.input, errors.company_name && styles.inputError]}
                  value={profileData.company_name || ''}
                  onChangeText={(text) => handleProfileChange('company_name', text)}
                  placeholder="Enter company name"
                />
                {errors.company_name && (
                  <Text style={styles.errorText}>{errors.company_name[0]}</Text>
                )}
              </View>
            </View>
            
            {/* Logo Section */}
            <View style={styles.logoSection}>
              <Text style={styles.label}>Company Logo</Text>
              <View style={styles.logoContainer}>
                {!removeLogo && (logoToUpload || profileData.logo_url) ? (
                  <Image
                    source={{ uri: logoToUpload || profileData.logo_url }}
                    style={styles.logoPreview}
                    resizeMode="contain"
                  />
                ) : (
                  <View style={styles.logoPlaceholder}>
                    <Icon name="business-outline" size={40} color="#6c757d" />
                  </View>
                )}
                
                <View style={styles.logoActions}>
                  <TouchableOpacity 
                    style={styles.logoButton} 
                    onPress={pickLogo}
                  >
                    <Icon name="cloud-upload-outline" size={16} color="#fff" />
                    <Text style={styles.logoButtonText}>Upload Logo</Text>
                  </TouchableOpacity>
                  
                  {(logoToUpload || (!removeLogo && profileData.logo_url)) && (
                    <TouchableOpacity 
                      style={styles.logoRemoveButton} 
                      onPress={handleRemoveLogo}
                    >
                      <Icon name="trash-outline" size={16} color="#fff" />
                      <Text style={styles.logoButtonText}>Remove</Text>
                    </TouchableOpacity>
                  )}
                </View>
                <Text style={styles.logoHelpText}>
                  Upload your company logo (JPG, PNG, GIF). Max size: 2MB
                </Text>
              </View>
            </View>
            
            {/* Address Section */}
            <View style={styles.inputContainer}>
              <Text style={styles.label}>Address</Text>
              <TextInput
                style={[styles.input, errors.address && styles.inputError]}
                value={profileData.address || ''}
                onChangeText={(text) => handleProfileChange('address', text)}
                placeholder="Enter address"
              />
              {errors.address && (
                <Text style={styles.errorText}>{errors.address[0]}</Text>
              )}
            </View>
            
            <View style={styles.inputRow}>
              <View style={styles.inputContainer}>
                <Text style={styles.label}>City</Text>
                <TextInput
                  style={[styles.input, errors.city && styles.inputError]}
                  value={profileData.city || ''}
                  onChangeText={(text) => handleProfileChange('city', text)}
                  placeholder="Enter city"
                />
                {errors.city && (
                  <Text style={styles.errorText}>{errors.city[0]}</Text>
                )}
              </View>
              
              <View style={styles.inputContainer}>
                <Text style={styles.label}>State/Province</Text>
                <TextInput
                  style={[styles.input, errors.state && styles.inputError]}
                  value={profileData.state || ''}
                  onChangeText={(text) => handleProfileChange('state', text)}
                  placeholder="Enter state"
                />
                {errors.state && (
                  <Text style={styles.errorText}>{errors.state[0]}</Text>
                )}
              </View>
              
              <View style={styles.inputContainer}>
                <Text style={styles.label}>Postal Code</Text>
                <TextInput
                  style={[styles.input, errors.postal_code && styles.inputError]}
                  value={profileData.postal_code || ''}
                  onChangeText={(text) => handleProfileChange('postal_code', text)}
                  placeholder="Enter postal code"
                />
                {errors.postal_code && (
                  <Text style={styles.errorText}>{errors.postal_code[0]}</Text>
                )}
              </View>
            </View>
          </View>
          
          {/* Footer */}
          <View style={styles.footer}>
            <View style={styles.footerRight}>
              <TouchableOpacity 
                style={[styles.saveButton, saving && styles.saveButtonDisabled]} 
                onPress={saveProfile}
                disabled={saving}
              >
                {saving ? (
                  <ActivityIndicator size="small" color="#fff" />
                ) : (
                  <>
                    <Icon name="save-outline" size={16} color="#fff" />
                    <Text style={styles.saveButtonText}>Save Changes</Text>
                  </>
                )}
              </TouchableOpacity>
              
              <Text style={styles.lastUpdated}>
                <Icon name="time-outline" size={12} color="#6c757d" />
                {' '}Last updated: {userData.updated_at ? 
                  new Date(userData.updated_at).toLocaleString() : 'Never'}
              </Text>
            </View>
          </View>

          
          {/* Development Mode Toggle */}
          <View style={styles.devModeContainer}>
            <Text style={styles.devModeText}>Use mock image picker:</Text>
            <TouchableOpacity
              style={[
                styles.devModeToggle,
                useMock ? styles.devModeToggleActive : {}
              ]}
              onPress={() => setUseMock(!useMock)}
            >
              <Text style={styles.devModeToggleText}>
                {useMock ? 'ON' : 'OFF'}
              </Text>
            </TouchableOpacity>
            <Text style={styles.devModeHint}>
              {useMock ? 'Using placeholder images' : 'Using device image picker'}
            </Text>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
      
      {/* Toast message for notifications */}
      <Toast />
    </FranchiseeLayout>
  );
};

const styles = StyleSheet.create({
  keyboardAvoidingView: {
    flex: (Platform.OS === 'ios') ? 1 : null,
  },
  scrollView: {
    flex: 1,
    padding: 16,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: 'white',
    padding: 16,
    borderRadius: 8,
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  headerTitleContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '500',
    marginLeft: 8,
    color: '#212529',
  },
  passwordButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'transparent',
    borderWidth: 1,
    borderColor: '#0066cc',
    borderRadius: 4,
    paddingVertical: 6,
    paddingHorizontal: 12,
  },
  passwordButtonText: {
    color: '#0066cc',
    fontSize: 12,
    marginLeft: 4,
  },
  section: {
    backgroundColor: 'white',
    borderRadius: 8,
    padding: 16,
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '500',
    marginBottom: 16,
    color: '#495057',
  },
  divider: {
    flexDirection: 'row',
    alignItems: 'center',
    marginVertical: 16,
  },
  dividerLine: {
    flex: 1,
    height: 1,
    backgroundColor: 'rgba(0,0,0,0.1)',
  },
  dividerText: {
    paddingHorizontal: 10,
    backgroundColor: '#f5f5f5', // Match FranchiseeLayout background
    color: '#6c757d',
    fontSize: 14,
    fontWeight: '500',
  },
  inputRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginBottom: 8,
  },
  inputContainer: {
    flex: 1,
    marginBottom: 16,
    marginRight: 8,
    minWidth: 150,
  },
  label: {
    fontSize: 14,
    fontWeight: '500',
    marginBottom: 8,
    color: '#495057',
  },
  input: {
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#dee2e6',
    borderRadius: 4,
    paddingHorizontal: 12,
    paddingVertical: 8,
    fontSize: 16,
  },
  inputError: {
    borderColor: '#dc3545',
  },
  errorText: {
    color: '#dc3545',
    fontSize: 12,
    marginTop: 4,
  },
  logoSection: {
    marginBottom: 16,
  },
  logoContainer: {
    borderWidth: 2,
    borderColor: '#dee2e6',
    borderStyle: 'dashed',
    borderRadius: 8,
    padding: 16,
    alignItems: 'center',
    backgroundColor: '#f8f9fa',
  },
  logoPreview: {
    width: 150,
    height: 150,
    marginBottom: 16,
    borderRadius: 8,
  },
  logoPlaceholder: {
    width: 150,
    height: 150,
    borderRadius: 8,
    backgroundColor: '#e9ecef',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
  },
  logoActions: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginBottom: 8,
  },
  logoButton: {
    backgroundColor: '#0066cc', // Match FranchiseeLayout theme
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 8,
    paddingHorizontal: 16,
    borderRadius: 4,
    marginHorizontal: 4,
  },
  logoRemoveButton: {
    backgroundColor: '#dc3545',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 8,
    paddingHorizontal: 16,
    borderRadius: 4,
    marginHorizontal: 4,
  },
  logoButtonText: {
    color: '#fff',
    fontSize: 14,
    marginLeft: 4,
  },
  logoHelpText: {
    fontSize: 12,
    color: '#6c757d',
    textAlign: 'center',
    marginTop: 8,
  },
  lastUpdated: {
    fontSize: 12,
    color: '#6c757d',
    marginTop: 6, // Spacing below button
  },
  
  saveButton: {
    backgroundColor: '#0066cc',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    paddingHorizontal: 24,
    borderRadius: 4,
    marginBottom: 16,
  },
  
  saveButtonDisabled: {
    backgroundColor: '#99c2ff',
  },
  
  saveButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '500',
    marginLeft: 8,
  },
  loaderContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'transparent',
    paddingVertical: 50,
  },
  loaderText: {
    marginTop: 16,
    fontSize: 16,
    color: '#6c757d',
  },
  // Development mode toggle
  devModeContainer: {
    backgroundColor: '#f8f9fa',
    padding: 10,
    marginBottom: 24,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#dee2e6',
    borderStyle: 'dashed',
    alignItems: 'center',
  },
  devModeText: {
    fontSize: 12,
    color: '#6c757d',
    marginBottom: 8,
  },
  devModeToggle: {
    backgroundColor: '#e9ecef',
    paddingVertical: 6,
    paddingHorizontal: 16,
    borderRadius: 16,
    marginBottom: 8,
  },
  devModeToggleActive: {
    backgroundColor: '#28a745',
  },
  devModeToggleText: {
    fontSize: 12,
    fontWeight: 'bold',
    color: '#212529',
  },
  devModeHint: {
    fontSize: 10,
    color: '#6c757d',
    fontStyle: 'italic',
  },
  footer: {
    flexDirection: 'row',
    justifyContent: 'center', // Align to the right
    alignItems: 'flex-start',
    marginTop: 8,
    marginBottom: 24,
  },
  
  
});

export default ProfileEditScreen;