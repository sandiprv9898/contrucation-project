import { apiClient } from '@/modules/shared/api/client'
import { API_CONFIG } from '@/modules/shared/constants/api'
import type {
  UserListItem,
  UserProfile,
  CreateUserRequest,
  UpdateUserRequest,
  UpdateUserPasswordRequest,
  UsersListResponse,
  UserFilters,
} from '../types/users.types'

export class UsersApi {
  /**
   * Get paginated list of users with filters
   */
  static async getUsers(filters?: UserFilters): Promise<UsersListResponse> {
    const params = new URLSearchParams()
    
    if (filters?.search) {
      params.append('search', filters.search)
    }
    if (filters?.role && filters.role !== '') {
      params.append('role', filters.role)
    }
    if (filters?.company_id && filters.company_id !== '') {
      params.append('company_id', filters.company_id)
    }
    if (filters?.verified !== undefined && filters.verified !== '') {
      params.append('verified', filters.verified.toString())
    }
    if (filters?.page) {
      params.append('page', filters.page.toString())
    }
    if (filters?.per_page) {
      params.append('per_page', filters.per_page.toString())
    }

    const response = await apiClient.get<UsersListResponse>(
      `${API_CONFIG.ENDPOINTS.USERS.LIST}?${params.toString()}`
    )
    return response
  }

  /**
   * Get user profile by ID
   */
  static async getUser(userId: string): Promise<UserProfile> {
    const response = await apiClient.get<{ data: UserProfile }>(
      API_CONFIG.ENDPOINTS.USERS.SHOW(userId)
    )
    return response.data
  }

  /**
   * Create a new user
   */
  static async createUser(userData: CreateUserRequest): Promise<UserProfile> {
    const response = await apiClient.post<{ data: UserProfile }>(
      API_CONFIG.ENDPOINTS.USERS.LIST,
      userData
    )
    return response.data
  }

  /**
   * Update user profile
   */
  static async updateUser(userId: string, userData: UpdateUserRequest): Promise<UserProfile> {
    const response = await apiClient.put<{ data: UserProfile }>(
      API_CONFIG.ENDPOINTS.USERS.UPDATE(userId),
      userData
    )
    return response.data
  }

  /**
   * Update user password
   */
  static async updateUserPassword(
    userId: string,
    passwordData: UpdateUserPasswordRequest
  ): Promise<{ message: string }> {
    const response = await apiClient.put<{ data: { message: string } }>(
      `${API_CONFIG.ENDPOINTS.USERS.UPDATE(userId)}/password`,
      passwordData
    )
    return response.data
  }

  /**
   * Delete user (soft delete)
   */
  static async deleteUser(userId: string): Promise<{ message: string }> {
    const response = await apiClient.delete<{ data: { message: string } }>(
      API_CONFIG.ENDPOINTS.USERS.DELETE(userId)
    )
    return response.data
  }

  /**
   * Upload user avatar
   */
  static async uploadAvatar(userId: string, file: File): Promise<{ avatar_url: string }> {
    const formData = new FormData()
    formData.append('avatar', file)

    const response = await apiClient.post<{ data: { avatar_url: string } }>(
      `${API_CONFIG.ENDPOINTS.USERS.UPDATE(userId)}/avatar`,
      formData
    )
    return response.data
  }

  /**
   * Resend email verification
   */
  static async resendEmailVerification(userId: string): Promise<{ message: string }> {
    const response = await apiClient.post<{ data: { message: string } }>(
      `${API_CONFIG.ENDPOINTS.USERS.SHOW(userId)}/resend-verification`
    )
    return response.data
  }

  /**
   * Get current user profile (authenticated user)
   */
  static async getCurrentUser(): Promise<UserProfile> {
    const response = await apiClient.get<{ data: UserProfile }>(API_CONFIG.ENDPOINTS.AUTH.ME)
    return response.data
  }

  /**
   * Update current user profile
   */
  static async updateCurrentUser(userData: UpdateUserRequest): Promise<UserProfile> {
    const response = await apiClient.put<{ data: UserProfile }>(
      `${API_CONFIG.ENDPOINTS.AUTH.ME}/profile`,
      userData
    )
    return response.data
  }

  /**
   * Update current user password
   */
  static async updateCurrentUserPassword(
    passwordData: UpdateUserPasswordRequest
  ): Promise<{ message: string }> {
    const response = await apiClient.put<{ data: { message: string } }>(
      `${API_CONFIG.ENDPOINTS.AUTH.ME}/password`,
      passwordData
    )
    return response.data
  }

  /**
   * Get users by role
   */
  static async getUsersByRole(role: string): Promise<UserListItem[]> {
    const response = await apiClient.get<{ data: UserListItem[] }>(
      `${API_CONFIG.ENDPOINTS.USERS.LIST}?role=${role}`
    )
    return response.data
  }

  /**
   * Get users in same company
   */
  static async getCompanyUsers(companyId?: string): Promise<UserListItem[]> {
    const endpoint = companyId 
      ? `${API_CONFIG.ENDPOINTS.USERS.LIST}?company_id=${companyId}`
      : `${API_CONFIG.ENDPOINTS.USERS.LIST}?same_company=true`
    
    const response = await apiClient.get<{ data: UserListItem[] }>(endpoint)
    return response.data
  }
}