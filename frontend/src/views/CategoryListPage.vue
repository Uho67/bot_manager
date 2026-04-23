<template>
  <div class="p-4">
    <h1 class="page-title">{{ t('categories.title') }}</h1>
    <div v-if="deleteError" class="form-error-box mb-4">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
        </div>
        <div class="ml-3 flex-1">
          <h3 class="text-sm font-medium text-red-800">{{ t('common.error') }}</h3>
          <p class="mt-1 text-sm text-red-700">{{ deleteError }}</p>
        </div>
        <button @click="deleteError = ''" class="ml-3 flex-shrink-0 text-red-400 hover:text-red-600">
          <span class="sr-only">{{ t('common.dismiss') }}</span>
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </button>
      </div>
    </div>
    <div class="table-wrapper">
      <table class="data-table rounded-lg">
        <thead>
          <tr>
            <th class="table-th">{{ t('table.id') }}</th>
            <th class="table-th">{{ t('table.name') }}</th>
            <th class="table-th">{{ t('table.children') }}</th>
            <th class="table-th">{{ t('table.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in categories" :key="category.id" class="table-row-hover">
            <td class="table-td">{{ category.id }}</td>
            <td class="table-td">{{ category.name }}</td>
            <td class="table-td">
              <span v-if="category.childCategories.length === 0" class="text-gray-400 text-sm">{{ t('categories.no_children') }}</span>
              <span v-else>
                <span v-for="child in category.childCategories" :key="child.id" class="badge badge-gray mx-1">{{ child.name }}</span>
              </span>
            </td>
            <td class="table-td">
              <div class="relative inline-block text-left">
                <button @click="openDropdown(category.id)" class="btn btn-secondary btn-sm">{{ t('common.actions') }}</button>
                <div v-if="dropdownOpen === category.id" class="absolute z-10 w-32 bg-white border rounded shadow-lg right-0 bottom-full mb-1">
                  <button @click="editCategory(category.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">{{ t('common.edit') }}</button>
                  <button @click="deleteCategory(category.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">{{ t('common.delete') }}</button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <button @click="createCategory" class="btn btn-primary mt-4">{{ t('categories.create') }}</button>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import api from '../api';
import type { Category } from '../types/Category';

const { t } = useI18n();
const categories = ref<Category[]>([]);
const dropdownOpen = ref<number|null>(null);
const deleteError = ref('');
const router = useRouter();

const fetchCategories = async () => {
  const { data } = await api.get('/api/categories');
  categories.value = data['member'] || [];
};

const openDropdown = (id: number) => {
  dropdownOpen.value = dropdownOpen.value === id ? null : id;
};

const editCategory = (id: number) => {
  router.push({ name: 'CategoryEdit', params: { id } });
};

const deleteCategory = async (id: number) => {
  if (confirm(t('categories.confirm_delete'))) {
    deleteError.value = '';
    dropdownOpen.value = null;
    try {
      await api.delete(`/api/categories/${id}`);
      await fetchCategories();
    } catch (error: any) {
      const apiError = error.response?.data;
      deleteError.value = apiError?.description || apiError?.detail || apiError?.title || t('common.error');
    }
  }
};

const createCategory = () => {
  router.push({ name: 'CategoryCreate' });
};

onMounted(fetchCategories);
</script>
