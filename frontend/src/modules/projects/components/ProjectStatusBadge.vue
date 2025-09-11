<template>
  <VBadge :color="badgeColor" :variant="badgeVariant">
    {{ statusLabel }}
  </VBadge>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { VBadge } from '@/components/ui'
import type { ProjectStatus } from '../types/projects.types'

interface Props {
  status: ProjectStatus
  color?: string
}

const props = withDefaults(defineProps<Props>(), {
  color: undefined
})

const statusLabels: Record<ProjectStatus, string> = {
  draft: 'Draft',
  active: 'Active',
  on_hold: 'On Hold',
  completed: 'Completed',
  cancelled: 'Cancelled'
}

const statusColors: Record<ProjectStatus, string> = {
  draft: 'gray',
  active: 'blue',
  on_hold: 'yellow',
  completed: 'green',
  cancelled: 'red'
}

const statusLabel = computed(() => statusLabels[props.status])
const badgeColor = computed(() => props.color || statusColors[props.status])
const badgeVariant = computed(() => {
  return props.status === 'completed' ? 'solid' : 'outline'
})
</script>