<template>
  <div class="p-4 h-screen flex flex-col">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-xl font-bold">Templates</h1>
      <div class="flex gap-2 items-center">
        <select v-model="typeFilter" @change="fetchTemplates" class="border rounded px-3 py-2">
          <option value="">All Types</option>
          <option value="post">Post</option>
          <option value="start">Start</option>
          <option value="category">Category</option>
          <option value="product">Product</option>
        </select>
        <button @click="createTemplate" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create Template</button>
      </div>
    </div>
    <div class="overflow-x-auto flex-1 flex flex-col">
      <table class="min-w-full bg-white border border-gray-200 rounded-lg h-full">
        <thead>
          <tr>
            <th class="px-4 py-2 border-b text-center">ID</th>
            <th class="px-4 py-2 border-b text-center">Name</th>
            <th class="px-4 py-2 border-b text-center">Type</th>
            <th class="px-4 py-2 border-b text-center">Lines</th>
            <th class="px-4 py-2 border-b text-center">Total Buttons</th>
            <th class="px-4 py-2 border-b text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="template in templates" :key="template.id">
            <td class="px-4 py-2 border-b text-center">{{ template.id }}</td>
            <td class="px-4 py-2 border-b text-center">{{ template.name }}</td>
            <td class="px-4 py-2 border-b text-center">
              <span
                :class="{
                  'bg-blue-100 text-blue-800': template.type === 'post',
                  'bg-green-100 text-green-800': template.type === 'start',
                  'bg-purple-100 text-purple-800': template.type === 'category',
                  'bg-orange-100 text-orange-800': template.type === 'product'
                }"
                class="px-2 py-1 rounded text-xs font-medium">
                {{ template.type }}
              </span>
            </td>
            <td class="px-4 py-2 border-b text-center">{{ template.layout.length }}</td>
            <td class="px-4 py-2 border-b text-center">{{ getTotalButtons(template.layout) }}</td>
            <td class="px-4 py-2 border-b text-center">
              <div class="relative inline-block text-left">
                <button @click="openDropdown(template.id)" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200">Actions</button>
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

