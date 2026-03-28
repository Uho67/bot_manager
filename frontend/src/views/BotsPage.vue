<template>
  <div class="page-wrapper px-4 py-6 sm:px-6 lg:px-8 pt-20 bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ t('bots.title') }}</h1>
          <p class="text-gray-600">{{ t('bots.subtitle') }}</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-3">
          <button @click="openCreateModal" class="btn btn-success flex items-center justify-center gap-2 px-6 py-3 shadow-md hover:shadow-lg">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ t('bots.create') }}
          </button>
          <button @click="fetchBots" :disabled="loading" class="btn btn-primary flex items-center justify-center gap-2 px-6 py-3 shadow-md hover:shadow-lg">
            <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
            </svg>
            <svg v-else class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ loading ? t('common.loading') : t('common.refresh') }}
          </button>
          <button @click="goBack" class="btn btn-secondary px-6 py-3 shadow-md hover:shadow-lg">
            {{ t('common.back_to_menu') }}
          </button>
        </div>
      </div>

      <div v-if="loading && !bots.length" class="spinner-overlay">
        <div class="text-center">
          <svg class="spinner-lg animate-spin mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="text-gray-600 text-lg">{{ t('bots.loading') }}</p>
        </div>
      </div>

      <div v-if="error" class="alert-error">
        <div class="flex items-center">
          <svg class="h-6 w-6 text-red-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <h3 class="text-red-800 font-medium">{{ t('bots.error_loading') }}</h3>
            <p class="text-red-700 text-sm mt-1">{{ error }}</p>
          </div>
        </div>
      </div>

      <div v-if="bots.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
        <div v-for="bot in bots" :key="bot.id" class="bot-card">
          <div class="bot-card-header">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="bot-card-avatar">
                  <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-white font-semibold text-lg truncate max-w-[150px]">{{ bot.bot_identifier }}</h3>
                  <p class="text-green-100 text-xs">ID: {{ bot.id }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="card-body space-y-3">
            <div class="flex items-start">
              <svg class="h-5 w-5 text-gray-400 mr-3 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
              </svg>
              <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 mb-1">{{ t('bots.bot_code') }}</p>
                <p class="text-sm font-medium text-gray-900 truncate">{{ bot.bot_code }}</p>
              </div>
            </div>
          </div>

          <div class="card-footer flex gap-2">
            <button @click="openEditModal(bot)" class="btn btn-primary btn-sm flex-1 flex items-center justify-center gap-2">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              {{ t('common.edit') }}
            </button>
            <button @click="deleteBot(bot)" :disabled="deletingId === bot.id" class="btn btn-danger btn-sm flex-1 flex items-center justify-center gap-2">
              <svg v-if="deletingId !== bot.id" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              <svg v-else class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ deletingId === bot.id ? t('common.deleting') : t('common.delete') }}
            </button>
          </div>
        </div>
      </div>

      <div v-if="!loading && !bots.length && !error" class="empty-state">
        <svg class="h-24 w-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
        </svg>
        <p class="text-gray-500 text-lg mb-2">{{ t('bots.no_bots') }}</p>
        <p class="text-gray-400 text-sm">{{ t('bots.create_first') }}</p>
      </div>
    </div>

    <div v-if="showModal" class="modal-overlay">
      <div class="modal-wrapper">
        <div class="modal-backdrop" @click="closeModal"></div>
        <div class="modal-content">
          <h2 class="text-2xl font-bold mb-4">{{ editingBot ? t('bots.edit_title') : t('bots.create_title') }}</h2>
          <form @submit.prevent="saveBot" class="space-y-4">
            <div class="form-group">
              <label class="form-label">{{ t('bots.bot_code') }}</label>
              <input v-model="formData.bot_identifier" type="text" :disabled="editingBot !== null" required class="form-input disabled:bg-gray-100" placeholder="unique-bot-id" />
              <p v-if="editingBot" class="form-hint">{{ t('bots.identifier_cannot_change') }}</p>
            </div>
            <div class="form-group">
              <label class="form-label">{{ t('bots.bot_code') }}</label>
              <input v-model="formData.bot_code" type="text" required class="form-input" placeholder="BOT_CODE" />
            </div>
            <div class="form-group">
              <label class="form-label">{{ t('bots.api_key') }}</label>
              <input v-model="formData.api_key" type="text" :required="!editingBot" class="form-input" placeholder="your-api-key-here" />
              <p class="form-hint">
                <svg class="inline h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                {{ t('bots.api_key_hint') }} {{ editingBot ? t('bots.api_key_hint_edit') : t('bots.api_key_hint_create') }}
              </p>
            </div>
            <div class="flex gap-3 pt-4">
              <button type="submit" :disabled="saving" class="btn btn-success flex-1">
                {{ saving ? t('common.saving') : (editingBot ? t('common.update') : t('common.create')) }}
              </button>
              <button type="button" @click="closeModal" class="btn btn-secondary flex-1">
                {{ t('common.cancel') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import api from '../api';

const { t } = useI18n();

interface Bot {
  id: number;
  bot_identifier: string;
  bot_code: string;
  api_key: string;
}

const router = useRouter();
const bots = ref<Bot[]>([]);
const loading = ref(false);
const error = ref('');
const showModal = ref(false);
const editingBot = ref<Bot | null>(null);
const saving = ref(false);
const deletingId = ref<number | null>(null);

const formData = ref({
  bot_identifier: '',
  bot_code: '',
  api_key: '',
});

async function fetchBots() {
  loading.value = true;
  error.value = '';
  try {
    const response = await api.get('/api/bots');
    bots.value = response.data['member'] || response.data;
  } catch (e: any) {
    error.value = e.response?.data?.message || t('bots.error_loading');
  } finally {
    loading.value = false;
  }
}

function openCreateModal() {
  editingBot.value = null;
  formData.value = { bot_identifier: '', bot_code: '', api_key: '' };
  showModal.value = true;
}

function openEditModal(bot: Bot) {
  editingBot.value = bot;
  formData.value = { bot_identifier: bot.bot_identifier, bot_code: bot.bot_code, api_key: '' };
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  editingBot.value = null;
}

async function saveBot() {
  saving.value = true;
  try {
    if (editingBot.value) {
      const updateData: any = { bot_code: formData.value.bot_code };
      if (formData.value.api_key) updateData.api_key = formData.value.api_key;
      await api.patch(`/api/bots/${editingBot.value.id}`, updateData, {
        headers: { 'Content-Type': 'application/merge-patch+json' }
      });
    } else {
      await api.post('/api/bots', formData.value, {
        headers: { 'Content-Type': 'application/ld+json' }
      });
    }
    closeModal();
    await fetchBots();
  } catch (e: any) {
    error.value = e.response?.data?.message || t('common.error');
  } finally {
    saving.value = false;
  }
}

async function deleteBot(bot: Bot) {
  if (!confirm(t('bots.confirm_delete', { name: bot.bot_identifier }))) return;
  deletingId.value = bot.id;
  try {
    await api.delete(`/api/bots/${bot.id}`);
    await fetchBots();
  } catch (e: any) {
    error.value = e.response?.data?.message || t('common.error');
  } finally {
    deletingId.value = null;
  }
}

function goBack() { router.push('/'); }

onMounted(() => { fetchBots(); });
</script>
