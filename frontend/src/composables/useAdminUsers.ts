import { ref, Ref } from 'vue';
import api from '../api';

export function useAdminUsers() {
  const admins = ref([]);
  const loading = ref(false);
  const error: Ref<Error | null> = ref(null);

  const fetchAdmins = async (): Promise<void> => {
    loading.value = true;
    error.value = null;
    try {
      const response = await api.get('/api/admin-users');
      admins.value = response.data;
    } catch (e) {
      error.value = e as Error;
    } finally {
      loading.value = false;
    }
  };

  return { admins, loading, error, fetchAdmins };
}
