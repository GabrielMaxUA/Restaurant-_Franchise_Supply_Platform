// src/screens/PendingOrdersScreen.js
import React from 'react';
import { View, Text, StyleSheet } from 'react-native';

const PendingOrdersScreen = () => {
  return (
    <View style={styles.container}>
      <Text style={styles.text}>Pending Orders Screen</Text>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f8f9fc',
  },
  text: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
  },
});

export default PendingOrdersScreen;