<template>
  <div
    :class="cn(
      // Base card styles
      'bg-card border border-border rounded-lg shadow-sm',
      // Custom classes
      className
    )"
  >
    <!-- Card header -->
    <div 
      v-if="$slots.header"
      :class="cn(
        'px-6 py-4 border-b border-border',
        headerClass
      )"
    >
      <slot name="header" />
    </div>

    <!-- Card content -->
    <div 
      :class="cn(
        'px-6 py-4',
        contentClass
      )"
    >
      <slot />
    </div>

    <!-- Card footer -->
    <div 
      v-if="$slots.footer"
      :class="cn(
        'px-6 py-4 border-t border-border bg-muted/50',
        footerClass
      )"
    >
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { cn } from '@/utils/cn';

interface Props {
  /**
   * Additional CSS classes for the card
   */
  className?: string;
  /**
   * Additional CSS classes for the header
   */
  headerClass?: string;
  /**
   * Additional CSS classes for the content
   */
  contentClass?: string;
  /**
   * Additional CSS classes for the footer
   */
  footerClass?: string;
}

const props = withDefaults(defineProps<Props>(), {});
</script>