<template>
  <div class="page-content-sm">
    <h1 class="page-title">{{ isEdit ? t('buttons.edit_title') : t('buttons.create_title') }}</h1>

    <div v-if="errorMessage" class="form-error-box">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
        </div>
        <div class="ml-3 flex-1">
          <h3 class="text-sm font-medium text-red-800">{{ t('common.error') }}</h3>
          <p class="mt-1 text-sm text-red-700">{{ errorMessage }}</p>
        </div>
        <button @click="errorMessage = ''" class="ml-3 flex-shrink-0 text-red-400 hover:text-red-600">
          <span class="sr-only">{{ t('common.dismiss') }}</span>
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </button>
      </div>
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <input v-if="isEdit" type="hidden" name="id" :value="form.id" />
      <div class="form-group">
        <label class="form-label">{{ t('buttons.code') }}</label>
        <input v-model="form.code" maxlength="20" required class="form-input" :placeholder="t('buttons.code_placeholder')" />
        <div class="form-hint">{{ t('buttons.code_hint') }}</div>
      </div>
      <div class="form-group">
        <label class="form-label">{{ t('buttons.label') }}</label>
        <input v-model="form.label" maxlength="60" required class="form-input" :placeholder="t('buttons.label_placeholder')" />
        <div class="form-hint">{{ t('buttons.label_hint') }}</div>
      </div>
      <div class="form-group">
        <label class="form-label">{{ t('buttons.type') }}</label>
        <select v-model="form.buttonType" required class="form-select">
          <option value="">{{ t('buttons.select_type') }}</option>
          <option value="url">URL</option>
          <option value="callback">Callback</option>
        </select>
        <div class="form-hint">{{ t('buttons.type_hint') }}</div>
      </div>
      <div class="form-group">
        <label class="form-label">{{ t('buttons.value') }}</label>
        <input v-model="form.value" maxlength="60" required class="form-input" :placeholder="form.buttonType === 'url' ? 'https://example.com' : 'callback_data'" />
        <div class="form-hint">{{ form.buttonType === 'url' ? t('buttons.value_hint_url') : t('buttons.value_hint_callback') }} {{ t('buttons.value_max') }}</div>
      </div>
      <div class="flex gap-2 mt-4">
        <button type="submit" :disabled="isSubmitting" class="btn btn-primary">
          {{ isSubmitting ? t('common.saving') : t('common.save') }}
        </button>
        <button type="button" @click="goBack" class="btn btn-secondary">{{ t('common.cancel') }}</button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import api from '../api';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);
const form = ref<{ id?: string; code: string; label: string; buttonType: string; value: string }>({
  id: '',
  code: '',
  label: '',
  buttonType: '',
  value: '',
});
const errorMessage = ref('');
const isSubmitting = ref(false);

const fetchButton = async () => {
  if (isEdit.value) {
    try {
      const { data } = await api.get(`/api/buttons/${route.params.id}`);
      form.value = {
        id: data.id,
        code: data.code,
        label: data.label,
        buttonType: data.buttonType,
        value: data.value,
      };
    } catch (error: any) {
      errorMessage.value = error.response?.data?.description || error.response?.data?.detail || t('common.error');
    }
  } else if (route.query.duplicateFrom) {
    try {
      const { data } = await api.get(`/api/buttons/${route.query.duplicateFrom}`);
      form.value = {
        code: data.code + '_copy',
        label: data.label + ' (Copy)',
        buttonType: data.buttonType,
        value: data.value,
      };
    } catch (error: any) {
      errorMessage.value = error.response?.data?.description || error.response?.data?.detail || t('common.error');
    }
  }
};

const submitForm = async () => {
  errorMessage.value = '';
  isSubmitting.value = true;
  try {
    if (isEdit.value) {
      await api.put(`/api/buttons/${route.params.id}`, form.value);
    } else {
      await api.post('/api/buttons', form.value);
    }
    router.push({ name: 'ButtonList' });
  } catch (error: any) {
    const apiError = error.response?.data;
    errorMessage.value = apiError?.description || apiError?.detail || apiError?.title || t('common.error');
  } finally {
    isSubmitting.value = false;
  }
};

const goBack = () => { router.push({ name: 'ButtonList' }); };

onMounted(() => { fetchButton(); });
</script>
