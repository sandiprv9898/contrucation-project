<template>
  <div class="h-full flex flex-col">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-semibold text-gray-900">Task Board</h2>
      <div class="flex items-center space-x-2">
        <button
          @click="refreshTasks"
          :disabled="isLoading"
          class="px-3 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 flex items-center space-x-1"
        >
          <RefreshCw :class="{ 'animate-spin': isLoading }" class="w-4 h-4" />
          <span>Refresh</span>
        </button>
      </div>
    </div>

    <!-- Kanban Board -->
    <div class="flex-1 overflow-x-auto">
      <div class="flex space-x-6 h-full min-w-max pb-6">
        <div
          v-for="status in statusColumns"
          :key="status.value"
          class="flex-shrink-0 w-80 bg-gray-50 rounded-lg flex flex-col"
          @dragover.prevent
          @drop="onDrop($event, status.value)"
        >
          <!-- Column Header -->
          <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <div 
                  :class="[
                    'w-3 h-3 rounded-full',
                    status.color === 'gray' ? 'bg-gray-400' :
                    status.color === 'blue' ? 'bg-blue-500' :
                    status.color === 'yellow' ? 'bg-yellow-500' :
                    status.color === 'green' ? 'bg-green-500' : 'bg-red-500'
                  ]"
                ></div>
                <h3 class="font-medium text-gray-900">{{ status.label }}</h3>
              </div>
              <span class="text-sm text-gray-500 bg-white px-2 py-1 rounded-full">
                {{ getTasksByStatus(status.value).length }}
              </span>
            </div>
          </div>

          <!-- Task Cards -->
          <div class="flex-1 p-4 space-y-3 overflow-y-auto">
            <div
              v-for="task in getTasksByStatus(status.value)"
              :key="task.id"
              :draggable="true"
              @dragstart="onDragStart($event, task)"
              @dragend="onDragEnd"
              class="bg-white rounded-lg border border-gray-200 p-4 cursor-move hover:shadow-md transition-all duration-200 transform hover:-translate-y-1"
              :class="{ 'opacity-50': draggedTask?.id === task.id }"
            >
              <!-- Task Header -->
              <div class="flex items-start justify-between mb-2">
                <h4 class="font-medium text-gray-900 text-sm line-clamp-2">{{ task.name }}</h4>
                <button
                  @click="$emit('task-details', task)"
                  class="text-gray-400 hover:text-blue-600 transition-colors ml-2"
                >
                  <ExternalLink class="w-4 h-4" />
                </button>
              </div>

              <!-- Task Meta -->
              <div class="space-y-2">
                <!-- Priority & Type -->
                <div class="flex items-center space-x-2">
                  <span 
                    :class="[
                      'inline-flex items-center px-2 py-1 rounded text-xs font-medium',
                      task.priority.color === 'red' ? 'bg-red-100 text-red-800' :
                      task.priority.color === 'yellow' ? 'bg-yellow-100 text-yellow-800' :
                      task.priority.color === 'green' ? 'bg-green-100 text-green-800' :
                      'bg-gray-100 text-gray-800'
                    ]"
                  >
                    {{ task.priority.label }}
                  </span>
                  <span class="text-xs text-gray-500">{{ task.task_type.label }}</span>
                </div>

                <!-- Progress Bar -->
                <div class="space-y-1">
                  <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">Progress</span>
                    <span class="text-xs font-medium text-gray-700">{{ task.progress_percentage }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-1.5">
                    <div 
                      class="bg-blue-600 h-1.5 rounded-full transition-all duration-300" 
                      :style="{ width: `${task.progress_percentage}%` }"
                    ></div>
                  </div>
                </div>

                <!-- Assignee -->
                <div v-if="task.assigned_to" class="flex items-center space-x-2">
                  <User class="w-3 h-3 text-gray-400" />
                  <span class="text-xs text-gray-600 truncate">{{ task.assigned_to.name }}</span>
                </div>

                <!-- Due Date -->
                <div v-if="task.due_date" class="flex items-center space-x-2">
                  <Calendar class="w-3 h-3 text-gray-400" />
                  <span 
                    class="text-xs truncate"
                    :class="{ 'text-red-600 font-medium': task.is_overdue, 'text-gray-600': !task.is_overdue }"
                  >
                    {{ formatDate(task.due_date) }}
                    <span v-if="task.is_overdue" class="text-red-600">(Overdue)</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-if="getTasksByStatus(status.value).length === 0" class="text-center py-8">
              <div class="text-gray-400 mb-2">
                <Inbox class="w-8 h-8 mx-auto" />
              </div>
              <p class="text-sm text-gray-500">No {{ status.label.toLowerCase() }} tasks</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="isUpdating" class="fixed inset-0 bg-black bg-opacity-25 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
        <span class="text-gray-900">Updating task status...</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useTaskStore } from '../stores/task.store'
import { RefreshCw, ExternalLink, User, Calendar, Inbox } from 'lucide-vue-next'
import type { Task } from '../types/task.types'

// Props & Emits
const emit = defineEmits<{
  'task-details': [task: Task]
}>()

// Store
const taskStore = useTaskStore()

// State
const draggedTask = ref<Task | null>(null)
const isUpdating = ref(false)

// Status columns configuration
const statusColumns = [
  { value: 'not_started', label: 'Not Started', color: 'gray' },
  { value: 'in_progress', label: 'In Progress', color: 'blue' },
  { value: 'review', label: 'Review', color: 'yellow' },
  { value: 'completed', label: 'Completed', color: 'green' },
  { value: 'on_hold', label: 'On Hold', color: 'red' },
  { value: 'cancelled', label: 'Cancelled', color: 'red' }
]

// Computed
const isLoading = computed(() => taskStore.isLoading)

// Methods
const getTasksByStatus = (status: string) => {
  return taskStore.tasks.filter(task => task.status.value === status)
}

const refreshTasks = async () => {
  await taskStore.refreshTasks()
}

const onDragStart = (event: DragEvent, task: Task) => {
  draggedTask.value = task
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move'
    event.dataTransfer.setData('text/plain', task.id)
  }
}

const onDragEnd = () => {
  draggedTask.value = null
}

const onDrop = async (event: DragEvent, newStatus: string) => {
  event.preventDefault()
  
  if (!draggedTask.value || draggedTask.value.status.value === newStatus) {
    return
  }

  const taskId = draggedTask.value.id
  const oldStatus = draggedTask.value.status.value

  try {
    isUpdating.value = true
    
    // Optimistically update the UI
    const taskIndex = taskStore.tasks.findIndex(t => t.id === taskId)
    if (taskIndex !== -1) {
      taskStore.tasks[taskIndex].status = {
        value: newStatus,
        label: statusColumns.find(s => s.value === newStatus)?.label || newStatus,
        color: statusColumns.find(s => s.value === newStatus)?.color || 'gray'
      }

      // Auto-update progress based on status
      if (newStatus === 'completed') {
        taskStore.tasks[taskIndex].progress_percentage = 100
      } else if (newStatus === 'in_progress' && taskStore.tasks[taskIndex].progress_percentage === 0) {
        taskStore.tasks[taskIndex].progress_percentage = 25
      } else if (newStatus === 'not_started') {
        taskStore.tasks[taskIndex].progress_percentage = 0
      }
    }

    // Update status via API
    const success = await taskStore.updateTaskStatus(taskId, newStatus)
    
    if (!success) {
      // Revert on failure
      if (taskIndex !== -1) {
        taskStore.tasks[taskIndex].status = {
          value: oldStatus,
          label: statusColumns.find(s => s.value === oldStatus)?.label || oldStatus,
          color: statusColumns.find(s => s.value === oldStatus)?.color || 'gray'
        }
      }
      alert('Failed to update task status')
    }
  } catch (error) {
    console.error('Error updating task status:', error)
    alert('Failed to update task status')
  } finally {
    isUpdating.value = false
    draggedTask.value = null
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

// Lifecycle
onMounted(async () => {
  if (taskStore.tasks.length === 0) {
    await taskStore.loadTasks()
  }
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