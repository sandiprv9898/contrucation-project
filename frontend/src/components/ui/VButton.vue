<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="cn(
      // Base styles
      'inline-flex items-center justify-center gap-2 rounded-md font-medium transition-colors',
      'focus:outline-none focus:ring-2 focus:ring-offset-2',
      'disabled:pointer-events-none disabled:cursor-not-allowed',
      // Size styles
      buttonSizes[size],
      // Variant styles
      buttonVariants[variant],
      // Custom classes
      className
    )"
    @click="handleClick"
  >
    <!-- Loading spinner -->
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-2 h-4 w-4"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
      />
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      />
    </svg>

    <!-- Icon slot (left side) -->
    <slot name="icon" />

    <!-- Default slot for text content -->
    <slot />

    <!-- Right icon slot -->
    <slot name="icon-right" />
  </button>
</template>

<script setup lang="ts">
import { cn } from '@/utils/cn';
import { buttonVariants, buttonSizes } from '@/utils/variants';
import type { ButtonVariant, ButtonSize } from '@/utils/variants';

interface Props {
  /**
   * Button variant style
   */
  variant?: ButtonVariant;
  /**
   * Button size
   */
  size?: ButtonSize;
  /**
   * HTML button type
   */
  type?: 'button' | 'submit' | 'reset';
  /**
   * Disabled state
   */
  disabled?: boolean;
  /**
   * Loading state - shows spinner and disables interaction
   */
  loading?: boolean;
  /**
   * Additional CSS classes
   */
  className?: string;
}

interface Emits {
  /**
   * Click event
   */
  click: [event: MouseEvent];
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'md',
  type: 'button',
  disabled: false,
  loading: false
});

const emit = defineEmits<Emits>();

const handleClick = (event: MouseEvent) => {
  if (!props.disabled && !props.loading) {
    emit('click', event);
  }
};
</script>