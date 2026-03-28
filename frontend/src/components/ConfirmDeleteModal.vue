<template>
  <div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
    <div class="modal-wrapper">
      <div class="modal-backdrop" @click="closeModal"></div>
      <div class="modal-content">
        <div class="modal-icon-danger">
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <div class="text-center">
          <h3 class="text-lg font-bold text-gray-900 mb-2">
            {{ t('confirm_delete_modal.title') }}
          </h3>
          <p class="text-sm text-gray-600 mb-6">
            {{ t('confirm_delete_modal.message', { name: adminName }) }}
          </p>
        </div>
        <div class="flex justify-center gap-3">
          <button type="button" @click="closeModal" class="btn btn-secondary">
            {{ t('common.cancel') }}
          </button>
          <button type="button" @click="confirmDelete" :disabled="deleting" class="btn btn-danger flex items-center gap-2">
            <svg v-if="deleting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ deleting ? t('confirm_delete_modal.deleting') : t('common.delete') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
  isOpen: boolean;
  adminName: string;
}>();

const emit = defineEmits<{
  close: [];
  confirm: [];
}>();

const deleting = ref(false);

watch(() => props.isOpen, (newValue) => {
  if (!newValue) {
    deleting.value = false;
  }
});

const closeModal = () => {
  deleting.value = false;
  emit('close');
};

const confirmDelete = async () => {
  deleting.value = true;
  emit('confirm');
};
</script>
