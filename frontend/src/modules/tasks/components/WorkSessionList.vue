<template>
  <div class="bg-white rounded-lg border border-gray-200">
    <!-- Header -->
    <div class="p-6 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900">Work Sessions & Workers</h3>
        <div class="flex items-center space-x-2">
          <button
            @click="refreshData"
            :disabled="loading"
            class="p-2 text-gray-500 hover:text-gray-700 disabled:opacity-50"
          >
            <RefreshCw :class="['w-4 h-4', loading && 'animate-spin']" />
          </button>
          <div class="text-sm text-gray-500">
            {{ timeLogs.length }} sessions
          </div>
        </div>
      </div>
    </div>

    <!-- Active Workers -->
    <div v-if="activeWorkers.length > 0" class="p-6 bg-green-50 border-b border-gray-200">
      <h4 class="text-sm font-medium text-green-800 mb-3 flex items-center">
        <Users class="w-4 h-4 mr-2" />
        Currently Working ({{ activeWorkers.length }})
      </h4>
      <div class="space-y-2">
        <div
          v-for="worker in activeWorkers"
          :key="worker.user.id"
          class="flex items-center justify-between p-3 bg-white rounded-lg border border-green-200"
        >
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
              <span class="text-white text-sm font-medium">
                {{ worker.user.name.charAt(0).toUpperCase() }}
              </span>
            </div>
            <div>
              <div class="font-medium text-gray-900">{{ worker.user.name }}</div>
              <div class="text-sm text-gray-500">{{ worker.activity_type }}</div>
            </div>
          </div>
          <div class="text-right">
            <div class="text-sm font-medium text-green-700">{{ worker.duration }}</div>
            <div class="text-xs text-gray-500">Started {{ formatTime(worker.start_time) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Workers Summary -->
    <div v-if="workerStats.length > 0" class="p-6 border-b border-gray-200">
      <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
        <BarChart3 class="w-4 h-4 mr-2" />
        Worker Summary
      </h4>
      <div class="space-y-3">
        <div
          v-for="worker in workerStats"
          :key="worker.user_id"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
        >
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
              <span class="text-white text-sm font-medium">
                {{ worker.user_name.charAt(0).toUpperCase() }}
              </span>
            </div>
            <div>
              <div class="font-medium text-gray-900">{{ worker.user_name }}</div>
              <div class="text-sm text-gray-500">{{ worker.session_count }} sessions</div>
            </div>
          </div>
          <div class="text-right">
            <div class="text-sm font-medium text-blue-700">{{ worker.total_hours }}h</div>
            <div class="text-xs text-gray-500">
              {{ worker.billable_hours }}h billable
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Controls -->
    <div class="p-4 border-b border-gray-200 bg-gray-50">
      <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-700">Filter:</label>
          <select 
            v-model="filters.activity_type" 
            @change="applyFilters"
            class="text-sm border border-gray-300 rounded px-2 py-1"
          >
            <option value="">All Activities</option>
            <option value="survey">Survey</option>
            <option value="excavation">Excavation</option>
            <option value="construction">Construction</option>
            <option value="inspection">Inspection</option>
            <option value="documentation">Documentation</option>
            <option value="maintenance">Maintenance</option>
            <option value="other">Other</option>
          </select>
        </div>
        
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-700">Date:</label>
          <input 
            v-model="filters.date" 
            type="date" 
            @change="applyFilters"
            class="text-sm border border-gray-300 rounded px-2 py-1"
          />
        </div>
        
        <div class="flex items-center space-x-2">
          <input 
            v-model="filters.billable_only" 
            type="checkbox" 
            @change="applyFilters"
            class="rounded"
          />
          <label class="text-sm text-gray-700">Billable only</label>
        </div>

        <button
          @click="clearFilters"
          class="text-sm text-blue-600 hover:text-blue-800"
        >
          Clear filters
        </button>
      </div>
    </div>

    <!-- Work Sessions Timeline -->
    <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
      <div v-if="loading && timeLogs.length === 0" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <div class="mt-2 text-sm text-gray-500">Loading work sessions...</div>
      </div>

      <div v-else-if="filteredTimeLogs.length === 0" class="p-8 text-center">
        <Clock class="w-12 h-12 text-gray-400 mx-auto mb-3" />
        <div class="text-gray-500">No work sessions found</div>
        <div class="text-sm text-gray-400 mt-1">
          {{ timeLogs.length === 0 ? 'No sessions recorded yet' : 'Try adjusting your filters' }}
        </div>
      </div>

      <div
        v-for="timeLog in filteredTimeLogs"
        :key="timeLog.id"
        class="p-4 hover:bg-gray-50 transition-colors"
      >
        <div class="flex items-start space-x-4">
          <!-- User Avatar -->
          <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
              <span class="text-white font-medium">
                {{ timeLog.user?.name.charAt(0).toUpperCase() || '?' }}
              </span>
            </div>
          </div>

          <!-- Session Content -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <h4 class="text-sm font-medium text-gray-900">
                  {{ timeLog.user?.name || 'Unknown User' }}
                </h4>
                <span :class="[
                  'px-2 py-1 text-xs rounded-full',
                  getActivityBadgeClass(timeLog.activity_type)
                ]">
                  {{ timeLog.activity_type }}
                </span>
                <span v-if="timeLog.billable" class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                  Billable
                </span>
              </div>
              
              <div class="flex items-center space-x-2 text-sm text-gray-500">
                <Clock class="w-4 h-4" />
                <span>{{ timeLog.formatted_duration }}</span>
                <span v-if="timeLog.billable_amount" class="font-medium text-green-600">
                  ${{ timeLog.billable_amount }}
                </span>
              </div>
            </div>

            <!-- Time Range -->
            <div class="text-sm text-gray-600 mt-1">
              {{ formatDateTime(timeLog.start_time) }}
              <span v-if="timeLog.end_time"> → {{ formatDateTime(timeLog.end_time) }}</span>
              <span v-else class="text-green-600 font-medium"> (Active)</span>
            </div>

            <!-- Description -->
            <div v-if="timeLog.description" class="text-sm text-gray-700 mt-2 bg-gray-50 rounded p-2">
              {{ timeLog.description }}
            </div>

            <!-- Location -->
            <div v-if="timeLog.clock_in_address || timeLog.clock_in_location_lat" class="mt-2">
              <div class="flex items-center text-sm text-gray-600">
                <MapPin class="w-4 h-4 mr-1" />
                <span>
                  {{ timeLog.clock_in_address || `${timeLog.clock_in_location_lat?.toFixed(6)}, ${timeLog.clock_in_location_lng?.toFixed(6)}` }}
                </span>
              </div>
            </div>

            <!-- Photos -->
            <div v-if="timeLog.clock_in_photos?.length || timeLog.clock_out_photos?.length" class="mt-3">
              <div class="text-xs text-gray-500 mb-2">Work Photos:</div>
              <div class="grid grid-cols-4 gap-2">
                <!-- Clock In Photos -->
                <div
                  v-for="(photo, index) in timeLog.clock_in_photos"
                  :key="`in-${index}`"
                  class="relative group cursor-pointer"
                  @click="openPhotoModal(photo, 'Start Work')"
                >
                  <img
                    :src="photo"
                    :alt="`Start photo ${index + 1}`"
                    class="w-full h-16 object-cover rounded border border-green-200"
                  />
                  <div class="absolute top-1 left-1 bg-green-500 text-white text-xs px-1 rounded">
                    Start
                  </div>
                </div>
                
                <!-- Clock Out Photos -->
                <div
                  v-for="(photo, index) in timeLog.clock_out_photos"
                  :key="`out-${index}`"
                  class="relative group cursor-pointer"
                  @click="openPhotoModal(photo, 'End Work')"
                >
                  <img
                    :src="photo"
                    :alt="`End photo ${index + 1}`"
                    class="w-full h-16 object-cover rounded border border-red-200"
                  />
                  <div class="absolute top-1 left-1 bg-red-500 text-white text-xs px-1 rounded">
                    End
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Photo Modal -->
    <div
      v-if="selectedPhoto"
      class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
      @click="closePhotoModal"
    >
      <div class="max-w-4xl max-h-[90vh] relative">
        <img
          :src="selectedPhoto.url"
          :alt="selectedPhoto.caption"
          class="max-w-full max-h-full object-contain"
        />
        <button
          @click="closePhotoModal"
          class="absolute top-4 right-4 bg-black bg-opacity-50 text-white rounded-full p-2 hover:bg-opacity-75"
        >
          <X class="w-6 h-6" />
        </button>
        <div class="absolute bottom-4 left-4 bg-black bg-opacity-50 text-white px-3 py-1 rounded">
          {{ selectedPhoto.caption }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { RefreshCw, Users, BarChart3, Clock, MapPin, X } from 'lucide-vue-next'
import { useTimeTracking } from '../composables/useTimeTracking'

interface TimeLog {
  id: string
  task_id: string
  user_id: string
  start_time: string
  end_time: string | null
  duration_minutes: number
  description: string | null
  billable: boolean
  hourly_rate: number | null
  clock_in_location_lat: number | null
  clock_in_location_lng: number | null
  clock_in_address: string | null
  clock_out_location_lat: number | null
  clock_out_location_lng: number | null
  clock_out_address: string | null
  clock_in_photos: string[]
  clock_out_photos: string[]
  activity_type: string
  is_active: boolean
  task?: {
    id: string
    name: string
  }
  user?: {
    id: string
    name: string
  }
  created_at: string
  updated_at: string
  formatted_duration: string
  duration_hours: number
  billable_amount: number
  location_distance: number | null
}

interface Props {
  taskId: string
}

interface WorkerStats {
  user_id: string
  user_name: string
  session_count: number
  total_hours: number
  billable_hours: number
}

interface ActiveWorker extends TimeLog {
  duration: string
}

const props = defineProps<Props>()

// Time tracking composable
const { getTaskTimeLogs, loading } = useTimeTracking()

// State
const timeLogs = ref<TimeLog[]>([])
const selectedPhoto = ref<{ url: string; caption: string } | null>(null)

// Filters
const filters = ref({
  activity_type: '',
  date: '',
  billable_only: false
})

// Computed
const activeWorkers = computed((): ActiveWorker[] => {
  return timeLogs.value
    .filter(log => log.is_active && log.end_time === null)
    .map(log => ({
      ...log,
      duration: calculateDuration(log.start_time)
    }))
})

const workerStats = computed((): WorkerStats[] => {
  const statsMap = new Map<string, WorkerStats>()
  
  timeLogs.value.forEach(log => {
    if (!log.user) return
    
    const userId = log.user.id
    if (!statsMap.has(userId)) {
      statsMap.set(userId, {
        user_id: userId,
        user_name: log.user.name,
        session_count: 0,
        total_hours: 0,
        billable_hours: 0
      })
    }
    
    const stats = statsMap.get(userId)!
    stats.session_count++
    stats.total_hours += log.duration_hours || 0
    if (log.billable) {
      stats.billable_hours += log.duration_hours || 0
    }
  })
  
  return Array.from(statsMap.values())
    .sort((a, b) => b.total_hours - a.total_hours)
})

const filteredTimeLogs = computed(() => {
  let filtered = [...timeLogs.value]
  
  if (filters.value.activity_type) {
    filtered = filtered.filter(log => log.activity_type === filters.value.activity_type)
  }
  
  if (filters.value.date) {
    filtered = filtered.filter(log => {
      const logDate = new Date(log.start_time).toISOString().split('T')[0]
      return logDate === filters.value.date
    })
  }
  
  if (filters.value.billable_only) {
    filtered = filtered.filter(log => log.billable)
  }
  
  return filtered.sort((a, b) => new Date(b.start_time).getTime() - new Date(a.start_time).getTime())
})

// Methods
const loadTimeLogs = async () => {
  try {
    timeLogs.value = await getTaskTimeLogs(props.taskId)
  } catch (err) {
    console.error('Failed to load time logs:', err)
  }
}

const refreshData = async () => {
  await loadTimeLogs()
}

const applyFilters = () => {
  // Filters are reactive, no need for explicit action
}

const clearFilters = () => {
  filters.value = {
    activity_type: '',
    date: '',
    billable_only: false
  }
}

const calculateDuration = (startTime: string): string => {
  const start = new Date(startTime)
  const now = new Date()
  const diffInMinutes = Math.floor((now.getTime() - start.getTime()) / (1000 * 60))
  
  const hours = Math.floor(diffInMinutes / 60)
  const minutes = diffInMinutes % 60
  
  return `${hours}h ${minutes}m`
}

const formatDateTime = (timestamp: string): string => {
  return new Date(timestamp).toLocaleString()
}

const formatTime = (timestamp: string): string => {
  return new Date(timestamp).toLocaleTimeString()
}

const getActivityBadgeClass = (activity: string): string => {
  const classes = {
    survey: 'bg-blue-100 text-blue-800',
    excavation: 'bg-orange-100 text-orange-800',
    construction: 'bg-yellow-100 text-yellow-800',
    inspection: 'bg-purple-100 text-purple-800',
    documentation: 'bg-gray-100 text-gray-800',
    maintenance: 'bg-green-100 text-green-800',
    other: 'bg-slate-100 text-slate-800'
  }
  return classes[activity as keyof typeof classes] || classes.other
}

const openPhotoModal = (photoUrl: string, caption: string) => {
  selectedPhoto.value = { url: photoUrl, caption }
}

const closePhotoModal = () => {
  selectedPhoto.value = null
}

// Lifecycle
onMounted(() => {
  loadTimeLogs()
})

// Watch for task changes
watch(() => props.taskId, () => {
  loadTimeLogs()
})
</script>