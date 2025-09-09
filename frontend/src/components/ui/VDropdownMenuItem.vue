<template>
  <button
    type="button"
    :class="[
      'group flex w-full items-center px-2 py-2 text-sm',
      disabled ? 'text-gray-400 cursor-not-allowed' : variantClasses,
    ]"
    :disabled="disabled"
    @click="handleClick"
    role="menuitem"
  >
    <slot />
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  variant?: 'default' | 'destructive';
  disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  disabled: false
});

const emit = defineEmits<{
  click: [event: MouseEvent];
}>();

const variantClasses = computed(() => {
  const variants = {
    default: 'text-gray-700 hover:bg-gray-100 hover:text-gray-900',
    destructive: 'text-red-600 hover:bg-red-50 hover:text-red-700'
  };
  return variants[props.variant];
});

const handleClick = (event: MouseEvent) => {
  if (!props.disabled) {
    emit('click', event);
  }
};
</script>