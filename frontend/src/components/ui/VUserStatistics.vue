<template>
  <div class="user-statistics">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Total Users -->
      <VCard class="bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-blue-600">Total Users</p>
              <p class="text-3xl font-bold text-blue-900">{{ statistics?.stats.total || 0 }}</p>
              <p class="text-sm text-blue-600 mt-1">
                {{ statistics?.stats.today || 0 }} added today
              </p>
            </div>
            <div class="w-12 h-12 bg-blue-200 rounded-full flex items-center justify-center">
              <UsersIcon class="w-6 h-6 text-blue-700" />
            </div>
          </div>
        </div>
      </VCard>

      <!-- Active Users -->
      <VCard class="bg-gradient-to-br from-green-50 to-green-100 border-green-200">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-green-600">Active Users</p>
              <p class="text-3xl font-bold text-green-900">{{ statistics?.stats.active || 0 }}</p>
              <p class="text-sm text-green-600 mt-1">
                {{ activePercentage }}% of total
              </p>
            </div>
            <div class="w-12 h-12 bg-green-200 rounded-full flex items-center justify-center">
              <CheckCircleIcon class="w-6 h-6 text-green-700" />
            </div>
          </div>
        </div>
      </VCard>

      <!-- Verified Users -->
      <VCard class="bg-gradient-to-br from-indigo-50 to-indigo-100 border-indigo-200">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-indigo-600">Verified</p>
              <p class="text-3xl font-bold text-indigo-900">{{ statistics?.stats.verified || 0 }}</p>
              <p class="text-sm text-indigo-600 mt-1">
                {{ verifiedPercentage }}% verified
              </p>
            </div>
            <div class="w-12 h-12 bg-indigo-200 rounded-full flex items-center justify-center">
              <ShieldCheckIcon class="w-6 h-6 text-indigo-700" />
            </div>
          </div>
        </div>
      </VCard>

      <!-- Admins -->
      <VCard class="bg-gradient-to-br from-purple-50 to-purple-100 border-purple-200">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-purple-600">Administrators</p>
              <p class="text-3xl font-bold text-purple-900">{{ statistics?.stats.admins || 0 }}</p>
              <p class="text-sm text-purple-600 mt-1">
                System admins
              </p>
            </div>
            <div class="w-12 h-12 bg-purple-200 rounded-full flex items-center justify-center">
              <KeyIcon class="w-6 h-6 text-purple-700" />
            </div>
          </div>
        </div>
      </VCard>
    </div>

    <!-- Time-based Statistics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Time-based Stats -->
      <VCard>
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Registration Timeline</h3>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-600">This Week</span>
              <div class="flex items-center">
                <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                  <div 
                    class="bg-blue-500 h-2 rounded-full" 
                    :style="{ width: `${getTimePercentage('this_week')}%` }"
                  ></div>
                </div>
                <span class="text-sm font-semibold text-gray-900 min-w-8">
                  {{ statistics?.stats.this_week || 0 }}
                </span>
              </div>
            </div>

            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-600">This Month</span>
              <div class="flex items-center">
                <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                  <div 
                    class="bg-green-500 h-2 rounded-full" 
                    :style="{ width: `${getTimePercentage('this_month')}%` }"
                  ></div>
                </div>
                <span class="text-sm font-semibold text-gray-900 min-w-8">
                  {{ statistics?.stats.this_month || 0 }}
                </span>
              </div>
            </div>

            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-600">Last 30 Days</span>
              <div class="flex items-center">
                <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                  <div 
                    class="bg-indigo-500 h-2 rounded-full" 
                    :style="{ width: `${getTimePercentage('last_30_days')}%` }"
                  ></div>
                </div>
                <span class="text-sm font-semibold text-gray-900 min-w-8">
                  {{ statistics?.stats.last_30_days || 0 }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </VCard>

      <!-- Role Distribution -->
      <VCard>
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Role Distribution</h3>
          <div class="space-y-4">
            <div 
              v-for="(count, role) in statistics?.role_distribution" 
              :key="role"
              class="flex items-center justify-between"
            >
              <div class="flex items-center">
                <div 
                  class="w-3 h-3 rounded-full mr-3" 
                  :class="getRoleColor(role)"
                ></div>
                <span class="text-sm font-medium text-gray-600 capitalize">
                  {{ formatRoleName(role) }}
                </span>
              </div>
              <div class="flex items-center">
                <div class="w-24 bg-gray-200 rounded-full h-2 mr-3">
                  <div 
                    class="h-2 rounded-full" 
                    :class="getRoleBarColor(role)"
                    :style="{ width: `${getRolePercentage(count)}%` }"
                  ></div>
                </div>
                <span class="text-sm font-semibold text-gray-900 min-w-8">{{ count }}</span>
              </div>
            </div>
          </div>
        </div>
      </VCard>
    </div>

    <!-- Applied Filters -->
    <VCard v-if="statistics?.applied_filters && statistics.applied_filters.length > 0" class="mb-6">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Applied Filters</h3>
        <div class="flex flex-wrap gap-2">
          <VBadge
            v-for="filter in statistics.applied_filters"
            :key="filter"
            variant="secondary"
            class="capitalize"
          >
            {{ formatFilterName(filter) }}
          </VBadge>
        </div>
      </div>
    </VCard>

    <!-- Refresh Controls -->
    <div class="flex justify-between items-center">
      <div class="text-sm text-gray-500">
        Last updated: {{ lastUpdated }}
      </div>
      <div class="flex space-x-2">
        <VButton
          variant="outline"
          size="sm"
          @click="refreshStatistics"
          :disabled="isLoading"
        >
          <RefreshCwIcon 
            class="w-4 h-4 mr-2" 
            :class="{ 'animate-spin': isLoading }" 
          />
          Refresh
        </VButton>
        <VButton
          variant="outline"
          size="sm"
          @click="toggleAutoRefresh"
          :class="{ 'bg-green-50 border-green-200 text-green-700': autoRefresh }"
        >
          <ClockIcon class="w-4 h-4 mr-2" />
          Auto-refresh: {{ autoRefresh ? 'On' : 'Off' }}
        </VButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { VCard, VButton, VBadge } from '@/components/ui'
import { 
  UsersIcon, 
  CheckCircleIcon, 
  ShieldCheckIcon, 
  KeyIcon, 
  RefreshCwIcon, 
  ClockIcon 
} from 'lucide-vue-next'
import type { UserStatistics, UserFilters } from '@/modules/users/types/users.types'

const props = defineProps<{
  statistics?: UserStatistics
  filters?: UserFilters
  isLoading?: boolean
}>()

const emit = defineEmits<{
  'refresh': []
}>()

// Component state
const autoRefresh = ref(false)
const lastUpdated = ref(new Date().toLocaleTimeString())
const refreshInterval = ref<NodeJS.Timeout | null>(null)

// Computed properties
const activePercentage = computed(() => {
  if (!props.statistics?.stats.total || !props.statistics?.stats.active) return 0
  return Math.round((props.statistics.stats.active / props.statistics.stats.total) * 100)
})

const verifiedPercentage = computed(() => {
  if (!props.statistics?.stats.total || !props.statistics?.stats.verified) return 0
  return Math.round((props.statistics.stats.verified / props.statistics.stats.total) * 100)
})

// Methods
const refreshStatistics = () => {
  lastUpdated.value = new Date().toLocaleTimeString()
  emit('refresh')
}

const toggleAutoRefresh = () => {
  autoRefresh.value = !autoRefresh.value
  
  if (autoRefresh.value) {
    refreshInterval.value = setInterval(() => {
      refreshStatistics()
    }, 30000) // Refresh every 30 seconds
  } else {
    if (refreshInterval.value) {
      clearInterval(refreshInterval.value)
      refreshInterval.value = null
    }
  }
}

const getTimePercentage = (period: string): number => {
  if (!props.statistics?.stats) return 0
  
  const value = props.statistics.stats[period as keyof typeof props.statistics.stats] as number
  const maxValue = Math.max(
    props.statistics.stats.this_week || 0,
    props.statistics.stats.this_month || 0,
    props.statistics.stats.last_30_days || 0
  )
  
  return maxValue > 0 ? Math.round((value / maxValue) * 100) : 0
}

const getRolePercentage = (count: number): number => {
  if (!props.statistics?.stats.total) return 0
  return Math.round((count / props.statistics.stats.total) * 100)
}

const formatRoleName = (role: string): string => {
  return role.replace(/_/g, ' ')
}

const formatFilterName = (filter: string): string => {
  return filter.replace(/_/g, ' ')
}

const getRoleColor = (role: string): string => {
  const colors: Record<string, string> = {
    admin: 'bg-red-500',
    project_manager: 'bg-blue-500',
    supervisor: 'bg-yellow-500',
    field_worker: 'bg-green-500'
  }
  return colors[role] || 'bg-gray-500'
}

const getRoleBarColor = (role: string): string => {
  const colors: Record<string, string> = {
    admin: 'bg-red-400',
    project_manager: 'bg-blue-400',
    supervisor: 'bg-yellow-400',
    field_worker: 'bg-green-400'
  }
  return colors[role] || 'bg-gray-400'
}

// Lifecycle
onMounted(() => {
  lastUpdated.value = new Date().toLocaleTimeString()
})

onUnmounted(() => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
  }
})
</script>

<style scoped>
.user-statistics {
  @apply space-y-6;
}
</style>