<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Mobile-First Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-10">
      <div class="px-4 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-xl font-bold text-gray-900">My Tasks</h1>
            <p class="text-sm text-gray-600">{{ getCurrentDate() }}</p>
          </div>
          <div class="flex items-center space-x-2">
            <!-- Active Timer Display -->
            <div v-if="isActivelyTracking" class="bg-green-100 border border-green-300 rounded-lg px-3 py-2">
              <div class="flex items-center space-x-2">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-sm font-medium text-green-800">{{ currentDuration }}</span>
              </div>
            </div>
            
            <button
              @click="refreshTasks"
              :disabled="isLoading"
              class="p-2 text-gray-600 hover:text-gray-900 transition-colors"
            >
              <RefreshCw class="w-5 h-5" :class="{ 'animate-spin': isLoading }" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats - Mobile Optimized -->
    <div class="px-4 py-4">
      <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-xl p-4 text-center shadow-sm">
          <div class="text-2xl font-bold text-blue-600">{{ todayTasks.length }}</div>
          <div class="text-xs text-gray-600 mt-1">Today</div>
        </div>
        <div class="bg-white rounded-xl p-4 text-center shadow-sm">
          <div class="text-2xl font-bold text-green-600">{{ completedToday }}</div>
          <div class="text-xs text-gray-600 mt-1">Done</div>
        </div>
        <div class="bg-white rounded-xl p-4 text-center shadow-sm">
          <div class="text-2xl font-bold text-orange-600">{{ overdueTasks.length }}</div>
          <div class="text-xs text-gray-600 mt-1">Overdue</div>
        </div>
      </div>
    </div>

    <!-- Filter Tabs - Simple -->
    <div class="px-4 mb-4">
      <div class="bg-white rounded-xl p-1 shadow-sm">
        <div class="grid grid-cols-3 gap-1">
          <button
            @click="activeFilter = 'today'"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-all',
              activeFilter === 'today' 
                ? 'bg-blue-600 text-white shadow-sm' 
                : 'text-gray-600 hover:text-gray-900'
            ]"
          >
            Today ({{ todayTasks.length }})
          </button>
          <button
            @click="activeFilter = 'overdue'"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-all',
              activeFilter === 'overdue' 
                ? 'bg-red-600 text-white shadow-sm' 
                : 'text-gray-600 hover:text-gray-900'
            ]"
          >
            Overdue ({{ overdueTasks.length }})
          </button>
          <button
            @click="activeFilter = 'all'"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-all',
              activeFilter === 'all' 
                ? 'bg-gray-600 text-white shadow-sm' 
                : 'text-gray-600 hover:text-gray-900'
            ]"
          >
            All ({{ myTasks.length }})
          </button>
        </div>
      </div>
    </div>

    <!-- Task Cards - Mobile-First Design -->
    <div class="px-4 space-y-3">
      <div v-if="isLoading" class="text-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-2">Loading tasks...</p>
      </div>
      
      <div v-else-if="filteredTasks.length === 0" class="text-center py-8">
        <div class="text-4xl mb-2">🎉</div>
        <p class="text-gray-600">No tasks for this filter!</p>
      </div>
      
      <div
        v-for="task in filteredTasks"
        :key="task.id"
        class="bg-white rounded-xl shadow-sm border-l-4 overflow-hidden"
        :class="{
          'border-green-400 bg-green-50': isTaskActivelyTracked(task.id),
          'border-red-400': task.is_overdue,
          'border-yellow-400': task.is_due_soon,
          'border-blue-400': !task.is_overdue && !task.is_due_soon
        }"
      >
        <!-- Task Header -->
        <div class="p-4">
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1 min-w-0">
              <h3 class="text-lg font-semibold text-gray-900 truncate mb-1">{{ task.name }}</h3>
              <div class="flex items-center space-x-2 text-sm">
                <span 
                  :class="[
                    'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                    getPriorityClasses(task.priority.value)
                  ]"
                >
                  {{ task.priority.label }}
                </span>
                <span v-if="task.due_date" class="text-gray-600">
                  Due: {{ formatDate(task.due_date) }}
                </span>
              </div>
            </div>
            
            <!-- Priority Indicator -->
            <div class="ml-2 flex-shrink-0">
              <div 
                v-if="task.priority.value === 'critical'" 
                class="w-3 h-3 bg-red-500 rounded-full animate-pulse"
                title="Critical Priority"
              ></div>
            </div>
          </div>

          <!-- Task Description -->
          <p v-if="task.description" class="text-gray-700 text-sm mb-4 line-clamp-2">
            {{ task.description }}
          </p>

          <!-- Progress Bar -->
          <div class="mb-4">
            <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
              <span>Progress</span>
              <span>{{ task.progress_percentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                class="h-2 rounded-full transition-all duration-300"
                :class="{
                  'bg-green-500': task.progress_percentage >= 80,
                  'bg-yellow-500': task.progress_percentage >= 50,
                  'bg-blue-500': task.progress_percentage < 50
                }"
                :style="{ width: `${task.progress_percentage}%` }"
              ></div>
            </div>
          </div>

          <!-- Time Tracking Info -->
          <div v-if="task.estimated_hours || task.actual_hours" class="bg-gray-50 rounded-lg p-3 mb-4">
            <div class="grid grid-cols-2 gap-4 text-center">
              <div>
                <div class="text-lg font-bold text-blue-600">{{ task.estimated_hours || 0 }}h</div>
                <div class="text-xs text-gray-600">Estimated</div>
              </div>
              <div>
                <div class="text-lg font-bold text-green-600">{{ task.actual_hours || 0 }}h</div>
                <div class="text-xs text-gray-600">Actual</div>
              </div>
            </div>
          </div>

          <!-- Action Buttons - Large & Touch-Friendly -->
          <div class="flex space-x-2">
            <!-- Time Tracking Button -->
            <div v-if="isTaskActivelyTracked(task.id)" class="flex-1">
              <button
                @click="viewTaskDetail(task.id)"
                class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-4 rounded-xl flex items-center justify-center space-x-2 transition-colors"
              >
                <Square class="w-5 h-5" />
                <span>Stop Work ({{ currentDuration }})</span>
              </button>
            </div>
            <div v-else class="flex-1">
              <button
                @click="quickStartWork(task)"
                :disabled="isActivelyTracking"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-xl flex items-center justify-center space-x-2 transition-colors disabled:opacity-50"
              >
                <Play class="w-5 h-5" />
                <span>Start Work</span>
              </button>
            </div>
            
            <!-- View Details Button -->
            <button
              @click="viewTaskDetail(task.id)"
              class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-xl flex items-center justify-center transition-colors"
            >
              <Eye class="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Padding for Mobile Navigation -->
    <div class="h-6"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { 
  RefreshCw, Play, Square, Eye
} from 'lucide-vue-next'
import { useTaskStore } from '@/modules/tasks/stores/task.store'
import { useTimeTracking } from '@/modules/tasks/composables/useTimeTracking'
import { useAuthStore } from '@/modules/auth'
import type { Task, TaskFilters } from '@/modules/tasks/types/task.types'

defineOptions({ name: 'TasksPageWorker' })

const router = useRouter()
const taskStore = useTaskStore()
const authStore = useAuthStore()

// Time tracking composable
const { 
  activeTimeLog, 
  isActivelyTracking, 
  currentDuration,
  clockIn,
  getActiveTimeLog 
} = useTimeTracking()

// Store properties
const tasks = computed(() => {
  console.log('Tasks from store:', taskStore.tasks?.length || 0, taskStore.tasks)
  return taskStore.tasks
})
const isLoading = computed(() => taskStore.isLoading)
const error = computed(() => taskStore.error)

// Local state
const activeFilter = ref<'today' | 'overdue' | 'all'>('today')

// Computed properties for worker-focused views
const myTasks = computed(() => {
  // In a real app, filter by current user's assigned tasks
  return tasks.value || []
})

const todayTasks = computed(() => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const tomorrow = new Date(today)
  tomorrow.setDate(tomorrow.getDate() + 1)

  return myTasks.value.filter(task => {
    if (task.due_date) {
      const dueDate = new Date(task.due_date)
      return dueDate >= today && dueDate < tomorrow
    }
    // Also include in-progress tasks without due dates
    return task.status.value === 'in_progress'
  })
})

const overdueTasks = computed(() => {
  return myTasks.value.filter(task => task.is_overdue)
})

const completedToday = computed(() => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  
  return myTasks.value.filter(task => {
    if (task.completed_at) {
      const completedDate = new Date(task.completed_at)
      return completedDate >= today && task.status.value === 'completed'
    }
    return false
  }).length
})

const filteredTasks = computed(() => {
  switch (activeFilter.value) {
    case 'today':
      return todayTasks.value
    case 'overdue':
      return overdueTasks.value
    case 'all':
    default:
      return myTasks.value.filter(task => task.status.value !== 'completed')
  }
})

// Methods
const loadTasks = async () => {
  if (!authStore.user?.id) {
    console.error('No authenticated user found')
    return
  }

  const filters: TaskFilters = {
    assigned_to_id: authStore.user.id,
    per_page: 100
  }
  
  console.log('Loading tasks for user:', authStore.user.id, authStore.user.name)
  
  try {
    await Promise.all([
      taskStore.loadTasks(filters),
      getActiveTimeLog()
    ])
    console.log('Tasks loaded:', taskStore.tasks?.length || 0)
  } catch (error) {
    console.error('Error loading tasks:', error)
  }
}

const refreshTasks = async () => {
  await loadTasks()
}

const quickStartWork = async (task: Task) => {
  try {
    const location = await getCurrentLocation()
    
    const clockInData = {
      latitude: location.lat,
      longitude: location.lng,
      address: location.address,
      activity_type: 'work',
      description: `Started work on ${task.name}`,
      photos: []
    }
    
    await clockIn(task.id, clockInData)
    await refreshTasks()
  } catch (err) {
    console.error('Failed to start work:', err)
  }
}

const viewTaskDetail = (taskId: string) => {
  router.push(`/app/worker/tasks/${taskId}`)
}

const getCurrentLocation = (): Promise<{ lat: number; lng: number; address?: string }> => {
  return new Promise((resolve) => {
    if (!navigator.geolocation) {
      resolve({ lat: 0, lng: 0, address: 'Location unavailable' })
      return
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        resolve({
          lat: position.coords.latitude,
          lng: position.coords.longitude,
          address: 'Current location'
        })
      },
      () => resolve({ lat: 0, lng: 0, address: 'Location unavailable' }),
      { enableHighAccuracy: false, timeout: 5000, maximumAge: 300000 }
    )
  })
}

const getCurrentDate = (): string => {
  return new Date().toLocaleDateString('en-US', { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  })
}

const getPriorityClasses = (priority: string): string => {
  const classes: Record<string, string> = {
    'low': 'bg-green-100 text-green-800',
    'medium': 'bg-yellow-100 text-yellow-800',
    'high': 'bg-orange-100 text-orange-800',
    'critical': 'bg-red-100 text-red-800'
  }
  return classes[priority] || 'bg-gray-100 text-gray-800'
}

const isTaskActivelyTracked = (taskId: string): boolean => {
  return isActivelyTracking.value && activeTimeLog.value?.task_id === taskId
}

// Lifecycle
onMounted(() => {
  loadTasks()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>