<template>
  <div class="p-6 max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Task Management</h1>
        <p class="text-gray-600 mt-1">Track and manage tasks across all your projects</p>
      </div>
      
      <div class="flex space-x-3">
        <button
          @click="showCreateModal = true"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors"
        >
          <Plus class="w-4 h-4" />
          <span>New Task</span>
        </button>
        
        <button
          @click="refreshTasks"
          :disabled="isLoading"
          class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors disabled:opacity-50"
        >
          <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': isLoading }" />
          <span>Refresh</span>
        </button>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6" v-if="statistics">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-2 bg-blue-100 rounded-lg">
            <CheckSquare class="w-6 h-6 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Tasks</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics?.total_tasks || 0 }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-2 bg-green-100 rounded-lg">
            <CheckCircle class="w-6 h-6 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Completed</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics?.by_status?.completed || 0 }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-2 bg-red-100 rounded-lg">
            <AlertCircle class="w-6 h-6 text-red-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Overdue</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics?.overdue_tasks || 0 }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-2 bg-yellow-100 rounded-lg">
            <Clock class="w-6 h-6 text-yellow-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Due Soon</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics?.due_soon_tasks || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <div class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
            <input
              v-model="filters.search"
              @input="applyFilters"
              type="text"
              placeholder="Search tasks..."
              class="pl-10 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.status"
            @change="applyFilters"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Statuses</option>
            <option value="not_started">Not Started</option>
            <option value="in_progress">In Progress</option>
            <option value="review">Review</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
            <option value="on_hold">On Hold</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
          <select
            v-model="filters.priority"
            @change="applyFilters"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Priorities</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="critical">Critical</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Quick Filters</label>
          <select
            v-model="quickFilter"
            @change="applyQuickFilter"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Tasks</option>
            <option value="overdue">Overdue</option>
            <option value="due_soon">Due Soon</option>
            <option value="unassigned">Unassigned</option>
            <option value="my_tasks">My Tasks</option>
          </select>
        </div>
      </div>
      
      <div class="flex justify-between items-center">
        <div class="text-sm text-gray-600">
          Showing {{ tasks.length }} of {{ pagination.total }} tasks
        </div>
        
        <div class="flex space-x-2">
          <button
            @click="viewMode = 'list'"
            :class="[
              'px-3 py-1 rounded text-sm',
              viewMode === 'list' 
                ? 'bg-blue-100 text-blue-700' 
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            ]"
          >
            <List class="w-4 h-4" />
          </button>
          <button
            @click="viewMode = 'kanban'"
            :class="[
              'px-3 py-1 rounded text-sm',
              viewMode === 'kanban' 
                ? 'bg-blue-100 text-blue-700' 
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            ]"
          >
            <Kanban class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Task List View -->
    <div v-if="viewMode === 'list'" class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="isLoading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Loading tasks...</p>
      </div>
      
      <div v-else-if="error" class="p-8 text-center">
        <AlertCircle class="w-12 h-12 text-red-500 mx-auto mb-4" />
        <p class="text-red-600 mb-4">{{ error }}</p>
        <button
          @click="refreshTasks"
          class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg"
        >
          Try Again
        </button>
      </div>
      
      <div v-else-if="tasks.length === 0" class="p-8 text-center">
        <CheckSquare class="w-12 h-12 text-gray-400 mx-auto mb-4" />
        <p class="text-gray-600 mb-4">No tasks found</p>
        <button
          @click="showCreateModal = true"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
        >
          Create First Task
        </button>
      </div>
      
      <div v-else class="divide-y divide-gray-200">
        <div
          v-for="task in tasks"
          :key="task.id"
          class="p-6 hover:bg-gray-50 cursor-pointer transition-colors"
          @click="selectTask(task)"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-3 mb-2">
                <h3 class="text-lg font-medium text-gray-900 truncate">{{ task.name }}</h3>
                
                <span 
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    getStatusClasses(task.status.value)
                  ]"
                >
                  {{ task.status.label }}
                </span>
                
                <span 
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    getPriorityClasses(task.priority.value)
                  ]"
                >
                  {{ task.priority.label }}
                </span>
              </div>
              
              <p v-if="task.description" class="text-gray-600 text-sm mb-2 line-clamp-2">
                {{ task.description }}
              </p>
              
              <div class="flex items-center space-x-4 text-sm text-gray-500">
                <div v-if="task.project" class="flex items-center space-x-1">
                  <Folder class="w-4 h-4" />
                  <span>{{ task.project.name }}</span>
                </div>
                
                <div v-if="task.assigned_to" class="flex items-center space-x-1">
                  <User class="w-4 h-4" />
                  <span>{{ task.assigned_to.name }}</span>
                </div>
                
                <div v-if="task.due_date" class="flex items-center space-x-1" :class="{ 'text-red-500': task.is_overdue }">
                  <Calendar class="w-4 h-4" />
                  <span>{{ formatDate(task.due_date) }}</span>
                </div>
                
                <div class="flex items-center space-x-1">
                  <BarChart class="w-4 h-4" />
                  <span>{{ task.progress_percentage }}%</span>
                </div>
              </div>
            </div>
            
            <div class="flex items-center space-x-2 ml-4">
              <button
                @click.stop="updateTaskStatus(task.id, getNextStatus(task.status.value))"
                class="text-gray-400 hover:text-blue-600 transition-colors"
                :title="getNextStatusLabel(task.status.value)"
              >
                <ArrowRight class="w-4 h-4" />
              </button>
              
              <button
                @click.stop="editTask(task)"
                class="text-gray-400 hover:text-green-600 transition-colors"
                title="Edit task"
              >
                <Edit class="w-4 h-4" />
              </button>
              
              <button
                @click.stop="deleteTaskConfirm(task)"
                class="text-gray-400 hover:text-red-600 transition-colors"
                title="Delete task"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
          
          <!-- Progress Bar -->
          <div class="mt-4">
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                :style="{ width: `${task.progress_percentage}%` }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Kanban Board View -->
    <div v-else class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
      <div
        v-for="status in ['not_started', 'in_progress', 'review', 'completed', 'on_hold']"
        :key="status"
        class="bg-gray-100 rounded-lg p-4"
      >
        <h3 class="font-medium text-gray-900 mb-4 capitalize">
          {{ status.replace('_', ' ') }}
          <span class="text-sm text-gray-500">({{ getTasksByStatus(status).length }})</span>
        </h3>
        
        <div class="space-y-3">
          <div
            v-for="task in getTasksByStatus(status)"
            :key="task.id"
            class="bg-white rounded-lg p-4 shadow-sm cursor-pointer hover:shadow-md transition-shadow"
            @click="selectTask(task)"
          >
            <h4 class="font-medium text-gray-900 mb-2 line-clamp-2">{{ task.name }}</h4>
            
            <div class="flex items-center justify-between text-sm text-gray-500 mb-2">
              <span 
                :class="[
                  'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                  getPriorityClasses(task.priority.value)
                ]"
              >
                {{ task.priority.label }}
              </span>
              
              <span v-if="task.due_date" :class="{ 'text-red-500': task.is_overdue }">
                {{ formatDate(task.due_date) }}
              </span>
            </div>
            
            <div v-if="task.assigned_to" class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
              <User class="w-3 h-3" />
              <span>{{ task.assigned_to.name }}</span>
            </div>
            
            <div class="w-full bg-gray-200 rounded-full h-1.5">
              <div 
                class="bg-blue-600 h-1.5 rounded-full" 
                :style="{ width: `${task.progress_percentage}%` }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Task Modal -->
    <div
      v-if="showCreateModal || editingTask"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold text-gray-900 mb-4">
          {{ editingTask ? 'Edit Task' : 'Create New Task' }}
        </h2>
        
        <TaskForm
          :task="editingTask"
          @saved="handleTaskSaved"
          @cancelled="closeModal"
        />
      </div>
    </div>

    <!-- Task Detail Modal -->
    <div
      v-if="selectedTask"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="selectedTask = null"
    >
      <div class="bg-white rounded-lg p-6 w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
        <TaskDetail
          :task="selectedTask"
          @updated="handleTaskUpdated"
          @closed="selectedTask = null"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { 
  CheckSquare, CheckCircle, AlertCircle, Clock, Plus, RefreshCw, Search,
  List, Calendar, User, Folder, BarChart, ArrowRight, Edit, Trash2, Kanban
} from 'lucide-vue-next'
import { useTaskStore } from '@/modules/tasks/stores/task.store'
import type { Task, TaskFilters } from '@/modules/tasks/types/task.types'
import TaskForm from '@/modules/tasks/components/TaskForm.vue'
import TaskDetail from '@/modules/tasks/components/TaskDetail.vue'

defineOptions({ name: 'TasksPage' })

// Store
const taskStore = useTaskStore()

// Direct access to store properties
const tasks = computed(() => taskStore.tasks)
const statistics = computed(() => taskStore.statistics)
const isLoading = computed(() => taskStore.isLoading)
const error = computed(() => taskStore.error)
const pagination = computed(() => taskStore.pagination)
const tasksByStatus = computed(() => taskStore.tasksByStatus)

// Local state
const viewMode = ref<'list' | 'kanban'>('list')
const showCreateModal = ref(false)
const editingTask = ref<Task | null>(null)
const selectedTask = ref<Task | null>(null)
const quickFilter = ref('')

const filters = ref<TaskFilters>({
  search: '',
  status: '',
  priority: '',
  per_page: 50
})

// Computed
const getTasksByStatus = (status: string): Task[] => {
  return tasks.value.filter(task => task.status.value === status)
}

// Methods
const loadTasks = async () => {
  await Promise.all([
    taskStore.loadTasks(filters.value),
    taskStore.loadStatistics()
  ])
}

const refreshTasks = async () => {
  await loadTasks()
}

const applyFilters = () => {
  taskStore.loadTasks(filters.value)
}

const applyQuickFilter = () => {
  const newFilters = { ...filters.value }
  
  switch (quickFilter.value) {
    case 'overdue':
      newFilters.overdue = true
      break
    case 'due_soon':
      newFilters.due_soon = true
      break
    case 'unassigned':
      newFilters.assigned_to_id = 'null'
      break
    case 'my_tasks':
      // This would use current user ID
      newFilters.assigned_to_id = 'current_user'
      break
    default:
      delete newFilters.overdue
      delete newFilters.due_soon
      delete newFilters.assigned_to_id
  }
  
  filters.value = newFilters
  applyFilters()
}

const selectTask = (task: Task) => {
  selectedTask.value = task
}

const editTask = (task: Task) => {
  editingTask.value = task
}

const closeModal = () => {
  showCreateModal.value = false
  editingTask.value = null
}

const handleTaskSaved = () => {
  closeModal()
  refreshTasks()
}

const handleTaskUpdated = () => {
  selectedTask.value = null
  refreshTasks()
}

const deleteTaskConfirm = async (task: Task) => {
  if (confirm(`Are you sure you want to delete "${task.name}"?`)) {
    await taskStore.deleteTask(task.id)
  }
}

const updateTaskStatus = async (taskId: string, status: string) => {
  await taskStore.updateTaskStatus(taskId, status)
}

const getNextStatus = (currentStatus: string): string => {
  const statusFlow: Record<string, string> = {
    'not_started': 'in_progress',
    'in_progress': 'review',
    'review': 'completed',
    'completed': 'completed',
    'cancelled': 'not_started',
    'on_hold': 'in_progress'
  }
  return statusFlow[currentStatus] || 'in_progress'
}

const getNextStatusLabel = (currentStatus: string): string => {
  const labels: Record<string, string> = {
    'not_started': 'Start Task',
    'in_progress': 'Send for Review',
    'review': 'Complete Task',
    'completed': 'Already Complete',
    'cancelled': 'Restart Task',
    'on_hold': 'Resume Task'
  }
  return labels[currentStatus] || 'Update Status'
}

const getStatusClasses = (status: string): string => {
  const classes: Record<string, string> = {
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
  const classes: Record<string, string> = {
    'low': 'bg-green-100 text-green-800',
    'medium': 'bg-yellow-100 text-yellow-800',
    'high': 'bg-orange-100 text-orange-800',
    'critical': 'bg-red-100 text-red-800'
  }
  return classes[priority] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString()
}

// Lifecycle
onMounted(() => {
  loadTasks()
})
</script>