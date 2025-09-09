import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { AuthApi } from '../api/auth.api'
import { TokenManager } from '@/modules/shared/utils/tokenManager'
import type {
  User,
  LoginRequest,
  RegisterRequest,
  ForgotPasswordRequest,
  AuthResponse,
} from '../types/auth.types'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref<User | null>(TokenManager.getUser<User>())
  const token = ref<string | null>(TokenManager.getToken())
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Initialize with mock user for development if no user exists
  if (!user.value && !token.value) {
    const mockUser: User = {
      id: '1',
      name: 'John Administrator',
      email: 'admin@construction.com',
      role: 'admin',
      avatar_url: null,
      company: {
        id: '1',
        name: 'Construction Corp'
      },
      email_verified_at: '2024-01-15T10:00:00Z',
      created_at: '2024-01-01T09:00:00Z',
      updated_at: '2024-01-15T10:00:00Z'
    }
    const mockToken = 'mock-token-for-development'
    
    user.value = mockUser
    token.value = mockToken
    TokenManager.setToken(mockToken)
    TokenManager.setUser(mockUser)
    console.log('Initialized with mock admin user for development')
  }

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const currentUser = computed(() => user.value)
  const userRole = computed(() => user.value?.role || null)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isProjectManager = computed(() => user.value?.role === 'project_manager')
  const isSupervisor = computed(() => user.value?.role === 'supervisor')
  const isFieldWorker = computed(() => user.value?.role === 'field_worker')

  // Private methods
  function setAuthData(authResponse: AuthResponse) {
    user.value = authResponse.user
    token.value = authResponse.token
    TokenManager.setToken(authResponse.token)
    TokenManager.setUser(authResponse.user)
  }

  function clearAuthData() {
    user.value = null
    token.value = null
    TokenManager.clear()
  }

  function setLoading(loading: boolean) {
    isLoading.value = loading
  }

  function setError(errorMessage: string | null) {
    error.value = errorMessage
  }

  // Actions
  async function login(credentials: LoginRequest): Promise<AuthResponse> {
    try {
      setLoading(true)
      setError(null)
      
      const response = await AuthApi.login(credentials)
      setAuthData(response)
      
      return response
    } catch (err: unknown) {
      const errorMessage = (err as any).response?.data?.message || 'Login failed'
      setError(errorMessage)
      throw err
    } finally {
      setLoading(false)
    }
  }

  async function register(userData: RegisterRequest): Promise<AuthResponse> {
    try {
      setLoading(true)
      setError(null)
      
      const response = await AuthApi.register(userData)
      setAuthData(response)
      
      return response
    } catch (err: unknown) {
      const errorMessage = (err as any).response?.data?.message || 'Registration failed'
      setError(errorMessage)
      throw err
    } finally {
      setLoading(false)
    }
  }

  async function logout(): Promise<void> {
    try {
      setLoading(true)
      setError(null)
      
      // Call logout API endpoint
      await AuthApi.logout()
    } catch (err: unknown) {
      const errorMessage = (err as any).response?.data?.message || 'Logout failed'
      setError(errorMessage)
    } finally {
      // Clear auth data regardless of API call success
      clearAuthData()
      setLoading(false)
    }
  }

  async function forgotPassword(data: ForgotPasswordRequest): Promise<void> {
    try {
      setLoading(true)
      setError(null)
      
      await AuthApi.forgotPassword(data)
    } catch (err: unknown) {
      const errorMessage = (err as any).response?.data?.message || 'Password reset request failed'
      setError(errorMessage)
      throw err
    } finally {
      setLoading(false)
    }
  }

  async function getCurrentUser(): Promise<User> {
    try {
      if (!token.value) {
        throw new Error('No authentication token found')
      }
      
      setLoading(true)
      setError(null)
      
      const response = await AuthApi.getCurrentUser()
      user.value = response.user
      TokenManager.setUser(response.user)
      
      return response.user
    } catch (err: unknown) {
      const errorMessage = (err as any).response?.data?.message || 'Failed to get user information'
      setError(errorMessage)
      clearAuthData()
      throw err
    } finally {
      setLoading(false)
    }
  }

  async function initAuth(): Promise<void> {
    if (token.value && !user.value) {
      try {
        await getCurrentUser()
      } catch {
        clearAuthData()
      }
    }
  }

  function clearError(): void {
    setError(null)
  }

  // Export store interface
  return {
    // State
    user,
    token,
    isLoading,
    error,
    
    // Getters
    isAuthenticated,
    currentUser,
    userRole,
    isAdmin,
    isProjectManager,
    isSupervisor,
    isFieldWorker,
    
    // Actions
    login,
    register,
    logout,
    forgotPassword,
    getCurrentUser,
    initAuth,
    clearError,
    clearAuthData,
  }
})