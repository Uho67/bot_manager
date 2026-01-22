<template>
  <div class="p-4 max-w-lg mx-auto">
    <h1 class="text-xl font-bold mb-4">{{ isEdit ? 'Edit Category' : 'Create Category' }}</h1>
    <form @submit.prevent="submitForm" class="space-y-4">
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
import type { Category } from '../types/Category';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);
const form = ref<{ name: string; childCategories: string[] }>({
  name: '',
  childCategories: [],
});
const allCategories = ref<Category[]>([]);

const fetchAllCategories = async () => {
  const { data } = await api.get('/api/categories');
  allCategories.value = data['member'] || [];
};

const fetchCategory = async () => {
  if (isEdit.value) {
    const { data } = await api.get(`/api/categories/${route.params.id}`);
    form.value = {
      name: data.name,
      childCategories: (data.childCategories || []).map((cat: Category) => `/api/categories/${cat.id}`),
    };
  }
};

const submitForm = async () => {
  if (form.value.childCategories.length > 10) {
    alert('You can select up to 10 children.');
    return;
  }
  if (isEdit.value) {
    await api.put(`/api/categories/${route.params.id}`, form.value);
  } else {
    await api.post('/api/categories', form.value);
  }
  router.push({ name: 'CategoryList' });
};

const goBack = () => {
  router.push({ name: 'CategoryList' });
};

onMounted(() => {
  fetchAllCategories();
  fetchCategory();
});
</script>

