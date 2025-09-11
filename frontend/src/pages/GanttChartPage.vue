<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="px-6 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Project Timeline</h1>
            <p class="text-sm text-gray-500">Gantt chart with task dependencies and scheduling</p>
          </div>
          <div class="flex items-center space-x-3">
            <select 
              v-model="selectedProjectId" 
              @change="loadProjectTasks"
              class="border border-gray-300 rounded-md px-3 py-2"
            >
              <option value="">All Projects</option>
              <option 
                v-for="project in projects" 
                :key="project.id" 
                :value="project.id"
              >
                {{ project.name }}
              </option>
            </select>
            
            <button
              @click="refreshData"
              :disabled="loading"
              class="flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg disabled:opacity-50"
            >
              <RefreshCw :class="['w-4 h-4', loading && 'animate-spin']" />
              <span>Refresh</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="p-6">
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p class="text-lg text-gray-600">Loading project timeline...</p>
        </div>
      </div>

      <div v-else-if="error" class="text-center py-12">
        <div class="text-red-600 mb-4">
          <AlertCircle class="w-12 h-12 mx-auto mb-2" />
          <p class="text-lg font-medium">Failed to load timeline</p>
          <p class="text-sm">{{ error }}</p>
        </div>
        <button
          @click="refreshData"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
        >
          Try Again
        </button>
      </div>

      <div v-else-if="tasks.length === 0" class="text-center py-12">
        <div class="text-gray-500 mb-4">
          <Calendar class="w-12 h-12 mx-auto mb-2" />
          <p class="text-lg font-medium">No tasks found</p>
          <p class="text-sm">Create some tasks to see them in the timeline</p>
        </div>
      </div>

      <div v-else>
        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <Calendar class="w-5 h-5 text-blue-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Total Tasks</p>
                <p class="text-2xl font-semibold text-gray-900">{{ tasks.length }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                <Clock class="w-5 h-5 text-yellow-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">In Progress</p>
                <p class="text-2xl font-semibold text-gray-900">{{ inProgressTasks }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                <CheckCircle class="w-5 h-5 text-green-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Completed</p>
                <p class="text-2xl font-semibold text-gray-900">{{ completedTasks }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                <AlertTriangle class="w-5 h-5 text-red-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">Overdue</p>
                <p class="text-2xl font-semibold text-gray-900">{{ overdueTasks }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Gantt Chart -->
        <GanttChart 
          :tasks="tasks" 
          @task-selected="handleTaskSelected"
          class="mb-6"
        />

        <!-- Task Dependencies Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Task Dependencies</h2>
          
          <div class="space-y-4">
            <div
              v-for="task in tasksWithDependencies"
              :key="task.id"
              class="border rounded-lg p-4"
            >
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-3">
                  <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                  <h3 class="font-medium text-gray-900">{{ task.name }}</h3>
                  <span class="text-sm text-gray-500">({{ task.dependencies?.length || 0 }} dependencies)</span>
                </div>
                <button
                  @click="editDependencies(task)"
                  class="text-sm text-blue-600 hover:text-blue-800"
                >
                  Edit Dependencies
                </button>
              </div>
              
              <div v-if="task.dependencies?.length > 0" class="space-y-2">
                <p class="text-sm text-gray-600">Depends on:</p>
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="depId in task.dependencies"
                    :key="depId"
                    class="px-2 py-1 bg-gray-100 rounded text-xs text-gray-700"
                  >
                    {{ getTaskName(depId) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Dependency Edit Modal -->
    <div
      v-if="editingTask"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="editingTask = null"
    >
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
          Edit Dependencies: {{ editingTask.name }}
        </h3>
        
        <div class="space-y-3 mb-6">
          <div
            v-for="task in availableTasks"
            :key="task.id"
            class="flex items-center"
          >
            <input
              v-model="selectedDependencies"
              :value="task.id"
              type="checkbox"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <label class="ml-2 text-sm text-gray-900">{{ task.name }}</label>
          </div>
        </div>
        
        <div class="flex space-x-3">
          <button
            @click="editingTask = null"
            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg"
          >
            Cancel
          </button>
          <button
            @click="saveDependencies"
            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg"
          >
            Save
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { RefreshCw, AlertCircle, Calendar, Clock, CheckCircle, AlertTriangle } from 'lucide-vue-next'
import GanttChart from '@/components/charts/GanttChart.vue'
import { useNotifications } from '@/composables/useNotifications'

interface Task {
  id: string
  name: string
  description?: string
  start_date: string
  due_date: string
  status: 'not_started' | 'in_progress' | 'review' | 'completed' | 'cancelled' | 'on_hold'
  progress_percentage: number
  dependencies?: string[]
  parent_id?: string | null
  priority?: 'low' | 'medium' | 'high' | 'critical'
  project?: { id: string; name: string }
}

interface Project {
  id: string
  name: string
}

defineOptions({ name: 'GanttChartPage' })

// Composables
const { showSuccess, showError } = useNotifications()

// State
const tasks = ref<Task[]>([])
const projects = ref<Project[]>([])
const loading = ref(false)
const error = ref('')
const selectedProjectId = ref('')
const editingTask = ref<Task | null>(null)
const selectedDependencies = ref<string[]>([])

// Sample data for demonstration
const sampleTasks: Task[] = [
  {
    id: '1',
    name: 'Site Preparation',
    start_date: '2024-01-01',
    due_date: '2024-01-10',
    status: 'completed',
    progress_percentage: 100,
    dependencies: [],
    priority: 'high'
  },
  {
    id: '2',
    name: 'Foundation Work',
    start_date: '2024-01-11',
    due_date: '2024-01-25',
    status: 'completed',
    progress_percentage: 100,
    dependencies: ['1'],
    priority: 'critical'
  },
  {
    id: '3',
    name: 'Framing',
    start_date: '2024-01-26',
    due_date: '2024-02-15',
    status: 'in_progress',
    progress_percentage: 60,
    dependencies: ['2'],
    priority: 'high'
  },
  {
    id: '4',
    name: 'Roofing',
    start_date: '2024-02-16',
    due_date: '2024-02-28',
    status: 'not_started',
    progress_percentage: 0,
    dependencies: ['3'],
    priority: 'high'
  },
  {
    id: '5',
    name: 'Electrical Rough-In',
    start_date: '2024-02-10',
    due_date: '2024-02-20',
    status: 'in_progress',
    progress_percentage: 30,
    dependencies: ['3'],
    priority: 'medium'
  },
  {
    id: '6',
    name: 'Plumbing Rough-In',
    start_date: '2024-02-12',
    due_date: '2024-02-22',
    status: 'not_started',
    progress_percentage: 0,
    dependencies: ['3'],
    priority: 'medium'
  },
  {
    id: '7',
    name: 'Insulation',
    start_date: '2024-03-01',
    due_date: '2024-03-10',
    status: 'not_started',
    progress_percentage: 0,
    dependencies: ['5', '6'],
    priority: 'medium'
  },
  {
    id: '8',
    name: 'Drywall',
    start_date: '2024-03-11',
    due_date: '2024-03-25',
    status: 'not_started',
    progress_percentage: 0,
    dependencies: ['7'],
    priority: 'medium'
  },
  {
    id: '9',
    name: 'Interior Finishes',
    start_date: '2024-03-26',
    due_date: '2024-04-15',
    status: 'not_started',
    progress_percentage: 0,
    dependencies: ['8'],
    priority: 'low'
  },
  {
    id: '10',
    name: 'Final Inspection',
    start_date: '2024-04-16',
    due_date: '2024-04-20',
    status: 'not_started',
    progress_percentage: 0,
    dependencies: ['9'],
    priority: 'critical'
  }
]

const sampleProjects: Project[] = [
  { id: '1', name: 'Shopping Center Renovation' },
  { id: '2', name: 'Office Building Construction' },
  { id: '3', name: 'Residential Complex' }
]

// Computed
const inProgressTasks = computed(() => 
  tasks.value.filter(task => task.status === 'in_progress').length
)

const completedTasks = computed(() => 
  tasks.value.filter(task => task.status === 'completed').length
)

const overdueTasks = computed(() => 
  tasks.value.filter(task => {
    const dueDate = new Date(task.due_date)
    const today = new Date()
    return dueDate < today && task.status !== 'completed'
  }).length
)

const tasksWithDependencies = computed(() => 
  tasks.value.filter(task => task.dependencies && task.dependencies.length > 0)
)

const availableTasks = computed(() => 
  tasks.value.filter(task => task.id !== editingTask.value?.id)
)

// Methods
const loadProjectTasks = async () => {
  loading.value = true
  error.value = ''
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    // For demonstration, use sample data
    tasks.value = sampleTasks
    projects.value = sampleProjects
    
    showSuccess('Timeline Loaded', 'Project timeline loaded successfully')
  } catch (err: any) {
    error.value = err.message || 'Failed to load tasks'
    showError('Load Failed', error.value)
  } finally {
    loading.value = false
  }
}

const refreshData = () => {
  loadProjectTasks()
}

const handleTaskSelected = (task: Task) => {
  // Handle task selection - could navigate to task detail
  showSuccess('Task Selected', `Selected: ${task.name}`)
}

const getTaskName = (taskId: string) => {
  const task = tasks.value.find(t => t.id === taskId)
  return task?.name || 'Unknown Task'
}

const editDependencies = (task: Task) => {
  editingTask.value = task
  selectedDependencies.value = [...(task.dependencies || [])]
}

const saveDependencies = async () => {
  if (!editingTask.value) return
  
  try {
    // Update task dependencies
    const taskIndex = tasks.value.findIndex(t => t.id === editingTask.value!.id)
    if (taskIndex !== -1) {
      tasks.value[taskIndex].dependencies = [...selectedDependencies.value]
    }
    
    // In a real app, you would make an API call here
    showSuccess('Dependencies Updated', 'Task dependencies have been updated successfully')
    editingTask.value = null
  } catch (err: any) {
    showError('Update Failed', 'Failed to update task dependencies')
  }
}

// Lifecycle
onMounted(() => {
  loadProjectTasks()
})
</script>

<style scoped>
/* Add any additional styles here */
</style>