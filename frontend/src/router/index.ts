import { createRouter, createWebHistory } from 'vue-router';
import MenuPage from '../views/MenuPage.vue';
import AdminUsersPage from '../views/AdminUsersPage.vue';

const routes = [
  { path: '/', component: MenuPage },
  { path: '/admin-users', component: AdminUsersPage },
];

export default createRouter({
  history: createWebHistory(),
  routes,
});

