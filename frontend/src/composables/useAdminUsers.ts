import { ref, Ref } from 'vue';
import api from '../api';
import type { AdminUser } from '../types/AdminUser';

interface UseAdminUsersReturn {
  admins: Ref<AdminUser[]>;
  totalItems: Ref<number>;
  loading: Ref<boolean>;
  error: Ref<Error | null>;
  fetchAdmins: () => Promise<void>;
  createAdmin: (data: Omit<AdminUser, 'id'>) => Promise<AdminUser>;
  updateAdmin: (id: number, data: Partial<AdminUser>) => Promise<AdminUser>;
  deleteAdmin: (id: number) => Promise<void>;
}

export function useAdminUsers(): UseAdminUsersReturn {
  const admins = ref<AdminUser[]>([]);
  const totalItems = ref<number>(0);
  const loading = ref<boolean>(false);
  const error = ref<Error | null>(null);

  const fetchAdmins = async (): Promise<void> => {
    loading.value = true;
    error.value = null;
    try {
      const response = await api.get('/api/admin_users');
      admins.value = response.data['member'] || response.data;
      totalItems.value = response.data['hydra:totalItems'] ?? response.data['totalItems'] ?? admins.value.length;
    } catch (e) {
      error.value = e as Error;
    } finally {
      loading.value = false;
    }
  };

  const createAdmin = async (data: Omit<AdminUser, 'id'>): Promise<AdminUser> => {
    const response = await api.post('/api/admin_users', data);
    await fetchAdmins();
    return response.data;
  };

  const updateAdmin = async (id: number, data: Partial<AdminUser>): Promise<AdminUser> => {
   const response = await api.patch(`/api/admin_users/${id}`, data, { headers: { 'Content-Type': 'application/merge-patch+json' } });
    await fetchAdmins();
    return response.data;
  };

  const deleteAdmin = async (id: number): Promise<void> => {
    await api.delete(`/api/admin_users/${id}`);
    await fetchAdmins();
  };

  return { admins, totalItems, loading, error, fetchAdmins, createAdmin, updateAdmin, deleteAdmin };
}
