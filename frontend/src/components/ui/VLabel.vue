<template>
  <label
    :for="htmlFor"
    :class="[
      // Base label styles
      'text-sm font-medium text-gray-700',
      'cursor-pointer select-none',
      // Custom classes
      className
    ]"
  >
    <slot>{{ text }}</slot>
    
    <!-- Required indicator -->
    <span 
      v-if="required"
      class="ml-1 text-red-500"
      aria-label="Required field"
    >
      *
    </span>
  </label>
</template>

<script setup lang="ts">

interface Props {
  /**
   * Associated input ID (for attribute)
   */
  htmlFor?: string;
  /**
   * Label text (alternative to default slot)
   */
  text?: string;
  /**
   * Show required indicator
   */
  required?: boolean;
  /**
   * Additional CSS classes
   */
  className?: string;
}

const props = withDefaults(defineProps<Props>(), {
  required: false
});
</script>