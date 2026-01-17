<template>
  <div class="min-h-screen bg-gray-50 px-4 py-6">
    <h2 class="text-xl font-bold mb-4 text-gray-800">Admin Users</h2>
    <button
      @click="fetchAdmins"
      :disabled="loading"
      class="mb-4 px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 transition disabled:opacity-50"
    >
      Refresh
    </button>
    <div v-if="loading" class="text-gray-500 mb-2">Loading...</div>
    <div v-if="error" class="text-red-600 mb-2">Error: {{ error.message }}</div>
    <ul v-if="admins.length" class="space-y-2">
      <li
        v-for="admin in admins"
        :key="admin.id"
        class="p-4 bg-white rounded shadow flex flex-col sm:flex-row sm:items-center justify-between"
      >
        <span class="font-medium text-gray-700">{{ admin.admin_name }}</span>
        <span class="text-xs text-gray-400 mt-1 sm:mt-0">{{ admin.bot_code }}</span>
      </li>
    </ul>
    <div v-else-if="!loading" class="text-gray-500">No admins found.</div>
  </div>
</template>

<script lang="ts" setup>
import { onMounted } from 'vue';
import { useAdminUsers } from '../composables/useAdminUsers';

const { admins, loading, error, fetchAdmins } = useAdminUsers();
onMounted(fetchAdmins);
</script>
