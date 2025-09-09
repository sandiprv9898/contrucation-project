/**
 * User Management Module
 * 
 * Complete user management system with role-based access control (RBAC)
 * for the Construction Management Platform.
 * 
 * Features:
 * - User CRUD operations
 * - Role-based permissions (admin, project_manager, supervisor, field_worker)
 * - DataTable-based user listing (mandatory per project standards)
 * - Permission-based UI rendering
 * - Avatar management
 * - Email verification
 */

// ==================== API ====================
export { UsersApi } from './api/users.api';

// ==================== STORES ====================
export { useUsersStore } from './stores/users.store';

// ==================== COMPOSABLES ====================
export { useUsers } from './composables/useUsers';
export { useUserPermissions } from './composables/useUserPermissions';
export { useUserValidation } from './composables/useUserValidation';
export { useUserStats } from './composables/useUserStats';

// ==================== COMPONENTS ====================
export { default as UserList } from './components/UserList.vue';
export { default as UserForm } from './components/UserForm.vue';
export { default as UserProfile } from './components/UserProfile.vue';
export { default as RoleBadge } from './components/RoleBadge.vue';
export { default as PermissionGuard } from './components/PermissionGuard.vue';
export { default as UserManagementDashboard } from './components/UserManagementDashboard.vue';

// ==================== TYPES ====================
export type {
  UserListItem,
  UserProfile,
  CreateUserRequest,
  UpdateUserRequest,
  UpdateUserPasswordRequest,
  UsersListResponse,
  UserFilters,
  UserState,
  RolePermissions
} from './types/users.types';

// ==================== CONSTANTS ====================
export * from './constants/users.constants';