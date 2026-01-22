<template>
  <div class="p-4 max-w-lg mx-auto">
    <h1 class="text-xl font-bold mb-4">{{ isEdit ? 'Edit Product' : 'Create Product' }}</h1>
    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block mb-1 font-medium">Name</label>
        <input v-model="form.name" maxlength="10" required class="w-full border rounded px-3 py-2" />
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
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
        <button type="button" @click="goBack" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../api';
import type { Product, Category } from '../types/Product';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);
const form = ref<{ name: string; description: string; categories: string[]; image?: string }>({
  name: '',
  description: '',
  categories: [],
  image: '',
});
const categories = ref<Category[]>([]);
const imagePreview = ref<string | null>(null);

const fetchCategories = async () => {
  const { data } = await api.get('/api/categories');
  categories.value = data['member'] || [];
};

const fetchProduct = async () => {
  if (isEdit.value) {
    const { data } = await api.get(`/api/products/${route.params.id}`);
    form.value = {
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
        alert('Failed to upload image');
      }
    } catch (error) {
      console.error('Image upload error:', error);
      alert('Failed to upload image');
    }
  }
};

const submitForm = async () => {
  if (form.value.categories.length > 3) {
    alert('You can select up to 3 categories.');
    return;
  }
  if (isEdit.value) {
    await api.put(`/api/products/${route.params.id}`, form.value);
  } else {
    await api.post('/api/products', form.value);
  }
  router.push({ name: 'ProductList' });
};

const goBack = () => {
  router.push({ name: 'ProductList' });
};

onMounted(() => {
  fetchCategories();
  fetchProduct();
});
</script>
