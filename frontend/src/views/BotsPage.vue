<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 px-4 py-6 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="max-w-7xl mx-auto">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">Bots</h1>
          <p class="text-gray-600">Manage your bots and API keys</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-3">
          <button
            @click="openCreateModal"
            class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg shadow-md hover:bg-green-700 hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Bot
          </button>
          <button
            @click="fetchBots"
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
          <button
            @click="goBack"
            class="px-6 py-3 bg-gray-600 text-white font-medium rounded-lg shadow-md hover:bg-gray-700 hover:shadow-lg transition-all duration-200"
          >
            Back to Menu
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading && !bots.length" class="flex items-center justify-center py-20">
        <div class="text-center">
          <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="text-gray-600 text-lg">Loading bots...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-if="error" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
        <div class="flex items-center">
          <svg class="h-6 w-6 text-red-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <h3 class="text-red-800 font-medium">Error loading bots</h3>
            <p class="text-red-700 text-sm mt-1">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Bots Grid -->
      <div v-if="bots.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
        <div
          v-for="bot in bots"
          :key="bot.id"
          class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-green-300 transform hover:-translate-y-1"
        >
          <!-- Card Header -->
          <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-lg">
                  <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-white font-semibold text-lg truncate max-w-[150px]">
                    {{ bot.bot_identifier }}
                  </h3>
                  <p class="text-green-100 text-xs">ID: {{ bot.id }}</p>
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
                  {{ bot.bot_code }}
                </p>
              </div>
            </div>

            <div class="flex items-start">
              <svg class="h-5 w-5 text-gray-400 mr-3 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
              </svg>
              <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 mb-1">API Key</p>
                <p class="text-sm font-mono text-gray-900 truncate">
                  {{ bot.api_key.substring(0, 20) }}...
                </p>
              </div>
            </div>
          </div>

          <!-- Card Actions -->
          <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex gap-2">
            <button
              @click="openEditModal(bot)"
              class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center gap-2"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Edit
            </button>
            <button
              @click="deleteBot(bot)"
              :disabled="deletingId === bot.id"
              class="flex-1 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <svg v-if="deletingId !== bot.id" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              <svg v-else class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ deletingId === bot.id ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && !bots.length && !error" class="text-center py-20">
        <svg class="h-24 w-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
        </svg>
        <p class="text-gray-500 text-lg mb-2">No bots found</p>
        <p class="text-gray-400 text-sm">Create your first bot to get started</p>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <h2 class="text-2xl font-bold mb-4">{{ editingBot ? 'Edit Bot' : 'Create New Bot' }}</h2>
        <form @submit.prevent="saveBot" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bot Identifier</label>
            <input
              v-model="formData.bot_identifier"
              type="text"
              :disabled="editingBot !== null"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent disabled:bg-gray-100"
              placeholder="unique-bot-id"
            />
            <p v-if="editingBot" class="text-xs text-gray-500 mt-1">Bot identifier cannot be changed</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bot Code</label>
            <input
              v-model="formData.bot_code"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
              placeholder="BOT_CODE"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
            <input
              v-model="formData.api_key"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
              placeholder="your-api-key-here"
            />
          </div>
          <div class="flex gap-3 pt-4">
            <button
              type="submit"
              :disabled="saving"
              class="flex-1 px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
            >
              {{ saving ? 'Saving...' : (editingBot ? 'Update' : 'Create') }}
            </button>
            <button
              type="button"
              @click="closeModal"
              class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-400 transition-colors"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';

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
    error.value = e.response?.data?.message || 'Failed to load bots';
  } finally {
    loading.value = false;
  }
}

function openCreateModal() {
  editingBot.value = null;
  formData.value = {
    bot_identifier: '',
    bot_code: '',
    api_key: '',
  };
  showModal.value = true;
}

function openEditModal(bot: Bot) {
  editingBot.value = bot;
  formData.value = {
    bot_identifier: bot.bot_identifier,
    bot_code: bot.bot_code,
    api_key: bot.api_key,
  };
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
      await api.patch(`/api/bots/${editingBot.value.id}`, {
        bot_code: formData.value.bot_code,
        api_key: formData.value.api_key,
      }, {
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
    error.value = e.response?.data?.message || 'Failed to save bot';
  } finally {
    saving.value = false;
  }
}

async function deleteBot(bot: Bot) {
  if (!confirm(`Are you sure you want to delete bot "${bot.bot_identifier}"?`)) {
    return;
  }
  deletingId.value = bot.id;
  try {
    await api.delete(`/api/bots/${bot.id}`);
    await fetchBots();
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Failed to delete bot';
  } finally {
    deletingId.value = null;
  }
}

function goBack() {
  router.push('/');
}

onMounted(() => {
  fetchBots();
});
</script>

<style scoped>
</style>

