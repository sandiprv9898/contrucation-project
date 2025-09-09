<template>
  <div :class="containerClass">
    <!-- Custom Header Slot -->
    <div v-if="$slots.header" class="mb-2">
      <slot name="header" />
    </div>

    <!-- Label Section -->
    <div v-if="label || $slots.label" class="flex items-center justify-between mb-2">
      <slot name="label">
        <VLabel 
          :for="fieldId" 
          :class="computedLabelClass"
          :required="required"
        >
          <span>{{ label }}</span>
          <!-- Optional Badge -->
          <VBadge v-if="badge" :variant="badgeVariant" class="ml-2" size="sm">
            {{ badge }}
          </VBadge>
          <!-- Tooltip -->
          <button
            v-if="tooltip"
            type="button"
            class="ml-1 text-gray-400 hover:text-gray-600"
            :title="tooltip"
            @click="showTooltip = !showTooltip"
          >
            <HelpCircle class="w-4 h-4" />
          </button>
        </VLabel>
      </slot>
      
      <!-- Label Actions Slot -->
      <div v-if="$slots.labelActions">
        <slot name="labelActions" />
      </div>
    </div>

    <!-- Tooltip Content -->
    <div v-if="tooltip && showTooltip" class="mb-2 p-2 text-xs bg-gray-100 rounded border">
      {{ tooltip }}
    </div>

    <!-- Input Container -->
    <div :class="inputContainerClass">
      <!-- Prefix -->
      <div v-if="$slots.prefix" class="absolute inset-y-0 left-0 flex items-center pl-3">
        <slot name="prefix" />
      </div>

      <!-- Main Input Slot -->
      <slot 
        :field-id="fieldId"
        :has-error="hasError"
        :input-class="computedInputClass"
        :disabled="disabled"
        :focused="isFocused"
        :touched="isTouched"
      />
      
      <!-- Suffix/Icon -->
      <div v-if="$slots.suffix || $slots.icon" class="absolute inset-y-0 right-0 flex items-center pr-3">
        <slot name="suffix">
          <slot name="icon" />
        </slot>
      </div>

      <!-- Loading Indicator -->
      <div v-if="loading" class="absolute inset-y-0 right-0 flex items-center pr-3">
        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-orange-500"></div>
      </div>
    </div>

    <!-- Character Count -->
    <div v-if="showCharCount && maxLength" class="flex justify-end mt-1">
      <span :class="charCountClass">
        {{ currentLength }} / {{ maxLength }}
      </span>
    </div>

    <!-- Help Text -->
    <div v-if="(helpText || $slots.help) && !hasError" class="mt-1">
      <slot name="help">
        <p :class="helpTextClass">
          <component v-if="helpIcon" :is="helpIcon" class="w-4 h-4 mr-1 inline" />
          {{ helpText }}
        </p>
      </slot>
    </div>

    <!-- Error Messages -->
    <div v-if="hasError" class="mt-1 space-y-1">
      <!-- Single Error -->
      <p v-if="typeof errorMessage === 'string'" :class="errorTextClass">
        <AlertCircle class="w-4 h-4 mr-1 inline" />
        {{ errorMessage }}
      </p>
      
      <!-- Multiple Errors -->
      <div v-else-if="Array.isArray(errorMessage)">
        <p v-for="(error, index) in errorMessage" :key="index" :class="errorTextClass">
          <AlertCircle class="w-4 h-4 mr-1 inline" />
          {{ error }}
        </p>
      </div>
    </div>

    <!-- Success Message -->
    <div v-if="successMessage && !hasError" class="mt-1">
      <p :class="successTextClass">
        <CheckCircle class="w-4 h-4 mr-1 inline text-green-500" />
        {{ successMessage }}
      </p>
    </div>

    <!-- Custom Footer -->
    <div v-if="$slots.footer" class="mt-2">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, useId, ref } from 'vue';
import { VLabel, VBadge } from '@/components/ui';
import { HelpCircle, AlertCircle, CheckCircle } from 'lucide-vue-next';
import { cn } from '@/utils/cn';

interface Props {
  label?: string;
  errorMessage?: string | string[];
  helpText?: string;
  successMessage?: string;
  required?: boolean;
  disabled?: boolean;
  loading?: boolean;
  size?: 'sm' | 'md' | 'lg';
  variant?: 'default' | 'compact';
  
  // Badge and tooltip
  badge?: string;
  badgeVariant?: 'default' | 'success' | 'warning' | 'danger' | 'info';
  tooltip?: string;
  
  // Character counting
  maxLength?: number;
  showCharCount?: boolean;
  currentLength?: number;
  
  // Icons
  helpIcon?: any;
  
  // States
  isFocused?: boolean;
  isTouched?: boolean;
  
  // Styling
  containerClass?: string;
  labelClass?: string;
  inputClass?: string;
  inputContainerClass?: string;
  helpTextClass?: string;
  errorTextClass?: string;
  successTextClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  variant: 'default',
  badgeVariant: 'default',
  showCharCount: false,
  currentLength: 0,
  labelClass: 'text-sm font-medium text-gray-700',
  inputClass: '',
  helpTextClass: 'text-xs text-gray-500 flex items-center',
  errorTextClass: 'text-xs text-red-600 font-medium flex items-center',
  successTextClass: 'text-xs text-green-600 font-medium flex items-center'
});

// State
const showTooltip = ref(false);

// Generate unique field ID
const fieldId = useId();

// Computed properties
const hasError = computed(() => {
  if (Array.isArray(props.errorMessage)) {
    return props.errorMessage.length > 0;
  }
  return !!props.errorMessage;
});

const containerClass = computed(() => {
  const base = 'space-y-1';
  return cn(base, props.containerClass);
});

const computedLabelClass = computed(() => {
  const base = props.labelClass;
  const requiredClasses = props.required ? 'required' : '';
  return cn(base, requiredClasses);
});

const inputContainerClass = computed(() => {
  const base = 'relative';
  const sizeClasses = {
    sm: '',
    md: '',
    lg: ''
  };
  
  return cn(
    base,
    sizeClasses[props.size],
    props.inputContainerClass
  );
});

const computedInputClass = computed(() => {
  const baseClasses = 'block w-full border rounded-md text-sm transition-all duration-200 bg-gray-50 border-gray-200 placeholder:text-gray-400 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:opacity-50';
  
  const sizeClasses = {
    sm: 'h-8 px-2 text-xs',
    md: 'h-10 px-3 text-sm', 
    lg: 'h-12 px-4 text-sm'
  };
  
  // Adjust padding for prefix/suffix
  const paddingClasses = {
    withPrefix: 'pl-10',
    withSuffix: 'pr-10',
    withBoth: 'pl-10 pr-10'
  };
  
  const variantClasses = {
    default: 'focus:ring-orange-500/20',
    compact: 'focus:ring-orange-500/10'
  };
  
  const stateClasses = hasError.value 
    ? 'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500/20'
    : props.successMessage && !hasError.value
    ? 'border-green-500 bg-green-50 focus:border-green-500 focus:ring-green-500/20'
    : 'border-gray-200 focus:border-orange-500';

  return cn(
    baseClasses,
    sizeClasses[props.size],
    variantClasses[props.variant],
    stateClasses,
    props.inputClass
  );
});

const charCountClass = computed(() => {
  if (!props.maxLength) return 'text-xs text-gray-500';
  
  const percentage = (props.currentLength / props.maxLength) * 100;
  
  if (percentage >= 100) {
    return 'text-xs text-red-600 font-medium';
  } else if (percentage >= 80) {
    return 'text-xs text-orange-600 font-medium';
  } else {
    return 'text-xs text-gray-500';
  }
});
</script>