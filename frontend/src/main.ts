import './index.css';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { useAuth } from './composables/useAuth';

const { loadUserFromCookies } = useAuth();
loadUserFromCookies();

createApp(App).use(router).mount('#app');
