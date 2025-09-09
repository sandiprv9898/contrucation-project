<template>
  <div class="user-profile space-y-6">
    <!-- Profile Header -->
    <VCard>
      <div class="p-6">
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-4">
            <!-- Avatar -->
            <div class="h-20 w-20 rounded-full bg-blue-100 flex items-center justify-center text-2xl font-semibold text-blue-700">
              {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
            </div>
            
            <!-- User Info -->
            <div>
              <h2 class="text-2xl font-bold text-gray-900">{{ user?.name }}</h2>
              <p class="text-gray-600">{{ user?.email }}</p>
              <div class="flex items-center gap-3 mt-2">
                <VBadge :variant="getRoleVariant(user?.role)">
                  {{ getRoleLabel(user?.role) }}
                </VBadge>
                <VBadge :variant="user?.email_verified_at ? 'success' : 'warning'">
                  <CheckCircle v-if="user?.email_verified_at" class="mr-1 h-3 w-3" />
                  <Clock v-else class="mr-1 h-3 w-3" />
                  {{ user?.email_verified_at ? 'Verified' : 'Pending Verification' }}
                </VBadge>
              </div>
            </div>
          </div>
          
          <!-- Actions -->
          <div class="flex items-center gap-2">
            <VButton 
              v-if="canEditUser"
              variant="outline"
              @click="handleEdit"
            >
              <Edit class="mr-2 h-4 w-4" />
              Edit Profile
            </VButton>
            <VDropdownMenu align="end" size="sm">
              <template #trigger>
                <VButton variant="outline" class="h-9 w-9 p-0">
                  <MoreVertical class="h-4 w-4" />
                </VButton>
              </template>
              <template #content>
                <VDropdownMenuItem 
                  v-if="!user?.email_verified_at && canEditUser"
                  @click="handleResendVerification"
                >
                  <Mail class="mr-2 h-4 w-4" />
                  Resend Verification
                </VDropdownMenuItem>
                <VDropdownMenuItem 
                  v-if="canEditUser"
                  @click="handleChangePassword"
                >
                  <Lock class="mr-2 h-4 w-4" />
                  Change Password
                </VDropdownMenuItem>
                <VDropdownMenuItem 
                  v-if="canEditUser"
                  @click="handleChangeRole"
                >
                  <Shield class="mr-2 h-4 w-4" />
                  Change Role
                </VDropdownMenuItem>
                <div class="border-t border-gray-100 my-1" v-if="canDeleteUser"></div>
                <VDropdownMenuItem 
                  v-if="canDeleteUser"
                  variant="destructive"
                  @click="handleDelete"
                >
                  <Trash class="mr-2 h-4 w-4" />
                  Delete User
                </VDropdownMenuItem>
              </template>
            </VDropdownMenu>
          </div>
        </div>
      </div>
    </VCard>

    <!-- Profile Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Personal Information -->
      <div class="lg:col-span-2">
        <VCard>
          <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Personal Information</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ user?.name || 'N/A' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ user?.email || 'N/A' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ user?.phone || 'N/A' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Department</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ user?.department || 'N/A' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Company</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ user?.company?.name || 'N/A' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(user?.created_at) }}</dd>
              </div>
            </dl>
            
            <div v-if="user?.bio" class="mt-6">
              <dt class="text-sm font-medium text-gray-500 mb-2">Bio</dt>
              <dd class="text-sm text-gray-900">{{ user.bio }}</dd>
            </div>
          </div>
        </VCard>
      </div>

      <!-- Permissions & Activity -->
      <div class="space-y-6">
        <!-- Role Permissions -->
        <VCard>
          <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Role Permissions</h3>
            <div class="space-y-2">
              <div 
                v-for="(permission, key) in userPermissions" 
                :key="key"
                class="flex items-center justify-between text-sm"
              >
                <span class="text-gray-600">{{ formatPermissionLabel(key) }}</span>
                <span>
                  <CheckCircle v-if="permission" class="h-4 w-4 text-green-600" />
                  <X v-else class="h-4 w-4 text-gray-400" />
                </span>
              </div>
            </div>
          </div>
        </VCard>

        <!-- Recent Activity -->
        <VCard>
          <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
            <div class="space-y-3">
              <div class="text-sm">
                <div class="font-medium text-gray-900">Last Login</div>
                <div class="text-gray-600">{{ formatDateTime(user?.last_login_at) || 'Never' }}</div>
              </div>
              <div class="text-sm">
                <div class="font-medium text-gray-900">Email Verified</div>
                <div class="text-gray-600">{{ formatDateTime(user?.email_verified_at) || 'Not verified' }}</div>
              </div>
              <div class="text-sm">
                <div class="font-medium text-gray-900">Profile Updated</div>
                <div class="text-gray-600">{{ formatDateTime(user?.updated_at) || 'Never' }}</div>
              </div>
            </div>
          </div>
        </VCard>
      </div>
    </div>

    <!-- Projects & Tasks (if applicable) -->
    <VCard v-if="user?.role !== 'admin'">
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-4">Assigned Projects</h3>
        <div v-if="loading" class="text-center py-8 text-gray-500">
          Loading projects...
        </div>
        <div v-else-if="!projects || projects.length === 0" class="text-center py-8 text-gray-500">
          No projects assigned yet
        </div>
        <div v-else class="space-y-3">
          <div 
            v-for="project in projects" 
            :key="project.id"
            class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50"
          >
            <div>
              <div class="font-medium text-gray-900">{{ project.name }}</div>
              <div class="text-sm text-gray-600">{{ project.status }}</div>
            </div>
            <VButton variant="outline" size="sm" @click="viewProject(project.id)">
              View
            </VButton>
          </div>
        </div>
      </div>
    </VCard>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useUserPermissions } from '../composables/useUserPermissions';
import { VButton, VCard, VBadge, VDropdownMenu, VDropdownMenuItem } from '@/components/ui';
import { 
  Edit, Trash, CheckCircle, Clock, X, MoreVertical, 
  Mail, Shield, Lock 
} from 'lucide-vue-next';
import { ROLE_LABELS, ROLE_BADGE_VARIANTS, ROLE_PERMISSIONS } from '../constants/users.constants';
import type { UserProfile } from '../types/users.types';

interface Props {
  user: UserProfile | null;
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false
});

const emit = defineEmits<{
  edit: [];
  delete: [];
  resendVerification: [];
  changePassword: [];
  changeRole: [];
}>();

// ==================== COMPOSABLES ====================
const router = useRouter();
const { hasPermission, canEditUser: checkCanEditUser, canDeleteUser: checkCanDeleteUser } = useUserPermissions();

// ==================== STATE ====================
const projects = ref<any[]>([]);

// ==================== COMPUTED ====================
const canEditUser = computed(() => {
  if (!props.user) return false;
  return checkCanEditUser(props.user);
});

const canDeleteUser = computed(() => {
  if (!props.user) return false;
  return checkCanDeleteUser(props.user);
});

const userPermissions = computed(() => {
  if (!props.user?.role) return {};
  return ROLE_PERMISSIONS[props.user.role] || {};
});

// ==================== METHODS ====================
/**
 * Get role label
 */
const getRoleLabel = (role?: string): string => {
  if (!role) return 'Unknown';
  return ROLE_LABELS[role as keyof typeof ROLE_LABELS] || role;
};

/**
 * Get role badge variant
 */
const getRoleVariant = (role?: string): 'default' | 'outline' | 'success' | 'warning' | 'destructive' => {
  if (!role) return 'default';
  return ROLE_BADGE_VARIANTS[role as keyof typeof ROLE_BADGE_VARIANTS] || 'default';
};

/**
 * Format date
 */
const formatDate = (dateString?: string): string => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString();
};

/**
 * Format date and time
 */
const formatDateTime = (dateString?: string): string => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleString();
};

/**
 * Format permission label
 */
const formatPermissionLabel = (key: string): string => {
  const labels: Record<string, string> = {
    canManageUsers: 'Manage Users',
    canViewUsers: 'View Users',
    canEditUser: 'Edit Users',
    canDeleteUser: 'Delete Users',
    canManageProjects: 'Manage Projects',
    canViewProjects: 'View Projects',
    canAssignTasks: 'Assign Tasks',
    canViewReports: 'View Reports',
    canManageCompany: 'Manage Company',
    canManageFinance: 'Manage Finance',
    canApproveTimesheet: 'Approve Timesheets',
    canManageEquipment: 'Manage Equipment',
    canManageSafety: 'Manage Safety',
  };
  return labels[key] || key;
};

/**
 * Handle edit
 */
const handleEdit = (): void => {
  emit('edit');
};

/**
 * Handle delete
 */
const handleDelete = (): void => {
  if (confirm(`Are you sure you want to delete ${props.user?.name}? This action cannot be undone.`)) {
    emit('delete');
  }
};

/**
 * Handle resend verification
 */
const handleResendVerification = (): void => {
  emit('resendVerification');
};

/**
 * Handle change password
 */
const handleChangePassword = (): void => {
  emit('changePassword');
};

/**
 * Handle change role
 */
const handleChangeRole = (): void => {
  emit('changeRole');
};

/**
 * View project
 */
const viewProject = (projectId: string): void => {
  router.push({ name: 'projects.view', params: { id: projectId } });
};

// ==================== LIFECYCLE ====================
onMounted(() => {
  // Load user's projects if applicable
  // This would typically fetch from an API
  if (props.user?.role !== 'admin') {
    // Simulated project data
    projects.value = [];
  }
});
</script>