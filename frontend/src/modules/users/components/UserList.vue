<template>
  <div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Team Members</h1>
          <p class="text-gray-600 mt-1">Manage your construction team and their roles</p>
        </div>
        
        <!-- Quick Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
          <div class="text-center lg:text-left">
            <div class="text-2xl font-bold text-gray-900">{{ statistics.total }}</div>
            <div class="text-sm text-gray-500">Total Members</div>
          </div>
          <div class="text-center lg:text-left">
            <div class="text-2xl font-bold text-green-600">{{ statistics.verified }}</div>
            <div class="text-sm text-gray-500">Verified</div>
          </div>
          <div class="text-center lg:text-left">
            <div class="text-2xl font-bold text-orange-600">{{ statistics.admins }}</div>
            <div class="text-sm text-gray-500">Administrators</div>
          </div>
          <div class="text-center lg:text-left">
            <div class="text-2xl font-bold text-blue-600">{{ thisMonthUsers }}</div>
            <div class="text-sm text-gray-500">New This Month</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Enhanced Filters Section -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
      <div class="flex flex-col lg:flex-row gap-4">
        <!-- Search -->
        <div class="flex-1 lg:max-w-md">
          <label class="block text-sm font-medium text-gray-700 mb-2">Search Members</label>
          <div class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
            <VInput 
              v-model="searchTerm"
              placeholder="Search by name, email, or department..."
              class="pl-10 h-11"
              @input="handleSearch"
            />
          </div>
        </div>
        
        <!-- Role Filter -->
        <div class="lg:w-48">
          <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
          <select 
            v-model="selectedRole"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            @change="handleFilterChange"
          >
            <option value="">All Roles</option>
            <option value="admin">👑 Administrator</option>
            <option value="project_manager">📋 Project Manager</option>
            <option value="supervisor">👷 Supervisor</option>
            <option value="field_worker">🔨 Field Worker</option>
          </select>
        </div>
        
        <!-- Status Filter -->
        <div class="lg:w-48">
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <select 
            v-model="selectedVerification"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            @change="handleFilterChange"
          >
            <option value="">All Statuses</option>
            <option value="true">✅ Verified</option>
            <option value="false">⏳ Pending Verification</option>
          </select>
        </div>
        
        <!-- Department Filter -->
        <div class="lg:w-48">
          <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
          <select 
            v-model="selectedDepartment"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            @change="handleFilterChange"
          >
            <option value="">All Departments</option>
            <option value="construction">🏗️ Construction</option>
            <option value="engineering">⚙️ Engineering</option>
            <option value="safety">🦺 Safety</option>
            <option value="management">💼 Management</option>
            <option value="logistics">📦 Logistics</option>
          </select>
        </div>
        
        <!-- Actions -->
        <div class="flex flex-col lg:justify-end gap-2">
          <VButton 
            v-if="canCreateUsers"
            @click="handleCreateUser"
            class="bg-orange-600 hover:bg-orange-700 text-white"
          >
            <Plus class="mr-2 h-4 w-4" />
            Add Member
          </VButton>
          <VButton 
            variant="outline" 
            @click="clearFilters"
            :disabled="!hasActiveFilters"
          >
            <X class="mr-2 h-4 w-4" />
            Clear Filters
          </VButton>
        </div>
      </div>
      
      <!-- Active Filters Display -->
      <div v-if="hasActiveFilters" class="mt-4 flex flex-wrap gap-2">
        <span class="text-sm text-gray-600">Active filters:</span>
        <span v-if="searchTerm" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
          Search: "{{ searchTerm }}"
          <button @click="searchTerm = ''" class="ml-1 text-blue-600 hover:text-blue-800">
            <X class="h-3 w-3" />
          </button>
        </span>
        <span v-if="selectedRole" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
          Role: {{ getRoleLabel(selectedRole) }}
          <button @click="selectedRole = ''" class="ml-1 text-green-600 hover:text-green-800">
            <X class="h-3 w-3" />
          </button>
        </span>
        <span v-if="selectedVerification" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
          Status: {{ selectedVerification === 'true' ? 'Verified' : 'Pending' }}
          <button @click="selectedVerification = ''" class="ml-1 text-yellow-600 hover:text-yellow-800">
            <X class="h-3 w-3" />
          </button>
        </span>
        <span v-if="selectedDepartment" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
          Department: {{ selectedDepartment }}
          <button @click="selectedDepartment = ''" class="ml-1 text-purple-600 hover:text-purple-800">
            <X class="h-3 w-3" />
          </button>
        </span>
      </div>
    </div>

    <!-- Results Section -->
    <div class="bg-white rounded-lg shadow-sm border">
      <!-- Results Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <h3 class="text-lg font-medium text-gray-900">
              {{ filteredUsers.length }} Member{{ filteredUsers.length !== 1 ? 's' : '' }}
              <span v-if="hasActiveFilters" class="text-gray-500">(filtered)</span>
            </h3>
            <div class="text-sm text-gray-500">
              Showing {{ startIndex }}-{{ endIndex }} of {{ totalUsers }}
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Data Table -->
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left px-6 py-4 font-semibold text-gray-900 cursor-pointer hover:bg-gray-100" @click="handleSort('name')">
                <div class="flex items-center gap-2">
                  Member
                  <ArrowUpDown class="h-4 w-4 text-gray-400" />
                </div>
              </th>
              <th class="text-left px-6 py-4 font-semibold text-gray-900">Role</th>
              <th class="text-left px-6 py-4 font-semibold text-gray-900">Department</th>
              <th class="text-left px-6 py-4 font-semibold text-gray-900">Status</th>
              <th class="text-left px-6 py-4 font-semibold text-gray-900 cursor-pointer hover:bg-gray-100" @click="handleSort('created_at')">
                <div class="flex items-center gap-2">
                  Joined
                  <ArrowUpDown class="h-4 w-4 text-gray-400" />
                </div>
              </th>
              <th class="text-right px-6 py-4 font-semibold text-gray-900">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-if="loading">
              <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                <div class="flex items-center justify-center">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-600"></div>
                  <span class="ml-3">Loading team members...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="displayedUsers.length === 0">
              <td colspan="6" class="px-6 py-12 text-center">
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
                    <img
                      v-if="user.avatar_url"
                      :src="user.avatar_url"
                      :alt="user.name"
                      class="h-12 w-12 rounded-full object-cover"
                    >
                    <div
                      v-else
                      class="h-12 w-12 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-bold text-lg"
                    >
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div v-if="user.email_verified_at" class="absolute -bottom-1 -right-1 h-4 w-4 bg-green-500 rounded-full border-2 border-white">
                      <Check class="h-2 w-2 text-white ml-0.5 mt-0.5" />
                    </div>
                  </div>
                  <div>
                    <div class="font-semibold text-gray-900">{{ user.name }}</div>
                    <div class="text-sm text-gray-600">{{ user.email }}</div>
                    <div v-if="user.department" class="text-xs text-gray-500 mt-1">{{ user.department }}</div>
                  </div>
                </div>
              </td>
              
              <!-- Role -->
              <td class="px-6 py-4">
                <VBadge 
                  :variant="getRoleVariant(user.role)"
                  class="font-medium"
                >
                  {{ getRoleLabel(user.role) }}
                </VBadge>
              </td>
              
              <!-- Department -->
              <td class="px-6 py-4">
                <div v-if="user.department" class="flex items-center gap-2">
                  <span class="text-lg">{{ getDepartmentIcon(user.department) }}</span>
                  <span class="text-sm font-medium text-gray-900 capitalize">{{ user.department }}</span>
                </div>
                <span v-else class="text-sm text-gray-400">Not specified</span>
              </td>
              
              <!-- Status -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <VBadge 
                    :variant="user.email_verified_at ? 'success' : 'warning'"
                    class="text-xs"
                  >
                    <CheckCircle v-if="user.email_verified_at" class="mr-1 h-3 w-3" />
                    <Clock v-else class="mr-1 h-3 w-3" />
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
                <VDropdownMenu align="end">
                  <template #trigger>
                    <VButton variant="outline" class="h-8 w-8 p-0">
                      <MoreVertical class="h-4 w-4" />
                    </VButton>
                  </template>
                  <template #content>
                    <VDropdownMenuItem @click="handleViewUser(user)">
                      <UserCheck class="mr-2 h-4 w-4" />
                      View Profile
                    </VDropdownMenuItem>
                    <VDropdownMenuItem 
                      v-if="canEditUser(user)"
                      @click="handleEditUser(user)"
                    >
                      <Edit class="mr-2 h-4 w-4" />
                      Edit Details
                    </VDropdownMenuItem>
                    <VDropdownMenuItem 
                      v-if="!user.email_verified_at && canEditUser(user)"
                      @click="handleResendVerification(user)"
                    >
                      <Mail class="mr-2 h-4 w-4" />
                      Resend Verification
                    </VDropdownMenuItem>
                    <VDropdownMenuItem 
                      v-if="canEditUser(user)"
                      @click="handleChangeRole(user)"
                    >
                      <Shield class="mr-2 h-4 w-4" />
                      Change Role
                    </VDropdownMenuItem>
                    <div class="border-t border-gray-100 my-1" v-if="canDeleteUser(user)"></div>
                    <VDropdownMenuItem 
                      v-if="canDeleteUser(user)"
                      variant="destructive"
                      @click="handleDeleteUser(user)"
                    >
                      <Trash class="mr-2 h-4 w-4" />
                      Remove Member
                    </VDropdownMenuItem>
                  </template>
                </VDropdownMenu>
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
import { VButton, VCard, VInput, VBadge, VDropdownMenu, VDropdownMenuItem } from '@/components/ui';
import { Plus, Search, Edit, Trash, CheckCircle, Clock, X, MoreVertical, Mail, Shield, UserCheck, Users, Check, ArrowUpDown } from 'lucide-vue-next';
import type { UserListItem } from '../types/users.types';

// ==================== COMPOSABLES ====================
const router = useRouter();
const { 
  users, 
  statistics, 
  loading,
  loadUsers, 
  deleteUser
} = useUsers();

const { 
  hasPermission,
  canEditUser,
  canDeleteUser
} = useUserPermissions();

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

const displayedUsers = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredUsers.value.slice(start, end);
});

const totalUsers = computed(() => filteredUsers.value.length);

const totalPages = computed(() => Math.ceil(totalUsers.value / pageSize.value));

const startIndex = computed(() => 
  filteredUsers.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1
);

const endIndex = computed(() => 
  Math.min(currentPage.value * pageSize.value, totalUsers.value)
);

const thisMonthUsers = computed(() => {
  const thisMonth = new Date();
  thisMonth.setDate(1);
  thisMonth.setHours(0, 0, 0, 0);
  
  return users.value.filter((user: any) => 
    new Date(user.created_at) >= thisMonth
  ).length;
});

// ==================== METHODS ====================
/**
 * Simple date formatter
 */
const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString();
};

/**
 * Get role label
 */
const getRoleLabel = (role: string): string => {
  const labels: { [key: string]: string } = {
    admin: 'Administrator',
    project_manager: 'Project Manager',
    supervisor: 'Supervisor',
    field_worker: 'Field Worker'
  };
  return labels[role] || role;
};

/**
 * Get role badge variant
 */
const getRoleVariant = (role: string): 'default' | 'outline' | 'success' | 'warning' | 'destructive' => {
  const variants: { [key: string]: 'default' | 'outline' | 'success' | 'warning' | 'destructive' } = {
    admin: 'destructive',
    project_manager: 'default',
    supervisor: 'warning',
    field_worker: 'success'
  };
  return variants[role] || 'default';
};

/**
 * Handle search with debouncing
 */
const handleSearch = () => {
  // Reset to first page when searching
  currentPage.value = 1;
};

/**
 * Handle filter changes
 */
const handleFilterChange = () => {
  currentPage.value = 1;
};

/**
 * Handle sorting
 */
const handleSort = (field: string): void => {
  if (sortBy.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = field;
    sortDirection.value = 'asc';
  }
  
  // Apply sorting to filteredUsers would happen here
  // For now, we'll just track the sort state
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
 * Get department icon
 */
const getDepartmentIcon = (department: string): string => {
  const icons: { [key: string]: string } = {
    construction: '🏗️',
    engineering: '⚙️',
    safety: '🦺',
    management: '💼',
    logistics: '📦'
  };
  return icons[department] || '📋';
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
 * Handle create user
 */
const handleCreateUser = (): void => {
  router.push({ name: 'users.create' });
};

/**
 * Handle view user profile
 */
const handleViewUser = (user: UserListItem): void => {
  router.push({ name: 'users.view', params: { id: user.id } });
};

/**
 * Handle edit user
 */
const handleEditUser = (user: UserListItem): void => {
  router.push({ name: 'users.edit', params: { id: user.id } });
};

/**
 * Handle resend verification email
 */
const handleResendVerification = async (user: UserListItem): Promise<void> => {
  try {
    // This would call the API to resend verification
    console.log(`Resending verification email to ${user.email}`);
    // Show success message
  } catch (error) {
    console.error('Failed to resend verification:', error);
  }
};

/**
 * Handle change user role
 */
const handleChangeRole = (user: UserListItem): void => {
  // This could open a modal or navigate to a role change page
  router.push({ name: 'users.role', params: { id: user.id } });
};

/**
 * Handle delete user
 */
const handleDeleteUser = async (user: UserListItem): Promise<void> => {
  if (!confirm(`Are you sure you want to delete ${user.name}? This action cannot be undone.`)) {
    return;
  }
  
  try {
    await deleteUser(user.id);
    // Show success message (would use toast in real implementation)
    console.log('User deleted successfully');
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