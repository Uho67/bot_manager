<template>
  <div class="nav-dropdown">
    <div>
      <button @click="toggle" type="button" class="nav-toggle-btn" id="menu-button" aria-expanded="true" aria-haspopup="true">
        {{ t('nav.menu') }}
        <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.584l3.71-3.354a.75.75 0 111.02 1.1l-4.25 3.846a.75.75 0 01-1.02 0l-4.25-3.846a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>
    <div v-if="open" class="nav-menu">
      <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="menu-button">
        <button @click="goTo('/')" class="nav-menu-item" role="menuitem">{{ t('nav.main_page') }}</button>
        <button v-if="isSuperAdmin" @click="goTo('/admin-users')" class="nav-menu-item" role="menuitem">{{ t('nav.admin_users') }}</button>
        <button v-if="isSuperAdmin" @click="goTo('/bots')" class="nav-menu-item" role="menuitem">{{ t('nav.bots') }}</button>
        <button v-if="user && (user.roles?.includes('ROLE_ADMIN') || user.roles?.includes('ROLE_SUPER_ADMIN'))" @click="goTo('/configs')" class="nav-menu-item" role="menuitem">{{ t('nav.configs') }}</button>
        <button v-if="user && (user.roles?.includes('ROLE_ADMIN') || user.roles?.includes('ROLE_SUPER_ADMIN'))" @click="goTo('/my-bots')" class="nav-menu-item" role="menuitem">{{ t('nav.my_bots') }}</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/products')" class="nav-menu-item" role="menuitem">{{ t('nav.products') }}</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/categories')" class="nav-menu-item" role="menuitem">{{ t('nav.categories') }}</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/buttons')" class="nav-menu-item" role="menuitem">{{ t('nav.buttons') }}</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/templates')" class="nav-menu-item" role="menuitem">{{ t('nav.templates') }}</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/posts')" class="nav-menu-item" role="menuitem">{{ t('nav.posts') }}</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/users')" class="nav-menu-item" role="menuitem">{{ t('nav.users') }}</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/mailout')" class="nav-menu-item" role="menuitem">{{ t('nav.mailout_status') }}</button>
        <div class="border-t my-1"></div>
        <button v-if="user && (user.roles?.includes('ROLE_ADMIN') || user.roles?.includes('ROLE_SUPER_ADMIN'))" @click="handleCacheClean" :disabled="cacheCleaning" class="nav-menu-item-action" role="menuitem">
          {{ cacheCleaning ? t('nav.cleaning') : t('nav.cache_clean') }}
        </button>
        <div class="border-t my-1"></div>
        <!-- Language switcher -->
        <div class="px-4 py-2 flex items-center gap-2">
          <span class="text-xs text-gray-500">{{ t('nav.language') }}:</span>
          <button @click="changeLocale('en')" :class="currentLocale === 'en' ? 'font-bold text-blue-600' : 'text-gray-500'" class="text-sm hover:text-blue-600 transition-colors">EN</button>
          <span class="text-gray-300">|</span>
          <button @click="changeLocale('ru')" :class="currentLocale === 'ru' ? 'font-bold text-blue-600' : 'text-gray-500'" class="text-sm hover:text-blue-600 transition-colors">RU</button>
          <span class="text-gray-300">|</span>
          <button @click="changeLocale('uk')" :class="currentLocale === 'uk' ? 'font-bold text-blue-600' : 'text-gray-500'" class="text-sm hover:text-blue-600 transition-colors">UK</button>
        </div>
        <div class="border-t my-1"></div>
        <button @click="logoutAndGoLogin" class="nav-menu-item-danger" role="menuitem">{{ t('nav.logout') }}</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuth } from '../composables/useAuth';
import { setLocale } from '../i18n';
import api from '../api';

const { t, locale } = useI18n();
const { user, logout } = useAuth();
const router = useRouter();
const open = ref(false);
const cacheCleaning = ref(false);

const currentLocale = computed(() => locale.value);

const isSuperAdmin = computed(() => {
  if (!user.value?.roles) return false;
  return user.value.roles.indexOf('ROLE_SUPER_ADMIN') !== -1;
});

function toggle() {
  open.value = !open.value;
}

function goTo(path: string) {
  open.value = false;
  router.push(path);
}

function logoutAndGoLogin() {
  open.value = false;
  logout();
  router.push('/login');
}

function changeLocale(lang: string) {
  setLocale(lang);
}

async function handleCacheClean() {
  if (cacheCleaning.value) return;

  if (!confirm(t('cache.confirm_clean'))) {
    return;
  }

  cacheCleaning.value = true;
  open.value = false;

  try {
    const response = await api.post('/api/admin-user/cache-clean');
    alert(response.data.message || t('cache.cleaned_success'));
  } catch (error: any) {
    const errorMessage = error.response?.data?.error || error.response?.data?.message || t('common.error');
    alert(`${t('common.error')}: ${errorMessage}`);
  } finally {
    cacheCleaning.value = false;
  }
}

// Close dropdown on outside click
function handleClickOutside(event: MouseEvent) {
  const dropdown = document.getElementById('menu-button');
  if (dropdown && !dropdown.contains(event.target as Node)) {
    open.value = false;
  }
}

import { onMounted, onBeforeUnmount } from 'vue';
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});
onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
