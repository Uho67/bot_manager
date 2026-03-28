<template>
  <div class="p-4">
    <h1 class="page-title">{{ t('posts.title') }}</h1>

    <div v-if="selectedPosts.length > 0" class="alert-info mb-4 flex items-center gap-4">
      <span class="text-blue-800 text-sm font-medium">{{ t('posts.selected', { count: selectedPosts.length }) }}</span>
      <button
        @click="showSendModal = true"
        :disabled="isSendingAll"
        class="btn btn-success btn-sm"
      >
        {{ isSendingAll ? t('common.sending') : t('common.send_to_all') }}
      </button>
      <button @click="selectedPosts = []" class="btn btn-secondary btn-sm">
        {{ t('common.reset') }}
      </button>
    </div>

    <div class="table-wrapper">
      <table class="data-table rounded-lg">
        <thead>
          <tr>
            <th class="table-th w-10">
              <input type="checkbox" :checked="allSelected" @change="toggleSelectAll" class="form-checkbox" />
            </th>
            <th class="table-th">{{ t('table.id') }}</th>
            <th class="table-th">{{ t('table.name') }}</th>
            <th class="table-th">{{ t('table.template_type') }}</th>
            <th class="table-th">{{ t('table.status') }}</th>
            <th class="table-th">{{ t('table.image') }}</th>
            <th class="table-th">{{ t('table.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="post in posts" :key="post.id" class="table-row-hover">
            <td class="table-td">
              <input type="checkbox" :value="post.id" v-model="selectedPosts" class="form-checkbox" />
            </td>
            <td class="table-td">{{ post.id }}</td>
            <td class="table-td">{{ post.name }}</td>
            <td class="table-td">
              <span class="badge badge-blue">{{ post.template_type }}</span>
            </td>
            <td class="table-td">
              <span :class="post.enabled ? 'badge-green' : 'badge-red'" class="badge">
                {{ post.enabled ? t('common.enabled') : t('common.disabled') }}
              </span>
            </td>
            <td class="table-td">
              <img v-if="post.image" :src="getImageUrl(post.image)" alt="Post Image" class="w-16 h-16 object-cover rounded mx-auto">
              <span v-else class="text-gray-400 text-sm">{{ t('common.no_image') }}</span>
            </td>
            <td class="table-td">
              <div class="relative inline-block text-left">
                <button @click="openDropdown(post.id)" class="btn btn-secondary btn-sm">{{ t('common.actions') }}</button>
                <div v-if="dropdownOpen === post.id" class="absolute z-10 w-32 bg-white border rounded shadow-lg right-0 bottom-full mb-1">
                  <button @click="editPost(post.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">{{ t('common.edit') }}</button>
                  <button @click="deletePost(post.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">{{ t('common.delete') }}</button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <button @click="createPost" class="btn btn-primary mt-4">{{ t('posts.create') }}</button>
  </div>

  <div v-if="showSendModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm mx-4">
      <h2 class="text-lg font-semibold mb-2">{{ t('posts.send_all_title', { count: selectedPosts.length }) }}</h2>
      <p class="text-sm text-gray-600 mb-6">{{ t('posts.send_delete_question') }}</p>
      <div class="flex flex-col gap-3">
        <button type="button" @click="sendSelectedToAllUsers('not_remove')" :disabled="isSendingAll" class="btn btn-secondary">
          {{ t('posts.not_remove') }}
        </button>
        <button type="button" @click="sendSelectedToAllUsers('remove')" :disabled="isSendingAll" class="btn btn-success">
          {{ t('posts.will_remove') }}
        </button>
        <button type="button" @click="showSendModal = false" :disabled="isSendingAll" class="btn btn-secondary text-gray-500">
          {{ t('common.cancel') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import api from '../api';
import type { Post } from '../types/Post';

const { t } = useI18n();
const posts = ref<Post[]>([]);
const dropdownOpen = ref<number|null>(null);
const selectedPosts = ref<number[]>([]);
const isSendingAll = ref(false);
const showSendModal = ref(false);
const router = useRouter();

const allSelected = computed(() =>
  posts.value.length > 0 && selectedPosts.value.length === posts.value.length
);

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedPosts.value = [];
  } else {
    selectedPosts.value = posts.value.map(p => p.id);
  }
};

const fetchPosts = async () => {
  const { data } = await api.get('/api/posts');
  posts.value = data['member'] || [];
};

const openDropdown = (id: number) => {
  dropdownOpen.value = dropdownOpen.value === id ? null : id;
};

const editPost = (id: number) => {
  router.push({ name: 'PostEdit', params: { id } });
};

const deletePost = async (id: number) => {
  if (confirm(t('posts.confirm_delete'))) {
    await api.delete(`/api/posts/${id}`);
    fetchPosts();
  }
};

const createPost = () => {
  router.push({ name: 'PostCreate' });
};

const sendSelectedToAllUsers = async (removeMode: 'remove' | 'not_remove') => {
  showSendModal.value = false;
  isSendingAll.value = true;
  let totalCreated = 0;
  try {
    for (const id of selectedPosts.value) {
      const { data } = await api.post(`/api/mailout/send-post/${id}`, { remove_mode: removeMode });
      totalCreated += data.created ?? 0;
    }
    alert(t('posts.mailouts_created', { count: totalCreated }));
    selectedPosts.value = [];
  } catch (error: any) {
    alert(error.response?.data?.description || error.response?.data?.detail || t('posts.send_error'));
  } finally {
    isSendingAll.value = false;
  }
};

const getImageUrl = (path: string) => {
  if (path.startsWith('http')) return path;
  return `${import.meta.env.VITE_API_URL}${path}`;
};

onMounted(fetchPosts);
</script>
