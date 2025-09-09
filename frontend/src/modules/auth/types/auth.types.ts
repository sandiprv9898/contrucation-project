// User role definition (defined first to avoid circular dependencies)
export type UserRole = 'admin' | 'project_manager' | 'supervisor' | 'field_worker'

export interface Company {
  id: string
  name: string
  industry: string
}

// Authentication request types
export interface LoginRequest {
  email: string
  password: string
  remember?: boolean
}

export interface RegisterRequest {
  name: string
  email: string
  password: string
  password_confirmation: string
  role?: UserRole
  company_id?: string
}

export interface ForgotPasswordRequest {
  email: string
}

// Authentication response types
export interface AuthResponse {
  message: string
  user: User
  token: string
}

export interface MessageResponse {
  message: string
}

// User types
export interface User {
  id: string
  name: string
  email: string
  role: UserRole
  avatar_url?: string
  email_verified_at?: string
  company?: Company
  created_at: string
  updated_at: string
}


// State types
export interface AuthState {
  user: User | null
  token: string | null
  isAuthenticated: boolean
  isLoading: boolean
  error: string | null
}

// API error types
export interface ApiError {
  message: string
  error?: string
  errors?: Record<string, string[]>
}