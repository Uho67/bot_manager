import { createRouter, createWebHistory } from 'vue-router';
import MenuPage from '../views/MenuPage.vue';
import AdminUsersPage from '../views/AdminUsersPage.vue';
import BotsPage from '../views/BotsPage.vue';
import LoginView from '../views/LoginView.vue';
import { useAuth } from '../composables/useAuth';

const routes = [
  { path: '/login', component: LoginView },
  { path: '/', component: MenuPage, meta: { requiresAuth: true } },
  { path: '/admin-users', component: AdminUsersPage, meta: { requiresAuth: true, requiresSuperAdmin: true } },
  { path: '/bots', component: BotsPage, meta: { requiresAuth: true, requiresSuperAdmin: true } },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guard: redirect to login if not authenticated
router.beforeEach((to, from, next) => {
  const { user, loadUserFromCookies } = useAuth();
  loadUserFromCookies();

  if (to.meta.requiresAuth && !user.value) {
    next('/login');
  } else if (to.meta.requiresSuperAdmin && user.value?.roles?.indexOf('ROLE_SUPER_ADMIN') === -1) {
    // Redirect to menu if user doesn't have ROLE_SUPER_ADMIN
    next('/');
  } else {
    next();
  }
});

export default router;
