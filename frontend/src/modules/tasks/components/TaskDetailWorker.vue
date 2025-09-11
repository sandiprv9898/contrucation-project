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

    <div class="px-4 py-4 space-y-4">
      <!-- Time Tracking - Most Important Section -->
      <div class="bg-white rounded-lg shadow-sm p-4 border border-blue-200">
        <h2 class="text-base font-semibold text-gray-900 mb-3 flex items-center">
          <Clock class="w-4 h-4 mr-2 text-blue-600" />
          Time Tracking
        </h2>
        
        <!-- Current Session Status -->
        <div v-if="isActivelyWorking" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
          <div class="text-center">
            <div class="w-12 h-12 bg-green-500 rounded-full mx-auto mb-2 flex items-center justify-center">
              <Clock class="w-5 h-5 text-white" />
            </div>
            <div class="text-2xl font-semibold text-green-800 mb-1">{{ currentDuration }}</div>
            <div class="text-sm text-green-700 mb-4">Work in Progress</div>
            <button
              @click="showStopWorkModal = true"
              class="w-full bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center space-x-2 transition-colors"
            >
              <Square class="w-4 h-4" />
              <span>Stop Work</span>
            </button>
          </div>
        </div>
        
        <div v-else class="text-center">
          <div class="w-12 h-12 bg-blue-100 rounded-full mx-auto mb-3 flex items-center justify-center">
            <Play class="w-5 h-5 text-blue-600" />
          </div>
          <div class="text-sm text-gray-700 mb-4">Ready to start working?</div>
          <button
            @click="startWork"
            :disabled="isStartingWork"
            class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center space-x-2 transition-colors disabled:opacity-50"
          >
            <Play class="w-4 h-4" />
            <span>{{ isStartingWork ? 'Starting...' : 'Start Work' }}</span>
          </button>
        </div>

        <!-- Time Summary -->
        <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-gray-200">
          <div class="text-center bg-blue-50 rounded-lg p-3">
            <div class="text-xl font-semibold text-blue-600 mb-1">{{ task.estimated_hours || 0 }}h</div>
            <div class="text-xs font-medium text-blue-700">Planned</div>
          </div>
          <div class="text-center bg-green-50 rounded-lg p-3">
            <div class="text-xl font-semibold text-green-600 mb-1">{{ task.actual_hours || 0 }}h</div>
            <div class="text-xs font-medium text-green-700">Logged</div>
          </div>
        </div>
      </div>

      <!-- Task Information -->
      <div class="bg-white rounded-lg shadow-sm p-4">
        <h2 class="text-base font-semibold text-gray-900 mb-3 flex items-center">
          <FileText class="w-4 h-4 mr-2 text-gray-600" />
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
      <div class="bg-white rounded-lg shadow-sm p-4">
        <h2 class="text-base font-semibold text-gray-900 mb-3 flex items-center">
          <TrendingUp class="w-4 h-4 mr-2 text-green-600" />
          Progress Update
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
          <label class="block text-sm font-medium text-gray-700 mb-2">Quick Update</label>
          <div class="grid grid-cols-4 gap-2">
            <button
              v-for="percentage in [25, 50, 75, 100]"
              :key="percentage"
              @click="newProgress = percentage; updateProgress()"
              :disabled="percentage === task.progress_percentage"
              class="py-2 px-2 rounded-lg font-medium text-xs transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              :class="{
                'bg-green-500 text-white': percentage === 100,
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
            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            Update to {{ newProgress }}%
          </button>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-lg shadow-sm p-4">
        <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
          <Zap class="w-4 h-4 mr-2 text-yellow-600" />
          Quick Actions
        </h2>
        
        <div class="grid grid-cols-1 gap-3">
          <button
            v-if="task.status.value !== 'completed'"
            @click="markComplete"
            class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-3 rounded-lg flex items-center justify-center space-x-2 transition-colors text-sm"
          >
            <CheckCircle class="w-4 h-4" />
            <span>Mark Complete</span>
          </button>
          
          <button
            v-if="task.status.value === 'not_started'"
            @click="markInProgress"
            class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-3 rounded-lg flex items-center justify-center space-x-2 transition-colors text-sm"
          >
            <Play class="w-4 h-4" />
            <span>Start Task</span>
          </button>
        </div>
      </div>

      <!-- Comments & Notes Section -->
      <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-base font-semibold text-gray-900 flex items-center">
            <MessageSquare class="w-4 h-4 mr-2 text-gray-600" />
            Notes & Comments
          </h2>
          <button
            @click="showNoteModal = true"
            class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-1 px-3 rounded text-sm flex items-center space-x-1 transition-colors"
          >
            <Plus class="w-3 h-3" />
            <span>Add</span>
          </button>
        </div>
        
        <!-- Notes List -->
        <div v-if="notes.length > 0" class="space-y-2">
          <div 
            v-for="note in notes" 
            :key="note.id"
            class="bg-gray-50 rounded-lg p-3 border-l-4 border-blue-500"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <p class="text-sm text-gray-800 mb-1">{{ note.content }}</p>
                <div class="flex items-center text-xs text-gray-500 space-x-2">
                  <span>{{ formatDateTime(note.created_at) }}</span>
                  <span v-if="note.type" class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">{{ note.type }}</span>
                </div>
              </div>
              <button
                @click="deleteNote(note.id)"
                class="text-gray-400 hover:text-red-500 transition-colors ml-2"
              >
                <X class="w-3 h-3" />
              </button>
            </div>
          </div>
        </div>
        
        <!-- Empty State -->
        <div v-else class="text-center py-6">
          <div class="w-12 h-12 bg-gray-100 rounded-full mx-auto mb-2 flex items-center justify-center">
            <MessageSquare class="w-5 h-5 text-gray-400" />
          </div>
          <p class="text-sm text-gray-500">No notes yet</p>
          <p class="text-xs text-gray-400">Add your first note or comment</p>
        </div>
      </div>

      <!-- Work Sessions History -->
      <div class="bg-white rounded-lg shadow-sm p-4">
        <h2 class="text-base font-semibold text-gray-900 mb-3 flex items-center">
          <History class="w-4 h-4 mr-2 text-gray-600" />
          Work Sessions & Location
        </h2>
        <WorkSessionList :task-id="task.id" :show-location="true" />
      </div>
    </div>

    <!-- Stop Work Modal -->
    <div
      v-if="showStopWorkModal"
      class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4"
      @click.self="showStopWorkModal = false"
    >
      <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Stop Work Session</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">What did you accomplish?</label>
            <textarea 
              v-model="stopWorkData.description"
              placeholder="Tell us what you completed today..."
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
            ></textarea>
          </div>
          
          <!-- Quick Accomplishment Options -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Quick Options</label>
            <div class="grid grid-cols-1 gap-2">
              <button
                v-for="option in quickOptions"
                :key="option"
                @click="stopWorkData.description = option"
                class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-left text-sm transition-colors"
              >
                {{ option }}
              </button>
            </div>
          </div>
        </div>
        
        <div class="flex space-x-3 mt-6">
          <button
            @click="showStopWorkModal = false"
            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition-colors"
          >
            Cancel
          </button>
          <button
            @click="confirmStopWork"
            :disabled="isStoppingWork"
            class="flex-1 bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg transition-colors disabled:opacity-50"
          >
            {{ isStoppingWork ? 'Stopping...' : 'Stop Work' }}
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
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Note</h3>
        
        <!-- Note Type Selection -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Note Type</label>
          <div class="grid grid-cols-3 gap-2">
            <button
              v-for="type in [{ value: 'general', label: 'General' }, { value: 'progress', label: 'Progress' }, { value: 'issue', label: 'Issue' }]"
              :key="type.value"
              @click="noteType = type.value"
              :class="[
                'py-2 px-3 rounded-lg text-sm font-medium transition-colors',
                noteType === type.value 
                  ? 'bg-blue-500 text-white' 
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              {{ type.label }}
            </button>
          </div>
        </div>
        
        <!-- Note Content -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Note</label>
          <textarea 
            v-model="noteText"
            placeholder="Add your note here..."
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
          ></textarea>
        </div>
        
        <!-- Quick Note Templates -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Quick Templates</label>
          <div class="space-y-1">
            <button
              v-for="template in quickNoteTemplates"
              :key="template"
              @click="noteText = template"
              class="w-full text-left p-2 text-sm bg-gray-50 hover:bg-gray-100 rounded transition-colors"
            >
              {{ template }}
            </button>
          </div>
        </div>
        
        <div class="flex space-x-3">
          <button
            @click="showNoteModal = false"
            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-3 rounded-lg transition-colors"
          >
            Cancel
          </button>
          <button
            @click="saveNote"
            :disabled="!noteText.trim()"
            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-3 rounded-lg transition-colors disabled:opacity-50"
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
  CheckCircle, MessageSquare, Plus, X, History
} from 'lucide-vue-next'
import { useTimeTracking } from '../composables/useTimeTracking'
import { useTaskStore } from '../stores/task.store'
import { useNotifications } from '@/composables/useNotifications'
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
const { showSuccess, showError, showWarning, showInfo } = useNotifications()

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
const noteType = ref('general')

// Removed local notification state - now using global notifications
const notes = ref([
  {
    id: '1',
    content: 'Started foundation work. Ground conditions are good.',
    type: 'progress',
    created_at: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000).toISOString(),
    user: 'John Worker'
  },
  {
    id: '2', 
    content: 'Materials delivered on schedule. Ready to proceed with next phase.',
    type: 'general',
    created_at: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString(),
    user: 'John Worker'
  }
])

const stopWorkData = ref({
  description: ''
})

const quickOptions = [
  'Completed assigned tasks',
  'Finished concrete work',
  'Installed electrical components',
  'Completed plumbing work',
  'Finished framing work',
  'Completed painting work'
]

const quickNoteTemplates = [
  'Work completed as expected',
  'Encountered minor delays due to weather',
  'Materials delivered on time',
  'Quality check passed',
  'Need additional resources',
  'Safety protocols followed'
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
    showSuccess('Work Started', 'Successfully clocked in to work on this task')
    emit('updated')
    
  } catch (err: any) {
    console.error('Failed to start work:', err)
    
    // Extract error message
    const errorMessage = extractErrorMessage(err)
    
    // Handle specific error cases
    if (errorMessage.toLowerCase().includes('clock out from your current task') || 
        errorMessage.toLowerCase().includes('must clock out') ||
        err.response?.status === 409) {
      showError(
        'Cannot Start Work', 
        'You must clock out from your current task before starting a new one. Please stop your current work session first.'
      )
    } else if (errorMessage.toLowerCase().includes('location') || 
               errorMessage.toLowerCase().includes('geolocation')) {
      showError(
        'Location Required', 
        'Location access is required to start work. Please enable location services.'
      )
    } else if (err.response?.status === 403) {
      showError(
        'Permission Denied', 
        'You do not have permission to start work on this task.'
      )
    } else if (err.response?.status === 404) {
      showError(
        'Task Not Found', 
        'This task could not be found. Please refresh the page and try again.'
      )
    } else {
      showError(
        'Failed to Start Work', 
        errorMessage || 'An unexpected error occurred while trying to start work.'
      )
    }
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
      photos: []
    }
    
    await clockOut(clockOutData)
    showSuccess('Work Completed', 'Successfully clocked out and saved your work session')
    showStopWorkModal.value = false
    stopWorkData.value = { description: '' }
    emit('updated')
    
  } catch (err: any) {
    console.error('Failed to stop work:', err)
    const errorMessage = extractErrorMessage(err)
    showError('Failed to Stop Work', errorMessage)
  } finally {
    isStoppingWork.value = false
  }
}

const updateProgress = async () => {
  try {
    const success = await taskStore.updateTaskProgress(props.task.id, newProgress.value)
    if (success) {
      showSuccess('Progress Updated', `Task progress updated to ${newProgress.value}%`)
      emit('updated')
    } else {
      showError('Update Failed', 'Failed to update task progress. Please try again.')
    }
  } catch (err: any) {
    const errorMessage = extractErrorMessage(err) || 'Failed to update progress'
    showError('Update Failed', errorMessage)
  }
}

const markComplete = async () => {
  try {
    const success = await taskStore.updateTaskStatus(props.task.id, 'completed')
    if (success) {
      newProgress.value = 100
      await updateProgress()
      showSuccess('Task Completed', 'Task has been marked as completed successfully!')
      emit('updated')
    } else {
      showError('Update Failed', 'Failed to mark task as complete. Please try again.')
    }
  } catch (err: any) {
    const errorMessage = extractErrorMessage(err) || 'Failed to complete task'
    showError('Update Failed', errorMessage)
  }
}

const markInProgress = async () => {
  try {
    const success = await taskStore.updateTaskStatus(props.task.id, 'in_progress')
    if (success) {
      showSuccess('Task Started', 'Task has been marked as in progress')
      emit('updated')
    } else {
      showError('Update Failed', 'Failed to start task. Please try again.')
    }
  } catch (err: any) {
    const errorMessage = extractErrorMessage(err) || 'Failed to start task'
    showError('Update Failed', errorMessage)
  }
}

const saveNote = async () => {
  if (!noteText.value.trim()) return
  
  try {
    const newNote = {
      id: Date.now().toString(),
      content: noteText.value.trim(),
      type: noteType.value,
      created_at: new Date().toISOString(),
      user: 'Current User'
    }
    
    notes.value.unshift(newNote)
    noteText.value = ''
    noteType.value = 'general'
    showNoteModal.value = false
    
    showSuccess('Note Added', 'Your note has been saved successfully')
    
    // In a real app, this would save to the backend
    console.log('Note saved:', newNote)
    // emit('updated')
  } catch (err: any) {
    showError('Failed to Save Note', err.message || 'Could not save your note')
  }
}

const deleteNote = (noteId: string) => {
  notes.value = notes.value.filter(note => note.id !== noteId)
  // In a real app, this would delete from the backend
  console.log('Note deleted:', noteId)
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

const formatDateTime = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Error handling helper
const extractErrorMessage = (err: any): string => {
  if (err.response?.data?.message) {
    return err.response.data.message
  } else if (err.response?.data?.error) {
    return err.response.data.error
  } else if (err.response?.data) {
    // Handle string responses
    if (typeof err.response.data === 'string') {
      return err.response.data
    }
  } else if (err.message) {
    return err.message
  }
  return 'An unexpected error occurred'
}

// Notification functions moved to global composable

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