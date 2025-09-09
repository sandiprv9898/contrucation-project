<template>
  <div class="space-y-6">
    <!-- Enhanced Header with Stats and Theme -->
    <VCard variant="elevated" class="border-l-4 border-orange-500">
      <template #header>
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
              <Users class="w-6 h-6 text-orange-600" />
              Team Members
            </h1>
            <p class="text-gray-600 mt-1">Manage your construction team and their roles</p>
          </div>
          
          <!-- Enhanced Stats with Icons -->
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 rounded-lg">
              <Users class="w-4 h-4 text-blue-600" />
              <div>
                <div class="font-semibold text-blue-900">{{ statistics.total }}</div>
                <div class="text-blue-600 text-xs">Total</div>
              </div>
            </div>
            <div class="flex items-center gap-2 px-3 py-2 bg-green-50 rounded-lg">
              <UserCheck class="w-4 h-4 text-green-600" />
              <div>
                <div class="font-semibold text-green-900">{{ statistics.active }}</div>
                <div class="text-green-600 text-xs">Active</div>
              </div>
            </div>
            <div class="flex items-center gap-2 px-3 py-2 bg-purple-50 rounded-lg">
              <CheckCircle class="w-4 h-4 text-purple-600" />
              <div>
                <div class="font-semibold text-purple-900">{{ statistics.verified }}</div>
                <div class="text-purple-600 text-xs">Verified</div>
              </div>
            </div>
            <div class="flex items-center gap-2 px-3 py-2 bg-orange-50 rounded-lg">
              <Plus class="w-4 h-4 text-orange-600" />
              <div>
                <div class="font-semibold text-orange-900">{{ statistics.thisMonth }}</div>
                <div class="text-orange-600 text-xs">This Month</div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </VCard>

    <!-- Advanced Data Management -->
    <VCard>
      <template #content>
        <!-- Enhanced Filter Bar -->
        <VFilterBar
          title="User Management Filters"
          searchable
          search-placeholder="Search users by name, email, phone, or department..."
          :filters="filterDefinitions"
          show-clear-button
          show-active-filters
          layout="horizontal"
          show-toggle
          @search-change="handleSearchChange"
          @filter-change="handleFilterChange"
          @clear-filters="handleClearFilters"
        >
          <template #actions="{ size }">
            <VButton
              variant="outline"
              :size="size"
              @click="exportUsers"
            >
              <Download class="w-4 h-4 mr-1" />
              Export
            </VButton>
            <VButton
              variant="outline"
              :size="size"
              @click="showBulkActions = !showBulkActions"
            >
              <MoreVertical class="w-4 h-4 mr-1" />
              Bulk Actions
            </VButton>
            <VButton
              v-if="canCreateUsers"
              :size="size"
              @click="handleCreateUser"
            >
              <UserPlus class="w-4 h-4 mr-1" />
              Add User
            </VButton>
          </template>
        </VFilterBar>

        <!-- Bulk Actions Panel -->
        <div v-if="showBulkActions" class="border-t border-gray-200 p-4 bg-gray-50">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <span class="text-sm text-gray-600">
                {{ selectedUsers.length }} users selected
              </span>
              <VButton
                variant="outline"
                size="sm"
                @click="selectAllUsers"
                v-if="selectedUsers.length < filteredUsers.length"
              >
                Select All ({{ filteredUsers.length }})
              </VButton>
              <VButton
                variant="outline"
                size="sm"
                @click="clearSelection"
                v-else
              >
                Clear Selection
              </VButton>
            </div>
            <div class="flex items-center gap-2">
              <VButton
                variant="outline"
                size="sm"
                :disabled="selectedUsers.length === 0"
                @click="bulkExport"
              >
                <Download class="w-4 h-4 mr-1" />
                Export Selected
              </VButton>
              <VButton
                variant="destructive"
                size="sm"
                :disabled="selectedUsers.length === 0"
                @click="bulkDelete"
              >
                <Trash class="w-4 h-4 mr-1" />
                Delete Selected
              </VButton>
            </div>
          </div>
        </div>


        <!-- Advanced Data Table -->
        <VDataTable
          :columns="tableColumns"
          :data="filteredUsers"
          :loading="loading"
          :searchable="false"
          :sortable="true"
          :resizable="true"
          :exportable="true"
          :paginated="true"
          :perPage="pageSize"
          :selectable="showBulkActions"
          :clickableRows="true"
          :emptyTitle="'No team members found'"
          :emptyMessage="hasActiveFilters ? 'No users match your current filters.' : 'Get started by adding your first team member.'"
          :emptyIcon="Users"
          @row-click="handleViewUser"
          @selection-change="selectedUsers = $event"
          class="border-0 shadow-none"
        >
          <template #cell-member="{ row }">
            <div class="flex items-center gap-4">
              <div class="relative">
                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center shadow-sm">
                  <span class="text-sm font-medium text-white">
                    {{ row.name.split(' ').map(n => n[0]).join('').toUpperCase() }}
                  </span>
                </div>
                <div v-if="row.email_verified_at" class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full flex items-center justify-center ring-2 ring-white">
                  <CheckCircle class="w-2.5 h-2.5 text-white" />
                </div>
                <div v-else class="absolute -top-1 -right-1 w-4 h-4 bg-yellow-500 rounded-full flex items-center justify-center ring-2 ring-white">
                  <Clock class="w-2.5 h-2.5 text-white" />
                </div>
              </div>
              <div class="min-w-0 flex-1">
                <div class="text-sm font-medium text-gray-900 truncate">
                  {{ row.name }}
                </div>
                <div class="text-sm text-gray-500 truncate flex items-center gap-1">
                  <Mail class="w-3 h-3" />
                  {{ row.email }}
                </div>
                <div v-if="row.phone" class="text-xs text-gray-400 truncate flex items-center gap-1 mt-0.5">
                  <Phone class="w-3 h-3" />
                  {{ row.phone }}
                </div>
              </div>
            </div>
          </template>

          <template #cell-role="{ row }">
            <div class="space-y-2">
              <VBadge :variant="getRoleVariant(row.role)" class="text-xs flex items-center gap-1 w-fit">
                <component :is="getRoleIcon(row.role)" class="w-3 h-3" />
                {{ formatRole(row.role) }}
              </VBadge>
              <div class="text-xs text-gray-500 flex items-center gap-1">
                <component :is="getDepartmentIcon(row.department || 'construction')" class="w-3 h-3" />
                {{ formatDepartment(row.department || 'construction') }}
              </div>
            </div>
          </template>

          <template #cell-status="{ row }">
            <div class="space-y-1">
              <VBadge :variant="row.email_verified_at ? 'success' : 'warning'" class="text-xs w-fit">
                <component :is="row.email_verified_at ? CheckCircle : Clock" class="w-3 h-3 mr-1" />
                {{ row.email_verified_at ? 'Verified' : 'Pending' }}
              </VBadge>
              <div class="text-xs text-gray-500">
                Last login: {{ row.last_login_at ? formatDate(row.last_login_at) : 'Never' }}
              </div>
            </div>
          </template>

          <template #cell-joined="{ row }">
            <div class="text-sm text-gray-900">{{ formatDate(row.created_at) }}</div>
            <div class="text-xs text-gray-500">{{ getRelativeTime(row.created_at) }}</div>
          </template>

          <template #cell-actions="{ row }">
            <div class="flex items-center gap-1" @click.stop>
              <VButton variant="ghost" size="sm" @click.stop="handleViewUser(row)" class="h-8 w-8 p-0">
                <Eye class="h-4 w-4" />
              </VButton>
              <VButton variant="ghost" size="sm" @click.stop="handleEditUser(row)" class="h-8 w-8 p-0">
                <Edit class="h-4 w-4" />
              </VButton>
              <VButton 
                v-if="canDeleteUser(row)"
                variant="ghost" 
                size="sm" 
                @click.stop="handleDeleteUser(row)"
                class="h-8 w-8 p-0 text-red-600 hover:text-red-700 hover:bg-red-50"
              >
                <Trash class="h-4 w-4" />
              </VButton>
              <VButton variant="ghost" size="sm" @click.stop="showUserMenu(row)" class="h-8 w-8 p-0">
                <MoreVertical class="h-4 w-4" />
              </VButton>
            </div>
          </template>
        </VDataTable>
      </template>
    </VCard>

    <!-- User Action Modal (for bulk operations, etc.) -->
    <VModal v-if="showUserModal" @close="showUserModal = false">
      <template #header>
        <h3 class="text-lg font-semibold">User Actions</h3>
      </template>
      <template #content>
        <div class="space-y-4">
          <p class="text-gray-600">Choose an action to perform on the selected user(s):</p>
          <!-- Modal content would go here -->
        </div>
      </template>
    </VModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useUsers } from '../composables/useUsers';
import { useUserPermissions } from '../composables/useUserPermissions';
import { VButton, VCard, VInput, VBadge, VDataTable, VFilterBar, VModal } from '@/components/ui';
// FontAwesome icon components for UserList
const Plus = { template: '<font-awesome-icon icon="plus" />' };
const Search = { template: '<font-awesome-icon icon="search" />' };
const Edit = { template: '<font-awesome-icon icon="edit" />' };
const Trash = { template: '<font-awesome-icon icon="trash" />' };
const CheckCircle = { template: '<font-awesome-icon icon="check-circle" />' };
const Clock = { template: '<font-awesome-icon icon="clock" />' };
const X = { template: '<font-awesome-icon icon="times" />' };
const MoreVertical = { template: '<font-awesome-icon icon="ellipsis-v" />' };
const Mail = { template: '<font-awesome-icon icon="envelope" />' };
const Shield = { template: '<font-awesome-icon icon="shield" />' };
const UserCheck = { template: '<font-awesome-icon icon="user" />' };
const Users = { template: '<font-awesome-icon icon="users" />' };
const Check = { template: '<font-awesome-icon icon="check" />' };
const ArrowUpDown = { template: '<font-awesome-icon icon="sort" />' };
const Crown = { template: '<font-awesome-icon icon="crown" />' };
const ClipboardList = { template: '<font-awesome-icon icon="clipboard" />' };
const HardHat = { template: '<font-awesome-icon icon="hard-hat" />' };
const Hammer = { template: '<font-awesome-icon icon="hammer" />' };
const Building2 = { template: '<font-awesome-icon icon="building" />' };
const Settings = { template: '<font-awesome-icon icon="cog" />' };
const ShieldCheck = { template: '<font-awesome-icon icon="shield" />' };
const Briefcase = { template: '<font-awesome-icon icon="briefcase" />' };
const Package = { template: '<font-awesome-icon icon="box" />' };
const Download = { template: '<font-awesome-icon icon="download" />' };
const UserPlus = { template: '<font-awesome-icon icon="user-plus" />' };
const Eye = { template: '<font-awesome-icon icon="eye" />' };
const Phone = { template: '<font-awesome-icon icon="phone" />' };
import { formatDate } from '@/utils/date';
import type { UserListItem } from '../types/users.types';
import type { DataTableColumn } from '@/components/ui/VDataTable.vue';

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
const activeFilters = ref({});
const pageSize = ref(25);
const selectedUsers = ref<UserListItem[]>([]);
const showBulkActions = ref(false);
const showUserModal = ref(false);

// Table Configuration
const tableColumns = computed<DataTableColumn[]>(() => [
  {
    key: 'member',
    label: 'Team Member',
    sortable: true,
    resizable: true,
    width: 320,
    sticky: 'left'
  },
  {
    key: 'role',
    label: 'Role & Department',
    sortable: true,
    resizable: true,
    width: 220
  },
  {
    key: 'status',
    label: 'Status & Activity',
    sortable: true,
    resizable: true,
    width: 160,
    filterable: true
  },
  {
    key: 'joined',
    label: 'Joined Date',
    sortable: true,
    resizable: true,
    width: 140
  },
  {
    key: 'actions',
    label: 'Actions',
    sortable: false,
    resizable: false,
    width: 140,
    sticky: 'right'
  }
]);

// Filter Definitions
const filterDefinitions = computed(() => [
  {
    key: 'role',
    label: 'Role',
    type: 'select' as const,
    placeholder: 'All Roles',
    options: [
      { label: 'Administrator', value: 'admin' },
      { label: 'Project Manager', value: 'project_manager' },
      { label: 'Supervisor', value: 'supervisor' },
      { label: 'Field Worker', value: 'field_worker' }
    ]
  },
  {
    key: 'verification',
    label: 'Verification Status',
    type: 'select' as const,
    placeholder: 'All Users',
    options: [
      { label: 'Verified', value: 'verified' },
      { label: 'Pending', value: 'pending' }
    ]
  },
  {
    key: 'department',
    label: 'Department',
    type: 'multi-select' as const,
    placeholder: 'All Departments',
    options: [
      { label: 'Construction', value: 'construction' },
      { label: 'Engineering', value: 'engineering' },
      { label: 'Safety & Compliance', value: 'safety' },
      { label: 'Project Management', value: 'management' },
      { label: 'Logistics & Supply', value: 'logistics' },
      { label: 'Equipment Maintenance', value: 'maintenance' },
      { label: 'Quality Assurance', value: 'quality' }
    ]
  },
  {
    key: 'joinedDate',
    label: 'Joined Date Range',
    type: 'date-range' as const
  },
  {
    key: 'lastLogin',
    label: 'Last Login Range',
    type: 'date-range' as const
  }
]);

// ==================== COMPUTED ====================
const canCreateUsers = computed(() => hasPermission('canManageUsers'));

const hasActiveFilters = computed(() => {
  return !!(searchTerm.value || Object.keys(activeFilters.value).length > 0);
});

const filteredUsers = computed(() => {
  let filtered = [...users.value];
  
  // Search filter
  if (searchTerm.value) {
    const search = searchTerm.value.toLowerCase();
    filtered = filtered.filter(user => 
      user.name.toLowerCase().includes(search) ||
      user.email.toLowerCase().includes(search) ||
      (user.department && user.department.toLowerCase().includes(search)) ||
      (user.phone && user.phone.toLowerCase().includes(search))
    );
  }
  
  // Apply additional filters
  filtered = applyFilters(filtered);
  
  return filtered;
});

// ==================== METHODS ====================

/**
 * Apply filters to users
 */
const applyFilters = (users: UserListItem[]): UserListItem[] => {
  let filtered = [...users];
  
  // Apply role filter
  if (activeFilters.value.role) {
    filtered = filtered.filter(user => user.role === activeFilters.value.role);
  }
  
  // Apply verification filter
  if (activeFilters.value.verification) {
    const isVerified = activeFilters.value.verification === 'verified';
    filtered = filtered.filter(user => 
      isVerified ? !!user.email_verified_at : !user.email_verified_at
    );
  }
  
  // Apply department filter (multi-select)
  if (activeFilters.value.department && Array.isArray(activeFilters.value.department) && activeFilters.value.department.length > 0) {
    filtered = filtered.filter(user => 
      activeFilters.value.department.includes(user.department)
    );
  }
  
  // Apply date range filters
  if (activeFilters.value.joinedDate?.from || activeFilters.value.joinedDate?.to) {
    const fromDate = activeFilters.value.joinedDate.from ? new Date(activeFilters.value.joinedDate.from) : null;
    const toDate = activeFilters.value.joinedDate.to ? new Date(activeFilters.value.joinedDate.to) : null;
    
    filtered = filtered.filter(user => {
      const userDate = new Date(user.created_at);
      if (fromDate && userDate < fromDate) return false;
      if (toDate && userDate > toDate) return false;
      return true;
    });
  }
  
  return filtered;
};

/**
 * Get department icon
 */
const getDepartmentIcon = (department: string) => {
  const icons: { [key: string]: any } = {
    construction: Building2,
    engineering: Settings,
    safety: ShieldCheck,
    management: Briefcase,
    logistics: Package,
    maintenance: Settings,
    quality: CheckCircle
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
    safety: 'Safety & Compliance',
    management: 'Project Management',
    logistics: 'Logistics & Supply',
    maintenance: 'Equipment Maintenance',
    quality: 'Quality Assurance'
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
 * Handle search change
 */
const handleSearchChange = (search: string): void => {
  searchTerm.value = search;
};

/**
 * Handle filter change
 */
const handleFilterChange = (filters: Record<string, any>): void => {
  activeFilters.value = filters;
  searchTerm.value = filters.search || '';
};

/**
 * Handle clear filters
 */
const handleClearFilters = (): void => {
  activeFilters.value = {};
  searchTerm.value = '';
};

/**
 * Export users data
 */
const exportUsers = (): void => {
  const dataToExport = filteredUsers.value.map(user => ({
    name: user.name,
    email: user.email,
    phone: user.phone || '',
    role: formatRole(user.role),
    department: formatDepartment(user.department || 'construction'),
    status: user.email_verified_at ? 'Verified' : 'Pending',
    joined: formatDate(user.created_at),
    lastLogin: user.last_login_at ? formatDate(user.last_login_at) : 'Never'
  }));
  
  // Create CSV content
  const headers = ['Name', 'Email', 'Phone', 'Role', 'Department', 'Status', 'Joined', 'Last Login'];
  const csvContent = [
    headers.join(','),
    ...dataToExport.map(row => 
      Object.values(row).map(value => `"${value}"`).join(',')
    )
  ].join('\n');
  
  // Download CSV
  const blob = new Blob([csvContent], { type: 'text/csv' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = `team-members-${new Date().toISOString().split('T')[0]}.csv`;
  link.click();
  URL.revokeObjectURL(url);
};

/**
 * Handle view user
 */
const handleViewUser = (user: UserListItem): void => {
  router.push(`/app/users/${user.id}`);
};

/**
 * Handle create user
 */
const handleCreateUser = (): void => {
  router.push('/app/users/create');
};

/**
 * Handle edit user
 */
const handleEditUser = (user: UserListItem): void => {
  router.push(`/app/users/${user.id}/edit`);
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
    await loadUsers();
  } catch (error) {
    console.error('Failed to delete user:', error);
  }
};

/**
 * Show user context menu
 */
const showUserMenu = (user: UserListItem): void => {
  // Implementation for showing user context menu
  console.log('Show menu for user:', user.name);
};

/**
 * Select all users
 */
const selectAllUsers = (): void => {
  selectedUsers.value = [...filteredUsers.value];
};

/**
 * Clear user selection
 */
const clearSelection = (): void => {
  selectedUsers.value = [];
};

/**
 * Bulk export selected users
 */
const bulkExport = (): void => {
  // Export only selected users
  console.log('Bulk export:', selectedUsers.value.length, 'users');
};

/**
 * Bulk delete selected users
 */
const bulkDelete = async (): Promise<void> => {
  if (!confirm(`Are you sure you want to delete ${selectedUsers.value.length} users?`)) {
    return;
  }
  
  try {
    // Implementation for bulk delete
    for (const user of selectedUsers.value) {
      await deleteUser(user.id);
    }
    await loadUsers();
    clearSelection();
  } catch (error) {
    console.error('Failed to bulk delete users:', error);
  }
};

// ==================== LIFECYCLE ====================
onMounted(async () => {
  await loadUsers();
});
</script>