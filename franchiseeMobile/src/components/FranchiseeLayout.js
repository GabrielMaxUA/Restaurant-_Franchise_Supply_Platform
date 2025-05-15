import React from 'react';
import { View, StyleSheet, TouchableOpacity, Text } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import Ionicons from 'react-native-vector-icons/Ionicons';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
const FranchiseeLayout = ({ title, children }) => {
  const navigation = useNavigation();

  return (
    <View style={styles.wrapper}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.navigate('Cart')} style={styles.iconButton}>
          <Ionicons name="cart-outline" size={24} color="#fff" />
          <View style={styles.badge}>
            <Text style={styles.badgeText}>3</Text>
          </View>
        </TouchableOpacity>

        <Text style={styles.title}>{title}</Text>

        <TouchableOpacity onPress={() => navigation.openDrawer?.()} style={styles.iconButton}>
          <MaterialIcons name="menu" size={26} color="#fff" />
        </TouchableOpacity>
      </View>

      <View style={styles.content}>{children}</View>
    </View>
  );
};

const styles = StyleSheet.create({
  wrapper: { flex: 1, backgroundColor: '#f5f5f5' },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#0066cc',
    padding: 15,
    justifyContent: 'space-between',
  },
  title: { color: '#fff', fontSize: 18, fontWeight: 'bold' },
  content: { flex: 1, padding: 16 },
  iconButton: { padding: 5 },
  badge: {
    position: 'absolute',
    top: -5,
    right: -5,
    backgroundColor: 'red',
    borderRadius: 8,
    paddingHorizontal: 5,
    paddingVertical: 2,
    zIndex: 10,
  },
  badgeText: { color: '#fff', fontSize: 10, fontWeight: 'bold' },
});

export default FranchiseeLayout;
