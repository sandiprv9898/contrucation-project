<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-10">
      <div class="px-4 py-4 flex items-center space-x-3">
        <button
          @click="$router.push('/app/worker/tasks')"
          class="p-2 text-gray-600 hover:text-gray-900 transition-colors"
        >
          <ArrowLeft class="w-5 h-5" />
        </button>
        <div class="flex-1 min-w-0">
          <h1 class="text-lg font-semibold text-gray-900 truncate">{{ task.name }}</h1>
          <div class="flex items-center space-x-2 mt-1">
            <span 
              :class="[
                'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                getStatusClasses(task.status.value)
              ]"
            >
              {{ task.status.label }}
            </span>
            <span 
              :class="[
                'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                getPriorityClasses(task.priority.value)
              ]"
            >
              {{ task.priority.label }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="px-4 py-6 space-y-6">
      <!-- Time Tracking - Most Important Section -->
      <div class="bg-white rounded-xl shadow-sm p-6 border-2 border-blue-200">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <Clock class="w-5 h-5 mr-2 text-blue-600" />
          Time Tracking
        </h2>
        
        <!-- Current Session Status -->
        <div v-if="isActivelyWorking" class="bg-green-50 border-4 border-green-400 rounded-2xl p-6 mb-6 shadow-lg">
          <div class="text-center">
            <div class="w-20 h-20 bg-green-500 rounded-full mx-auto mb-3 flex items-center justify-center animate-pulse">
              <Clock class="w-8 h-8 text-white" />
            </div>
            <div class="text-4xl font-bold text-green-800 mb-2">{{ currentDuration }}</div>
            <div class="text-lg text-green-700 mb-6">🔧 Work in Progress</div>
            <button
              @click="showStopWorkModal = true"
              class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-6 px-8 rounded-2xl flex items-center justify-center space-x-3 transition-all transform hover:scale-105 shadow-lg text-xl"
            >
              <Square class="w-6 h-6" />
              <span>STOP WORK</span>
            </button>
          </div>
        </div>
        
        <div v-else class="text-center">
          <div class="w-16 h-16 bg-blue-100 rounded-full mx-auto mb-4 flex items-center justify-center">
            <Play class="w-8 h-8 text-blue-600" />
          </div>
          <div class="text-lg text-gray-700 mb-6">👷 Ready to start working?</div>
          <button
            @click="startWork"
            :disabled="isStartingWork"
            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-6 px-8 rounded-2xl flex items-center justify-center space-x-3 transition-all transform hover:scale-105 shadow-lg text-xl disabled:opacity-50"
          >
            <Play class="w-6 h-6" />
            <span>{{ isStartingWork ? '⏳ STARTING...' : '🚀 START WORK' }}</span>
          </button>
        </div>

        <!-- Time Summary -->
        <div class="grid grid-cols-2 gap-4 mt-6 pt-6 border-t-2 border-gray-200">
          <div class="text-center bg-blue-50 rounded-xl p-4">
            <div class="text-3xl font-bold text-blue-600 mb-1">{{ task.estimated_hours || 0 }}h</div>
            <div class="text-sm font-medium text-blue-700">📋 Planned</div>
          </div>
          <div class="text-center bg-green-50 rounded-xl p-4">
            <div class="text-3xl font-bold text-green-600 mb-1">{{ task.actual_hours || 0 }}h</div>
            <div class="text-sm font-medium text-green-700">✅ Logged</div>
          </div>
        </div>
      </div>

      <!-- Task Information -->
      <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <FileText class="w-5 h-5 mr-2 text-gray-600" />
          Task Details
        </h2>
        
        <div v-if="task.description" class="mb-4">
          <h3 class="font-medium text-gray-900 mb-2">Description</h3>
          <p class="text-gray-700 leading-relaxed">{{ task.description }}</p>
        </div>

        <div class="grid grid-cols-1 gap-4">
          <div v-if="task.due_date" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <span class="font-medium text-gray-700">Due Date</span>
            <span 
              :class="{
                'text-red-600 font-medium': task.is_overdue,
                'text-yellow-600 font-medium': task.is_due_soon,
                'text-gray-900': !task.is_overdue && !task.is_due_soon
              }"
            >
              {{ formatDate(task.due_date) }}
              <span v-if="task.is_overdue" class="text-red-500">(Overdue)</span>
              <span v-else-if="task.is_due_soon" class="text-yellow-500">(Due Soon)</span>
            </span>
          </div>
          
          <div v-if="task.project" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <span class="font-medium text-gray-700">Project</span>
            <span class="text-gray-900">{{ task.project.name }}</span>
          </div>
          
          <div v-if="task.assigned_to" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <span class="font-medium text-gray-700">Assigned To</span>
            <span class="text-gray-900">{{ task.assigned_to.name }}</span>
          </div>
        </div>
      </div>

      <!-- Progress Update -->
      <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <TrendingUp class="w-5 h-5 mr-2 text-green-600" />
          📊 Progress Update
        </h2>
        
        <div class="mb-6">
          <div class="flex items-center justify-between text-lg font-medium text-gray-700 mb-3">
            <span>Current Progress</span>
            <span class="text-2xl font-bold text-blue-600">{{ task.progress_percentage }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-4 shadow-inner">
            <div 
              class="h-4 rounded-full transition-all duration-500 shadow-sm"
              :class="{
                'bg-gradient-to-r from-green-400 to-green-600': task.progress_percentage >= 80,
                'bg-gradient-to-r from-yellow-400 to-yellow-600': task.progress_percentage >= 50,
                'bg-gradient-to-r from-blue-400 to-blue-600': task.progress_percentage < 50
              }"
              :style="{ width: `${task.progress_percentage}%` }"
            ></div>
          </div>
        </div>

        <!-- Quick Progress Buttons -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-3">Quick Update</label>
          <div class="grid grid-cols-4 gap-2">
            <button
              v-for="percentage in [25, 50, 75, 100]"
              :key="percentage"
              @click="newProgress = percentage; updateProgress()"
              :disabled="percentage === task.progress_percentage"
              class="py-3 px-2 rounded-xl font-medium text-sm transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
              :class="{
                'bg-green-600 text-white': percentage === 100,
                'bg-yellow-500 text-white': percentage === 75,
                'bg-blue-500 text-white': percentage === 50,
                'bg-gray-400 text-white': percentage === 25
              }"
            >
              {{ percentage }}%
            </button>
          </div>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">Custom Progress</label>
            <input
              v-model="newProgress"
              type="range"
              min="0"
              max="100"
              step="5"
              class="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer"
            />
            <div class="flex justify-between text-sm text-gray-500 mt-2">
              <span>0%</span>
              <span class="font-bold text-lg text-blue-600">{{ newProgress }}%</span>
              <span>100%</span>
            </div>
          </div>
          
          <button
            @click="updateProgress"
            :disabled="newProgress === task.progress_percentage"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all transform hover:scale-105 shadow-lg text-lg"
          >
            📈 Update to {{ newProgress }}%
          </button>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
          <Zap class="w-5 h-5 mr-2 text-yellow-600" />
          ⚡ Quick Actions
        </h2>
        
        <div class="grid grid-cols-1 gap-4">
          <button
            v-if="task.status.value !== 'completed'"
            @click="markComplete"
            class="bg-green-600 hover:bg-green-700 text-white font-bold py-5 px-6 rounded-2xl flex items-center justify-center space-x-3 transition-all transform hover:scale-105 shadow-lg text-lg"
          >
            <CheckCircle class="w-6 h-6" />
            <span>✅ MARK COMPLETE</span>
          </button>
          
          <button
            v-if="task.status.value === 'not_started'"
            @click="markInProgress"
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-5 px-6 rounded-2xl flex items-center justify-center space-x-3 transition-all transform hover:scale-105 shadow-lg text-lg"
          >
            <Play class="w-6 h-6" />
            <span>🔄 START TASK</span>
          </button>
          
          <button
            @click="showNoteModal = true"
            class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-5 px-6 rounded-2xl flex items-center justify-center space-x-3 transition-all transform hover:scale-105 shadow-lg text-lg"
          >
            <MessageSquare class="w-6 h-6" />
            <span>📝 ADD NOTE</span>
          </button>
          
          <button
            @click="capturePhoto"
            class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-5 px-6 rounded-2xl flex items-center justify-center space-x-3 transition-all transform hover:scale-105 shadow-lg text-lg"
          >
            <Camera class="w-6 h-6" />
            <span>📸 TAKE PHOTO</span>
          </button>
        </div>
      </div>

      <!-- Work Sessions History - Simplified -->
      <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <History class="w-5 h-5 mr-2 text-gray-600" />
          Recent Work
        </h2>
        <WorkSessionList :task-id="task.id" />
      </div>
    </div>

    <!-- Stop Work Modal -->
    <div
      v-if="showStopWorkModal"
      class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4"
      @click.self="showStopWorkModal = false"
    >
      <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl">
        <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">🛑 Stop Work Session</h3>
        
        <div class="space-y-6">
          <div>
            <label class="block text-lg font-medium text-gray-700 mb-3">What did you accomplish?</label>
            <textarea 
              v-model="stopWorkData.description"
              placeholder="Tell us what you completed today..."
              rows="4"
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none text-lg"
            ></textarea>
          </div>
          
          <!-- Quick Accomplishment Options -->
          <div>
            <label class="block text-lg font-medium text-gray-700 mb-3">Quick Options</label>
            <div class="grid grid-cols-1 gap-2">
              <button
                v-for="option in quickOptions"
                :key="option"
                @click="stopWorkData.description = option"
                class="p-3 bg-gray-100 hover:bg-gray-200 rounded-lg text-left font-medium transition-colors"
              >
                {{ option }}
              </button>
            </div>
          </div>
          
          <div class="bg-blue-50 rounded-xl p-4">
            <label class="flex items-center justify-between">
              <div class="flex items-center">
                <input
                  v-model="stopWorkData.billable"
                  type="checkbox"
                  class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-3"
                />
                <span class="text-lg font-medium text-gray-700">💰 Billable time</span>
              </div>
              
              <input
                v-if="stopWorkData.billable"
                v-model.number="stopWorkData.hourly_rate"
                type="number"
                min="0"
                step="1"
                placeholder="$/hr"
                class="w-20 px-3 py-2 border-2 border-gray-300 rounded-lg text-lg font-medium text-center"
              />
            </label>
          </div>
        </div>
        
        <div class="flex space-x-4 mt-8">
          <button
            @click="showStopWorkModal = false"
            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-4 px-6 rounded-xl transition-all text-lg"
          >
            Cancel
          </button>
          <button
            @click="confirmStopWork"
            :disabled="isStoppingWork"
            class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-xl transition-all disabled:opacity-50 text-lg transform hover:scale-105"
          >
            {{ isStoppingWork ? '⏳ Stopping...' : '✅ STOP WORK' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Add Note Modal -->
    <div
      v-if="showNoteModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showNoteModal = false"
    >
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Add Note</h3>
        
        <textarea 
          v-model="noteText"
          placeholder="Add your note here..."
          rows="4"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
        ></textarea>
        
        <div class="flex space-x-3 mt-4">
          <button
            @click="showNoteModal = false"
            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-4 rounded-xl transition-colors"
          >
            Cancel
          </button>
          <button
            @click="saveNote"
            :disabled="!noteText.trim()"
            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-xl transition-colors disabled:opacity-50"
          >
            Save Note
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { 
  ArrowLeft, Clock, Play, Square, FileText, TrendingUp, Zap, 
  CheckCircle, MessageSquare, History, Camera
} from 'lucide-vue-next'
import { useTimeTracking } from '../composables/useTimeTracking'
import { useTaskStore } from '../stores/task.store'
import WorkSessionList from './WorkSessionList.vue'
import type { Task } from '../types/task.types'

interface Props {
  task: Task
}

interface Emits {
  (e: 'updated'): void
  (e: 'closed'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const taskStore = useTaskStore()

// Time tracking composable
const {
  activeTimeLog,
  isActivelyTracking,
  currentDuration,
  clockIn,
  clockOut,
  getActiveTimeLog
} = useTimeTracking()

// Local state
const newProgress = ref(props.task.progress_percentage)
const isStartingWork = ref(false)
const isStoppingWork = ref(false)
const showStopWorkModal = ref(false)
const showNoteModal = ref(false)
const noteText = ref('')

const stopWorkData = ref({
  description: '',
  billable: true,
  hourly_rate: 25
})

const quickOptions = [
  'Completed assigned tasks',
  'Finished concrete work',
  'Installed electrical components',
  'Completed plumbing work',
  'Finished framing work',
  'Completed painting work'
]

// Computed
const isActivelyWorking = computed(() => 
  isActivelyTracking.value && activeTimeLog.value?.task_id === props.task.id
)

// Methods
const startWork = async () => {
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
    emit('updated')
    
  } catch (err) {
    console.error('Failed to start work:', err)
  } finally {
    isStartingWork.value = false
  }
}

const confirmStopWork = async () => {
  try {
    isStoppingWork.value = true
    
    const location = await getCurrentLocation()
    
    const clockOutData = {
      latitude: location.lat,
      longitude: location.lng,
      address: location.address,
      description: stopWorkData.value.description || `Completed work on ${props.task.name}`,
      billable: stopWorkData.value.billable,
      hourly_rate: stopWorkData.value.billable ? stopWorkData.value.hourly_rate : undefined,
      photos: []
    }
    
    await clockOut(clockOutData)
    showStopWorkModal.value = false
    stopWorkData.value = { description: '', billable: true, hourly_rate: 25 }
    emit('updated')
    
  } catch (err) {
    console.error('Failed to stop work:', err)
  } finally {
    isStoppingWork.value = false
  }
}

const updateProgress = async () => {
  const success = await taskStore.updateTaskProgress(props.task.id, newProgress.value)
  if (success) {
    emit('updated')
  }
}

const markComplete = async () => {
  const success = await taskStore.updateTaskStatus(props.task.id, 'completed')
  if (success) {
    newProgress.value = 100
    await updateProgress()
    emit('updated')
  }
}

const markInProgress = async () => {
  const success = await taskStore.updateTaskStatus(props.task.id, 'in_progress')
  if (success) {
    emit('updated')
  }
}

const saveNote = async () => {
  // In a real app, this would save the note to the task
  console.log('Saving note:', noteText.value)
  noteText.value = ''
  showNoteModal.value = false
  // emit('updated')
}

const capturePhoto = () => {
  // In a real app, this would open camera to capture work photos
  console.log('Opening camera for work photo')
  // Would use navigator.mediaDevices.getUserMedia for camera access
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

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric'
  })
}

const getStatusClasses = (status: string): string => {
  const classes = {
    'not_started': 'bg-gray-100 text-gray-800',
    'in_progress': 'bg-blue-100 text-blue-800',
    'review': 'bg-yellow-100 text-yellow-800',
    'completed': 'bg-green-100 text-green-800',
    'cancelled': 'bg-red-100 text-red-800',
    'on_hold': 'bg-orange-100 text-orange-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPriorityClasses = (priority: string): string => {
  const classes = {
    'low': 'bg-green-100 text-green-800',
    'medium': 'bg-yellow-100 text-yellow-800',
    'high': 'bg-orange-100 text-orange-800',
    'critical': 'bg-red-100 text-red-800'
  }
  return classes[priority] || 'bg-gray-100 text-gray-800'
}

// Load active time log on mount
onMounted(() => {
  getActiveTimeLog()
})
</script>