import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => ({
  plugins: [vue()],
  base: mode === 'production' ? '/bot_manager/' : '/',
  server: {
    proxy: {
      '/api': 'http://localhost:8000', // Proxy API requests to Symfony backend
    },
  },
}));

