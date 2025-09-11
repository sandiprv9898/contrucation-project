<template>
  <div class="space-y-6">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="flex items-center space-x-2">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="text-gray-600">Loading project details...</span>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="rounded-md bg-red-50 p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <AlertCircle class="h-5 w-5 text-red-400" />
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Error loading project</h3>
          <div class="mt-2 text-sm text-red-700">{{ error }}</div>
        </div>
      </div>
    </div>

    <!-- Project Details -->
    <div v-else-if="currentProject" class="space-y-6">
      <!-- Header -->
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-4">
              <li>
                <div>
                  <router-link to="/app/projects" class="text-gray-400 hover:text-gray-500">
                    <Home class="flex-shrink-0 h-5 w-5" />
                    <span class="sr-only">Projects</span>
                  </router-link>
                </div>
              </li>
              <li>
                <div class="flex items-center">
                  <ChevronRight class="flex-shrink-0 h-5 w-5 text-gray-400" />
                  <router-link to="/app/projects" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">
                    Projects
                  </router-link>
                </div>
              </li>
              <li>
                <div class="flex items-center">
                  <ChevronRight class="flex-shrink-0 h-5 w-5 text-gray-400" />
                  <span class="ml-4 text-sm font-medium text-gray-500">{{ currentProject.name }}</span>
                </div>
              </li>
            </ol>
          </nav>

          <div class="flex items-center space-x-3 mb-2">
            <h1 class="text-3xl font-bold text-gray-900">{{ currentProject.name }}</h1>
            <ProjectStatusBadge :status="currentProject.status.value" :color="currentProject.status.color" />
            <ProjectPriorityBadge :priority="currentProject.priority.value" :color="currentProject.priority.color" />
          </div>
          
          <p v-if="currentProject.description" class="text-lg text-gray-600 mt-2">
            {{ currentProject.description }}
          </p>
        </div>

        <div class="flex items-center space-x-2 ml-6">
          <VButton variant="outline" @click="editProject">
            <Edit class="w-4 h-4 mr-2" />
            Edit Project
          </VButton>
          <VButton @click="showTaskModal = true">
            <Plus class="w-4 h-4 mr-2" />
            Add Task
          </VButton>
        </div>
      </div>

      <!-- Key Metrics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Progress Card -->
        <VCard>
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <TrendingUp class="h-8 w-8 text-blue-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Progress</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ currentProject.progress_percentage }}%</dd>
                </dl>
              </div>
            </div>
            <div class="mt-4">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Completion</span>
                <span class="font-medium">{{ currentProject.progress_percentage }}%</span>
              </div>
              <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                <div 
                  class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                  :style="{ width: `${currentProject.progress_percentage}%` }"
                ></div>
              </div>
            </div>
          </div>
        </VCard>

        <!-- Budget Card -->
        <VCard>
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <DollarSign class="h-8 w-8 text-green-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Budget</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ formatCurrency(currentProject.actual_budget) }}</dd>
                </dl>
              </div>
            </div>
            <div class="mt-4">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">of {{ formatCurrency(currentProject.planned_budget || 0) }}</span>
                <span 
                  :class="currentProject.is_over_budget ? 'text-red-600 font-medium' : 'text-green-600'"
                >
                  {{ currentProject.budget_variance >= 0 ? '+' : '' }}{{ formatCurrency(currentProject.budget_variance) }}
                </span>
              </div>
              <div v-if="currentProject.is_over_budget" class="mt-1 text-xs text-red-600">
                Over budget
              </div>
            </div>
          </div>
        </VCard>

        <!-- Timeline Card -->
        <VCard>
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <Calendar class="h-8 w-8 text-purple-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Timeline</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ currentProject.duration_days }} days</dd>
                </dl>
              </div>
            </div>
            <div class="mt-4">
              <div class="text-sm text-gray-500">
                <div v-if="currentProject.start_date">Start: {{ formatDate(currentProject.start_date) }}</div>
                <div v-if="currentProject.end_date" :class="{ 'text-red-600 font-medium': currentProject.is_overdue }">
                  End: {{ formatDate(currentProject.end_date) }}
                </div>
              </div>
              <div v-if="currentProject.is_overdue" class="mt-1 text-xs text-red-600 font-medium">
                OVERDUE
              </div>
            </div>
          </div>
        </VCard>

        <!-- Tasks Card -->
        <VCard>
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CheckSquare class="h-8 w-8 text-indigo-600" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Tasks</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ currentProject.tasks_count || 0 }}</dd>
                </dl>
              </div>
            </div>
            <div class="mt-4">
              <div class="text-sm text-gray-500">
                <div>{{ projectStats.completedTasks }} completed</div>
                <div>{{ projectStats.activeTasks }} in progress</div>
              </div>
            </div>
          </div>
        </VCard>
      </div>

      <!-- Main Content Tabs -->
      <VCard>
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8 px-6">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'py-4 px-1 border-b-2 font-medium text-sm',
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              <component :is="tab.icon" class="w-5 h-5 mr-2 inline-block" />
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <div class="p-6">
          <!-- Overview Tab -->
          <div v-if="activeTab === 'overview'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Project Information -->
              <div class="space-y-6">
                <div>
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Project Information</h3>
                  <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                    <div>
                      <dt class="text-sm font-medium text-gray-500">Client</dt>
                      <dd class="mt-1 text-sm text-gray-900">{{ currentProject.client.name }}</dd>
                    </div>
                    <div>
                      <dt class="text-sm font-medium text-gray-500">Project Manager</dt>
                      <dd class="mt-1 text-sm text-gray-900">{{ currentProject.manager.name }}</dd>
                    </div>
                    <div>
                      <dt class="text-sm font-medium text-gray-500">Project Type</dt>
                      <dd class="mt-1 text-sm text-gray-900">{{ currentProject.project_type.label }}</dd>
                    </div>
                    <div>
                      <dt class="text-sm font-medium text-gray-500">Status</dt>
                      <dd class="mt-1">
                        <ProjectStatusBadge :status="currentProject.status.value" :color="currentProject.status.color" />
                      </dd>
                    </div>
                    <div>
                      <dt class="text-sm font-medium text-gray-500">Priority</dt>
                      <dd class="mt-1">
                        <ProjectPriorityBadge :priority="currentProject.priority.value" :color="currentProject.priority.color" />
                      </dd>
                    </div>
                    <div>
                      <dt class="text-sm font-medium text-gray-500">Created</dt>
                      <dd class="mt-1 text-sm text-gray-900">{{ formatDate(currentProject.created_at) }}</dd>
                    </div>
                  </dl>
                </div>

                <!-- Location -->
                <div v-if="currentProject.address">
                  <h4 class="text-sm font-medium text-gray-900 mb-2">Location</h4>
                  <div class="flex items-start space-x-2">
                    <MapPin class="w-4 h-4 text-gray-400 mt-0.5" />
                    <span class="text-sm text-gray-600">{{ currentProject.address }}</span>
                  </div>
                </div>
              </div>

              <!-- Recent Activity -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
                <div class="flow-root">
                  <ul class="-mb-8">
                    <li v-for="(activity, index) in recentActivities" :key="index">
                      <div class="relative pb-8">
                        <span v-if="index !== recentActivities.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                        <div class="relative flex space-x-3">
                          <div>
                            <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                              <component :is="activity.icon" class="w-4 h-4 text-white" />
                            </span>
                          </div>
                          <div class="flex-1 min-w-0">
                            <div>
                              <p class="text-sm text-gray-600">
                                {{ activity.description }}
                              </p>
                              <p class="mt-0.5 text-xs text-gray-400">
                                {{ formatRelativeTime(activity.timestamp) }}
                              </p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Tasks Tab -->
          <div v-else-if="activeTab === 'tasks'" class="space-y-4">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Project Tasks</h3>
              <VButton @click="showTaskModal = true">
                <Plus class="w-4 h-4 mr-2" />
                Add Task
              </VButton>
            </div>
            
            <!-- Task filters -->
            <div class="flex items-center space-x-4">
              <VValidatedField
                v-model="taskFilters.status"
                type="select"
                placeholder="All Statuses"
                :options="taskStatusOptions"
                class="w-48"
                @change="filterTasks"
              />
              <VValidatedField
                v-model="taskFilters.priority"
                type="select"
                placeholder="All Priorities"
                :options="taskPriorityOptions"
                class="w-48"
                @change="filterTasks"
              />
            </div>

            <!-- Tasks list -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
              <ul class="divide-y divide-gray-200">
                <li v-for="task in filteredTasks" :key="task.id">
                  <div class="px-4 py-4 flex items-center justify-between">
                    <div class="flex-1">
                      <div class="flex items-center space-x-3">
                        <h4 class="text-sm font-medium text-gray-900">{{ task.name }}</h4>
                        <span 
                          :class="[
                            'inline-flex items-center px-2 py-1 rounded text-xs font-medium',
                            task.status === 'completed' ? 'bg-green-100 text-green-800' :
                            task.status === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                            task.status === 'on_hold' ? 'bg-yellow-100 text-yellow-800' :
                            'bg-gray-100 text-gray-800'
                          ]"
                        >
                          {{ task.status.replace('_', ' ').toUpperCase() }}
                        </span>
                      </div>
                      <p class="text-sm text-gray-500 mt-1">{{ task.description }}</p>
                      <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                        <span v-if="task.assigned_to">Assigned to: {{ task.assigned_to.name }}</span>
                        <span v-if="task.due_date">Due: {{ formatDate(task.due_date) }}</span>
                        <span>Progress: {{ task.progress_percentage }}%</span>
                      </div>
                    </div>
                    <div class="flex items-center space-x-2">
                      <VButton size="sm" variant="outline" @click="viewTask(task.id)">
                        <Eye class="w-4 h-4" />
                      </VButton>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>

          <!-- Timeline Tab -->
          <div v-else-if="activeTab === 'timeline'" class="space-y-4">
            <h3 class="text-lg font-medium text-gray-900">Project Timeline</h3>
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-gray-600 text-center py-8">Timeline view will be implemented here</p>
            </div>
          </div>

          <!-- Files Tab -->
          <div v-else-if="activeTab === 'files'" class="space-y-4">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Project Files</h3>
              <VButton variant="outline">
                <Upload class="w-4 h-4 mr-2" />
                Upload File
              </VButton>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-gray-600 text-center py-8">File management will be implemented here</p>
            </div>
          </div>
        </div>
      </VCard>
    </div>
  </div>

  <!-- Add Task Modal -->
  <VModal v-model="showTaskModal" title="Add New Task" size="lg">
    <div class="space-y-4">
      <VValidatedField
        v-model="newTask.name"
        label="Task Name"
        placeholder="Enter task name"
        required
      />
      <VValidatedField
        v-model="newTask.description"
        label="Description"
        type="textarea"
        placeholder="Enter task description"
        rows="3"
      />
      <div class="grid grid-cols-2 gap-4">
        <VValidatedField
          v-model="newTask.priority"
          label="Priority"
          type="select"
          :options="taskPriorityOptions"
          required
        />
        <VValidatedField
          v-model="newTask.due_date"
          label="Due Date"
          type="date"
        />
      </div>
    </div>
    
    <template #actions>
      <VButton variant="outline" @click="showTaskModal = false">Cancel</VButton>
      <VButton @click="createTask" :loading="loading">Create Task</VButton>
    </template>
  </VModal>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { VCard, VButton, VValidatedField, VModal } from '@/components/ui'
import {
  Home, ChevronRight, Edit, Plus, TrendingUp, DollarSign, Calendar,
  CheckSquare, MapPin, Eye, Upload, AlertCircle, User, Clock, FileText,
  Activity
} from 'lucide-vue-next'
import { useProjects } from '../composables/useProjects'
import ProjectStatusBadge from '../components/ProjectStatusBadge.vue'
import ProjectPriorityBadge from '../components/ProjectPriorityBadge.vue'

const route = useRoute()
const router = useRouter()

// Composables
const {
  currentProject,
  loading,
  error,
  fetchProject
} = useProjects()

// Local state
const activeTab = ref('overview')
const showTaskModal = ref(false)
const taskFilters = reactive({
  status: '',
  priority: ''
})

const newTask = reactive({
  name: '',
  description: '',
  priority: 'medium',
  due_date: ''
})

// Tab configuration
const tabs = [
  { id: 'overview', name: 'Overview', icon: Activity },
  { id: 'tasks', name: 'Tasks', icon: CheckSquare },
  { id: 'timeline', name: 'Timeline', icon: Clock },
  { id: 'files', name: 'Files', icon: FileText }
]

// Mock data for demonstration
const recentActivities = ref([
  {
    description: 'Task "Foundation work" was completed',
    timestamp: new Date(Date.now() - 2 * 60 * 60 * 1000), // 2 hours ago
    icon: CheckSquare
  },
  {
    description: 'New task "Electrical installation" was created',
    timestamp: new Date(Date.now() - 5 * 60 * 60 * 1000), // 5 hours ago
    icon: Plus
  },
  {
    description: 'Project status updated to "In Progress"',
    timestamp: new Date(Date.now() - 24 * 60 * 60 * 1000), // 1 day ago
    icon: Activity
  }
])

const projectTasks = ref([
  {
    id: '1',
    name: 'Site Survey and Preparation',
    description: 'Complete site survey and prepare foundation area',
    status: 'completed',
    priority: 'high',
    assigned_to: { name: 'John Smith' },
    due_date: '2025-09-15',
    progress_percentage: 100
  },
  {
    id: '2',
    name: 'Foundation Excavation',
    description: 'Excavate and prepare foundation',
    status: 'in_progress',
    priority: 'high',
    assigned_to: { name: 'Mike Johnson' },
    due_date: '2025-09-20',
    progress_percentage: 65
  },
  {
    id: '3',
    name: 'Electrical Planning',
    description: 'Plan electrical system layout',
    status: 'not_started',
    priority: 'medium',
    assigned_to: null,
    due_date: '2025-09-25',
    progress_percentage: 0
  }
])

// Options
const taskStatusOptions = [
  { value: 'not_started', label: 'Not Started' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'completed', label: 'Completed' },
  { value: 'on_hold', label: 'On Hold' }
]

const taskPriorityOptions = [
  { value: 'low', label: 'Low' },
  { value: 'medium', label: 'Medium' },
  { value: 'high', label: 'High' },
  { value: 'urgent', label: 'Urgent' }
]

// Computed
const projectStats = computed(() => {
  const completedTasks = projectTasks.value.filter(t => t.status === 'completed').length
  const activeTasks = projectTasks.value.filter(t => t.status === 'in_progress').length
  return { completedTasks, activeTasks }
})

const filteredTasks = computed(() => {
  let tasks = projectTasks.value
  
  if (taskFilters.status) {
    tasks = tasks.filter(task => task.status === taskFilters.status)
  }
  
  if (taskFilters.priority) {
    tasks = tasks.filter(task => task.priority === taskFilters.priority)
  }
  
  return tasks
})

// Methods
const editProject = () => {
  router.push(`/app/projects/${route.params.id}/edit`)
}

const viewTask = (taskId: string) => {
  router.push(`/app/projects/${route.params.id}/tasks/${taskId}`)
}

const createTask = async () => {
  // TODO: Implement task creation
  console.log('Creating task:', newTask)
  showTaskModal.value = false
  // Reset form
  Object.assign(newTask, {
    name: '',
    description: '',
    priority: 'medium',
    due_date: ''
  })
}

const filterTasks = () => {
  // Filtering is handled by computed property
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatRelativeTime = (date: Date) => {
  const now = new Date()
  const diffInHours = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60))
  
  if (diffInHours < 1) return 'Just now'
  if (diffInHours < 24) return `${diffInHours} hours ago`
  const diffInDays = Math.floor(diffInHours / 24)
  if (diffInDays < 7) return `${diffInDays} days ago`
  return formatDate(date.toISOString())
}

// Initialize
onMounted(async () => {
  const projectId = route.params.id as string
  if (projectId) {
    await fetchProject(projectId)
  }
})
</script>