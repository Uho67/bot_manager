<template>
  <div class="p-4">
    <h1 class="text-xl font-bold mb-4">Categories</h1>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white border border-gray-200 rounded-lg">
        <thead>
          <tr>
            <th class="px-4 py-2 border-b text-center">ID</th>
            <th class="px-4 py-2 border-b text-center">Name</th>
            <th class="px-4 py-2 border-b text-center">Children</th>
            <th class="px-4 py-2 border-b text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in categories" :key="category.id">
            <td class="px-4 py-2 border-b text-center">{{ category.id }}</td>
            <td class="px-4 py-2 border-b text-center">{{ category.name }}</td>
            <td class="px-4 py-2 border-b text-center">
              <span v-if="category.childCategories.length === 0" class="text-gray-400 text-sm">No children</span>
              <span v-else>
                <span v-for="child in category.childCategories" :key="child.id" class="inline-block bg-gray-100 rounded px-2 py-1 mx-1 text-xs">{{ child.name }}</span>
              </span>
            </td>
            <td class="px-4 py-2 border-b text-center">
              <div class="relative inline-block text-left">
                <button @click="openDropdown(category.id)" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200">Actions</button>
                <div v-if="dropdownOpen === category.id" class="absolute z-10 mt-2 w-32 bg-white border rounded shadow-lg">
                  <button @click="editCategory(category.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Edit</button>
                  <button @click="deleteCategory(category.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">Delete</button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <button @click="createCategory" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create Category</button>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';
import type { Category } from '../types/Category';

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
  if (confirm('Are you sure you want to delete this category?')) {
    await api.delete(`/api/categories/${id}`);
    fetchCategories();
  }
};

const createCategory = () => {
  router.push({ name: 'CategoryCreate' });
};

onMounted(fetchCategories);
</script>

