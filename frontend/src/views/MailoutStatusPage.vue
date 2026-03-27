<template>
  <div class="p-4">
    <h1 class="page-title">Mailout Status</h1>

    <!-- Statistics Summary -->
    <div v-if="loading" class="empty-state">Loading...</div>

    <div v-else class="space-y-4">
      <!-- Overall Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg">
          <div class="text-sm text-blue-600 font-medium">Total Mailouts</div>
          <div class="text-2xl font-bold text-blue-800">{{ totalMailouts }}</div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
          <div class="text-sm text-green-600 font-medium">Sent</div>
          <div class="text-2xl font-bold text-green-800">{{ totalSent }}</div>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg">
          <div class="text-sm text-yellow-600 font-medium">Pending</div>
          <div class="text-2xl font-bold text-yellow-800">{{ totalPending }}</div>
        </div>
        <div class="bg-red-50 p-4 rounded-lg">
          <div class="text-sm text-red-600 font-medium">Failed</div>
          <div class="text-2xl font-bold text-red-800">{{ totalFailed }}</div>
        </div>
      </div>

      <!-- Statistics Table -->
      <div class="table-wrapper">
        <table class="data-table rounded-lg">
          <thead>
            <tr>
              <th class="table-th">Post ID</th>
              <th class="table-th">Total</th>
              <th class="table-th">Sent</th>
              <th class="table-th">Pending</th>
              <th class="table-th">Failed</th>
              <th class="table-th">Progress</th>
              <th class="table-th">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="stat in statistics" :key="stat.post_id" class="table-row-hover">
              <td class="table-td">{{ stat.post_id }}</td>
              <td class="table-td">{{ stat.total }}</td>
              <td class="table-td">
                <span class="badge badge-green">{{ stat.sent }}</span>
              </td>
              <td class="table-td">
                <span class="badge badge-yellow">{{ stat.pending }}</span>
              </td>
              <td class="table-td">
                <span class="badge badge-red">{{ stat.failed }}</span>
              </td>
              <td class="table-td">
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-blue-600 h-2 rounded-full"
                    :style="{ width: `${getProgressPercentage(stat)}%` }"
                  ></div>
                </div>
                <span class="text-xs text-gray-600 mt-1">{{ getProgressPercentage(stat) }}%</span>
              </td>
              <td class="table-td">
                <button
                  @click="cleanMailouts(stat.post_id)"
                  class="btn btn-danger btn-sm"
                >
                  Clean
                </button>
              </td>
            </tr>
            <tr v-if="statistics.length === 0">
              <td colspan="7" class="px-4 py-8 text-center text-gray-500">No mailout records found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import api from '../api';

interface MailoutStatistic {
  post_id: number;
  total: number;
  sent: number;
  pending: number;
  failed: number;
}

const statistics = ref<MailoutStatistic[]>([]);
const loading = ref(true);

const totalMailouts = computed(() => {
  return statistics.value.reduce((sum, stat) => sum + stat.total, 0);
});

const totalSent = computed(() => {
  return statistics.value.reduce((sum, stat) => sum + stat.sent, 0);
});

const totalPending = computed(() => {
  return statistics.value.reduce((sum, stat) => sum + stat.pending, 0);
});

const totalFailed = computed(() => {
  return statistics.value.reduce((sum, stat) => sum + stat.failed, 0);
});

const getProgressPercentage = (stat: MailoutStatistic): number => {
  if (stat.total === 0) return 0;
  return Math.round((stat.sent / stat.total) * 100);
};

const fetchStatistics = async () => {
  loading.value = true;
  try {
    const { data } = await api.get('/api/mailout/statistics');
    // If data is an object with product_id, it's a single product stat
    // Otherwise it's an array/object of product stats
    if (Array.isArray(data)) {
      statistics.value = data;
    } else if (data.post_id !== undefined) {
      statistics.value = [data];
    } else {
      // Convert object with post_id keys to array
      statistics.value = Object.values(data);
    }
  } catch (error) {
    console.error('Error fetching mailout statistics:', error);
    statistics.value = [];
  } finally {
    loading.value = false;
  }
};

const cleanMailouts = async (postId: number) => {
  if (!confirm(`Clean all mailout records for post ${postId}?`)) {
    return;
  }

  try {
    const { data } = await api.delete(`/api/mailout/clean/${postId}`);
    alert(`Cleaned: ${data.deleted} record(s) removed`);
    fetchStatistics();
  } catch (error: any) {
    const errorMessage = error.response?.data?.error || 'Failed to clean mailouts';
    alert(`Error: ${errorMessage}`);
  }
};

onMounted(fetchStatistics);
</script>
