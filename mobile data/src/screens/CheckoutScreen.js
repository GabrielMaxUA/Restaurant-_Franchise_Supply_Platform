import React, { useState, useEffect, useContext } from 'react';
import {
  View,
  Text,
  StyleSheet,
  SafeAreaView,
  ScrollView,
  TouchableOpacity,
  TextInput,
  Alert,
  ActivityIndicator
} from 'react-native';
import { FontAwesome5 } from 'react-native-vector-icons';
import Header from '../components/Header';
import { cartService } from '../services/api';
import { AuthContext } from '../contexts/AuthContext';

const CheckoutScreen = ({ navigation }) => {
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [cart, setCart] = useState(null);
  const [address, setAddress] = useState(null);
  const [notes, setNotes] = useState('');
  
  const { state, updateCartCount } = useContext(AuthContext);
  
  // Format currency
  const formatCurrency = (value) => {
    return '$' + parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
  };
  
  useEffect(() => {
    const loadCheckoutData = async () => {
      try {
        setLoading(true);
        // Get checkout information including cart and address
        const response = await cartService.getCheckout();
        
        if (response && response.success) {
          setCart(response.cart);
          setAddress(response.address);
        } else {
          Alert.alert(
            'Error',
            'Unable to load checkout information',
            [{ text: 'OK', onPress: () => navigation.goBack() }]
          );
        }
      } catch (error) {
        console.error('Error loading checkout:', error);
        Alert.alert(
          'Error',
          'An error occurred while loading checkout information',
          [{ text: 'OK', onPress: () => navigation.goBack() }]
        );
      } finally {
        setLoading(false);
      }
    };
    
    loadCheckoutData();
  }, [navigation]);
  
  const handlePlaceOrder = async () => {
    try {
      setSubmitting(true);
      
      const response = await cartService.placeOrder(notes);
      
      if (response && response.success) {
        // Update cart count to 0
        updateCartCount(0);
        
        // Show success message and navigate to order details
        Alert.alert(
          'Success',
          'Your order has been placed successfully!',
          [
            { 
              text: 'View Order', 
              onPress: () => navigation.navigate('OrderDetails', { orderId: response.order_id }) 
            }
          ]
        );
      } else {
        Alert.alert('Error', 'Failed to place order. Please try again.');
      }
    } catch (error) {
      console.error('Error placing order:', error);
      Alert.alert('Error', 'An error occurred while placing your order');
    } finally {
      setSubmitting(false);
    }
  };
  
  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <Header title="Checkout" showBackButton={true} />
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#4e73df" />
          <Text style={styles.loadingText}>Loading checkout information...</Text>
        </View>
      </SafeAreaView>
    );
  }
  
  // If no cart, show empty state
  if (!cart || !cart.items || cart.items.length === 0) {
    return (
      <SafeAreaView style={styles.container}>
        <Header title="Checkout" showBackButton={true} />
        <View style={styles.emptyContainer}>
          <FontAwesome5 name="shopping-cart" size={50} color="#ccc" style={styles.emptyIcon} />
          <Text style={styles.emptyText}>Your cart is empty</Text>
          <TouchableOpacity
            style={styles.continueShopping}
            onPress={() => navigation.navigate('Catalog')}
          >
            <Text style={styles.continueShoppingText}>Continue Shopping</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }
  
  return (
    <SafeAreaView style={styles.container}>
      <Header title="Checkout" showBackButton={true} />
      <ScrollView style={styles.scrollView}>
        {/* Delivery Address Section */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Delivery Address</Text>
          </View>
          
          <View style={styles.addressContainer}>
            {address ? (
              <>
                <Text style={styles.addressName}>{address.contact_name}</Text>
                <Text style={styles.addressText}>{address.address}</Text>
                <Text style={styles.addressText}>
                  {address.city}, {address.state} {address.postal_code}
                </Text>
              </>
            ) : (
              <Text style={styles.noAddressText}>No delivery address available</Text>
            )}
          </View>
        </View>
        
        {/* Order Summary Section */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Order Summary</Text>
            <Text style={styles.itemCount}>{cart.items.length} items</Text>
          </View>
          
          <View style={styles.orderItemsContainer}>
            {cart.items.map(item => (
              <View key={item.id} style={styles.orderItem}>
                <View style={styles.itemDetails}>
                  <Text style={styles.itemName}>{item.product.name}</Text>
                  <Text style={styles.itemQuantity}>{item.quantity} x {formatCurrency(item.product.price)}</Text>
                </View>
                <Text style={styles.itemTotal}>
                  {formatCurrency(item.quantity * item.product.price)}
                </Text>
              </View>
            ))}
          </View>
          
          <View style={styles.totalContainer}>
            <View style={styles.totalRow}>
              <Text style={styles.totalLabel}>Subtotal</Text>
              <Text style={styles.totalValue}>{formatCurrency(cart.total)}</Text>
            </View>
            <View style={styles.totalRow}>
              <Text style={styles.totalLabel}>Delivery Fee</Text>
              <Text style={styles.totalValue}>$0.00</Text>
            </View>
            <View style={styles.totalRow}>
              <Text style={styles.totalLabelBold}>Total</Text>
              <Text style={styles.totalValueBold}>{formatCurrency(cart.total)}</Text>
            </View>
          </View>
        </View>
        
        {/* Order Notes Section */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Order Notes</Text>
          </View>
          
          <TextInput
            style={styles.notesInput}
            placeholder="Add notes for your order (optional)"
            placeholderTextColor="#999"
            multiline
            numberOfLines={4}
            value={notes}
            onChangeText={setNotes}
          />
        </View>
        
        {/* Place Order Button */}
        <View style={styles.buttonContainer}>
          <TouchableOpacity
            style={[styles.placeOrderButton, submitting && styles.disabledButton]}
            onPress={handlePlaceOrder}
            disabled={submitting}
          >
            {submitting ? (
              <ActivityIndicator color="#fff" size="small" />
            ) : (
              <>
                <FontAwesome5 name="check-circle" size={16} color="#fff" style={styles.buttonIcon} />
                <Text style={styles.buttonText}>Place Order</Text>
              </>
            )}
          </TouchableOpacity>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  scrollView: {
    flex: 1,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 12,
    fontSize: 16,
    color: '#666',
  },
  section: {
    backgroundColor: '#fff',
    marginHorizontal: 16,
    marginTop: 16,
    borderRadius: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#f0f0f0',
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  itemCount: {
    fontSize: 14,
    color: '#666',
  },
  addressContainer: {
    padding: 16,
  },
  addressName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 4,
  },
  addressText: {
    fontSize: 14,
    color: '#555',
    marginBottom: 2,
  },
  noAddressText: {
    fontSize: 14,
    color: '#999',
    fontStyle: 'italic',
  },
  orderItemsContainer: {
    paddingHorizontal: 16,
  },
  orderItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#f0f0f0',
  },
  itemDetails: {
    flex: 1,
  },
  itemName: {
    fontSize: 14,
    color: '#333',
    marginBottom: 4,
  },
  itemQuantity: {
    fontSize: 12,
    color: '#666',
  },
  itemTotal: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#333',
  },
  totalContainer: {
    padding: 16,
  },
  totalRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  totalLabel: {
    fontSize: 14,
    color: '#666',
  },
  totalValue: {
    fontSize: 14,
    color: '#333',
  },
  totalLabelBold: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  totalValueBold: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#28a745',
  },
  notesInput: {
    padding: 16,
    height: 100,
    textAlignVertical: 'top',
    color: '#333',
  },
  buttonContainer: {
    padding: 16,
    marginBottom: 16,
  },
  placeOrderButton: {
    backgroundColor: '#28a745',
    borderRadius: 8,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 16,
  },
  disabledButton: {
    backgroundColor: '#aaa',
  },
  buttonIcon: {
    marginRight: 8,
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  emptyIcon: {
    marginBottom: 16,
  },
  emptyText: {
    fontSize: 18,
    color: '#666',
    marginBottom: 24,
  },
  continueShopping: {
    backgroundColor: '#4e73df',
    paddingVertical: 12,
    paddingHorizontal: 20,
    borderRadius: 8,
  },
  continueShoppingText: {
    color: '#fff',
    fontWeight: 'bold',
  },
});

export default CheckoutScreen;