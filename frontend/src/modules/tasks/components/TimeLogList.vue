<template>
  <div class="time-log-list">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-medium text-gray-900">Time Logs</h3>
      <div class="flex items-center space-x-3">
        <!-- View Toggle -->
        <div class="flex border border-gray-300 rounded-md overflow-hidden">
          <button
            @click="viewMode = 'list'"
            class="px-3 py-1 text-sm"
            :class="{
              'bg-blue-600 text-white': viewMode === 'list',
              'bg-white text-gray-700 hover:bg-gray-50': viewMode !== 'list'
            }"
          >
            <List class="w-4 h-4" />
          </button>
          <button
            @click="viewMode = 'card'"
            class="px-3 py-1 text-sm border-l border-gray-300"
            :class="{
              'bg-blue-600 text-white': viewMode === 'card',
              'bg-white text-gray-700 hover:bg-gray-50': viewMode !== 'card'
            }"
          >
            <Grid class="w-4 h-4" />
          </button>
        </div>
        
        <button
          @click="showCreateModal = true"
          class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
        >
          <Plus class="w-4 h-4 inline mr-1" />
          Add Manual Entry
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-gray-50 p-4 rounded-lg mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
          <input
            type="date"
            v-model="filters.date_from"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
          <input
            type="date"
            v-model="filters.date_to"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Activity Type</label>
          <select
            v-model="filters.activity_type"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
          >
            <option value="">All Activities</option>
            <option value="work">Work</option>
            <option value="meeting">Meeting</option>
            <option value="inspection">Inspection</option>
            <option value="travel">Travel</option>
            <option value="break">Break</option>
            <option value="planning">Planning</option>
            <option value="documentation">Documentation</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Billable</label>
          <select
            v-model="filters.billable"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
          >
            <option value="">All</option>
            <option value="true">Billable</option>
            <option value="false">Non-billable</option>
          </select>
        </div>
      </div>
      <div class="flex justify-end mt-4 space-x-2">
        <button
          @click="clearFilters"
          class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50"
        >
          Clear
        </button>
        <button
          @click="applyFilters"
          class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
        >
          Apply Filters
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-8">
      <Loader2 class="w-6 h-6 animate-spin text-gray-400" />
      <span class="ml-2 text-gray-600">Loading time logs...</span>
    </div>

    <!-- Empty State -->
    <div v-else-if="timeLogs.length === 0" class="text-center py-8">
      <Clock class="w-12 h-12 mx-auto text-gray-400 mb-3" />
      <h4 class="text-lg font-medium text-gray-900 mb-1">No time logs found</h4>
      <p class="text-gray-600">Start tracking time or adjust your filters</p>
    </div>

    <!-- List View -->
    <div v-else-if="viewMode === 'list'" class="space-y-3">
      <div
        v-for="timeLog in timeLogs"
        :key="timeLog.id"
        class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
      >
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <div class="flex items-center space-x-3 mb-2">
              <h4 class="font-medium text-gray-900">{{ timeLog.task?.name || 'Unknown Task' }}</h4>
              <span class="px-2 py-1 text-xs rounded-full"
                :class="getActivityTypeClasses(timeLog.activity_type)"
              >
                {{ timeLog.formatted_activity_type || timeLog.activity_type }}
              </span>
              <span v-if="timeLog.billable" class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                Billable
              </span>
            </div>
            
            <div class="flex items-center space-x-4 text-sm text-gray-600 mb-2">
              <div class="flex items-center">
                <Calendar class="w-4 h-4 mr-1" />
                {{ formatDate(timeLog.start_time) }}
              </div>
              <div class="flex items-center">
                <Clock class="w-4 h-4 mr-1" />
                {{ formatTime(timeLog.start_time) }} - 
                {{ timeLog.end_time ? formatTime(timeLog.end_time) : 'Active' }}
              </div>
              <div class="flex items-center font-medium">
                <Timer class="w-4 h-4 mr-1" />
                {{ timeLog.formatted_duration }}
              </div>
            </div>

            <!-- Location Info -->
            <div v-if="timeLog.clock_in_address || timeLog.clock_out_address" class="flex items-start space-x-4 text-xs text-gray-500 mb-2">
              <div v-if="timeLog.clock_in_address" class="flex items-center">
                <MapPin class="w-3 h-3 mr-1 text-green-500" />
                In: {{ timeLog.clock_in_address }}
              </div>
              <div v-if="timeLog.clock_out_address" class="flex items-center">
                <MapPin class="w-3 h-3 mr-1 text-red-500" />
                Out: {{ timeLog.clock_out_address }}
              </div>
              <div v-if="timeLog.location_distance" class="flex items-center">
                <Navigation class="w-3 h-3 mr-1" />
                {{ formatDistance(timeLog.location_distance) }}
              </div>
            </div>

            <!-- Description -->
            <div v-if="timeLog.description" class="text-sm text-gray-700 mb-2">
              {{ timeLog.description }}
            </div>

            <!-- Photos -->
            <div v-if="(timeLog.clock_in_photos && timeLog.clock_in_photos.length) || 
                      (timeLog.clock_out_photos && timeLog.clock_out_photos.length)" 
                 class="flex items-center space-x-2 mb-2">
              <Camera class="w-4 h-4 text-gray-400" />
              <span class="text-xs text-gray-500">
                {{ getTotalPhotoCount(timeLog) }} photo(s) attached
              </span>
            </div>
          </div>

          <div class="flex items-center space-x-2 ml-4">
            <div v-if="timeLog.billable && timeLog.hourly_rate" class="text-right">
              <div class="text-lg font-semibold text-green-600">
                ${{ timeLog.billable_amount?.toFixed(2) || '0.00' }}
              </div>
              <div class="text-xs text-gray-500">
                ${{ timeLog.hourly_rate }}/hr
              </div>
            </div>

            <div class="flex flex-col space-y-1">
              <button
                @click="editTimeLog(timeLog)"
                class="p-1 text-gray-400 hover:text-blue-600 transition-colors"
                title="Edit"
              >
                <Edit class="w-4 h-4" />
              </button>
              <button
                @click="deleteTimeLog(timeLog)"
                class="p-1 text-gray-400 hover:text-red-600 transition-colors"
                title="Delete"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Card View -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="timeLog in timeLogs"
        :key="timeLog.id"
        class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
      >
        <!-- Card content similar to list but in card format -->
        <div class="flex items-center justify-between mb-3">
          <span class="px-2 py-1 text-xs rounded-full"
            :class="getActivityTypeClasses(timeLog.activity_type)"
          >
            {{ timeLog.formatted_activity_type || timeLog.activity_type }}
          </span>
          <div class="flex items-center space-x-1">
            <button
              @click="editTimeLog(timeLog)"
              class="p-1 text-gray-400 hover:text-blue-600"
            >
              <Edit class="w-4 h-4" />
            </button>
            <button
              @click="deleteTimeLog(timeLog)"
              class="p-1 text-gray-400 hover:text-red-600"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>

        <h4 class="font-medium text-gray-900 mb-2">{{ timeLog.task?.name || 'Unknown Task' }}</h4>
        
        <div class="space-y-2 text-sm text-gray-600 mb-3">
          <div class="flex items-center justify-between">
            <span>Duration:</span>
            <span class="font-medium">{{ timeLog.formatted_duration }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span>Date:</span>
            <span>{{ formatDate(timeLog.start_time) }}</span>
          </div>
          <div v-if="timeLog.billable && timeLog.hourly_rate" class="flex items-center justify-between">
            <span>Earnings:</span>
            <span class="font-medium text-green-600">${{ timeLog.billable_amount?.toFixed(2) || '0.00' }}</span>
          </div>
        </div>

        <div v-if="timeLog.description" class="text-sm text-gray-700 mb-3 line-clamp-2">
          {{ timeLog.description }}
        </div>

        <div v-if="timeLog.clock_in_address" class="text-xs text-gray-500 flex items-center">
          <MapPin class="w-3 h-3 mr-1" />
          {{ timeLog.clock_in_address }}
        </div>
      </div>
    </div>

    <!-- Manual Time Entry Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Add Manual Time Entry</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Task</label>
            <select 
              v-model="createForm.task_id" 
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              required
            >
              <option value="">Select a task...</option>
              <option v-for="task in tasks" :key="task.id" :value="task.id">
                {{ task.name }}
              </option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
              <input
                type="datetime-local"
                v-model="createForm.start_time"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                required
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
              <input
                type="datetime-local"
                v-model="createForm.end_time"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                required
              >
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Activity Type</label>
            <select 
              v-model="createForm.activity_type" 
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="work">Work</option>
              <option value="meeting">Meeting</option>
              <option value="inspection">Inspection</option>
              <option value="travel">Travel</option>
              <option value="break">Break</option>
              <option value="planning">Planning</option>
              <option value="documentation">Documentation</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea 
              v-model="createForm.description"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              rows="3"
              placeholder="What did you work on?"
              required
            ></textarea>
          </div>

          <div class="flex items-center space-x-4">
            <label class="flex items-center">
              <input 
                type="checkbox" 
                v-model="createForm.billable"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              >
              <span class="ml-2 text-sm text-gray-700">Billable time</span>
            </label>
            
            <div v-if="createForm.billable" class="flex items-center space-x-2">
              <label class="text-sm text-gray-700">Rate:</label>
              <input
                type="number"
                v-model="createForm.hourly_rate"
                step="0.01"
                min="0"
                placeholder="0.00"
                class="w-20 rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
              >
              <span class="text-sm text-gray-700">/hr</span>
            </div>
          </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button
            @click="showCreateModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="createManualEntry"
            :disabled="loading || !createForm.task_id || !createForm.start_time || !createForm.end_time || !createForm.description"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50"
          >
            {{ loading ? 'Creating...' : 'Create Entry' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="mt-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md text-sm">
      {{ error }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import {
  List, Grid, Plus, Loader2, Clock, Calendar, Timer, MapPin, Navigation,
  Camera, Edit, Trash2
} from 'lucide-vue-next'
import { useTimeTracking } from '../composables/useTimeTracking'

interface Task {
  id: string
  name: string
}

interface Props {
  tasks: Task[]
  taskId?: string  // If provided, show logs for specific task
}

interface Emits {
  (e: 'time-log-created', timeLog: any): void
  (e: 'time-log-updated', timeLog: any): void
  (e: 'time-log-deleted', timeLogId: string): void
  (e: 'error', error: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const {
  loading,
  error,
  timeLogs,
  getUserTimeLogs,
  getTaskTimeLogs,
  createManualTimeLog,
  deleteTimeLog: deleteTimeLogApi
} = useTimeTracking()

// State
const viewMode = ref<'list' | 'card'>('list')
const showCreateModal = ref(false)

const filters = ref({
  date_from: '',
  date_to: '',
  activity_type: '',
  billable: ''
})

const createForm = ref({
  task_id: '',
  start_time: '',
  end_time: '',
  description: '',
  activity_type: 'work',
  billable: true,
  hourly_rate: undefined as number | undefined
})

// Methods
const loadTimeLogs = async () => {
  try {
    if (props.taskId) {
      await getTaskTimeLogs(props.taskId, filters.value)
    } else {
      await getUserTimeLogs(filters.value)
    }
  } catch (err: any) {
    emit('error', err.message)
  }
}

const applyFilters = () => {
  loadTimeLogs()
}

const clearFilters = () => {
  filters.value = {
    date_from: '',
    date_to: '',
    activity_type: '',
    billable: ''
  }
  loadTimeLogs()
}

const createManualEntry = async () => {
  if (!createForm.value.task_id) return

  try {
    const timeLog = await createManualTimeLog(createForm.value.task_id, {
      start_time: createForm.value.start_time,
      end_time: createForm.value.end_time,
      description: createForm.value.description,
      activity_type: createForm.value.activity_type,
      billable: createForm.value.billable,
      hourly_rate: createForm.value.hourly_rate
    })

    showCreateModal.value = false
    createForm.value = {
      task_id: '',
      start_time: '',
      end_time: '',
      description: '',
      activity_type: 'work',
      billable: true,
      hourly_rate: undefined
    }

    await loadTimeLogs()
    emit('time-log-created', timeLog)
  } catch (err: any) {
    emit('error', err.message)
  }
}

const editTimeLog = (timeLog: any) => {
  // Implement edit functionality
  console.log('Edit time log:', timeLog)
}

const deleteTimeLog = async (timeLog: any) => {
  if (!confirm('Are you sure you want to delete this time log?')) return

  try {
    await deleteTimeLogApi(timeLog.id)
    await loadTimeLogs()
    emit('time-log-deleted', timeLog.id)
  } catch (err: any) {
    emit('error', err.message)
  }
}

const getActivityTypeClasses = (activityType: string): string => {
  const classes = {
    work: 'bg-blue-100 text-blue-800',
    meeting: 'bg-purple-100 text-purple-800',
    inspection: 'bg-orange-100 text-orange-800',
    travel: 'bg-yellow-100 text-yellow-800',
    break: 'bg-gray-100 text-gray-800',
    planning: 'bg-indigo-100 text-indigo-800',
    documentation: 'bg-green-100 text-green-800',
    other: 'bg-pink-100 text-pink-800'
  }
  return classes[activityType] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString()
}

const formatTime = (dateString: string): string => {
  return new Date(dateString).toLocaleTimeString([], { 
    hour: '2-digit', 
    minute: '2-digit',
    hour12: true
  })
}

const formatDistance = (meters: number): string => {
  if (meters < 1000) {
    return `${Math.round(meters)}m`
  } else {
    return `${(meters / 1000).toFixed(1)}km`
  }
}

const getTotalPhotoCount = (timeLog: any): number => {
  const clockInPhotos = timeLog.clock_in_photos?.length || 0
  const clockOutPhotos = timeLog.clock_out_photos?.length || 0
  return clockInPhotos + clockOutPhotos
}

// Initialize
onMounted(() => {
  loadTimeLogs()
})

// Watch for task changes
watch(() => props.taskId, () => {
  loadTimeLogs()
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