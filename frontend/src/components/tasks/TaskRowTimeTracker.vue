<template>
  <div class="flex items-center space-x-2">
    <!-- Active Work Indicator -->
    <div v-if="isActivelyWorking" class="flex items-center space-x-2 bg-green-50 border border-green-200 rounded-md px-3 py-1">
      <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
      <span class="text-sm font-medium text-green-800">{{ currentDuration }}</span>
      
      <!-- Quick Stop Button -->
      <button
        @click.stop="quickStopWork"
        :disabled="isStoppingWork"
        class="flex items-center px-2 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700 disabled:opacity-50"
      >
        <Square class="w-3 h-3 mr-1" />
        Stop
      </button>
    </div>
    
    <!-- Start Work Button -->
    <div v-else class="flex items-center space-x-2">
      <!-- Activity Type Quick Select -->
      <select 
        v-model="quickActivityType"
        class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:border-blue-500"
        @click.stop
      >
        <option value="work">Work</option>
        <option value="inspection">Inspection</option>
        <option value="planning">Planning</option>
        <option value="meeting">Meeting</option>
        <option value="travel">Travel</option>
      </select>
      
      <!-- Quick Start Button -->
      <button
        @click.stop="quickStartWork"
        :disabled="isStartingWork"
        class="flex items-center px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 disabled:opacity-50"
      >
        <Play class="w-3 h-3 mr-1" />
        Start
      </button>
    </div>
    
    <!-- Work History Indicator -->
    <div v-if="workHistory.length > 0" class="flex items-center space-x-1 text-xs text-gray-500">
      <Clock class="w-3 h-3" />
      <span>{{ totalHoursWorked }}h</span>
    </div>
  </div>
  
  <!-- Quick Stop Modal -->
  <div
    v-if="showQuickStopModal"
    @click.stop
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
  >
    <div class="bg-white rounded-lg p-4 w-full max-w-md mx-4">
      <h3 class="text-lg font-medium text-gray-900 mb-3">Stop Work Session</h3>
      
      <div class="space-y-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Completion Notes</label>
          <textarea 
            v-model="stopWorkData.description"
            placeholder="What did you accomplish?"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
          ></textarea>
        </div>
        
        <div class="flex items-center">
          <input
            v-model="stopWorkData.billable"
            type="checkbox"
            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
          />
          <label class="ml-2 text-sm text-gray-700">Billable time</label>
          
          <input
            v-if="stopWorkData.billable"
            v-model.number="stopWorkData.hourly_rate"
            type="number"
            min="0"
            step="0.01"
            placeholder="Rate"
            class="ml-2 w-20 px-2 py-1 border border-gray-300 rounded text-sm"
          />
        </div>
      </div>
      
      <div class="flex justify-end space-x-2 mt-4">
        <button
          @click="cancelQuickStop"
          class="px-3 py-1 text-gray-700 bg-gray-100 rounded hover:bg-gray-200 text-sm"
        >
          Cancel
        </button>
        <button
          @click="confirmQuickStop"
          :disabled="isStoppingWork"
          class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50 text-sm"
        >
          {{ isStoppingWork ? 'Stopping...' : 'Stop Work' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Play, Square, Clock } from 'lucide-vue-next'
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

// Time tracking composable
const {
  activeTimeLog,
  isActivelyTracking,
  currentDuration,
  clockIn,
  clockOut,
  getActiveTimeLog,
  getTimeLogsByTask
} = useTimeTracking()

// Local state
const isStartingWork = ref(false)
const isStoppingWork = ref(false)
const showQuickStopModal = ref(false)
const quickActivityType = ref('work')
const workHistory = ref<any[]>([])

const stopWorkData = ref({
  description: '',
  billable: false,
  hourly_rate: 0
})

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
      activity_type: quickActivityType.value,
      description: `Started ${quickActivityType.value} on ${props.task.name}`,
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

const quickStopWork = () => {
  showQuickStopModal.value = true
}

const confirmQuickStop = async () => {
  try {
    isStoppingWork.value = true
    
    const location = await getCurrentLocation()
    
    const clockOutData = {
      latitude: location.lat,
      longitude: location.lng,
      address: location.address,
      description: stopWorkData.value.description || `Completed work on ${props.task.name}`,
      billable: stopWorkData.value.billable,
      ...(stopWorkData.value.billable && stopWorkData.value.hourly_rate && { 
        hourly_rate: stopWorkData.value.hourly_rate 
      }),
      photos: []
    }
    
    await clockOut(clockOutData)
    emit('workStopped', props.task.id)
    
    // Reset and close modal
    stopWorkData.value = { description: '', billable: false, hourly_rate: 0 }
    showQuickStopModal.value = false
    
    // Reload work history
    loadWorkHistory()
    
  } catch (err: any) {
    console.error('Failed to stop work:', err)
  } finally {
    isStoppingWork.value = false
  }
}

const cancelQuickStop = () => {
  showQuickStopModal.value = false
  stopWorkData.value = { description: '', billable: false, hourly_rate: 0 }
}

const loadWorkHistory = async () => {
  try {
    workHistory.value = await getTimeLogsByTask(props.task.id)
  } catch (err) {
    console.error('Failed to load work history:', err)
  }
}

// Lifecycle
onMounted(async () => {
  await getActiveTimeLog()
  await loadWorkHistory()
})
</script>