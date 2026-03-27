<template>
  <div class="login-page">
    <form @submit.prevent="onLogin" class="login-form">
      <h1 class="login-title">Admin Login</h1>
      <input v-model="admin_name" type="text" placeholder="Admin Name" class="form-input" required/>
      <input v-model="admin_password" type="password" placeholder="Password" class="form-input" required/>
      <button type="submit" class="btn btn-primary w-full" :disabled="loading">
        <span v-if="loading">Logging in...</span>
        <span v-else>Login</span>
      </button>
      <div v-if="error" class="form-error text-center">{{ error }}</div>
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


