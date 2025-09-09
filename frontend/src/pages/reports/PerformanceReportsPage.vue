<template>
      <div class="p-6">
        <div class="max-w-7xl mx-auto">
          <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Performance Reports</h1>
            <p class="text-gray-600 mt-2">Analyze system and team performance metrics</p>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <TrendingUp class="w-16 h-16 text-orange-400 mx-auto mb-4" />
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Performance Reports</h2>
            <p class="text-gray-600 mb-4">Coming soon! This feature will provide detailed performance analytics and insights.</p>
            <div class="text-sm text-gray-500">
              Track project completion rates, team productivity, and resource utilization
            </div>
          </div>
        </div>
      </div>
</template>

<script setup lang="ts">
import { TrendingUp } from 'lucide-vue-next'
</script>