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
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 space-y-4">
        <!-- Primary filters row -->
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

            <select
              v-model="departmentFilter"
              class="block px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
            >
              <option value="">All Departments</option>
              <option value="engineering">Engineering</option>
              <option value="construction">Construction</option>
              <option value="project_management">Project Management</option>
              <option value="safety">Safety</option>
              <option value="quality_control">Quality Control</option>
              <option value="equipment">Equipment</option>
              <option value="administration">Administration</option>
            </select>

            <select
              v-model="sortFilter"
              class="block px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
            >
              <option value="">Default Sort</option>
              <option value="name_asc">Name (A-Z)</option>
              <option value="name_desc">Name (Z-A)</option>
              <option value="email_asc">Email (A-Z)</option>
              <option value="email_desc">Email (Z-A)</option>
              <option value="created_at_desc">Newest First</option>
              <option value="created_at_asc">Oldest First</option>
              <option value="last_login_at_desc">Recently Active</option>
              <option value="role_asc">Role (A-Z)</option>
            </select>

            <button
              @click="toggleAdvancedFilters"
              class="px-3 py-2 text-sm text-orange-600 hover:text-orange-700 border border-orange-300 rounded-md hover:bg-orange-50"
            >
              {{ showAdvancedFilters ? 'Fewer Filters' : 'More Filters' }}
            </button>
            
            <button
              v-if="hasFilters"
              @click="clearFilters"
              :disabled="isClearing"
              class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50"
            >
              {{ isClearing ? 'Clearing...' : 'Clear Filters' }}
            </button>
          </div>
        </div>

        <!-- Advanced filters row (collapsible) -->
        <div v-if="showAdvancedFilters" class="border-t pt-4">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <!-- Activity Filters -->
            <select
              v-model="activityFilter"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
            >
              <option value="">All Activity</option>
              <option value="recently_active">Recently Active (7 days)</option>
              <option value="never_logged_in">Never Logged In</option>
              <option value="dormant_users">Dormant (30+ days)</option>
            </select>

            <!-- Phone Filter -->
            <select
              v-model="phoneFilter"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
            >
              <option value="">Any Phone Status</option>
              <option value="has_phone">Has Phone</option>
              <option value="no_phone">No Phone</option>
            </select>

            <!-- Date Range Filters -->
            <input
              v-model="createdFromFilter"
              type="date"
              placeholder="Created From"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
            />

            <input
              v-model="createdToFilter"
              type="date"
              placeholder="Created To"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
            />

            <!-- Email Domain Filter -->
            <input
              v-model="emailDomainFilter"
              type="text"
              placeholder="Email domain (e.g., company.com)"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
            />

            <!-- Active Status Filter -->
            <select
              v-model="activeFilter"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
            >
              <option value="">Any Status</option>
              <option value="active">Active Users</option>
              <option value="inactive">Inactive Users</option>
            </select>
          </div>

          <!-- Filter Summary -->
          <div v-if="appliedFiltersCount > 0" class="mt-3 text-sm text-gray-600">
            {{ appliedFiltersCount }} filter{{ appliedFiltersCount !== 1 ? 's' : '' }} applied
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

    <!-- Table Footer with Pagination -->
    <div class="px-6">
      <div class="bg-white px-4 py-3 flex flex-col sm:flex-row items-center justify-between border-t border-gray-200 sm:px-6 rounded-b-lg space-y-3 sm:space-y-0">
        <!-- Pagination Info -->
        <div class="flex items-center space-x-4">
          <div class="text-sm text-gray-700">
            Showing 
            <span class="font-medium">{{ ((pagination.current_page - 1) * pagination.per_page) + 1 }}</span>
            to 
            <span class="font-medium">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span>
            of 
            <span class="font-medium">{{ pagination.total }}</span>
            team members
            <span v-if="hasFilters" class="text-gray-500">(filtered)</span>
          </div>
          
          <!-- Page Size Selector -->
          <div class="flex items-center space-x-2">
            <label class="text-sm text-gray-700">Show:</label>
            <select
              :value="pagination.per_page"
              @change="handlePageSizeChange"
              class="border border-gray-300 rounded text-sm px-2 py-1 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
            >
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
        </div>

        <!-- Pagination Navigation -->
        <div class="flex items-center space-x-1" v-if="pagination.last_page > 1">
          <!-- Previous Button -->
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>

          <!-- Page Numbers -->
          <div class="flex items-center space-x-1">
            <!-- First page -->
            <button
              v-if="startPage > 1"
              @click="changePage(1)"
              class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              1
            </button>
            
            <!-- Ellipsis after first page -->
            <span v-if="startPage > 2" class="px-3 py-2 text-sm text-gray-500">...</span>
            
            <!-- Visible page numbers -->
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="changePage(page)"
              :class="[
                'px-3 py-2 text-sm font-medium rounded-md',
                page === pagination.current_page
                  ? 'text-orange-600 bg-orange-50 border border-orange-500'
                  : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'
              ]"
            >
              {{ page }}
            </button>
            
            <!-- Ellipsis before last page -->
            <span v-if="endPage < pagination.last_page - 1" class="px-3 py-2 text-sm text-gray-500">...</span>
            
            <!-- Last page -->
            <button
              v-if="endPage < pagination.last_page"
              @click="changePage(pagination.last_page)"
              class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              {{ pagination.last_page }}
            </button>
          </div>

          <!-- Next Button -->
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page >= pagination.last_page"
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash-es';
import { useUsers } from '../composables/useUsers';
import type { UserListItem, UserFilters } from '../types/users.types';

const router = useRouter();
const { users, loading, loadUsers, deleteUser, updateFilters, clearError, pagination, changePage, changePageSize } = useUsers();

// Basic filters
const searchTerm = ref('');
const roleFilter = ref('');
const statusFilter = ref('');
const departmentFilter = ref('');
const sortFilter = ref('');

// Advanced filters
const showAdvancedFilters = ref(false);
const activityFilter = ref('');
const phoneFilter = ref('');
const createdFromFilter = ref('');
const createdToFilter = ref('');
const emailDomainFilter = ref('');
const activeFilter = ref('');

// Control flags
const isClearing = ref(false); // Flag to prevent watcher conflicts during clear

// Computed properties
const hasFilters = computed(() => {
  return !!(
    searchTerm.value || 
    roleFilter.value || 
    statusFilter.value ||
    departmentFilter.value ||
    sortFilter.value ||
    activityFilter.value ||
    phoneFilter.value ||
    createdFromFilter.value ||
    createdToFilter.value ||
    emailDomainFilter.value ||
    activeFilter.value
  );
});

const appliedFiltersCount = computed(() => {
  let count = 0;
  if (searchTerm.value) count++;
  if (roleFilter.value) count++;
  if (statusFilter.value) count++;
  if (departmentFilter.value) count++;
  if (sortFilter.value) count++;
  if (activityFilter.value) count++;
  if (phoneFilter.value) count++;
  if (createdFromFilter.value) count++;
  if (createdToFilter.value) count++;
  if (emailDomainFilter.value) count++;
  if (activeFilter.value) count++;
  return count;
});

// Pagination computed properties
const startPage = computed(() => {
  const maxVisible = 5;
  let start = Math.max(1, pagination.value.current_page - Math.floor(maxVisible / 2));
  const end = Math.min(start + maxVisible - 1, pagination.value.last_page);
  start = Math.max(1, end - maxVisible + 1);
  return start;
});

const endPage = computed(() => {
  const maxVisible = 5;
  const start = startPage.value;
  return Math.min(start + maxVisible - 1, pagination.value.last_page);
});

const visiblePages = computed(() => {
  const pages = [];
  for (let i = startPage.value; i <= endPage.value; i++) {
    pages.push(i);
  }
  return pages;
});

// Use server-side filtered users directly from API
const filteredUsers = computed(() => {
  console.log('🔍 [USER LIST] Using server-filtered users:', users.value?.length || 0);
  
  if (!Array.isArray(users.value)) {
    console.warn('⚠️ [USER LIST] users.value is not an array, returning empty array');
    return [];
  }
  
  return users.value;
});

// Current filters for API calls
const currentFilters = computed((): Partial<UserFilters> => {
  const filters: Partial<UserFilters> = {};
  
  // Basic filters
  if (searchTerm.value.trim()) {
    filters.search = searchTerm.value.trim();
  }
  
  if (roleFilter.value) {
    filters.role = roleFilter.value as any;
  }
  
  if (statusFilter.value) {
    filters.verified = statusFilter.value === 'verified';
  }

  if (departmentFilter.value) {
    filters.department = departmentFilter.value;
  }

  // Sorting
  if (sortFilter.value) {
    const [sortBy, sortDirection] = sortFilter.value.split('_');
    filters.sort_by = sortBy as any;
    filters.sort_direction = sortDirection as 'asc' | 'desc';
  }

  // Advanced filters
  if (activityFilter.value) {
    if (activityFilter.value === 'recently_active') {
      filters.recently_active = true;
    } else if (activityFilter.value === 'never_logged_in') {
      filters.never_logged_in = true;
    } else if (activityFilter.value === 'dormant_users') {
      filters.dormant_users = true;
    }
  }

  if (phoneFilter.value) {
    filters.has_phone = phoneFilter.value === 'has_phone';
  }

  if (createdFromFilter.value) {
    filters.created_from = createdFromFilter.value;
  }

  if (createdToFilter.value) {
    filters.created_to = createdToFilter.value;
  }

  if (emailDomainFilter.value.trim()) {
    filters.email_domain = emailDomainFilter.value.trim();
  }

  if (activeFilter.value) {
    filters.active = activeFilter.value === 'active';
  }
  
  // Add pagination parameters
  filters.page = pagination.value.current_page;
  filters.per_page = pagination.value.per_page;
  
  return filters;
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

// Debounced search function
const debouncedLoadUsers = debounce(async () => {
  console.log('🔍 [USER LIST] Debounced load with filters:', currentFilters.value);
  try {
    await loadUsers(currentFilters.value, true);
  } catch (error) {
    console.error('❌ [USER LIST] Error loading filtered users:', error);
  }
}, 500);

// Filter operations
const clearFilters = async (): Promise<void> => {
  console.log('🧹 [USER LIST] Clearing all filters...');
  isClearing.value = true;
  
  try {
    // Clear any existing errors first
    clearError();
    
    // Clear all basic filter values in the UI
    searchTerm.value = '';
    roleFilter.value = '';
    statusFilter.value = '';
    departmentFilter.value = '';
    sortFilter.value = '';
    
    // Clear all advanced filter values
    activityFilter.value = '';
    phoneFilter.value = '';
    createdFromFilter.value = '';
    createdToFilter.value = '';
    emailDomainFilter.value = '';
    activeFilter.value = '';
    
    // Close advanced filters panel
    showAdvancedFilters.value = false;
    
    // Explicitly load users without any filters - this will clear store filters too
    // Note: loadUsers will reset pagination to page 1 when clearing filters
    await loadUsers({}, true); // Pass empty filters object and force refresh
    console.log('✅ [USER LIST] All filters cleared and users reloaded successfully');
    console.log('🔍 [USER LIST] Current users count after clear:', users.value?.length || 0);
  } catch (error) {
    console.error('❌ [USER LIST] Error clearing filters:', error);
    alert('Failed to clear filters. Please refresh the page.');
  } finally {
    isClearing.value = false;
  }
};

// Toggle advanced filters
const toggleAdvancedFilters = (): void => {
  showAdvancedFilters.value = !showAdvancedFilters.value;
};

// Pagination functions
const handlePageSizeChange = (event: Event): void => {
  const target = event.target as HTMLSelectElement;
  const newSize = parseInt(target.value);
  console.log('📄 [USER LIST] Changing page size to:', newSize);
  changePageSize(newSize);
};

// Apply filters immediately for dropdowns, debounced for search
const applyFilters = async (isSearch = false): Promise<void> => {
  if (isSearch) {
    // Debounce search to avoid too many API calls
    debouncedLoadUsers();
  } else {
    // Apply dropdown filters immediately
    console.log('🔍 [USER LIST] Applying filters immediately:', currentFilters.value);
    try {
      await loadUsers(currentFilters.value, true);
    } catch (error) {
      console.error('❌ [USER LIST] Error applying filters:', error);
    }
  }
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

// Watchers for filter changes
// Basic filters
watch(() => searchTerm.value, () => {
  if (isClearing.value) return; // Skip if clearing filters
  console.log('🔍 [USER LIST] Search term changed:', searchTerm.value);
  applyFilters(true); // Use debounced search
});

watch(() => roleFilter.value, () => {
  if (isClearing.value) return; // Skip if clearing filters
  console.log('🔍 [USER LIST] Role filter changed:', roleFilter.value);
  applyFilters(false); // Apply immediately
});

watch(() => statusFilter.value, () => {
  if (isClearing.value) return; // Skip if clearing filters
  console.log('🔍 [USER LIST] Status filter changed:', statusFilter.value);
  applyFilters(false); // Apply immediately
});

watch(() => departmentFilter.value, () => {
  if (isClearing.value) return; // Skip if clearing filters
  console.log('🔍 [USER LIST] Department filter changed:', departmentFilter.value);
  applyFilters(false); // Apply immediately
});

watch(() => sortFilter.value, () => {
  if (isClearing.value) return; // Skip if clearing filters
  console.log('🔍 [USER LIST] Sort filter changed:', sortFilter.value);
  applyFilters(false); // Apply immediately
});

// Advanced filters
watch(() => activityFilter.value, () => {
  if (isClearing.value) return; // Skip if clearing filters
  console.log('🔍 [USER LIST] Activity filter changed:', activityFilter.value);
  applyFilters(false); // Apply immediately
});

watch(() => phoneFilter.value, () => {
  if (isClearing.value) return; // Skip if clearing filters
  console.log('🔍 [USER LIST] Phone filter changed:', phoneFilter.value);
  applyFilters(false); // Apply immediately
});

watch(() => createdFromFilter.value, () => {
  if (isClearing.value) return; // Skip if clearing filters
  console.log('🔍 [USER LIST] Created from filter changed:', createdFromFilter.value);
  applyFilters(false); // Apply immediately
});

watch(() => createdToFilter.value, () => {
  if (isClearing.value) return; // Skip if clearing filters
  console.log('🔍 [USER LIST] Created to filter changed:', createdToFilter.value);
  applyFilters(false); // Apply immediately
});

watch(() => emailDomainFilter.value, () => {
  if (isClearing.value) return; // Skip if clearing filters
  console.log('🔍 [USER LIST] Email domain filter changed:', emailDomainFilter.value);
  applyFilters(true); // Use debounced for text input
});

watch(() => activeFilter.value, () => {
  if (isClearing.value) return; // Skip if clearing filters
  console.log('🔍 [USER LIST] Active filter changed:', activeFilter.value);
  applyFilters(false); // Apply immediately
});

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