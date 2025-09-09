<template>
  <div class="space-y-6">
    <!-- Simple Header -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
            👥 Team Members
          </h1>
          <p class="text-gray-600 mt-1">Manage your construction team and their roles</p>
        </div>
        
        <!-- Stats without problematic icons -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
          <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 rounded-lg">
            <div class="w-4 h-4 bg-blue-600 rounded"></div>
            <div>
              <div class="font-semibold text-blue-900">{{ statistics.total }}</div>
              <div class="text-blue-600 text-xs">Total</div>
            </div>
          </div>
          <div class="flex items-center gap-2 px-3 py-2 bg-green-50 rounded-lg">
            <div class="w-4 h-4 bg-green-600 rounded"></div>
            <div>
              <div class="font-semibold text-green-900">{{ statistics.active }}</div>
              <div class="text-green-600 text-xs">Active</div>
            </div>
          </div>
          <div class="flex items-center gap-2 px-3 py-2 bg-purple-50 rounded-lg">
            <div class="w-4 h-4 bg-purple-600 rounded"></div>
            <div>
              <div class="font-semibold text-purple-900">{{ statistics.verified }}</div>
              <div class="text-purple-600 text-xs">Verified</div>
            </div>
          </div>
          <div class="flex items-center gap-2 px-3 py-2 bg-orange-50 rounded-lg">
            <div class="w-4 h-4 bg-orange-600 rounded"></div>
            <div>
              <div class="font-semibold text-orange-900">{{ statistics.thisMonth }}</div>
              <div class="text-orange-600 text-xs">This Month</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Simple Search -->
    <div class="bg-white border border-gray-200 rounded-lg p-4">
      <div class="flex items-center gap-4">
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Search users by name, email, phone, or department..."
          class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500"
        >
        <button 
          class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700"
          @click="exportUsers"
        >
          📤 Export
        </button>
        <button 
          v-if="canCreateUsers"
          class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700"
        >
          ➕ Add User
        </button>
      </div>
    </div>

    <!-- Working Data Table -->
    <div class="bg-white border border-gray-200 rounded-lg">
      <BasicVDataTable
        :columns="tableColumns"
        :data="searchedUsers"
        :loading="loading"
        :show-pagination="true"
        :per-page="25"
      >
        <!-- Custom cell slots -->
        <template #cell-name="{ row }">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
              <span class="text-orange-600 font-medium text-sm">
                {{ row.name.charAt(0) }}
              </span>
            </div>
            <div>
              <div class="font-medium text-gray-900">{{ row.name }}</div>
              <div class="text-sm text-gray-500">{{ row.phone }}</div>
            </div>
          </div>
        </template>
        
        <template #cell-role="{ row }">
          <span class="inline-flex px-2 py-1 text-xs rounded-full" 
                :class="getRoleBadgeClass(row.role)">
            {{ getRoleLabel(row.role) }}
          </span>
        </template>
        
        <template #cell-status="{ row }">
          <span class="inline-flex px-2 py-1 text-xs rounded-full"
                :class="row.isActive ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
            {{ row.isActive ? 'Active' : 'Inactive' }}
          </span>
        </template>
      </BasicVDataTable>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useUsers } from '../composables/useUsers';
import { useUserPermissions } from '../composables/useUserPermissions';
import BasicVDataTable from '@/components/debug/BasicVDataTable.vue';

// Composables
const { 
  users,
  filteredUsers,
  statistics,
  loading,
  error,
  selectedUsers,
  loadUsers,
  exportUsers
} = useUsers();

const { canCreateUsers, canEditUsers, canDeleteUsers } = useUserPermissions();

// Local state
const searchQuery = ref('');
const showBulkActions = ref(false);

// Table configuration
const tableColumns = [
  { 
    key: 'name', 
    label: 'Name', 
    sortable: true 
  },
  { 
    key: 'email', 
    label: 'Email', 
    sortable: true 
  },
  { 
    key: 'role', 
    label: 'Role', 
    sortable: true 
  },
  { 
    key: 'status', 
    label: 'Status', 
    sortable: true 
  },
  { 
    key: 'department', 
    label: 'Department', 
    sortable: true 
  }
];

// Computed
const searchedUsers = computed(() => {
  if (!searchQuery.value.trim()) return filteredUsers.value;
  
  const query = searchQuery.value.toLowerCase().trim();
  return filteredUsers.value.filter(user => 
    user.name.toLowerCase().includes(query) ||
    user.email.toLowerCase().includes(query) ||
    user.phone?.toLowerCase().includes(query) ||
    user.department?.toLowerCase().includes(query)
  );
});

// Methods
const getRoleLabel = (role: string): string => {
  const roleLabels: Record<string, string> = {
    admin: 'Administrator',
    project_manager: 'Project Manager',
    site_manager: 'Site Manager',
    supervisor: 'Supervisor',
    field_worker: 'Field Worker',
    safety_officer: 'Safety Officer',
    quality_inspector: 'Quality Inspector'
  };
  return roleLabels[role] || role;
};

const getRoleBadgeClass = (role: string): string => {
  const roleClasses: Record<string, string> = {
    admin: 'bg-red-100 text-red-800',
    project_manager: 'bg-purple-100 text-purple-800',
    site_manager: 'bg-blue-100 text-blue-800',
    supervisor: 'bg-green-100 text-green-800',
    field_worker: 'bg-yellow-100 text-yellow-800',
    safety_officer: 'bg-orange-100 text-orange-800',
    quality_inspector: 'bg-indigo-100 text-indigo-800'
  };
  return roleClasses[role] || 'bg-gray-100 text-gray-800';
};

// Load users on mount
loadUsers();
</script>