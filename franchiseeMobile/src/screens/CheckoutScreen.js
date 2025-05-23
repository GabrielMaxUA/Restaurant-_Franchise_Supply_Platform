import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  TextInput,
  Switch,
  Alert,
  SafeAreaView,
  ActivityIndicator,
  Platform,
  Image
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import FranchiseeLayout, { cartEventEmitter } from '../components/FranchiseeLayout';
import FallbackIcon from '../components/icon/FallbackIcon';
import { getCart, placeOrder, getProfileData } from '../services/api';

const CheckoutScreen = ({ navigation }) => {
  const [loading, setLoading] = useState(true);
  const [cartItems, setCartItems] = useState([]);
  const [cartTotal, setCartTotal] = useState(0);
  const [submitting, setSubmitting] = useState(false);
  
  // Form state
  const [useFranchiseAddress, setUseFranchiseAddress] = useState(false);
  const [shippingAddress, setShippingAddress] = useState('');
  const [shippingCity, setShippingCity] = useState('');
  const [shippingState, setShippingState] = useState('');
  const [shippingZip, setShippingZip] = useState('');
  const [notes, setNotes] = useState('');
  const [deliveryPreference, setDeliveryPreference] = useState('standard');
  const [deliveryDate, setDeliveryDate] = useState('');
  
  // Errors
  const [errors, setErrors] = useState({});
  
  // Calculated values
  const [shippingCost, setShippingCost] = useState(0);
  const [tax, setTax] = useState(0);
  const [finalTotal, setFinalTotal] = useState(0);

  useEffect(() => {
    loadCheckoutData();
  }, []);

  useEffect(() => {
    calculateTotals();
  }, [cartTotal, deliveryPreference]);

  const loadCheckoutData = async () => {
    try {
      setLoading(true);
      const token = await AsyncStorage.getItem('userToken');
      
      if (!token) {
        Alert.alert('Error', 'Authentication required. Please login again.');
        navigation.navigate('Login');
        return;
      }

      // Load cart data
      const cartResponse = await getCart(token);
      
      if (cartResponse.success) {
        let items = [];
        let total = 0;
        
        if (cartResponse.cart_items && Array.isArray(cartResponse.cart_items)) {
          items = cartResponse.cart_items;
          total = cartResponse.total || 0;
        }
        
        if (items.length === 0) {
          Alert.alert('Empty Cart', 'Your cart is empty. Please add items before checkout.', [
            { text: 'OK', onPress: () => navigation.navigate('Cart') }
          ]);
          return;
        }
        
        setCartItems(items);
        setCartTotal(total);
      } else {
        throw new Error(cartResponse.message || 'Failed to load cart');
      }
    } catch (error) {
      console.error('Error loading checkout data:', error);
      Alert.alert('Error', 'Failed to load checkout data. Please try again.');
      navigation.goBack();
    } finally {
      setLoading(false);
    }
  };

  const calculateTotals = () => {
    const taxAmount = cartTotal * 0.08; // 8% tax
    let shipping = 0;
    
    if (deliveryPreference === 'express') {
      shipping = 15.00;
    }
    
    setTax(taxAmount);
    setShippingCost(shipping);
    setFinalTotal(cartTotal + taxAmount + shipping);
  };

  const loadFranchiseAddress = async () => {
    try {
      const token = await AsyncStorage.getItem('userToken');
      const response = await getProfileData(token);
      
      if (response.success && response.profile) {
        const profile = response.profile;
        setShippingAddress(profile.address || '');
        setShippingCity(profile.city || '');
        setShippingState(profile.state || '');
        setShippingZip(profile.postal_code || '');
      } else {
        Alert.alert('Error', 'Could not retrieve your franchise address. Please enter it manually.');
        setUseFranchiseAddress(false);
      }
    } catch (error) {
      console.error('Error loading franchise address:', error);
      Alert.alert('Error', 'Could not retrieve your franchise address. Please enter it manually.');
      setUseFranchiseAddress(false);
    }
  };

  const handleUseFranchiseAddressToggle = (value) => {
    setUseFranchiseAddress(value);
    
    if (value) {
      loadFranchiseAddress();
    } else {
      // Clear the fields
      setShippingAddress('');
      setShippingCity('');
      setShippingState('');
      setShippingZip('');
    }
  };

  const validateForm = () => {
    const newErrors = {};
    
    if (!shippingAddress.trim()) {
      newErrors.shippingAddress = 'Shipping address is required';
    }
    
    if (!shippingCity.trim()) {
      newErrors.shippingCity = 'City is required';
    }
    
    if (!shippingState.trim()) {
      newErrors.shippingState = 'State is required';
    }
    
    if (!shippingZip.trim()) {
      newErrors.shippingZip = 'ZIP code is required';
    }
    
    if (deliveryPreference === 'scheduled' && !deliveryDate) {
      newErrors.deliveryDate = 'Please select a delivery date';
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

 // Replace the handlePlaceOrder function in your CheckoutScreen component

const handlePlaceOrder = async () => {
  if (!validateForm()) {
    Alert.alert('Validation Error', 'Please fill in all required fields.');
    return;
  }

  try {
    setSubmitting(true);
    
    const token = await AsyncStorage.getItem('userToken');
    
    if (!token) {
      Alert.alert('Error', 'Authentication required. Please login again.');
      navigation.navigate('Login');
      return;
    }
    
    const orderData = {
      shipping_address: shippingAddress,
      shipping_city: shippingCity,
      shipping_state: shippingState,
      shipping_zip: shippingZip,
      delivery_preference: deliveryPreference,
      delivery_date: deliveryDate || null,
      notes: notes
    };

    console.log('Placing order with data:', orderData);

    const response = await placeOrder(orderData, token);
    
    console.log('Place order response:', response);
    
    if (response.success) {
      // Clear cart count in header
      cartEventEmitter.emit('cartUpdated', 0);
      
      Alert.alert(
        'Order Placed Successfully!',
        `Your order #${response.order_number || response.order_id} has been placed successfully.\n\nOrder total: $${(response.total || finalTotal).toFixed(2)}`,
        [
          {
            text: 'View Orders',
            onPress: () => navigation.navigate('Orders')
          },
          {
            text: 'Continue Shopping',
            onPress: () => navigation.navigate('Catalog')
          }
        ]
      );
    } else {
      // Handle specific error cases
      let errorMessage = response.message || response.error || 'Failed to place order';
      
      if (response.details && Array.isArray(response.details)) {
        errorMessage += '\n\nDetails:\n' + response.details.join('\n');
      }
      
      console.error('Order placement failed:', response);
      Alert.alert('Order Failed', errorMessage);
    }
  } catch (error) {
    console.error('Error placing order:', error);
    Alert.alert('Error', error.message || 'Failed to place order. Please try again.');
  } finally {
    setSubmitting(false);
  }
};

  const formatCurrency = (amount) => {
    return '$' + (amount || 0).toFixed(2);
  };

  const renderCartItem = (item, index) => (
    <View key={index} style={styles.cartItem}>
      <View style={styles.cartItemContent}>
        <View style={styles.cartItemImageContainer}>
          {item.product.image_url ? (
            <Image 
              source={{ uri: item.product.image_url }} 
              style={styles.cartItemImage}
              resizeMode="cover"
            />
          ) : (
            <View style={styles.cartItemImagePlaceholder}>
              <FallbackIcon name="image" iconType="FontAwesome" size={20} color="#ccc" />
            </View>
          )}
        </View>
        
        <View style={styles.cartItemDetails}>
          <Text style={styles.cartItemName}>{item.product.name}</Text>
          {item.variant && (
            <Text style={styles.cartItemVariant}>{item.variant.name}</Text>
          )}
          <View style={styles.cartItemMeta}>
            <Text style={styles.cartItemQuantity}>Qty: {item.quantity}</Text>
            <Text style={styles.cartItemPrice}>
              {formatCurrency(item.price * item.quantity)}
            </Text>
          </View>
        </View>
      </View>
    </View>
  );

  if (loading) {
    return (
      <FranchiseeLayout title="Checkout">
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#28a745" />
          <Text style={styles.loadingText}>Loading checkout...</Text>
        </View>
      </FranchiseeLayout>
    );
  }

  return (
    <FranchiseeLayout title="Checkout">
      <SafeAreaView style={styles.container}>
        <ScrollView style={styles.scrollView} showsVerticalScrollIndicator={false}>
          {/* Shipping Information Card */}
          <View style={styles.card}>
            <View style={styles.cardHeader}>
              <Text style={styles.cardTitle}>Shipping Information</Text>
            </View>
            
            <View style={styles.cardContent}>
              {/* Use Franchise Address Toggle */}
              <View style={styles.switchContainer}>
                <Text style={styles.switchLabel}>Use my franchise address for shipping</Text>
                <Switch
                  value={useFranchiseAddress}
                  onValueChange={handleUseFranchiseAddressToggle}
                  trackColor={{ false: '#ccc', true: '#28a745' }}
                  thumbColor={useFranchiseAddress ? '#fff' : '#f4f3f4'}
                />
              </View>
              
              {/* Address Fields */}
              <View style={styles.inputContainer}>
                <Text style={styles.inputLabel}>Shipping Address *</Text>
                <TextInput
                  style={[
                    styles.textInput,
                    errors.shippingAddress && styles.inputError,
                    useFranchiseAddress && styles.inputReadOnly
                  ]}
                  value={shippingAddress}
                  onChangeText={setShippingAddress}
                  placeholder="Enter shipping address"
                  editable={!useFranchiseAddress}
                />
                {errors.shippingAddress && (
                  <Text style={styles.errorText}>{errors.shippingAddress}</Text>
                )}
              </View>
              
              <View style={styles.row}>
                <View style={styles.col}>
                  <Text style={styles.inputLabel}>City *</Text>
                  <TextInput
                    style={[
                      styles.textInput,
                      errors.shippingCity && styles.inputError,
                      useFranchiseAddress && styles.inputReadOnly
                    ]}
                    value={shippingCity}
                    onChangeText={setShippingCity}
                    placeholder="City"
                    editable={!useFranchiseAddress}
                  />
                  {errors.shippingCity && (
                    <Text style={styles.errorText}>{errors.shippingCity}</Text>
                  )}
                </View>
                
                <View style={styles.col}>
                  <Text style={styles.inputLabel}>State *</Text>
                  <TextInput
                    style={[
                      styles.textInput,
                      errors.shippingState && styles.inputError,
                      useFranchiseAddress && styles.inputReadOnly
                    ]}
                    value={shippingState}
                    onChangeText={setShippingState}
                    placeholder="State"
                    editable={!useFranchiseAddress}
                  />
                  {errors.shippingState && (
                    <Text style={styles.errorText}>{errors.shippingState}</Text>
                  )}
                </View>
              </View>
              
              <View style={styles.inputContainer}>
                <Text style={styles.inputLabel}>ZIP Code *</Text>
                <TextInput
                  style={[
                    styles.textInput,
                    errors.shippingZip && styles.inputError,
                    useFranchiseAddress && styles.inputReadOnly,
                    { width: '50%' }
                  ]}
                  value={shippingZip}
                  onChangeText={setShippingZip}
                  placeholder="ZIP Code"
                  keyboardType="numeric"
                  editable={!useFranchiseAddress}
                />
                {errors.shippingZip && (
                  <Text style={styles.errorText}>{errors.shippingZip}</Text>
                )}
              </View>
              
              {/* Order Notes */}
              <View style={styles.inputContainer}>
                <Text style={styles.inputLabel}>Order Notes (Optional)</Text>
                <TextInput
                  style={[styles.textInput, styles.textArea]}
                  value={notes}
                  onChangeText={setNotes}
                  placeholder="Any special instructions for your order..."
                  multiline
                  numberOfLines={3}
                />
              </View>
              
              {/* Delivery Preference */}
              <View style={styles.inputContainer}>
                <Text style={styles.inputLabel}>Delivery Preference</Text>
                
                {/* Standard Delivery Option */}
                <TouchableOpacity
                  style={[
                    styles.deliveryOption,
                    deliveryPreference === 'standard' && styles.deliveryOptionSelected
                  ]}
                  onPress={() => setDeliveryPreference('standard')}
                >
                  <View style={styles.radioContainer}>
                    <View style={[
                      styles.radioButton,
                      deliveryPreference === 'standard' && styles.radioButtonSelected
                    ]}>
                      {deliveryPreference === 'standard' && (
                        <View style={styles.radioButtonInner} />
                      )}
                    </View>
                  </View>
                  <View style={styles.deliveryOptionContent}>
                    <Text style={styles.deliveryOptionTitle}>Standard Delivery</Text>
                    <Text style={styles.deliveryOptionSubtitle}>3-5 business days • FREE</Text>
                  </View>
                </TouchableOpacity>
                
                {/* Express Delivery Option */}
                <TouchableOpacity
                  style={[
                    styles.deliveryOption,
                    deliveryPreference === 'express' && styles.deliveryOptionSelected
                  ]}
                  onPress={() => setDeliveryPreference('express')}
                >
                  <View style={styles.radioContainer}>
                    <View style={[
                      styles.radioButton,
                      deliveryPreference === 'express' && styles.radioButtonSelected
                    ]}>
                      {deliveryPreference === 'express' && (
                        <View style={styles.radioButtonInner} />
                      )}
                    </View>
                  </View>
                  <View style={styles.deliveryOptionContent}>
                    <Text style={styles.deliveryOptionTitle}>Express Delivery</Text>
                    <Text style={styles.deliveryOptionSubtitle}>1-2 business days • +$15.00</Text>
                  </View>
                </TouchableOpacity>
                
                {/* Scheduled Delivery Option */}
                <TouchableOpacity
                  style={[
                    styles.deliveryOption,
                    deliveryPreference === 'scheduled' && styles.deliveryOptionSelected
                  ]}
                  onPress={() => setDeliveryPreference('scheduled')}
                >
                  <View style={styles.radioContainer}>
                    <View style={[
                      styles.radioButton,
                      deliveryPreference === 'scheduled' && styles.radioButtonSelected
                    ]}>
                      {deliveryPreference === 'scheduled' && (
                        <View style={styles.radioButtonInner} />
                      )}
                    </View>
                  </View>
                  <View style={styles.deliveryOptionContent}>
                    <Text style={styles.deliveryOptionTitle}>Scheduled Delivery</Text>
                    <Text style={styles.deliveryOptionSubtitle}>Choose a specific date</Text>
                  </View>
                </TouchableOpacity>
              </View>
              
              {/* Delivery Date (conditional) */}
              {deliveryPreference === 'scheduled' && (
                <View style={styles.inputContainer}>
                  <Text style={styles.inputLabel}>Preferred Delivery Date *</Text>
                  <TextInput
                    style={[styles.textInput, errors.deliveryDate && styles.inputError]}
                    value={deliveryDate}
                    onChangeText={setDeliveryDate}
                    placeholder="YYYY-MM-DD"
                  />
                  {errors.deliveryDate && (
                    <Text style={styles.errorText}>{errors.deliveryDate}</Text>
                  )}
                  <Text style={styles.helperText}>
                    Please enter date in YYYY-MM-DD format (minimum 3 days from today)
                  </Text>
                </View>
              )}
            </View>
          </View>
          
          {/* Order Summary Card */}
          <View style={styles.card}>
            <View style={styles.cardHeader}>
              <Text style={styles.cardTitle}>Order Summary</Text>
            </View>
            
            <View style={styles.orderSummary}>
              <Text style={styles.summarySubtitle}>Items ({cartItems.length})</Text>
              
              {cartItems.map((item, index) => renderCartItem(item, index))}
              
              {/* Totals */}
              <View style={styles.totalsContainer}>
                <View style={styles.totalRow}>
                  <Text style={styles.totalLabel}>Subtotal:</Text>
                  <Text style={styles.totalValue}>{formatCurrency(cartTotal)}</Text>
                </View>
                
                <View style={styles.totalRow}>
                  <Text style={styles.totalLabel}>Shipping:</Text>
                  <Text style={styles.totalValue}>{formatCurrency(shippingCost)}</Text>
                </View>
                
                <View style={styles.totalRow}>
                  <Text style={styles.totalLabel}>Tax (8%):</Text>
                  <Text style={styles.totalValue}>{formatCurrency(tax)}</Text>
                </View>
                
                <View style={styles.separator} />
                
                <View style={styles.totalRow}>
                  <Text style={styles.totalLabelFinal}>Total:</Text>
                  <Text style={styles.totalValueFinal}>{formatCurrency(finalTotal)}</Text>
                </View>
              </View>
            </View>
          </View>
          
          {/* Delivery Information */}
          <View style={styles.card}>
            <View style={styles.cardHeader}>
              <Text style={styles.cardTitle}>Delivery Information</Text>
            </View>
            <View style={styles.cardContent}>
              <View style={styles.infoRow}>
                <FallbackIcon name="info-circle" iconType="FontAwesome" size={16} color="#28a745" />
                <Text style={styles.infoText}>Orders are typically delivered within 3-5 business days.</Text>
              </View>
              <View style={styles.infoRow}>
                <FallbackIcon name="truck" iconType="FontAwesome" size={16} color="#28a745" />
                <Text style={styles.infoText}>Free standard shipping on all orders.</Text>
              </View>
              <View style={styles.infoRow}>
                <FallbackIcon name="phone" iconType="FontAwesome" size={16} color="#28a745" />
                <Text style={styles.infoText}>For delivery questions, contact support@restaurantsupply.com</Text>
              </View>
            </View>
          </View>
        </ScrollView>
        
        {/* Action Buttons */}
        <View style={styles.actionButtons}>
          <TouchableOpacity
            style={styles.backButton}
            onPress={() => navigation.goBack()}
          >
            <FallbackIcon name="arrow-left" iconType="FontAwesome" size={16} color="#6c757d" />
            <Text style={styles.backButtonText}>Back to Cart</Text>
          </TouchableOpacity>
          
          <TouchableOpacity
            style={[styles.placeOrderButton, submitting && styles.buttonDisabled]}
            onPress={handlePlaceOrder}
            disabled={submitting}
          >
            {submitting ? (
              <ActivityIndicator size="small" color="#fff" />
            ) : (
              <>
                <FallbackIcon name="check-circle" iconType="FontAwesome" size={16} color="#fff" />
                <Text style={styles.placeOrderButtonText}>Place Order</Text>
              </>
            )}
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    </FranchiseeLayout>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8f9fa',
  },
  loadingContainer: {
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
  scrollView: {
    flex: 1,
    padding: 15,
  },
  card: {
    backgroundColor: '#fff',
    borderRadius: 10,
    marginBottom: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
  },
  cardHeader: {
    padding: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#e9ecef',
  },
  cardTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
  },
  cardContent: {
    padding: 15,
  },
  switchContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 20,
    paddingVertical: 10,
  },
  switchLabel: {
    fontSize: 16,
    color: '#333',
    flex: 1,
    marginRight: 10,
  },
  inputContainer: {
    marginBottom: 15,
  },
  inputLabel: {
    fontSize: 14,
    fontWeight: '500',
    color: '#333',
    marginBottom: 5,
  },
  textInput: {
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    backgroundColor: '#fff',
  },
  textArea: {
    height: 80,
    textAlignVertical: 'top',
  },
  inputError: {
    borderColor: '#dc3545',
  },
  inputReadOnly: {
    backgroundColor: '#f8f9fa',
    color: '#666',
  },
  errorText: {
    color: '#dc3545',
    fontSize: 12,
    marginTop: 5,
  },
  helperText: {
    color: '#6c757d',
    fontSize: 12,
    marginTop: 5,
  },
  row: {
    flexDirection: 'row',
    marginHorizontal: -7.5,
  },
  col: {
    flex: 1,
    paddingHorizontal: 7.5,
  },
  pickerContainer: {
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    backgroundColor: '#fff',
  },
  picker: {
    height: 50,
  },
  deliveryOption: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 15,
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    backgroundColor: '#fff',
    marginBottom: 10,
  },
  deliveryOptionSelected: {
    borderColor: '#28a745',
    backgroundColor: '#f8fff9',
  },
  radioContainer: {
    marginRight: 12,
  },
  radioButton: {
    width: 20,
    height: 20,
    borderRadius: 10,
    borderWidth: 2,
    borderColor: '#ddd',
    justifyContent: 'center',
    alignItems: 'center',
  },
  radioButtonSelected: {
    borderColor: '#28a745',
  },
  radioButtonInner: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: '#28a745',
  },
  deliveryOptionContent: {
    flex: 1,
  },
  deliveryOptionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 2,
  },
  deliveryOptionSubtitle: {
    fontSize: 14,
    color: '#666',
  },
  orderSummary: {
    padding: 15,
    backgroundColor: '#f8f9fa',
  },
  summarySubtitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 15,
  },
  cartItem: {
    borderBottomWidth: 1,
    borderBottomColor: '#e9ecef',
    paddingVertical: 12,
  },
  cartItemContent: {
    flexDirection: 'row',
  },
  cartItemImageContainer: {
    marginRight: 12,
  },
  cartItemImage: {
    width: 60,
    height: 60,
    borderRadius: 8,
  },
  cartItemImagePlaceholder: {
    width: 60,
    height: 60,
    borderRadius: 8,
    backgroundColor: '#f0f0f0',
    justifyContent: 'center',
    alignItems: 'center',
  },
  cartItemDetails: {
    flex: 1,
  },
  cartItemName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 2,
  },
  cartItemVariant: {
    fontSize: 14,
    color: '#6c757d',
    marginBottom: 4,
  },
  cartItemMeta: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  cartItemQuantity: {
    fontSize: 14,
    color: '#6c757d',
  },
  cartItemPrice: {
    fontSize: 16,
    fontWeight: '600',
    color: '#28a745',
  },
  totalsContainer: {
    marginTop: 20,
  },
  totalRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  totalLabel: {
    fontSize: 14,
    color: '#333',
  },
  totalValue: {
    fontSize: 14,
    color: '#333',
  },
  totalLabelFinal: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  totalValueFinal: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  separator: {
    height: 1,
    backgroundColor: '#e9ecef',
    marginVertical: 10,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 10,
  },
  infoText: {
    fontSize: 14,
    color: '#333',
    marginLeft: 10,
    flex: 1,
  },
  actionButtons: {
    flexDirection: 'row',
    padding: 15,
    backgroundColor: '#fff',
    borderTopWidth: 1,
    borderTopColor: '#e9ecef',
  },
  backButton: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 20,
    borderWidth: 1,
    borderColor: '#6c757d',
    borderRadius: 8,
    marginRight: 10,
  },
  backButtonText: {
    fontSize: 16,
    color: '#6c757d',
    marginLeft: 8,
  },
  placeOrderButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#28a745',
    paddingVertical: 12,
    paddingHorizontal: 20,
    borderRadius: 8,
  },
  placeOrderButtonText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#fff',
    marginLeft: 8,
  },
  buttonDisabled: {
    opacity: 0.6,
  },
});

export default CheckoutScreen;