<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 px-4">
    <div class="text-center mb-8">
      <h1 class="text-2xl font-bold text-gray-800">Admin Panel</h1>
      <p class="text-gray-600 mt-2">Welcome, {{ user?.admin_name }}</p>
      <button @click="handleLogout" class="mt-4 text-sm text-red-600 hover:text-red-700 underline">
        Logout
      </button>
    </div>
    <ul class="w-full max-w-xs space-y-4">
      <li v-if="isSuperAdmin">
        <router-link
          to="/admin-users"
          class="block w-full text-center py-3 rounded-lg bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 transition"
        >
          Admin Users
        </router-link>
      </li>
      <li v-if="isSuperAdmin">
        <router-link
          to="/bots"
          class="block w-full text-center py-3 rounded-lg bg-green-600 text-white font-semibold shadow hover:bg-green-700 transition"
        >
          Bots
        </router-link>
      </li>
      <li v-if="user && (user.roles?.includes('ROLE_ADMIN') || user.roles?.includes('ROLE_SUPER_ADMIN'))">
        <router-link
          to="/configs"
          class="block w-full text-center py-3 rounded-lg bg-indigo-600 text-white font-semibold shadow hover:bg-indigo-700 transition"
        >
          Configs
        </router-link>
      </li>
      <li v-if="user && (user.roles?.includes('ROLE_ADMIN') || user.roles?.includes('ROLE_SUPER_ADMIN'))">
        <router-link
          to="/my-bots"
          class="block w-full text-center py-3 rounded-lg bg-purple-600 text-white font-semibold shadow hover:bg-purple-700 transition"
        >
          My Bots
        </router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link
          to="/products"
          class="block w-full text-center py-3 rounded-lg bg-pink-600 text-white font-semibold shadow hover:bg-pink-700 transition"
        >
          Products
        </router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link
          to="/categories"
          class="block w-full text-center py-3 rounded-lg bg-orange-600 text-white font-semibold shadow hover:bg-orange-700 transition"
        >
          Categories
        </router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link
          to="/templates"
          class="block w-full text-center py-3 rounded-lg bg-cyan-600 text-white font-semibold shadow hover:bg-cyan-700 transition"
        >
          Templates
        </router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link
          to="/posts"
          class="block w-full text-center py-3 rounded-lg bg-teal-600 text-white font-semibold shadow hover:bg-teal-700 transition"
        >
          Posts
        </router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link
          to="/users"
          class="block w-full text-center py-3 rounded-lg bg-yellow-600 text-white font-semibold shadow hover:bg-yellow-700 transition"
        >
          Users
        </router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link
          to="/mailout"
          class="block w-full text-center py-3 rounded-lg bg-red-600 text-white font-semibold shadow hover:bg-red-700 transition"
        >
          Mailout Status
        </router-link>
      </li>
      <li v-if="!isSuperAdmin && (!user?.roles || user?.roles.indexOf('ROLE_ADMIN') === -1)">
        <div class="text-center text-gray-500">
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

<style scoped>
</style>
