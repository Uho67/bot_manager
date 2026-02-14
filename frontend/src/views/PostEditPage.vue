<template>
  <div class="p-4 max-w-lg mx-auto">
    <h1 class="text-xl font-bold mb-4">{{ isEdit ? 'Edit Post' : 'Create Post' }}</h1>

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
        <input v-model="form.name" required class="w-full border rounded px-3 py-2" placeholder="e.g., Welcome Post" />
      </div>
      <div>
        <label class="block mb-1 font-medium">Description</label>
        <textarea v-model="form.description" rows="5" required class="w-full border rounded px-3 py-2" placeholder="Post description text"></textarea>
      </div>
      <div>
        <label class="block mb-1 font-medium">Template Type</label>
        <select v-model="form.template_type" required class="w-full border rounded px-3 py-2">
          <option value="">Select template type...</option>
          <option value="start">Start</option>
          <option value="product">Product</option>
          <option value="post">Post</option>
        </select>
        <div class="text-xs text-gray-500 mt-1">Select the template type this post will use</div>
      </div>
      <div>
        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="form.enabled" class="w-4 h-4" />
          <span class="font-medium">Enabled</span>
        </label>
        <div class="text-xs text-gray-500 mt-1">Only enabled posts are accessible via API</div>
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
        <button type="button" @click="goBack" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../api';
import type { Post } from '../types/Post';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);
const isSubmitting = ref(false);
const form = ref<{ id?: string; name: string; description: string; template_type: 'start' | 'product' | 'post'; image?: string; enabled: boolean }>({
  id: '',
  name: '',
  description: '',
  template_type: 'start',
  image: '',
  enabled: true,
});
const imagePreview = ref<string | null>(null);
const errorMessage = ref('');

const fetchPost = async () => {
  if (isEdit.value) {
    try {
      const { data } = await api.get(`/api/posts/${route.params.id}`);
      form.value = {
        id: data.id,
        name: data.name,
        description: data.description,
        template_type: data.template_type,
        image: data.image || '',
        enabled: data.enabled !== undefined ? data.enabled : true,
      };
      // Set image preview with full URL if image exists
      if (data.image) {
        imagePreview.value = data.image.startsWith('http')
          ? data.image
          : `${import.meta.env.VITE_API_URL}${data.image}`;
      }
    } catch (error: any) {
      errorMessage.value = error.response?.data?.description || error.response?.data?.detail || 'Failed to load post';
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

      const response = await api.post('/api/post/upload-image', formData, {
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
  errorMessage.value = '';
  isSubmitting.value = true;

  try {
    if (isEdit.value) {
      await api.put(`/api/posts/${route.params.id}`, form.value);
    } else {
      await api.post('/api/posts', form.value);
    }
    router.push({ name: 'PostList' });
  } catch (error: any) {
    // Extract error message from API Platform error response
    const apiError = error.response?.data;
    errorMessage.value = apiError?.description || apiError?.detail || apiError?.title || 'An error occurred while saving the post';
    console.error('API Error:', error.response?.data);
  } finally {
    isSubmitting.value = false;
  }
};

const goBack = () => {
  router.push({ name: 'PostList' });
};

onMounted(() => {
  fetchPost();
});
</script>
