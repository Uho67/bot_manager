<template>
  <div class="p-4">
    <h1 class="page-title">{{ t('products.title') }}</h1>
    <div class="table-wrapper">
      <table class="data-table rounded-lg">
        <thead>
          <tr>
            <th class="table-th">{{ t('table.id') }}</th>
            <th class="table-th">{{ t('table.name') }}</th>
            <th class="table-th">{{ t('table.image') }}</th>
            <th class="table-th">{{ t('table.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id" class="table-row-hover">
            <td class="table-td">{{ product.id }}</td>
            <td class="table-td">
              {{ product.name }}
              <span v-if="!product.enabled" class="ml-2 inline-block bg-red-100 text-red-700 text-xs font-semibold px-2 py-0.5 rounded">
                {{ t('products.out_of_stock') }}
              </span>
            </td>
            <td class="table-td">
              <img v-if="product.image" :src="getImageUrl(product.image)" alt="Product Image" class="w-16 h-16 object-cover rounded mx-auto">
              <span v-else class="text-gray-400 text-sm">{{ t('common.no_image') }}</span>
            </td>
            <td class="table-td">
              <div class="relative inline-block text-left">
                <button @click="openDropdown(product.id)" class="btn btn-secondary btn-sm">{{ t('common.actions') }}</button>
                <div v-if="dropdownOpen === product.id" class="absolute z-10 w-32 bg-white border rounded shadow-lg right-0 bottom-full mb-1">
                  <button @click="editProduct(product.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">{{ t('common.edit') }}</button>
                  <button @click="deleteProduct(product.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">{{ t('common.delete') }}</button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <button @click="createProduct" class="btn btn-primary mt-4">{{ t('products.create') }}</button>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import api from '../api';
import type { Product } from '../types/Product';

const { t } = useI18n();
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
  if (confirm(t('products.confirm_delete'))) {
    await api.delete(`/api/products/${id}`);
    fetchProducts();
  }
};

const createProduct = () => {
  router.push({ name: 'ProductCreate' });
};

const getImageUrl = (path: string) => {
  if (path.startsWith('http')) return path;
  return `${import.meta.env.VITE_API_URL}${path}`;
};

onMounted(fetchProducts);
</script>
