<template>
  <div class="space-y-6">
    <!-- Form Header -->
    <div v-if="title || description || $slots.header" class="space-y-2">
      <slot name="header">
        <h2 v-if="title" class="text-2xl font-bold text-gray-900">{{ title }}</h2>
        <p v-if="description" class="text-gray-600">{{ description }}</p>
      </slot>
    </div>
    
    <!-- Form Progress (for multi-step forms) -->
    <div v-if="showProgress && steps.length > 1" class="mb-8">
      <div class="flex items-center justify-between mb-4">
        <span class="text-sm font-medium text-gray-700">
          Step {{ currentStep + 1 }} of {{ steps.length }}
        </span>
        <span class="text-sm text-gray-500">
          {{ Math.round(((currentStep + 1) / steps.length) * 100) }}% Complete
        </span>
      </div>
      
      <div class="w-full bg-gray-200 rounded-full h-2">
        <div 
          class="bg-orange-500 h-2 rounded-full transition-all duration-300"
          :style="{ width: `${((currentStep + 1) / steps.length) * 100}%` }"
        ></div>
      </div>
      
      <!-- Step Labels -->
      <div class="flex justify-between mt-4">
        <button
          v-for="(step, index) in steps"
          :key="index"
          @click="goToStep(index)"
          :disabled="!canNavigateToStep(index)"
          :class="[
            'text-xs px-3 py-1 rounded-full transition-colors',
            index === currentStep
              ? 'bg-orange-100 text-orange-700 font-medium'
              : index < currentStep
              ? 'bg-green-100 text-green-700 hover:bg-green-200 cursor-pointer'
              : 'bg-gray-100 text-gray-500 cursor-not-allowed'
          ]"
        >
          {{ step.title || `Step ${index + 1}` }}
        </button>
      </div>
    </div>

    <!-- Form Content -->
    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Current Step Fields -->
      <div v-if="steps.length > 0" class="space-y-6">
        <h3 v-if="currentStepData.title && steps.length > 1" class="text-lg font-semibold text-gray-900">
          {{ currentStepData.title }}
        </h3>
        <p v-if="currentStepData.description" class="text-gray-600 mb-4">
          {{ currentStepData.description }}
        </p>
        
        <div :class="getFieldContainerClass(currentStepData.layout)">
          <component
            v-for="field in currentStepData.fields"
            :key="field.name"
            :is="getFieldComponent(field)"
            v-bind="getFieldProps(field)"
            v-model="formData[field.name]"
            @update:model-value="updateField(field.name, $event)"
            @blur="validateField(field.name)"
            :class="field.class"
          >
            <!-- Dynamic slots for field components -->
            <template v-for="(slotContent, slotName) in field.slots" :key="slotName" #[slotName]>
              <component v-if="slotContent.component" :is="slotContent.component" v-bind="slotContent.props" />
              <span v-else>{{ slotContent }}</span>
            </template>
          </component>
        </div>
      </div>
      
      <!-- Single Form (no steps) -->
      <div v-else :class="getFieldContainerClass(layout)">
        <component
          v-for="field in fields"
          :key="field.name"
          :is="getFieldComponent(field)"
          v-bind="getFieldProps(field)"
          v-model="formData[field.name]"
          @update:model-value="updateField(field.name, $event)"
          @blur="validateField(field.name)"
          :class="field.class"
        >
          <!-- Dynamic slots for field components -->
          <template v-for="(slotContent, slotName) in field.slots" :key="slotName" #[slotName]>
            <component v-if="slotContent.component" :is="slotContent.component" v-bind="slotContent.props" />
            <span v-else>{{ slotContent }}</span>
          </template>
        </component>
      </div>
      
      <!-- Form Actions -->
      <div class="flex items-center justify-between pt-6 border-t border-gray-200">
        <div class="flex items-center space-x-3">
          <!-- Back Button (for multi-step) -->
          <VButton
            v-if="steps.length > 1 && currentStep > 0"
            type="button"
            variant="outline"
            @click="previousStep"
            :disabled="isSubmitting"
          >
            <ChevronLeft class="w-4 h-4 mr-1" />
            Back
          </VButton>
          
          <!-- Custom Actions Slot -->
          <slot name="actions" :form-data="formData" :errors="errors" :is-valid="isValid" />
        </div>
        
        <div class="flex items-center space-x-3">
          <!-- Save Draft Button -->
          <VButton
            v-if="allowDraft"
            type="button"
            variant="outline"
            @click="saveDraft"
            :loading="isDraftSaving"
          >
            Save Draft
          </VButton>
          
          <!-- Reset Button -->
          <VButton
            v-if="showReset"
            type="button"
            variant="outline"
            @click="resetForm"
            :disabled="isSubmitting"
          >
            Reset
          </VButton>
          
          <!-- Next/Submit Button -->
          <VButton
            type="submit"
            :loading="isSubmitting"
            :disabled="!canProceed"
          >
            <span v-if="steps.length > 1 && currentStep < steps.length - 1">
              Next
              <ChevronRight class="w-4 h-4 ml-1" />
            </span>
            <span v-else>
              {{ submitText }}
            </span>
          </VButton>
        </div>
      </div>
    </form>
    
    <!-- Form Summary Modal (for multi-step forms) -->
    <VModal
      v-if="steps.length > 1"
      v-model="showSummary"
      title="Form Summary"
      size="lg"
      :show-footer="true"
    >
      <div class="space-y-6">
        <div v-for="(step, stepIndex) in steps" :key="stepIndex" class="border-b border-gray-200 pb-4 last:border-b-0">
          <h4 class="font-semibold text-gray-900 mb-3">{{ step.title || `Step ${stepIndex + 1}` }}</h4>
          
          <div class="space-y-2">
            <div
              v-for="field in step.fields"
              :key="field.name"
              v-if="formData[field.name] !== undefined && formData[field.name] !== ''"
              class="flex justify-between"
            >
              <span class="text-sm font-medium text-gray-700">{{ field.label }}:</span>
              <span class="text-sm text-gray-900">
                {{ formatFieldValue(field, formData[field.name]) }}
              </span>
            </div>
          </div>
        </div>
      </div>
      
      <template #footer>
        <VButton variant="outline" @click="showSummary = false">
          Edit
        </VButton>
        <VButton @click="submitForm" :loading="isSubmitting">
          {{ submitText }}
        </VButton>
      </template>
    </VModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, reactive, watch, onMounted } from 'vue';
import { 
  VFormField, VInput, VTextarea, VSelect, VCheckbox, VButton, VModal 
} from '@/components/ui';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { cn } from '@/utils/cn';

// Types
interface FieldSlot {
  component?: any;
  props?: Record<string, any>;
  content?: string;
}

interface FormField {
  name: string;
  type: 'text' | 'email' | 'password' | 'number' | 'textarea' | 'select' | 'checkbox' | 'radio' | 'date' | 'file';
  label?: string;
  placeholder?: string;
  required?: boolean;
  disabled?: boolean;
  readonly?: boolean;
  
  // Validation
  validation?: {
    required?: boolean;
    min?: number;
    max?: number;
    minLength?: number;
    maxLength?: number;
    pattern?: RegExp;
    email?: boolean;
    custom?: (value: any) => string | null;
  };
  
  // Field-specific props
  options?: Array<{ label: string; value: any }>;
  multiple?: boolean;
  rows?: number;
  accept?: string;
  
  // Styling and layout
  class?: string;
  size?: 'sm' | 'md' | 'lg';
  
  // Conditional display
  condition?: (formData: Record<string, any>) => boolean;
  
  // Dynamic slots
  slots?: Record<string, FieldSlot>;
  
  // Default value
  defaultValue?: any;
}

interface FormStep {
  title?: string;
  description?: string;
  fields: FormField[];
  layout?: 'vertical' | 'horizontal' | 'grid';
  validation?: (formData: Record<string, any>) => Record<string, string>;
}

interface Props {
  // Form configuration
  title?: string;
  description?: string;
  fields?: FormField[];
  steps?: FormStep[];
  
  // Layout
  layout?: 'vertical' | 'horizontal' | 'grid';
  
  // Features
  showProgress?: boolean;
  allowDraft?: boolean;
  showReset?: boolean;
  showSummary?: boolean;
  
  // Validation
  validateOnChange?: boolean;
  validateOnBlur?: boolean;
  
  // Form behavior
  submitText?: string;
  resetOnSubmit?: boolean;
  
  // Initial data
  initialData?: Record<string, any>;
  
  // Styling
  containerClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
  fields: () => [],
  steps: () => [],
  layout: 'vertical',
  showProgress: true,
  allowDraft: false,
  showReset: true,
  showSummary: true,
  validateOnChange: true,
  validateOnBlur: true,
  submitText: 'Submit',
  resetOnSubmit: false,
  initialData: () => ({})
});

const emit = defineEmits<{
  'submit': [data: Record<string, any>];
  'step-change': [step: number, data: Record<string, any>];
  'field-change': [fieldName: string, value: any, formData: Record<string, any>];
  'validation-error': [errors: Record<string, string>];
  'draft-saved': [data: Record<string, any>];
  'reset': [];
}>();

// State
const formData = reactive<Record<string, any>>({});
const errors = reactive<Record<string, string>>({});
const currentStep = ref(0);
const isSubmitting = ref(false);
const isDraftSaving = ref(false);
const showSummaryModal = ref(false);

// Computed properties
const currentStepData = computed(() => {
  return props.steps[currentStep.value] || { fields: props.fields, layout: props.layout };
});

const isValid = computed(() => {
  if (props.steps.length > 0) {
    // Validate current step only
    return validateStep(currentStep.value).length === 0;
  } else {
    // Validate all fields
    return Object.keys(errors).length === 0;
  }
});

const canProceed = computed(() => {
  if (props.steps.length > 1) {
    return isValid.value;
  }
  return true; // For single forms, allow submission even with errors (they'll be shown)
});

const showSummary = computed({
  get: () => showSummaryModal.value,
  set: (value: boolean) => { showSummaryModal.value = value; }
});

// Methods
const getFieldComponent = (field: FormField): any => {
  const componentMap: Record<string, any> = {
    text: VInput,
    email: VInput,
    password: VInput,
    number: VInput,
    date: VInput,
    file: VInput,
    textarea: VTextarea,
    select: VSelect,
    checkbox: VCheckbox,
    radio: VSelect // Could be a custom radio component
  };
  
  return componentMap[field.type] || VInput;
};

const getFieldProps = (field: FormField): Record<string, any> => {
  const baseProps: Record<string, any> = {
    label: field.label,
    placeholder: field.placeholder,
    required: field.required,
    disabled: field.disabled,
    readonly: field.readonly,
    size: field.size || 'md',
    errorMessage: errors[field.name]
  };
  
  // Type-specific props
  switch (field.type) {
    case 'email':
      baseProps.type = 'email';
      break;
    case 'password':
      baseProps.type = 'password';
      break;
    case 'number':
      baseProps.type = 'number';
      if (field.validation?.min !== undefined) baseProps.min = field.validation.min;
      if (field.validation?.max !== undefined) baseProps.max = field.validation.max;
      break;
    case 'date':
      baseProps.type = 'date';
      break;
    case 'file':
      baseProps.type = 'file';
      if (field.accept) baseProps.accept = field.accept;
      break;
    case 'textarea':
      if (field.rows) baseProps.rows = field.rows;
      if (field.validation?.maxLength) baseProps.maxlength = field.validation.maxLength;
      break;
    case 'select':
      baseProps.items = field.options || [];
      baseProps.multiple = field.multiple;
      break;
    case 'checkbox':
      baseProps.checked = formData[field.name];
      break;
  }
  
  return baseProps;
};

const getFieldContainerClass = (layout?: string): string => {
  const layoutClasses = {
    vertical: 'space-y-6',
    horizontal: 'flex flex-wrap gap-4',
    grid: 'grid grid-cols-1 md:grid-cols-2 gap-4'
  };
  
  return cn(
    layoutClasses[layout as keyof typeof layoutClasses] || layoutClasses.vertical,
    props.containerClass
  );
};

const updateField = (fieldName: string, value: any): void => {
  formData[fieldName] = value;
  
  if (props.validateOnChange) {
    validateField(fieldName);
  }
  
  emit('field-change', fieldName, value, { ...formData });
};

const validateField = (fieldName: string): string | null => {
  const field = getAllFields().find(f => f.name === fieldName);
  if (!field) return null;
  
  const value = formData[fieldName];
  const validation = field.validation;
  
  // Clear previous error
  if (errors[fieldName]) {
    delete errors[fieldName];
  }
  
  if (!validation) return null;
  
  // Required validation
  if (validation.required && (!value || value === '')) {
    errors[fieldName] = `${field.label || fieldName} is required`;
    return errors[fieldName];
  }
  
  // Skip other validations if field is empty and not required
  if (!value || value === '') return null;
  
  // Email validation
  if (validation.email || field.type === 'email') {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(value)) {
      errors[fieldName] = 'Please enter a valid email address';
      return errors[fieldName];
    }
  }
  
  // Pattern validation
  if (validation.pattern && !validation.pattern.test(value)) {
    errors[fieldName] = `${field.label || fieldName} format is invalid`;
    return errors[fieldName];
  }
  
  // Length validation
  if (validation.minLength && value.length < validation.minLength) {
    errors[fieldName] = `${field.label || fieldName} must be at least ${validation.minLength} characters`;
    return errors[fieldName];
  }
  
  if (validation.maxLength && value.length > validation.maxLength) {
    errors[fieldName] = `${field.label || fieldName} must be no more than ${validation.maxLength} characters`;
    return errors[fieldName];
  }
  
  // Number validation
  if (field.type === 'number') {
    const numValue = parseFloat(value);
    if (isNaN(numValue)) {
      errors[fieldName] = `${field.label || fieldName} must be a valid number`;
      return errors[fieldName];
    }
    
    if (validation.min !== undefined && numValue < validation.min) {
      errors[fieldName] = `${field.label || fieldName} must be at least ${validation.min}`;
      return errors[fieldName];
    }
    
    if (validation.max !== undefined && numValue > validation.max) {
      errors[fieldName] = `${field.label || fieldName} must be no more than ${validation.max}`;
      return errors[fieldName];
    }
  }
  
  // Custom validation
  if (validation.custom) {
    const customError = validation.custom(value);
    if (customError) {
      errors[fieldName] = customError;
      return errors[fieldName];
    }
  }
  
  return null;
};

const validateStep = (stepIndex: number): string[] => {
  const step = props.steps[stepIndex];
  if (!step) return [];
  
  const stepErrors: string[] = [];
  
  // Validate individual fields
  step.fields.forEach(field => {
    if (shouldShowField(field)) {
      const error = validateField(field.name);
      if (error) stepErrors.push(error);
    }
  });
  
  // Run step-level validation if provided
  if (step.validation) {
    const stepValidationErrors = step.validation(formData);
    Object.entries(stepValidationErrors).forEach(([field, error]) => {
      errors[field] = error;
      stepErrors.push(error);
    });
  }
  
  return stepErrors;
};

const validateForm = (): boolean => {
  const allFields = getAllFields();
  let hasErrors = false;
  
  // Clear previous errors
  Object.keys(errors).forEach(key => delete errors[key]);
  
  // Validate all visible fields
  allFields.forEach(field => {
    if (shouldShowField(field)) {
      const error = validateField(field.name);
      if (error) hasErrors = true;
    }
  });
  
  // Validate each step if using steps
  if (props.steps.length > 0) {
    props.steps.forEach((step, index) => {
      if (step.validation) {
        const stepValidationErrors = step.validation(formData);
        Object.entries(stepValidationErrors).forEach(([field, error]) => {
          errors[field] = error;
          hasErrors = true;
        });
      }
    });
  }
  
  if (hasErrors) {
    emit('validation-error', { ...errors });
  }
  
  return !hasErrors;
};

const getAllFields = (): FormField[] => {
  if (props.steps.length > 0) {
    return props.steps.flatMap(step => step.fields);
  }
  return props.fields;
};

const shouldShowField = (field: FormField): boolean => {
  if (field.condition) {
    return field.condition(formData);
  }
  return true;
};

const formatFieldValue = (field: FormField, value: any): string => {
  if (field.type === 'select' && field.options) {
    const option = field.options.find(opt => opt.value === value);
    return option?.label || String(value);
  }
  
  if (field.type === 'checkbox') {
    return value ? 'Yes' : 'No';
  }
  
  return String(value || '');
};

// Navigation methods (for multi-step forms)
const canNavigateToStep = (stepIndex: number): boolean => {
  // Can navigate to previous steps or current step
  return stepIndex <= currentStep.value;
};

const goToStep = (stepIndex: number): void => {
  if (!canNavigateToStep(stepIndex)) return;
  
  currentStep.value = stepIndex;
  emit('step-change', stepIndex, { ...formData });
};

const nextStep = (): void => {
  if (currentStep.value < props.steps.length - 1) {
    // Validate current step
    const stepErrors = validateStep(currentStep.value);
    if (stepErrors.length === 0) {
      currentStep.value++;
      emit('step-change', currentStep.value, { ...formData });
    }
  }
};

const previousStep = (): void => {
  if (currentStep.value > 0) {
    currentStep.value--;
    emit('step-change', currentStep.value, { ...formData });
  }
};

// Form actions
const handleSubmit = (): void => {
  if (props.steps.length > 1 && currentStep.value < props.steps.length - 1) {
    // Multi-step form: go to next step
    nextStep();
  } else {
    // Single form or last step: show summary or submit
    if (props.steps.length > 1 && props.showSummary) {
      showSummaryModal.value = true;
    } else {
      submitForm();
    }
  }
};

const submitForm = async (): Promise<void> => {
  if (!validateForm()) {
    return;
  }
  
  isSubmitting.value = true;
  
  try {
    emit('submit', { ...formData });
    
    if (props.resetOnSubmit) {
      resetForm();
    }
  } finally {
    isSubmitting.value = false;
    if (props.steps.length > 1 && props.showSummary) {
      showSummaryModal.value = false;
    }
  }
};

const resetForm = (): void => {
  // Clear form data
  Object.keys(formData).forEach(key => delete formData[key]);
  
  // Clear errors
  Object.keys(errors).forEach(key => delete errors[key]);
  
  // Reset to first step
  currentStep.value = 0;
  
  // Set initial data
  Object.assign(formData, props.initialData);
  
  // Set default values
  getAllFields().forEach(field => {
    if (field.defaultValue !== undefined && formData[field.name] === undefined) {
      formData[field.name] = field.defaultValue;
    }
  });
  
  emit('reset');
};

const saveDraft = async (): Promise<void> => {
  isDraftSaving.value = true;
  
  try {
    emit('draft-saved', { ...formData });
  } finally {
    isDraftSaving.value = false;
  }
};

// Initialization
onMounted(() => {
  // Set initial data
  Object.assign(formData, props.initialData);
  
  // Set default values for fields
  getAllFields().forEach(field => {
    if (field.defaultValue !== undefined && formData[field.name] === undefined) {
      formData[field.name] = field.defaultValue;
    }
  });
});

// Watch for changes in initial data
watch(() => props.initialData, (newData) => {
  Object.assign(formData, newData);
}, { deep: true });
</script>