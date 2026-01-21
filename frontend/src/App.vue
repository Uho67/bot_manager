<template>
  <AppLayout>
    <router-view />
  </AppLayout>
</template>

<script lang="ts" setup>
import AppLayout from './components/AppLayout.vue';
// ...existing code...
</script>

<style>
body {
  margin: 0;
  font-family: 'Inter', Arial, sans-serif;
  background: #f8f9fa;
}
</style>
