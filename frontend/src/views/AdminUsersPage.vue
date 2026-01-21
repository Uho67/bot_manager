<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 px-4 py-6 sm:px-6 lg:px-8 pt-20">
    <!-- Header Section -->
    <div class="max-w-7xl mx-auto">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">Admin Users</h1>
          <p class="text-gray-600">Manage your admin users and permissions</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-3">
          <button
            @click="openCreateModal"
            class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg shadow-md hover:bg-green-700 hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Admin
          </button>
          <button
            @click="fetchAdmins"
            :disabled="loading"
            class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg shadow-md hover:bg-blue-700 hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
            </svg>
            <svg v-else class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ loading ? 'Loading...' : 'Refresh' }}
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading && !admins.length" class="flex items-center justify-center py-20">
        <div class="text-center">
          <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="text-gray-600 text-lg">Loading admin users...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-if="error" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
        <div class="flex items-center">
          <svg class="h-6 w-6 text-red-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <h3 class="text-red-800 font-medium">Error loading admin users</h3>
            <p class="text-red-700 text-sm mt-1">{{ error.message }}</p>
          </div>
        </div>
      </div>

      <!-- Admin Users Grid -->
      <div v-if="admins.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
        <div
          v-for="admin in admins"
          :key="admin.id"
          class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-blue-300 transform hover:-translate-y-1"
        >
          <!-- Card Header -->
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-lg">
                  {{ admin.admin_name?.charAt(0).toUpperCase() || 'A' }}
                </div>
                <div>
                  <h3 class="text-white font-semibold text-lg truncate max-w-[150px]">
                    {{ admin.admin_name }}
                  </h3>
                  <p class="text-blue-100 text-xs">ID: {{ admin.id }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Card Body -->
          <div class="px-6 py-4 space-y-3">
            <div class="flex items-start">
              <svg class="h-5 w-5 text-gray-400 mr-3 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
              </svg>
              <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 mb-1">Bot Code</p>
                <p class="text-sm font-medium text-gray-900 truncate">
                  {{ admin.bot_code || 'Not assigned' }}
                </p>
              </div>
            </div>

            <div class="flex items-center pt-2 border-t border-gray-100">
              <svg class="h-5 w-5 text-gray-400 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              <div class="flex-1">
                <p class="text-xs text-gray-500">Permission Level</p>
              </div>
            </div>
          </div>

          <!-- Card Footer -->
          <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex gap-2">
            <button
              @click="openEditModal(admin)"
              class="flex-1 text-center text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors py-2 hover:bg-blue-50 rounded"
            >
              <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Edit
            </button>
            <button
              @click="openDeleteModal(admin)"
              class="flex-1 text-center text-sm text-red-600 hover:text-red-800 font-medium transition-colors py-2 hover:bg-red-50 rounded"
            >
              <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!loading" class="text-center py-20">
        <svg class="mx-auto h-24 w-24 text-gray-300 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <h3 class="text-xl font-medium text-gray-900 mb-2">No admin users found</h3>
        <p class="text-gray-500">Get started by creating your first admin user.</p>
        <button
          @click="openCreateModal"
          class="mt-6 px-6 py-3 bg-blue-600 text-white font-medium rounded-lg shadow hover:bg-blue-700 transition-colors"
        >
          Create Admin User
        </button>
      </div>

      <!-- Stats Footer -->
      <div v-if="admins.length" class="mt-8 bg-white rounded-lg shadow-md p-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div class="text-center">
            <p class="text-3xl font-bold text-blue-600">{{ admins.length }}</p>
            <p class="text-sm text-gray-600 mt-1">Total Admins</p>
          </div>
          <div class="text-center">
            <p class="text-3xl font-bold text-green-600">{{ admins.filter(a => a.bot_code).length }}</p>
            <p class="text-sm text-gray-600 mt-1">Assigned Bots</p>
          </div>
          <div class="text-center">
            <p class="text-3xl font-bold text-gray-600">{{ admins.filter(a => !a.bot_code).length }}</p>
            <p class="text-sm text-gray-600 mt-1">Unassigned</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <AdminUserModal
      :is-open="isModalOpen"
      :admin="selectedAdmin"
      @close="closeModal"
      @submit="handleSubmit"
    />

    <ConfirmDeleteModal
      :is-open="isDeleteModalOpen"
      :admin-name="adminToDelete?.admin_name || ''"
      @close="closeDeleteModal"
      @confirm="handleDelete"
    />
  </div>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue';
import { useAdminUsers } from '../composables/useAdminUsers';
import type { AdminUser } from '../types/AdminUser';
import AdminUserModal from '../components/AdminUserModal.vue';
import ConfirmDeleteModal from '../components/ConfirmDeleteModal.vue';

const { admins, loading, error, fetchAdmins, createAdmin, updateAdmin, deleteAdmin } = useAdminUsers();

// Modal states
const isModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedAdmin = ref<AdminUser | null>(null);
const adminToDelete = ref<AdminUser | null>(null);

onMounted(fetchAdmins);

const openCreateModal = () => {
  selectedAdmin.value = null;
  isModalOpen.value = true;
};

const openEditModal = (admin: AdminUser) => {
  selectedAdmin.value = admin;
  isModalOpen.value = true;
};

const openDeleteModal = (admin: AdminUser) => {
  adminToDelete.value = admin;
  isDeleteModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  selectedAdmin.value = null;
};

const closeDeleteModal = () => {
  isDeleteModalOpen.value = false;
  adminToDelete.value = null;
};

const handleSubmit = async (data: Partial<AdminUser>) => {
  try {
    if (selectedAdmin.value) {
      await updateAdmin(selectedAdmin.value.id, data);
    } else {
      await createAdmin(data as Omit<AdminUser, 'id'>);
    }
    closeModal();
  } catch (e) {
    console.error('Error saving admin:', e);
  }
};

const handleDelete = async () => {
  if (adminToDelete.value) {
    try {
      await deleteAdmin(adminToDelete.value.id);
      // Небольшая задержка для плавности UI
      setTimeout(() => {
        closeDeleteModal();
      }, 300);
    } catch (e) {
      console.error('Error deleting admin:', e);
      closeDeleteModal();
    }
  }
};
</script>
