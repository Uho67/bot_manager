<template>
  <div class="p-4 max-w-lg mx-auto">
    <h1 class="text-xl font-bold mb-4">{{ isEdit ? 'Edit Button' : 'Create Button' }}</h1>

    <!-- Error Message Display -->
    <div v-if="errorMessage" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
        </div>
        <div class="ml-3 flex-1">
          <h3 class="text-sm font-medium text-red-800">Error</h3>
          <p class="mt-1 text-sm text-red-700">{{ errorMessage }}</p>
        </div>
        <button @click="errorMessage = ''" class="ml-3 flex-shrink-0 text-red-400 hover:text-red-600">
          <span class="sr-only">Dismiss</span>
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </button>
      </div>
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <input v-if="isEdit" type="hidden" name="id" :value="form.id" />
      <div>
        <label class="block mb-1 font-medium">Code</label>
        <input v-model="form.code" maxlength="20" required class="w-full border rounded px-3 py-2" placeholder="e.g., welcome_catalog" />
        <div class="text-xs text-gray-500">Unique identifier for the button (max 20 characters)</div>
      </div>
      <div>
        <label class="block mb-1 font-medium">Label</label>
        <input v-model="form.label" maxlength="60" required class="w-full border rounded px-3 py-2" placeholder="e.g., Browse Catalog" />
        <div class="text-xs text-gray-500">Text displayed on the button (max 60 characters)</div>
      </div>
      <div>
        <label class="block mb-1 font-medium">Button Type</label>
        <select v-model="form.buttonType" required class="w-full border rounded px-3 py-2">
          <option value="">Select type...</option>
          <option value="url">URL</option>
          <option value="callback">Callback</option>
        </select>
        <div class="text-xs text-gray-500">URL opens a link, Callback triggers a bot action</div>
      </div>
      <div>
        <label class="block mb-1 font-medium">Value</label>
        <input v-model="form.value" maxlength="60" required class="w-full border rounded px-3 py-2" :placeholder="form.buttonType === 'url' ? 'https://example.com' : 'callback_data'" />
        <div class="text-xs text-gray-500">{{ form.buttonType === 'url' ? 'URL to open when clicked' : 'Callback data sent to the bot' }} (max 60 characters)</div>
      </div>
      <div>
        <label class="block mb-1 font-medium">Sort Order</label>
        <input v-model.number="form.sortOrder" type="number" min="0" class="w-full border rounded px-3 py-2" />
        <div class="text-xs text-gray-500">Lower numbers appear first</div>
      </div>
      <div class="flex gap-2 mt-4">
        <button type="submit" :disabled="isSubmitting" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
          {{ isSubmitting ? 'Saving...' : 'Save' }}
        </button>
        <button type="button" @click="goBack" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../api';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);
const form = ref<{ id?: string; code: string; label: string; buttonType: string; value: string; sortOrder?: number }>({
  id: '',
  code: '',
  label: '',
  buttonType: '',
  value: '',
  sortOrder: 0,
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
        sortOrder: data.sortOrder || 0,
      };
    } catch (error: any) {
      errorMessage.value = error.response?.data?.description || error.response?.data?.detail || 'Failed to load button';
    }
  } else if (route.query.duplicateFrom) {
    // Handle duplicate functionality
    try {
      const { data } = await api.get(`/api/buttons/${route.query.duplicateFrom}`);
      form.value = {
        code: data.code + '_copy',
        label: data.label + ' (Copy)',
        buttonType: data.buttonType,
        value: data.value,
        sortOrder: data.sortOrder || 0,
      };
    } catch (error: any) {
      errorMessage.value = error.response?.data?.description || error.response?.data?.detail || 'Failed to load button for duplication';
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
    // Extract error message from API Platform error response
    const apiError = error.response?.data;
    errorMessage.value = apiError?.description || apiError?.detail || apiError?.title || 'An error occurred while saving the button';
    console.error('API Error:', error.response?.data);
  } finally {
    isSubmitting.value = false;
  }
};

const goBack = () => {
  router.push({ name: 'ButtonList' });
};

onMounted(() => {
  fetchButton();
});
</script>
