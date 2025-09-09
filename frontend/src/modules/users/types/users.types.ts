// Import auth types for consistency
import type { User, Company, UserRole } from '../../auth/types/auth.types'

// Re-export for external consumption
export type { User, Company, UserRole }

// User management specific types
export interface UserListItem {
  id: string
  name: string
  email: string
  role: UserRole
  avatar_url?: string
  company?: {
    id: string
    name: string
  }
  email_verified_at?: string
  created_at: string
  updated_at: string
}

export interface UserProfile {
  id: string
  name: string
  email: string
  role: UserRole
  avatar_url?: string
  company?: Company
  email_verified_at?: string
  phone?: string
  department?: string
  hire_date?: string
  bio?: string
  created_at: string
  updated_at: string
}

export interface CreateUserRequest {
  name: string
  email: string
  role: UserRole
  company_id?: string
  password: string
  password_confirmation: string
  phone?: string
  department?: string
}

export interface UpdateUserRequest {
  name?: string
  email?: string
  role?: UserRole
  phone?: string
  department?: string
  bio?: string
  avatar_url?: string
}

export interface UpdateUserPasswordRequest {
  current_password: string
  password: string
  password_confirmation: string
}

export interface UsersListResponse {
  data: UserListItem[]
  meta: {
    current_page: number
    per_page: number
    total: number
    last_page: number
  }
}

export interface UserFilters {
  role?: UserRole | '' | undefined
  company_id?: string | '' | undefined
  search?: string
  verified?: boolean | '' | undefined
  page?: number
  per_page?: number
}

export interface UserState {
  users: UserListItem[]
  currentUser: UserProfile | null
  isLoading: boolean
  error: string | null
  filters: UserFilters
  pagination: {
    current_page: number
    per_page: number
    total: number
    last_page: number
  }
}

// Role-based permissions
export interface RolePermissions {
  canManageUsers: boolean
  canViewUsers: boolean
  canEditUser: boolean
  canDeleteUser: boolean
  canManageProjects: boolean
  canViewProjects: boolean
  canAssignTasks: boolean
  canViewReports: boolean
  canManageCompany: boolean
}

// Role hierarchy and permissions mapping
export const ROLE_HIERARCHY: Record<UserRole, number> = {
  admin: 4,
  project_manager: 3,
  supervisor: 2,
  field_worker: 1,
} as const

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
  },
} as const

export const ROLE_LABELS: Record<UserRole, string> = {
  admin: 'Administrator',
  project_manager: 'Project Manager',
  supervisor: 'Supervisor',
  field_worker: 'Field Worker',
} as const

export const ROLE_DESCRIPTIONS: Record<UserRole, string> = {
  admin: 'Full system access with user and company management capabilities',
  project_manager: 'Manage projects, assign tasks, and view comprehensive reports',
  supervisor: 'Oversee field operations, assign tasks to workers, and track progress',
  field_worker: 'Execute assigned tasks and update project progress',
} as const