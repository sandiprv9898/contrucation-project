<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
        <p class="text-gray-600">Manage team members and their access permissions</p>
      </div>
    </div>

    <!-- Filter Bar -->
    <VFilterBar
      title="User Filters"
      :filters="userFilters"
      show-active-filters
      @filter-change="handleFilterChange"
      @search-change="handleSearchChange"
    >
      <template #actions>
        <VButton variant="outline" size="sm" @click="exportUsers">
          <Download class="w-4 h-4 mr-1" />
          Export
        </VButton>
      </template>
    </VFilterBar>

    <!-- Users Data Table -->
    <VDataTable
      title="Team Members"
      :data="filteredUsers"
      :columns="userColumns"
      :stats="userStats"
      :loading="loading"
      searchable
      selectable
      @row-click="handleUserClick"
      @selection-change="handleSelectionChange"
    >
      <!-- Actions in header -->
      <template #actions>
        <VButton @click="openCreateUserModal">
          <UserPlus class="w-4 h-4 mr-2" />
          Add User
        </VButton>
      </template>

      <!-- Custom cell for user name with avatar -->
      <template #cell-name="{ row }">
        <div class="flex items-center space-x-3">
          <img
            v-if="row.avatar_url"
            :src="row.avatar_url"
            :alt="row.name"
            class="w-8 h-8 rounded-full"
          />
          <div
            v-else
            class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center"
          >
            <span class="text-orange-600 font-medium text-sm">
              {{ row.name?.charAt(0).toUpperCase() }}
            </span>
          </div>
          <div>
            <div class="font-medium text-gray-900">{{ row.name }}</div>
            <div class="text-sm text-gray-500">{{ row.email }}</div>
          </div>
        </div>
      </template>

      <!-- Custom cell for role -->
      <template #cell-role="{ row }">
        <div class="flex items-center space-x-2">
          <component :is="getRoleIcon(row.role)" class="w-4 h-4 text-gray-500" />
          <VBadge :variant="getRoleVariant(row.role)">
            {{ formatRole(row.role) }}
          </VBadge>
        </div>
      </template>

      <!-- Custom cell for department -->
      <template #cell-department="{ row }">
        <div class="flex items-center space-x-2">
          <component :is="getDepartmentIcon(row.department)" class="w-4 h-4 text-gray-500" />
          <span class="text-sm text-gray-900">{{ row.department || 'Not assigned' }}</span>
        </div>
      </template>

      <!-- Custom cell for status -->
      <template #cell-status="{ row }">
        <VBadge :variant="row.email_verified_at ? 'success' : 'warning'">
          <component :is="row.email_verified_at ? CheckCircle : Clock" class="w-3 h-3 mr-1" />
          {{ row.email_verified_at ? 'Verified' : 'Pending' }}
        </VBadge>
      </template>

      <!-- Custom cell for actions -->
      <template #cell-actions="{ row }">
        <VDropdownMenu>
          <template #trigger>
            <VButton variant="ghost" size="sm">
              <MoreHorizontal class="w-4 h-4" />
            </VButton>
          </template>
          <VDropdownMenuItem @click="editUser(row)">
            <Edit class="w-4 h-4 mr-2" />
            Edit User
          </VDropdownMenuItem>
          <VDropdownMenuItem @click="changeUserRole(row)">
            <UserCog class="w-4 h-4 mr-2" />
            Change Role
          </VDropdownMenuItem>
          <VDropdownMenuItem @click="resetPassword(row)">
            <Key class="w-4 h-4 mr-2" />
            Reset Password
          </VDropdownMenuItem>
          <VDropdownMenuItem @click="deleteUser(row)" class="text-red-600">
            <Trash class="w-4 h-4 mr-2" />
            Delete User
          </VDropdownMenuItem>
        </VDropdownMenu>
      </template>

      <!-- Bulk actions -->
      <template #bulk-actions="{ selected }">
        <VButton variant="outline" size="sm" @click="bulkChangeRole(selected)">
          <UserCog class="w-4 h-4 mr-1" />
          Change Role
        </VButton>
        <VButton variant="outline" size="sm" @click="bulkExport(selected)">
          <Download class="w-4 h-4 mr-1" />
          Export
        </VButton>
        <VButton variant="danger" size="sm" @click="bulkDelete(selected)">
          <Trash class="w-4 h-4 mr-1" />
          Delete
        </VButton>
      </template>
    </VDataTable>

    <!-- Create User Modal -->
    <VModal
      v-model="showCreateModal"
      title="Add New User"
      subtitle="Create a new team member account"
      size="md"
      @confirm="handleCreateUser"
      @cancel="showCreateModal = false"
    >
      <div class="space-y-4">
        <VFormField label="Full Name" required>
          <template #default="{ fieldId, inputClass }">
            <VInput
              :id="fieldId"
              v-model="newUser.name"
              :class="inputClass"
              placeholder="Enter full name"
            />
          </template>
        </VFormField>
        
        <VFormField label="Email Address" required>
          <template #default="{ fieldId, inputClass }">
            <VInput
              :id="fieldId"
              v-model="newUser.email"
              type="email"
              :class="inputClass"
              placeholder="user@company.com"
            />
          </template>
        </VFormField>
        
        <VFormField label="Role" required>
          <template #default="{ fieldId, hasError }">
            <VSelect
              :id="fieldId"
              v-model="newUser.role"
              :items="roleOptions"
              placeholder="Select role"
              :has-error="hasError"
            />
          </template>
        </VFormField>
      </div>
    </VModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, reactive } from 'vue';
import { 
  VButton, VDataTable, VFilterBar, VModal, VFormField, 
  VInput, VSelect, VBadge, VDropdownMenu, VDropdownMenuItem 
} from '@/components/ui';
import {
  UserPlus, Download, Edit, MoreHorizontal, UserCog, Key, Trash,
  Crown, ClipboardList, HardHat, Hammer, CheckCircle, Clock,
  Building2, Settings, ShieldCheck, Briefcase, Package
} from 'lucide-vue-next';

// Sample data - in real app this would come from a store/API
const users = ref([
  {
    id: 1,
    name: 'John Smith',
    email: 'john.smith@construction.com',
    role: 'admin',
    department: 'management',
    email_verified_at: '2024-01-15T10:00:00Z',
    created_at: '2024-01-01T00:00:00Z',
    avatar_url: null
  },
  {
    id: 2,
    name: 'Sarah Johnson',
    email: 'sarah.johnson@construction.com',
    role: 'project_manager',
    department: 'construction',
    email_verified_at: '2024-01-16T10:00:00Z',
    created_at: '2024-01-05T00:00:00Z',
    avatar_url: null
  },
  {
    id: 3,
    name: 'Mike Wilson',
    email: 'mike.wilson@construction.com',
    role: 'supervisor',
    department: 'safety',
    email_verified_at: null,
    created_at: '2024-02-01T00:00:00Z',
    avatar_url: null
  }
]);

// State
const loading = ref(false);
const showCreateModal = ref(false);
const searchQuery = ref('');
const activeFilters = reactive({});

// New user form
const newUser = reactive({
  name: '',
  email: '',
  role: ''
});

// Table configuration
const userColumns = [
  { key: 'name', label: 'User', sortable: true, width: '300px' },
  { key: 'role', label: 'Role', sortable: true, width: '150px' },
  { key: 'department', label: 'Department', sortable: true, width: '150px' },
  { key: 'status', label: 'Status', sortable: true, width: '120px' },
  { key: 'actions', label: 'Actions', width: '100px' }
];

// Filter configuration
const userFilters = [
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
    key: 'department',
    label: 'Department',
    type: 'select' as const,
    placeholder: 'All Departments',
    options: [
      { label: 'Management', value: 'management' },
      { label: 'Construction', value: 'construction' },
      { label: 'Safety', value: 'safety' },
      { label: 'Administration', value: 'administration' }
    ]
  },
  {
    key: 'status',
    label: 'Email Status',
    type: 'select' as const,
    placeholder: 'All Status',
    options: [
      { label: 'Verified', value: 'verified' },
      { label: 'Pending', value: 'pending' }
    ]
  }
];

const roleOptions = [
  { label: 'Administrator', value: 'admin' },
  { label: 'Project Manager', value: 'project_manager' },
  { label: 'Supervisor', value: 'supervisor' },
  { label: 'Field Worker', value: 'field_worker' }
];

// Computed
const filteredUsers = computed(() => {
  let filtered = [...users.value];
  
  // Apply search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(user =>
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query)
    );
  }
  
  // Apply filters
  if (activeFilters.role) {
    filtered = filtered.filter(user => user.role === activeFilters.role);
  }
  
  if (activeFilters.department) {
    filtered = filtered.filter(user => user.department === activeFilters.department);
  }
  
  if (activeFilters.status) {
    if (activeFilters.status === 'verified') {
      filtered = filtered.filter(user => user.email_verified_at);
    } else if (activeFilters.status === 'pending') {
      filtered = filtered.filter(user => !user.email_verified_at);
    }
  }
  
  return filtered;
});

const userStats = computed(() => ({
  'Total': users.value.length,
  'Active': users.value.filter(u => u.email_verified_at).length,
  'Pending': users.value.filter(u => !u.email_verified_at).length
}));

// Helper functions
const getRoleIcon = (role: string) => {
  const icons: { [key: string]: any } = {
    admin: Crown,
    project_manager: ClipboardList,
    supervisor: HardHat,
    field_worker: Hammer
  };
  return icons[role] || UserCog;
};

const getDepartmentIcon = (department: string) => {
  const icons: { [key: string]: any } = {
    management: Building2,
    construction: Settings,
    safety: ShieldCheck,
    administration: Briefcase,
    logistics: Package
  };
  return icons[department] || Building2;
};

const getRoleVariant = (role: string) => {
  const variants: { [key: string]: string } = {
    admin: 'destructive',
    project_manager: 'default',
    supervisor: 'secondary',
    field_worker: 'outline'
  };
  return variants[role] || 'outline';
};

const formatRole = (role: string) => {
  return role.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};

// Event handlers
const handleFilterChange = (filters: Record<string, any>) => {
  Object.assign(activeFilters, filters);
};

const handleSearchChange = (query: string) => {
  searchQuery.value = query;
};

const handleUserClick = (user: any) => {
  console.log('User clicked:', user);
};

const handleSelectionChange = (selected: any[]) => {
  console.log('Selection changed:', selected);
};

const openCreateUserModal = () => {
  showCreateModal.value = true;
};

const handleCreateUser = () => {
  console.log('Create user:', newUser);
  showCreateModal.value = false;
  // Reset form
  Object.keys(newUser).forEach(key => {
    newUser[key as keyof typeof newUser] = '';
  });
};

const editUser = (user: any) => {
  console.log('Edit user:', user);
};

const changeUserRole = (user: any) => {
  console.log('Change role for user:', user);
};

const resetPassword = (user: any) => {
  console.log('Reset password for user:', user);
};

const deleteUser = (user: any) => {
  console.log('Delete user:', user);
};

const bulkChangeRole = (selected: any[]) => {
  console.log('Bulk change role:', selected);
};

const bulkExport = (selected: any[]) => {
  console.log('Bulk export:', selected);
};

const bulkDelete = (selected: any[]) => {
  console.log('Bulk delete:', selected);
};

const exportUsers = () => {
  console.log('Export all users');
};
</script>