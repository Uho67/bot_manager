import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: {
    'Content-Type': 'application/ld+json',
    'Accept': 'application/ld+json',
  },
});

// Add XDEBUG_SESSION to every request as query parameter
api.interceptors.request.use((config) => {
  config.params = {
    ...config.params,
    XDEBUG_SESSION_START: 'PHPSTORM'
  };
  return config;
});

export default api;

