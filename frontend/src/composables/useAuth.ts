import { ref } from 'vue';
import api from '../api';

const user = ref<{ admin_name: string; bot_code: string; roles: string[] } | null>(null);

function getCookie(name: string): string | null {
  const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
  return match ? decodeURIComponent(match[2]) : null;
}

function loadUserFromCookies() {
  const token = getCookie('token');
  const admin_name = getCookie('admin_name');
  const bot_code = getCookie('bot_code');
  const roles = getCookie('roles');
  if (token && admin_name && bot_code && roles) {
    user.value = {
      admin_name,
      bot_code,
      roles: JSON.parse(roles),
    };
    api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  } else {
    user.value = null;
    delete api.defaults.headers.common['Authorization'];
  }
}

function logout() {
  document.cookie = 'token=; Max-Age=0; path=/';
  document.cookie = 'admin_name=; Max-Age=0; path=/';
  document.cookie = 'bot_code=; Max-Age=0; path=/';
  document.cookie = 'roles=; Max-Age=0; path=/';
  user.value = null;
  delete api.defaults.headers.common['Authorization'];
}

// Intercept 503 errors and logout
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 503) {
      logout();
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

function isSuperAdmin(): boolean {
  if (!user.value?.roles) return false;
  return user.value.roles.indexOf('ROLE_SUPER_ADMIN') !== -1;
}

export function useAuth() {
  return { user, loadUserFromCookies, logout, isSuperAdmin };
}

