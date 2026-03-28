<template>
  <div class="p-4">
    <h1 class="page-title">{{ t('mailout.title') }}</h1>

    <div v-if="loading" class="empty-state">{{ t('mailout.loading') }}</div>

    <div v-else class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg">
          <div class="text-sm text-blue-600 font-medium">{{ t('mailout.total') }}</div>
          <div class="text-2xl font-bold text-blue-800">{{ totalMailouts }}</div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
          <div class="text-sm text-green-600 font-medium">{{ t('mailout.sent') }}</div>
          <div class="text-2xl font-bold text-green-800">{{ totalSent }}</div>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg">
          <div class="text-sm text-yellow-600 font-medium">{{ t('mailout.pending') }}</div>
          <div class="text-2xl font-bold text-yellow-800">{{ totalPending }}</div>
        </div>
        <div class="bg-red-50 p-4 rounded-lg">
          <div class="text-sm text-red-600 font-medium">{{ t('mailout.failed') }}</div>
          <div class="text-2xl font-bold text-red-800">{{ totalFailed }}</div>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table rounded-lg">
          <thead>
            <tr>
              <th class="table-th">{{ t('table.post_id') }}</th>
              <th class="table-th">{{ t('table.total') }}</th>
              <th class="table-th">{{ t('table.sent') }}</th>
              <th class="table-th">{{ t('table.pending') }}</th>
              <th class="table-th">{{ t('table.failed') }}</th>
              <th class="table-th">{{ t('table.progress') }}</th>
              <th class="table-th">{{ t('table.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="stat in statistics" :key="stat.post_id" class="table-row-hover">
              <td class="table-td">{{ stat.post_id }}</td>
              <td class="table-td">{{ stat.total }}</td>
              <td class="table-td"><span class="badge badge-green">{{ stat.sent }}</span></td>
              <td class="table-td"><span class="badge badge-yellow">{{ stat.pending }}</span></td>
              <td class="table-td"><span class="badge badge-red">{{ stat.failed }}</span></td>
              <td class="table-td">
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div class="bg-blue-600 h-2 rounded-full" :style="{ width: `${getProgressPercentage(stat)}%` }"></div>
                </div>
                <span class="text-xs text-gray-600 mt-1">{{ getProgressPercentage(stat) }}%</span>
              </td>
              <td class="table-td">
                <button @click="cleanMailouts(stat.post_id)" class="btn btn-danger btn-sm">
                  {{ t('mailout.clean') }}
                </button>
              </td>
            </tr>
            <tr v-if="statistics.length === 0">
              <td colspan="7" class="px-4 py-8 text-center text-gray-500">{{ t('mailout.no_records') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../api';

const { t } = useI18n();

interface MailoutStatistic {
  post_id: number;
  total: number;
  sent: number;
  pending: number;
  failed: number;
}

const statistics = ref<MailoutStatistic[]>([]);
const loading = ref(true);

const totalMailouts = computed(() => statistics.value.reduce((sum, stat) => sum + stat.total, 0));
const totalSent = computed(() => statistics.value.reduce((sum, stat) => sum + stat.sent, 0));
const totalPending = computed(() => statistics.value.reduce((sum, stat) => sum + stat.pending, 0));
const totalFailed = computed(() => statistics.value.reduce((sum, stat) => sum + stat.failed, 0));

const getProgressPercentage = (stat: MailoutStatistic): number => {
  if (stat.total === 0) return 0;
  return Math.round((stat.sent / stat.total) * 100);
};

const fetchStatistics = async () => {
  loading.value = true;
  try {
    const { data } = await api.get('/api/mailout/statistics');
    if (Array.isArray(data)) {
      statistics.value = data;
    } else if (data.post_id !== undefined) {
      statistics.value = [data];
    } else {
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
  if (!confirm(t('mailout.confirm_clean', { id: postId }))) return;
  try {
    const { data } = await api.delete(`/api/mailout/clean/${postId}`);
    alert(t('mailout.cleaned', { count: data.deleted }));
    fetchStatistics();
  } catch (error: any) {
    const errorMessage = error.response?.data?.error || t('common.error');
    alert(`${t('common.error')}: ${errorMessage}`);
  }
};

onMounted(fetchStatistics);
</script>
