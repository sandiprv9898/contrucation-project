export const AUTH_ROUTES = {
  LOGIN: '/auth/login',
  REGISTER: '/auth/register',
  FORGOT_PASSWORD: '/auth/forgot-password',
  DASHBOARD: '/dashboard',
} as const

export const USER_ROLES = {
  ADMIN: 'admin',
  PROJECT_MANAGER: 'project_manager',
  SUPERVISOR: 'supervisor',
  FIELD_WORKER: 'field_worker',
} as const

export const ROLE_LABELS = {
  [USER_ROLES.ADMIN]: 'Administrator',
  [USER_ROLES.PROJECT_MANAGER]: 'Project Manager',
  [USER_ROLES.SUPERVISOR]: 'Supervisor',
  [USER_ROLES.FIELD_WORKER]: 'Field Worker',
} as const

export const AUTH_ERRORS = {
  INVALID_CREDENTIALS: 'Invalid credentials provided',
  USER_NOT_FOUND: 'User not found',
  EMAIL_ALREADY_EXISTS: 'Email address already exists',
  TOKEN_EXPIRED: 'Session has expired',
  NETWORK_ERROR: 'Network connection error',
  UNKNOWN_ERROR: 'An unexpected error occurred',
} as const

export const VALIDATION_RULES = {
  EMAIL: {
    REQUIRED: 'Email address is required',
    INVALID: 'Please enter a valid email address',
  },
  PASSWORD: {
    REQUIRED: 'Password is required',
    MIN_LENGTH: 'Password must be at least 8 characters',
    PATTERN: 'Password must contain uppercase, lowercase, and number',
  },
  NAME: {
    REQUIRED: 'Name is required',
    MIN_LENGTH: 'Name must be at least 2 characters',
  },
  PASSWORD_CONFIRMATION: {
    REQUIRED: 'Please confirm your password',
    MISMATCH: 'Passwords do not match',
  },
  TERMS: {
    REQUIRED: 'You must agree to the terms and privacy policy',
  },
} as const