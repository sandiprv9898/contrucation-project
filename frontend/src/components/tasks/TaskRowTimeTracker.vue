<template>
  <div class="flex items-center space-x-2">
    <!-- Active Work Indicator -->
    <div v-if="isActivelyWorking" class="flex items-center space-x-2 bg-green-50 border border-green-200 rounded-md px-3 py-1">
      <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
      <span class="text-sm font-medium text-green-800">{{ currentDuration }}</span>
      
      <!-- Go to Detail Button -->
      <button
        @click.stop="viewTaskDetail"
        class="flex items-center px-2 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700"
      >
        <Square class="w-3 h-3 mr-1" />
        Stop Work
      </button>
    </div>
    
    <!-- Start Work Button -->
    <div v-else class="flex items-center space-x-2">
      <!-- Quick Start Button -->
      <button
        @click.stop="quickStartWork"
        :disabled="isStartingWork"
        class="flex items-center px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 disabled:opacity-50"
      >
        <Play class="w-3 h-3 mr-1" />
        Start
      </button>
      
      <!-- View Task Detail Button -->
      <button
        @click.stop="viewTaskDetail"
        class="flex items-center px-2 py-1 text-gray-600 hover:text-gray-800 rounded text-sm border border-gray-300 hover:border-gray-400"
      >
        <Eye class="w-3 h-3 mr-1" />
        Detail
      </button>
    </div>
    
    <!-- Work History Indicator -->
    <div v-if="workHistory.length > 0" class="flex items-center space-x-1 text-xs text-gray-500">
      <Clock class="w-3 h-3" />
      <span>{{ totalHoursWorked }}h</span>
    </div>
  </div>
  
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Play, Square, Clock, Eye } from 'lucide-vue-next'
import { useTimeTracking } from '@/modules/tasks/composables/useTimeTracking'
import type { Task } from '@/modules/tasks/types/task.types'

interface Props {
  task: Task
}

const props = defineProps<Props>()
const emit = defineEmits<{
  workStarted: [taskId: string]
  workStopped: [taskId: string]
}>()

const router = useRouter()

// Time tracking composable
const {
  activeTimeLog,
  isActivelyTracking,
  currentDuration,
  clockIn,
  clockOut,
  getActiveTimeLog,
  getTaskTimeLogs
} = useTimeTracking()

// Local state
const isStartingWork = ref(false)
const workHistory = ref<any[]>([])

// Computed
const isActivelyWorking = computed(() => 
  isActivelyTracking.value && activeTimeLog.value?.task_id === props.task.id
)

const totalHoursWorked = computed(() => {
  return workHistory.value
    .reduce((total, log) => total + (log.duration_minutes || 0), 0) / 60
    .toFixed(1)
})

// Methods
const getCurrentLocation = (): Promise<{ lat: number; lng: number; address?: string }> => {
  return new Promise((resolve, reject) => {
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

const quickStartWork = async () => {
  try {
    isStartingWork.value = true
    
    const location = await getCurrentLocation()
    
    const clockInData = {
      latitude: location.lat,
      longitude: location.lng,
      address: location.address,
      activity_type: 'work',
      description: `Started work on ${props.task.name}`,
      photos: []
    }
    
    await clockIn(props.task.id, clockInData)
    emit('workStarted', props.task.id)
    
  } catch (err: any) {
    console.error('Failed to start work:', err)
  } finally {
    isStartingWork.value = false
  }
}

const viewTaskDetail = () => {
  router.push(`/app/tasks/${props.task.id}`)
}


const loadWorkHistory = async () => {
  try {
    workHistory.value = await getTaskTimeLogs(props.task.id)
  } catch (err) {
    console.error('Failed to load work history:', err)
  }
}

// Lifecycle
onMounted(async () => {
  // Only load work history - active time log is loaded at page level
  await loadWorkHistory()
})
</script>