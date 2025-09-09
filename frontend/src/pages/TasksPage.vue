<template>
    <div class="text-center py-12">
      <div class="max-w-md mx-auto">
        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <CheckSquare class="text-green-600 w-12 h-12" />
        </div>
        <h2 class="text-2xl font-semibold text-gray-900 mb-2">Tasks</h2>
        <p class="text-gray-600 mb-6">
          Track and manage individual tasks across all your projects.
        </p>
        <div class="text-sm text-gray-500">
          Coming soon...
        </div>
      </div>
    </div>
</template>

<script setup lang="ts">
import { CheckSquare } from 'lucide-vue-next'

defineOptions({ name: 'TasksPage' })
</script>