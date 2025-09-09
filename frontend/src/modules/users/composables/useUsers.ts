import { ref, computed, watch, readonly } from 'vue';
import { useRoute } from 'vue-router';
import { useUsersStore } from '../stores/users.store';
import { useAuthStore } from '@/modules/auth/stores/auth.store';
import { userService } from '../api/user.service';
import type { UserListItem, UserProfile, UserFilters } from '../types/users.types';
import type { UserRole } from '@/modules/auth/types/auth.types';

export function useUsers(userId?: string) {
  // ==================== STORES ====================
  const usersStore = useUsersStore();
  const authStore = useAuthStore();
  const route = useRoute();
  
  // ==================== STATE ====================
  const loading = ref(false);
  const error = ref<Error | null>(null);
  
  // Get user ID from route if not provided
  const id = userId || route.params.id as string;
  
  // ==================== COMPUTED ====================
  const user = computed(() => 
    usersStore.users.find(u => u.id === id)
  );
  
  const currentUser = computed(() => usersStore.currentUser);
  
  const users = computed(() => usersStore.users);
  
  const filteredUsers = computed(() => usersStore.filteredUsers);
  
  const statistics = computed(() => usersStore.statistics);
  
  const pagination = computed(() => usersStore.pagination);
  
  const isCurrentUserAdmin = computed(() => 
    authStore.user?.role === 'admin'
  );
  
  const isCurrentUserManager = computed(() => 
    authStore.user?.role === 'admin' || authStore.user?.role === 'project_manager'
  );
  
  const canManageUser = computed(() => {
    if (!authStore.user || !user.value) return false;
    
    // Admins can manage anyone except themselves for certain actions
    if (authStore.user.role === 'admin') return true;
    
    // Project managers can manage supervisors and field workers
    if (authStore.user.role === 'project_manager') {
      return ['supervisor', 'field_worker'].includes(user.value.role);
    }
    
    // Supervisors can manage field workers
    if (authStore.user.role === 'supervisor') {
      return user.value.role === 'field_worker';
    }
    
    return false;
  });
  
  const canEditUser = computed(() => {
    if (!authStore.user || !user.value) return false;
    
    // Users can edit themselves (basic profile info)
    if (authStore.user.id === user.value.id) return true;
    
    return canManageUser.value;
  });
  
  const canDeleteUser = computed(() => {
    if (!authStore.user || !user.value) return false;
    
    // Users cannot delete themselves
    if (authStore.user.id === user.value.id) return false;
    
    return canManageUser.value;
  });

  // ==================== METHODS ====================
  /**
   * Load user data
   */
  async function loadUser(userId: string, forceRefresh = false) {
    if (!forceRefresh && usersStore.users.find(u => u.id === userId)) {
      return usersStore.users.find(u => u.id === userId);
    }
    
    loading.value = true;
    error.value = null;
    
    try {
      const userData = await usersStore.fetchUser(userId);
      return userData;
    } catch (err) {
      error.value = err as Error;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  /**
   * Load users list with filters
   */
  async function loadUsers(filters?: Partial<UserFilters>, forceRefresh = false) {
    console.log('🔧 [USE USERS] loadUsers called with filters:', filters, 'forceRefresh:', forceRefresh);
    console.log('🔧 [USE USERS] Current store users length:', usersStore.users.length);
    
    if (!forceRefresh && usersStore.users.length > 0 && !filters) {
      console.log('🔧 [USE USERS] Returning cached users:', usersStore.users);
      return usersStore.users;
    }
    
    loading.value = true;
    error.value = null;
    
    try {
      console.log('🔧 [USE USERS] Calling usersStore.fetchUsers...');
      await usersStore.fetchUsers(filters);
      console.log('🔧 [USE USERS] Store fetch completed. Store users:', usersStore.users);
      console.log('🔧 [USE USERS] Store users length:', usersStore.users.length);
      return usersStore.users;
    } catch (err) {
      console.error('🔧 [USE USERS] Error in loadUsers:', err);
      error.value = err as Error;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  /**
   * Create new user
   */
  async function createUser(userData: any) {
    loading.value = true;
    error.value = null;
    
    try {
      const newUser = await usersStore.createUser(userData);
      return newUser;
    } catch (err) {
      error.value = err as Error;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  /**
   * Update user data
   */
  async function updateUser(userId: string, updates: any) {
    loading.value = true;
    error.value = null;
    
    try {
      const updated = await usersStore.updateUser(userId, updates);
      return updated;
    } catch (err) {
      error.value = err as Error;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  /**
   * Delete user
   */
  async function deleteUser(userId: string) {
    loading.value = true;
    error.value = null;
    
    try {
      await usersStore.deleteUser(userId);
    } catch (err) {
      error.value = err as Error;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  /**
   * Upload avatar
   */
  async function uploadAvatar(userId: string, file: File) {
    loading.value = true;
    error.value = null;
    
    try {
      const avatarUrl = await usersStore.uploadAvatar(userId, file);
      return avatarUrl;
    } catch (err) {
      error.value = err as Error;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  /**
   * Resend email verification
   */
  async function resendVerification(userId: string) {
    loading.value = true;
    error.value = null;
    
    try {
      await usersStore.resendEmailVerification(userId);
    } catch (err) {
      error.value = err as Error;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  /**
   * Get users by role
   */
  async function getUsersByRole(role: UserRole): Promise<UserListItem[]> {
    try {
      return await usersStore.getUsersByRole(role);
    } catch (err) {
      error.value = err as Error;
      throw err;
    }
  }
  
  /**
   * Filter users
   */
  function updateFilters(newFilters: Partial<UserFilters>) {
    usersStore.updateFilters(newFilters);
  }
  
  /**
   * Change page
   */
  function changePage(page: number) {
    usersStore.changePage(page);
  }
  
  /**
   * Change page size
   */
  function changePageSize(perPage: number) {
    usersStore.changePageSize(perPage);
  }
  
  /**
   * Clear errors
   */
  function clearError() {
    error.value = null;
    usersStore.clearError();
  }
  
  /**
   * Export users data
   */
  async function exportUsers(format: 'csv' | 'xlsx' = 'csv', filters?: Partial<UserFilters>) {
    loading.value = true;
    error.value = null;
    
    try {
      await userService.exportUsers(format, filters);
    } catch (err) {
      error.value = err as Error;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  /**
   * Refresh users list
   */
  function refreshUsers() {
    return loadUsers(undefined, true);
  }
  
  /**
   * Refresh users list (alias for backwards compatibility)
   */
  function refresh() {
    return refreshUsers();
  }

  // ==================== LIFECYCLE ====================
  // Auto-load user if ID is provided
  if (id && !user.value) {
    loadUser(id);
  }

  return {
    // State
    user: readonly(user),
    currentUser: readonly(currentUser),
    users: readonly(users),
    filteredUsers: readonly(filteredUsers),
    statistics: readonly(statistics),
    pagination: readonly(pagination),
    loading: readonly(loading),
    error: readonly(error),
    
    // Computed permissions
    isCurrentUserAdmin: readonly(isCurrentUserAdmin),
    isCurrentUserManager: readonly(isCurrentUserManager),
    canManageUser: readonly(canManageUser),
    canEditUser: readonly(canEditUser),
    canDeleteUser: readonly(canDeleteUser),
    
    // Methods
    loadUser,
    loadUsers,
    createUser,
    updateUser,
    deleteUser,
    uploadAvatar,
    resendVerification,
    getUsersByRole,
    updateFilters,
    changePage,
    changePageSize,
    clearError,
    exportUsers,
    refreshUsers,
    refresh
  };
}