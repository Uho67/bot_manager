<template>
  <div class="relative inline-block text-left z-50">
    <div>
      <button @click="toggle" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none" id="menu-button" aria-expanded="true" aria-haspopup="true">
        Menu
        <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.584l3.71-3.354a.75.75 0 111.02 1.1l-4.25 3.846a.75.75 0 01-1.02 0l-4.25-3.846a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>
    <div v-if="open" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
      <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="menu-button">
        <button @click="goTo('/')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Main Page</button>
        <button v-if="isSuperAdmin" @click="goTo('/admin-users')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Admin Users</button>
        <button v-if="isSuperAdmin" @click="goTo('/bots')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Bots</button>
        <button v-if="user && (user.roles?.includes('ROLE_ADMIN') || user.roles?.includes('ROLE_SUPER_ADMIN'))" @click="goTo('/configs')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Configs</button>
        <button v-if="user && (user.roles?.includes('ROLE_ADMIN') || user.roles?.includes('ROLE_SUPER_ADMIN'))" @click="goTo('/my-bots')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">My Bots</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/products')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Products</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/categories')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Categories</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/buttons')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Buttons</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/templates')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Templates</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/posts')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Posts</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/users')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Users</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/mailout')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Mailout Status</button>
        <div class="border-t my-1"></div>
        <button v-if="user && (user.roles?.includes('ROLE_ADMIN') || user.roles?.includes('ROLE_SUPER_ADMIN'))" @click="handleCacheClean" :disabled="cacheCleaning" class="block w-full text-left px-4 py-2 text-sm bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed" role="menuitem">
          {{ cacheCleaning ? 'Cleaning...' : 'Cache Clean' }}
        </button>
        <div class="border-t my-1"></div>
        <button @click="logoutAndGoLogin" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-100" role="menuitem">Logout</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';
import api from '../api';

const { user, logout } = useAuth();
const router = useRouter();
const open = ref(false);
const cacheCleaning = ref(false);

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

async function handleCacheClean() {
  if (cacheCleaning.value) return;
  
  if (!confirm('Are you sure you want to clean the cache?')) {
    return;
  }

  cacheCleaning.value = true;
  open.value = false;

  try {
    const response = await api.post('/api/admin-user/cache-clean');
    alert(response.data.message || 'Cache cleaned successfully');
  } catch (error: any) {
    const errorMessage = error.response?.data?.error || error.response?.data?.message || 'Failed to clean cache';
    alert(`Error: ${errorMessage}`);
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

// Add/remove event listener
import { onMounted, onBeforeUnmount } from 'vue';
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});
onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
</style>

