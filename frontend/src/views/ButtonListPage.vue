<template>
  <div class="p-4 h-screen flex flex-col">
    <h1 class="page-title">Buttons</h1>
    <div class="table-wrapper flex-1 flex flex-col">
      <table class="data-table h-full">
        <thead>
          <tr>
            <th class="table-th">ID</th>
            <th class="table-th">Code</th>
            <th class="table-th">Label</th>
            <th class="table-th">Type</th>
            <th class="table-th">Value</th>
            <th class="table-th">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="button in buttons" :key="button.id" class="table-row-hover">
            <td class="table-td">{{ button.id }}</td>
            <td class="table-td">{{ button.code }}</td>
            <td class="table-td">{{ button.label }}</td>
            <td class="table-td">
              <span :class="button.buttonType === 'url' ? 'badge-blue' : 'badge-green'" class="badge">
                {{ button.buttonType }}
              </span>
            </td>
            <td class="table-td text-sm truncate max-w-xs" :title="button.value">{{ button.value }}</td>
            <td class="table-td">
              <div class="relative inline-block text-left">
                <button @click="openDropdown(button.id)" class="btn btn-secondary btn-sm">Actions</button>
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
    <button @click="createButton" class="btn btn-primary mt-4">Create Button</button>
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
