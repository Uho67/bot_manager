<template>
  <div class="p-4">
    <h1 class="page-title">{{ t('categories.title') }}</h1>
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
    await api.delete(`/api/categories/${id}`);
    fetchCategories();
  }
};

const createCategory = () => {
  router.push({ name: 'CategoryCreate' });
};

onMounted(fetchCategories);
</script>
