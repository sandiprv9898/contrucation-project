<template>
      <div class="p-6">
        <div class="max-w-7xl mx-auto">
          <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Project Reports</h1>
            <p class="text-gray-600 mt-2">Comprehensive project analytics and insights</p>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <BarChart3 class="w-16 h-16 text-orange-400 mx-auto mb-4" />
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Project Reports</h2>
            <p class="text-gray-600 mb-4">Coming soon! This feature will provide detailed project analytics and reporting.</p>
            <div class="text-sm text-gray-500">
              Track project progress, budget utilization, and milestone completion
            </div>
          </div>
        </div>
      </div>
</template>

<script setup lang="ts">
import { BarChart3 } from 'lucide-vue-next'
</script>