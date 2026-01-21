import {ref} from 'vue';
import api from '../api';

const user = ref<{ admin_name: string; bot_code: string; bot_identifier?: string; roles: string[] } | null>(null);
const token = ref<string | null>(null);

function getCookie(name: string): string | null {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? decodeURIComponent(match[2]) : null;
}

function loadUserFromCookies() {
    token.value = getCookie('token');
    const admin_name = getCookie('admin_name');
    const bot_code = getCookie('bot_code');
    const bot_identifier = getCookie('bot_identifier');
    const roles = getCookie('roles');
    if (token.value && admin_name && bot_code && roles) {
        user.value = {
            admin_name,
            bot_code,
            bot_identifier: bot_identifier || undefined,
            roles: JSON.parse(roles),
        };
        api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
    } else {
        user.value = null;
        token.value = null;
        delete api.defaults.headers.common['Authorization'];
    }
}

function logout() {
    document.cookie = 'token=; Max-Age=0; path=/';
    document.cookie = 'admin_name=; Max-Age=0; path=/';
    document.cookie = 'bot_code=; Max-Age=0; path=/';
    document.cookie = 'bot_identifier=; Max-Age=0; path=/';
    document.cookie = 'roles=; Max-Age=0; path=/';
    user.value = null;
    token.value = null;
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
    return {user, token, loadUserFromCookies, logout, isSuperAdmin};
}
