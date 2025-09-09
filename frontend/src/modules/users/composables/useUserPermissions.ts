import { computed, readonly } from 'vue';
import { useAuthStore } from '@/modules/auth/stores/auth.store';
import { ROLE_PERMISSIONS, ROLE_HIERARCHY } from '../types/users.types';
import type { UserRole, User } from '@/modules/auth/types/auth.types';
import type { RolePermissions } from '../types/users.types';

export function useUserPermissions() {
  // ==================== STORES ====================
  const authStore = useAuthStore();
  
  // ==================== COMPUTED ====================
  const currentUser = computed(() => authStore.user);
  
  const currentUserRole = computed(() => authStore.user?.role);
  
  const currentUserPermissions = computed((): RolePermissions | null => {
    if (!currentUserRole.value) return null;
    return ROLE_PERMISSIONS[currentUserRole.value];
  });
  
  const isAdmin = computed(() => currentUserRole.value === 'admin');
  
  const isProjectManager = computed(() => currentUserRole.value === 'project_manager');
  
  const isSupervisor = computed(() => currentUserRole.value === 'supervisor');
  
  const isFieldWorker = computed(() => currentUserRole.value === 'field_worker');

  // ==================== METHODS ====================
  /**
   * Check if current user has a specific permission
   */
  function hasPermission(permission: keyof RolePermissions): boolean {
    if (!currentUserPermissions.value) return false;
    return currentUserPermissions.value[permission];
  }
  
  /**
   * Check if current user can perform action on target user
   */
  function canManageUser(targetUser: User | { role: UserRole }): boolean {
    if (!currentUser.value || !targetUser) return false;
    
    const currentRole = currentUser.value.role;
    const targetRole = targetUser.role;
    
    // Admin can manage everyone except other admins (unless they're the same person)
    if (currentRole === 'admin') {
      return targetRole !== 'admin' || currentUser.value.id === (targetUser as User).id;
    }
    
    // Project managers can manage supervisors and field workers
    if (currentRole === 'project_manager') {
      return ['supervisor', 'field_worker'].includes(targetRole);
    }
    
    // Supervisors can manage field workers
    if (currentRole === 'supervisor') {
      return targetRole === 'field_worker';
    }
    
    return false;
  }
  
  /**
   * Check if current user can edit target user
   */
  function canEditUser(targetUser: User): boolean {
    if (!currentUser.value) return false;
    
    // Users can always edit themselves
    if (currentUser.value.id === targetUser.id) return true;
    
    // Otherwise check management permissions
    return canManageUser(targetUser);
  }
  
  /**
   * Check if current user can delete target user
   */
  function canDeleteUser(targetUser: User): boolean {
    if (!currentUser.value) return false;
    
    // Users cannot delete themselves
    if (currentUser.value.id === targetUser.id) return false;
    
    return canManageUser(targetUser);
  }
  
  /**
   * Check if current user has higher or equal role hierarchy
   */
  function hasHigherOrEqualRole(targetRole: UserRole): boolean {
    if (!currentUserRole.value) return false;
    
    const currentHierarchy = ROLE_HIERARCHY[currentUserRole.value];
    const targetHierarchy = ROLE_HIERARCHY[targetRole];
    
    return currentHierarchy >= targetHierarchy;
  }
  
  /**
   * Check if current user has higher role hierarchy
   */
  function hasHigherRole(targetRole: UserRole): boolean {
    if (!currentUserRole.value) return false;
    
    const currentHierarchy = ROLE_HIERARCHY[currentUserRole.value];
    const targetHierarchy = ROLE_HIERARCHY[targetRole];
    
    return currentHierarchy > targetHierarchy;
  }
  
  /**
   * Get available roles that current user can assign
   */
  function getAssignableRoles(): UserRole[] {
    if (!currentUserRole.value) return [];
    
    const currentHierarchy = ROLE_HIERARCHY[currentUserRole.value];
    
    return Object.entries(ROLE_HIERARCHY)
      .filter(([_, hierarchy]) => hierarchy < currentHierarchy)
      .map(([role, _]) => role as UserRole);
  }
  
  /**
   * Check if current user can assign specific role
   */
  function canAssignRole(targetRole: UserRole): boolean {
    return getAssignableRoles().includes(targetRole);
  }
  
  /**
   * Get permissions for a specific role
   */
  function getRolePermissions(role: UserRole): RolePermissions {
    return ROLE_PERMISSIONS[role];
  }
  
  /**
   * Check if role has specific permission
   */
  function roleHasPermission(role: UserRole, permission: keyof RolePermissions): boolean {
    return ROLE_PERMISSIONS[role][permission];
  }
  
  /**
   * Check if current user is in same company as target user
   */
  function isInSameCompany(targetUser: User): boolean {
    if (!currentUser.value) return false;
    return currentUser.value.company?.id === targetUser.company?.id;
  }
  
  /**
   * Get user access level description
   */
  function getUserAccessLevel(): string {
    if (!currentUserRole.value) return 'No Access';
    
    switch (currentUserRole.value) {
      case 'admin':
        return 'Full System Access';
      case 'project_manager':
        return 'Project Management Access';
      case 'supervisor':
        return 'Supervisory Access';
      case 'field_worker':
        return 'Field Worker Access';
      default:
        return 'Limited Access';
    }
  }
  
  /**
   * Check multiple permissions at once
   */
  function hasAnyPermission(permissions: (keyof RolePermissions)[]): boolean {
    return permissions.some(permission => hasPermission(permission));
  }
  
  /**
   * Check if all permissions are satisfied
   */
  function hasAllPermissions(permissions: (keyof RolePermissions)[]): boolean {
    return permissions.every(permission => hasPermission(permission));
  }

  return {
    // State
    currentUser: readonly(currentUser),
    currentUserRole: readonly(currentUserRole),
    currentUserPermissions: readonly(currentUserPermissions),
    
    // Role checks
    isAdmin: readonly(isAdmin),
    isProjectManager: readonly(isProjectManager),
    isSupervisor: readonly(isSupervisor),
    isFieldWorker: readonly(isFieldWorker),
    
    // Permission methods
    hasPermission,
    canManageUser,
    canEditUser,
    canDeleteUser,
    hasHigherOrEqualRole,
    hasHigherRole,
    getAssignableRoles,
    canAssignRole,
    getRolePermissions,
    roleHasPermission,
    isInSameCompany,
    getUserAccessLevel,
    hasAnyPermission,
    hasAllPermissions
  };
}