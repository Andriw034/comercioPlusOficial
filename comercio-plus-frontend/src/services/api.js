// src/services/api.js
import axios from 'axios';

const API_BASE_URL = (import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api').replace(/\/$/, '');
const SANCTUM_URL = API_BASE_URL.replace(/\/api$/, '');

axios.defaults.baseURL = API_BASE_URL;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.withCredentials = true;

const API = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  withCredentials: true,
});

let csrfFetched = false;

API.interceptors.request.use(
  async (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    const method = (config.method || '').toLowerCase();
    if (!csrfFetched && ['post', 'put', 'patch', 'delete'].includes(method)) {
      try {
        await axios.get(`${SANCTUM_URL}/sanctum/csrf-cookie`, { withCredentials: true });
        csrfFetched = true;
      } catch (error) {
        console.warn('No se pudo obtener CSRF cookie:', error);
      }
    }
    return config;
  },
  (error) => Promise.reject(error),
);

API.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('user');
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  },
);

export default API;
