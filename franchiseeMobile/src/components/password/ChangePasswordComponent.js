import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TextInput,
  TouchableOpacity,
  ActivityIndicator,
  Platform,
  KeyboardAvoidingView,
  Alert
} from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import Toast from 'react-native-toast-message';

// Import the BASE_URL 
import { BASE_URL } from '../services/axiosInstance';

/**
 * ChangePasswordComponent - A reusable component for password changing functionality
 * 
 * @param {Object} props - Component props
 * @param {Function} props.onSuccess - Callback function called on successful password change
 * @param {Function} props.onCancel - Callback function called when user cancels the operation
 * @param {boolean} props.isModal - Whether this component is being used within a modal
 * @param {Object} props.navigation - Navigation object (optional, used when component is a screen)
 */
const ChangePasswordComponent = ({
  onSuccess,
  onCancel,
  isModal = false,
  navigation = null
}) => {
  // State variables
  const [passwords, setPasswords] = useState({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
  });
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [passwordVisible, setPasswordVisible] = useState({
    current: false,
    new: false,
    confirm: false
  });

  // Handle form input changes
  const handleChange = (field, value) => {
    setPasswords({
      ...passwords,
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

  // Toggle password visibility
  const togglePasswordVisibility = (field) => {
    setPasswordVisible({
      ...passwordVisible,
      [field]: !passwordVisible[field]
    });
  };

  // Function to handle cancel/back
  const handleBack = () => {
    console.log('🔙 Handling back navigation');
    if (isModal && onCancel) {
      onCancel();
    } else if (navigation) {
      navigation.goBack();
    }
  };

  // Function to update password
  const updatePassword = async () => {
    try {
      console.log('🔐 Starting password update process');

      // Clear previous errors
      setErrors({});

      // Client-side validation
      if (!passwords.current_password) {
        setErrors({ current_password: ['Current password is required'] });
        return;
      }

      if (!passwords.new_password) {
        setErrors({ new_password: ['New password is required'] });
        return;
      }

      if (passwords.new_password.length < 8) {
        setErrors({ new_password: ['Password must be at least 8 characters'] });
        return;
      }

      if (passwords.new_password !== passwords.new_password_confirmation) {
        setErrors({ new_password_confirmation: ['Password confirmation does not match'] });
        return;
      }

      // Set loading state
      setLoading(true);

      // Get auth token
      const token = await AsyncStorage.getItem('userToken');
      if (!token) {
        console.error('⛔ No auth token found in AsyncStorage!');
        Alert.alert('Authentication Error', 'Please log in again.');

        // Navigate to login if available
        if (navigation) {
          navigation.navigate('Login');
        }
        return;
      }

      console.log('🔑 Auth token found for password update');
      console.log('🌐 Making request to:', `${BASE_URL}/franchisee/profile/password`);

      // Send API request
      const response = await fetch(`${BASE_URL}/franchisee/profile/password`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          current_password: passwords.current_password,
          new_password: passwords.new_password,
          new_password_confirmation: passwords.new_password_confirmation
        })
      });

      console.log('📊 Password Update API Status:', response.status);

      // Handle authentication issues
      if (response.status === 401) {
        console.error('🔒 Authentication error during password update');
        Toast.show({
          type: 'error',
          text1: 'Session Expired',
          text2: 'Please log in again'
        });

        if (navigation) {
          navigation.navigate('Login');
        }
        return;
      }

      // Parse response
      const data = await response.json();
      console.log('📡 Password Update API Response received');

      if (response.ok && data.success) {
        console.log('✅ Password updated successfully');

        // Reset form
        setPasswords({
          current_password: '',
          new_password: '',
          new_password_confirmation: ''
        });

        // Show success message
        Toast.show({
          type: 'success',
          text1: 'Success',
          text2: 'Password updated successfully'
        });

        // Call success callback or navigate back
        if (onSuccess) {
          setTimeout(() => {
            onSuccess();
          }, 1000);
        } else if (navigation) {
          setTimeout(() => {
            navigation.goBack();
          }, 1000);
        }
      } else {
        console.error('❌ Password update failed:', data.message || data.error || 'Unknown error');

        // Handle validation errors
        if (response.status === 422 && data.errors) {
          setErrors(data.errors);

          // Show the first validation error
          const firstError = Object.values(data.errors)[0][0];
          Toast.show({
            type: 'error',
            text1: 'Validation Error',
            text2: firstError
          });
        } else if (response.status === 400 || data.message) {
          // Handle case when current password is incorrect
          setErrors({ current_password: [data.message || 'Current password is incorrect'] });
          Toast.show({
            type: 'error',
            text1: 'Error',
            text2: data.message || 'Current password is incorrect'
          });
        } else {
          // Show general error
          Toast.show({
            type: 'error',
            text1: 'Error',
            text2: data.message || 'Failed to update password'
          });
        }
      }
    } catch (error) {
      console.error('🔥 Exception in updatePassword:', error);
      Toast.show({
        type: 'error',
        text1: 'Error',
        text2: 'Network error occurred'
      });
    } finally {
      setLoading(false);
    }
  };

  // Check if password meets requirements
  const meetsRequirement = (requirement) => {
    switch (requirement) {
      case 'uppercase':
        return /[A-Z]/.test(passwords.new_password);
      case 'lowercase':
        return /[a-z]/.test(passwords.new_password);
      case 'number':
        return /[0-9]/.test(passwords.new_password);
      case 'special':
        return /[!@#$%^&*(),.?":{}|<>]/.test(passwords.new_password);
      case 'length':
        return passwords.new_password.length >= 8;
      default:
        return false;
    }
  };

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === 'ios' ? 'padding' : null}
      style={styles.keyboardAvoidingView}
    >
      <ScrollView style={styles.scrollView}>
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity
            style={styles.backButton}
            onPress={handleBack}
          >
            <Icon name="arrow-back" size={24} color="#2c7be5" />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Change Password</Text>
        </View>

        <View style={styles.card}>
          <View style={styles.securityInfoContainer}>
            <Icon name="shield-checkmark-outline" size={40} color="#2c7be5" />
            <Text style={styles.securityInfoText}>
              Create a strong password that includes uppercase letters,
              lowercase letters, numbers, and special characters for better security.
            </Text>
          </View>

          {/* Password Form */}
          <View style={styles.formContainer}>
            {/* Current Password */}
            <View style={styles.inputContainer}>
              <Text style={styles.label}>Current Password</Text>
              <View style={styles.passwordInputContainer}>
                <TextInput
                  style={[
                    styles.input,
                    styles.passwordInput,
                    errors.current_password && styles.inputError
                  ]}
                  secureTextEntry={!passwordVisible.current}
                  value={passwords.current_password}
                  onChangeText={(text) => handleChange('current_password', text)}
                  placeholder="Enter current password"
                />
                <TouchableOpacity
                  style={styles.eyeIcon}
                  onPress={() => togglePasswordVisibility('current')}
                >
                  <Icon
                    name={passwordVisible.current ? "eye-off-outline" : "eye-outline"}
                    size={20}
                    color="#6c757d"
                  />
                </TouchableOpacity>
              </View>
              {errors.current_password && (
                <Text style={styles.errorText}>{errors.current_password[0]}</Text>
              )}
            </View>

            {/* New Password */}
            <View style={styles.inputContainer}>
              <Text style={styles.label}>New Password</Text>
              <View style={styles.passwordInputContainer}>
                <TextInput
                  style={[
                    styles.input,
                    styles.passwordInput,
                    errors.new_password && styles.inputError
                  ]}
                  secureTextEntry={!passwordVisible.new}
                  value={passwords.new_password}
                  onChangeText={(text) => handleChange('new_password', text)}
                  placeholder="Enter new password"
                />
                <TouchableOpacity
                  style={styles.eyeIcon}
                  onPress={() => togglePasswordVisibility('new')}
                >
                  <Icon
                    name={passwordVisible.new ? "eye-off-outline" : "eye-outline"}
                    size={20}
                    color="#6c757d"
                  />
                </TouchableOpacity>
              </View>
              {errors.new_password && (
                <Text style={styles.errorText}>{errors.new_password[0]}</Text>
              )}
            </View>

            {/* Password Requirements */}
            <View style={styles.passwordRequirements}>
              <Text style={styles.requirementTitle}>Password must contain:</Text>
              <View style={styles.requirementRow}>
                <Icon
                  name={meetsRequirement('uppercase') ? "checkmark-circle" : "ellipse-outline"}
                  size={16}
                  color={meetsRequirement('uppercase') ? "#28a745" : "#6c757d"}
                />
                <Text style={styles.requirementText}>At least one uppercase letter</Text>
              </View>
              <View style={styles.requirementRow}>
                <Icon
                  name={meetsRequirement('lowercase') ? "checkmark-circle" : "ellipse-outline"}
                  size={16}
                  color={meetsRequirement('lowercase') ? "#28a745" : "#6c757d"}
                />
                <Text style={styles.requirementText}>At least one lowercase letter</Text>
              </View>
              <View style={styles.requirementRow}>
                <Icon
                  name={meetsRequirement('number') ? "checkmark-circle" : "ellipse-outline"}
                  size={16}
                  color={meetsRequirement('number') ? "#28a745" : "#6c757d"}
                />
                <Text style={styles.requirementText}>At least one number</Text>
              </View>
              <View style={styles.requirementRow}>
                <Icon
                  name={meetsRequirement('special') ? "checkmark-circle" : "ellipse-outline"}
                  size={16}
                  color={meetsRequirement('special') ? "#28a745" : "#6c757d"}
                />
                <Text style={styles.requirementText}>At least one special character</Text>
              </View>
              <View style={styles.requirementRow}>
                <Icon
                  name={meetsRequirement('length') ? "checkmark-circle" : "ellipse-outline"}
                  size={16}
                  color={meetsRequirement('length') ? "#28a745" : "#6c757d"}
                />
                <Text style={styles.requirementText}>Minimum 8 characters</Text>
              </View>
            </View>

            {/* Confirm Password */}
            <View style={styles.inputContainer}>
              <Text style={styles.label}>Confirm New Password</Text>
              <View style={styles.passwordInputContainer}>
                <TextInput
                  style={[
                    styles.input,
                    styles.passwordInput,
                    errors.new_password_confirmation && styles.inputError
                  ]}
                  secureTextEntry={!passwordVisible.confirm}
                  value={passwords.new_password_confirmation}
                  onChangeText={(text) => handleChange('new_password_confirmation', text)}
                  placeholder="Confirm new password"
                />
                <TouchableOpacity
                  style={styles.eyeIcon}
                  onPress={() => togglePasswordVisibility('confirm')}
                >
                  <Icon
                    name={passwordVisible.confirm ? "eye-off-outline" : "eye-outline"}
                    size={20}
                    color="#6c757d"
                  />
                </TouchableOpacity>
              </View>
              {errors.new_password_confirmation && (
                <Text style={styles.errorText}>{errors.new_password_confirmation[0]}</Text>
              )}
              {passwords.new_password &&
                passwords.new_password_confirmation &&
                passwords.new_password !== passwords.new_password_confirmation && (
                  <Text style={styles.errorText}>Passwords do not match</Text>
                )}
            </View>

            {/* Buttons */}
            <View style={styles.buttonRow}>
              {/* Cancel Button (only in modal mode) */}
              {isModal && (
                <TouchableOpacity
                  style={styles.cancelButton}
                  onPress={handleBack}
                  disabled={loading}
                >
                  <Text style={styles.cancelButtonText}>Cancel</Text>
                </TouchableOpacity>
              )}

              {/* Update Button */}
              <TouchableOpacity
                style={[
                  styles.updateButton,
                  loading && styles.buttonDisabled,
                  isModal ? { flex: 1 } : { width: '100%' }
                ]}
                onPress={updatePassword}
                disabled={loading}
              >
                {loading ? (
                  <ActivityIndicator size="small" color="#fff" />
                ) : (
                  <>
                    <Icon name="lock-closed-outline" size={18} color="#fff" />
                    <Text style={styles.updateButtonText}>Update Password</Text>
                  </>
                )}
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  keyboardAvoidingView: {
    flex: 1,
  },
  scrollView: {
    flex: 1,
    padding: 16,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
  },
  backButton: {
    padding: 8,
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: '600',
    marginLeft: 8,
  },
  card: {
    backgroundColor: 'white',
    borderRadius: 8,
    padding: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  securityInfoContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#e8f4f8',
    padding: 16,
    borderRadius: 8,
    marginBottom: 24,
  },
  securityInfoText: {
    flex: 1,
    marginLeft: 16,
    color: '#495057',
    fontSize: 14,
    lineHeight: 20,
  },
  formContainer: {
    marginBottom: 16,
  },
  inputContainer: {
    marginBottom: 20,
  },
  label: {
    fontSize: 14,
    fontWeight: '500',
    marginBottom: 8,
    color: '#495057',
  },
  passwordInputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    position: 'relative',
  },
  input: {
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#dee2e6',
    borderRadius: 4,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 16,
  },
  passwordInput: {
    flex: 1,
    paddingRight: 40, // Space for eye icon
  },
  eyeIcon: {
    position: 'absolute',
    right: 12,
    height: '100%',
    justifyContent: 'center',
  },
  inputError: {
    borderColor: '#dc3545',
  },
  errorText: {
    color: '#dc3545',
    fontSize: 12,
    marginTop: 4,
  },
  passwordRequirements: {
    backgroundColor: '#f8f9fa',
    padding: 16,
    borderRadius: 8,
    marginBottom: 20,
  },
  requirementTitle: {
    fontSize: 14,
    fontWeight: '500',
    marginBottom: 8,
    color: '#495057',
  },
  requirementRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 6,
  },
  requirementText: {
    marginLeft: 8,
    fontSize: 13,
    color: '#6c757d',
  },
  buttonRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: 8,
  },
  updateButton: {
    backgroundColor: '#2c7be5',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    borderRadius: 4,
    flex: 3,
  },
  buttonDisabled: {
    backgroundColor: '#a9c6f0',
  },
  updateButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '500',
    marginLeft: 8,
  },
  cancelButton: {
    backgroundColor: '#f8f9fa',
    borderWidth: 1,
    borderColor: '#dee2e6',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    borderRadius: 4,
    marginRight: 10,
    flex: 1,
  },
  cancelButtonText: {
    color: '#495057',
    fontSize: 16,
    fontWeight: '500',
  },
});

export default ChangePasswordComponent;