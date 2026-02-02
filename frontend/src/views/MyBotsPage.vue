<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 px-4 py-6 sm:px-6 lg:px-8 pt-20">
    <div class="max-w-7xl mx-auto">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">My Bots</h1>
          <p class="text-gray-600">These are the bots you have access to.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-3">
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
      <div v-if="bots.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
        <div
          v-for="bot in bots"
          :key="bot.id"
          class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-green-300 transform hover:-translate-y-1"
        >
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
            <div class="flex justify-end pt-2">
              <button @click="openEditModal(bot)" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">Edit</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Modal -->
      <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
          <h2 class="text-2xl font-bold mb-4">Edit Bot</h2>
          <form @submit.prevent="saveBot" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bot Identifier</label>
              <input
                v-model="formData.bot_identifier"
                type="text"
                disabled
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gray-100"
              />
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
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                placeholder="Leave empty to keep current key"
              />
              <p class="text-xs text-gray-500 mt-1">
                API keys are securely hashed. Leave empty to keep current key.
              </p>
            </div>
            <div class="flex gap-3 pt-4">
              <button
                type="submit"
                :disabled="saving"
                class="flex-1 px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
              >
                {{ saving ? 'Saving...' : 'Update' }}
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

      <div v-if="!loading && !bots.length && !error" class="text-center py-20">
        <svg class="h-24 w-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
        </svg>
        <p class="text-gray-500 text-lg mb-2">No bots found</p>
        <p class="text-gray-400 text-sm">You do not have any bots assigned.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';
import { useAuth } from '../composables/useAuth';

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
const saving = ref(false);
const formData = ref<Partial<Bot>>({});
const { user } = useAuth();

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

function goBack() {
  router.push('/');
}

function openEditModal(bot: Bot) {
  formData.value = {
    ...bot,
    api_key: '', // Don't pre-fill API key (it's hashed in DB)
  };
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  formData.value = {};
}

async function saveBot() {
  saving.value = true;
  error.value = '';
  try {
    const payload: any = {
      bot_code: formData.value.bot_code,
    };
    // Only include api_key if it's provided
    if (formData.value.api_key) {
      payload.api_key = formData.value.api_key;
    }
    await api.patch('/api/my-bot', payload, {
      headers: { 'Content-Type': 'application/merge-patch+json' }
    });
    closeModal();
    fetchBots();
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Failed to update bot';
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  fetchBots();
});
</script>

<style scoped>
</style>
