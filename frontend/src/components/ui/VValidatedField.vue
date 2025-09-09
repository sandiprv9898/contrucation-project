<template>
  <VFormField
    :label="label"
    :error-message="displayError"
    :success-message="successMessage"
    :help-text="helpText"
    :required="required"
    :loading="isValidating"
    :size="size"
    :variant="variant"
    v-bind="formFieldProps"
  >
    <!-- Pass through all slots to the underlying component -->
    <component
      :is="fieldComponent"
      :id="fieldId"
      v-model="modelValue"
      v-bind="fieldProps"
      :disabled="disabled"
      :readonly="readonly"
      :placeholder="placeholder"
      :class="inputClass"
      @input="handleInput"
      @change="handleChange"
      @blur="handleBlur"
      @focus="handleFocus"
    >
      <!-- Pass through slots to the field component -->
      <template v-for="(_, name) in $slots" #[name]="slotProps">
        <slot :name="name" v-bind="slotProps" />
      </template>
    </component>

    <!-- Validation Status Indicator -->
    <template v-if="showValidationStatus" #suffix>
      <div class="flex items-center">
        <!-- Success indicator -->
        <CheckCircle 
          v-if="isValid && isTouched && !isValidating"
          class="w-4 h-4 text-green-500"
        />
        <!-- Error indicator -->
        <AlertCircle 
          v-else-if="hasError && isTouched"
          class="w-4 h-4 text-red-500"
        />
        <!-- Validating indicator -->
        <div 
          v-else-if="isValidating"
          class="animate-spin rounded-full h-4 w-4 border-2 border-orange-500 border-t-transparent"
        ></div>
      </div>
    </template>

    <!-- Validation Progress -->
    <template v-if="showValidationProgress && rules.length > 0" #help>
      <div class="space-y-1">
        <div v-if="helpText" class="text-xs text-gray-500">{{ helpText }}</div>
        <div class="space-y-1">
          <div
            v-for="(rule, index) in validationProgress"
            :key="index"
            class="flex items-center text-xs"
          >
            <CheckCircle 
              v-if="rule.passed"
              class="w-3 h-3 text-green-500 mr-1 flex-shrink-0"
            />
            <XCircle 
              v-else-if="rule.failed"
              class="w-3 h-3 text-red-500 mr-1 flex-shrink-0"
            />
            <div 
              v-else
              class="w-3 h-3 rounded-full bg-gray-300 mr-1 flex-shrink-0"
            ></div>
            <span :class="[
              rule.passed ? 'text-green-600' : rule.failed ? 'text-red-600' : 'text-gray-500'
            ]">
              {{ rule.message }}
            </span>
          </div>
        </div>
      </div>
    </template>
  </VFormField>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { VFormField, VInput, VTextarea, VSelect, VCheckbox } from '@/components/ui';
import { CheckCircle, AlertCircle, XCircle } from 'lucide-vue-next';
import { useValidation, type ValidationRule, type FieldValidationConfig, validationRules } from '@/composables/useValidation';

interface ValidationProgress {
  message: string;
  passed: boolean;
  failed: boolean;
}

interface Props {
  // Field identification
  name: string;
  modelValue?: any;
  
  // Field type and component
  type?: 'text' | 'email' | 'password' | 'number' | 'textarea' | 'select' | 'checkbox' | 'date' | 'url' | 'tel';
  component?: any;
  
  // Basic field props
  label?: string;
  placeholder?: string;
  helpText?: string;
  successMessage?: string;
  required?: boolean;
  disabled?: boolean;
  readonly?: boolean;
  
  // Validation configuration
  rules?: ValidationRule[];
  validateOn?: ('change' | 'blur' | 'submit')[];
  debounce?: number;
  transform?: (value: any) => any;
  dependencies?: string[];
  
  // Built-in validation shortcuts
  email?: boolean;
  url?: boolean;
  phone?: boolean;
  numeric?: boolean;
  minLength?: number;
  maxLength?: number;
  min?: number;
  max?: number;
  pattern?: RegExp;
  match?: string;
  
  // Async validation
  asyncValidator?: (value: any, formData?: Record<string, any>) => Promise<string | null>;
  
  // UI configuration
  size?: 'sm' | 'md' | 'lg';
  variant?: 'default' | 'compact';
  showValidationStatus?: boolean;
  showValidationProgress?: boolean;
  showErrorsOnTouch?: boolean;
  
  // Field-specific props (for selects, textareas, etc.)
  options?: Array<{ label: string; value: any }>;
  items?: Array<{ label: string; value: any }>;
  rows?: number;
  multiple?: boolean;
  
  // Styling
  inputClass?: string;
  
  // Form integration
  formData?: Record<string, any>;
  validationInstance?: ReturnType<typeof useValidation>;
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  validateOn: () => ['change', 'blur'],
  debounce: 0,
  showValidationStatus: true,
  showValidationProgress: false,
  showErrorsOnTouch: true,
  size: 'md',
  variant: 'default',
  rules: () => []
});

const emit = defineEmits<{
  'update:modelValue': [value: any];
  'validation-change': [isValid: boolean, error: string | null];
  'touch': [];
  'dirty': [];
  input: [event: Event];
  change: [event: Event];
  blur: [event: FocusEvent];
  focus: [event: FocusEvent];
}>();

// Create or use existing validation instance
const validation = props.validationInstance || useValidation(props.formData || {});

// Field state
const isTouched = ref(false);
const isDirty = ref(false);
const isValidating = ref(false);
const currentError = ref<string | null>(null);
const fieldId = `field-${props.name}`;

// Computed field component
const fieldComponent = computed(() => {
  if (props.component) return props.component;
  
  const componentMap: Record<string, any> = {
    textarea: VTextarea,
    select: VSelect,
    checkbox: VCheckbox,
    text: VInput,
    email: VInput,
    password: VInput,
    number: VInput,
    date: VInput,
    url: VInput,
    tel: VInput
  };
  
  return componentMap[props.type] || VInput;
});

// Build validation rules from props
const rules = computed((): ValidationRule[] => {
  const allRules: ValidationRule[] = [...props.rules];
  
  // Add built-in validation rules based on props
  if (props.required) {
    allRules.unshift(validationRules.required());
  }
  
  if (props.email || props.type === 'email') {
    allRules.push(validationRules.email());
  }
  
  if (props.url || props.type === 'url') {
    allRules.push(validationRules.url());
  }
  
  if (props.phone || props.type === 'tel') {
    allRules.push(validationRules.phone());
  }
  
  if (props.numeric || props.type === 'number') {
    allRules.push(validationRules.numeric());
  }
  
  if (props.minLength !== undefined) {
    allRules.push(validationRules.minLength(props.minLength));
  }
  
  if (props.maxLength !== undefined) {
    allRules.push(validationRules.maxLength(props.maxLength));
  }
  
  if (props.min !== undefined) {
    allRules.push(validationRules.min(props.min));
  }
  
  if (props.max !== undefined) {
    allRules.push(validationRules.max(props.max));
  }
  
  if (props.pattern) {
    allRules.push(validationRules.pattern(props.pattern));
  }
  
  if (props.match) {
    allRules.push(validationRules.match(props.match));
  }
  
  if (props.asyncValidator) {
    allRules.push(validationRules.async(props.asyncValidator));
  }
  
  return allRules;
});

// Validation configuration
const fieldConfig = computed((): FieldValidationConfig => ({
  rules: rules.value,
  required: props.required,
  validateOn: props.validateOn,
  debounce: props.debounce,
  transform: props.transform,
  dependencies: props.dependencies
}));

// Validation state
const hasError = computed(() => !!currentError.value);
const isValid = computed(() => !hasError.value && isTouched.value);

// Error display logic
const displayError = computed(() => {
  if (!props.showErrorsOnTouch) return currentError.value;
  return isTouched.value ? currentError.value : null;
});

// Validation progress for visual feedback
const validationProgress = computed((): ValidationProgress[] => {
  if (!props.showValidationProgress) return [];
  
  return rules.value.map(rule => {
    const message = rule.message || 'Validation rule';
    let passed = false;
    let failed = false;
    
    if (isTouched.value && props.modelValue !== undefined && props.modelValue !== '') {
      try {
        const result = rule.validate(props.modelValue, props.formData);
        passed = result === null;
        failed = result !== null;
      } catch {
        failed = true;
      }
    }
    
    return { message, passed, failed };
  });
});

// Field props for the underlying component
const fieldProps = computed(() => {
  const baseProps: Record<string, any> = {
    type: props.type === 'text' ? undefined : props.type,
    required: props.required,
    disabled: props.disabled,
    readonly: props.readonly
  };
  
  // Type-specific props
  switch (props.type) {
    case 'textarea':
      if (props.rows) baseProps.rows = props.rows;
      if (props.maxLength) baseProps.maxlength = props.maxLength;
      break;
    case 'select':
      baseProps.items = props.items || props.options;
      baseProps.multiple = props.multiple;
      break;
    case 'number':
      if (props.min !== undefined) baseProps.min = props.min;
      if (props.max !== undefined) baseProps.max = props.max;
      break;
  }
  
  return baseProps;
});

// VFormField props
const formFieldProps = computed(() => ({
  size: props.size,
  variant: props.variant,
  class: props.inputClass
}));

// Event handlers
const handleInput = async (event: Event) => {
  const target = event.target as HTMLInputElement;
  const value = target.value;
  
  if (!isDirty.value) {
    isDirty.value = true;
    emit('dirty');
  }
  
  emit('update:modelValue', value);
  emit('input', event);
  
  // Validate on change if configured
  if (props.validateOn.includes('change')) {
    await validateField('change');
  }
};

const handleChange = async (event: Event) => {
  emit('change', event);
  
  // Validate on change if configured
  if (props.validateOn.includes('change')) {
    await validateField('change');
  }
};

const handleBlur = async (event: FocusEvent) => {
  if (!isTouched.value) {
    isTouched.value = true;
    emit('touch');
  }
  
  emit('blur', event);
  
  // Validate on blur if configured
  if (props.validateOn.includes('blur')) {
    await validateField('blur');
  }
};

const handleFocus = (event: FocusEvent) => {
  emit('focus', event);
};

// Validation method
const validateField = async (trigger: 'change' | 'blur' | 'submit' = 'change') => {
  isValidating.value = true;
  
  try {
    const error = await validation.validateField(props.name, props.modelValue, trigger);
    currentError.value = error;
    emit('validation-change', !error, error);
    return error;
  } finally {
    isValidating.value = false;
  }
};

// Public API
const validate = (trigger: 'change' | 'blur' | 'submit' = 'submit') => validateField(trigger);

const reset = () => {
  isTouched.value = false;
  isDirty.value = false;
  currentError.value = null;
  validation.resetField(props.name);
};

const touch = () => {
  if (!isTouched.value) {
    isTouched.value = true;
    emit('touch');
  }
};

// Setup validation
onMounted(() => {
  validation.addFieldValidation(props.name, fieldConfig.value);
  
  // Watch for external validation state changes
  watch(
    () => validation.getFieldError(props.name),
    (error) => {
      currentError.value = error;
      emit('validation-change', !error, error);
    }
  );
  
  // Watch for validation status
  watch(
    () => validation.validationState.isValidating,
    (validating) => {
      // Check if this field is being validated
      isValidating.value = validating && validation.validationState.validatedFields.has(props.name);
    }
  );
});

onUnmounted(() => {
  validation.removeFieldValidation(props.name);
});

// Watch for configuration changes
watch(fieldConfig, (newConfig) => {
  validation.updateFieldValidation(props.name, newConfig);
}, { deep: true });

// Expose public API
defineExpose({
  validate,
  reset,
  touch,
  hasError,
  isValid,
  isTouched,
  isDirty,
  isValidating,
  error: computed(() => currentError.value)
});
</script>