<template>
  <div class="p-4 h-screen flex flex-col">
    <h1 class="text-xl font-bold mb-4">Buttons</h1>
    <div class="overflow-x-auto flex-1 flex flex-col">
      <table class="min-w-full bg-white border border-gray-200 rounded-lg h-full">
        <thead>
          <tr>
            <th class="px-4 py-2 border-b text-center">ID</th>
            <th class="px-4 py-2 border-b text-center">Code</th>
            <th class="px-4 py-2 border-b text-center">Label</th>
            <th class="px-4 py-2 border-b text-center">Type</th>
            <th class="px-4 py-2 border-b text-center">Value</th>
            <th class="px-4 py-2 border-b text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="button in buttons" :key="button.id">
            <td class="px-4 py-2 border-b text-center">{{ button.id }}</td>
            <td class="px-4 py-2 border-b text-center">{{ button.code }}</td>
            <td class="px-4 py-2 border-b text-center">{{ button.label }}</td>
            <td class="px-4 py-2 border-b text-center">
              <span :class="button.buttonType === 'url' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'" class="px-2 py-1 rounded text-xs font-medium">
                {{ button.buttonType }}
              </span>
            </td>
            <td class="px-4 py-2 border-b text-center text-sm truncate max-w-xs" :title="button.value">{{ button.value }}</td>
            <td class="px-4 py-2 border-b text-center">
              <div class="relative inline-block text-left">
                <button @click="openDropdown(button.id)" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200">Actions</button>
                <div v-if="dropdownOpen === button.id" class="absolute z-10 w-32 bg-white border rounded shadow-lg right-0 top-full mt-1">
                  <button @click="editButton(button.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Edit</button>
                  <button @click="duplicateButton(button.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Duplicate</button>
                  <button @click="deleteButton(button.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">Delete</button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <button @click="createButton" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create Button</button>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';
import type { Button } from '../types/Button';

const buttons = ref<Button[]>([]);
const dropdownOpen = ref<number|null>(null);
const router = useRouter();

const fetchButtons = async () => {
  const { data } = await api.get('/api/buttons');
  buttons.value = data['member'] || [];
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

const editButton = (id: number) => {
  closeDropdown();
  router.push({ name: 'ButtonEdit', params: { id } });
};

const duplicateButton = (id: number) => {
  closeDropdown();
  router.push({ name: 'ButtonCreate', query: { duplicateFrom: id.toString() } });
};

const deleteButton = async (id: number) => {
  if (confirm('Are you sure you want to delete this button?')) {
    closeDropdown();
    await api.delete(`/api/buttons/${id}`);
    fetchButtons();
  }
};

const createButton = () => {
  router.push({ name: 'ButtonCreate' });
};

onMounted(() => {
  fetchButtons();
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
