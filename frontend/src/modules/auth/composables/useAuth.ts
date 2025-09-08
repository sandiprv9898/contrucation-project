import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'
import { AUTH_ROUTES } from '../constants/auth.constants'
import type { LoginRequest, RegisterRequest, ForgotPasswordRequest } from '../types/auth.types'

export function useAuth() {
  const authStore = useAuthStore()
  const router = useRouter()

  // Computed properties
  const user = computed(() => authStore.currentUser)
  const isAuthenticated = computed(() => authStore.isAuthenticated)
  const isLoading = computed(() => authStore.isLoading)
  const error = computed(() => authStore.error)
  const userRole = computed(() => authStore.userRole)

  // Role checks
  const isAdmin = computed(() => authStore.isAdmin)
  const isProjectManager = computed(() => authStore.isProjectManager)
  const isSupervisor = computed(() => authStore.isSupervisor)
  const isFieldWorker = computed(() => authStore.isFieldWorker)

  // Actions
  const login = async (credentials: LoginRequest): Promise<void> => {
    try {
      await authStore.login(credentials)
      await router.push(AUTH_ROUTES.DASHBOARD)
    } catch (error) {
      console.error('Login failed:', error)
      throw error
    }
  }

  const register = async (userData: RegisterRequest): Promise<void> => {
    try {
      await authStore.register(userData)
      await router.push(AUTH_ROUTES.DASHBOARD)
    } catch (error) {
      console.error('Registration failed:', error)
      throw error
    }
  }

  const logout = async (): Promise<void> => {
    try {
      await authStore.logout()
      await router.push(AUTH_ROUTES.LOGIN)
    } catch (error) {
      console.error('Logout failed:', error)
      // Redirect to login even if logout API call fails
      await router.push(AUTH_ROUTES.LOGIN)
    }
  }

  const forgotPassword = async (data: ForgotPasswordRequest): Promise<void> => {
    try {
      await authStore.forgotPassword(data)
    } catch (error) {
      console.error('Password reset request failed:', error)
      throw error
    }
  }

  const clearError = (): void => {
    authStore.clearError()
  }

  const initAuth = async (): Promise<void> => {
    await authStore.initAuth()
  }

  return {
    // State
    user,
    isAuthenticated,
    isLoading,
    error,
    userRole,
    
    // Role checks
    isAdmin,
    isProjectManager,
    isSupervisor,
    isFieldWorker,
    
    // Actions
    login,
    register,
    logout,
    forgotPassword,
    clearError,
    initAuth,
  }
}