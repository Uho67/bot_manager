<template>
  <div class="nav-dropdown">
    <div>
      <button @click="toggle" type="button" class="nav-toggle-btn" id="menu-button" aria-expanded="true" aria-haspopup="true">
        Menu
        <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.584l3.71-3.354a.75.75 0 111.02 1.1l-4.25 3.846a.75.75 0 01-1.02 0l-4.25-3.846a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>
    <div v-if="open" class="nav-menu">
      <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="menu-button">
        <button @click="goTo('/')" class="nav-menu-item" role="menuitem">Main Page</button>
        <button v-if="isSuperAdmin" @click="goTo('/admin-users')" class="nav-menu-item" role="menuitem">Admin Users</button>
        <button v-if="isSuperAdmin" @click="goTo('/bots')" class="nav-menu-item" role="menuitem">Bots</button>
        <button v-if="user && (user.roles?.includes('ROLE_ADMIN') || user.roles?.includes('ROLE_SUPER_ADMIN'))" @click="goTo('/configs')" class="nav-menu-item" role="menuitem">Configs</button>
        <button v-if="user && (user.roles?.includes('ROLE_ADMIN') || user.roles?.includes('ROLE_SUPER_ADMIN'))" @click="goTo('/my-bots')" class="nav-menu-item" role="menuitem">My Bots</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/products')" class="nav-menu-item" role="menuitem">Products</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/categories')" class="nav-menu-item" role="menuitem">Categories</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/buttons')" class="nav-menu-item" role="menuitem">Buttons</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/templates')" class="nav-menu-item" role="menuitem">Templates</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/posts')" class="nav-menu-item" role="menuitem">Posts</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/users')" class="nav-menu-item" role="menuitem">Users</button>
        <button v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1" @click="goTo('/mailout')" class="nav-menu-item" role="menuitem">Mailout Status</button>
        <div class="border-t my-1"></div>
        <button v-if="user && (user.roles?.includes('ROLE_ADMIN') || user.roles?.includes('ROLE_SUPER_ADMIN'))" @click="handleCacheClean" :disabled="cacheCleaning" class="nav-menu-item-action" role="menuitem">
          {{ cacheCleaning ? 'Cleaning...' : 'Cache Clean' }}
        </button>
        <div class="border-t my-1"></div>
        <button @click="logoutAndGoLogin" class="nav-menu-item-danger" role="menuitem">Logout</button>
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


