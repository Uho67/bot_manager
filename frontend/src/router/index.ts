import { createRouter, createWebHistory } from 'vue-router';
import MenuPage from '../views/MenuPage.vue';
import AdminUsersPage from '../views/AdminUsersPage.vue';
import BotsPage from '../views/BotsPage.vue';
import LoginView from '../views/LoginView.vue';
import MyAccountPage from '../views/MyAccountPage.vue';
import MyBotsPage from '../views/MyBotsPage.vue';
import ConfigsPage from '../views/ConfigsPage.vue';
import ProductListPage from '../views/ProductListPage.vue';
import ProductEditPage from '../views/ProductEditPage.vue';
import CategoryListPage from '../views/CategoryListPage.vue';
import CategoryEditPage from '../views/CategoryEditPage.vue';
import ButtonListPage from '../views/ButtonListPage.vue';
import ButtonEditPage from '../views/ButtonEditPage.vue';
import TemplateListPage from '../views/TemplateListPage.vue';
import TemplateEditPage from '../views/TemplateEditPage.vue';
import PostListPage from '../views/PostListPage.vue';
import PostEditPage from '../views/PostEditPage.vue';
import UserListPage from '../views/UserListPage.vue';
import MailoutStatusPage from '../views/MailoutStatusPage.vue';
import { useAuth } from '../composables/useAuth';

const routes = [
  { path: '/login', component: LoginView },
  { path: '/', component: MenuPage, meta: { requiresAuth: true } },
  { path: '/admin-users', component: AdminUsersPage, meta: { requiresAuth: true, requiresSuperAdmin: true } },
  { path: '/bots', component: BotsPage, meta: { requiresAuth: true, requiresSuperAdmin: true } },
  { path: '/my-account', component: MyAccountPage, meta: { requiresAuth: true } },
  { path: '/my-bots', component: MyBotsPage, meta: { requiresAuth: true } },
  { path: '/configs', component: ConfigsPage, meta: { requiresAuth: true } },
  { path: '/products', name: 'ProductList', component: ProductListPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/products/create', name: 'ProductCreate', component: ProductEditPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/products/:id/edit', name: 'ProductEdit', component: ProductEditPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/categories', name: 'CategoryList', component: CategoryListPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/categories/create', name: 'CategoryCreate', component: CategoryEditPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/categories/:id/edit', name: 'CategoryEdit', component: CategoryEditPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/buttons', name: 'ButtonList', component: ButtonListPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/buttons/create', name: 'ButtonCreate', component: ButtonEditPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/buttons/:id/edit', name: 'ButtonEdit', component: ButtonEditPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/templates', name: 'TemplateList', component: TemplateListPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/templates/create', name: 'TemplateCreate', component: TemplateEditPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/templates/:id/edit', name: 'TemplateEdit', component: TemplateEditPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/posts', name: 'PostList', component: PostListPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/posts/create', name: 'PostCreate', component: PostEditPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/posts/:id/edit', name: 'PostEdit', component: PostEditPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/users', name: 'UserList', component: UserListPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/mailout', name: 'MailoutStatus', component: MailoutStatusPage, meta: { requiresAuth: true, requiresAdmin: true } },
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
  } else if (to.meta.requiresAdmin && (!user.value || user.value.roles?.indexOf('ROLE_ADMIN') === -1 || user.value.roles?.indexOf('ROLE_SUPER_ADMIN') !== -1)) {
    // Only allow admins, not super admins
    next('/');
  } else {
    next();
  }
});

export default router;
