import { defineStore } from 'pinia';
import { ref, computed, readonly } from 'vue';
import { UsersApi } from '../api/users.api';
import type { 
  UserListItem, 
  UserProfile, 
  UserFilters, 
  CreateUserRequest,
  UpdateUserRequest,
  UsersListResponse 
} from '../types/users.types';
import type { UserRole } from '@/modules/auth/types/auth.types';

export const useUsersStore = defineStore('users', () => {
  // ==================== STATE ====================
  const users = ref<UserListItem[]>([]);
  const currentUser = ref<UserProfile | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);
  const filters = ref<UserFilters>({
    search: '',
    role: '',
    company_id: '',
    verified: '',
    page: 1,
    per_page: 50
  });
  const pagination = ref({
    current_page: 1,
    per_page: 50,
    total: 0,
    last_page: 0
  });

  // ==================== GETTERS ====================
  const usersByRole = computed(() => {
    const grouped = new Map<UserRole, UserListItem[]>();
    
    users.value.forEach(user => {
      const role = user.role;
      if (!grouped.has(role)) {
        grouped.set(role, []);
      }
      grouped.get(role)!.push(user);
    });
    
    return grouped;
  });

  const activeUsers = computed(() => 
    users.value.filter(u => u.email_verified_at)
  );

  const verifiedUsers = computed(() => 
    users.value.filter(u => u.email_verified_at)
  );

  const statistics = computed(() => {
    const now = new Date();
    const currentMonth = now.getMonth();
    const currentYear = now.getFullYear();
    
    const thisMonth = users.value.filter(user => {
      const createdAt = new Date(user.created_at);
      return createdAt.getMonth() === currentMonth && 
             createdAt.getFullYear() === currentYear;
    }).length;

    return {
      total: users.value.length,
      verified: verifiedUsers.value.length,
      active: activeUsers.value.length,
      thisMonth,
      admins: users.value.filter(u => u.role === 'admin').length,
      project_managers: users.value.filter(u => u.role === 'project_manager').length,
      supervisors: users.value.filter(u => u.role === 'supervisor').length,
      field_workers: users.value.filter(u => u.role === 'field_worker').length
    };
  });

  const filteredUsers = computed(() => {
    let result = [...users.value];
    
    // Apply search filter
    if (filters.value.search) {
      const search = filters.value.search.toLowerCase();
      result = result.filter(user => 
        user.name.toLowerCase().includes(search) ||
        user.email.toLowerCase().includes(search) ||
        (user.company?.name.toLowerCase().includes(search))
      );
    }
    
    // Apply role filter
    if (filters.value.role && filters.value.role !== '' as UserRole) {
      result = result.filter(user => user.role === filters.value.role);
    }

    // Apply company filter
    if (filters.value.company_id && filters.value.company_id !== '') {
      result = result.filter(user => user.company?.id === filters.value.company_id);
    }
    
    // Apply verified filter
    if (filters.value.verified !== undefined && filters.value.verified !== '') {
      result = result.filter(user => 
        filters.value.verified ? !!user.email_verified_at : !user.email_verified_at
      );
    }
    
    return result;
  });

  // ==================== ACTIONS ====================
  /**
   * Fetch users from API with filters
   */
  async function fetchUsers(customFilters?: Partial<UserFilters>): Promise<void> {
    isLoading.value = true;
    error.value = null;
    
    try {
      const appliedFilters = { ...filters.value, ...customFilters };
      console.log('🏪 [USERS STORE] Calling UsersApi.getUsers with filters:', appliedFilters);
      const response: UsersListResponse = await UsersApi.getUsers(appliedFilters);
      console.log('🏪 [USERS STORE] API response received:', response);
      console.log('🏪 [USERS STORE] Response data array:', response.data);
      console.log('🏪 [USERS STORE] Response data length:', response.data?.length);
      
      users.value = response.data;
      console.log('🏪 [USERS STORE] Users set in store:', users.value);
      console.log('🏪 [USERS STORE] Store users length after set:', users.value?.length);
      pagination.value = {
        current_page: response.meta.current_page,
        per_page: response.meta.per_page,
        total: response.meta.total,
        last_page: response.meta.last_page
      };
      
      if (customFilters && Object.keys(customFilters).length > 0) {
        filters.value = { ...filters.value, ...appliedFilters };
      } else if (customFilters && Object.keys(customFilters).length === 0) {
        // Clear filters when empty object is passed
        filters.value = {
          search: '',
          role: '',
          company_id: '',
          verified: '',
          page: 1,
          per_page: 50
        };
      }
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch users';
      console.error('Failed to fetch users:', err);
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Fetch single user profile
   */
  async function fetchUser(userId: string): Promise<UserProfile> {
    isLoading.value = true;
    
    try {
      const user = await UsersApi.getUser(userId);
      
      // Update in list if exists
      const index = users.value.findIndex(u => u.id === userId);
      if (index !== -1) {
        // Convert UserProfile to UserListItem for the list
        const listItem: UserListItem = {
          id: user.id,
          name: user.name,
          email: user.email,
          role: user.role,
          ...(user.avatar_url && { avatar_url: user.avatar_url }),
          ...(user.company && { company: user.company }),
          ...(user.email_verified_at && { email_verified_at: user.email_verified_at }),
          created_at: user.created_at,
          updated_at: user.updated_at
        };
        users.value[index] = listItem;
      }
      
      currentUser.value = user;
      return user;
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch user';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Create new user
   */
  async function createUser(data: CreateUserRequest): Promise<UserProfile> {
    isLoading.value = true;
    
    try {
      const user = await UsersApi.createUser(data);
      
      // Add to users list
      const listItem: UserListItem = {
        id: user.id,
        name: user.name,
        email: user.email,
        role: user.role,
        ...(user.avatar_url && { avatar_url: user.avatar_url }),
        ...(user.company && { company: user.company }),
        ...(user.email_verified_at && { email_verified_at: user.email_verified_at }),
        created_at: user.created_at,
        updated_at: user.updated_at
      };
      users.value.unshift(listItem);
      
      return user;
    } catch (err: any) {
      error.value = err.message || 'Failed to create user';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Update existing user
   */
  async function updateUser(userId: string, data: UpdateUserRequest): Promise<UserProfile> {
    isLoading.value = true;
    
    try {
      const updated = await UsersApi.updateUser(userId, data);
      
      // Update in list
      const index = users.value.findIndex(u => u.id === userId);
      if (index !== -1) {
        const listItem: UserListItem = {
          id: updated.id,
          name: updated.name,
          email: updated.email,
          role: updated.role,
          ...(updated.avatar_url && { avatar_url: updated.avatar_url }),
          ...(updated.company && { company: updated.company }),
          ...(updated.email_verified_at && { email_verified_at: updated.email_verified_at }),
          created_at: updated.created_at,
          updated_at: updated.updated_at
        };
        users.value[index] = listItem;
      }
      
      if (currentUser.value?.id === userId) {
        currentUser.value = updated;
      }
      
      return updated;
    } catch (err: any) {
      error.value = err.message || 'Failed to update user';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Delete user (soft delete)
   */
  async function deleteUser(userId: string): Promise<void> {
    isLoading.value = true;
    
    try {
      await UsersApi.deleteUser(userId);
      
      // Remove from list
      users.value = users.value.filter(u => u.id !== userId);
      
      if (currentUser.value?.id === userId) {
        currentUser.value = null;
      }
    } catch (err: any) {
      error.value = err.message || 'Failed to delete user';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Upload user avatar
   */
  async function uploadAvatar(userId: string, file: File): Promise<string> {
    isLoading.value = true;
    
    try {
      const response = await UsersApi.uploadAvatar(userId, file);
      
      // Update avatar in list and current user
      const index = users.value.findIndex(u => u.id === userId);
      if (index !== -1) {
        users.value[index].avatar_url = response.avatar_url;
      }
      
      if (currentUser.value?.id === userId) {
        currentUser.value.avatar_url = response.avatar_url;
      }
      
      return response.avatar_url;
    } catch (err: any) {
      error.value = err.message || 'Failed to upload avatar';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Resend email verification
   */
  async function resendEmailVerification(userId: string): Promise<void> {
    isLoading.value = true;
    
    try {
      await UsersApi.resendEmailVerification(userId);
    } catch (err: any) {
      error.value = err.message || 'Failed to resend verification email';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Get users by role
   */
  async function getUsersByRole(role: UserRole): Promise<UserListItem[]> {
    try {
      return await UsersApi.getUsersByRole(role);
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch users by role';
      throw err;
    }
  }

  /**
   * Get users in same company
   */
  async function getCompanyUsers(companyId?: string): Promise<UserListItem[]> {
    try {
      return await UsersApi.getCompanyUsers(companyId);
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch company users';
      throw err;
    }
  }

  /**
   * Set current user
   */
  function setCurrentUser(user: UserProfile | null): void {
    currentUser.value = user;
  }

  /**
   * Update filters
   */
  function updateFilters(newFilters: Partial<UserFilters>): void {
    filters.value = { ...filters.value, ...newFilters };
  }

  /**
   * Change page
   */
  function changePage(page: number): void {
    filters.value.page = page;
    fetchUsers();
  }

  /**
   * Change page size
   */
  function changePageSize(perPage: number): void {
    filters.value.per_page = perPage;
    filters.value.page = 1; // Reset to first page
    fetchUsers();
  }

  /**
   * Clear error
   */
  function clearError(): void {
    error.value = null;
  }

  /**
   * Reset store
   */
  function $reset(): void {
    users.value = [];
    currentUser.value = null;
    isLoading.value = false;
    error.value = null;
    filters.value = {
      search: '',
      role: '',
      company_id: '',
      verified: '',
      page: 1,
      per_page: 50
    };
    pagination.value = {
      current_page: 1,
      per_page: 50,
      total: 0,
      last_page: 0
    };
  }

  return {
    // State
    users: readonly(users),
    currentUser: readonly(currentUser),
    isLoading: readonly(isLoading),
    error: readonly(error),
    filters: readonly(filters),
    pagination: readonly(pagination),
    
    // Getters
    usersByRole,
    activeUsers,
    verifiedUsers,
    statistics,
    filteredUsers,
    
    // Actions
    fetchUsers,
    fetchUser,
    createUser,
    updateUser,
    deleteUser,
    uploadAvatar,
    resendEmailVerification,
    getUsersByRole,
    getCompanyUsers,
    setCurrentUser,
    updateFilters,
    changePage,
    changePageSize,
    clearError,
    $reset
  };
});