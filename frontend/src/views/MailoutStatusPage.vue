<template>
  <div class="p-4">
    <h1 class="text-xl font-bold mb-4">Mailout Status</h1>
    
    <!-- Statistics Summary -->
    <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>
    
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

      <!-- Product Statistics Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
          <thead>
            <tr>
              <th class="px-4 py-2 border-b text-center">Product ID</th>
              <th class="px-4 py-2 border-b text-center">Total</th>
              <th class="px-4 py-2 border-b text-center">Sent</th>
              <th class="px-4 py-2 border-b text-center">Pending</th>
              <th class="px-4 py-2 border-b text-center">Failed</th>
              <th class="px-4 py-2 border-b text-center">Progress</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="stat in statistics" :key="stat.product_id">
              <td class="px-4 py-2 border-b text-center">{{ stat.product_id }}</td>
              <td class="px-4 py-2 border-b text-center">{{ stat.total }}</td>
              <td class="px-4 py-2 border-b text-center">
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">{{ stat.sent }}</span>
              </td>
              <td class="px-4 py-2 border-b text-center">
                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-sm">{{ stat.pending }}</span>
              </td>
              <td class="px-4 py-2 border-b text-center">
                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-sm">{{ stat.failed }}</span>
              </td>
              <td class="px-4 py-2 border-b text-center">
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-blue-600 h-2 rounded-full" 
                    :style="{ width: `${getProgressPercentage(stat)}%` }"
                  ></div>
                </div>
                <span class="text-xs text-gray-600 mt-1">{{ getProgressPercentage(stat) }}%</span>
              </td>
            </tr>
            <tr v-if="statistics.length === 0">
              <td colspan="6" class="px-4 py-8 text-center text-gray-500">No mailout records found</td>
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
  product_id: number;
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
    } else if (data.product_id !== undefined) {
      statistics.value = [data];
    } else {
      // Convert object with product_id keys to array
      statistics.value = Object.values(data);
    }
  } catch (error) {
    console.error('Error fetching mailout statistics:', error);
    statistics.value = [];
  } finally {
    loading.value = false;
  }
};

onMounted(fetchStatistics);
</script>
