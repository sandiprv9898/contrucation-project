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
      console.log('🌐 [USERS API] Attempting real API call to:', `${API_CONFIG.ENDPOINTS.USERS.LIST}?${params.toString()}`);
      const response = await apiClient.get<UsersListResponse>(
        `${API_CONFIG.ENDPOINTS.USERS.LIST}?${params.toString()}`
      )
      console.log('✅ [USERS API] Real API response received:', response);
      return response
    } catch (error) {
      // Fallback to mock data for development
      console.warn('⚠️ [USERS API] Real API failed, falling back to mock data. Error:', error);
      const mockResponse = this.getMockUsers(filters);
      console.log('📋 [USERS API] Mock response generated:', mockResponse);
      console.log('📋 [USERS API] Mock data array length:', mockResponse.data?.length);
      return mockResponse;
    }
  }

  /**
   * Mock users data for development
   */
  static getMockUsers(filters?: UserFilters): UsersListResponse {
    const mockUsers: UserListItem[] = [
      {
        id: '1',
        name: 'Susan Lewis',
        email: 'susan.worker@construction.com',
        role: 'field_worker',
        phone: '+1-555-0101',
        department: 'construction',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:08Z',
        created_at: '2023-09-04T08:56:08Z',
        updated_at: '2023-09-04T08:56:08Z'
      },
      {
        id: '2',
        name: 'Kevin Walker',
        email: 'kevin.worker@construction.com',
        role: 'field_worker',
        phone: '+1-555-0102',
        department: 'construction',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:08Z',
        created_at: '2023-09-04T08:56:08Z',
        updated_at: '2023-09-04T08:56:08Z'
      },
      {
        id: '3',
        name: 'Daniel Garcia',
        email: 'daniel.worker@construction.com',
        role: 'field_worker',
        phone: '+1-555-0103',
        department: 'construction',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:07Z',
        created_at: '2023-09-04T08:56:07Z',
        updated_at: '2023-09-04T08:56:07Z'
      },
      {
        id: '4',
        name: 'Linda Rodriguez',
        email: 'linda.worker@construction.com',
        role: 'field_worker',
        phone: '+1-555-0104',
        department: 'construction',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:07Z',
        created_at: '2023-09-04T08:56:07Z',
        updated_at: '2023-09-04T08:56:07Z'
      },
      {
        id: '5',
        name: 'Paul Thompson',
        email: 'paul.worker@construction.com',
        role: 'field_worker',
        phone: '+1-555-0105',
        department: 'construction',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:07Z',
        created_at: '2023-09-04T08:56:07Z',
        updated_at: '2023-09-04T08:56:07Z'
      },
      {
        id: '6',
        name: 'Nancy Harris',
        email: 'nancy.worker@construction.com',
        role: 'field_worker',
        phone: '+1-555-0106',
        department: 'construction',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:07Z',
        created_at: '2023-09-04T08:56:07Z',
        updated_at: '2023-09-04T08:56:07Z'
      },
      {
        id: '7',
        name: 'Mark Clark',
        email: 'mark.worker@construction.com',
        role: 'field_worker',
        phone: '+1-555-0107',
        department: 'construction',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:07Z',
        created_at: '2023-09-04T08:56:07Z',
        updated_at: '2023-09-04T08:56:07Z'
      },
      {
        id: '8',
        name: 'Patricia Martinez',
        email: 'patricia.supervisor@construction.com',
        role: 'supervisor',
        phone: '+1-555-0108',
        department: 'construction',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:06Z',
        created_at: '2023-09-04T08:56:06Z',
        updated_at: '2023-09-04T08:56:06Z'
      },
      {
        id: '9',
        name: 'Tom Smith',
        email: 'tom.worker@construction.com',
        role: 'field_worker',
        phone: '+1-555-0109',
        department: 'construction',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:06Z',
        created_at: '2023-09-04T08:56:06Z',
        updated_at: '2023-09-04T08:56:06Z'
      },
      {
        id: '10',
        name: 'Mary Johnson',
        email: 'mary.worker@construction.com',
        role: 'field_worker',
        phone: '+1-555-0110',
        department: 'construction',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:06Z',
        created_at: '2023-09-04T08:56:06Z',
        updated_at: '2023-09-04T08:56:06Z'
      },
      {
        id: '11',
        name: 'Christopher Lee',
        email: 'chris.worker@construction.com',
        role: 'field_worker',
        phone: '+1-555-0111',
        department: 'construction',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:06Z',
        created_at: '2023-09-04T08:56:06Z',
        updated_at: '2023-09-04T08:56:06Z'
      },
      {
        id: '12',
        name: 'Jennifer White',
        email: 'jennifer.worker@construction.com',
        role: 'field_worker',
        phone: '+1-555-0112',
        department: 'construction',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:06Z',
        created_at: '2023-09-04T08:56:06Z',
        updated_at: '2023-09-04T08:56:06Z'
      },
      {
        id: '13',
        name: 'Emily Smith',
        email: 'emily.smith@construction.com',
        role: 'project_manager',
        phone: '+1-555-0113',
        department: 'management',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:06Z',
        created_at: '2023-09-04T08:56:06Z',
        updated_at: '2023-09-04T08:56:06Z'
      },
      {
        id: '14',
        name: 'Robert Wilson',
        email: 'robert.pm@construction.com',
        role: 'project_manager',
        phone: '+1-555-0114',
        department: 'management',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:06Z',
        created_at: '2023-09-04T08:56:06Z',
        updated_at: '2023-09-04T08:56:06Z'
      },
      {
        id: '15',
        name: 'David Brown',
        email: 'david.supervisor@construction.com',
        role: 'supervisor',
        phone: '+1-555-0115',
        department: 'construction',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:06Z',
        created_at: '2023-09-04T08:56:06Z',
        updated_at: '2023-09-04T08:56:06Z'
      },
      {
        id: '16',
        name: 'Lisa Anderson',
        email: 'lisa.supervisor@construction.com',
        role: 'supervisor',
        phone: '+1-555-0116',
        department: 'construction',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:06Z',
        created_at: '2023-09-04T08:56:06Z',
        updated_at: '2023-09-04T08:56:06Z'
      },
      {
        id: '17',
        name: 'James Miller',
        email: 'james.supervisor@construction.com',
        role: 'supervisor',
        phone: '+1-555-0117',
        department: 'construction',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:05Z',
        created_at: '2023-09-04T08:56:05Z',
        updated_at: '2023-09-04T08:56:05Z'
      },
      {
        id: '18',
        name: 'John Administrator',
        email: 'admin@construction.com',
        role: 'admin',
        phone: '+1-555-0118',
        department: 'management',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:04Z',
        created_at: '2023-09-04T08:56:04Z',
        updated_at: '2023-09-04T08:56:04Z'
      },
      {
        id: '19',
        name: 'Sarah Admin',
        email: 'sarah.admin@construction.com',
        role: 'admin',
        phone: '+1-555-0119',
        department: 'management',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:04Z',
        created_at: '2023-09-04T08:56:04Z',
        updated_at: '2023-09-04T08:56:04Z'
      },
      {
        id: '20',
        name: 'Michael Johnson',
        email: 'michael.pm@construction.com',
        role: 'project_manager',
        phone: '+1-555-0120',
        department: 'management',
        avatar_url: null,
        company: { id: '1', name: 'Construction Corp' },
        email_verified_at: '2023-09-04T08:56:04Z',
        created_at: '2023-09-04T08:56:04Z',
        updated_at: '2023-09-04T08:56:04Z'
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
    try {
      console.log('🌐 [USERS API] Attempting to get user by ID:', userId);
      const response = await apiClient.get<{ data: UserProfile }>(
        API_CONFIG.ENDPOINTS.USERS.SHOW(userId)
      )
      console.log('✅ [USERS API] User retrieved successfully:', response);
      return response.data
    } catch (error) {
      console.warn('⚠️ [USERS API] Get user failed, using mock implementation. Error:', error);
      
      // Mock implementation for development - find user from our mock users list
      const mockUsers = this.getMockUsers().data;
      const foundUser = mockUsers.find(user => user.id === userId);
      
      if (!foundUser) {
        throw new Error(`User with ID ${userId} not found`);
      }
      
      // Convert UserListItem to UserProfile format
      const mockUserProfile: UserProfile = {
        id: foundUser.id,
        name: foundUser.name,
        email: foundUser.email,
        role: foundUser.role,
        phone: foundUser.phone,
        department: foundUser.department,
        bio: null, // Not in list format
        avatar_url: foundUser.avatar_url,
        company: foundUser.company,
        email_verified_at: foundUser.email_verified_at,
        created_at: foundUser.created_at,
        updated_at: foundUser.updated_at,
        last_login_at: null, // Not in list format
        settings: {
          notifications: {
            email: true,
            push: true,
            sms: false
          },
          privacy: {
            profile_visibility: 'company',
            show_email: false,
            show_phone: false
          }
        },
        permissions: [],
        stats: {
          projects_count: Math.floor(Math.random() * 10),
          tasks_completed: Math.floor(Math.random() * 50),
          hours_logged: Math.floor(Math.random() * 200)
        }
      };
      
      console.log('📋 [USERS API] Mock user profile retrieved:', mockUserProfile);
      
      // Simulate API delay
      await new Promise(resolve => setTimeout(resolve, 500));
      
      return mockUserProfile;
    }
  }

  /**
   * Create a new user
   */
  static async createUser(userData: CreateUserRequest): Promise<UserProfile> {
    try {
      console.log('🌐 [USERS API] Attempting to create user:', userData);
      const response = await apiClient.post<{ data: UserProfile }>(
        API_CONFIG.ENDPOINTS.USERS.LIST,
        userData
      )
      console.log('✅ [USERS API] User created successfully:', response);
      return response.data
    } catch (error) {
      console.warn('⚠️ [USERS API] Create user failed, using mock implementation. Error:', error);
      
      // Mock implementation for development
      const newUser: UserProfile = {
        id: Math.random().toString(36).substr(2, 9),
        name: userData.name,
        email: userData.email,
        role: userData.role,
        phone: userData.phone || null,
        department: userData.department || null,
        bio: userData.bio || null,
        avatar_url: null,
        company: {
          id: '1',
          name: 'Construction Corp'
        },
        email_verified_at: null, // New users start unverified
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
        last_login_at: null,
        settings: {
          notifications: {
            email: true,
            push: true,
            sms: false
          },
          privacy: {
            profile_visibility: 'company',
            show_email: false,
            show_phone: false
          }
        },
        permissions: [],
        stats: {
          projects_count: 0,
          tasks_completed: 0,
          hours_logged: 0
        }
      };
      
      console.log('📋 [USERS API] Mock user created:', newUser);
      
      // Simulate API delay
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      return newUser;
    }
  }

  /**
   * Update user profile
   */
  static async updateUser(userId: string, userData: UpdateUserRequest): Promise<UserProfile> {
    try {
      console.log('🌐 [USERS API] Attempting to update user:', userId, userData);
      const response = await apiClient.put<{ data: UserProfile }>(
        API_CONFIG.ENDPOINTS.USERS.UPDATE(userId),
        userData
      )
      console.log('✅ [USERS API] User updated successfully:', response);
      return response.data
    } catch (error) {
      console.warn('⚠️ [USERS API] Update user failed, using mock implementation. Error:', error);
      
      // Mock implementation for development
      const updatedUser: UserProfile = {
        id: userId,
        name: userData.name,
        email: userData.email,
        role: userData.role,
        phone: userData.phone || null,
        department: userData.department || null,
        bio: userData.bio || null,
        avatar_url: null,
        company: {
          id: '1',
          name: 'Construction Corp'
        },
        email_verified_at: '2024-09-09T12:00:00Z', // Mock verified status
        created_at: '2024-08-15T10:00:00Z', // Mock original creation date
        updated_at: new Date().toISOString(), // Current timestamp for update
        last_login_at: '2024-09-08T14:30:00Z', // Mock last login
        settings: {
          notifications: {
            email: true,
            push: true,
            sms: false
          },
          privacy: {
            profile_visibility: 'company',
            show_email: false,
            show_phone: false
          }
        },
        permissions: [],
        stats: {
          projects_count: 3,
          tasks_completed: 12,
          hours_logged: 120
        }
      };
      
      console.log('📋 [USERS API] Mock user updated:', updatedUser);
      
      // Simulate API delay
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      return updatedUser;
    }
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