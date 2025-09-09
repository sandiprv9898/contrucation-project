<template>
  <router-link
    :to="to"
    @click="$emit('navigate')"
    class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150"
    :class="linkClasses"
    active-class="bg-orange-50 text-orange-700 border-r-2 border-orange-500"
  >
    <component 
      :is="icon" 
      class="flex-shrink-0 mr-3 w-5 h-5" 
      :class="iconClasses"
    />
    <span class="flex-1 truncate">{{ name }}</span>
    <span
      v-if="badge"
      class="ml-3 inline-block py-0.5 px-2 text-xs font-medium rounded-full"
      :class="badgeClasses"
    >
      {{ badge }}
    </span>
  </router-link>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'

defineOptions({ name: 'SidebarLink' })

const props = defineProps<{
  to: string
  icon: string | any
  name: string
  badge?: string
}>()

defineEmits<{
  navigate: []
}>()

const route = useRoute()

const isActive = computed(() => {
  if (props.to === '/dashboard') {
    return route.path === '/dashboard'
  }
  return route.path.startsWith(props.to)
})

const linkClasses = computed(() => [
  isActive.value
    ? 'bg-orange-50 text-orange-700 border-r-2 border-orange-500'
    : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
])

const iconClasses = computed(() => [
  isActive.value
    ? 'text-orange-500'
    : 'text-gray-400 group-hover:text-gray-500'
])

const badgeClasses = computed(() => [
  isActive.value
    ? 'bg-orange-100 text-orange-600'
    : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200'
])
</script>