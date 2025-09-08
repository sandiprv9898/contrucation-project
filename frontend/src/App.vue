<script setup lang="ts">
import { RouterView } from 'vue-router'
import { onMounted } from 'vue'
import { useAuthStore } from './modules/auth'

const authStore = useAuthStore()

onMounted(() => {
  // Initialize auth state on app mount
  authStore.initAuth()
})
</script>

<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <RouterView />
  </div>
</template>

<style>
/* Global styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  line-height: 1.6;
  color: #1f2937;
}

#app {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Construction industry color scheme */
:root {
  --primary-orange: #f97316;
  --primary-orange-light: #fb923c;
  --primary-orange-dark: #ea580c;
  --secondary-blue: #1e40af;
  --secondary-blue-light: #3b82f6;
  --secondary-blue-dark: #1e3a8a;
  --accent-yellow: #fbbf24;
  --neutral-gray: #6b7280;
  --neutral-light: #f9fafb;
  --neutral-dark: #111827;
}
</style>
