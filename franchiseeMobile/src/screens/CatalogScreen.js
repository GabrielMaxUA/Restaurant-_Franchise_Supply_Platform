import React, { useState, useEffect, useCallback } from 'react';
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
  Alert,
  ScrollView,
  Dimensions,
  Modal,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { getCatalog, toggleFavorite, addToCart, getCart, getProductDetails } from '../services/api';
import { addToCartWithQuantityManagement } from '../utils/quantityManagement';
import { cartEventEmitter } from '../components/FranchiseeLayout';
import FallbackIcon from '../components/icon/FallbackIcon';
import FranchiseeLayout from '../components/FranchiseeLayout';
import AddToCartModal from '../components/AddToCartModal';
import { API_BASE_URL } from '../services/axiosInstance';

const { width } = Dimensions.get('window');
const cardWidth = (width / 2) - 15; // 2 cards per row with spacing

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
  const [showCategoryModal, setShowCategoryModal] = useState(false);
  const [showFilters, setShowFilters] = useState(false);
  const [sortOption, setSortOption] = useState('newest');
  const [showSortModal, setShowSortModal] = useState(false);
  const [showFilterModal, setShowFilterModal] = useState(false);
  const [favoritesOnly, setFavoritesOnly] = useState(false);
  
  // Product detail and add to cart modal state
  const [addToCartModalVisible, setAddToCartModalVisible] = useState(false);
  const [selectedProductId, setSelectedProductId] = useState(null);

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
  }, [userToken, selectedCategory, sortOption, favoritesOnly]);

  useEffect(() => {
    filterProducts();
  }, [searchQuery, products]);

  const loadCatalog = async () => {
    if (!userToken) return;

    try {
      setLoading(true);
      setError('');

      // Create filters object based on selected options
      const filters = {};
      if (selectedCategory) {
        filters.category = selectedCategory;
      }
      
      // Add sort option
      if (sortOption) {
        filters.sort = sortOption;
      }
      
      // Add favorites filter
      if (favoritesOnly) {
        filters.favorites = 1;
      }

      console.log('Fetching catalog with filters:', filters);
      const catalogResponse = await getCatalog(userToken, 1, filters);

      console.log('Catalog response structure:', JSON.stringify(catalogResponse, null, 2).substring(0, 200) + '...');

      if (!catalogResponse.success) {
        throw new Error(catalogResponse.message || 'Failed to load catalog');
      }

      // Extract products array from response structure
      let productsArray = [];
      if (catalogResponse.products && catalogResponse.products.data) {
        // If response has products.data structure (pagination)
        productsArray = catalogResponse.products.data;
      } else if (Array.isArray(catalogResponse.products)) {
        // If response has direct products array
        productsArray = catalogResponse.products;
      }

      console.log(`Loaded ${productsArray.length} products`);
      
      // Pre-process images to fix URLs
      const processImageUrl = (url) => {
        if (!url) return null;
        
        if (!url.startsWith('http')) {
          console.log(`Found a relative image URL: ${url}`);

          
          // If path starts with /storage (Laravel public storage)
          if (url.includes('/storage/') || url.includes('storage/')) {
            let storagePath = url;
            
            // Clean up the path to ensure proper format
            if (storagePath.startsWith('/')) {
              return `${API_BASE_URL}${storagePath}`;
            } else {
              return `${API_BASE_URL}/${storagePath}`;
            }
          } 
          // If path is a direct product-images path
          else if (url.includes('product-images')) {
            // Add the /storage/ prefix that Laravel's asset() would add
            return `${API_BASE_URL}/storage/${url.replace('product-images', 'product-images/')}`;
          }
          // For other relative URLs
          else {
            return `${API_BASE_URL}/${url}`;
          }
        }
        
        return url;
      };
      
      // Transform products to ensure they have all required fields
      const transformedProducts = productsArray.map(product => {
        // Process the image URL
        const processedImageUrl = processImageUrl(product.image_url);
        console.log(`Product ${product.id} image: ${product.image_url} → ${processedImageUrl}`);
        
        return {
          id: product.id,
          name: product.name || 'Unnamed Product',
          description: product.description || '',
          price: parseFloat(product.price || product.base_price || 0),
          image_url: processedImageUrl, // Use the fixed URL
          category: product.category || { name: 'Uncategorized' },
          is_favorite: product.is_favorite ? true : false,
          is_purchasable: product.is_purchasable !== undefined ? product.is_purchasable : true,
          stock_status: product.stock_status || 'in_stock',
          has_in_stock_variants: product.has_in_stock_variants || false,
          variants: product.variants || [],
          unit_size: product.unit_size || '',
          unit_type: product.unit_type || '',
          inventory_count: product.inventory_count || 0,
          total_variant_inventory: product.total_variant_inventory || 0
        };
      });

      setProducts(transformedProducts);
      setFilteredProducts(transformedProducts);

      // Extract categories
      if (catalogResponse.categories && Array.isArray(catalogResponse.categories)) {
        const categoryList = catalogResponse.categories.map(cat => 
          typeof cat === 'object' ? cat : { id: cat, name: cat }
        );
        setCategories(categoryList);
      }

    } catch (error) {
      console.error('Catalog loading error:', error);
      setError(`Failed to load catalog: ${error.message}. Pull down to refresh.`);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const filterProducts = () => {
    if (searchQuery.trim() === '') {
      setFilteredProducts(products);
      return;
    }
    
    const query = searchQuery.toLowerCase();
    const filtered = products.filter(product =>
      (product.name && product.name.toLowerCase().includes(query)) ||
      (product.description && product.description.toLowerCase().includes(query))
    );

    setFilteredProducts(filtered);
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadCatalog();
  };

  const handleToggleFavorite = async (productId) => {
    if (!userToken) return;
    try {
      console.log(`Attempting to toggle favorite for product ID: ${productId}`);
      const response = await toggleFavorite(userToken, productId);
      console.log('Toggle favorite response:', response);
      
      if (response.success) {
        // Update both products and filteredProducts arrays
        const updateProductList = (list) => list.map(p =>
          p.id === productId ? { ...p, is_favorite: !p.is_favorite } : p
        );
        
        setProducts(updateProductList(products));
        setFilteredProducts(updateProductList(filteredProducts));
        
        // If the user has "favorites only" filter on and is unfavoriting an item, it may need to
        // disappear from the list
        if (favoritesOnly && response.is_favorite === false) {
          setFilteredProducts(prev => prev.filter(p => p.id !== productId));
        }
        
        // Show a toast or alert
        const message = response.is_favorite 
          ? 'Product added to favorites' 
          : 'Product removed from favorites';
        Alert.alert(
          response.is_favorite ? 'Added to Favorites' : 'Removed from Favorites',
          message
        );
      } else {
        Alert.alert('Error', response.message || 'Failed to update favorites');
      }
    } catch (error) {
      console.error('Toggle favorite error:', error);
      Alert.alert('Error', 'Failed to update favorites');
    }
  };

  const handleAddToCart = async (productId, quantity = 1, directAdd = false) => {
    try {
      // If not direct add, show the product detail modal
      if (!directAdd) {
        // Just open the modal for adding to cart or viewing details
        showProductDetails(productId);
        return;
      }
      
      // Find the product in our list
      const product = products.find(p => p.id === productId);
      if (!product) {
        console.error(`Product with ID ${productId} not found`);
        return;
      }

      // Get user token
      const token = await AsyncStorage.getItem('userToken');
      if (!token) {
        Alert.alert('Error', 'You need to be logged in to add items to your cart');
        return;
      }

      // Get current cart to check if product is already there
      const cartResponse = await getCart(token);
      let currentCartItems = [];
      
      if (cartResponse.success) {
        if (cartResponse.cart_items && Array.isArray(cartResponse.cart_items)) {
          currentCartItems = cartResponse.cart_items;
        } else if (cartResponse.cart && cartResponse.cart.items) {
          currentCartItems = cartResponse.cart.items;
        }
      }

      // Use centralized quantity management
      await addToCartWithQuantityManagement({
        product,
        selectedVariant: null,
        quantity,
        cartItems: currentCartItems,
        userToken: token,
        addToCartAPI: addToCart,
        onSuccess: ({ response, actualQuantityAdded, productName, successMessage }) => {
          // Update cart count in the header
          if (response.items_count !== undefined) {
            cartEventEmitter.emit('cartUpdated', response.items_count);
          } else if (cartResponse.success) {
            // Refetch cart to get updated count
            getCart(token).then(updatedCartResponse => {
              if (updatedCartResponse.success && updatedCartResponse.items_count) {
                cartEventEmitter.emit('cartUpdated', updatedCartResponse.items_count);
              }
            });
          }
          
          // Show success alert
          Alert.alert('Added to Cart', successMessage);
        },
        onError: (errorMessage, title = 'Error') => {
          Alert.alert(title, errorMessage);
        }
      });
    } catch (error) {
      console.error('Error adding to cart:', error);
      Alert.alert('Error', 'Failed to add product to cart. Please try again.');
    }
  };
  
  // Show the product details modal
  const showProductDetails = (productId) => {
    setSelectedProductId(productId);
    setAddToCartModalVisible(true);
  };

  const getStockStatusInfo = (status) => {
    switch(status) {
      case 'in_stock':
        return { label: 'In Stock', color: '#198754', icon: 'check-circle' };
      case 'low_stock':
        return { label: 'Low Stock', color: '#ffc107', icon: 'exclamation-circle' };
      case 'variants_only':
        return { label: 'Variants Only', color: '#ffc107', icon: 'exclamation-circle' };
      case 'out_of_stock':
      default:
        return { label: 'Out of Stock', color: '#dc3545', icon: 'times-circle' };
    }
  };

  const renderProductCard = ({ item }) => {
    const stockStatus = getStockStatusInfo(item.stock_status);
    
    return (
      <TouchableOpacity 
        style={styles.card}
        onPress={() => showProductDetails(item.id)}
        activeOpacity={0.7}
      >
        <View style={styles.cardImageContainer}>
          {item.image_url ? (
            <Image 
              source={{ uri: item.image_url }}
              style={styles.cardImage}
              resizeMode="cover"
              onError={() => {
                console.log(`Image failed to load for product ${item.id}: ${item.image_url}`);
              }}
            />
          ) : (
            <View style={styles.noImageContainer}>
              <FallbackIcon name="image" iconType="FontAwesome" size={40} color="#cccccc" />
            </View>
          )}
          <TouchableOpacity 
            style={styles.favoriteButton}
            onPress={(e) => {
              e.stopPropagation();
              handleToggleFavorite(item.id);
            }}
          >
            <FallbackIcon 
              name="heart" 
              iconType="FontAwesome" 
              size={18} 
              color={item.is_favorite ? '#dc3545' : '#ffffff'} 
            />
          </TouchableOpacity>
        </View>
        
        <View style={styles.cardContent}>
          <Text style={styles.stockStatus} numberOfLines={1}>
            <FallbackIcon 
              name={stockStatus.icon} 
              iconType="FontAwesome" 
              size={12} 
              color={stockStatus.color} 
            /> {stockStatus.label}
          </Text>
          <Text style={styles.productName} numberOfLines={2}>{item.name}</Text>
          
          {/* Display unit size and type if available */}
          {(item.unit_size || item.unit_type) && (
            <Text style={styles.unitInfo}>{item.unit_size} {item.unit_type}</Text>
          )}
          
          {/* Display category */}
          <Text style={styles.productCategory} numberOfLines={1}>{item.category?.name}</Text>
          
          {/* Display price and variants info */}
          <Text style={styles.productPrice}>${parseFloat(item.price).toFixed(2)}</Text>
          
          {/* Variants information */}
          {item.has_in_stock_variants && (
            <View style={styles.variantsTag}>
              <FallbackIcon name="tags" iconType="FontAwesome" size={10} color="#ffc107" />
              <Text style={styles.variantsText}>
                {item.total_variant_inventory 
                  ? `${item.total_variant_inventory} in variants`
                  : 'Variants available'}
              </Text>
            </View>
          )}
          
          <TouchableOpacity
            style={[
              styles.addButton, 
              (!item.is_purchasable || item.stock_status === 'out_of_stock') && styles.disabledButton
            ]}
            disabled={!item.is_purchasable || item.stock_status === 'out_of_stock'}
            onPress={(e) => {
              e.stopPropagation(); // Prevent navigating to product detail
              showProductDetails(item.id);
            }}
          >
            <FallbackIcon name="cart-plus" iconType="FontAwesome" size={16} color="#ffffff" />
            <Text style={styles.addButtonText}>Add</Text>
          </TouchableOpacity>
        </View>
      </TouchableOpacity>
    );
  };

  const renderCategoryModal = () => (
    <Modal
      animationType="slide"
      transparent={true}
      visible={showCategoryModal}
      onRequestClose={() => setShowCategoryModal(false)}
    >
      <View style={styles.modalOverlay}>
        <View style={styles.modalContent}>
          <View style={styles.modalHeader}>
            <Text style={styles.modalTitle}>Select Category</Text>
            <TouchableOpacity onPress={() => setShowCategoryModal(false)}>
              <FallbackIcon name="times" iconType="FontAwesome" size={20} color="#000" />
            </TouchableOpacity>
          </View>
          
          <ScrollView style={styles.modalBody}>
            <TouchableOpacity 
              style={[styles.categoryItem, selectedCategory === null && styles.selectedCategory]}
              onPress={() => {
                setSelectedCategory(null);
                setShowCategoryModal(false);
              }}
            >
              <Text style={styles.categoryText}>All Categories</Text>
            </TouchableOpacity>
            
            {categories.map((category) => (
              <TouchableOpacity 
                key={category.id} 
                style={[
                  styles.categoryItem, 
                  selectedCategory === category.id && styles.selectedCategory
                ]}
                onPress={() => {
                  setSelectedCategory(category.id);
                  setShowCategoryModal(false);
                }}
              >
                <Text style={styles.categoryText}>{category.name}</Text>
              </TouchableOpacity>
            ))}
          </ScrollView>
        </View>
      </View>
    </Modal>
  );

  const renderSortModal = () => (
    <Modal
      animationType="slide"
      transparent={true}
      visible={showSortModal}
      onRequestClose={() => setShowSortModal(false)}
    >
      <View style={styles.modalOverlay}>
        <View style={styles.modalContent}>
          <View style={styles.modalHeader}>
            <Text style={styles.modalTitle}>Sort By</Text>
            <TouchableOpacity onPress={() => setShowSortModal(false)}>
              <FallbackIcon name="times" iconType="FontAwesome" size={20} color="#000" />
            </TouchableOpacity>
          </View>
          
          <ScrollView style={styles.modalBody}>
            {[
              { id: 'name_asc', label: 'Name (A-Z)' },
              { id: 'name_desc', label: 'Name (Z-A)' },
              { id: 'price_asc', label: 'Price (Low to High)' },
              { id: 'price_desc', label: 'Price (High to Low)' },
              { id: 'popular', label: 'Most Popular' },
            ].map((option) => (
              <TouchableOpacity 
                key={option.id} 
                style={[
                  styles.categoryItem, 
                  sortOption === option.id && styles.selectedCategory
                ]}
                onPress={() => {
                  setSortOption(option.id);
                  setShowSortModal(false);
                }}
              >
                <Text style={styles.categoryText}>{option.label}</Text>
              </TouchableOpacity>
            ))}
          </ScrollView>
        </View>
      </View>
    </Modal>
  );

  if (loading && !refreshing) {
    return (
      <View style={styles.loaderContainer}>
        <ActivityIndicator size="large" color="#0066cc" />
        <Text style={styles.loaderText}>Loading catalog...</Text>
      </View>
    );
  }

  return (
    <FranchiseeLayout title="Catalog">
      {/* Search Bar */}
      <View style={styles.searchBox}>
        <View style={styles.searchInputContainer}>
          <FallbackIcon name="search" iconType="FontAwesome" size={16} color="#666" style={styles.searchIcon} />
          <TextInput
            value={searchQuery}
            onChangeText={setSearchQuery}
            placeholder="Search products..."
            style={styles.searchInput}
            returnKeyType="search"
            clearButtonMode="while-editing"
          />
        </View>
      </View>

      {/* Filter Bar */}
      <View style={styles.filterBar}>
        <TouchableOpacity 
          style={styles.filterButton}
          onPress={() => setShowCategoryModal(true)}
        >
          <FallbackIcon name="tag" iconType="FontAwesome" size={14} color="#0066cc" />
          <Text style={styles.filterButtonText}>
            {selectedCategory ? 
              categories.find(c => c.id === selectedCategory)?.name || 'Category' : 
              'Category'}
          </Text>
          <FallbackIcon name="chevron-down" iconType="FontAwesome" size={12} color="#0066cc" />
        </TouchableOpacity>

        <TouchableOpacity 
          style={styles.filterButton}
          onPress={() => setShowSortModal(true)}
        >
          <FallbackIcon name="sort" iconType="FontAwesome" size={14} color="#0066cc" />
          <Text style={styles.filterButtonText}>Sort</Text>
          <FallbackIcon name="chevron-down" iconType="FontAwesome" size={12} color="#0066cc" />
        </TouchableOpacity>

        <TouchableOpacity 
          style={[styles.filterButton, favoritesOnly && styles.activeFilterButton]}
          onPress={() => setFavoritesOnly(!favoritesOnly)}
        >
          <FallbackIcon 
            name="heart" 
            iconType="FontAwesome" 
            size={14} 
            color={favoritesOnly ? "#dc3545" : "#0066cc"} 
          />
          <Text style={[
            styles.filterButtonText, 
            favoritesOnly && styles.activeFilterButtonText
          ]}>
            Favorites
          </Text>
        </TouchableOpacity>
      </View>

      {error ? (
        <View style={styles.errorContainer}>
          <Text style={styles.errorText}>{error}</Text>
          <TouchableOpacity style={styles.retryButton} onPress={onRefresh}>
            <Text style={styles.retryText}>Retry</Text>
          </TouchableOpacity>
        </View>
      ) : null}

      {loading && !refreshing ? (
        <View style={styles.loaderContainer}>
          <ActivityIndicator size="large" color="#0066cc" />
          <Text style={styles.loaderText}>Loading catalog...</Text>
        </View>
      ) : filteredProducts.length === 0 ? (
        <View style={styles.emptyContainer}>
          <FallbackIcon name="exclamation-circle" iconType="FontAwesome" size={50} color="#ccc" />
          <Text style={styles.emptyText}>No products found</Text>
          <Text style={styles.emptySubtext}>Try adjusting your filters or search terms</Text>
          <TouchableOpacity style={styles.resetButton} onPress={() => {
            setSearchQuery('');
            setSelectedCategory(null);
            setSortOption('newest');
            setFavoritesOnly(false);
            loadCatalog();
          }}>
            <Text style={styles.resetButtonText}>Reset Filters</Text>
          </TouchableOpacity>
        </View>
      ) : (
        <FlatList
          data={filteredProducts}
          keyExtractor={(item) => item.id.toString()}
          renderItem={renderProductCard}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
          contentContainerStyle={styles.productList}
          numColumns={2}
          columnWrapperStyle={styles.row}
        />
      )}

      {renderCategoryModal()}
      {renderSortModal()}
      
      {/* AddToCartModal for product details */}
      {addToCartModalVisible && selectedProductId && (
        <AddToCartModal
          visible={addToCartModalVisible}
          productId={selectedProductId}
          onClose={() => {
            setAddToCartModalVisible(false);
            setSelectedProductId(null);
          }}
        />
      )}
    </FranchiseeLayout>
  );
};

const styles = StyleSheet.create({
  container: { 
    flex: 1, 
    backgroundColor: '#f5f5f5' 
  },
  loaderContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#fff',
    padding: 20
  },
  loaderText: {
    marginTop: 10,
    color: '#0066cc'
  },
  searchBox: { 
    padding: 10,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#eee'
  },
  searchInputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f0f0f0',
    borderRadius: 8,
    paddingHorizontal: 10
  },
  searchIcon: {
    marginRight: 8
  },
  searchInput: { 
    flex: 1,
    height: 40,
    fontSize: 16
  },
  filterBar: {
    flexDirection: 'row',
    padding: 10,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
    justifyContent: 'space-between'
  },
  filterButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f0f0f0',
    borderRadius: 4,
    paddingVertical: 6,
    paddingHorizontal: 10,
    marginRight: 8
  },
  filterButtonText: {
    color: '#0066cc',
    marginHorizontal: 4,
    fontSize: 14
  },
  activeFilterButton: {
    backgroundColor: '#ffecef',
    borderWidth: 1,
    borderColor: '#ffc9d0'
  },
  activeFilterButtonText: {
    color: '#dc3545'
  },
  productList: {
    padding: 8
  },
  row: {
    justifyContent: 'space-between',
    marginBottom: 0
  },
  card: {
    width: cardWidth,
    backgroundColor: '#fff',
    borderRadius: 8,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
    overflow: 'hidden'
  },
  cardImageContainer: {
    position: 'relative',
    height: 140
  },
  cardImage: {
    width: '100%',
    height: '100%'
  },
  noImageContainer: {
    width: '100%',
    height: '100%',
    backgroundColor: '#f9f9f9',
    justifyContent: 'center',
    alignItems: 'center'
  },
  favoriteButton: {
    position: 'absolute',
    top: 8,
    right: 8,
    backgroundColor: 'rgba(0,0,0,0.3)',
    width: 32,
    height: 32,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center'
  },
  favoriteIcon: {
    // Additional styling if needed
  },
  cardContent: {
    padding: 10
  },
  stockStatus: {
    fontSize: 11,
    marginBottom: 4
  },
  productName: {
    fontSize: 14,
    fontWeight: 'bold',
    marginBottom: 4
  },
  productCategory: {
    fontSize: 12,
    color: '#666',
    marginBottom: 4
  },
  unitInfo: {
    fontSize: 12,
    color: '#888',
    marginBottom: 2
  },
  variantsTag: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255, 193, 7, 0.1)',
    paddingVertical: 2,
    paddingHorizontal: 6,
    borderRadius: 4,
    marginBottom: 6,
  },
  variantsText: {
    fontSize: 10,
    color: '#856404',
    marginLeft: 4
  },
  productPrice: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#0066cc',
    marginBottom: 8
  },
  addButton: {
    backgroundColor: '#28a745',
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 6,
    borderRadius: 4
  },
  addButtonText: {
    color: '#fff',
    marginLeft: 5,
    fontSize: 14,
    fontWeight: 'bold'
  },
  disabledButton: {
    backgroundColor: '#ccc'
  },
  errorContainer: {
    margin: 10,
    padding: 15,
    backgroundColor: '#ffe0e0',
    borderRadius: 8,
    alignItems: 'center'
  },
  errorText: {
    color: '#d32f2f',
    marginBottom: 10,
    textAlign: 'center'
  },
  retryButton: {
    backgroundColor: '#0066cc',
    paddingVertical: 8,
    paddingHorizontal: 20,
    borderRadius: 4
  },
  retryText: {
    color: '#fff',
    fontWeight: 'bold'
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20
  },
  emptyText: {
    fontSize: 18,
    color: '#666',
    marginTop: 10
  },
  emptySubtext: {
    fontSize: 14,
    color: '#999',
    marginTop: 5,
    textAlign: 'center'
  },
  resetButton: {
    marginTop: 20,
    backgroundColor: '#0066cc',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 4
  },
  resetButtonText: {
    color: '#fff',
    fontWeight: 'bold'
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'flex-end'
  },
  modalContent: {
    backgroundColor: '#fff',
    borderTopLeftRadius: 15,
    borderTopRightRadius: 15,
    maxHeight: '70%'
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#eee'
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: 'bold'
  },
  modalBody: {
    padding: 15
  },
  categoryItem: {
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#eee'
  },
  selectedCategory: {
    backgroundColor: '#e6f2ff'
  },
  categoryText: {
    fontSize: 16
  }
});

export default CatalogScreen;