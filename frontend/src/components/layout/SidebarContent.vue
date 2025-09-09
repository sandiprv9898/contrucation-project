<template>
  <div class="flex flex-col h-full">
    <!-- Logo/Brand -->
    <div class="flex items-center justify-center h-16 px-4 bg-orange-600">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
            <Building2 class="text-orange-600 w-5 h-5" />
          </div>
        </div>
        <div class="ml-3 text-white">
          <h1 class="text-lg font-bold">Construction</h1>
          <p class="text-xs text-orange-100">Management Platform</p>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
      <!-- Main Navigation -->
      <div class="space-y-1">
        <SidebarLink
          v-for="item in mainNavigation"
          :key="item.name"
          :to="item.to"
          :icon="item.icon"
          :name="item.name"
          :badge="item.badge"
          @navigate="$emit('navigate')"
        />
      </div>

      <!-- Management Section -->
      <div v-if="canViewManagementSection" class="pt-6">
        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
          Management
        </h3>
        <div class="mt-2 space-y-1">
          <SidebarLink
            v-for="item in managementNavigation"
            :key="item.name"
            :to="item.to"
            :icon="item.icon"
            :name="item.name"
            :badge="item.badge"
            @navigate="$emit('navigate')"
          />
        </div>
      </div>

      <!-- Administration Section -->
      <div v-if="canViewAdminSection" class="pt-6">
        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
          Administration
        </h3>
        <div class="mt-2 space-y-1">
          <SidebarLink
            v-for="item in adminNavigation"
            :key="item.name"
            :to="item.to"
            :icon="item.icon"
            :name="item.name"
            :badge="item.badge"
            @navigate="$emit('navigate')"
          />
        </div>
      </div>

      <!-- Reports Section -->
      <div v-if="canViewReports" class="pt-6">
        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
          Reports & Analytics
        </h3>
        <div class="mt-2 space-y-1">
          <SidebarLink
            v-for="item in reportsNavigation"
            :key="item.name"
            :to="item.to"
            :icon="item.icon"
            :name="item.name"
            :badge="item.badge"
            @navigate="$emit('navigate')"
          />
        </div>
      </div>
    </nav>

    <!-- User Profile Section -->
    <div class="flex-shrink-0 px-4 py-4 border-t border-gray-200">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <img
            v-if="currentUser?.avatar_url"
            :src="currentUser.avatar_url"
            :alt="currentUser.name"
            class="w-8 h-8 rounded-full"
          >
          <div
            v-else
            class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center"
          >
            <span class="text-orange-600 font-medium text-sm">
              {{ currentUser?.name?.charAt(0).toUpperCase() }}
            </span>
          </div>
        </div>
        <div class="ml-3 flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-900 truncate">
            {{ currentUser?.name }}
          </p>
          <p class="text-xs text-gray-500 capitalize truncate">
            {{ currentUser?.role?.replace('_', ' ') }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useAuthStore } from '@/modules/auth'
import { useUserPermissions } from '@/modules/users'
import SidebarLink from './SidebarLink.vue'
import { 
  Building2, 
  Home, 
  ClipboardList, 
  CheckSquare, 
  Calendar, 
  FileText, 
  Users, 
  Settings, 
  Edit, 
  UserCog, 
  BarChart3, 
  Clock, 
  DollarSign, 
  TrendingUp 
} from 'lucide-vue-next'

defineOptions({ name: 'SidebarContent' })

defineEmits<{
  navigate: []
}>()

const authStore = useAuthStore()
const { hasPermission } = useUserPermissions()

const currentUser = computed(() => authStore.currentUser)

// Permission checks
const canViewManagementSection = computed(() => 
  hasPermission('canViewProjects') || 
  hasPermission('canManageProjects') ||
  hasPermission('canAssignTasks')
)

const canViewAdminSection = computed(() => 
  hasPermission('canManageUsers') || 
  hasPermission('canManageCompany')
)

const canViewReports = computed(() => 
  hasPermission('canViewReports')
)

// Navigation items
const mainNavigation = computed(() => [
  {
    name: 'Dashboard',
    to: '/app/dashboard',
    icon: Home,
  },
  {
    name: 'Projects',
    to: '/app/projects',
    icon: ClipboardList,
    badge: '12', // This could be dynamic
  },
  {
    name: 'Tasks',
    to: '/app/tasks',
    icon: CheckSquare,
    badge: '8',
  },
  {
    name: 'Calendar',
    to: '/app/calendar',
    icon: Calendar,
  },
  {
    name: 'Documents',
    to: '/app/documents',
    icon: FileText,
  },
])

const managementNavigation = computed(() => {
  const items = []
  
  if (hasPermission('canViewUsers') && !hasPermission('canManageUsers')) {
    items.push({
      name: 'Team Members',
      to: '/app/users',
      icon: Users,
    })
  }
  
  if (hasPermission('canManageProjects')) {
    items.push({
      name: 'Project Settings',
      to: '/app/projects/settings',
      icon: Settings,
    })
  }
  
  if (hasPermission('canAssignTasks')) {
    items.push({
      name: 'Task Assignment',
      to: '/app/tasks/assign',
      icon: Edit,
    })
  }
  
  return items
})

const adminNavigation = computed(() => {
  const items = []
  
  if (hasPermission('canManageUsers')) {
    items.push({
      name: 'User Management',
      to: '/app/users',
      icon: UserCog,
    })
  }
  
  if (hasPermission('canManageCompany')) {
    items.push({
      name: 'Company Settings',
      to: '/app/admin/company',
      icon: Building2,
    })
    items.push({
      name: 'System Settings',
      to: '/app/admin/settings',
      icon: Settings,
    })
  }
  
  return items
})

const reportsNavigation = computed(() => 
  hasPermission('canViewReports') ? [
    {
      name: 'Project Reports',
      to: '/app/reports/projects',
      icon: BarChart3,
    },
    {
      name: 'Time Tracking',
      to: '/app/reports/time',
      icon: Clock,
    },
    {
      name: 'Cost Analysis',
      to: '/app/reports/costs',
      icon: DollarSign,
    },
    {
      name: 'Performance',
      to: '/app/reports/performance',
      icon: TrendingUp,
    },
  ] : []
)
</script>