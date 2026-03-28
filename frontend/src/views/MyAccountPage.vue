<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 px-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
      <h2 class="page-title text-center">{{ t('my_account.title') }}</h2>
      <form @submit.prevent="onSubmit" class="space-y-4">
        <div class="form-group">
          <label class="form-label">{{ t('my_account.admin_name') }}</label>
          <input v-model="form.admin_name" type="text" class="form-input bg-gray-100" disabled />
        </div>
        <div class="form-group">
          <label class="form-label">{{ t('my_account.bot_code') }}</label>
          <input v-model="form.bot_code" type="text" class="form-input" />
        </div>
        <div class="form-group">
          <label class="form-label">{{ t('my_account.new_password') }}</label>
          <input v-model="form.admin_password" type="password" class="form-input" :placeholder="t('my_account.password_placeholder')" />
        </div>
        <div v-if="error" class="form-error">{{ error }}</div>
        <div v-if="success" class="text-green-600 text-sm">{{ success }}</div>
        <button type="submit" :disabled="loading" class="btn btn-primary w-full py-3">
          {{ loading ? t('my_account.saving') : t('my_account.save_changes') }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../api';
import { useAuth } from '../composables/useAuth';

const { t } = useI18n();
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
    error.value = e.response?.data?.error || t('my_account.failed_load');
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
    success.value = t('my_account.updated_success');
    loadUserFromCookies();
  } catch (e: any) {
    error.value = e.response?.data?.error || t('my_account.failed_update');
  } finally {
    loading.value = false;
  }
}
</script>
