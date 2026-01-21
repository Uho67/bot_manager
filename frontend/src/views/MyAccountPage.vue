<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 px-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
      <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">My Account</h2>
      <form @submit.prevent="onSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Admin Name</label>
          <input v-model="form.admin_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" disabled />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Bot Code</label>
          <input v-model="form.bot_code" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
          <input v-model="form.admin_password" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Leave blank to keep current password" />
        </div>
        <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>
        <div v-if="success" class="text-green-600 text-sm">{{ success }}</div>
        <button type="submit" :disabled="loading" class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
          {{ loading ? 'Saving...' : 'Save Changes' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../api';
import { useAuth } from '../composables/useAuth';

const { user, loadUserFromCookies } = useAuth();
const form = ref({
  admin_name: '',
  bot_code: '',
  admin_password: ''
});
const loading = ref(false);
const error = ref('');
const success = ref('');

onMounted(async () => {
  try {
    loading.value = true;
    const res = await api.get('/api/me');
    form.value.admin_name = res.data.admin_name;
    form.value.bot_code = res.data.bot_code || '';
    form.value.admin_password = '';
  } catch (e: any) {
    error.value = e.response?.data?.error || 'Failed to load account info';
  } finally {
    loading.value = false;
  }
});

async function onSubmit() {
  error.value = '';
  success.value = '';
  loading.value = true;
  try {
    const payload: any = { bot_code: form.value.bot_code };
    if (form.value.admin_password) payload.admin_password = form.value.admin_password;
    const res = await api.patch('/api/me', payload, { headers: { 'Content-Type': 'application/ld+json' } });
    form.value.bot_code = res.data.bot_code || '';
    form.value.admin_password = '';
    success.value = 'Account updated successfully!';
    loadUserFromCookies();
  } catch (e: any) {
    error.value = e.response?.data?.error || 'Failed to update account';
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
</style>
