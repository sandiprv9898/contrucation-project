<template>
  <div v-if="hasRequiredPermission" class="permission-guard">
    <slot />
  </div>
  <div v-else-if="showFallback" class="permission-fallback">
    <slot name="fallback">
      <div class="text-center py-8 text-gray-500">
        <div class="text-sm">{{ fallbackMessage }}</div>
      </div>
    </slot>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useUserPermissions } from '../composables/useUserPermissions';
import type { RolePermissions } from '../types/users.types';
import type { UserRole, User } from '@/modules/auth/types/auth.types';

interface Props {
  /**
   * Single permission to check
   */
  permission?: keyof RolePermissions;
  
  /**
   * Multiple permissions - user must have ANY of these
   */
  anyPermissions?: (keyof RolePermissions)[];
  
  /**
   * Multiple permissions - user must have ALL of these
   */
  allPermissions?: (keyof RolePermissions)[];
  
  /**
   * Required role - user must have this exact role
   */
  role?: UserRole;
  
  /**
   * Required roles - user must have ANY of these roles
   */
  anyRoles?: UserRole[];
  
  /**
   * User to check permissions for (defaults to current user)
   */
  user?: User;
  
  /**
   * Show fallback content when permission denied
   */
  showFallback?: boolean;
  
  /**
   * Custom fallback message
   */
  fallbackMessage?: string;
  
  /**
   * Inverse logic - show content when permission is NOT granted
   */
  inverse?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  showFallback: false,
  fallbackMessage: 'You do not have permission to view this content.',
  inverse: false
});

// ==================== COMPOSABLES ====================
const {
  currentUser,
  currentUserRole,
  hasPermission,
  hasAnyPermission,
  hasAllPermissions
} = useUserPermissions();

// ==================== COMPUTED ====================
const targetUser = computed(() => props.user || currentUser.value);
const targetUserRole = computed(() => props.user?.role || currentUserRole.value);

/**
 * Check if user has required permission
 */
const hasRequiredPermission = computed(() => {
  // If no user, deny access
  if (!targetUser.value) {
    return props.inverse;
  }
  
  let hasAccess = true;
  
  // Check single permission
  if (props.permission) {
    hasAccess = hasAccess && hasPermission(props.permission);
  }
  
  // Check any permissions
  if (props.anyPermissions && props.anyPermissions.length > 0) {
    hasAccess = hasAccess && hasAnyPermission(props.anyPermissions);
  }
  
  // Check all permissions
  if (props.allPermissions && props.allPermissions.length > 0) {
    hasAccess = hasAccess && hasAllPermissions(props.allPermissions);
  }
  
  // Check exact role
  if (props.role) {
    hasAccess = hasAccess && (targetUserRole.value === props.role);
  }
  
  // Check any roles
  if (props.anyRoles && props.anyRoles.length > 0) {
    hasAccess = hasAccess && props.anyRoles.includes(targetUserRole.value!);
  }
  
  // Apply inverse logic if needed
  return props.inverse ? !hasAccess : hasAccess;
});
</script>

<script lang="ts">
export default {
  name: 'PermissionGuard'
};
</script>