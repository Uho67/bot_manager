import './index.css';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { useAuth } from './composables/useAuth';
import { i18n } from './i18n';

const { loadUserFromCookies } = useAuth();
loadUserFromCookies();

createApp(App).use(router).use(i18n).mount('#app');
