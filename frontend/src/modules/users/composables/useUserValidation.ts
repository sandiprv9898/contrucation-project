import { computed, ref, readonly } from 'vue';
import type { CreateUserRequest, UpdateUserRequest } from '../types/users.types';
import type { UserRole } from '@/modules/auth/types/auth.types';

export interface ValidationRule {
  required?: boolean;
  minLength?: number;
  maxLength?: number;
  pattern?: RegExp;
  custom?: (value: any) => string | null;
}

export interface ValidationRules {
  [key: string]: ValidationRule[];
}

export interface ValidationErrors {
  [key: string]: string[];
}

export function useUserValidation() {
  // ==================== STATE ====================
  const errors = ref<ValidationErrors>({});
  
  // ==================== VALIDATION RULES ====================
  const createUserRules: ValidationRules = {
    name: [
      { required: true },
      { minLength: 2 },
      { maxLength: 100 }
    ],
    email: [
      { required: true },
      { 
        pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, 
        custom: () => 'Please enter a valid email address' 
      }
    ],
    role: [
      { required: true },
      { 
        custom: (value: UserRole) => {
          const validRoles: UserRole[] = ['admin', 'project_manager', 'supervisor', 'field_worker'];
          return validRoles.includes(value) ? null : 'Please select a valid role';
        }
      }
    ],
    password: [
      { required: true },
      { minLength: 8 },
      { 
        pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/,
        custom: () => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character'
      }
    ],
    password_confirmation: [
      { required: true },
      { 
        custom: (value: string, data: any) => {
          return value === data.password ? null : 'Password confirmation must match password';
        }
      }
    ],
    phone: [
      { 
        pattern: /^\+?[\d\s\-\(\)]{10,}$/,
        custom: () => 'Please enter a valid phone number'
      }
    ],
    department: [
      { maxLength: 100 }
    ]
  };

  const updateUserRules: ValidationRules = {
    name: [
      { minLength: 2 },
      { maxLength: 100 }
    ],
    email: [
      { 
        pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, 
        custom: () => 'Please enter a valid email address' 
      }
    ],
    role: [
      { 
        custom: (value: UserRole) => {
          if (!value) return null; // Optional for updates
          const validRoles: UserRole[] = ['admin', 'project_manager', 'supervisor', 'field_worker'];
          return validRoles.includes(value) ? null : 'Please select a valid role';
        }
      }
    ],
    phone: [
      { 
        pattern: /^\+?[\d\s\-\(\)]{10,}$/,
        custom: () => 'Please enter a valid phone number'
      }
    ],
    department: [
      { maxLength: 100 }
    ],
    bio: [
      { maxLength: 500 }
    ]
  };

  const passwordChangeRules: ValidationRules = {
    current_password: [
      { required: true }
    ],
    password: [
      { required: true },
      { minLength: 8 },
      { 
        pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/,
        custom: () => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character'
      }
    ],
    password_confirmation: [
      { required: true },
      { 
        custom: (value: string, data: any) => {
          return value === data.password ? null : 'Password confirmation must match password';
        }
      }
    ]
  };

  // ==================== COMPUTED ====================
  const hasErrors = computed(() => Object.keys(errors.value).length > 0);
  
  const isValid = computed(() => !hasErrors.value);

  // ==================== METHODS ====================
  /**
   * Validate a single field
   */
  function validateField(
    fieldName: string, 
    value: any, 
    rules: ValidationRule[], 
    data?: any
  ): string[] {
    const fieldErrors: string[] = [];
    
    for (const rule of rules) {
      // Required check
      if (rule.required && (!value || (typeof value === 'string' && value.trim() === ''))) {
        fieldErrors.push(`${getFieldLabel(fieldName)} is required`);
        continue;
      }
      
      // Skip other validations if value is empty and not required
      if (!value || (typeof value === 'string' && value.trim() === '')) {
        continue;
      }
      
      // Min length check
      if (rule.minLength && typeof value === 'string' && value.length < rule.minLength) {
        fieldErrors.push(`${getFieldLabel(fieldName)} must be at least ${rule.minLength} characters long`);
      }
      
      // Max length check
      if (rule.maxLength && typeof value === 'string' && value.length > rule.maxLength) {
        fieldErrors.push(`${getFieldLabel(fieldName)} must not exceed ${rule.maxLength} characters`);
      }
      
      // Pattern check
      if (rule.pattern && typeof value === 'string' && !rule.pattern.test(value)) {
        fieldErrors.push(`${getFieldLabel(fieldName)} format is invalid`);
      }
      
      // Custom validation
      if (rule.custom) {
        const customError = rule.custom(value, data);
        if (customError) {
          fieldErrors.push(customError);
        }
      }
    }
    
    return fieldErrors;
  }
  
  /**
   * Validate entire form data
   */
  function validateForm(data: any, rules: ValidationRules): ValidationErrors {
    const formErrors: ValidationErrors = {};
    
    for (const [fieldName, fieldRules] of Object.entries(rules)) {
      const fieldValue = data[fieldName];
      const fieldErrors = validateField(fieldName, fieldValue, fieldRules, data);
      
      if (fieldErrors.length > 0) {
        formErrors[fieldName] = fieldErrors;
      }
    }
    
    return formErrors;
  }
  
  /**
   * Validate create user form
   */
  function validateCreateUser(data: CreateUserRequest): ValidationErrors {
    const formErrors = validateForm(data, createUserRules);
    errors.value = formErrors;
    return formErrors;
  }
  
  /**
   * Validate update user form
   */
  function validateUpdateUser(data: UpdateUserRequest): ValidationErrors {
    const formErrors = validateForm(data, updateUserRules);
    errors.value = formErrors;
    return formErrors;
  }
  
  /**
   * Validate password change form
   */
  function validatePasswordChange(data: any): ValidationErrors {
    const formErrors = validateForm(data, passwordChangeRules);
    errors.value = formErrors;
    return formErrors;
  }
  
  /**
   * Get field label for error messages
   */
  function getFieldLabel(fieldName: string): string {
    const labels: { [key: string]: string } = {
      name: 'Name',
      email: 'Email',
      role: 'Role',
      password: 'Password',
      password_confirmation: 'Password Confirmation',
      current_password: 'Current Password',
      phone: 'Phone',
      department: 'Department',
      bio: 'Bio',
      company_id: 'Company'
    };
    
    return labels[fieldName] || fieldName;
  }
  
  /**
   * Clear all errors
   */
  function clearErrors(): void {
    errors.value = {};
  }
  
  /**
   * Clear specific field error
   */
  function clearFieldError(fieldName: string): void {
    if (errors.value[fieldName]) {
      delete errors.value[fieldName];
    }
  }
  
  /**
   * Set custom error for field
   */
  function setFieldError(fieldName: string, error: string): void {
    if (!errors.value[fieldName]) {
      errors.value[fieldName] = [];
    }
    errors.value[fieldName].push(error);
  }
  
  /**
   * Get errors for specific field
   */
  function getFieldErrors(fieldName: string): string[] {
    return errors.value[fieldName] || [];
  }
  
  /**
   * Check if specific field has errors
   */
  function hasFieldError(fieldName: string): boolean {
    return !!errors.value[fieldName] && errors.value[fieldName].length > 0;
  }
  
  /**
   * Validate email uniqueness (async)
   */
  async function validateEmailUnique(email: string, excludeUserId?: string): Promise<boolean> {
    // This would typically make an API call to check email uniqueness
    // For now, we'll return true (valid) as a placeholder
    // TODO: Implement actual API call when backend endpoint is available
    return true;
  }
  
  /**
   * Get first error for field (useful for displaying single error)
   */
  function getFirstFieldError(fieldName: string): string | null {
    const fieldErrors = getFieldErrors(fieldName);
    return fieldErrors.length > 0 ? fieldErrors[0] : null;
  }
  
  /**
   * Real-time field validation
   */
  function validateFieldRealTime(
    fieldName: string, 
    value: any, 
    formType: 'create' | 'update' | 'password' = 'create',
    formData?: any
  ): void {
    let rules: ValidationRule[] = [];
    
    switch (formType) {
      case 'create':
        rules = createUserRules[fieldName] || [];
        break;
      case 'update':
        rules = updateUserRules[fieldName] || [];
        break;
      case 'password':
        rules = passwordChangeRules[fieldName] || [];
        break;
    }
    
    const fieldErrors = validateField(fieldName, value, rules, formData);
    
    if (fieldErrors.length > 0) {
      errors.value[fieldName] = fieldErrors;
    } else {
      clearFieldError(fieldName);
    }
  }

  return {
    // State
    errors: readonly(errors),
    hasErrors: readonly(hasErrors),
    isValid: readonly(isValid),
    
    // Validation methods
    validateCreateUser,
    validateUpdateUser,
    validatePasswordChange,
    validateField,
    validateForm,
    validateFieldRealTime,
    validateEmailUnique,
    
    // Error management
    clearErrors,
    clearFieldError,
    setFieldError,
    getFieldErrors,
    getFirstFieldError,
    hasFieldError,
    
    // Utilities
    getFieldLabel
  };
}