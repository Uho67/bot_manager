<template>
  <div class="p-4">
    <h1 class="page-title">Categories</h1>
    <div class="table-wrapper">
      <table class="data-table rounded-lg">
        <thead>
          <tr>
            <th class="table-th">ID</th>
            <th class="table-th">Name</th>
            <th class="table-th">Children</th>
            <th class="table-th">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in categories" :key="category.id" class="table-row-hover">
            <td class="table-td">{{ category.id }}</td>
            <td class="table-td">{{ category.name }}</td>
            <td class="table-td">
              <span v-if="category.childCategories.length === 0" class="text-gray-400 text-sm">No children</span>
              <span v-else>
                <span v-for="child in category.childCategories" :key="child.id" class="badge badge-gray mx-1">{{ child.name }}</span>
              </span>
            </td>
            <td class="table-td">
              <div class="relative inline-block text-left">
                <button @click="openDropdown(category.id)" class="btn btn-secondary btn-sm">Actions</button>
                <div v-if="dropdownOpen === category.id" class="absolute z-10 w-32 bg-white border rounded shadow-lg right-0 bottom-full mb-1">
                  <button @click="editCategory(category.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Edit</button>
                  <button @click="deleteCategory(category.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">Delete</button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <button @click="createCategory" class="btn btn-primary mt-4">Create Category</button>
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
