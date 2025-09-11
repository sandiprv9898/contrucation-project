<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-start">
      <div class="flex-1 min-w-0">
        <h2 class="text-2xl font-bold text-gray-900 truncate">{{ task.name }}</h2>
        <div class="flex items-center space-x-3 mt-2">
          <span 
            :class="[
              'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
              getStatusClasses(task.status.value)
            ]"
          >
            {{ task.status.label }}
          </span>
          
          <span 
            :class="[
              'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
              getPriorityClasses(task.priority.value)
            ]"
          >
            {{ task.priority.label }}
          </span>
          
          <span 
            :class="[
              'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
              getTypeClasses(task.task_type.value)
            ]"
          >
            {{ task.task_type.label }}
          </span>
        </div>
      </div>
      
      <div class="flex items-center space-x-2 ml-4">
        <button
          @click="$emit('closed')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
          title="Close"
        >
          <X class="w-6 h-6" />
        </button>
      </div>
    </div>

    <!-- Progress Bar -->
    <div class="bg-gray-100 rounded-lg p-4">
      <div class="flex justify-between items-center mb-2">
        <span class="text-sm font-medium text-gray-700">Progress</span>
        <span class="text-sm font-medium text-gray-900">{{ task.progress_percentage }}%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-3">
        <div 
          class="bg-blue-600 h-3 rounded-full transition-all duration-300" 
          :style="{ width: `${task.progress_percentage}%` }"
        ></div>
      </div>
      
      <!-- Progress Controls -->
      <div class="flex items-center space-x-2 mt-3">
        <label class="text-sm font-medium text-gray-700">Update Progress:</label>
        <input
          v-model="newProgress"
          type="range"
          min="0"
          max="100"
          step="5"
          class="flex-1"
        />
        <span class="text-sm text-gray-600 w-12">{{ newProgress }}%</span>
        <button
          @click="updateProgress"
          :disabled="newProgress === task.progress_percentage"
          class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Update
        </button>
      </div>
    </div>

    <!-- Task Information Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Left Column -->
      <div class="space-y-4">
        <div>
          <h3 class="text-lg font-medium text-gray-900 mb-3">Task Details</h3>
          
          <!-- Description -->
          <div v-if="task.description" class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <p class="text-gray-600 text-sm">{{ task.description }}</p>
          </div>
          
          <!-- Project & Phase -->
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div v-if="task.project">
              <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
              <div class="flex items-center space-x-2">
                <Folder class="w-4 h-4 text-gray-400" />
                <span class="text-sm text-gray-900">{{ task.project.name }}</span>
              </div>
            </div>
            
            <div v-if="task.phase">
              <label class="block text-sm font-medium text-gray-700 mb-1">Phase</label>
              <div class="flex items-center space-x-2">
                <Layers class="w-4 h-4 text-gray-400" />
                <span class="text-sm text-gray-900">{{ task.phase.name }}</span>
              </div>
            </div>
          </div>
          
          <!-- Assignment -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Assignment</label>
            <div class="flex items-center justify-between">
              <div v-if="task.assigned_to" class="flex items-center space-x-2">
                <User class="w-4 h-4 text-gray-400" />
                <span class="text-sm text-gray-900">{{ task.assigned_to.name }}</span>
                <span class="text-xs text-gray-500">({{ task.assigned_to.role }})</span>
              </div>
              <span v-else class="text-sm text-gray-500">Unassigned</span>
              
              <button
                @click="showAssignModal = true"
                class="text-xs text-blue-600 hover:text-blue-800"
              >
                {{ task.assigned_to ? 'Reassign' : 'Assign' }}
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Right Column -->
      <div class="space-y-4">
        <div>
          <h3 class="text-lg font-medium text-gray-900 mb-3">Timeline & Tracking</h3>
          
          <!-- Dates -->
          <div class="grid grid-cols-1 gap-4 mb-4">
            <div v-if="task.start_date">
              <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
              <div class="flex items-center space-x-2">
                <Calendar class="w-4 h-4 text-gray-400" />
                <span class="text-sm text-gray-900">{{ formatDate(task.start_date) }}</span>
              </div>
            </div>
            
            <div v-if="task.due_date">
              <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
              <div class="flex items-center space-x-2">
                <Calendar class="w-4 h-4" :class="{ 'text-red-500': task.is_overdue, 'text-gray-400': !task.is_overdue }" />
                <span class="text-sm" :class="{ 'text-red-600': task.is_overdue, 'text-gray-900': !task.is_overdue }">
                  {{ formatDate(task.due_date) }}
                  <span v-if="task.is_overdue" class="text-red-500 font-medium">(Overdue)</span>
                  <span v-else-if="task.is_due_soon" class="text-yellow-600 font-medium">(Due Soon)</span>
                </span>
              </div>
            </div>
            
            <div v-if="task.completed_at">
              <label class="block text-sm font-medium text-gray-700 mb-1">Completed At</label>
              <div class="flex items-center space-x-2">
                <CheckCircle class="w-4 h-4 text-green-500" />
                <span class="text-sm text-gray-900">{{ formatDateTime(task.completed_at) }}</span>
              </div>
            </div>
          </div>
          
          <!-- Time Tracking -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Time Tracking</label>
            <div class="bg-gray-50 rounded-md p-3 space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Estimated:</span>
                <span class="font-medium">{{ task.estimated_hours || 0 }}h</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Actual:</span>
                <span class="font-medium">{{ task.actual_hours || 0 }}h</span>
              </div>
              <div v-if="task.estimated_hours" class="flex justify-between text-sm">
                <span class="text-gray-600">Variance:</span>
                <span 
                  class="font-medium"
                  :class="{
                    'text-green-600': (task.actual_hours || 0) <= task.estimated_hours,
                    'text-red-600': (task.actual_hours || 0) > task.estimated_hours
                  }"
                >
                  {{ getTimeVariance() }}h
                </span>
              </div>
            </div>
            
            <!-- Log Time -->
            <div class="flex items-center space-x-2 mt-2">
              <label class="text-sm font-medium text-gray-700">Log Time:</label>
              <input
                v-model="timeToLog"
                type="number"
                min="0.1"
                max="24"
                step="0.1"
                placeholder="Hours"
                class="w-20 px-2 py-1 text-sm border border-gray-300 rounded"
              />
              <button
                @click="logTime"
                :disabled="!timeToLog || timeToLog <= 0"
                class="px-3 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Log
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Status Actions -->
    <div class="bg-gray-50 rounded-lg p-4">
      <h3 class="text-lg font-medium text-gray-900 mb-3">Quick Actions</h3>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="status in availableStatuses"
          :key="status.value"
          @click="updateStatus(status.value)"
          :disabled="status.value === task.status.value"
          class="px-3 py-1 text-sm rounded-md border transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          :class="getStatusButtonClasses(status.value)"
        >
          {{ status.label }}
        </button>
      </div>
    </div>

    <!-- Metadata -->
    <div class="border-t border-gray-200 pt-4">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600">
        <div>
          <span class="font-medium">Created:</span>
          <div>{{ formatDateTime(task.created_at) }}</div>
        </div>
        <div v-if="task.created_by">
          <span class="font-medium">Created by:</span>
          <div>{{ task.created_by.name }}</div>
        </div>
        <div>
          <span class="font-medium">Updated:</span>
          <div>{{ formatDateTime(task.updated_at) }}</div>
        </div>
        <div>
          <span class="font-medium">Task ID:</span>
          <div class="font-mono">{{ task.id }}</div>
        </div>
      </div>
    </div>

    <!-- Assignment Modal -->
    <div
      v-if="showAssignModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="showAssignModal = false"
    >
      <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Task</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Assign to:</label>
            <select
              v-model="selectedAssignee"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">Unassigned</option>
              <option value="user-1">John Smith (Field Worker)</option>
              <option value="user-2">Jane Manager (Project Manager)</option>
              <option value="user-3">Mike Worker (Field Worker)</option>
              <option value="user-4">Sarah Inspector (Supervisor)</option>
            </select>
          </div>
        </div>
        
        <div class="flex justify-end space-x-3 mt-6">
          <button
            @click="showAssignModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="assignTask"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
          >
            Assign
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { 
  X, Calendar, User, Folder, Layers, CheckCircle, Clock
} from 'lucide-vue-next'
import { useTaskStore } from '../stores/task.store'
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

// Local state
const newProgress = ref(props.task.progress_percentage)
const timeToLog = ref<number | null>(null)
const showAssignModal = ref(false)
const selectedAssignee = ref(props.task.assigned_to?.id || '')

// Available statuses for quick actions
const availableStatuses = [
  { value: 'not_started', label: 'Not Started' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'review', label: 'Review' },
  { value: 'completed', label: 'Completed' },
  { value: 'on_hold', label: 'On Hold' },
  { value: 'cancelled', label: 'Cancelled' }
]

// Methods
const updateProgress = async () => {
  const success = await taskStore.updateTaskProgress(props.task.id, newProgress.value)
  if (success) {
    emit('updated')
  }
}

const updateStatus = async (status: string) => {
  const success = await taskStore.updateTaskStatus(props.task.id, status)
  if (success) {
    emit('updated')
  }
}

const logTime = async () => {
  if (timeToLog.value && timeToLog.value > 0) {
    const success = await taskStore.logTime(props.task.id, timeToLog.value)
    if (success) {
      timeToLog.value = null
      emit('updated')
    }
  }
}

const assignTask = async () => {
  const success = await taskStore.assignTask(props.task.id, selectedAssignee.value || null)
  if (success) {
    showAssignModal.value = false
    emit('updated')
  }
}

const getTimeVariance = (): string => {
  if (!props.task.estimated_hours) return '0'
  const variance = (props.task.actual_hours || 0) - props.task.estimated_hours
  return variance >= 0 ? `+${variance}` : variance.toString()
}

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString()
}

const formatDateTime = (date: string): string => {
  return new Date(date).toLocaleString()
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

const getTypeClasses = (type: string): string => {
  const classes = {
    'general': 'bg-blue-100 text-blue-800',
    'construction': 'bg-orange-100 text-orange-800',
    'inspection': 'bg-purple-100 text-purple-800',
    'planning': 'bg-indigo-100 text-indigo-800',
    'documentation': 'bg-gray-100 text-gray-800',
    'maintenance': 'bg-green-100 text-green-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getStatusButtonClasses = (status: string): string => {
  const baseClasses = 'border-gray-300 text-gray-700 hover:bg-gray-50'
  const activeClasses = {
    'not_started': 'bg-gray-100 border-gray-400 text-gray-800',
    'in_progress': 'bg-blue-100 border-blue-400 text-blue-800',
    'review': 'bg-yellow-100 border-yellow-400 text-yellow-800',
    'completed': 'bg-green-100 border-green-400 text-green-800',
    'cancelled': 'bg-red-100 border-red-400 text-red-800',
    'on_hold': 'bg-orange-100 border-orange-400 text-orange-800'
  }
  
  return status === props.task.status.value 
    ? activeClasses[status] || baseClasses
    : baseClasses
}
</script>