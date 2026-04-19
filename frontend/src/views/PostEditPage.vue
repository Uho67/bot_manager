<template>
  <div class="page-content-sm">
    <h1 class="page-title">{{ isEdit ? t('posts.edit_title') : t('posts.create_title') }}</h1>

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
        <label class="form-label">{{ t('posts.name') }}</label>
        <input v-model="form.name" required class="form-input" :placeholder="t('posts.name_placeholder')" />
      </div>
      <div class="form-group">
        <label class="form-label">{{ t('posts.description') }}</label>
        <RichTextarea v-model="form.description" :rows="5" required :placeholder="t('posts.description_placeholder')" />
      </div>
      <div class="form-group">
        <label class="form-label">{{ t('posts.template_type') }}</label>
        <select v-model="form.template_type" required class="form-select">
          <option value="">{{ t('posts.select_template_type') }}</option>
          <option value="start">Start</option>
          <option value="product">Product</option>
          <option value="post">Post</option>
        </select>
        <div class="form-hint">{{ t('posts.template_hint') }}</div>
      </div>
      <div class="form-group">
        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="form.enabled" class="form-checkbox" />
          <span class="font-medium">{{ t('posts.enabled_label') }}</span>
        </label>
        <div class="form-hint">{{ t('posts.enabled_hint') }}</div>
      </div>
      <div class="form-group">
        <label class="form-label">{{ t('posts.image') }}</label>
        <input type="file" accept="image/*" @change="onImageChange" class="form-input" />
        <div v-if="imagePreview" class="mt-2 flex items-start gap-2">
          <img :src="imagePreview" alt="Preview" class="max-h-32 rounded border" />
          <button type="button" @click="removeImage" class="btn btn-danger btn-sm">
            {{ t('products.remove_image') }}
          </button>
        </div>
      </div>
      <div class="flex gap-2 mt-4">
        <button type="submit" :disabled="isSubmitting" class="btn btn-primary">
          {{ isSubmitting ? t('common.saving') : t('common.save') }}
        </button>
        <button type="button" @click="goBack" class="btn btn-secondary">{{ t('common.cancel') }}</button>
      </div>
    </form>

    <div v-if="isEdit" class="mt-6 pt-4 border-t border-gray-200">
      <button
        type="button"
        @click="showSendModal = true"
        :disabled="isSending"
        class="btn btn-success"
      >
        {{ isSending ? t('common.sending') : t('common.send_to_all') }}
      </button>
    </div>
  </div>

  <div v-if="showSendModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm mx-4">
      <h2 class="text-lg font-semibold mb-2">{{ t('posts.send_single_title') }}</h2>
      <p class="text-sm text-gray-600 mb-6">{{ t('posts.send_delete_question') }}</p>
      <div class="flex flex-col gap-3">
        <button type="button" @click="sendToAllUsers('not_remove')" :disabled="isSending" class="btn btn-secondary">
          {{ t('posts.not_remove') }}
        </button>
        <button type="button" @click="sendToAllUsers('remove')" :disabled="isSending" class="btn btn-success">
          {{ t('posts.will_remove') }}
        </button>
        <button type="button" @click="showSendModal = false" :disabled="isSending" class="btn btn-secondary text-gray-500">
          {{ t('common.cancel') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import api from '../api';
import type { Post } from '../types/Post';
import RichTextarea from '../components/RichTextarea.vue';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);
const isSubmitting = ref(false);
const isSending = ref(false);
const showSendModal = ref(false);
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

const removeImage = async () => {
  if (isEdit.value && form.value.image) {
    try {
      await api.delete(`/api/post/${route.params.id}/remove-image`);
    } catch (error: any) {
      errorMessage.value = error.response?.data?.description || error.response?.data?.detail || t('products.failed_delete_image');
      return;
    }
  }
  form.value.image = '';
  imagePreview.value = null;
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
      const response = await api.post('/api/post/upload-image', formData, {
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
    const apiError = error.response?.data;
    errorMessage.value = apiError?.description || apiError?.detail || apiError?.title || t('common.error');
  } finally {
    isSubmitting.value = false;
  }
};

const sendToAllUsers = async (removeMode: 'remove' | 'not_remove') => {
  showSendModal.value = false;
  isSending.value = true;
  try {
    const { data } = await api.post(`/api/mailout/send-post/${route.params.id}`, { remove_mode: removeMode });
    alert(t('posts.mailouts_created_detail', { count: data.created, total: data.total_users }));
  } catch (error: any) {
    errorMessage.value = error.response?.data?.description || error.response?.data?.detail || t('posts.send_error');
  } finally {
    isSending.value = false;
  }
};

const goBack = () => { router.push({ name: 'PostList' }); };

onMounted(() => { fetchPost(); });
</script>
