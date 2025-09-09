<template>
  <MainLayout>
    <div class="text-center py-12">
      <div class="max-w-md mx-auto">
        <div class="w-24 h-24 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <span class="text-orange-600 text-4xl">📋</span>
        </div>
        <h2 class="text-2xl font-semibold text-gray-900 mb-2">Projects</h2>
        <p class="text-gray-600 mb-6">
          Manage your construction projects, timelines, and deliverables.
        </p>
        <div class="text-sm text-gray-500">
          Coming soon...
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup lang="ts">
import MainLayout from '@/layouts/MainLayout.vue'

defineOptions({ name: 'ProjectsPage' })
</script>