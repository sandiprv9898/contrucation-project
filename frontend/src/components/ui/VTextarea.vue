<template>
  <textarea
    :id="fieldId"
    v-model="modelValue"
    :disabled="disabled"
    :required="required"
    :placeholder="placeholder"
    :rows="rows"
    :maxlength="maxlength"
    :class="computedTextareaClass"
    v-bind="$attrs"
    @input="handleInput"
    @change="handleChange"
    @blur="handleBlur"
    @focus="handleFocus"
  />
</template>

<script setup lang="ts">
import { computed, useId } from 'vue';
import { cn } from '@/utils/cn';

interface Props {
  modelValue?: string;
  placeholder?: string;
  disabled?: boolean;
  required?: boolean;
  rows?: number;
  maxlength?: number;
  resize?: 'none' | 'both' | 'horizontal' | 'vertical';
  size?: 'sm' | 'md' | 'lg';
  variant?: 'default' | 'compact';
  hasError?: boolean;
  textareaClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  rows: 3,
  resize: 'vertical',
  size: 'md',
  variant: 'default'
});

const emit = defineEmits<{
  'update:modelValue': [value: string];
  input: [event: Event];
  change: [event: Event];
  blur: [event: FocusEvent];
  focus: [event: FocusEvent];
}>();

// Generate unique field ID
const fieldId = useId();

// Computed textarea classes
const computedTextareaClass = computed(() => {
  const baseClasses = 'block w-full border rounded-md text-sm transition-all duration-200 bg-gray-50 border-gray-200 placeholder:text-gray-400 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:opacity-50';
  
  const sizeClasses = {
    sm: 'px-2 py-1.5 text-xs',
    md: 'px-3 py-2 text-sm',
    lg: 'px-4 py-3 text-sm'
  };
  
  const resizeClasses = {
    none: 'resize-none',
    both: 'resize',
    horizontal: 'resize-x',
    vertical: 'resize-y'
  };
  
  const variantClasses = {
    default: 'hover:border-gray-300 focus:border-orange-500 focus:ring-orange-500/20',
    compact: 'hover:border-gray-300 focus:border-orange-500 focus:ring-orange-500/10'
  };
  
  const errorClasses = props.hasError
    ? 'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500/20'
    : variantClasses[props.variant];

  return cn(
    baseClasses,
    sizeClasses[props.size],
    resizeClasses[props.resize],
    errorClasses,
    props.textareaClass
  );
});

// Event handlers
const handleInput = (event: Event) => {
  const target = event.target as HTMLTextAreaElement;
  emit('update:modelValue', target.value);
  emit('input', event);
};

const handleChange = (event: Event) => {
  emit('change', event);
};

const handleBlur = (event: FocusEvent) => {
  emit('blur', event);
};

const handleFocus = (event: FocusEvent) => {
  emit('focus', event);
};

// Expose fieldId for external use (like VFormField)
defineExpose({ fieldId });
</script>