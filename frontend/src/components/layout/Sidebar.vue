<template>
  <!-- Mobile sidebar overlay -->
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 lg:hidden"
      @click="$emit('close')"
    >
      <div class="fixed inset-0 bg-gray-600 bg-opacity-75"></div>
    </div>
  </Teleport>

  <!-- Mobile sidebar -->
  <div
    :class="[
      'fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:hidden',
      isOpen ? 'translate-x-0' : '-translate-x-full'
    ]"
  >
    <SidebarContent @navigate="$emit('close')" />
  </div>

  <!-- Desktop sidebar -->
  <div class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-50 lg:block lg:w-64 lg:bg-white lg:shadow-lg">
    <SidebarContent />
  </div>
</template>

<script setup lang="ts">
import SidebarContent from './SidebarContent.vue'

defineOptions({ name: 'Sidebar' })

defineProps<{
  isOpen: boolean
}>()

defineEmits<{
  close: []
}>()
</script>