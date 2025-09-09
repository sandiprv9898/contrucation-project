<template>
      <div class="p-6">
        <div class="max-w-7xl mx-auto">
          <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Time Tracking Reports</h1>
            <p class="text-gray-600 mt-2">Monitor time allocation and productivity metrics</p>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <Clock class="w-16 h-16 text-orange-400 mx-auto mb-4" />
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Time Tracking Reports</h2>
            <p class="text-gray-600 mb-4">Coming soon! This feature will provide comprehensive time tracking analytics.</p>
            <div class="text-sm text-gray-500">
              Analyze team productivity, project time allocation, and resource efficiency
            </div>
          </div>
        </div>
      </div>
</template>

<script setup lang="ts">
import { Clock } from 'lucide-vue-next'
</script>