<template>
  <div class="page-content-sm">
    <h1 class="page-title">{{ isEdit ? t('products.edit_title') : t('products.create_title') }}</h1>

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
        <label class="form-label">{{ t('products.name') }}</label>
        <input v-model="form.name" maxlength="50" required class="form-input" />
      </div>
      <div class="form-group">
        <label class="form-label">{{ t('products.description') }}</label>
        <RichTextarea v-model="form.description" :rows="5" required />
      </div>
      <div class="form-group">
        <label class="form-label">{{ t('products.categories') }}</label>
        <select v-model="form.categories" multiple class="form-select">
          <option v-for="cat in categories" :key="cat.id" :value="`/api/categories/${cat.id}`">{{ cat.name }}</option>
        </select>
        <div class="form-hint">{{ t('products.categories_hint') }}</div>
      </div>
      <div class="form-group">
        <label class="form-label">{{ t('products.image') }}</label>
        <input type="file" accept="image/*" @change="onImageChange" class="form-input" />
        <div v-if="imagePreview" class="mt-2">
          <img :src="imagePreview" alt="Preview" class="max-h-32 rounded border" />
        </div>
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
import RichTextarea from '../components/RichTextarea.vue';

const { t } = useI18n();

interface Category {
  id: number;
  name: string;
}

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);
const form = ref<{ id?: string; name: string; description: string; categories: string[]; image?: string }>({
  id: '',
  name: '',
  description: '',
  categories: [],
  image: '',
});
const categories = ref<Category[]>([]);
const imagePreview = ref<string | null>(null);
const errorMessage = ref('');
const isSubmitting = ref(false);

const fetchCategories = async () => {
  try {
    const { data } = await api.get('/api/categories');
    categories.value = data['member'] || [];
  } catch (error: any) {
    errorMessage.value = error.response?.data?.description || error.response?.data?.detail || t('common.error');
  }
};

const fetchProduct = async () => {
  if (isEdit.value) {
    try {
      const { data } = await api.get(`/api/products/${route.params.id}`);
      form.value = {
        id: data.id,
        name: data.name,
        description: data.description,
        categories: (data.categories || []).map((cat: Category) => `/api/categories/${cat.id}`),
        image: data.image || '',
      };
      if (data.image) {
        imagePreview.value = data.image.startsWith('http')
          ? data.image
          : `${import.meta.env.VITE_API_URL}${data.image}`;
      }
    } catch (error: any) {
      errorMessage.value = error.response?.data?.description || error.response?.data?.detail || t('common.error');
    }
  }
};

const onImageChange = async (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    const file = target.files[0];
    const reader = new FileReader();
    reader.onload = (e) => { imagePreview.value = e.target?.result as string; };
    reader.readAsDataURL(file);
    try {
      const formData = new FormData();
      formData.append('image', file);
      const response = await api.post('/api/product/upload-image', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      if (response.data.success) {
        form.value.image = response.data.path;
      } else {
        errorMessage.value = t('products.failed_upload');
      }
    } catch (error: any) {
      errorMessage.value = error.response?.data?.description || error.response?.data?.detail || t('products.failed_upload');
    }
  }
};

const submitForm = async () => {
  if (form.value.categories.length > 3) {
    errorMessage.value = t('products.max_categories');
    return;
  }
  errorMessage.value = '';
  isSubmitting.value = true;
  try {
    if (isEdit.value) {
      await api.put(`/api/products/${route.params.id}`, form.value);
    } else {
      await api.post('/api/products', form.value);
    }
    router.push({ name: 'ProductList' });
  } catch (error: any) {
    const apiError = error.response?.data;
    errorMessage.value = apiError?.description || apiError?.detail || apiError?.title || t('common.error');
  } finally {
    isSubmitting.value = false;
  }
};

const goBack = () => { router.push({ name: 'ProductList' }); };

onMounted(() => {
  fetchCategories();
  fetchProduct();
});
</script>
