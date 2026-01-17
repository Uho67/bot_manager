<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 px-4">
    <h1 class="text-2xl font-bold mb-8 text-gray-800">Admin Panel</h1>
    <ul class="w-full max-w-xs space-y-4">
      <li>
        <router-link
          to="/admin-users"
          class="block w-full text-center py-3 rounded-lg bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 transition"
        >
          Admin Users
        </router-link>
      </li>
    </ul>
  </div>
</template>

<script lang="ts" setup>
// No logic needed for static menu
</script>

<style scoped>
/* Add any additional scoped styles here if needed */
</style>
