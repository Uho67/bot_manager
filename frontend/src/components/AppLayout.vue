<template>
  <div class="relative min-h-screen bg-gray-50">
    <div class="absolute top-4 right-4 z-50">
      <NavDropdown />
    </div>
    <slot />
  </div>
</template>

<script setup lang="ts">
import NavDropdown from './NavDropdown.vue';
</script>

<style scoped>
</style>

