<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Team Members</h1>
          <p class="text-sm text-gray-500">Manage your construction team and user access</p>
        </div>
        <div class="flex items-center space-x-3">
          <button
            @click="exportUsers"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
          >
            Export CSV
          </button>
          <button
            @click="handleCreateUser"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
          >
            Add Team Member
          </button>
        </div>
      </div>
    </div>

    <!-- Search and Filter Bar -->
    <div class="px-6">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between space-x-4">
          <div class="flex-1 max-w-md">
            <div class="relative">
              <input
                v-model="searchTerm"
                type="text"
                placeholder="Search team members..."
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
              />
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
            </div>
          </div>
          
          <div class="flex items-center space-x-3">
            <select
              v-model="roleFilter"
              class="block px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
            >
              <option value="">All Roles</option>
              <option value="admin">Administrator</option>
              <option value="project_manager">Project Manager</option>
              <option value="supervisor">Supervisor</option>
              <option value="field_worker">Field Worker</option>
            </select>
            
            <select
              v-model="statusFilter"
              class="block px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
            >
              <option value="">All Status</option>
              <option value="verified">Verified</option>
              <option value="pending">Pending</option>
            </select>
            
            <button
              v-if="hasFilters"
              @click="clearFilters"
              class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700"
            >
              Clear Filters
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Users Data Table -->
    <div class="px-6">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Team Member
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Role & Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Contact
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
              <tr v-if="loading">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                  <div class="flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"></div>
                    <span class="ml-3">Loading team members...</span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="!filteredUsers.length">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                  <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="mt-4 text-lg font-medium text-gray-900">No team members found</p>
                    <p class="mt-2 text-sm text-gray-500">Get started by adding a team member to your project.</p>
                  </div>
                </td>
              </tr>
              <tr
                v-else
                v-for="user in filteredUsers"
                :key="user.id"
                class="hover:bg-gray-50 transition-colors duration-150"
              >
                <!-- Team Member Info -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center">
                        <span class="text-orange-600 font-medium text-sm">
                          {{ getInitials(user.name) }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ user.name }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ user.email }}
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Role & Status -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col space-y-1">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                          :class="getRoleBadgeClass(user.role)">
                      {{ formatRole(user.role) }}
                    </span>
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                          :class="getStatusBadgeClass(user.email_verified_at)">
                      {{ user.email_verified_at ? 'Verified' : 'Pending' }}
                    </span>
                  </div>
                </td>

                <!-- Contact -->
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div class="space-y-1">
                    <div>{{ user.email }}</div>
                    <div v-if="user.phone" class="text-xs">{{ user.phone }}</div>
                  </div>
                </td>

                <!-- Joined Date -->
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(user.created_at) }}
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <button
                      @click="handleViewUser(user)"
                      class="text-orange-600 hover:text-orange-900 transition-colors duration-150"
                      title="View Details"
                    >
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    <button
                      @click="handleEditUser(user)"
                      class="text-gray-600 hover:text-gray-900 transition-colors duration-150"
                      title="Edit User"
                    >
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      @click="handleDeleteUser(user)"
                      class="text-red-600 hover:text-red-900 transition-colors duration-150"
                      title="Delete User"
                    >
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Table Footer with Pagination Info -->
    <div class="px-6">
      <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-b-lg">
        <div class="flex-1 flex justify-between sm:hidden">
          <p class="text-sm text-gray-700">
            Showing {{ filteredUsers.length }} of {{ users.length }} team members
          </p>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing <span class="font-medium">{{ filteredUsers.length }}</span> of <span class="font-medium">{{ users.length }}</span> team members
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useUsers } from '../composables/useUsers';
import type { UserListItem } from '../types/users.types';

const router = useRouter();
const { users, loading, loadUsers, deleteUser } = useUsers();

// Filters and search
const searchTerm = ref('');
const roleFilter = ref('');
const statusFilter = ref('');

// Computed properties
const hasFilters = computed(() => {
  return !!(searchTerm.value || roleFilter.value || statusFilter.value);
});

const filteredUsers = computed(() => {
  console.log('🔍 [USER LIST] Computing filteredUsers...');
  console.log('🔍 [USER LIST] users.value:', users.value);
  
  if (!Array.isArray(users.value)) {
    console.warn('⚠️ [USER LIST] users.value is not an array, returning empty array');
    return [];
  }
  
  let filtered = [...users.value];
  
  // Apply search filter
  if (searchTerm.value) {
    const search = searchTerm.value.toLowerCase();
    filtered = filtered.filter(user => 
      user.name.toLowerCase().includes(search) ||
      user.email.toLowerCase().includes(search)
    );
  }
  
  // Apply role filter
  if (roleFilter.value) {
    filtered = filtered.filter(user => user.role === roleFilter.value);
  }
  
  // Apply status filter
  if (statusFilter.value) {
    const isVerified = statusFilter.value === 'verified';
    filtered = filtered.filter(user => 
      isVerified ? !!user.email_verified_at : !user.email_verified_at
    );
  }
  
  console.log('🔍 [USER LIST] Filtered users:', filtered.length);
  return filtered;
});

// Helper functions
const getInitials = (name: string): string => {
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .substring(0, 2);
};

const formatRole = (role: string): string => {
  const roleMap: { [key: string]: string } = {
    admin: 'Administrator',
    project_manager: 'Project Manager', 
    supervisor: 'Supervisor',
    field_worker: 'Field Worker'
  };
  return roleMap[role] || role;
};

const formatDate = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short', 
    day: 'numeric'
  });
};

const getRoleBadgeClass = (role: string): string => {
  const classes: { [key: string]: string } = {
    admin: 'bg-red-100 text-red-800',
    project_manager: 'bg-blue-100 text-blue-800',
    supervisor: 'bg-green-100 text-green-800', 
    field_worker: 'bg-gray-100 text-gray-800'
  };
  return classes[role] || 'bg-gray-100 text-gray-800';
};

const getStatusBadgeClass = (emailVerified: string | null): string => {
  return emailVerified 
    ? 'bg-green-100 text-green-800'
    : 'bg-yellow-100 text-yellow-800';
};

// CRUD Operations
const handleCreateUser = (): void => {
  router.push('/app/users/create');
};

const handleViewUser = (user: UserListItem): void => {
  router.push(`/app/users/${user.id}`);
};

const handleEditUser = (user: UserListItem): void => {
  router.push(`/app/users/${user.id}/edit`);
};

const handleDeleteUser = async (user: UserListItem): Promise<void> => {
  if (!confirm(`Are you sure you want to remove ${user.name} from the team? This action cannot be undone.`)) {
    return;
  }

  try {
    console.log('🗑️ [USER LIST] Deleting user:', user.id);
    await deleteUser(user.id);
    console.log('✅ [USER LIST] User deleted successfully');
    
    // Reload the users list
    await loadUsers();
    console.log('✅ [USER LIST] Users list refreshed');
  } catch (error) {
    console.error('❌ [USER LIST] Failed to delete user:', error);
    alert('Failed to delete user. Please try again.');
  }
};

// Filter operations
const clearFilters = (): void => {
  searchTerm.value = '';
  roleFilter.value = '';
  statusFilter.value = '';
};

// Export functionality
const exportUsers = (): void => {
  const dataToExport = filteredUsers.value.map(user => ({
    name: user.name,
    email: user.email,
    role: formatRole(user.role),
    status: user.email_verified_at ? 'Verified' : 'Pending',
    joined: formatDate(user.created_at),
    phone: user.phone || ''
  }));
  
  // Create CSV content
  const headers = ['Name', 'Email', 'Role', 'Status', 'Joined', 'Phone'];
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
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
  
  console.log('📄 [USER LIST] CSV exported with', dataToExport.length, 'users');
};

// Lifecycle
onMounted(async () => {
  console.log('🚀 [USER LIST] Component mounted, loading users...');
  try {
    await loadUsers();
    console.log('✅ [USER LIST] Users loaded successfully');
  } catch (error) {
    console.error('❌ [USER LIST] Error loading users:', error);
  }
});
</script>