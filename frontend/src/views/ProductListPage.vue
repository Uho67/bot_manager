<template>
  <div class="p-4">
    <h1 class="page-title">Products</h1>
    <div class="table-wrapper">
      <table class="data-table rounded-lg">
        <thead>
          <tr>
            <th class="table-th">ID</th>
            <th class="table-th">Name</th>
            <th class="table-th">Image</th>
            <th class="table-th">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id" class="table-row-hover">
            <td class="table-td">{{ product.id }}</td>
            <td class="table-td">{{ product.name }}</td>
            <td class="table-td">
              <img v-if="product.image" :src="getImageUrl(product.image)" alt="Product Image" class="w-16 h-16 object-cover rounded mx-auto">
              <span v-else class="text-gray-400 text-sm">No image</span>
            </td>
            <td class="table-td">
              <div class="relative inline-block text-left">
                <button @click="openDropdown(product.id)" class="btn btn-secondary btn-sm">Actions</button>
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
    <button @click="createProduct" class="btn btn-primary mt-4">Create Product</button>
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
