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
      <li v-if="!isSuperAdmin">
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
