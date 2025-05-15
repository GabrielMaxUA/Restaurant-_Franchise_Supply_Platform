import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TextInput,
  TouchableOpacity,
  Image,
  ActivityIndicator,
  RefreshControl,
  SafeAreaView,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { getCatalog, toggleFavorite, addToCart } from '../services/api';

const CatalogScreen = ({ navigation }) => {
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [products, setProducts] = useState([]);
  const [filteredProducts, setFilteredProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [selectedCategory, setSelectedCategory] = useState(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [error, setError] = useState('');
  const [userToken, setUserToken] = useState('');

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
      loadCatalog();
    }
  }, [userToken]);

  useEffect(() => {
    filterProducts();
  }, [searchQuery, selectedCategory, products]);

  const loadCatalog = async () => {
    if (!userToken) return;

    try {
      setLoading(true);
      setError('');

      const catalogResponse = await getCatalog(userToken);
      console.log('Catalog response (full):', JSON.stringify(catalogResponse));
      
      // Adapt response format if needed (for Laravel standard responses)
      let processedResponse = { ...catalogResponse };
      
      // Check if we have a Laravel style response with data property
      if (catalogResponse.data && !catalogResponse.success) {
        console.log('Detected Laravel response format for catalog, adapting...');
        processedResponse.success = true;
        
        // Handle different possible Laravel response structures
        if (Array.isArray(catalogResponse.data)) {
          // If data is directly an array of products
          processedResponse.products = catalogResponse.data;
        } else if (catalogResponse.data.products) {
          // If data contains a products property
          processedResponse.products = catalogResponse.data.products;
        } else if (catalogResponse.data.data && Array.isArray(catalogResponse.data.data)) {
          // If data contains a nested data property (common in Laravel resources)
          processedResponse.products = catalogResponse.data.data;
        } else {
          // Fallback - use data as is
          processedResponse.products = [catalogResponse.data];
        }
      }

      if (!processedResponse.success) {
        throw new Error(processedResponse.error || catalogResponse.message || 'Failed to load catalog');
      }

      // Make sure we have an array of products
      const productsArray = processedResponse.products || [];
      console.log(`Found ${productsArray.length} products in response`);
      
      setProducts(productsArray);
      
      // Extract unique categories
      if (productsArray.length > 0) {
        const uniqueCategories = [...new Set(productsArray
          .map(product => product.category)
          .filter(category => category) // Remove null/undefined
        )];
        
        setCategories(uniqueCategories);
        console.log(`Found ${uniqueCategories.length} unique categories`);
      }
    } catch (error) {
      console.error('Catalog loading error:', error);
      setError('Failed to load catalog. Pull down to refresh.');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const filterProducts = () => {
    let filtered = [...products];
    
    // Filter by category
    if (selectedCategory) {
      filtered = filtered.filter(product => product.category === selectedCategory);
    }
    
    // Filter by search
    if (searchQuery.trim()) {
      const query = searchQuery.toLowerCase().trim();
      filtered = filtered.filter(product => 
        product.name.toLowerCase().includes(query) || 
        (product.description && product.description.toLowerCase().includes(query))
      );
    }
    
    setFilteredProducts(filtered);
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadCatalog();
  };

  const handleToggleFavorite = async (productId) => {
    if (!userToken) return;

    try {
      const response = await toggleFavorite(userToken, productId);
      
      if (response.success) {
        // Update local state to reflect the change
        setProducts(prevProducts => prevProducts.map(product => {
          if (product.id === productId) {
            return { ...product, is_favorite: !product.is_favorite };
          }
          return product;
        }));
      } else {
        console.error('Failed to toggle favorite:', response.error);
      }
    } catch (error) {
      console.error('Toggle favorite error:', error);
    }
  };

  const handleAddToCart = async (productId, quantity = 1) => {
    if (!userToken) return;

    try {
      const response = await addToCart(userToken, productId, null, quantity);
      
      if (response.success) {
        // Show success message or update UI
        alert('Product added to cart successfully!');
      } else {
        console.error('Failed to add to cart:', response.error);
        alert('Failed to add product to cart. Please try again.');
      }
    } catch (error) {
      console.error('Add to cart error:', error);
      alert('Network error. Please try again.');
    }
  };

  const formatCurrency = (amount) => {
    return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
  };

  const renderCategoryItem = ({ item }) => (
    <TouchableOpacity 
      style={[
        styles.categoryItem, 
        selectedCategory === item && styles.selectedCategoryItem
      ]}
      onPress={() => setSelectedCategory(selectedCategory === item ? null : item)}
    >
      <Text 
        style={[
          styles.categoryText,
          selectedCategory === item && styles.selectedCategoryText
        ]}
      >
        {item}
      </Text>
    </TouchableOpacity>
  );

  const renderProductItem = ({ item }) => (
    <View style={styles.productCard}>
      {item.image_url ? (
        <Image 
          source={{ uri: item.image_url }} 
          style={styles.productImage}
          resizeMode="cover"
        />
      ) : (
        <View style={styles.productImagePlaceholder}>
          <Text style={styles.productImagePlaceholderText}>No Image</Text>
        </View>
      )}
      
      <View style={styles.productHeader}>
        <Text style={styles.productName}>{item.name}</Text>
        <TouchableOpacity
          onPress={() => handleToggleFavorite(item.id)}
          style={styles.favoriteButton}
        >
          <Text style={styles.favoriteIcon}>
            {item.is_favorite ? '★' : '☆'}
          </Text>
        </TouchableOpacity>
      </View>
      
      {item.description && (
        <Text 
          style={styles.productDescription}
          numberOfLines={2}
        >
          {item.description}
        </Text>
      )}
      
      <View style={styles.productFooter}>
        <Text style={styles.productPrice}>
          {formatCurrency(item.price)}
        </Text>
        
        <TouchableOpacity
          style={styles.addToCartButton}
          onPress={() => handleAddToCart(item.id)}
        >
          <Text style={styles.addToCartButtonText}>Add to Cart</Text>
        </TouchableOpacity>
      </View>
    </View>
  );

  if (loading && !refreshing) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color="#0066cc" />
        <Text style={styles.loadingText}>Loading catalog...</Text>
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.searchContainer}>
        <TextInput
          style={styles.searchInput}
          placeholder="Search products..."
          value={searchQuery}
          onChangeText={setSearchQuery}
          clearButtonMode="while-editing"
        />
      </View>
      
      {/* Categories horizontal list */}
      {categories.length > 0 && (
        <View style={styles.categoriesContainer}>
          <FlatList
            horizontal
            data={categories}
            renderItem={renderCategoryItem}
            keyExtractor={(item) => item}
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.categoriesList}
          />
        </View>
      )}
      
      {error ? (
        <View style={styles.errorContainer}>
          <Text style={styles.errorText}>{error}</Text>
        </View>
      ) : null}
      
      <FlatList
        data={filteredProducts}
        renderItem={renderProductItem}
        keyExtractor={(item) => item.id.toString()}
        contentContainerStyle={styles.productsList}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>
              {searchQuery || selectedCategory
                ? 'No products match your search'
                : 'No products available'}
            </Text>
          </View>
        }
      />
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
  searchContainer: {
    padding: 15,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  searchInput: {
    backgroundColor: '#f0f0f0',
    padding: 10,
    borderRadius: 8,
    fontSize: 16,
  },
  categoriesContainer: {
    backgroundColor: '#fff',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  categoriesList: {
    paddingHorizontal: 15,
  },
  categoryItem: {
    paddingHorizontal: 15,
    paddingVertical: 8,
    marginRight: 10,
    borderRadius: 20,
    backgroundColor: '#f0f0f0',
  },
  selectedCategoryItem: {
    backgroundColor: '#0066cc',
  },
  categoryText: {
    fontSize: 14,
    color: '#333',
  },
  selectedCategoryText: {
    color: '#fff',
  },
  errorContainer: {
    margin: 15,
    padding: 10,
    backgroundColor: '#ffebee',
    borderRadius: 5,
  },
  errorText: {
    color: '#c62828',
  },
  productsList: {
    padding: 15,
  },
  productCard: {
    backgroundColor: '#fff',
    borderRadius: 10,
    marginBottom: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
    overflow: 'hidden',
  },
  productImage: {
    height: 150,
    width: '100%',
    backgroundColor: '#f0f0f0',
  },
  productImagePlaceholder: {
    height: 150,
    width: '100%',
    backgroundColor: '#f0f0f0',
    justifyContent: 'center',
    alignItems: 'center',
  },
  productImagePlaceholderText: {
    color: '#999',
  },
  productHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 15,
  },
  productName: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    flex: 1,
  },
  favoriteButton: {
    width: 30,
    height: 30,
    justifyContent: 'center',
    alignItems: 'center',
  },
  favoriteIcon: {
    fontSize: 24,
    color: '#ffc107',
  },
  productDescription: {
    fontSize: 14,
    color: '#666',
    paddingHorizontal: 15,
    marginBottom: 15,
  },
  productFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 15,
    borderTopWidth: 1,
    borderTopColor: '#eee',
  },
  productPrice: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  addToCartButton: {
    backgroundColor: '#0066cc',
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 5,
  },
  addToCartButtonText: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '500',
  },
  emptyContainer: {
    padding: 30,
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 16,
    color: '#999',
    textAlign: 'center',
  },
});

export default CatalogScreen;