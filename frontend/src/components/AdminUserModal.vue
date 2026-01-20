<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Overlay -->
      <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal"></div>

      <!-- Modal -->
      <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-bold text-gray-900">
            {{ editMode ? 'Edit Admin User' : 'Create Admin User' }}
          </h3>
          <button
            @click="closeModal"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Admin Name -->
          <div>
            <label for="admin_name" class="block text-sm font-medium text-gray-700 mb-1">
              Admin Name *
            </label>
            <input
              id="admin_name"
              v-model="formData.admin_name"
              type="text"
              required
              maxlength="20"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter admin name"
            />
          </div>

          <!-- Password -->
          <div v-if="!editMode || showPasswordField">
            <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1">
              Password {{ editMode ? '' : '*' }}
            </label>
            <input
              id="admin_password"
              v-model="formData.admin_password"
              type="password"
              :required="!editMode"
              minlength="6"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter password"
            />
          </div>

          <!-- Change Password Button (Edit Mode) -->
          <div v-if="editMode && !showPasswordField">
            <button
              type="button"
              @click="showPasswordField = true"
              class="text-sm text-blue-600 hover:text-blue-800"
            >
              Change Password
            </button>
          </div>

          <!-- Bot Code -->
          <div>
            <label for="bot_code" class="block text-sm font-medium text-gray-700 mb-1">
              Bot Code
            </label>
            <input
              id="bot_code"
              v-model="formData.bot_code"
              type="text"
              maxlength="50"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter bot code"
            />
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-800">{{ errorMessage }}</p>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <svg v-if="submitting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ submitting ? 'Saving...' : (editMode ? 'Update' : 'Create') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, watch } from 'vue';
import type { AdminUser } from '../types/AdminUser';

const props = defineProps<{
  isOpen: boolean;
  admin?: AdminUser | null;
}>();

const emit = defineEmits<{
  close: [];
  submit: [data: Partial<AdminUser>];
}>();

const editMode = ref(false);
const showPasswordField = ref(false);
const submitting = ref(false);
const errorMessage = ref('');

const formData = ref({
  admin_name: '',
  admin_password: '',
  bot_code: '',
});

watch(() => props.admin, (admin) => {
  if (admin) {
    editMode.value = true;
    formData.value = {
      admin_name: admin.admin_name || '',
      admin_password: '',
      bot_code: admin.bot_code || '',
    };
    showPasswordField.value = false;
  } else {
    editMode.value = false;
    formData.value = {
      admin_name: '',
      admin_password: '',
      bot_code: '',
    };
    showPasswordField.value = false;
  }
  errorMessage.value = '';
  submitting.value = false;
}, { immediate: true });

watch(() => props.isOpen, (newValue) => {
  if (!newValue) {
    submitting.value = false;
    errorMessage.value = '';
  }
});

const closeModal = () => {
  submitting.value = false;
  errorMessage.value = '';
  emit('close');
};

const handleSubmit = async () => {
  submitting.value = true;
  errorMessage.value = '';

  try {
    const data: Record<string, any> = {
      admin_name: formData.value.admin_name,
      bot_code: formData.value.bot_code || null,
    };

    if (formData.value.admin_password) {
      data.admin_password = formData.value.admin_password;
    }

    emit('submit', data as Partial<AdminUser>);
  } catch (e) {
    errorMessage.value = e instanceof Error ? e.message : 'An error occurred';
  } finally {
    submitting.value = false;
  }
};
</script>

