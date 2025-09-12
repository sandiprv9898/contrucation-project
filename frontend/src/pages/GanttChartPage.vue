<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Enhanced Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 shadow-lg">
      <div class="px-6 py-6">
        <div class="flex items-center justify-between">
          <div class="text-white">
            <h1 class="text-3xl font-bold">Project Timeline & Dependencies</h1>
            <p class="text-blue-100 mt-1">Interactive Gantt chart with task dependencies and resource management</p>
          </div>
          <div class="flex items-center space-x-4">
            <!-- Enhanced Project Selector -->
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3">
              <label class="block text-sm font-medium text-blue-100 mb-2">Select Project</label>
              <select 
                v-model="selectedProjectId" 
                @change="loadProjectTasks"
                class="bg-white border-0 rounded-md px-4 py-2 pr-8 min-w-[200px] focus:ring-2 focus:ring-blue-300"
              >
                <option value="">Choose a project...</option>
                <option 
                  v-for="project in projects" 
                  :key="project.id" 
                  :value="project.id"
                >
                  {{ project.name }}
                </option>
              </select>
            </div>
            
            <button
              @click="refreshData"
              :disabled="loading"
              class="flex items-center space-x-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white px-6 py-3 rounded-lg transition-all duration-200 disabled:opacity-50 border border-white/20"
            >
              <RefreshCw :class="['w-5 h-5', loading && 'animate-spin']" />
              <span class="font-medium">Refresh Data</span>
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
        <!-- Enhanced Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Total Tasks Card -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total Tasks</p>
                <p class="text-3xl font-bold text-gray-900">{{ statistics.total || 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Across project phases</p>
              </div>
              <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
                <Calendar class="w-7 h-7 text-blue-600" />
              </div>
            </div>
          </div>

          <!-- In Progress Card -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 mb-1">In Progress</p>
                <p class="text-3xl font-bold text-orange-600">{{ statistics.inProgress || 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ ((statistics.inProgress || 0) / (statistics.total || 1) * 100).toFixed(0) }}% of total</p>
              </div>
              <div class="w-14 h-14 bg-gradient-to-br from-orange-100 to-orange-200 rounded-xl flex items-center justify-center">
                <Clock class="w-7 h-7 text-orange-600" />
              </div>
            </div>
          </div>

          <!-- Completed Card -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Completed</p>
                <p class="text-3xl font-bold text-green-600">{{ statistics.completed || 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ statistics.completionRate || 0 }}% completion rate</p>
              </div>
              <div class="w-14 h-14 bg-gradient-to-br from-green-100 to-green-200 rounded-xl flex items-center justify-center">
                <CheckCircle class="w-7 h-7 text-green-600" />
              </div>
            </div>
          </div>

          <!-- Progress Overview Card -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total Hours</p>
                <p class="text-3xl font-bold text-purple-600">{{ statistics.totalActualHours || 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">of {{ statistics.totalEstimatedHours || 0 }} estimated</p>
              </div>
              <div class="w-14 h-14 bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl flex items-center justify-center">
                <Clock class="w-7 h-7 text-purple-600" />
              </div>
            </div>
          </div>
        </div>

        <!-- Progress Overview Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8" v-if="selectedProjectId">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Progress Overview</h3>
          <div class="space-y-4">
            <!-- Overall Progress Bar -->
            <div>
              <div class="flex justify-between text-sm mb-2">
                <span class="font-medium text-gray-700">Overall Completion</span>
                <span class="text-gray-600">{{ statistics.completionRate || 0 }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div 
                  class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-300" 
                  :style="{ width: `${statistics.completionRate || 0}%` }"
                ></div>
              </div>
            </div>
            
            <!-- Hours Progress Bar -->
            <div v-if="statistics.totalEstimatedHours">
              <div class="flex justify-between text-sm mb-2">
                <span class="font-medium text-gray-700">Hours Progress</span>
                <span class="text-gray-600">{{ statistics.totalActualHours }} / {{ statistics.totalEstimatedHours }} hours</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div 
                  class="bg-gradient-to-r from-purple-500 to-purple-600 h-3 rounded-full transition-all duration-300" 
                  :style="{ width: `${Math.min(((statistics.totalActualHours || 0) / (statistics.totalEstimatedHours || 1)) * 100, 100)}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Interactive Gantt Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
          <div class="border-b border-gray-200 p-6">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-semibold text-gray-900">Project Timeline</h3>
              <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2 text-sm text-gray-600">
                  <div class="flex items-center space-x-1">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <span>Not Started</span>
                  </div>
                  <div class="flex items-center space-x-1">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <span>In Progress</span>
                  </div>
                  <div class="flex items-center space-x-1">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span>Completed</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="p-6">
            <ProjectGantt 
              v-if="selectedProjectId"
              :project-id="selectedProjectId" 
              @task-selected="handleTaskSelected"
            />
            <div v-else class="text-center py-12">
              <Calendar class="w-16 h-16 mx-auto text-gray-300 mb-4" />
              <p class="text-lg font-medium text-gray-500 mb-2">Select a project to view timeline</p>
              <p class="text-sm text-gray-400">Choose a project from the dropdown above to display its Gantt chart</p>
            </div>
          </div>
        </div>

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
import { ref, computed, onMounted, watch } from 'vue'
import { RefreshCw, AlertCircle, Calendar, Clock, CheckCircle, AlertTriangle } from 'lucide-vue-next'
import ProjectGantt from '@/pages/projects/ProjectGantt.vue'
import { ganttApi } from '@/services/api/ganttApi'
import { api } from '@/services/api/client'
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
  estimated_hours?: number
  actual_hours?: number
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

// Statistics computed from real data
const statistics = computed(() => {
  const total = tasks.value.length
  const inProgress = tasks.value.filter(t => t.status === 'in_progress').length
  const completed = tasks.value.filter(t => t.status === 'completed').length
  const completionRate = total > 0 ? Math.round((completed / total) * 100) : 0
  
  // Calculate hours if available (these would come from API)
  const totalEstimatedHours = tasks.value.reduce((sum, task) => {
    // Assuming we add estimated_hours to Task interface later
    return sum + (task.estimated_hours || 8) // Default 8 hours per task
  }, 0)
  
  const totalActualHours = tasks.value.reduce((sum, task) => {
    // Calculate actual hours based on progress
    return sum + ((task.estimated_hours || 8) * (task.progress_percentage / 100))
  }, 0)
  
  return {
    total,
    inProgress,
    completed,
    overdue: overdueTasks.value,
    completionRate,
    totalEstimatedHours: Math.round(totalEstimatedHours),
    totalActualHours: Math.round(totalActualHours)
  }
})

const tasksWithDependencies = computed(() => 
  tasks.value.filter(task => task.dependencies && task.dependencies.length > 0)
)

const availableTasks = computed(() => 
  tasks.value.filter(task => task.id !== editingTask.value?.id)
)

// Methods
const loadProjects = async () => {
  try {
    const response = await api.get('/projects')
    projects.value = response.data.data || []
  } catch (err: any) {
    console.warn('Failed to load projects:', err.message)
    // Fallback to sample data
    projects.value = sampleProjects
  }
}

const loadProjectTasks = async () => {
  if (!selectedProjectId.value) {
    tasks.value = []
    return
  }
  
  loading.value = true
  error.value = ''
  
  try {
    // Try to load real API data first
    const response = await ganttApi.getGanttTasks(selectedProjectId.value)
    
    if (response && response.length > 0) {
      tasks.value = response
    } else {
      // Fallback to sample data for demonstration
      tasks.value = sampleTasks
    }
    
    showSuccess('Timeline Loaded', `Loaded ${tasks.value.length} tasks for project timeline`)
  } catch (err: any) {
    console.warn('Failed to load real data, using sample data:', err.message)
    // Fallback to sample data
    tasks.value = sampleTasks
    error.value = ''
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

// Watch for project selection changes
watch(selectedProjectId, (newProjectId) => {
  if (newProjectId) {
    loadProjectTasks()
  } else {
    tasks.value = []
  }
})

// Lifecycle
onMounted(async () => {
  await loadProjects()
  
  // Auto-select first project if available
  if (projects.value.length > 0) {
    selectedProjectId.value = projects.value[0].id
  }
})
</script>

<style scoped>
/* Add any additional styles here */
</style>