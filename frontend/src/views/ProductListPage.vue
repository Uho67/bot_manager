<template>
  <div class="p-4">
    <div class="flex items-center justify-between mb-4">
      <h1 class="page-title mb-0">{{ t('products.title') }}</h1>
      <button @click="createProduct" class="btn btn-primary">{{ t('products.create') }}</button>
    </div>
    <div class="table-wrapper">
      <table class="data-table rounded-lg">
        <thead>
          <tr>
            <th class="table-th cursor-pointer select-none" @click="toggleSort('id')">
              {{ t('table.id') }}
              <span v-if="sortField === 'id'">{{ sortDirection === 'asc' ? ' ▲' : ' ▼' }}</span>
            </th>
            <th class="table-th cursor-pointer select-none" @click="toggleSort('name')">
              {{ t('table.name') }}
              <span v-if="sortField === 'name'">{{ sortDirection === 'asc' ? ' ▲' : ' ▼' }}</span>
            </th>
            <th class="table-th">{{ t('table.image') }}</th>
            <th class="table-th">{{ t('table.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id" class="table-row-hover">
            <td class="table-td">{{ product.id }}</td>
            <td class="table-td">{{ product.name }}</td>
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
          <tr v-if="products.length === 0">
            <td colspan="4" class="px-4 py-8 text-center text-gray-500">{{ t('products.no_products') }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="pagination.totalItems > 0" class="mt-4 flex items-center justify-between">
      <div class="text-sm text-gray-700">
        {{ t('products.showing', { first: pagination.firstItem, last: pagination.lastItem, total: pagination.totalItems }) }}
      </div>
      <div class="flex items-center gap-2">
        <button @click="goToPage(pagination.currentPage - 1)" :disabled="pagination.currentPage === 1" class="btn btn-secondary btn-sm">
          {{ t('products.previous') }}
        </button>
        <span class="text-sm text-gray-700">
          {{ t('products.page_of', { current: pagination.currentPage, total: pagination.totalPages }) }}
        </span>
        <button @click="goToPage(pagination.currentPage + 1)" :disabled="pagination.currentPage >= pagination.totalPages" class="btn btn-secondary btn-sm">
          {{ t('products.next') }}
        </button>
        <select v-model="pagination.itemsPerPage" @change="changeItemsPerPage" class="form-select w-auto text-sm">
          <option :value="10">{{ t('products.per_page', { n: 10 }) }}</option>
          <option :value="20">{{ t('products.per_page', { n: 20 }) }}</option>
          <option :value="50">{{ t('products.per_page', { n: 50 }) }}</option>
          <option :value="100">{{ t('products.per_page', { n: 100 }) }}</option>
        </select>
      </div>
    </div>

    <button @click="createProduct" class="btn btn-primary mt-4">{{ t('products.create') }}</button>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import api from '../api';
import type { Product } from '../types/Product';

const { t } = useI18n();
const products = ref<Product[]>([]);
const dropdownOpen = ref<number|null>(null);
const router = useRouter();
const route = useRoute();

const sortField = ref<string>(typeof route.query.sortField === 'string' ? route.query.sortField : 'id');
const sortDirection = ref<string>(typeof route.query.sortDirection === 'string' ? route.query.sortDirection : 'desc');

const pagination = ref({
  currentPage: route.query.page ? Number(route.query.page) : 1,
  itemsPerPage: route.query.itemsPerPage ? Number(route.query.itemsPerPage) : 20,
  totalItems: 0,
  totalPages: 0,
  firstItem: 0,
  lastItem: 0,
});

const updateQueryParams = () => {
  router.replace({
    query: {
      page: pagination.value.currentPage.toString(),
      itemsPerPage: pagination.value.itemsPerPage.toString(),
      sortField: sortField.value,
      sortDirection: sortDirection.value,
    },
  });
};

const fetchProducts = async () => {
  const params = new URLSearchParams();
  params.append('page', pagination.value.currentPage.toString());
  params.append('itemsPerPage', pagination.value.itemsPerPage.toString());
  params.append(`order[${sortField.value}]`, sortDirection.value);

  const { data } = await api.get(`/api/products?${params.toString()}`);
  products.value = data['hydra:member'] || data['member'] || [];

  const total = data['hydra:totalItems'] ?? data['totalItems'];
  if (total !== undefined) {
    pagination.value.totalItems = total;
  }
  if (pagination.value.totalItems > 0) {
    pagination.value.totalPages = Math.ceil(pagination.value.totalItems / pagination.value.itemsPerPage);
    pagination.value.firstItem = (pagination.value.currentPage - 1) * pagination.value.itemsPerPage + 1;
    pagination.value.lastItem = Math.min(pagination.value.currentPage * pagination.value.itemsPerPage, pagination.value.totalItems);
  } else {
    pagination.value.totalPages = 0;
    pagination.value.firstItem = 0;
    pagination.value.lastItem = 0;
  }

  updateQueryParams();
};

const goToPage = (page: number) => {
  if (page >= 1 && page <= pagination.value.totalPages) {
    pagination.value.currentPage = page;
    fetchProducts();
  }
};

const changeItemsPerPage = () => {
  pagination.value.currentPage = 1;
  fetchProducts();
};

const toggleSort = (field: string) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }
  pagination.value.currentPage = 1;
  fetchProducts();
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
