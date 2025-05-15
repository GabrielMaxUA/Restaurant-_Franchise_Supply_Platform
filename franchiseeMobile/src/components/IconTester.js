import React from 'react';
import { SafeAreaView, View, Text, StyleSheet, ScrollView } from 'react-native';
import Ionicons from 'react-native-vector-icons/Ionicons';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import FontAwesome from 'react-native-vector-icons/FontAwesome';
import FontAwesome5 from 'react-native-vector-icons/FontAwesome5';
import FontAwesome6 from 'react-native-vector-icons/FontAwesome6';
import Entypo from 'react-native-vector-icons/Entypo';
import Feather from 'react-native-vector-icons/Feather';
import AntDesign from 'react-native-vector-icons/AntDesign';

// This is a standalone screen to test all vector icon libraries
const IconTester = () => {
  return (
    <SafeAreaView style={styles.container}>
      <Text style={styles.header}>Icon Testing Page</Text>
      <Text style={styles.subheader}>Check which icon libraries are working</Text>
      
      <ScrollView>
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Ionicons</Text>
          <View style={styles.iconRow}>
            <IconItem name="home" lib="Ionicons" />
            <IconItem name="menu" lib="Ionicons" />
            <IconItem name="cart" lib="Ionicons" />
            <IconItem name="person" lib="Ionicons" />
          </View>
        </View>
        
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>MaterialIcons</Text>
          <View style={styles.iconRow}>
            <IconItem name="home" lib="MaterialIcons" />
            <IconItem name="menu" lib="MaterialIcons" />
            <IconItem name="shopping-cart" lib="MaterialIcons" />
            <IconItem name="person" lib="MaterialIcons" />
          </View>
        </View>
        
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>FontAwesome</Text>
          <View style={styles.iconRow}>
            <IconItem name="home" lib="FontAwesome" />
            <IconItem name="bars" lib="FontAwesome" />
            <IconItem name="shopping-cart" lib="FontAwesome" />
            <IconItem name="user" lib="FontAwesome" />
          </View>
        </View>
        
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>FontAwesome5</Text>
          <View style={styles.iconRow}>
            <IconItem name="home" lib="FontAwesome5" />
            <IconItem name="bars" lib="FontAwesome5" />
            <IconItem name="shopping-cart" lib="FontAwesome5" />
            <IconItem name="user" lib="FontAwesome5" />
          </View>
        </View>
        
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>FontAwesome6</Text>
          <View style={styles.iconRow}>
            <IconItem name="house" lib="FontAwesome6" />
            <IconItem name="bars" lib="FontAwesome6" />
            <IconItem name="cart-shopping" lib="FontAwesome6" />
            <IconItem name="user" lib="FontAwesome6" />
          </View>
        </View>
        
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Entypo</Text>
          <View style={styles.iconRow}>
            <IconItem name="home" lib="Entypo" />
            <IconItem name="menu" lib="Entypo" />
            <IconItem name="shopping-cart" lib="Entypo" />
            <IconItem name="user" lib="Entypo" />
          </View>
        </View>
        
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>AntDesign</Text>
          <View style={styles.iconRow}>
            <IconItem name="home" lib="AntDesign" />
            <IconItem name="menu-fold" lib="AntDesign" />
            <IconItem name="shoppingcart" lib="AntDesign" />
            <IconItem name="user" lib="AntDesign" />
          </View>
        </View>
        
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>MaterialCommunityIcons</Text>
          <View style={styles.iconRow}>
            <IconItem name="home" lib="MaterialCommunityIcons" />
            <IconItem name="menu" lib="MaterialCommunityIcons" />
            <IconItem name="cart" lib="MaterialCommunityIcons" />
            <IconItem name="account" lib="MaterialCommunityIcons" />
          </View>
        </View>
        
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Feather</Text>
          <View style={styles.iconRow}>
            <IconItem name="home" lib="Feather" />
            <IconItem name="menu" lib="Feather" />
            <IconItem name="shopping-cart" lib="Feather" />
            <IconItem name="user" lib="Feather" />
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
};

// Helper component for displaying each icon with its name
const IconItem = ({ name, lib }) => {
  const renderIcon = () => {
    switch(lib) {
      case 'Ionicons':
        return <Ionicons name={name} size={24} color="#333" />;
      case 'MaterialIcons':
        return <MaterialIcons name={name} size={24} color="#333" />;
      case 'FontAwesome':
        return <FontAwesome name={name} size={24} color="#333" />;
      case 'FontAwesome5':
        return <FontAwesome5 name={name} size={24} color="#333" />;
      case 'FontAwesome6':
        return <FontAwesome6 name={name} size={24} color="#333" />;
      case 'Entypo':
        return <Entypo name={name} size={24} color="#333" />;
      case 'AntDesign':
        return <AntDesign name={name} size={24} color="#333" />;
      case 'MaterialCommunityIcons':
        return <MaterialCommunityIcons name={name} size={24} color="#333" />;
      case 'Feather':
        return <Feather name={name} size={24} color="#333" />;
      default:
        return <Text>?</Text>;
    }
  };

  return (
    <View style={styles.iconItem}>
      {renderIcon()}
      <Text style={styles.iconName}>{name}</Text>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 16,
    backgroundColor: '#f5f5f5',
  },
  header: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 8,
    textAlign: 'center',
  },
  subheader: {
    fontSize: 16,
    color: '#666',
    marginBottom: 20,
    textAlign: 'center',
  },
  section: {
    marginBottom: 24,
    backgroundColor: 'white',
    borderRadius: 8,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 16,
    color: '#333',
  },
  iconRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  iconItem: {
    width: '23%',
    alignItems: 'center',
    marginBottom: 16,
    backgroundColor: '#f9f9f9',
    padding: 8,
    borderRadius: 4,
  },
  iconName: {
    marginTop: 4,
    fontSize: 12,
    color: '#666',
    textAlign: 'center',
  },
});

export default IconTester;