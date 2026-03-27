<template>
  <div class="p-4">
    <h1 class="page-title">Users</h1>

    <!-- Filters -->
    <div class="section-card">
      <h2 class="text-lg font-semibold mb-3">Filters</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Status Filter -->
        <div class="form-group">
          <label class="form-label">Status</label>
          <select v-model="filters.status" @change="applyFilters" class="form-select">
            <option value="">All</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="blocked">Blocked</option>
          </select>
        </div>

        <!-- Created At From -->
        <div class="form-group">
          <label class="form-label">Created At From</label>
          <input
            type="datetime-local"
            v-model="filters.created_at_from"
            @change="applyFilters"
            class="form-input"
          />
        </div>

        <!-- Created At To -->
        <div class="form-group">
          <label class="form-label">Created At To</label>
          <input
            type="datetime-local"
            v-model="filters.created_at_to"
            @change="applyFilters"
            class="form-input"
          />
        </div>

        <!-- Updated At From -->
        <div class="form-group">
          <label class="form-label">Updated At From</label>
          <input
            type="datetime-local"
            v-model="filters.updated_at_from"
            @change="applyFilters"
            class="form-input"
          />
        </div>

        <!-- Updated At To -->
        <div class="form-group">
          <label class="form-label">Updated At To</label>
          <input
            type="datetime-local"
            v-model="filters.updated_at_to"
            @change="applyFilters"
            class="form-input"
          />
        </div>

        <!-- Username Filter -->
        <div class="form-group">
          <label class="form-label">Username</label>
          <input
            type="text"
            v-model="filters.username"
            @input="applyFilters"
            placeholder="Search by username..."
            class="form-input"
          />
        </div>

        <!-- Clear Filters Button -->
        <div class="flex items-end">
          <button @click="clearFilters" class="btn btn-secondary w-full">Clear Filters</button>
        </div>
      </div>
    </div>

    <!-- Mass Actions -->
    <div v-if="selectedUsers.length > 0" class="alert-info mb-4 flex items-center justify-between">
      <span class="text-sm font-medium text-blue-800">
        {{ selectedUsers.length }} user(s) selected
      </span>
      <div class="flex gap-2">
        <button @click="showSendPostModal = true" class="btn btn-success btn-sm">Send Post</button>
        <button @click="massDelete" class="btn btn-danger btn-sm">Delete Selected</button>
      </div>
    </div>

    <!-- Send Post Modal -->
    <div v-if="showSendPostModal" class="modal-overlay">
      <div class="modal-wrapper">
        <div class="modal-backdrop" @click="showSendPostModal = false; sendPostId = null"></div>
        <div class="modal-content max-w-sm">
          <h2 class="text-lg font-semibold mb-4">Send Post to {{ selectedUsers.length }} user(s)</h2>
          <div class="form-group">
            <label class="form-label">Post ID</label>
            <input
              type="number"
              v-model.number="sendPostId"
              min="1"
              placeholder="Enter post ID..."
              class="form-input"
            />
          </div>
          <div class="flex gap-2 justify-end">
            <button
              @click="showSendPostModal = false; sendPostId = null"
              class="btn btn-secondary"
            >
              Cancel
            </button>
            <button
              @click="massSendPost"
              :disabled="!sendPostId"
              class="btn btn-success"
            >
              Send
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="table-wrapper">
      <table class="data-table rounded-lg">
        <thead>
          <tr>
            <th class="table-th">
              <input
                type="checkbox"
                @change="toggleSelectAll"
                :checked="selectedUsers.length === users.length && users.length > 0"
                class="form-checkbox"
              />
            </th>
            <th class="table-th">ID</th>
            <th class="table-th">Name</th>
            <th class="table-th">Username</th>
            <th class="table-th">Chat ID</th>
            <th class="table-th">Status</th>
            <th class="table-th">Created At</th>
            <th class="table-th">Updated At</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id" class="table-row-hover">
            <td class="table-td">
              <input type="checkbox" :value="user.id" v-model="selectedUsers" class="form-checkbox" />
            </td>
            <td class="table-td">{{ user.id }}</td>
            <td class="table-td">{{ user.name }}</td>
            <td class="table-td">{{ user.username }}</td>
            <td class="table-td">{{ user.chat_id }}</td>
            <td class="table-td">
              <span :class="getStatusClass(user.status)" class="badge">{{ user.status }}</span>
            </td>
            <td class="table-td">{{ formatDate(user.created_at) }}</td>
            <td class="table-td">{{ formatDate(user.updated_at) }}</td>
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
          class="btn btn-secondary btn-sm"
        >
          Previous
        </button>
        <span class="text-sm text-gray-700">
          Page {{ pagination.currentPage }} of {{ pagination.totalPages }}
        </span>
        <button
          @click="goToPage(pagination.currentPage + 1)"
          :disabled="pagination.currentPage >= pagination.totalPages"
          class="btn btn-secondary btn-sm"
        >
          Next
        </button>
        <select
          v-model="pagination.itemsPerPage"
          @change="changeItemsPerPage"
          class="form-select w-auto text-sm"
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
const showSendPostModal = ref(false);
const sendPostId = ref<number | null>(null);
const filters = ref({
  status: '',
  username: '',
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
    if (filters.value.username) {
      params.append('username', filters.value.username);
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
    username: '',
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

const massSendPost = async () => {
  if (selectedUsers.value.length === 0 || !sendPostId.value) {
    return;
  }

  try {
    const { data } = await api.post('/api/users/mass-send-post', {
      ids: selectedUsers.value,
      post_id: sendPostId.value,
    });

    showSendPostModal.value = false;
    sendPostId.value = null;
    selectedUsers.value = [];
    alert(`Success: ${data.created} mailout(s) created`);
  } catch (error: any) {
    const errorMessage = error.response?.data?.error || 'Failed to create mailouts';
    alert(`Error: ${errorMessage}`);
  }
};

const getStatusClass = (status: string) => {
  switch (status?.toLowerCase()) {
    case 'active':
      return 'badge-green';
    case 'inactive':
      return 'badge-gray';
    case 'blocked':
      return 'badge-red';
    default:
      return 'badge-blue';
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
