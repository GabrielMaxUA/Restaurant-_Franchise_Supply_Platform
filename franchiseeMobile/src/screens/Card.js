import React from 'react';
import { View, StyleSheet } from 'react-native';
import PropTypes from 'prop-types';

/**
 * Card component for displaying content in a card-like container
 */
const Card = ({ children, style }) => {
  return (
    <View style={[styles.card, style]}>
      {children}
    </View>
  );
};

const styles = StyleSheet.create({
  card: {
    backgroundColor: '#ffffff',
    borderRadius: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    overflow: 'hidden',
  },
});

Card.propTypes = {
  children: PropTypes.node,
  style: PropTypes.object,
};

export default Card;
