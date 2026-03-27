<template>
  <div class="p-4">
    <h1 class="page-title">Sended Posts</h1>

    <div v-if="loading" class="empty-state">Loading...</div>

    <div v-else class="table-wrapper">
      <table class="data-table rounded-lg">
        <thead>
          <tr>
            <th class="table-th">Post ID</th>
            <th class="table-th">Messages Stored</th>
            <th class="table-th">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in stats" :key="row.post_id" class="table-row-hover">
            <td class="table-td">{{ row.post_id }}</td>
            <td class="table-td">
              <span class="badge badge-blue">{{ row.count }}</span>
            </td>
            <td class="table-td">
              <button
                @click="deleteSent(row.post_id)"
                :disabled="deleting[row.post_id]"
                class="btn btn-danger btn-sm"
              >
                {{ deleting[row.post_id] ? 'Deleting...' : 'Delete from users' }}
              </button>
            </td>
          </tr>
          <tr v-if="stats.length === 0">
            <td colspan="3" class="px-4 py-8 text-center text-gray-500">No sent post records found</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue';
import api from '../api';

interface SentPostStat {
  post_id: number;
  count: number;
}

const stats = ref<SentPostStat[]>([]);
const loading = ref(true);
const deleting = reactive<Record<number, boolean>>({});

const fetchStats = async () => {
  loading.value = true;
  try {
    const { data } = await api.get<SentPostStat[]>('/api/mailout/sent-stats');
    stats.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error('Error fetching sent post stats:', error);
    stats.value = [];
  } finally {
    loading.value = false;
  }
};

const deleteSent = async (postId: number) => {
  if (!confirm(`Mark all stored messages for post ${postId} for deletion from Telegram?`)) {
    return;
  }

  deleting[postId] = true;
  try {
    const { data } = await api.post(`/api/mailout/delete-sent/${postId}`);
    alert(`Marked ${data.marked} message(s) for deletion. They will be removed within 2 minutes.`);
    await fetchStats();
  } catch (error: any) {
    const errorMessage = error.response?.data?.error || 'Failed to mark messages for deletion';
    alert(`Error: ${errorMessage}`);
  } finally {
    deleting[postId] = false;
  }
};

onMounted(fetchStats);
</script>
