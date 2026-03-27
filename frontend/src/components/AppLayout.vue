<template>
  <div class="app-layout">
    <div class="nav-dropdown-container">
      <NavDropdown />
    </div>
    <slot />
  </div>
</template>

<script setup lang="ts">
import NavDropdown from './NavDropdown.vue';
</script>


