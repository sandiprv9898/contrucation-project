<template>
  <div class="w-full">
    <input
      :id="inputId"
      :type="type"
      :name="name"
      :placeholder="placeholder"
      :disabled="disabled"
      :required="required"
      :value="modelValue"
      :autocomplete="autocomplete"
      :class="[
        // Base styles
        'h-10 w-full px-3 py-2 text-sm rounded-md transition-colors',
        'bg-white border border-gray-300',
        'placeholder:text-gray-500',
        'focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500',
        'disabled:cursor-not-allowed disabled:opacity-50',
        // Error state
        error ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : '',
        // Custom classes
        className
      ]"
      @input="handleInput"
      @blur="handleBlur"
      @focus="handleFocus"
    />
    
    <!-- Error message -->
    <p 
      v-if="error" 
      class="mt-1 text-xs text-red-600"
      :id="`${inputId}-error`"
    >
      {{ error }}
    </p>
    
    <!-- Help text -->
    <p 
      v-else-if="help" 
      class="mt-1 text-xs text-gray-500"
      :id="`${inputId}-help`"
    >
      {{ help }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  /**
   * Input value (v-model)
   */
  modelValue?: string | number;
  /**
   * Input type
   */
  type?: 'text' | 'email' | 'password' | 'number' | 'tel' | 'url' | 'search';
  /**
   * Input name attribute
   */
  name?: string;
  /**
   * Placeholder text
   */
  placeholder?: string;
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
   * Help text
   */
  help?: string;
  /**
   * Input variant for styling
   */
  variant?: 'default' | 'error';
  /**
   * Autocomplete attribute
   */
  autocomplete?: string;
  /**
   * Additional CSS classes
   */
  className?: string;
  /**
   * Input ID (auto-generated if not provided)
   */
  id?: string;
}

interface Emits {
  /**
   * v-model update event
   */
  'update:modelValue': [value: string | number];
  /**
   * Input focus event
   */
  focus: [event: FocusEvent];
  /**
   * Input blur event
   */
  blur: [event: FocusEvent];
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  disabled: false,
  required: false,
  variant: 'default'
});

const emit = defineEmits<Emits>();

// Generate unique ID for input
const inputId = computed(() => {
  return props.id || `input-${Math.random().toString(36).substr(2, 9)}`;
});

// Determine variant based on error state
const variant = computed(() => {
  if (props.error) return 'error';
  return props.variant;
});

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const value = props.type === 'number' ? Number(target.value) : target.value;
  emit('update:modelValue', value);
};

const handleFocus = (event: FocusEvent) => {
  emit('focus', event);
};

const handleBlur = (event: FocusEvent) => {
  emit('blur', event);
};
</script>