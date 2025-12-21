// src/services/api.js
import axios from 'axios';

// Configurar axios global para Sanctum
axios.defaults.baseURL = 'http://127.0.0.1:8000/api';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.withCredentials = true; // Necesario para Sanctum

const API = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  },
  withCredentials: true
});

// Interceptor para obtener CSRF cookie automáticamente
API.interceptors.request.use(async config => {
  // Obtener CSRF cookie antes de cada petición POST/PUT/PATCH/DELETE
  if (['post', 'put', 'patch', 'delete'].includes(config.method)) {
    try {
      await axios.get('http://127.0.0.1:8000/sanctum/csrf-cookie', { withCredentials: true });
    } catch (error) {
      console.warn('No se pudo obtener CSRF cookie:', error);
    }
  }
  return config;
}, error => Promise.reject(error));

// Interceptor para manejar errores de respuesta
API.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Token expirado o no autorizado
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default API;
