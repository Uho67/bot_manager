<template>
  <div class="menu-page">
    <div class="menu-header">
      <h1 class="menu-title">{{ t('menu.title') }}</h1>
      <p class="menu-subtitle">{{ t('menu.welcome', { name: user?.admin_name }) }}</p>
      <button @click="handleLogout" class="menu-logout-btn">
        {{ t('nav.logout') }}
      </button>
    </div>
    <ul class="menu-list">
      <li v-if="isSuperAdmin">
        <router-link to="/admin-users" class="menu-link-blue">{{ t('nav.admin_users') }}</router-link>
      </li>
      <li v-if="isSuperAdmin">
        <router-link to="/bots" class="menu-link-green">{{ t('nav.bots') }}</router-link>
      </li>
      <li v-if="user && (user.roles?.includes('ROLE_ADMIN') || user.roles?.includes('ROLE_SUPER_ADMIN'))">
        <router-link to="/configs" class="menu-link-indigo">{{ t('nav.configs') }}</router-link>
      </li>
      <li v-if="user && (user.roles?.includes('ROLE_ADMIN') || user.roles?.includes('ROLE_SUPER_ADMIN'))">
        <router-link to="/my-bots" class="menu-link-purple">{{ t('nav.my_bots') }}</router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link to="/products" class="menu-link-pink">{{ t('nav.products') }}</router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link to="/categories" class="menu-link-orange">{{ t('nav.categories') }}</router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link to="/templates" class="menu-link-cyan">{{ t('nav.templates') }}</router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link to="/posts" class="menu-link-teal">{{ t('nav.posts') }}</router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link to="/users" class="menu-link-yellow">{{ t('nav.users') }}</router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link to="/mailout" class="menu-link-red">{{ t('nav.mailout_status') }}</router-link>
      </li>
      <li v-if="!isSuperAdmin && user?.roles?.indexOf('ROLE_ADMIN') !== -1">
        <router-link to="/sent-posts" class="menu-link-red">{{ t('menu.sended_posts') }}</router-link>
      </li>
      <li v-if="!isSuperAdmin && (!user?.roles || user?.roles.indexOf('ROLE_ADMIN') === -1)">
        <div class="empty-state">
          <p>{{ t('menu.no_items') }}</p>
        </div>
      </li>
    </ul>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuth } from '../composables/useAuth';
import { useRouter } from 'vue-router';

const { t } = useI18n();
const { user, logout } = useAuth();
const router = useRouter();

const isSuperAdmin = computed(() => {
  if (!user.value?.roles) return false;
  return user.value.roles.indexOf('ROLE_SUPER_ADMIN') !== -1;
});

function handleLogout() {
  logout();
  router.push('/login');
}
</script>
