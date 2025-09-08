import { ref, computed, type Ref } from 'vue'
import { AuthValidator, type ValidationResult } from '../utils/validation'

export function useLoginValidation() {
  const errors = ref<Record<string, string>>({})
  
  const validate = (email: string, password: string): boolean => {
    const result = AuthValidator.validateLogin(email, password)
    errors.value = result.errors
    return result.isValid
  }
  
  const clearErrors = () => {
    errors.value = {}
  }
  
  const isFormValid = computed(() => Object.keys(errors.value).length === 0)
  
  return {
    errors: errors as Readonly<Ref<Record<string, string>>>,
    validate,
    clearErrors,
    isFormValid,
  }
}

export function useRegisterValidation() {
  const errors = ref<Record<string, string>>({})
  
  const validate = (
    name: string,
    email: string,
    password: string,
    passwordConfirmation: string,
    agreeToTerms: boolean
  ): boolean => {
    const result = AuthValidator.validateRegister(
      name,
      email,
      password,
      passwordConfirmation,
      agreeToTerms
    )
    errors.value = result.errors
    return result.isValid
  }
  
  const clearErrors = () => {
    errors.value = {}
  }
  
  const isFormValid = computed(() => Object.keys(errors.value).length === 0)
  
  return {
    errors: errors as Readonly<Ref<Record<string, string>>>,
    validate,
    clearErrors,
    isFormValid,
  }
}

export function useForgotPasswordValidation() {
  const errors = ref<Record<string, string>>({})
  
  const validate = (email: string): boolean => {
    const result = AuthValidator.validateForgotPassword(email)
    errors.value = result.errors
    return result.isValid
  }
  
  const clearErrors = () => {
    errors.value = {}
  }
  
  const isFormValid = computed(() => Object.keys(errors.value).length === 0)
  
  return {
    errors: errors as Readonly<Ref<Record<string, string>>>,
    validate,
    clearErrors,
    isFormValid,
  }
}

// General validation composable for field-level validation
export function useFieldValidation() {
  const validateEmail = (email: string) => AuthValidator.validateEmail(email)
  const validatePassword = (password: string) => AuthValidator.validatePassword(password)
  const validateName = (name: string) => AuthValidator.validateName(name)
  const validatePasswordConfirmation = (password: string, confirmation: string) =>
    AuthValidator.validatePasswordConfirmation(password, confirmation)
  
  return {
    validateEmail,
    validatePassword,
    validateName,
    validatePasswordConfirmation,
  }
}