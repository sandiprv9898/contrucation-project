<template>
  <div class="text-center py-12">
    <div class="max-w-md mx-auto">
      <div class="w-24 h-24 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <Settings class="text-purple-600 w-12 h-12" />
      </div>
      <h2 class="text-2xl font-semibold text-gray-900 mb-2">Project Settings</h2>
      <p class="text-gray-600 mb-6">
        Configure project settings, templates, and workflows.
      </p>
      <div class="text-sm text-gray-500">
        Coming soon...
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Settings } from 'lucide-vue-next'

defineOptions({ name: 'ProjectSettingsPage' })
</script>