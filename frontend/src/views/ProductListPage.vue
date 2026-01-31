<template>
  <div class="p-4">
    <h1 class="text-xl font-bold mb-4">Products</h1>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white border border-gray-200 rounded-lg">
        <thead>
          <tr>
            <th class="px-4 py-2 border-b text-center">ID</th>
            <th class="px-4 py-2 border-b text-center">Name</th>
            <th class="px-4 py-2 border-b text-center">Sort Order</th>
            <th class="px-4 py-2 border-b text-center">Image</th>
            <th class="px-4 py-2 border-b text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id">
            <td class="px-4 py-2 border-b text-center">{{ product.id }}</td>
            <td class="px-4 py-2 border-b text-center">{{ product.name }}</td>
            <td class="px-4 py-2 border-b text-center">{{ product.sortOrder || 0 }}</td>
            <td class="px-4 py-2 border-b text-center">
              <img v-if="product.image" :src="getImageUrl(product.image)" alt="Product Image" class="w-16 h-16 object-cover rounded mx-auto">
              <span v-else class="text-gray-400 text-sm">No image</span>
            </td>
            <td class="px-4 py-2 border-b text-center">
              <div class="relative inline-block text-left">
                <button @click="openDropdown(product.id)" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200">Actions</button>
                <div v-if="dropdownOpen === product.id" class="absolute z-10 w-32 bg-white border rounded shadow-lg right-0 bottom-full mb-1">
                  <button @click="editProduct(product.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Edit</button>
                  <button @click="deleteProduct(product.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">Delete</button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <button @click="createProduct" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create Product</button>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';
import type { Product } from '../types/Product';

const products = ref<Product[]>([]);
const dropdownOpen = ref<number|null>(null);
const router = useRouter();

const fetchProducts = async () => {
  const { data } = await api.get('/api/products');
  products.value = data['member'] || [];
};

const openDropdown = (id: number) => {
  dropdownOpen.value = dropdownOpen.value === id ? null : id;
};

const editProduct = (id: number) => {
  router.push({ name: 'ProductEdit', params: { id } });
};

const deleteProduct = async (id: number) => {
  if (confirm('Are you sure you want to delete this product?')) {
    await api.delete(`/api/products/${id}`);
    fetchProducts();
  }
};

const createProduct = () => {
  router.push({ name: 'ProductCreate' });
};

const getImageUrl = (path: string) => {
  // If path already starts with http, return as is
  if (path.startsWith('http')) {
    return path;
  }
  // Otherwise, prepend the API base URL
  return `${import.meta.env.VITE_API_URL}${path}`;
};

onMounted(fetchProducts);
</script>
