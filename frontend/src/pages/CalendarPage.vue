<template>
  <MainLayout>
    <div class="text-center py-12">
      <div class="max-w-md mx-auto">
        <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <Calendar class="text-blue-600 w-12 h-12" />
        </div>
        <h2 class="text-2xl font-semibold text-gray-900 mb-2">Calendar</h2>
        <p class="text-gray-600 mb-6">
          View project timelines, deadlines, and team schedules.
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
import { Calendar } from 'lucide-vue-next'

defineOptions({ name: 'CalendarPage' })
</script>