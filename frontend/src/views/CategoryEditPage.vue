<template>
  <div class="p-4 max-w-lg mx-auto">
    <h1 class="text-xl font-bold mb-4">{{ isEdit ? 'Edit Category' : 'Create Category' }}</h1>

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
        <label class="block mb-1 font-medium">Name</label>
        <input v-model="form.name" maxlength="10" required class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block mb-1 font-medium">Children Categories</label>
        <select v-model="form.childCategories" multiple class="w-full border rounded px-3 py-2">
          <option v-for="cat in allCategories" :key="cat.id" :value="`/api/categories/${cat.id}`">{{ cat.name }}</option>
        </select>
        <div class="text-xs text-gray-500">Select up to 10 children</div>
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
import type { Category } from '../types/Category';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);
const form = ref<{ id?: string; name: string; childCategories: string[] }>({
  id: '',
  name: '',
  childCategories: [],
});
const allCategories = ref<Category[]>([]);
const errorMessage = ref('');
const isSubmitting = ref(false);

const fetchAllCategories = async () => {
  try {
    const { data } = await api.get('/api/categories');
    allCategories.value = data['member'] || [];
  } catch (error: any) {
    errorMessage.value = error.response?.data?.description || error.response?.data?.detail || 'Failed to load categories';
  }
};

const fetchCategory = async () => {
  if (isEdit.value) {
    try {
      const { data } = await api.get(`/api/categories/${route.params.id}`);
      form.value = {
        id: data.id,
        name: data.name,
        childCategories: (data.childCategories || []).map((cat: Category) => `/api/categories/${cat.id}`),
      };
    } catch (error: any) {
      errorMessage.value = error.response?.data?.description || error.response?.data?.detail || 'Failed to load category';
    }
  }
};

const submitForm = async () => {
  if (form.value.childCategories.length > 10) {
    errorMessage.value = 'You can select up to 10 children.';
    return;
  }

  errorMessage.value = '';
  isSubmitting.value = true;

  try {
    if (isEdit.value) {
      await api.put(`/api/categories/${route.params.id}`, form.value);
    } else {
      await api.post('/api/categories', form.value);
    }
    router.push({ name: 'CategoryList' });
  } catch (error: any) {
    // Extract error message from API Platform error response
    const apiError = error.response?.data;
    errorMessage.value = apiError?.description || apiError?.detail || apiError?.title || 'An error occurred while saving the category';
    console.error('API Error:', error.response?.data);
  } finally {
    isSubmitting.value = false;
  }
};

const goBack = () => {
  router.push({ name: 'CategoryList' });
};

onMounted(() => {
  fetchAllCategories();
  fetchCategory();
});
</script>
