import AsyncStorage from '@react-native-async-storage/async-storage';
import axios from 'axios';

// Update this to your Laravel server IP when testing on a physical device
// For Android emulator use: http://10.0.2.2:8000
// For Expo Go on device: use your machine's LAN IP e.g. http://192.168.x.x:8000
export const BASE_URL = 'http://192.168.254.122:8000';
const API_URL = `${BASE_URL}/api`;

const api = axios.create({
    baseURL: API_URL,
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    timeout: 15000,
});

api.interceptors.request.use(async (config) => {
    const token = await AsyncStorage.getItem('token');
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
});

api.interceptors.response.use(
    (res) => res,
    (err) => {
        const msg = err.response?.data?.message || err.message || 'Something went wrong';
        return Promise.reject(new Error(msg));
    }
);

export default api;
