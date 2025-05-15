import React from 'react';
import { Text, View, StyleSheet } from 'react-native';

// Component that attempts to use icon fonts directly via unicode
const DirectIconTest = () => {
  return (
    <View style={styles.container}>
      <Text style={styles.title}>Direct Icon Test</Text>
      <Text style={styles.subtitle}>Testing icons using direct Unicode characters</Text>

      <View style={styles.row}>
        {/* Ionicons */}
        <View style={styles.iconContainer}>
          <Text style={[styles.iconText, { fontFamily: 'Ionicons' }]}>
            {/* Home icon in Ionicons */}
            &#xe908;
          </Text>
          <Text style={styles.label}>Ionicons</Text>
        </View>

        {/* FontAwesome */}
        <View style={styles.iconContainer}>
          <Text style={[styles.iconText, { fontFamily: 'FontAwesome' }]}>
            {/* Home icon in FontAwesome */}
            &#xf015;
          </Text>
          <Text style={styles.label}>FontAwesome</Text>
        </View>

        {/* MaterialIcons */}
        <View style={styles.iconContainer}>
          <Text style={[styles.iconText, { fontFamily: 'MaterialIcons' }]}>
            {/* Home icon in MaterialIcons */}
            &#xe88a;
          </Text>
          <Text style={styles.label}>MaterialIcons</Text>
        </View>
      </View>

      <View style={styles.alternativeWrapper}>
        <Text style={styles.sectionTitle}>Fallback Icons</Text>
        <Text style={styles.explanation}>
          If vector icons are not working, use these universal fallbacks:
        </Text>
        
        <View style={styles.row}>
          <View style={styles.iconContainer}>
            <Text style={styles.fallbackIcon}>🏠</Text>
            <Text style={styles.label}>Home</Text>
          </View>
          <View style={styles.iconContainer}>
            <Text style={styles.fallbackIcon}>📋</Text>
            <Text style={styles.label}>List</Text>
          </View>
          <View style={styles.iconContainer}>
            <Text style={styles.fallbackIcon}>🛒</Text>
            <Text style={styles.label}>Cart</Text>
          </View>
          <View style={styles.iconContainer}>
            <Text style={styles.fallbackIcon}>👤</Text>
            <Text style={styles.label}>User</Text>
          </View>
        </View>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#fff',
    borderRadius: 8,
    padding: 16,
    margin: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.2,
    shadowRadius: 1.5,
    elevation: 2,
  },
  title: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 4,
    textAlign: 'center',
  },
  subtitle: {
    fontSize: 14,
    color: '#666',
    marginBottom: 16,
    textAlign: 'center',
  },
  row: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    marginVertical: 16,
  },
  iconContainer: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  iconText: {
    fontSize: 24,
    color: '#333',
  },
  label: {
    marginTop: 8,
    fontSize: 12,
    color: '#666',
  },
  alternativeWrapper: {
    marginTop: 16,
    backgroundColor: '#f9f9f9',
    padding: 16,
    borderRadius: 8,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 8,
  },
  explanation: {
    fontSize: 14,
    color: '#666',
    marginBottom: 16,
  },
  fallbackIcon: {
    fontSize: 24,
  }
});

export default DirectIconTest;