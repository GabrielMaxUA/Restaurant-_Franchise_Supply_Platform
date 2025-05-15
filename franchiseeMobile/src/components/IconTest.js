import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import Ionicons from 'react-native-vector-icons/Ionicons';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import FontAwesome from 'react-native-vector-icons/FontAwesome';
import Entypo from 'react-native-vector-icons/Entypo';

// This component tests different icon families to verify what's working
const IconTest = () => {
  return (
    <View style={styles.container}>
      <Text style={styles.title}>Icon Test Component</Text>
      
      <View style={styles.row}>
        <View style={styles.iconContainer}>
          <Ionicons name="home" size={30} color="#000" />
          <Text style={styles.label}>Ionicons</Text>
        </View>
        
        <View style={styles.iconContainer}>
          <MaterialIcons name="home" size={30} color="#000" />
          <Text style={styles.label}>MaterialIcons</Text>
        </View>
      </View>
      
      <View style={styles.row}>
        <View style={styles.iconContainer}>
          <FontAwesome name="home" size={30} color="#000" />
          <Text style={styles.label}>FontAwesome</Text>
        </View>
        
        <View style={styles.iconContainer}>
          <Entypo name="home" size={30} color="#000" />
          <Text style={styles.label}>Entypo</Text>
        </View>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    padding: 20,
    backgroundColor: '#f0f0f0',
    marginVertical: 10,
    borderRadius: 8,
  },
  title: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 20,
    textAlign: 'center',
  },
  row: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    marginBottom: 20,
  },
  iconContainer: {
    alignItems: 'center',
  },
  label: {
    marginTop: 5,
    fontSize: 12,
  },
});

export default IconTest;