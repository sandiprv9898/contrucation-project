<template>
  <select
    :id="fieldId"
    v-model="modelValue"
    :disabled="disabled"
    :required="required"
    :class="computedSelectClass"
    v-bind="$attrs"
    @change="handleChange"
  >
    <option v-if="placeholder" value="" disabled>
      {{ placeholder }}
    </option>
    
    <!-- Default slot for custom options -->
    <slot>
      <!-- Auto-generated options from items prop -->
      <option
        v-for="item in items"
        :key="getItemValue(item)"
        :value="getItemValue(item)"
        :disabled="getItemDisabled(item)"
      >
        {{ getItemLabel(item) }}
      </option>
    </slot>
  </select>
</template>

<script setup lang="ts">
import { computed, useId } from 'vue';
import { cn } from '@/utils/cn';

// Define types for select items
interface SelectItem {
  label: string;
  value: string | number;
  disabled?: boolean;
}

interface Props {
  modelValue?: string | number | null;
  items?: SelectItem[] | string[] | number[];
  placeholder?: string;
  disabled?: boolean;
  required?: boolean;
  size?: 'sm' | 'md' | 'lg';
  variant?: 'default' | 'compact';
  hasError?: boolean;
  selectClass?: string;
  // For complex objects
  itemLabel?: string;
  itemValue?: string;
  itemDisabled?: string;
}

const props = withDefaults(defineProps<Props>(), {
  items: () => [],
  size: 'md',
  variant: 'default',
  itemLabel: 'label',
  itemValue: 'value',
  itemDisabled: 'disabled'
});

const emit = defineEmits<{
  'update:modelValue': [value: string | number | null];
  change: [value: string | number | null];
}>();

// Generate unique field ID
const fieldId = useId();

// Helper functions for different item types
const getItemLabel = (item: any): string => {
  if (typeof item === 'string' || typeof item === 'number') {
    return String(item);
  }
  return item[props.itemLabel] || item.label || String(item);
};

const getItemValue = (item: any): string | number => {
  if (typeof item === 'string' || typeof item === 'number') {
    return item;
  }
  return item[props.itemValue] || item.value || item;
};

const getItemDisabled = (item: any): boolean => {
  if (typeof item === 'string' || typeof item === 'number') {
    return false;
  }
  return item[props.itemDisabled] || item.disabled || false;
};

// Computed select classes
const computedSelectClass = computed(() => {
  const baseClasses = 'block w-full border rounded-md bg-white text-sm transition-all duration-200 focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none bg-no-repeat bg-right bg-select-arrow';
  
  const sizeClasses = {
    sm: 'h-8 px-2 pr-8 text-xs',
    md: 'h-10 px-3 pr-10 text-sm',
    lg: 'h-12 px-4 pr-12 text-sm'
  };
  
  const variantClasses = {
    default: 'border-gray-200 hover:border-gray-300 focus:border-orange-500 focus:ring-orange-500/20',
    compact: 'border-gray-200 hover:border-gray-300 focus:border-orange-500 focus:ring-orange-500/10'
  };
  
  const errorClasses = props.hasError
    ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
    : variantClasses[props.variant];

  return cn(
    baseClasses,
    sizeClasses[props.size],
    errorClasses,
    props.selectClass
  );
});

// Event handlers
const handleChange = (event: Event) => {
  const target = event.target as HTMLSelectElement;
  const value = target.value || null;
  emit('update:modelValue', value);
  emit('change', value);
};

// Expose fieldId for external use (like VFormField)
defineExpose({ fieldId });
</script>

<style scoped>
/* Custom select arrow */
select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-size: 1.25em 1.25em;
}

select:focus {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23f97316' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
}
</style>