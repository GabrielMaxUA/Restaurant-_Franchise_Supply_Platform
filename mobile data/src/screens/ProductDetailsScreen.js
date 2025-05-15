import React, { useState, useEffect } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  SafeAreaView, 
  ScrollView, 
  Image, 
  TouchableOpacity,
  ActivityIndicator
} from 'react-native';
import { FontAwesome5 } from 'react-native-vector-icons';
import Header from '../components/Header';
import { catalogService, cartService } from '../services/api';

const ProductDetailsScreen = ({ route, navigation }) => {
  const { productId } = route.params || {};
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [quantity, setQuantity] = useState(1);
  const [addingToCart, setAddingToCart] = useState(false);

  useEffect(() => {
    const fetchProductDetails = async () => {
      if (!productId) {
        navigation.goBack();
        return;
      }

      try {
        setLoading(true);
        // Assuming you have an API endpoint for product details
        // This would need to be implemented in your catalogService
        const response = await catalogService.getProductDetails(productId);
        if (response.success && response.data) {
          setProduct(response.data);
        } else {
          // Handle error case
          alert('Could not load product details');
          navigation.goBack();
        }
      } catch (error) {
        console.error('Error fetching product details:', error);
        alert('An error occurred while loading product details');
        navigation.goBack();
      } finally {
        setLoading(false);
      }
    };

    fetchProductDetails();
  }, [productId, navigation]);

  const handleIncreaseQuantity = () => {
    setQuantity(prev => prev + 1);
  };

  const handleDecreaseQuantity = () => {
    if (quantity > 1) {
      setQuantity(prev => prev - 1);
    }
  };

  const handleAddToCart = async () => {
    if (!product) return;

    try {
      setAddingToCart(true);
      const response = await cartService.addToCart(product.id, quantity);
      if (response.success) {
        alert('Product added to cart');
        navigation.navigate('Cart');
      } else {
        alert('Failed to add product to cart');
      }
    } catch (error) {
      console.error('Error adding to cart:', error);
      alert('An error occurred while adding to cart');
    } finally {
      setAddingToCart(false);
    }
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <Header title="Product Details" showBackButton={true} />
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#4e73df" />
          <Text style={styles.loadingText}>Loading product details...</Text>
        </View>
      </SafeAreaView>
    );
  }

  // For demonstration purposes, use placeholder data if product is not available
  const placeholderProduct = {
    id: productId || 1,
    name: 'Sample Product',
    description: 'This is a placeholder product description. The actual product details would appear here.',
    price: 99.99,
    unit_size: '1',
    unit_type: 'kg',
    image_url: null,
    in_stock: true,
  };

  const displayProduct = product || placeholderProduct;

  return (
    <SafeAreaView style={styles.container}>
      <Header title="Product Details" showBackButton={true} />
      <ScrollView style={styles.scrollView}>
        <View style={styles.productImageContainer}>
          {displayProduct.image_url ? (
            <Image 
              source={{ uri: displayProduct.image_url }} 
              style={styles.productImage}
              resizeMode="contain"
            />
          ) : (
            <View style={styles.imagePlaceholder}>
              <FontAwesome5 name="box" size={50} color="#ccc" />
            </View>
          )}
        </View>

        <View style={styles.productInfo}>
          <Text style={styles.productName}>{displayProduct.name}</Text>
          <View style={styles.priceRow}>
            <Text style={styles.productPrice}>
              ${displayProduct.price?.toFixed(2)}
            </Text>
            <Text style={styles.unitText}>
              per {displayProduct.unit_size} {displayProduct.unit_type}
            </Text>
          </View>

          {displayProduct.description && (
            <View style={styles.descriptionContainer}>
              <Text style={styles.sectionTitle}>Description</Text>
              <Text style={styles.descriptionText}>{displayProduct.description}</Text>
            </View>
          )}

          <View style={styles.quantityContainer}>
            <Text style={styles.sectionTitle}>Quantity</Text>
            <View style={styles.quantitySelector}>
              <TouchableOpacity 
                style={styles.quantityButton} 
                onPress={handleDecreaseQuantity}
                disabled={quantity <= 1}
              >
                <FontAwesome5 
                  name="minus" 
                  size={12} 
                  color={quantity <= 1 ? '#ccc' : '#333'} 
                />
              </TouchableOpacity>
              <Text style={styles.quantityText}>{quantity}</Text>
              <TouchableOpacity 
                style={styles.quantityButton} 
                onPress={handleIncreaseQuantity}
              >
                <FontAwesome5 name="plus" size={12} color="#333" />
              </TouchableOpacity>
            </View>
          </View>

          <TouchableOpacity 
            style={[
              styles.addToCartButton,
              (!displayProduct.in_stock || addingToCart) && styles.disabledButton
            ]}
            onPress={handleAddToCart}
            disabled={!displayProduct.in_stock || addingToCart}
          >
            {addingToCart ? (
              <ActivityIndicator color="#fff" size="small" />
            ) : (
              <>
                <FontAwesome5 name="cart-plus" size={16} color="#fff" style={styles.buttonIcon} />
                <Text style={styles.buttonText}>
                  {displayProduct.in_stock ? 'Add to Cart' : 'Out of Stock'}
                </Text>
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
  productImageContainer: {
    height: 250,
    backgroundColor: '#fff',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  productImage: {
    width: '100%',
    height: '100%',
  },
  imagePlaceholder: {
    width: '100%',
    height: '100%',
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f9f9f9',
  },
  productInfo: {
    padding: 16,
  },
  productName: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 8,
  },
  priceRow: {
    flexDirection: 'row',
    alignItems: 'baseline',
    marginBottom: 16,
  },
  productPrice: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#28a745',
    marginRight: 8,
  },
  unitText: {
    fontSize: 14,
    color: '#777',
  },
  descriptionContainer: {
    marginBottom: 16,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 8,
  },
  descriptionText: {
    fontSize: 14,
    color: '#555',
    lineHeight: 20,
  },
  quantityContainer: {
    marginBottom: 24,
  },
  quantitySelector: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 4,
    alignSelf: 'flex-start',
  },
  quantityButton: {
    padding: 12,
    borderWidth: 0,
    alignItems: 'center',
    justifyContent: 'center',
    width: 40,
  },
  quantityText: {
    paddingHorizontal: 16,
    fontSize: 16,
    fontWeight: 'bold',
  },
  addToCartButton: {
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
});

export default ProductDetailsScreen;