<template>
  <div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
    <div class="modal-wrapper">
      <div class="modal-backdrop" @click="closeModal"></div>
      <div class="modal-content">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-bold text-gray-900">
            {{ editMode ? t('admin_user_modal.edit_title') : t('admin_user_modal.create_title') }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div class="form-group">
            <label for="admin_name" class="form-label">{{ t('admin_user_modal.admin_name') }}</label>
            <input
              id="admin_name"
              v-model="formData.admin_name"
              type="text"
              required
              maxlength="20"
              class="form-input"
              :placeholder="t('admin_user_modal.admin_name_placeholder')"
            />
          </div>
          <div v-if="!editMode || showPasswordField" class="form-group">
            <label for="admin_password" class="form-label">
              {{ editMode ? t('admin_user_modal.password') : t('admin_user_modal.password_required') }}
            </label>
            <input
              id="admin_password"
              v-model="formData.admin_password"
              type="password"
              :required="!editMode"
              minlength="6"
              class="form-input"
              :placeholder="t('admin_user_modal.password_placeholder')"
            />
          </div>
          <div v-if="editMode && !showPasswordField">
            <button
              type="button"
              @click="showPasswordField = true"
              class="text-sm text-blue-600 hover:text-blue-800 cursor-pointer"
            >
              {{ t('admin_user_modal.change_password') }}
            </button>
          </div>
          <div class="form-group">
            <label for="bot_code" class="form-label">{{ t('admin_user_modal.bot_code') }}</label>
            <input
              id="bot_code"
              v-model="formData.bot_code"
              type="text"
              maxlength="50"
              class="form-input"
              :placeholder="t('admin_user_modal.bot_code_placeholder')"
            />
          </div>
          <div class="form-group">
            <label for="bot_identifier" class="form-label">{{ t('admin_user_modal.bot_identifier') }}</label>
            <input
              id="bot_identifier"
              v-model="formData.bot_identifier"
              type="text"
              maxlength="50"
              class="form-input"
              :placeholder="t('admin_user_modal.bot_identifier_placeholder')"
            />
          </div>
          <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-800">{{ errorMessage }}</p>
          </div>
          <div class="flex justify-end gap-3 pt-4">
            <button type="button" @click="closeModal" class="btn btn-secondary">
              {{ t('common.cancel') }}
            </button>
            <button type="submit" :disabled="submitting" class="btn btn-primary flex items-center gap-2">
              <svg v-if="submitting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ submitting ? t('admin_user_modal.saving') : (editMode ? t('admin_user_modal.update') : t('admin_user_modal.create')) }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import type { AdminUser } from '../types/AdminUser';

const { t } = useI18n();

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
  bot_identifier: '',
});

watch(() => props.admin, (admin) => {
  if (admin) {
    editMode.value = true;
    formData.value = {
      admin_name: admin.admin_name || '',
      admin_password: '',
      bot_code: admin.bot_code || '',
      bot_identifier: admin.bot_identifier || '',
    };
    showPasswordField.value = false;
  } else {
    editMode.value = false;
    formData.value = {
      admin_name: '',
      admin_password: '',
      bot_code: '',
      bot_identifier: '',
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
      bot_identifier: formData.value.bot_identifier || null,
    };

    if (formData.value.admin_password) {
      data.admin_password = formData.value.admin_password;
    }

    emit('submit', data as Partial<AdminUser>);
  } catch (e) {
    errorMessage.value = e instanceof Error ? e.message : t('admin_user_modal.an_error_occurred');
  } finally {
    submitting.value = false;
  }
};
</script>
