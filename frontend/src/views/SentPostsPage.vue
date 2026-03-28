<template>
  <div class="p-4">
    <h1 class="page-title">{{ t('sent_posts.title') }}</h1>

    <div v-if="loading" class="empty-state">{{ t('common.loading') }}</div>

    <div v-else class="table-wrapper">
      <table class="data-table rounded-lg">
        <thead>
          <tr>
            <th class="table-th">{{ t('table.post_id') }}</th>
            <th class="table-th">{{ t('table.messages_stored') }}</th>
            <th class="table-th">{{ t('table.actions') }}</th>
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
                {{ deleting[row.post_id] ? t('common.deleting') : t('sent_posts.delete_from_users') }}
              </button>
            </td>
          </tr>
          <tr v-if="stats.length === 0">
            <td colspan="3" class="px-4 py-8 text-center text-gray-500">{{ t('sent_posts.no_records') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../api';

const { t } = useI18n();

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
  if (!confirm(t('sent_posts.confirm_delete', { id: postId }))) return;
  deleting[postId] = true;
  try {
    const { data } = await api.post(`/api/mailout/delete-sent/${postId}`);
    alert(t('sent_posts.marked', { count: data.marked }));
    await fetchStats();
  } catch (error: any) {
    const errorMessage = error.response?.data?.error || t('common.error');
    alert(`${t('common.error')}: ${errorMessage}`);
  } finally {
    deleting[postId] = false;
  }
};

onMounted(fetchStats);
</script>
