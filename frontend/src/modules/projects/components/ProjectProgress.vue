<template>
  <div class="flex items-center space-x-2">
    <div class="flex-1 bg-gray-200 rounded-full h-2">
      <div 
        class="h-2 rounded-full transition-all duration-300"
        :class="progressColor"
        :style="{ width: `${percentage}%` }"
      ></div>
    </div>
    <span class="text-sm font-medium text-gray-600 min-w-[3rem]">
      {{ percentage }}%
    </span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  percentage: number
}

const props = defineProps<Props>()

const progressColor = computed(() => {
  if (props.percentage >= 100) return 'bg-green-600'
  if (props.percentage >= 75) return 'bg-blue-600'
  if (props.percentage >= 50) return 'bg-yellow-600'
  if (props.percentage >= 25) return 'bg-orange-600'
  return 'bg-red-600'
})
</script>