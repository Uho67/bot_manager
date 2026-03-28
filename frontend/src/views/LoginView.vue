<template>
  <div class="login-page">
    <form @submit.prevent="onLogin" class="login-form">
      <h1 class="login-title">{{ t('login.title') }}</h1>
      <input v-model="admin_name" type="text" :placeholder="t('login.admin_name')" class="form-input" required/>
      <input v-model="admin_password" type="password" :placeholder="t('login.password')" class="form-input" required/>
      <button type="submit" class="btn btn-primary w-full" :disabled="loading">
        <span v-if="loading">{{ t('login.logging_in') }}</span>
        <span v-else>{{ t('login.login') }}</span>
      </button>
      <div v-if="error" class="form-error text-center">{{ error }}</div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../api';
import { useRouter } from 'vue-router';

const { t } = useI18n();
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
    document.cookie = `token=${response.data.token}; path=/`;
    document.cookie = `admin_name=${response.data.admin_name}; path=/`;
    document.cookie = `bot_code=${response.data.bot_code}; path=/`;
    document.cookie = `roles=${encodeURIComponent(JSON.stringify(response.data.roles))}; path=/`;
    router.push('/');
  } catch (e: any) {
    error.value = e.response?.data?.error || t('login.login_failed');
  } finally {
    loading.value = false;
  }
}
</script>
