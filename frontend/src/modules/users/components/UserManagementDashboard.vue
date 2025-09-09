<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 p-4 lg:p-8">
    <!-- Dashboard Header -->
    <div class="mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <!-- Title Section -->
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-3">
            <div class="p-2 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg">
              <Users class="w-8 h-8 text-white" />
            </div>
            User Management Dashboard
          </h1>
          <p class="text-gray-600 dark:text-gray-400 text-lg">
            Comprehensive user administration and analytics
          </p>
        </div>

        <!-- Quick Actions -->
        <div class="flex flex-wrap items-center gap-3">
          <VButton
            variant="default"
            size="md"
            @click="showCreateUser = true"
            class="shadow-md hover:shadow-lg"
          >
            <UserPlus class="w-4 h-4 mr-2" />
            Add User
          </VButton>

          <VButton
            variant="outline"
            size="md"
            @click="exportUsers"
            :loading="isExporting"
            class="shadow-md hover:shadow-lg"
          >
            <Download class="w-4 h-4 mr-2" />
            Export
          </VButton>

          <VButton
            variant="ghost"
            size="md"
            @click="refreshData"
            :loading="isRefreshing"
          >
            <RefreshCw class="w-4 h-4 mr-2" />
            Refresh
          </VButton>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Total Users -->
      <VCard variant="elevated" class="overflow-hidden group hover:shadow-xl transition-all duration-300">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Users</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ userStats.total || 0 }}</p>
              <p class="text-sm text-green-600 dark:text-green-400 mt-1 flex items-center">
                <TrendingUp class="w-3 h-3 mr-1" />
                +{{ userStats.newThisMonth || 0 }} this month
              </p>
            </div>
            <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-md group-hover:scale-110 transition-transform">
              <Users class="w-6 h-6 text-white" />
            </div>
          </div>
        </div>
        <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600"></div>
      </VCard>

      <!-- Active Users -->
      <VCard variant="elevated" class="overflow-hidden group hover:shadow-xl transition-all duration-300">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Active Users</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ userStats.active || 0 }}</p>
              <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                {{ activePercentage }}% of total
              </p>
            </div>
            <div class="p-3 bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-md group-hover:scale-110 transition-transform">
              <UserCheck class="w-6 h-6 text-white" />
            </div>
          </div>
        </div>
        <div class="h-1 bg-gradient-to-r from-green-500 to-green-600"></div>
      </VCard>

      <!-- Role Distribution -->
      <VCard variant="elevated" class="overflow-hidden group hover:shadow-xl transition-all duration-300">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Roles</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ Object.keys(roleStats).length }}</p>
              <p class="text-sm text-orange-600 dark:text-orange-400 mt-1">
                {{ userStats.admins || 0 }} admins
              </p>
            </div>
            <div class="p-3 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-md group-hover:scale-110 transition-transform">
              <Shield class="w-6 h-6 text-white" />
            </div>
          </div>
        </div>
        <div class="h-1 bg-gradient-to-r from-orange-500 to-orange-600"></div>
      </VCard>

      <!-- Recent Activity -->
      <VCard variant="elevated" class="overflow-hidden group hover:shadow-xl transition-all duration-300">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Recent Logins</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ userStats.recentLogins || 0 }}</p>
              <p class="text-sm text-purple-600 dark:text-purple-400 mt-1">
                Last 24 hours
              </p>
            </div>
            <div class="p-3 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-md group-hover:scale-110 transition-transform">
              <Activity class="w-6 h-6 text-white" />
            </div>
          </div>
        </div>
        <div class="h-1 bg-gradient-to-r from-purple-500 to-purple-600"></div>
      </VCard>
    </div>

    <!-- Role Distribution Chart -->
    <div class="mb-8">
      <VCard variant="elevated" class="p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <BarChart3 class="w-5 h-5 text-orange-600" />
            Role Distribution
          </h3>
          <div class="flex gap-2">
            <VBadge variant="outline" size="sm">Live Data</VBadge>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div
            v-for="(count, role) in roleStats"
            :key="role"
            class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-700 hover:shadow-md transition-all"
          >
            <div class="flex items-center justify-between mb-3">
              <RoleBadge 
                :role="role as UserRole"
                :show-icon="true"
                size="md"
                variant="gradient"
              />
              <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ count }}</span>
            </div>
            
            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
              <div 
                class="h-2 rounded-full transition-all duration-500 ease-in-out"
                :class="getRoleProgressClass(role as UserRole)"
                :style="{ width: `${(count / userStats.total) * 100}%` }"
              ></div>
            </div>
            
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
              {{ Math.round((count / userStats.total) * 100) }}% of total users
            </p>
          </div>
        </div>
      </VCard>
    </div>

    <!-- Users Table -->
    <div class="mb-8">
      <VCard variant="elevated" class="overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
              <Users class="w-5 h-5 text-orange-600" />
              All Users
            </h3>
            
            <div class="flex items-center gap-3">
              <!-- View Toggle -->
              <div class="flex rounded-lg border border-gray-300 dark:border-gray-600 p-1">
                <button
                  @click="viewMode = 'table'"
                  :class="cn(
                    'px-3 py-1.5 rounded text-sm font-medium transition-colors',
                    viewMode === 'table'
                      ? 'bg-orange-600 text-white shadow-sm'
                      : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                  )"
                >
                  <List class="w-4 h-4" />
                </button>
                <button
                  @click="viewMode = 'cards'"
                  :class="cn(
                    'px-3 py-1.5 rounded text-sm font-medium transition-colors',
                    viewMode === 'cards'
                      ? 'bg-orange-600 text-white shadow-sm'
                      : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                  )"
                >
                  <Grid3x3 class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Table View -->
        <div v-if="viewMode === 'table'" class="p-6">
          <UserList
            :loading="isLoading"
            @create="showCreateUser = true"
            @edit="handleEditUser"
            @view="handleViewUser"
          />
        </div>

        <!-- Cards View -->
        <div v-else class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div 
              v-for="user in users" 
              :key="user.id"
              class="group"
            >
              <VCard 
                variant="elevated" 
                class="hover:shadow-xl transition-all duration-300 cursor-pointer"
                @click="handleViewUser(user)"
              >
                <div class="p-6">
                  <!-- User Avatar -->
                  <div class="flex items-center gap-4 mb-4">
                    <div class="relative">
                      <div class="h-12 w-12 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center shadow-sm">
                        <span class="text-lg font-semibold text-white">
                          {{ user.name.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                      <div v-if="user.email_verified_at" class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full ring-2 ring-white">
                        <CheckCircle class="w-4 h-4 text-white" />
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                        {{ user.name }}
                      </h4>
                      <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                        {{ user.email }}
                      </p>
                    </div>
                  </div>

                  <!-- User Details -->
                  <div class="space-y-3">
                    <div class="flex items-center justify-between">
                      <span class="text-sm text-gray-600 dark:text-gray-400">Role</span>
                      <RoleBadge 
                        :role="user.role"
                        size="sm"
                        variant="gradient"
                      />
                    </div>
                    
                    <div class="flex items-center justify-between">
                      <span class="text-sm text-gray-600 dark:text-gray-400">Status</span>
                      <VBadge 
                        :variant="user.email_verified_at ? 'success' : 'warning'"
                        size="sm"
                      >
                        {{ user.email_verified_at ? 'Active' : 'Pending' }}
                      </VBadge>
                    </div>
                    
                    <div class="flex items-center justify-between">
                      <span class="text-sm text-gray-600 dark:text-gray-400">Joined</span>
                      <span class="text-sm text-gray-900 dark:text-white">
                        {{ formatDate(user.created_at) }}
                      </span>
                    </div>
                  </div>

                  <!-- Card Actions -->
                  <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex gap-2">
                    <VButton
                      variant="outline"
                      size="sm"
                      @click.stop="handleEditUser(user)"
                      class="flex-1"
                    >
                      <Edit class="w-3 h-3 mr-1" />
                      Edit
                    </VButton>
                    <VButton
                      variant="ghost"
                      size="sm"
                      @click.stop="handleViewUser(user)"
                    >
                      <Eye class="w-3 h-3" />
                    </VButton>
                  </div>
                </div>
              </VCard>
            </div>
          </div>
        </div>
      </VCard>
    </div>

    <!-- User Creation Modal -->
    <VModal 
      :show="showCreateUser" 
      @close="showCreateUser = false"
      size="lg"
      title="Create New User"
    >
      <UserForm
        @submit="handleUserCreated"
        @cancel="showCreateUser = false"
        :loading="isCreating"
      />
    </VModal>

    <!-- User Edit Modal -->
    <VModal 
      :show="showEditUser" 
      @close="showEditUser = false"
      size="lg"
      title="Edit User"
    >
      <UserForm
        v-if="selectedUser"
        :user="selectedUser"
        :is-editing="true"
        @submit="handleUserUpdated"
        @cancel="showEditUser = false"
        :loading="isUpdating"
      />
    </VModal>

    <!-- User Profile Modal -->
    <VModal 
      :show="showUserProfile" 
      @close="showUserProfile = false"
      size="xl"
      :title="`${selectedUser?.name}'s Profile`"
    >
      <UserProfile
        v-if="selectedUser"
        :user-id="selectedUser.id"
        @close="showUserProfile = false"
      />
    </VModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { 
  Users, UserPlus, Download, RefreshCw, TrendingUp, UserCheck, Shield, Activity,
  BarChart3, List, Grid3x3, CheckCircle, Edit, Eye
} from 'lucide-vue-next';
import { VCard, VButton, VBadge, VModal } from '@/components/ui';
import { cn } from '@/utils/cn';
import { formatDate } from '@/utils/date';
import { useUsers } from '../composables/useUsers';
import { useUserStats } from '../composables/useUserStats';
import type { User, UserRole } from '@/modules/auth/types/auth.types';

// Components
import UserList from './UserList.vue';
import UserForm from './UserForm.vue';
import UserProfile from './UserProfile.vue';
import RoleBadge from './RoleBadge.vue';

// ==================== COMPOSABLES ====================
const { 
  users, 
  isLoading, 
  createUser, 
  updateUser, 
  exportUsers: exportUsersData,
  refreshUsers 
} = useUsers();

const {
  userStats,
  roleStats,
  refreshStats
} = useUserStats();

// ==================== REACTIVE STATE ====================
const viewMode = ref<'table' | 'cards'>('table');
const showCreateUser = ref(false);
const showEditUser = ref(false);
const showUserProfile = ref(false);
const selectedUser = ref<User | null>(null);

const isCreating = ref(false);
const isUpdating = ref(false);
const isExporting = ref(false);
const isRefreshing = ref(false);

// ==================== COMPUTED ====================
const activePercentage = computed(() => {
  if (!userStats.value.total || !userStats.value.active) return 0;
  return Math.round((userStats.value.active / userStats.value.total) * 100);
});

// ==================== METHODS ====================
const handleUserCreated = async (userData: any) => {
  try {
    isCreating.value = true;
    await createUser(userData);
    showCreateUser.value = false;
    await refreshData();
  } catch (error) {
    console.error('Failed to create user:', error);
  } finally {
    isCreating.value = false;
  }
};

const handleUserUpdated = async (userData: any) => {
  if (!selectedUser.value) return;
  
  try {
    isUpdating.value = true;
    await updateUser(selectedUser.value.id, userData);
    showEditUser.value = false;
    selectedUser.value = null;
    await refreshData();
  } catch (error) {
    console.error('Failed to update user:', error);
  } finally {
    isUpdating.value = false;
  }
};

const handleEditUser = (user: User) => {
  selectedUser.value = user;
  showEditUser.value = true;
};

const handleViewUser = (user: User) => {
  selectedUser.value = user;
  showUserProfile.value = true;
};

const exportUsers = async () => {
  try {
    isExporting.value = true;
    await exportUsersData();
  } catch (error) {
    console.error('Failed to export users:', error);
  } finally {
    isExporting.value = false;
  }
};

const refreshData = async () => {
  try {
    isRefreshing.value = true;
    await Promise.all([
      refreshUsers(),
      refreshStats()
    ]);
  } catch (error) {
    console.error('Failed to refresh data:', error);
  } finally {
    isRefreshing.value = false;
  }
};

const getRoleProgressClass = (role: UserRole) => {
  const classes = {
    admin: 'bg-gradient-to-r from-red-500 to-red-600',
    project_manager: 'bg-gradient-to-r from-blue-500 to-blue-600',
    supervisor: 'bg-gradient-to-r from-orange-500 to-orange-600',
    field_worker: 'bg-gradient-to-r from-green-500 to-green-600'
  };
  return classes[role] || classes.field_worker;
};

// ==================== LIFECYCLE ====================
onMounted(() => {
  refreshData();
});
</script>