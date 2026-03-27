<template>
  <div class="p-4 h-screen flex flex-col">
    <div class="flex justify-between items-center mb-4">
      <h1 class="page-title mb-0">Templates</h1>
      <div class="flex gap-2 items-center">
        <select v-model="typeFilter" @change="fetchTemplates" class="form-select w-auto">
          <option value="">All Types</option>
          <option value="post">Post</option>
          <option value="start">Start</option>
          <option value="category">Category</option>
          <option value="product">Product</option>
        </select>
        <button @click="createTemplate" class="btn btn-primary">Create Template</button>
      </div>
    </div>
    <div class="table-wrapper flex-1 flex flex-col">
      <table class="data-table h-full">
        <thead>
          <tr>
            <th class="table-th">ID</th>
            <th class="table-th">Name</th>
            <th class="table-th">Type</th>
            <th class="table-th">Lines</th>
            <th class="table-th">Total Buttons</th>
            <th class="table-th">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="template in templates" :key="template.id" class="table-row-hover">
            <td class="table-td">{{ template.id }}</td>
            <td class="table-td">{{ template.name }}</td>
            <td class="table-td">
              <span
                :class="{
                  'badge-blue': template.type === 'post',
                  'badge-green': template.type === 'start',
                  'badge-purple': template.type === 'category',
                  'badge-orange': template.type === 'product'
                }"
                class="badge">
                {{ template.type }}
              </span>
            </td>
            <td class="table-td">{{ template.layout.length }}</td>
            <td class="table-td">{{ getTotalButtons(template.layout) }}</td>
            <td class="table-td">
              <div class="relative inline-block text-left">
                <button @click="openDropdown(template.id)" class="btn btn-secondary btn-sm">Actions</button>
                <div v-if="dropdownOpen === template.id" class="absolute z-10 w-32 bg-white border rounded shadow-lg right-0 top-full mt-1">
                  <button @click="editTemplate(template.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Edit</button>
                  <button @click="duplicateTemplate(template.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Duplicate</button>
                  <button @click="deleteTemplate(template.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">Delete</button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';
import type { Template } from '../types/Template';

const templates = ref<Template[]>([]);
const dropdownOpen = ref<number|null>(null);
const typeFilter = ref('');
const router = useRouter();

const fetchTemplates = async () => {
  const { data } = await api.get('/api/templates');
  let allTemplates = data['member'] || [];

  if (typeFilter.value) {
    templates.value = allTemplates.filter((t: Template) => t.type === typeFilter.value);
  } else {
    templates.value = allTemplates;
  }
};

const getTotalButtons = (layout: number[][]) => {
  return layout.reduce((sum, line) => sum + line.length, 0);
};

const openDropdown = (id: number) => {
  dropdownOpen.value = dropdownOpen.value === id ? null : id;
};

const closeDropdown = () => {
  dropdownOpen.value = null;
};

const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement;
  if (!target.closest('.relative')) {
    closeDropdown();
  }
};

const editTemplate = (id: number) => {
  closeDropdown();
  router.push({ name: 'TemplateEdit', params: { id } });
};

const duplicateTemplate = (id: number) => {
  closeDropdown();
  router.push({ name: 'TemplateCreate', query: { duplicateFrom: id.toString() } });
};

const deleteTemplate = async (id: number) => {
  if (confirm('Are you sure you want to delete this template?')) {
    closeDropdown();
    await api.delete(`/api/templates/${id}`);
    fetchTemplates();
  }
};

const createTemplate = () => {
  router.push({ name: 'TemplateCreate' });
};

onMounted(() => {
  fetchTemplates();
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
