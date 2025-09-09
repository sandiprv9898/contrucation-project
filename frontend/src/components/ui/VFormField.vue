<template>
  <div class="space-y-2">
    <!-- Label -->
    <VLabel 
      v-if="label" 
      :for="fieldId" 
      :class="labelClass"
      :required="required"
    >
      {{ label }}
    </VLabel>

    <!-- Input Slot -->
    <div class="relative">
      <slot 
        :field-id="fieldId"
        :has-error="hasError"
        :input-class="computedInputClass"
        :disabled="disabled"
      />
      
      <!-- Icon slot for password toggle, etc -->
      <div v-if="$slots.icon" class="absolute inset-y-0 right-0 flex items-center pr-3">
        <slot name="icon" />
      </div>
    </div>

    <!-- Help Text -->
    <p v-if="helpText && !hasError" :class="helpTextClass">
      {{ helpText }}
    </p>

    <!-- Error Message -->
    <p v-if="hasError && errorMessage" :class="errorTextClass">
      {{ errorMessage }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { computed, useId } from 'vue';
import { VLabel } from '@/components/ui';
import { cn } from '@/utils/cn';

interface Props {
  label?: string;
  errorMessage?: string;
  helpText?: string;
  required?: boolean;
  disabled?: boolean;
  size?: 'sm' | 'md' | 'lg';
  variant?: 'default' | 'compact';
  labelClass?: string;
  inputClass?: string;
  helpTextClass?: string;
  errorTextClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  variant: 'default',
  labelClass: 'text-sm font-medium text-gray-700',
  inputClass: '',
  helpTextClass: 'text-xs text-gray-500',
  errorTextClass: 'text-xs text-red-600 font-medium'
});

// Generate unique field ID
const fieldId = useId();

// Computed properties
const hasError = computed(() => !!props.errorMessage);

const computedInputClass = computed(() => {
  const baseClasses = 'block w-full border rounded-md px-3 py-2 text-sm transition-all duration-200 bg-gray-50 border-gray-200 placeholder:text-gray-400 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:border-orange-500 disabled:cursor-not-allowed disabled:opacity-50';
  
  const sizeClasses = {
    sm: 'h-8 px-2 text-xs',
    md: 'h-10 px-3 text-sm', 
    lg: 'h-12 px-4 text-sm'
  };
  
  const variantClasses = {
    default: 'focus:ring-orange-500/20',
    compact: 'focus:ring-orange-500/10 h-8'
  };
  
  const errorClasses = hasError.value 
    ? 'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500/20'
    : 'border-gray-200';

  return cn(
    baseClasses,
    sizeClasses[props.size],
    variantClasses[props.variant],
    errorClasses,
    props.inputClass
  );
});
</script>