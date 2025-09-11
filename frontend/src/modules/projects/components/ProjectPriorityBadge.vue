<template>
  <VBadge :color="badgeColor" :variant="badgeVariant">
    {{ priorityLabel }}
  </VBadge>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { VBadge } from '@/components/ui'
import type { ProjectPriority } from '../types/projects.types'

interface Props {
  priority: ProjectPriority
  color?: string
}

const props = withDefaults(defineProps<Props>(), {
  color: undefined
})

const priorityLabels: Record<ProjectPriority, string> = {
  low: 'Low',
  medium: 'Medium',
  high: 'High',
  urgent: 'Urgent'
}

const priorityColors: Record<ProjectPriority, string> = {
  low: 'gray',
  medium: 'blue',
  high: 'yellow',
  urgent: 'red'
}

const priorityLabel = computed(() => priorityLabels[props.priority])
const badgeColor = computed(() => props.color || priorityColors[props.priority])
const badgeVariant = computed(() => {
  return props.priority === 'urgent' ? 'solid' : 'outline'
})
</script>