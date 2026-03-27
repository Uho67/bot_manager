<template>
  <div class="menu-page">
    <div class="menu-header">
      <h1 class="menu-title">Admin Panel</h1>
      <p class="menu-subtitle">Welcome, {{ user?.admin_name }}</p>
      <button @click="handleLogout" class="menu-logout-btn">
        Logout
      </button>
    </div>
    <ul class="menu-list">
      <li v-if="isSuperAdmin">
        <router-link to="/admin-users" class="menu-link-blue">Admin Users</router-link>
      </li>
      <li v-if="isSuperAdmin">
        <router-link to="/bots" class="menu-link-green">Bots</router-link>
      </li>
      <li v-if="user && (user.roles?.includes('ROLE_ADMIN') || user.roles?.includes('ROLE_SUPER_ADMIN'))">
        <router-link to="/configs" class="menu-link-indigo">Configs</router-link>
      </li>
      <li v-if="user && (user.roles?.includes('ROLE_ADMIN') || user.roles?.includes('ROLE_SUPER_ADMIN'))">
        <router-link to="/my-bots" class="menu-link-purple">My Bots</router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link to="/products" class="menu-link-pink">Products</router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link to="/categories" class="menu-link-orange">Categories</router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link to="/templates" class="menu-link-cyan">Templates</router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link to="/posts" class="menu-link-teal">Posts</router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link to="/users" class="menu-link-yellow">Users</router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link to="/mailout" class="menu-link-red">Mailout Status</router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link to="/sent-posts" class="menu-link-red">Sended Posts</router-link>
      </li>
      <li v-if="!isSuperAdmin && (!user?.roles || user?.roles.indexOf('ROLE_ADMIN') === -1)">
        <div class="empty-state">
          <p>No menu items available for your role.</p>
        </div>
      </li>
    </ul>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue';
import { useAuth } from '../composables/useAuth';
import { useRouter } from 'vue-router';

const { user, logout } = useAuth();
const router = useRouter();

const isSuperAdmin = computed(() => {
  if (!user.value?.roles) return false;
  return user.value.roles.indexOf('ROLE_SUPER_ADMIN') !== -1;
});

function handleLogout() {
  logout();
  router.push('/login');
}
</script>


