// context/AuthContext.js
import React, { createContext, useState, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [userToken, setUserToken] = useState(null);
  const [loading, setLoading] = useState(true);

  const loadToken = async () => {
    const token = await AsyncStorage.getItem('userToken');
    setUserToken(token);
    setLoading(false);
  };

  useEffect(() => {
    loadToken();
  }, []);

  const login = async (token) => {
    await AsyncStorage.setItem('userToken', token);
    setUserToken(token);
  };

  const logout = async () => {
    await AsyncStorage.removeItem('userToken');
    await AsyncStorage.removeItem('userData');
    setUserToken(null);
  };

  return (
    <AuthContext.Provider value={{ userToken, setUserToken, loading, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
};
