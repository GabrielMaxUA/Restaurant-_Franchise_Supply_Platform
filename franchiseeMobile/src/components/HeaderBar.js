import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import FontAwesome5 from 'react-native-vector-icons/FontAwesome5';


const HeaderBar = ({ title, onCartPress, onMenuPress }) => {
  return (
    <View style={styles.header}>
      <TouchableOpacity onPress={onCartPress}>
        <FontAwesome5 name="shopping-cart" size={24} color="#000" />
      </TouchableOpacity>

      <Text style={styles.title}>{title}</Text>

      <TouchableOpacity onPress={onMenuPress}>
        <FontAwesome5 name="bars" size={22} color="#333" />
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  header: {
    backgroundColor: '#e8f4fc',
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 15,
    elevation: 4,
    borderBottomWidth: 1,
    borderColor: '#ccc',
  },
  title: {
    fontSize: 18,
    fontWeight: '600',
    color: '#333',
  },
});

export default HeaderBar;
