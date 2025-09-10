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
  UserStatistics,
  BulkActionRequest,
  BulkActionResponse,
  ExportResponse,
} from '../types/users.types'

export class UsersApi {
  /**
   * Get paginated list of users with advanced filtering
   */
  static async getUsers(filters?: UserFilters): Promise<UsersListResponse> {
    console.log('🌐 [USERS API] Getting users with filters:', filters);
    
    const response = await apiClient.get<UsersListResponse>(
      API_CONFIG.ENDPOINTS.USERS.LIST,
      filters as Record<string, unknown>
    )
    
    console.log('✅ [USERS API] Users retrieved successfully:', response);
    return response
  }


  /**
   * Get user profile by ID
   */
  static async getUser(userId: string): Promise<UserProfile> {
    console.log('🌐 [USERS API] Getting user by ID:', userId);
    
    const response = await apiClient.get<{ data: UserProfile }>(
      API_CONFIG.ENDPOINTS.USERS.SHOW(userId)
    )
    
    console.log('✅ [USERS API] User retrieved successfully:', response);
    return response.data
  }

  /**
   * Create a new user
   */
  static async createUser(userData: CreateUserRequest): Promise<UserProfile> {
    console.log('🌐 [USERS API] Creating user:', userData);
    
    const response = await apiClient.post<{ data: UserProfile }>(
      API_CONFIG.ENDPOINTS.USERS.LIST,
      userData
    )
    
    console.log('✅ [USERS API] User created successfully:', response);
    return response.data
  }

  /**
   * Update user profile
   */
  static async updateUser(userId: string, userData: UpdateUserRequest): Promise<UserProfile> {
    console.log('🌐 [USERS API] Updating user:', userId, userData);
    
    const response = await apiClient.put<{ data: UserProfile }>(
      API_CONFIG.ENDPOINTS.USERS.UPDATE(userId),
      userData
    )
    
    console.log('✅ [USERS API] User updated successfully:', response);
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
      API_CONFIG.ENDPOINTS.USERS.LIST,
      { role }
    )
    return response.data
  }

  /**
   * Get users in same company
   */
  static async getCompanyUsers(companyId?: string): Promise<UserListItem[]> {
    const params = companyId 
      ? { company_id: companyId }
      : { same_company: true }
    
    const response = await apiClient.get<{ data: UserListItem[] }>(
      API_CONFIG.ENDPOINTS.USERS.LIST,
      params
    )
    return response.data
  }

  /**
   * Get user statistics
   */
  static async getUserStats(): Promise<{
    total: number;
    active: number;
    admins: number;
    newThisMonth: number;
    recentLogins: number;
  }> {
    const response = await apiClient.get<{ 
      data: {
        total: number;
        active: number;
        admins: number;
        newThisMonth: number;
        recentLogins: number;
      }
    }>(`${API_CONFIG.ENDPOINTS.USERS.LIST}/stats`)
    return response.data
  }

  /**
   * Get role distribution statistics
   */
  static async getRoleStats(): Promise<{ [key: string]: number }> {
    const response = await apiClient.get<{ 
      data: { [key: string]: number } 
    }>(`${API_CONFIG.ENDPOINTS.USERS.LIST}/role-stats`)
    return response.data
  }

  /**
   * Get user statistics with advanced filtering
   */
  static async getUserStatistics(filters?: UserFilters): Promise<UserStatistics> {
    console.log('🌐 [USERS API] Getting user statistics with filters:', filters)
    
    const response = await apiClient.get<UserStatistics>(
      `${API_CONFIG.ENDPOINTS.USERS.LIST}/statistics`,
      filters as Record<string, unknown>
    )
    
    console.log('✅ [USERS API] Statistics retrieved:', response)
    return response
  }

  /**
   * Bulk operations on users
   */
  static async bulkAction(request: BulkActionRequest): Promise<BulkActionResponse> {
    console.log('🌐 [USERS API] Performing bulk action:', request)
    const response = await apiClient.post<BulkActionResponse>(
      `${API_CONFIG.ENDPOINTS.USERS.LIST}/bulk`,
      request
    )
    console.log('✅ [USERS API] Bulk action completed:', response)
    return response
  }

  /**
   * Export users data with advanced filtering
   */
  static async exportUsers(format: 'csv' | 'xlsx' = 'csv', filters?: UserFilters): Promise<ExportResponse> {
    console.log('🌐 [USERS API] Exporting users with format:', format, 'filters:', filters)
    
    // Combine format with filters
    const exportParams = {
      format,
      ...filters
    }
    
    const response = await apiClient.get<ExportResponse>(
      `${API_CONFIG.ENDPOINTS.USERS.LIST}/export`,
      exportParams as Record<string, unknown>
    )
    
    console.log('✅ [USERS API] Export completed:', response)
    return response
  }
}