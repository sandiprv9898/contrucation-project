<template>
  <div class="space-y-6">
    <!-- Header with Stats -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Team Members</h1>
          <p class="text-gray-600 mt-1">Manage your construction team and their roles</p>
        </div>
        
        <!-- Inline Stats -->
        <div class="flex items-center gap-6 text-sm text-gray-600">
          <span>Total: <strong class="text-gray-900">{{ statistics.total }}</strong></span>
          <span>Active: <strong class="text-green-600">{{ statistics.active }}</strong></span>
          <span>Verified: <strong class="text-gray-900">{{ statistics.verified }}</strong></span>
          <span>This Month: <strong class="text-blue-600">{{ statistics.thisMonth }}</strong></span>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-4">
      <div class="flex flex-wrap items-center gap-4">
        <!-- Search -->
        <div class="relative flex-1 min-w-[200px]">
          <Search class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
          <VInput
            v-model="searchTerm"
            placeholder="Search by name or email..."
            class="pl-9"
          />
        </div>
        
        <!-- Role Filter -->
        <div class="lg:w-48">
          <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
          <select 
            v-model="selectedRole"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent"
          >
            <option value="">All Roles</option>
            <option value="admin">Administrator</option>
            <option value="project_manager">Project Manager</option>
            <option value="supervisor">Supervisor</option>
            <option value="field_worker">Field Worker</option>
          </select>
        </div>
        
        <!-- Department Filter -->
        <div class="lg:w-48">
          <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
          <select 
            v-model="selectedDepartment"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent"
          >
            <option value="">All Departments</option>
            <option value="construction">Construction</option>
            <option value="engineering">Engineering</option>
            <option value="safety">Safety</option>
            <option value="management">Management</option>
            <option value="logistics">Logistics</option>
          </select>
        </div>
        
        <!-- Status Filter -->
        <div class="lg:w-40">
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <select 
            v-model="selectedVerification"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent"
          >
            <option value="">All Status</option>
            <option value="true">Verified</option>
            <option value="false">Pending</option>
          </select>
        </div>
        
        <!-- Clear Filters -->
        <VButton 
          v-if="hasActiveFilters"
          variant="outline" 
          @click="clearFilters"
          class="flex items-center gap-2"
        >
          <X class="h-4 w-4" />
          Clear Filters
        </VButton>
        
        <!-- Add User Button -->
        <VButton 
          v-if="canCreateUsers"
          @click="handleCreateUser"
          class="bg-orange-600 hover:bg-orange-700 text-white"
        >
          <Plus class="mr-2 h-4 w-4" />
          Add Member
        </VButton>
      </div>
    </div>

    <!-- User Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Member
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Role & Department
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Joined
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <!-- Loading State -->
            <tr v-if="loading">
              <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                <div class="flex items-center justify-center">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-600"></div>
                  <span class="ml-3">Loading team members...</span>
                </div>
              </td>
            </tr>
            
            <!-- Empty State -->
            <tr v-else-if="displayedUsers.length === 0">
              <td colspan="5" class="px-6 py-12 text-center">
                <div class="text-gray-400">
                  <Users class="h-16 w-16 mx-auto mb-4" />
                  <h3 class="text-lg font-medium text-gray-900 mb-2">No members found</h3>
                  <p class="text-gray-500">
                    {{ hasActiveFilters ? 'Try adjusting your filters' : 'Get started by adding your first team member' }}
                  </p>
                  <VButton 
                    v-if="!hasActiveFilters && canCreateUsers"
                    @click="handleCreateUser"
                    class="mt-4 bg-orange-600 hover:bg-orange-700 text-white"
                  >
                    <Plus class="mr-2 h-4 w-4" />
                    Add First Member
                  </VButton>
                </div>
              </td>
            </tr>
            
            <!-- User Rows -->
            <tr 
              v-else
              v-for="user in displayedUsers" 
              :key="user.id"
              class="hover:bg-gray-50 transition-colors cursor-pointer"
              @click="handleViewUser(user)"
            >
              <!-- Member Info -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-4">
                  <div class="relative">
                    <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center">
                      <span class="text-sm font-medium text-orange-800">
                        {{ user.name.split(' ').map(n => n[0]).join('').toUpperCase() }}
                      </span>
                    </div>
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-gray-900 truncate">
                      {{ user.name }}
                    </div>
                    <div class="text-sm text-gray-500 truncate">
                      {{ user.email }}
                    </div>
                  </div>
                </div>
              </td>
              
              <!-- Role & Department -->
              <td class="px-6 py-4">
                <div class="space-y-1">
                  <VBadge :variant="getRoleVariant(user.role)" class="text-xs flex items-center gap-1">
                    <component :is="getRoleIcon(user.role)" class="w-3 h-3" />
                    {{ formatRole(user.role) }}
                  </VBadge>
                  <div class="text-xs text-gray-500 flex items-center gap-1">
                    <component :is="getDepartmentIcon(user.department || 'construction')" class="w-3 h-3" />
                    {{ formatDepartment(user.department || 'construction') }}
                  </div>
                </div>
              </td>
              
              <!-- Status -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <VBadge :variant="user.email_verified_at ? 'success' : 'warning'" class="text-xs">
                    <component :is="user.email_verified_at ? CheckCircle : Clock" class="w-3 h-3 mr-1" />
                    {{ user.email_verified_at ? 'Verified' : 'Pending' }}
                  </VBadge>
                </div>
              </td>
              
              <!-- Joined Date -->
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ formatDate(user.created_at) }}</div>
                <div class="text-xs text-gray-500">{{ getRelativeTime(user.created_at) }}</div>
              </td>
              
              <!-- Actions -->
              <td class="px-6 py-4 text-right" @click.stop>
                <VButton variant="outline" size="sm" @click.stop="handleViewUser(user)">
                  <Edit class="h-4 w-4 mr-1" />
                  Edit
                </VButton>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div class="flex items-center justify-between p-4 border-t border-gray-200">
        <div class="text-sm text-gray-600">
          Showing {{ startIndex }}-{{ endIndex }} of {{ totalUsers }}
        </div>
        <div class="flex items-center gap-2">
          <VButton 
            variant="outline"
            :disabled="currentPage === 1"
            @click="handlePageChange(currentPage - 1)"
            class="text-sm px-3 py-1"
          >
            Previous
          </VButton>
          <span class="px-2 text-sm text-gray-600">
            Page {{ currentPage }} of {{ totalPages }}
          </span>
          <VButton 
            variant="outline"
            :disabled="currentPage === totalPages"
            @click="handlePageChange(currentPage + 1)"
            class="text-sm px-3 py-1"
          >
            Next
          </VButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useUsers } from '../composables/useUsers';
import { useUserPermissions } from '../composables/useUserPermissions';
import { VButton, VCard, VInput, VBadge } from '@/components/ui';
import { Plus, Search, Edit, Trash, CheckCircle, Clock, X, MoreVertical, Mail, Shield, UserCheck, Users, Check, ArrowUpDown, Crown, ClipboardList, HardHat, Hammer, Building2, Settings, ShieldCheck, Briefcase, Package } from 'lucide-vue-next';
import type { UserListItem } from '../types/users.types';

// ==================== COMPOSABLES ====================
const router = useRouter();
const { 
  users,
  statistics, 
  loading,
  error,
  loadUsers,
  deleteUser,
  updateUser
} = useUsers();

const { hasPermission, canEditUser, canDeleteUser } = useUserPermissions();

// ==================== STATE ====================
const searchTerm = ref('');
const selectedRole = ref('');
const selectedVerification = ref('');
const selectedDepartment = ref('');
const sortBy = ref('created_at');
const sortDirection = ref<'asc' | 'desc'>('desc');
const currentPage = ref(1);
const pageSize = ref(50);

// ==================== COMPUTED ====================
const canCreateUsers = computed(() => hasPermission('canManageUsers'));

const hasActiveFilters = computed(() => {
  return !!(searchTerm.value || selectedRole.value || selectedVerification.value || selectedDepartment.value);
});

const filteredUsers = computed(() => {
  let filtered = [...users.value];
  
  // Search filter
  if (searchTerm.value) {
    const search = searchTerm.value.toLowerCase();
    filtered = filtered.filter(user => 
      user.name.toLowerCase().includes(search) ||
      user.email.toLowerCase().includes(search) ||
      (user.department && user.department.toLowerCase().includes(search))
    );
  }
  
  // Role filter
  if (selectedRole.value) {
    filtered = filtered.filter(user => user.role === selectedRole.value);
  }
  
  // Verification filter
  if (selectedVerification.value !== '') {
    const isVerified = selectedVerification.value === 'true';
    filtered = filtered.filter(user => 
      isVerified ? !!user.email_verified_at : !user.email_verified_at
    );
  }
  
  // Department filter
  if (selectedDepartment.value) {
    filtered = filtered.filter(user => user.department === selectedDepartment.value);
  }
  
  return filtered;
});

const totalUsers = computed(() => filteredUsers.value.length);
const totalPages = computed(() => Math.ceil(totalUsers.value / pageSize.value));

const startIndex = computed(() => {
  return totalUsers.value === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1;
});

const endIndex = computed(() => {
  return Math.min(currentPage.value * pageSize.value, totalUsers.value);
});

const displayedUsers = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredUsers.value.slice(start, end);
});

// ==================== METHODS ====================

/**
 * Get department icon
 */
const getDepartmentIcon = (department: string) => {
  const icons: { [key: string]: any } = {
    construction: Building2,
    engineering: Settings,
    safety: ShieldCheck,
    management: Briefcase,
    logistics: Package
  };
  return icons[department] || ClipboardList;
};

/**
 * Get relative time
 */
const getRelativeTime = (dateString: string): string => {
  const date = new Date(dateString);
  const now = new Date();
  const diffInMs = now.getTime() - date.getTime();
  const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24));
  
  if (diffInDays === 0) {
    return 'Today';
  } else if (diffInDays === 1) {
    return 'Yesterday';
  } else if (diffInDays < 7) {
    return `${diffInDays} days ago`;
  } else if (diffInDays < 30) {
    const weeks = Math.floor(diffInDays / 7);
    return `${weeks} week${weeks > 1 ? 's' : ''} ago`;
  } else if (diffInDays < 365) {
    const months = Math.floor(diffInDays / 30);
    return `${months} month${months > 1 ? 's' : ''} ago`;
  } else {
    const years = Math.floor(diffInDays / 365);
    return `${years} year${years > 1 ? 's' : ''} ago`;
  }
};

/**
 * Format role display
 */
const formatRole = (role: string): string => {
  const roleMap: { [key: string]: string } = {
    admin: 'Administrator',
    project_manager: 'Project Manager',
    supervisor: 'Supervisor',
    field_worker: 'Field Worker'
  };
  return roleMap[role] || role;
};

/**
 * Format department display
 */
const formatDepartment = (department: string): string => {
  const departmentMap: { [key: string]: string } = {
    construction: 'Construction',
    engineering: 'Engineering',
    safety: 'Safety',
    management: 'Management',
    logistics: 'Logistics'
  };
  return departmentMap[department] || department;
};

/**
 * Get role icon
 */
const getRoleIcon = (role: string) => {
  const icons: { [key: string]: any } = {
    admin: Crown,
    project_manager: ClipboardList,
    supervisor: HardHat,
    field_worker: Hammer
  };
  return icons[role] || UserCheck;
};

/**
 * Get role badge variant
 */
const getRoleVariant = (role: string): string => {
  const variants: { [key: string]: string } = {
    admin: 'destructive',
    project_manager: 'default',
    supervisor: 'secondary',
    field_worker: 'outline'
  };
  return variants[role] || 'outline';
};

/**
 * Format date
 */
const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString();
};

/**
 * Clear all filters
 */
const clearFilters = (): void => {
  searchTerm.value = '';
  selectedRole.value = '';
  selectedVerification.value = '';
  selectedDepartment.value = '';
  currentPage.value = 1;
};

/**
 * Handle page change
 */
const handlePageChange = (page: number): void => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

/**
 * Handle view user
 */
const handleViewUser = (user: UserListItem): void => {
  router.push(`/users/${user.id}`);
};

/**
 * Handle create user
 */
const handleCreateUser = (): void => {
  router.push('/users/create');
};

/**
 * Handle edit user
 */
const handleEditUser = (user: UserListItem): void => {
  router.push(`/users/${user.id}/edit`);
};

/**
 * Handle delete user
 */
const handleDeleteUser = async (user: UserListItem): Promise<void> => {
  if (!confirm(`Are you sure you want to remove ${user.name} from the team?`)) {
    return;
  }

  try {
    await deleteUser(user.id);
    // Reload users list
    await loadUsers();
  } catch (error) {
    console.error('Failed to delete user:', error);
    // Show error message (would use toast in real implementation)
  }
};

// ==================== LIFECYCLE ====================
onMounted(async () => {
  await loadUsers();
});
</script>