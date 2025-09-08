<template>
  <div class="flex items-start gap-2">
    <div class="relative flex items-center justify-center">
      <input
        :id="inputId"
        type="checkbox"
        :name="name"
        :disabled="disabled"
        :required="required"
        :checked="modelValue"
        :class="cn(
          // Base styles - ensuring 44px touch target
          'h-4 w-4 min-h-[44px] min-w-[44px] sm:h-4 sm:w-4 sm:min-h-[16px] sm:min-w-[16px]',
          'border border-border rounded transition-colors',
          'focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-1',
          'disabled:cursor-not-allowed disabled:opacity-50',
          // Checked state
          modelValue && 'bg-primary border-primary text-white',
          // Custom classes
          className
        )"
        @change="handleChange"
      />
      
      <!-- Custom checkmark (hidden on mobile, visible on desktop) -->
      <svg
        v-if="modelValue"
        class="absolute h-3 w-3 text-white pointer-events-none hidden sm:block"
        fill="currentColor"
        viewBox="0 0 20 20"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          fill-rule="evenodd"
          d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
          clip-rule="evenodd"
        />
      </svg>
    </div>

    <!-- Label and content -->
    <div v-if="$slots.default || label" class="flex-1 min-w-0">
      <label
        :for="inputId"
        :class="cn(
          'text-sm font-medium cursor-pointer select-none',
          'text-foreground',
          disabled && 'cursor-not-allowed opacity-50',
          labelClass
        )"
      >
        <slot>{{ label }}</slot>
      </label>
      
      <!-- Description text -->
      <p 
        v-if="description" 
        class="mt-1 text-xs text-muted-foreground"
      >
        {{ description }}
      </p>
      
      <!-- Error message -->
      <p 
        v-if="error" 
        class="mt-1 text-xs text-destructive"
      >
        {{ error }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/utils/cn';

interface Props {
  /**
   * Checkbox value (v-model)
   */
  modelValue?: boolean;
  /**
   * Input name attribute
   */
  name?: string;
  /**
   * Label text (alternative to default slot)
   */
  label?: string;
  /**
   * Description text below label
   */
  description?: string;
  /**
   * Disabled state
   */
  disabled?: boolean;
  /**
   * Required field indicator
   */
  required?: boolean;
  /**
   * Error message
   */
  error?: string;
  /**
   * Additional CSS classes for checkbox
   */
  className?: string;
  /**
   * Additional CSS classes for label
   */
  labelClass?: string;
  /**
   * Checkbox ID (auto-generated if not provided)
   */
  id?: string;
}

interface Emits {
  /**
   * v-model update event
   */
  'update:modelValue': [value: boolean];
  /**
   * Change event
   */
  change: [value: boolean, event: Event];
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  disabled: false,
  required: false
});

const emit = defineEmits<Emits>();

// Generate unique ID for input
const inputId = computed(() => {
  return props.id || `checkbox-${Math.random().toString(36).substr(2, 9)}`;
});

const handleChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const checked = target.checked;
  emit('update:modelValue', checked);
  emit('change', checked, event);
};
</script>