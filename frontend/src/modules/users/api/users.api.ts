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

    try {
      const response = await apiClient.get<UsersListResponse>(
        `${API_CONFIG.ENDPOINTS.USERS.LIST}?${params.toString()}`
      )
      return response
    } catch (error) {
      // Fallback to mock data for development
      console.warn('Users API endpoint not available, using mock data')
      return this.getMockUsers(filters)
    }
  }

  /**
   * Mock users data for development
   */
  static getMockUsers(filters?: UserFilters): UsersListResponse {
    const mockUsers: UserListItem[] = [
      {
        id: '1',
        name: 'John Smith',
        email: 'john.smith@construction.com',
        role: 'admin',
        phone: '+1-555-0101',
        department: 'management',
        avatar_url: null,
        company: {
          id: '1',
          name: 'Construction Corp'
        },
        email_verified_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-01T09:00:00Z',
        updated_at: '2024-01-15T10:00:00Z'
      },
      {
        id: '2',
        name: 'Sarah Johnson',
        email: 'sarah.johnson@construction.com',
        role: 'project_manager',
        phone: '+1-555-0102',
        department: 'management',
        avatar_url: null,
        company: {
          id: '1',
          name: 'Construction Corp'
        },
        email_verified_at: '2024-02-10T14:30:00Z',
        created_at: '2024-01-15T10:00:00Z',
        updated_at: '2024-02-10T14:30:00Z'
      },
      {
        id: '3',
        name: 'Mike Rodriguez',
        email: 'mike.rodriguez@construction.com',
        role: 'supervisor',
        phone: '+1-555-0103',
        department: 'construction',
        avatar_url: null,
        company: {
          id: '1',
          name: 'Construction Corp'
        },
        email_verified_at: '2024-03-05T08:15:00Z',
        created_at: '2024-02-01T11:30:00Z',
        updated_at: '2024-03-05T08:15:00Z'
      },
      {
        id: '4',
        name: 'Emily Davis',
        email: 'emily.davis@construction.com',
        role: 'field_worker',
        phone: '+1-555-0104',
        department: 'construction',
        avatar_url: null,
        company: {
          id: '1',
          name: 'Construction Corp'
        },
        email_verified_at: null,
        created_at: '2024-08-15T13:45:00Z',
        updated_at: '2024-08-15T13:45:00Z'
      },
      {
        id: '5',
        name: 'David Wilson',
        email: 'david.wilson@construction.com',
        role: 'field_worker',
        phone: '+1-555-0105',
        department: 'construction',
        avatar_url: null,
        company: {
          id: '1',
          name: 'Construction Corp'
        },
        email_verified_at: '2024-09-01T16:20:00Z',
        created_at: '2024-09-01T16:20:00Z',
        updated_at: '2024-09-01T16:20:00Z'
      }
    ]

    // Apply search filter
    let filtered = [...mockUsers];
    if (filters?.search) {
      const search = filters.search.toLowerCase();
      filtered = filtered.filter(user => 
        user.name.toLowerCase().includes(search) ||
        user.email.toLowerCase().includes(search) ||
        (user.department && user.department.toLowerCase().includes(search)) ||
        (user.phone && user.phone.toLowerCase().includes(search))
      );
    }

    // Apply role filter
    if (filters?.role && filters.role !== '') {
      filtered = filtered.filter(user => user.role === filters.role);
    }

    // Apply verified filter
    if (filters?.verified !== undefined && filters.verified !== '') {
      const isVerified = filters.verified === 'verified';
      filtered = filtered.filter(user => 
        isVerified ? !!user.email_verified_at : !user.email_verified_at
      );
    }

    const page = filters?.page || 1;
    const per_page = filters?.per_page || 50;
    const startIndex = (page - 1) * per_page;
    const endIndex = startIndex + per_page;
    const paginatedData = filtered.slice(startIndex, endIndex);

    return {
      data: paginatedData,
      meta: {
        current_page: page,
        per_page,
        total: filtered.length,
        last_page: Math.ceil(filtered.length / per_page)
      }
    }
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
    try {
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
    } catch (error) {
      // Fallback to mock data if endpoint doesn't exist
      console.warn('User stats endpoint not available, using mock data')
      return {
        total: 156,
        active: 142,
        admins: 8,
        newThisMonth: 12,
        recentLogins: 45
      }
    }
  }

  /**
   * Get role distribution statistics
   */
  static async getRoleStats(): Promise<{ [key: string]: number }> {
    try {
      const response = await apiClient.get<{ 
        data: { [key: string]: number } 
      }>(`${API_CONFIG.ENDPOINTS.USERS.LIST}/role-stats`)
      return response.data
    } catch (error) {
      // Fallback to mock data if endpoint doesn't exist
      console.warn('Role stats endpoint not available, using mock data')
      return {
        admin: 8,
        project_manager: 24,
        supervisor: 48,
        field_worker: 76
      }
    }
  }

  /**
   * Export users data
   */
  static async exportUsers(format: 'csv' | 'xlsx' = 'csv', filters?: UserFilters): Promise<Blob> {
    const params = new URLSearchParams()
    params.append('format', format)
    
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

    try {
      const response = await apiClient.get(
        `${API_CONFIG.ENDPOINTS.USERS.LIST}/export?${params.toString()}`,
        { responseType: 'blob' }
      )
      return response as unknown as Blob
    } catch (error) {
      console.warn('Export endpoint not available, generating mock export')
      // Generate a simple CSV as fallback
      const csvContent = 'Name,Email,Role,Status\nJohn Doe,john@example.com,Admin,Active\nJane Smith,jane@example.com,User,Active'
      return new Blob([csvContent], { type: 'text/csv' })
    }
  }
}