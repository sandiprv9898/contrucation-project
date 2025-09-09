import type { UserRole } from '@/modules/auth/types/auth.types';

/**
 * User role permissions mapping
 */
export interface RolePermissions {
  canManageUsers: boolean;
  canViewUsers: boolean;
  canEditUser: boolean;
  canDeleteUser: boolean;
  canManageProjects: boolean;
  canViewProjects: boolean;
  canAssignTasks: boolean;
  canViewReports: boolean;
  canManageCompany: boolean;
  canManageFinance: boolean;
  canApproveTimesheet: boolean;
  canManageEquipment: boolean;
  canManageSafety: boolean;
}

/**
 * Role-based permission matrix
 */
export const ROLE_PERMISSIONS: Record<UserRole, RolePermissions> = {
  admin: {
    canManageUsers: true,
    canViewUsers: true,
    canEditUser: true,
    canDeleteUser: true,
    canManageProjects: true,
    canViewProjects: true,
    canAssignTasks: true,
    canViewReports: true,
    canManageCompany: true,
    canManageFinance: true,
    canApproveTimesheet: true,
    canManageEquipment: true,
    canManageSafety: true,
  },
  project_manager: {
    canManageUsers: false,
    canViewUsers: true,
    canEditUser: false,
    canDeleteUser: false,
    canManageProjects: true,
    canViewProjects: true,
    canAssignTasks: true,
    canViewReports: true,
    canManageCompany: false,
    canManageFinance: false,
    canApproveTimesheet: true,
    canManageEquipment: true,
    canManageSafety: true,
  },
  supervisor: {
    canManageUsers: false,
    canViewUsers: true,
    canEditUser: false,
    canDeleteUser: false,
    canManageProjects: false,
    canViewProjects: true,
    canAssignTasks: true,
    canViewReports: true,
    canManageCompany: false,
    canManageFinance: false,
    canApproveTimesheet: false,
    canManageEquipment: false,
    canManageSafety: true,
  },
  field_worker: {
    canManageUsers: false,
    canViewUsers: false,
    canEditUser: false,
    canDeleteUser: false,
    canManageProjects: false,
    canViewProjects: true,
    canAssignTasks: false,
    canViewReports: false,
    canManageCompany: false,
    canManageFinance: false,
    canApproveTimesheet: false,
    canManageEquipment: false,
    canManageSafety: false,
  },
};

/**
 * Role labels for display
 */
export const ROLE_LABELS: Record<UserRole, string> = {
  admin: 'Administrator',
  project_manager: 'Project Manager',
  supervisor: 'Supervisor',
  field_worker: 'Field Worker',
};

/**
 * Role badge variants for UI display
 */
export const ROLE_BADGE_VARIANTS: Record<UserRole, 'default' | 'outline' | 'success' | 'warning' | 'destructive'> = {
  admin: 'destructive',
  project_manager: 'default',
  supervisor: 'warning',
  field_worker: 'success',
};

/**
 * User status types
 */
export enum UserStatus {
  ACTIVE = 'active',
  INACTIVE = 'inactive',
  PENDING = 'pending',
  SUSPENDED = 'suspended',
}

/**
 * User status labels
 */
export const USER_STATUS_LABELS: Record<UserStatus, string> = {
  [UserStatus.ACTIVE]: 'Active',
  [UserStatus.INACTIVE]: 'Inactive',
  [UserStatus.PENDING]: 'Pending Verification',
  [UserStatus.SUSPENDED]: 'Suspended',
};

/**
 * User status badge variants
 */
export const USER_STATUS_VARIANTS: Record<UserStatus, 'default' | 'outline' | 'success' | 'warning' | 'destructive'> = {
  [UserStatus.ACTIVE]: 'success',
  [UserStatus.INACTIVE]: 'default',
  [UserStatus.PENDING]: 'warning',
  [UserStatus.SUSPENDED]: 'destructive',
};

/**
 * Default pagination settings
 */
export const USER_PAGINATION = {
  DEFAULT_PAGE_SIZE: 50,
  PAGE_SIZE_OPTIONS: [10, 25, 50, 100, 250],
  DEFAULT_SORT_BY: 'created_at',
  DEFAULT_SORT_DIRECTION: 'desc' as const,
};

/**
 * User validation rules
 */
export const USER_VALIDATION = {
  NAME_MIN_LENGTH: 2,
  NAME_MAX_LENGTH: 100,
  EMAIL_MAX_LENGTH: 255,
  PHONE_MAX_LENGTH: 20,
  DEPARTMENT_MAX_LENGTH: 100,
  BIO_MAX_LENGTH: 500,
  PASSWORD_MIN_LENGTH: 8,
  PASSWORD_PATTERN: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/,
};

/**
 * User form field configurations
 */
export const USER_FORM_FIELDS = {
  name: {
    label: 'Full Name',
    placeholder: 'Enter full name',
    required: true,
    type: 'text',
  },
  email: {
    label: 'Email Address',
    placeholder: 'Enter email address',
    required: true,
    type: 'email',
  },
  role: {
    label: 'Role',
    placeholder: 'Select a role',
    required: true,
    type: 'select',
  },
  phone: {
    label: 'Phone Number',
    placeholder: 'Enter phone number',
    required: false,
    type: 'tel',
  },
  department: {
    label: 'Department',
    placeholder: 'Enter department',
    required: false,
    type: 'text',
  },
  password: {
    label: 'Password',
    placeholder: 'Enter password',
    required: true,
    type: 'password',
  },
  password_confirmation: {
    label: 'Confirm Password',
    placeholder: 'Confirm password',
    required: true,
    type: 'password',
  },
  bio: {
    label: 'Bio',
    placeholder: 'Tell us about yourself...',
    required: false,
    type: 'textarea',
  },
};

/**
 * User API endpoints
 */
export const USER_API_ENDPOINTS = {
  LIST: '/api/v1/users',
  CREATE: '/api/v1/users',
  GET: (id: string) => `/api/v1/users/${id}`,
  UPDATE: (id: string) => `/api/v1/users/${id}`,
  DELETE: (id: string) => `/api/v1/users/${id}`,
  AVATAR: (id: string) => `/api/v1/users/${id}/avatar`,
  RESEND_VERIFICATION: (id: string) => `/api/v1/users/${id}/resend`,
  CHANGE_ROLE: (id: string) => `/api/v1/users/${id}/role`,
  CURRENT_USER: '/api/v1/auth/me',
  UPDATE_PROFILE: '/api/v1/auth/me/profile',
  CHANGE_PASSWORD: '/api/v1/auth/me/password',
};

/**
 * User error messages
 */
export const USER_ERROR_MESSAGES = {
  FETCH_FAILED: 'Failed to fetch users. Please try again.',
  CREATE_FAILED: 'Failed to create user. Please check your input and try again.',
  UPDATE_FAILED: 'Failed to update user. Please try again.',
  DELETE_FAILED: 'Failed to delete user. Please try again.',
  INVALID_EMAIL: 'Please enter a valid email address.',
  INVALID_PASSWORD: 'Password must contain at least 8 characters with uppercase, lowercase, number, and special character.',
  PASSWORD_MISMATCH: 'Passwords do not match.',
  REQUIRED_FIELD: 'This field is required.',
  NAME_TOO_SHORT: `Name must be at least ${USER_VALIDATION.NAME_MIN_LENGTH} characters.`,
  NAME_TOO_LONG: `Name cannot exceed ${USER_VALIDATION.NAME_MAX_LENGTH} characters.`,
  PHONE_INVALID: 'Please enter a valid phone number.',
  UNAUTHORIZED: 'You do not have permission to perform this action.',
};

/**
 * User success messages
 */
export const USER_SUCCESS_MESSAGES = {
  CREATED: 'User created successfully.',
  UPDATED: 'User updated successfully.',
  DELETED: 'User deleted successfully.',
  VERIFICATION_SENT: 'Verification email sent successfully.',
  ROLE_CHANGED: 'User role changed successfully.',
  PASSWORD_CHANGED: 'Password changed successfully.',
  AVATAR_UPLOADED: 'Avatar uploaded successfully.',
};