<template>
  <label
    :for="htmlFor"
    :class="cn(
      // Base label styles
      'text-sm font-medium text-foreground',
      'cursor-pointer select-none',
      // Custom classes
      className
    )"
  >
    <slot>{{ text }}</slot>
    
    <!-- Required indicator -->
    <span 
      v-if="required"
      class="ml-1 text-destructive"
      aria-label="Required field"
    >
      *
    </span>
  </label>
</template>

<script setup lang="ts">
import { cn } from '@/utils/cn';

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