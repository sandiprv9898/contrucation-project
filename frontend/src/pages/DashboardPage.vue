<template>
  <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
          <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg leading-6 font-medium text-gray-900 mb-2">
              Dashboard
            </h2>
            <p class="text-sm text-gray-600">
              Welcome to your construction management platform dashboard.
            </p>
          </div>
        </div>

        <!-- Statistics Cards -->
        <div class="mb-6">
          <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Users -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-600 rounded-lg flex items-center justify-center">
                      <Users class="text-white w-4 h-4" />
                    </div>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                      <dd class="text-lg font-medium text-gray-900">
                        {{ dashboardStore.isLoading ? '...' : (dashboardStore.stats?.total_users || 0) }}
                      </dd>
                    </dl>
                  </div>
                </div>
              </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                      <CheckCircle class="text-white w-4 h-4" />
                    </div>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-sm font-medium text-gray-500 truncate">Active Users</dt>
                      <dd class="text-lg font-medium text-gray-900">
                        {{ dashboardStore.isLoading ? '...' : (dashboardStore.stats?.active_users || 0) }}
                      </dd>
                    </dl>
                  </div>
                </div>
              </div>
            </div>

            <!-- Total Companies -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                      <Building2 class="text-white w-4 h-4" />
                    </div>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-sm font-medium text-gray-500 truncate">Companies</dt>
                      <dd class="text-lg font-medium text-gray-900">
                        {{ dashboardStore.isLoading ? '...' : (dashboardStore.stats?.total_companies || 0) }}
                      </dd>
                    </dl>
                  </div>
                </div>
              </div>
            </div>

            <!-- User Roles -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center">
                      <Wrench class="text-white w-4 h-4" />
                    </div>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-sm font-medium text-gray-500 truncate">User Roles</dt>
                      <dd class="text-lg font-medium text-gray-900">
                        {{ dashboardStore.isLoading ? '...' : Object.keys(dashboardStore.stats?.user_roles || {}).length }}
                      </dd>
                    </dl>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- User Info Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              Your Account Information
            </h3>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
              <div>
                <dt class="text-sm font-medium text-gray-500">Full name</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ authStore.currentUser?.name }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Email address</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ authStore.currentUser?.email }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Role</dt>
                <dd class="mt-1 text-sm text-gray-900 capitalize">{{ authStore.userRole?.replace('_', ' ') }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Account status</dt>
                <dd class="mt-1 text-sm text-green-600">Active</dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              Quick Actions
            </h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <div class="relative group bg-gray-50 p-6 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-600 rounded-lg flex items-center justify-center">
                      <ClipboardList class="text-white w-4 h-4" />
                    </div>
                  </div>
                  <div class="ml-4">
                    <h4 class="text-sm font-medium text-gray-900">Projects</h4>
                    <p class="text-sm text-gray-600">Manage construction projects</p>
                  </div>
                </div>
              </div>

              <div class="relative group bg-gray-50 p-6 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-600 rounded-lg flex items-center justify-center">
                      <Users class="text-white w-4 h-4" />
                    </div>
                  </div>
                  <div class="ml-4">
                    <h4 class="text-sm font-medium text-gray-900">Team</h4>
                    <p class="text-sm text-gray-600">Manage team members</p>
                  </div>
                </div>
              </div>

              <div class="relative group bg-gray-50 p-6 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-600 rounded-lg flex items-center justify-center">
                      <BarChart3 class="text-white w-4 h-4" />
                    </div>
                  </div>
                  <div class="ml-4">
                    <h4 class="text-sm font-medium text-gray-900">Reports</h4>
                    <p class="text-sm text-gray-600">View project reports</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              Recent Activity
            </h3>
            <div v-if="dashboardStore.isLoading" class="text-sm text-gray-500">
              Loading recent activity...
            </div>
            <div v-else-if="dashboardStore.recentActivity.length === 0" class="text-sm text-gray-500">
              No recent activity found.
            </div>
            <div v-else class="flow-root">
              <ul class="-mb-8">
                <li v-for="(activity, activityIdx) in dashboardStore.recentActivity" :key="activity.id" class="mb-4">
                  <div class="relative pb-8">
                    <span v-if="activityIdx !== dashboardStore.recentActivity.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                    <div class="relative flex space-x-3">
                      <div>
                        <span class="h-8 w-8 bg-orange-500 rounded-full flex items-center justify-center ring-8 ring-white">
                          <User class="text-white w-4 h-4" />
                        </span>
                      </div>
                      <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                        <div>
                          <p class="text-sm text-gray-500">
                            {{ activity.description }} 
                            <span class="font-medium text-gray-900 capitalize">
                              ({{ activity.user.role.replace('_', ' ') }})
                            </span>
                            <span v-if="activity.user.company_name" class="text-gray-400">
                              at {{ activity.user.company_name }}
                            </span>
                          </p>
                        </div>
                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                          <time>{{ activity.created_at }}</time>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from '@/modules/auth'
import { useDashboardStore } from '@/modules/dashboard/stores/dashboard.store'
import { Users, CheckCircle, Building2, Wrench, ClipboardList, BarChart3, User } from 'lucide-vue-next'

// Define component name
defineOptions({ name: 'DashboardPage' })

const authStore = useAuthStore()
const dashboardStore = useDashboardStore()

// Load dashboard data when component mounts
onMounted(() => {
  dashboardStore.loadDashboardData()
})
</script>