import { VALIDATION_RULES } from '../constants/auth.constants'

export interface ValidationResult {
  isValid: boolean
  errors: Record<string, string>
}

export class AuthValidator {
  static validateEmail(email: string): { isValid: boolean; error?: string } {
    if (!email) {
      return { isValid: false, error: VALIDATION_RULES.EMAIL.REQUIRED }
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!emailRegex.test(email)) {
      return { isValid: false, error: VALIDATION_RULES.EMAIL.INVALID }
    }
    
    return { isValid: true }
  }

  static validatePassword(password: string): { isValid: boolean; error?: string } {
    if (!password) {
      return { isValid: false, error: VALIDATION_RULES.PASSWORD.REQUIRED }
    }
    
    if (password.length < 8) {
      return { isValid: false, error: VALIDATION_RULES.PASSWORD.MIN_LENGTH }
    }
    
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/
    if (!passwordRegex.test(password)) {
      return { isValid: false, error: VALIDATION_RULES.PASSWORD.PATTERN }
    }
    
    return { isValid: true }
  }

  static validateName(name: string): { isValid: boolean; error?: string } {
    if (!name) {
      return { isValid: false, error: VALIDATION_RULES.NAME.REQUIRED }
    }
    
    if (name.length < 2) {
      return { isValid: false, error: VALIDATION_RULES.NAME.MIN_LENGTH }
    }
    
    return { isValid: true }
  }

  static validatePasswordConfirmation(
    password: string,
    confirmation: string
  ): { isValid: boolean; error?: string } {
    if (!confirmation) {
      return { isValid: false, error: VALIDATION_RULES.PASSWORD_CONFIRMATION.REQUIRED }
    }
    
    if (password !== confirmation) {
      return { isValid: false, error: VALIDATION_RULES.PASSWORD_CONFIRMATION.MISMATCH }
    }
    
    return { isValid: true }
  }

  static validateLogin(email: string, password: string): ValidationResult {
    const errors: Record<string, string> = {}
    
    const emailValidation = this.validateEmail(email)
    if (!emailValidation.isValid) {
      errors.email = emailValidation.error!
    }
    
    if (!password) {
      errors.password = VALIDATION_RULES.PASSWORD.REQUIRED
    }
    
    return {
      isValid: Object.keys(errors).length === 0,
      errors,
    }
  }

  static validateRegister(
    name: string,
    email: string,
    password: string,
    passwordConfirmation: string,
    agreeToTerms: boolean
  ): ValidationResult {
    const errors: Record<string, string> = {}
    
    const nameValidation = this.validateName(name)
    if (!nameValidation.isValid) {
      errors.name = nameValidation.error!
    }
    
    const emailValidation = this.validateEmail(email)
    if (!emailValidation.isValid) {
      errors.email = emailValidation.error!
    }
    
    const passwordValidation = this.validatePassword(password)
    if (!passwordValidation.isValid) {
      errors.password = passwordValidation.error!
    }
    
    const confirmationValidation = this.validatePasswordConfirmation(password, passwordConfirmation)
    if (!confirmationValidation.isValid) {
      errors.password_confirmation = confirmationValidation.error!
    }
    
    if (!agreeToTerms) {
      errors.terms = VALIDATION_RULES.TERMS.REQUIRED
    }
    
    return {
      isValid: Object.keys(errors).length === 0,
      errors,
    }
  }

  static validateForgotPassword(email: string): ValidationResult {
    const errors: Record<string, string> = {}
    
    const emailValidation = this.validateEmail(email)
    if (!emailValidation.isValid) {
      errors.email = emailValidation.error!
    }
    
    return {
      isValid: Object.keys(errors).length === 0,
      errors,
    }
  }
}