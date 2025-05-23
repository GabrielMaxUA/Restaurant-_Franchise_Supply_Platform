/**
 * Centralized axios instance with authentication interceptors
 */
import axios from 'axios';
import { Platform } from 'react-native';
import { setupAxiosInterceptors } from './authService';

// Determine correct base URL based on platform
export const BASE_URL = Platform.OS === 'ios' 
  ? 'http://localhost:8000/api'   // For iOS simulator
  : 'http://10.0.2.2:8000/api';   // For Android emulator

// Uncomment below for physical device testing (replace with your machine's IP)
//export const BASE_URL = 'http://172.20.10.2:8000';
export const API_BASE_URL = 'http://127.0.0.1:8000'

// Create axios instance with base configuration
const instance = axios.create({
  baseURL: BASE_URL,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  },
  timeout: 15000 // 15 second timeout
});

// Setup authentication interceptors
setupAxiosInterceptors(instance);

export default instance;