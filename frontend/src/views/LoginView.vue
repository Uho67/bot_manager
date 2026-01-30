<template>
  <div class="flex flex-col min-h-screen justify-center items-center bg-gray-100 p-4">
    <form @submit.prevent="onLogin" class="w-full max-w-xs bg-white rounded shadow p-6 flex flex-col gap-4">
      <h1 class="text-xl font-bold text-center">Admin Login</h1>
      <input v-model="admin_name" type="text" placeholder="Admin Name" class="input input-bordered w-full" required/>
      <input v-model="admin_password" type="password" placeholder="Password" class="input input-bordered w-full"
             required/>
      <button type="submit" class="btn btn-primary w-full" :disabled="loading">
        <span v-if="loading">Logging in...</span>
        <span v-else>Login</span>
      </button>
      <div v-if="error" class="text-red-500 text-center">{{ error }}</div>
    </form>
  </div>
</template>

<script setup lang="ts">
import {ref} from 'vue';
import api from '../api';
import {useRouter} from 'vue-router';

const admin_name = ref('');
const admin_password = ref('');
const loading = ref(false);
const error = ref('');
const router = useRouter();

async function onLogin() {
  error.value = '';
  loading.value = true;
  try {
    const response = await api.post('/api/login', {
      admin_name: admin_name.value,
      admin_password: admin_password.value,
    }, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      }
    });
    // Save user info and token to cookies
    document.cookie = `token=${response.data.token}; path=/`;
    document.cookie = `admin_name=${response.data.admin_name}; path=/`;
    document.cookie = `bot_code=${response.data.bot_code}; path=/`;
    document.cookie = `roles=${encodeURIComponent(JSON.stringify(response.data.roles))}; path=/`;
    // Redirect to main page
    router.push('/');
  } catch (e: any) {
    error.value = e.response?.data?.error || 'Login failed';
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.input {
  @apply border border-gray-300 rounded px-3 py-2;
}

.btn {
  @apply bg-blue-600 text-white rounded px-4 py-2 font-semibold hover:bg-blue-700 transition;
}
</style>

