import { ref, reactive, computed, watch, toRef, readonly } from 'vue';

// Validation rule types
export interface ValidationRule {
  validate: (value: any, formData?: Record<string, any>) => string | null;
  message?: string;
  trigger?: 'change' | 'blur' | 'submit';
  debounce?: number;
  async?: boolean;
}

export interface FieldValidationConfig {
  rules: ValidationRule[];
  required?: boolean;
  validateOn?: ('change' | 'blur' | 'submit')[];
  debounce?: number;
  transform?: (value: any) => any;
  dependencies?: string[];
}

export interface ValidationSchema {
  [fieldName: string]: FieldValidationConfig;
}

export interface ValidationError {
  field: string;
  message: string;
  rule: ValidationRule;
}

export interface ValidationState {
  isValid: boolean;
  isValidating: boolean;
  errors: Record<string, string>;
  touched: Set<string>;
  dirty: Set<string>;
  validatedFields: Set<string>;
}

// Built-in validation rules
export const validationRules = {
  required: (message = 'This field is required'): ValidationRule => ({
    validate: (value) => {
      if (value === null || value === undefined || value === '') {
        return message;
      }
      if (Array.isArray(value) && value.length === 0) {
        return message;
      }
      return null;
    },
    message,
    trigger: 'blur'
  }),

  email: (message = 'Please enter a valid email address'): ValidationRule => ({
    validate: (value) => {
      if (!value) return null;
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(value) ? null : message;
    },
    message,
    trigger: 'blur'
  }),

  minLength: (min: number, message?: string): ValidationRule => ({
    validate: (value) => {
      if (!value) return null;
      const msg = message || `Must be at least ${min} characters`;
      return String(value).length >= min ? null : msg;
    },
    message: message || `Must be at least ${min} characters`,
    trigger: 'change',
    debounce: 300
  }),

  maxLength: (max: number, message?: string): ValidationRule => ({
    validate: (value) => {
      if (!value) return null;
      const msg = message || `Must be no more than ${max} characters`;
      return String(value).length <= max ? null : msg;
    },
    message: message || `Must be no more than ${max} characters`,
    trigger: 'change',
    debounce: 300
  }),

  pattern: (regex: RegExp, message = 'Invalid format'): ValidationRule => ({
    validate: (value) => {
      if (!value) return null;
      return regex.test(String(value)) ? null : message;
    },
    message,
    trigger: 'blur'
  }),

  numeric: (message = 'Must be a valid number'): ValidationRule => ({
    validate: (value) => {
      if (!value) return null;
      const num = Number(value);
      return !isNaN(num) && isFinite(num) ? null : message;
    },
    message,
    trigger: 'change'
  }),

  min: (minimum: number, message?: string): ValidationRule => ({
    validate: (value) => {
      if (!value) return null;
      const num = Number(value);
      if (isNaN(num)) return null; // Let numeric rule handle this
      const msg = message || `Must be at least ${minimum}`;
      return num >= minimum ? null : msg;
    },
    message: message || `Must be at least ${minimum}`,
    trigger: 'change'
  }),

  max: (maximum: number, message?: string): ValidationRule => ({
    validate: (value) => {
      if (!value) return null;
      const num = Number(value);
      if (isNaN(num)) return null; // Let numeric rule handle this
      const msg = message || `Must be no more than ${maximum}`;
      return num <= maximum ? null : msg;
    },
    message: message || `Must be no more than ${maximum}`,
    trigger: 'change'
  }),

  url: (message = 'Please enter a valid URL'): ValidationRule => ({
    validate: (value) => {
      if (!value) return null;
      try {
        new URL(value);
        return null;
      } catch {
        return message;
      }
    },
    message,
    trigger: 'blur'
  }),

  phone: (message = 'Please enter a valid phone number'): ValidationRule => ({
    validate: (value) => {
      if (!value) return null;
      const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
      return phoneRegex.test(String(value).replace(/[\s\-\(\)]/g, '')) ? null : message;
    },
    message,
    trigger: 'blur'
  }),

  match: (fieldName: string, message?: string): ValidationRule => ({
    validate: (value, formData) => {
      if (!value || !formData) return null;
      const msg = message || `Must match ${fieldName}`;
      return value === formData[fieldName] ? null : msg;
    },
    message: message || `Must match ${fieldName}`,
    trigger: 'change'
  }),

  custom: (
    validator: (value: any, formData?: Record<string, any>) => string | null,
    message = 'Invalid value',
    options: Partial<ValidationRule> = {}
  ): ValidationRule => ({
    validate: validator,
    message,
    trigger: 'blur',
    ...options
  }),

  async: (
    asyncValidator: (value: any, formData?: Record<string, any>) => Promise<string | null>,
    message = 'Validation failed',
    options: Partial<ValidationRule> = {}
  ): ValidationRule => ({
    validate: asyncValidator as any,
    message,
    trigger: 'blur',
    async: true,
    debounce: 500,
    ...options
  })
};

// Validation composable
export function useValidation(
  formData: Record<string, any>,
  schema: ValidationSchema = {}
) {
  // State
  const validationState = reactive<ValidationState>({
    isValid: true,
    isValidating: false,
    errors: {},
    touched: new Set(),
    dirty: new Set(),
    validatedFields: new Set()
  });

  const pendingValidations = new Map<string, Promise<void>>();
  const debounceTimers = new Map<string, NodeJS.Timeout>();

  // Computed properties
  const isFormValid = computed(() => {
    return Object.keys(validationState.errors).length === 0 && !validationState.isValidating;
  });

  const hasErrors = computed(() => Object.keys(validationState.errors).length > 0);

  const touchedFields = computed(() => Array.from(validationState.touched));

  const dirtyFields = computed(() => Array.from(validationState.dirty));

  const getFieldError = (fieldName: string) => validationState.errors[fieldName] || null;

  const isFieldValid = (fieldName: string) => !validationState.errors[fieldName];

  const isFieldTouched = (fieldName: string) => validationState.touched.has(fieldName);

  const isFieldDirty = (fieldName: string) => validationState.dirty.has(fieldName);

  // Validation methods
  const validateField = async (
    fieldName: string,
    value: any,
    trigger: 'change' | 'blur' | 'submit' = 'change'
  ): Promise<string | null> => {
    const config = schema[fieldName];
    if (!config) return null;

    // Clear existing debounce timer
    const existingTimer = debounceTimers.get(fieldName);
    if (existingTimer) {
      clearTimeout(existingTimer);
      debounceTimers.delete(fieldName);
    }

    // Get debounce delay
    const debounce = config.debounce || 0;
    const shouldValidateOnTrigger = config.validateOn?.includes(trigger) ?? true;

    if (!shouldValidateOnTrigger) {
      return validationState.errors[fieldName] || null;
    }

    // Apply field transformation if provided
    const transformedValue = config.transform ? config.transform(value) : value;

    // Debounced validation
    return new Promise((resolve) => {
      const performValidation = async () => {
        try {
          validationState.isValidating = true;

          // Cancel any pending validation for this field
          const pendingValidation = pendingValidations.get(fieldName);
          if (pendingValidation) {
            // We can't actually cancel the promise, but we won't use its result
            pendingValidations.delete(fieldName);
          }

          const validationPromise = runFieldValidation(fieldName, transformedValue, trigger);
          pendingValidations.set(fieldName, validationPromise);

          const error = await validationPromise;
          
          // Only update state if this is still the current validation
          if (pendingValidations.get(fieldName) === validationPromise) {
            if (error) {
              validationState.errors[fieldName] = error;
            } else {
              delete validationState.errors[fieldName];
            }
            
            validationState.validatedFields.add(fieldName);
            pendingValidations.delete(fieldName);
          }

          resolve(error);
        } catch (err) {
          const errorMessage = err instanceof Error ? err.message : 'Validation error';
          validationState.errors[fieldName] = errorMessage;
          resolve(errorMessage);
        } finally {
          // Only set isValidating to false if no other validations are pending
          if (pendingValidations.size === 0) {
            validationState.isValidating = false;
          }
        }
      };

      if (debounce > 0) {
        const timer = setTimeout(performValidation, debounce);
        debounceTimers.set(fieldName, timer);
      } else {
        performValidation();
      }
    });
  };

  const runFieldValidation = async (
    fieldName: string,
    value: any,
    trigger: 'change' | 'blur' | 'submit'
  ): Promise<string | null> => {
    const config = schema[fieldName];
    if (!config) return null;

    // Check required validation first
    if (config.required && (value === null || value === undefined || value === '')) {
      return 'This field is required';
    }

    // Skip other validations if field is empty and not required
    if (!config.required && (value === null || value === undefined || value === '')) {
      return null;
    }

    // Run validation rules
    for (const rule of config.rules) {
      // Check if rule should run on this trigger
      if (rule.trigger && rule.trigger !== trigger) continue;

      try {
        let result: string | null;
        
        if (rule.async) {
          result = await (rule.validate as any)(value, formData);
        } else {
          result = rule.validate(value, formData);
        }

        if (result) {
          return result;
        }
      } catch (error) {
        return rule.message || 'Validation failed';
      }
    }

    return null;
  };

  const validateForm = async (): Promise<boolean> => {
    const validationPromises: Promise<void>[] = [];

    Object.keys(schema).forEach(fieldName => {
      const promise = validateField(fieldName, formData[fieldName], 'submit').then(() => {});
      validationPromises.push(promise);
    });

    await Promise.all(validationPromises);
    return isFormValid.value;
  };

  const validateFields = async (fieldNames: string[]): Promise<boolean> => {
    const validationPromises = fieldNames.map(fieldName => 
      validateField(fieldName, formData[fieldName], 'submit').then(() => {})
    );

    await Promise.all(validationPromises);
    return fieldNames.every(fieldName => isFieldValid(fieldName));
  };

  // Field state management
  const touchField = (fieldName: string) => {
    validationState.touched.add(fieldName);
  };

  const markFieldDirty = (fieldName: string) => {
    validationState.dirty.add(fieldName);
  };

  const resetField = (fieldName: string) => {
    delete validationState.errors[fieldName];
    validationState.touched.delete(fieldName);
    validationState.dirty.delete(fieldName);
    validationState.validatedFields.delete(fieldName);
    
    // Cancel pending validation
    const existingTimer = debounceTimers.get(fieldName);
    if (existingTimer) {
      clearTimeout(existingTimer);
      debounceTimers.delete(fieldName);
    }
    
    pendingValidations.delete(fieldName);
  };

  const resetForm = () => {
    Object.keys(validationState.errors).forEach(field => delete validationState.errors[field]);
    validationState.touched.clear();
    validationState.dirty.clear();
    validationState.validatedFields.clear();
    validationState.isValidating = false;
    
    // Clear all timers and pending validations
    debounceTimers.forEach(timer => clearTimeout(timer));
    debounceTimers.clear();
    pendingValidations.clear();
  };

  const setFieldError = (fieldName: string, error: string) => {
    validationState.errors[fieldName] = error;
  };

  const clearFieldError = (fieldName: string) => {
    delete validationState.errors[fieldName];
  };

  const setErrors = (errors: Record<string, string>) => {
    Object.keys(validationState.errors).forEach(field => delete validationState.errors[field]);
    Object.assign(validationState.errors, errors);
  };

  // Auto-validation setup
  const setupAutoValidation = (
    fieldName: string,
    valueRef: any,
    options: {
      validateOnChange?: boolean;
      validateOnBlur?: boolean;
      immediate?: boolean;
    } = {}
  ) => {
    const { validateOnChange = true, validateOnBlur = true, immediate = false } = options;

    // Watch for value changes
    if (validateOnChange) {
      watch(
        () => valueRef.value,
        (newValue, oldValue) => {
          if (newValue !== oldValue) {
            markFieldDirty(fieldName);
            if (validationState.touched.has(fieldName) || immediate) {
              validateField(fieldName, newValue, 'change');
            }
          }
        },
        { immediate }
      );
    }

    // Return blur handler
    const onBlur = () => {
      touchField(fieldName);
      if (validateOnBlur) {
        validateField(fieldName, valueRef.value, 'blur');
      }
    };

    return { onBlur };
  };

  // Dependency validation
  const validateDependentFields = async (changedField: string) => {
    const dependentFields = Object.entries(schema)
      .filter(([_, config]) => config.dependencies?.includes(changedField))
      .map(([fieldName]) => fieldName);

    if (dependentFields.length > 0) {
      await validateFields(dependentFields);
    }
  };

  // Watch for form data changes to trigger dependent field validation
  watch(
    () => formData,
    (newData, oldData) => {
      Object.keys(newData).forEach(fieldName => {
        if (newData[fieldName] !== oldData?.[fieldName]) {
          validateDependentFields(fieldName);
        }
      });
    },
    { deep: true }
  );

  return {
    // State
    validationState: readonly(validationState),
    isFormValid,
    hasErrors,
    
    // Field queries
    getFieldError,
    isFieldValid,
    isFieldTouched,
    isFieldDirty,
    touchedFields,
    dirtyFields,
    
    // Validation methods
    validateField,
    validateForm,
    validateFields,
    
    // State management
    touchField,
    markFieldDirty,
    resetField,
    resetForm,
    setFieldError,
    clearFieldError,
    setErrors,
    
    // Auto-validation
    setupAutoValidation,
    
    // Schema management
    addFieldValidation: (fieldName: string, config: FieldValidationConfig) => {
      schema[fieldName] = config;
    },
    removeFieldValidation: (fieldName: string) => {
      delete schema[fieldName];
      resetField(fieldName);
    },
    updateFieldValidation: (fieldName: string, config: Partial<FieldValidationConfig>) => {
      if (schema[fieldName]) {
        Object.assign(schema[fieldName], config);
      }
    }
  };
}

// Helper functions
export function createValidationSchema(config: Record<string, FieldValidationConfig>): ValidationSchema {
  return config;
}

export function combineValidationRules(...rules: ValidationRule[]): ValidationRule[] {
  return rules;
}

// Common validation patterns
export const commonValidations = {
  email: () => combineValidationRules(
    validationRules.required(),
    validationRules.email()
  ),
  
  password: (minLength = 8) => combineValidationRules(
    validationRules.required(),
    validationRules.minLength(minLength),
    validationRules.pattern(
      /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/,
      'Password must contain at least one lowercase letter, one uppercase letter, and one number'
    )
  ),
  
  confirmPassword: (passwordField = 'password') => combineValidationRules(
    validationRules.required('Please confirm your password'),
    validationRules.match(passwordField, 'Passwords do not match')
  ),
  
  name: () => combineValidationRules(
    validationRules.required(),
    validationRules.minLength(2),
    validationRules.pattern(/^[a-zA-Z\s]+$/, 'Name can only contain letters and spaces')
  ),
  
  phoneNumber: () => combineValidationRules(
    validationRules.required(),
    validationRules.phone()
  ),
  
  website: () => combineValidationRules(
    validationRules.url()
  )
};

// Integration helpers for Vue components
export function useFieldValidation(
  fieldName: string,
  formData: Record<string, any>,
  validation: ReturnType<typeof useValidation>,
  fieldConfig?: FieldValidationConfig
) {
  const fieldRef = toRef(formData, fieldName);
  
  if (fieldConfig) {
    validation.addFieldValidation(fieldName, fieldConfig);
  }
  
  const { onBlur } = validation.setupAutoValidation(fieldName, fieldRef);
  
  return {
    error: computed(() => validation.getFieldError(fieldName)),
    isValid: computed(() => validation.isFieldValid(fieldName)),
    isTouched: computed(() => validation.isFieldTouched(fieldName)),
    isDirty: computed(() => validation.isFieldDirty(fieldName)),
    onBlur,
    validate: (trigger: 'change' | 'blur' | 'submit' = 'change') => 
      validation.validateField(fieldName, fieldRef.value, trigger)
  };
}