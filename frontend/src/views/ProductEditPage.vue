<template>
  <div class="p-4 max-w-lg mx-auto">
    <h1 class="text-xl font-bold mb-4">{{ isEdit ? 'Edit Product' : 'Create Product' }}</h1>

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
        <input v-model="form.name" maxlength="50" required class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block mb-1 font-medium">Description</label>
        <textarea v-model="form.description" rows="5" required class="w-full border rounded px-3 py-2"></textarea>
      </div>
      <div>
        <label class="block mb-1 font-medium">Categories</label>
        <select v-model="form.categories" multiple class="w-full border rounded px-3 py-2">
          <option v-for="cat in categories" :key="cat.id" :value="`/api/categories/${cat.id}`">{{ cat.name }}</option>
        </select>
        <div class="text-xs text-gray-500">Select up to 3 categories</div>
      </div>
      <div>
        <label class="block mb-1 font-medium">Image</label>
        <input type="file" accept="image/*" @change="onImageChange" class="w-full border rounded px-3 py-2" />
        <div v-if="imagePreview" class="mt-2">
          <img :src="imagePreview" alt="Preview" class="max-h-32 rounded border" />
        </div>
      </div>
      <div class="flex gap-2 mt-4">
        <button type="submit" :disabled="isSubmitting" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
          {{ isSubmitting ? 'Saving...' : 'Save' }}
        </button>
        <button 
          v-if="isEdit" 
          type="button" 
          @click="sendToAllUsers" 
          :disabled="isSending"
          class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isSending ? 'Sending...' : 'Send to All Users' }}
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
const isSending = ref(false);

const fetchCategories = async () => {
  try {
    const { data } = await api.get('/api/categories');
    categories.value = data['member'] || [];
  } catch (error: any) {
    errorMessage.value = error.response?.data?.description || error.response?.data?.detail || 'Failed to load categories';
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
      // Set image preview with full URL if image exists
      if (data.image) {
        imagePreview.value = data.image.startsWith('http')
          ? data.image
          : `${import.meta.env.VITE_API_URL}${data.image}`;
      }
    } catch (error: any) {
      errorMessage.value = error.response?.data?.description || error.response?.data?.detail || 'Failed to load product';
    }
  }
};

const onImageChange = async (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    const file = target.files[0];

    // Show preview immediately
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);

    // Upload file to backend
    try {
      const formData = new FormData();
      formData.append('image', file);

      const response = await api.post('/api/product/upload-image', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      if (response.data.success) {
        form.value.image = response.data.path;
      } else {
        errorMessage.value = 'Failed to upload image';
      }
    } catch (error: any) {
      console.error('Image upload error:', error);
      errorMessage.value = error.response?.data?.description || error.response?.data?.detail || 'Failed to upload image';
    }
  }
};

const submitForm = async () => {
  if (form.value.categories.length > 3) {
    errorMessage.value = 'You can select up to 3 categories.';
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
    // Extract error message from API Platform error response
    const apiError = error.response?.data;
    errorMessage.value = apiError?.description || apiError?.detail || apiError?.title || 'An error occurred while saving the product';
    console.error('API Error:', error.response?.data);
  } finally {
    isSubmitting.value = false;
  }
};

const goBack = () => {
  router.push({ name: 'ProductList' });
};

const sendToAllUsers = async () => {
  if (!isEdit.value || !form.value.id) {
    return;
  }

  if (!confirm('Are you sure you want to send this product to all active users? This will create mailout records for all active users.')) {
    return;
  }

  isSending.value = true;
  errorMessage.value = '';

  try {
    const response = await api.post(`/api/mailout/send-product/${form.value.id}`);
    alert(`Successfully created ${response.data.created} mailout records for ${response.data.total_users} active users.`);
  } catch (error: any) {
    const apiError = error.response?.data;
    errorMessage.value = apiError?.error || apiError?.message || 'Failed to send product to users';
    console.error('Send error:', error.response?.data);
  } finally {
    isSending.value = false;
  }
};

onMounted(() => {
  fetchCategories();
  fetchProduct();
});
</script>
