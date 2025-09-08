<template>
  <span
    :class="cn(
      // Base badge styles
      'inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full',
      'transition-colors',
      // Variant styles
      badgeVariants[variant],
      // Custom classes
      className
    )"
  >
    <!-- Icon slot -->
    <slot name="icon" />
    
    <!-- Badge content -->
    <slot>{{ text }}</slot>
  </span>
</template>

<script setup lang="ts">
import { cn } from '@/utils/cn';
import { badgeVariants } from '@/utils/variants';
import type { BadgeVariant } from '@/utils/variants';

interface Props {
  /**
   * Badge variant style
   */
  variant?: BadgeVariant;
  /**
   * Badge text (alternative to default slot)
   */
  text?: string;
  /**
   * Additional CSS classes
   */
  className?: string;
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default'
});
</script>