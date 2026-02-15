<template>
  <div class="p-4">
    <h1 class="text-xl font-bold mb-4">Users</h1>
    
    <!-- Filters -->
    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
      <h2 class="text-lg font-semibold mb-3">Filters</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.status" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="blocked">Blocked</option>
          </select>
        </div>

        <!-- Created At From -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Created At From</label>
          <input 
            type="datetime-local" 
            v-model="filters.created_at_from" 
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Created At To -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Created At To</label>
          <input 
            type="datetime-local" 
            v-model="filters.created_at_to" 
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Updated At From -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Updated At From</label>
          <input 
            type="datetime-local" 
            v-model="filters.updated_at_from" 
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Updated At To -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Updated At To</label>
          <input 
            type="datetime-local" 
            v-model="filters.updated_at_to" 
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Clear Filters Button -->
        <div class="flex items-end">
          <button 
            @click="clearFilters" 
            class="w-full px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
          >
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Mass Actions -->
    <div v-if="selectedUsers.length > 0" class="mb-4 p-3 bg-blue-50 rounded-lg flex items-center justify-between">
      <span class="text-sm font-medium text-blue-800">
        {{ selectedUsers.length }} user(s) selected
      </span>
      <button 
        @click="massDelete" 
        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm font-medium"
      >
        Delete Selected
      </button>
    </div>

    <!-- Users Grid -->
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white border border-gray-200 rounded-lg">
        <thead>
          <tr>
            <th class="px-4 py-2 border-b text-center">
              <input 
                type="checkbox" 
                @change="toggleSelectAll" 
                :checked="selectedUsers.length === users.length && users.length > 0"
                class="cursor-pointer"
              />
            </th>
            <th class="px-4 py-2 border-b text-center">ID</th>
            <th class="px-4 py-2 border-b text-center">Name</th>
            <th class="px-4 py-2 border-b text-center">Username</th>
            <th class="px-4 py-2 border-b text-center">Chat ID</th>
            <th class="px-4 py-2 border-b text-center">Status</th>
            <th class="px-4 py-2 border-b text-center">Created At</th>
            <th class="px-4 py-2 border-b text-center">Updated At</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td class="px-4 py-2 border-b text-center">
              <input 
                type="checkbox" 
                :value="user.id" 
                v-model="selectedUsers"
                class="cursor-pointer"
              />
            </td>
            <td class="px-4 py-2 border-b text-center">{{ user.id }}</td>
            <td class="px-4 py-2 border-b text-center">{{ user.name }}</td>
            <td class="px-4 py-2 border-b text-center">{{ user.username }}</td>
            <td class="px-4 py-2 border-b text-center">{{ user.chat_id }}</td>
            <td class="px-4 py-2 border-b text-center">
              <span :class="getStatusClass(user.status)" class="px-2 py-1 rounded text-sm">
                {{ user.status }}
              </span>
            </td>
            <td class="px-4 py-2 border-b text-center">{{ formatDate(user.created_at) }}</td>
            <td class="px-4 py-2 border-b text-center">{{ formatDate(user.updated_at) }}</td>
          </tr>
          <tr v-if="users.length === 0">
            <td colspan="8" class="px-4 py-8 text-center text-gray-500">No users found</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.totalItems > 0" class="mt-4 flex items-center justify-between">
      <div class="text-sm text-gray-700">
        Showing {{ pagination.firstItem }} to {{ pagination.lastItem }} of {{ pagination.totalItems }} users
      </div>
      <div class="flex items-center gap-2">
        <button 
          @click="goToPage(pagination.currentPage - 1)" 
          :disabled="pagination.currentPage === 1"
          class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Previous
        </button>
        <span class="text-sm text-gray-700">
          Page {{ pagination.currentPage }} of {{ pagination.totalPages }}
        </span>
        <button 
          @click="goToPage(pagination.currentPage + 1)" 
          :disabled="pagination.currentPage >= pagination.totalPages"
          class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Next
        </button>
        <select 
          v-model="pagination.itemsPerPage" 
          @change="changeItemsPerPage"
          class="px-2 py-1 border border-gray-300 rounded text-sm"
        >
          <option :value="10">10 per page</option>
          <option :value="20">20 per page</option>
          <option :value="50">50 per page</option>
          <option :value="100">100 per page</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../api';
import type { User } from '../types/User';

const users = ref<User[]>([]);
const selectedUsers = ref<number[]>([]);
const filters = ref({
  status: '',
  created_at_from: '',
  created_at_to: '',
  updated_at_from: '',
  updated_at_to: '',
});
const pagination = ref({
  currentPage: 1,
  itemsPerPage: 20,
  totalItems: 0,
  totalPages: 0,
  firstItem: 0,
  lastItem: 0,
});

const fetchUsers = async () => {
  try {
    const params = new URLSearchParams();
    if (filters.value.status) {
      params.append('status', filters.value.status);
    }
    if (filters.value.created_at_from) {
      params.append('created_at[gte]', filters.value.created_at_from);
    }
    if (filters.value.created_at_to) {
      params.append('created_at[lte]', filters.value.created_at_to);
    }
    if (filters.value.updated_at_from) {
      params.append('updated_at[gte]', filters.value.updated_at_from);
    }
    if (filters.value.updated_at_to) {
      params.append('updated_at[lte]', filters.value.updated_at_to);
    }
    
    // Add pagination parameters
    params.append('page', pagination.value.currentPage.toString());
    params.append('itemsPerPage', pagination.value.itemsPerPage.toString());

    const { data } = await api.get(`/api/users?${params.toString()}`);
    users.value = data['hydra:member'] || data['member'] || [];
    
    // Update pagination info from API Platform response
    if (data['hydra:totalItems'] !== undefined) {
      pagination.value.totalItems = data['hydra:totalItems'];
    }
    
    // Calculate total pages
    if (pagination.value.totalItems > 0) {
      pagination.value.totalPages = Math.ceil(pagination.value.totalItems / pagination.value.itemsPerPage);
    } else {
      pagination.value.totalPages = 0;
    }
    
    // Calculate first and last item numbers
    if (pagination.value.totalItems > 0) {
      pagination.value.firstItem = (pagination.value.currentPage - 1) * pagination.value.itemsPerPage + 1;
      pagination.value.lastItem = Math.min(
        pagination.value.currentPage * pagination.value.itemsPerPage,
        pagination.value.totalItems
      );
    } else {
      pagination.value.firstItem = 0;
      pagination.value.lastItem = 0;
    }
    
    // Clear selection when data changes
    selectedUsers.value = [];
  } catch (error) {
    console.error('Error fetching users:', error);
    users.value = [];
  }
};

const applyFilters = () => {
  pagination.value.currentPage = 1;
  fetchUsers();
};

const clearFilters = () => {
  filters.value = {
    status: '',
    created_at_from: '',
    created_at_to: '',
    updated_at_from: '',
    updated_at_to: '',
  };
  pagination.value.currentPage = 1;
  fetchUsers();
};

const goToPage = (page: number) => {
  if (page >= 1 && page <= pagination.value.totalPages) {
    pagination.value.currentPage = page;
    fetchUsers();
  }
};

const changeItemsPerPage = () => {
  pagination.value.currentPage = 1;
  fetchUsers();
};

const toggleSelectAll = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.checked) {
    selectedUsers.value = users.value.map(u => u.id);
  } else {
    selectedUsers.value = [];
  }
};

const massDelete = async () => {
  if (selectedUsers.value.length === 0) {
    return;
  }

  if (!confirm(`Are you sure you want to delete ${selectedUsers.value.length} user(s)?`)) {
    return;
  }

  try {
    await api.post('/api/users/mass-delete', {
      ids: selectedUsers.value
    });
    
    selectedUsers.value = [];
    fetchUsers();
    alert('Users deleted successfully');
  } catch (error: any) {
    const errorMessage = error.response?.data?.error || 'Failed to delete users';
    alert(`Error: ${errorMessage}`);
  }
};

const getStatusClass = (status: string) => {
  switch (status?.toLowerCase()) {
    case 'active':
      return 'bg-green-100 text-green-800';
    case 'inactive':
      return 'bg-gray-100 text-gray-800';
    case 'blocked':
      return 'bg-red-100 text-red-800';
    default:
      return 'bg-blue-100 text-blue-800';
  }
};

const formatDate = (dateString?: string) => {
  if (!dateString) return '-';
  try {
    const date = new Date(dateString);
    return date.toLocaleString();
  } catch {
    return dateString;
  }
};

onMounted(fetchUsers);
</script>
