<template>
  <div class="p-4">
    <div class="flex items-center gap-4 mb-4">
      <h1 class="page-title mb-0">{{ t('users.title') }}</h1>
      <span v-if="dataLoaded" class="badge badge-blue text-sm px-3 py-1">
        {{ hasActiveFilters ? t('users.count_filtered', { count: pagination.totalItems }) : t('users.count_total', { count: pagination.totalItems }) }}
      </span>
      <input ref="csvFileInput" type="file" accept=".csv" class="hidden" @change="importUsers" />
      <button @click="(csvFileInput as HTMLInputElement).click()" :disabled="importing" class="btn btn-primary btn-sm">
        {{ importing ? t('users.importing') : t('users.import_users') }}
      </button>
    </div>

    <div class="section-card">
      <h2 class="text-lg font-semibold mb-3">{{ t('users.filters') }}</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="form-group">
          <label class="form-label">{{ t('users.status') }}</label>
          <select v-model="filters.status" @change="applyFilters" class="form-select">
            <option value="">{{ t('users.status_all') }}</option>
            <option value="active">{{ t('users.status_active') }}</option>
            <option value="inactive">{{ t('users.status_inactive') }}</option>
            <option value="blocked">{{ t('users.status_blocked') }}</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">{{ t('users.created_from') }}</label>
          <input type="datetime-local" v-model="filters.created_at_from" @change="applyFilters" class="form-input" />
        </div>
        <div class="form-group">
          <label class="form-label">{{ t('users.created_to') }}</label>
          <input type="datetime-local" v-model="filters.created_at_to" @change="applyFilters" class="form-input" />
        </div>
        <div class="form-group">
          <label class="form-label">{{ t('users.updated_from') }}</label>
          <input type="datetime-local" v-model="filters.updated_at_from" @change="applyFilters" class="form-input" />
        </div>
        <div class="form-group">
          <label class="form-label">{{ t('users.updated_to') }}</label>
          <input type="datetime-local" v-model="filters.updated_at_to" @change="applyFilters" class="form-input" />
        </div>
        <div class="form-group">
          <label class="form-label">{{ t('users.username') }}</label>
          <input type="text" v-model="filters.username" @input="applyFilters" :placeholder="t('users.search_username')" class="form-input" />
        </div>
        <div class="flex items-end">
          <button @click="clearFilters" class="btn btn-secondary w-full">{{ t('users.clear_filters') }}</button>
        </div>
      </div>
    </div>

    <div v-if="selectedUsers.length > 0" class="alert-info mb-4">
      <div class="flex items-center justify-between flex-wrap gap-2">
        <div class="flex items-center gap-3 flex-wrap">
          <span class="text-sm font-medium text-blue-800">
            {{ t('users.selected', { count: selectedUsers.length }) }}
          </span>
          <button
            v-if="!allPagesSelected && isPageFullySelected && pagination.totalItems > users.length"
            @click="selectAllPages"
            :disabled="loadingAllIds"
            class="text-sm text-blue-700 underline hover:text-blue-900 bg-transparent border-0 p-0 cursor-pointer"
          >
            {{ loadingAllIds ? t('users.loading_ids') : t('users.select_all_n', { total: pagination.totalItems }) }}
          </button>
          <span v-if="allPagesSelected" class="text-sm text-blue-600 italic">
            {{ t('users.all_selected', { total: pagination.totalItems }) }}
          </span>
        </div>
        <div class="flex gap-2">
          <button @click="showSendPostModal = true" class="btn btn-success btn-sm">{{ t('users.send_post') }}</button>
          <button @click="massDelete" class="btn btn-danger btn-sm">{{ t('users.delete_selected') }}</button>
        </div>
      </div>
    </div>

    <div v-if="showSendPostModal" class="modal-overlay">
      <div class="modal-wrapper">
        <div class="modal-backdrop" @click="showSendPostModal = false; sendPostId = null"></div>
        <div class="modal-content max-w-sm">
          <h2 class="text-lg font-semibold mb-4">{{ t('users.send_post_title', { count: selectedUsers.length }) }}</h2>
          <div class="form-group">
            <label class="form-label">{{ t('users.post_id') }}</label>
            <input type="number" v-model.number="sendPostId" min="1" :placeholder="t('users.post_id_placeholder')" class="form-input" />
          </div>
          <div class="flex gap-2 justify-end">
            <button @click="showSendPostModal = false; sendPostId = null" class="btn btn-secondary">
              {{ t('common.cancel') }}
            </button>
            <button @click="massSendPost" :disabled="!sendPostId" class="btn btn-success">
              {{ t('common.send') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="table-wrapper">
      <table class="data-table rounded-lg">
        <thead>
          <tr>
            <th class="table-th">
              <input type="checkbox" @change="toggleSelectAll" :checked="isPageFullySelected" class="form-checkbox" />
            </th>
            <th class="table-th cursor-pointer select-none" @click="toggleSort('id')">
              {{ t('table.id') }} <span class="text-xs opacity-60">{{ getSortIcon('id') }}</span>
            </th>
            <th class="table-th cursor-pointer select-none" @click="toggleSort('name')">
              {{ t('users.name') }} <span class="text-xs opacity-60">{{ getSortIcon('name') }}</span>
            </th>
            <th class="table-th cursor-pointer select-none" @click="toggleSort('username')">
              {{ t('users.username') }} <span class="text-xs opacity-60">{{ getSortIcon('username') }}</span>
            </th>
            <th class="table-th cursor-pointer select-none" @click="toggleSort('chat_id')">
              {{ t('users.chat_id') }} <span class="text-xs opacity-60">{{ getSortIcon('chat_id') }}</span>
            </th>
            <th class="table-th cursor-pointer select-none" @click="toggleSort('status')">
              {{ t('table.status') }} <span class="text-xs opacity-60">{{ getSortIcon('status') }}</span>
            </th>
            <th class="table-th cursor-pointer select-none" @click="toggleSort('created_at')">
              {{ t('users.created_at') }} <span class="text-xs opacity-60">{{ getSortIcon('created_at') }}</span>
            </th>
            <th class="table-th cursor-pointer select-none" @click="toggleSort('updated_at')">
              {{ t('users.updated_at') }} <span class="text-xs opacity-60">{{ getSortIcon('updated_at') }}</span>
            </th>
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
            <td colspan="8" class="px-4 py-8 text-center text-gray-500">{{ t('users.no_users') }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="pagination.totalItems > 0" class="mt-4 flex items-center justify-between flex-wrap gap-2">
      <div class="text-sm text-gray-700">
        {{ t('users.showing', { first: pagination.firstItem, last: pagination.lastItem, total: pagination.totalItems }) }}
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <button @click="goToPage(1)" :disabled="pagination.currentPage === 1" class="btn btn-secondary btn-sm">
          {{ t('users.first') }}
        </button>
        <button @click="goToPage(pagination.currentPage - 1)" :disabled="pagination.currentPage === 1" class="btn btn-secondary btn-sm">
          {{ t('users.previous') }}
        </button>
        <div class="flex items-center gap-1 text-sm text-gray-700">
          <input
            type="number"
            v-model.number="pageInput"
            @keyup.enter="goToPage(pageInput)"
            @blur="goToPage(pageInput)"
            min="1"
            :max="pagination.totalPages"
            class="form-input w-16 text-center text-sm py-1"
          />
          <span>{{ t('users.of_pages', { total: pagination.totalPages }) }}</span>
        </div>
        <button @click="goToPage(pagination.currentPage + 1)" :disabled="pagination.currentPage >= pagination.totalPages" class="btn btn-secondary btn-sm">
          {{ t('users.next') }}
        </button>
        <button @click="goToPage(pagination.totalPages)" :disabled="pagination.currentPage >= pagination.totalPages" class="btn btn-secondary btn-sm">
          {{ t('users.last') }}
        </button>
        <select v-model="pagination.itemsPerPage" @change="changeItemsPerPage" class="form-select w-auto text-sm">
          <option :value="20">{{ t('users.per_page', { n: 20 }) }}</option>
          <option :value="50">{{ t('users.per_page', { n: 50 }) }}</option>
          <option :value="100">{{ t('users.per_page', { n: 100 }) }}</option>
          <option :value="200">{{ t('users.per_page', { n: 200 }) }}</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../api';
import type { User } from '../types/User';

const { t } = useI18n();
const users = ref<User[]>([]);
const dataLoaded = ref(false);
const csvFileInput = ref<HTMLInputElement | null>(null);
const importing = ref(false);
const selectedUsers = ref<number[]>([]);
const allPagesSelected = ref(false);
const loadingAllIds = ref(false);
const showSendPostModal = ref(false);
const sendPostId = ref<number | null>(null);
const sortColumn = ref('');
const sortDirection = ref<'asc' | 'desc'>('asc');
const pageInput = ref(1);

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
  itemsPerPage: 100,
  totalItems: 0,
  totalPages: 0,
  firstItem: 0,
  lastItem: 0,
});

const hasActiveFilters = computed(() =>
  Object.values(filters.value).some(v => v !== '')
);

const isPageFullySelected = computed(
  () => users.value.length > 0 && users.value.every(u => selectedUsers.value.includes(u.id))
);

const buildFilterParams = (): URLSearchParams => {
  const params = new URLSearchParams();
  if (filters.value.status) params.append('status', filters.value.status);
  if (filters.value.username) params.append('username', filters.value.username);
  if (filters.value.created_at_from) params.append('created_at[after]', filters.value.created_at_from);
  if (filters.value.created_at_to) params.append('created_at[before]', filters.value.created_at_to);
  if (filters.value.updated_at_from) params.append('updated_at[after]', filters.value.updated_at_from);
  if (filters.value.updated_at_to) params.append('updated_at[before]', filters.value.updated_at_to);
  if (sortColumn.value) params.append(`order[${sortColumn.value}]`, sortDirection.value);
  return params;
};

const fetchUsers = async () => {
  try {
    const params = buildFilterParams();
    params.append('page', pagination.value.currentPage.toString());
    params.append('itemsPerPage', pagination.value.itemsPerPage.toString());

    const { data } = await api.get(`/api/users?${params.toString()}`);
    users.value = data['hydra:member'] || data['member'] || [];

    const serverTotal = data['hydra:totalItems'] ?? data['totalItems'];
    if (serverTotal !== undefined) {
      pagination.value.totalItems = serverTotal;
    }
    dataLoaded.value = true;
    if (pagination.value.totalItems > 0) {
      pagination.value.totalPages = Math.ceil(pagination.value.totalItems / pagination.value.itemsPerPage);
      pagination.value.firstItem = (pagination.value.currentPage - 1) * pagination.value.itemsPerPage + 1;
      pagination.value.lastItem = Math.min(pagination.value.currentPage * pagination.value.itemsPerPage, pagination.value.totalItems);
    } else {
      pagination.value.totalPages = 0;
      pagination.value.firstItem = 0;
      pagination.value.lastItem = 0;
    }
    pageInput.value = pagination.value.currentPage;
    if (!allPagesSelected.value) {
      selectedUsers.value = [];
    }
  } catch (error) {
    console.error('Error fetching users:', error);
    users.value = [];
  }
};

const applyFilters = () => {
  pagination.value.currentPage = 1;
  allPagesSelected.value = false;
  selectedUsers.value = [];
  fetchUsers();
};

const clearFilters = () => {
  filters.value = { status: '', username: '', created_at_from: '', created_at_to: '', updated_at_from: '', updated_at_to: '' };
  sortColumn.value = '';
  sortDirection.value = 'asc';
  pagination.value.currentPage = 1;
  allPagesSelected.value = false;
  selectedUsers.value = [];
  fetchUsers();
};

const goToPage = (page: number) => {
  const target = Math.max(1, Math.min(page, pagination.value.totalPages));
  if (target !== pagination.value.currentPage) {
    pagination.value.currentPage = target;
    fetchUsers();
  }
  pageInput.value = pagination.value.currentPage;
};

const changeItemsPerPage = () => {
  pagination.value.currentPage = 1;
  allPagesSelected.value = false;
  selectedUsers.value = [];
  fetchUsers();
};

const toggleSort = (column: string) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
  pagination.value.currentPage = 1;
  fetchUsers();
};

const getSortIcon = (column: string): string => {
  if (sortColumn.value !== column) return '⇅';
  return sortDirection.value === 'asc' ? '▲' : '▼';
};

const toggleSelectAll = (event: Event) => {
  const checked = (event.target as HTMLInputElement).checked;
  if (checked) {
    selectedUsers.value = users.value.map(u => u.id);
  } else {
    selectedUsers.value = [];
    allPagesSelected.value = false;
  }
};

const selectAllPages = async () => {
  loadingAllIds.value = true;
  try {
    const params = buildFilterParams();
    const { data } = await api.get(`/api/users/select-all-ids?${params.toString()}`);
    selectedUsers.value = data.ids ?? [];
    allPagesSelected.value = true;
  } catch (error) {
    console.error('Error fetching all user IDs:', error);
  } finally {
    loadingAllIds.value = false;
  }
};

const massDelete = async () => {
  if (selectedUsers.value.length === 0) return;
  if (!confirm(t('users.confirm_delete_selected', { count: selectedUsers.value.length }))) return;
  try {
    await api.post('/api/users/mass-delete', { ids: selectedUsers.value });
    selectedUsers.value = [];
    allPagesSelected.value = false;
    fetchUsers();
    alert(t('users.deleted_successfully'));
  } catch (error: any) {
    const errorMessage = error.response?.data?.error || t('common.error');
    alert(`${t('common.error')}: ${errorMessage}`);
  }
};

const massSendPost = async () => {
  if (selectedUsers.value.length === 0 || !sendPostId.value) return;
  try {
    const { data } = await api.post('/api/users/mass-send-post', {
      ids: selectedUsers.value,
      post_id: sendPostId.value,
    });
    showSendPostModal.value = false;
    sendPostId.value = null;
    selectedUsers.value = [];
    allPagesSelected.value = false;
    alert(t('users.mailouts_created', { count: data.created }));
  } catch (error: any) {
    const errorMessage = error.response?.data?.error || t('common.error');
    alert(`${t('common.error')}: ${errorMessage}`);
  }
};

const importUsers = async (event: Event) => {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;

  importing.value = true;
  const formData = new FormData();
  formData.append('file', file);

  try {
    const { data } = await api.post('/api/users/import-csv', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    alert(t('users.import_result', { imported: data.imported, skipped: data.skipped }));
    fetchUsers();
  } catch (error: any) {
    const errorMessage = error.response?.data?.error || t('users.import_error');
    alert(`${t('users.import_error')}: ${errorMessage}`);
  } finally {
    importing.value = false;
    input.value = '';
  }
};

const getStatusClass = (status: string) => {
  switch (status?.toLowerCase()) {
    case 'active': return 'badge-green';
    case 'inactive': return 'badge-gray';
    case 'blocked': return 'badge-red';
    default: return 'badge-blue';
  }
};

const formatDate = (dateString?: string) => {
  if (!dateString) return '-';
  try {
    return new Date(dateString).toLocaleString();
  } catch {
    return dateString;
  }
};

onMounted(fetchUsers);
</script>
