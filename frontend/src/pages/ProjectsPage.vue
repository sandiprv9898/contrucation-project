<template>
  <div class="p-6 max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Project Management</h1>
        <p class="text-gray-600 mt-1">Manage your construction projects, timelines, and deliverables</p>
      </div>
      
      <div class="flex space-x-3">
        <button
          @click="showCreateModal = true"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors"
        >
          <Plus class="w-4 h-4" />
          <span>New Project</span>
        </button>
        
        <button
          @click="refreshProjects"
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
            <ClipboardList class="w-6 h-6 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Projects</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.total_projects }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-2 bg-green-100 rounded-lg">
            <CheckCircle class="w-6 h-6 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Active Projects</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.active_projects }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-2 bg-yellow-100 rounded-lg">
            <DollarSign class="w-6 h-6 text-yellow-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Budget</p>
            <p class="text-2xl font-bold text-gray-900">${{ formatMoney(statistics.total_budget) }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-2 bg-purple-100 rounded-lg">
            <TrendingUp class="w-6 h-6 text-purple-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Avg Progress</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.avg_progress }}%</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow mb-6">
      <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Filters</h2>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <div class="relative">
              <Search class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
              <input
                v-model="filters.search"
                type="text"
                placeholder="Search projects..."
                class="pl-9 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                @input="debouncedSearch"
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
              <option value="">All Status</option>
              <option value="planning">Planning</option>
              <option value="active">Active</option>
              <option value="on_hold">On Hold</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
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
              <option value="critical">Critical</option>
              <option value="high">High</option>
              <option value="medium">Medium</option>
              <option value="low">Low</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Project Type</label>
            <select
              v-model="filters.project_type"
              @change="applyFilters"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">All Types</option>
              <option value="commercial">Commercial</option>
              <option value="residential">Residential</option>
              <option value="renovation">Renovation</option>
              <option value="restoration">Restoration</option>
            </select>
          </div>
        </div>
        
        <div class="flex items-center space-x-4">
          <label class="flex items-center">
            <input
              v-model="filters.overdue"
              type="checkbox"
              @change="applyFilters"
              class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
            <span class="ml-2 text-sm text-gray-700">Show overdue projects only</span>
          </label>
          
          <button
            @click="clearFilters"
            class="text-sm text-gray-500 hover:text-gray-700"
          >
            Clear all filters
          </button>
        </div>
      </div>
    </div>

    <!-- View Mode Toggle -->
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center space-x-2">
        <button
          @click="viewMode = 'grid'"
          :class="[
            'px-3 py-2 rounded-md text-sm font-medium transition-colors',
            viewMode === 'grid' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:text-gray-700'
          ]"
        >
          <LayoutGrid class="w-4 h-4" />
        </button>
        <button
          @click="viewMode = 'list'"
          :class="[
            'px-3 py-2 rounded-md text-sm font-medium transition-colors',
            viewMode === 'list' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:text-gray-700'
          ]"
        >
          <List class="w-4 h-4" />
        </button>
      </div>
      
      <div class="text-sm text-gray-500">
        Showing {{ projects.length }} of {{ pagination.total }} projects
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading && projects.length === 0" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
      <p class="text-gray-500">Loading projects...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!isLoading && projects.length === 0" class="text-center py-12">
      <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <ClipboardList class="text-gray-400 w-12 h-12" />
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No projects found</h3>
      <p class="text-gray-500 mb-6">Get started by creating your first project.</p>
      <button
        @click="showCreateModal = true"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center space-x-2 transition-colors"
      >
        <Plus class="w-4 h-4" />
        <span>Create Project</span>
      </button>
    </div>

    <!-- Projects Grid View -->
    <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="project in projects"
        :key="project.id"
        class="bg-white rounded-lg shadow hover:shadow-md transition-shadow cursor-pointer border border-gray-200"
        @click="selectProject(project)"
      >
        <div class="p-6">
          <!-- Project Header -->
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ project.name }}</h3>
              <p class="text-sm text-gray-600 line-clamp-2">{{ project.description }}</p>
            </div>
            <div class="ml-3 flex space-x-1">
              <button
                @click.stop="editProject(project)"
                class="text-gray-400 hover:text-blue-600 transition-colors"
                title="Edit project"
              >
                <Edit class="w-4 h-4" />
              </button>
              <button
                @click.stop="deleteProjectConfirm(project)"
                class="text-gray-400 hover:text-red-600 transition-colors"
                title="Delete project"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Status and Priority -->
          <div class="flex items-center space-x-2 mb-4">
            <span 
              :class="[
                'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                getStatusClasses(project.status.value)
              ]"
            >
              {{ project.status.label }}
            </span>
            
            <span 
              :class="[
                'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                getPriorityClasses(project.priority.value)
              ]"
            >
              {{ project.priority.label }}
            </span>
          </div>

          <!-- Progress -->
          <div class="mb-4">
            <div class="flex justify-between items-center mb-1">
              <span class="text-xs text-gray-500">Progress</span>
              <span class="text-xs font-medium text-gray-700">{{ project.progress_percentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                :style="{ width: `${project.progress_percentage}%` }"
              ></div>
            </div>
          </div>

          <!-- Project Details -->
          <div class="space-y-2 text-xs text-gray-500">
            <div v-if="project.client_company" class="flex items-center">
              <Building class="w-3 h-3 mr-2" />
              <span>{{ project.client_company.name }}</span>
            </div>
            
            <div v-if="project.project_manager" class="flex items-center">
              <User class="w-3 h-3 mr-2" />
              <span>{{ project.project_manager.name }}</span>
            </div>
            
            <div class="flex items-center">
              <DollarSign class="w-3 h-3 mr-2" />
              <span>${{ formatMoney(project.planned_budget) }} planned</span>
            </div>
            
            <div v-if="project.end_date" class="flex items-center">
              <Calendar class="w-3 h-3 mr-2" />
              <span :class="{ 'text-red-500': project.is_overdue }">
                Due {{ formatDate(project.end_date) }}
                <span v-if="project.is_overdue" class="font-medium">(Overdue)</span>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Projects List View -->
    <div v-else class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Project
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Progress
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Budget
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Due Date
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Manager
              </th>
              <th class="relative px-6 py-3">
                <span class="sr-only">Actions</span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr 
              v-for="project in projects"
              :key="project.id"
              class="hover:bg-gray-50 cursor-pointer"
              @click="selectProject(project)"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ project.name }}</div>
                    <div class="text-sm text-gray-500">{{ project.project_type.label }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  :class="[
                    'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                    getStatusClasses(project.status.value)
                  ]"
                >
                  {{ project.status.label }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                    <div 
                      class="bg-blue-600 h-2 rounded-full" 
                      :style="{ width: `${project.progress_percentage}%` }"
                    ></div>
                  </div>
                  <span class="text-sm text-gray-900">{{ project.progress_percentage }}%</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${{ formatMoney(project.planned_budget) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span :class="{ 'text-red-600': project.is_overdue, 'text-gray-900': !project.is_overdue }">
                  {{ project.end_date ? formatDate(project.end_date) : 'No due date' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ project.project_manager?.name || 'Unassigned' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex space-x-2">
                  <button
                    @click.stop="editProject(project)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    <Edit class="w-4 h-4" />
                  </button>
                  <button
                    @click.stop="deleteProjectConfirm(project)"
                    class="text-red-600 hover:text-red-900"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.total > pagination.per_page" class="flex items-center justify-between mt-6">
      <div class="text-sm text-gray-700">
        Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to 
        {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of 
        {{ pagination.total }} results
      </div>
      <div class="flex space-x-2">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page <= 1"
          class="px-3 py-1 text-sm border rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Previous
        </button>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page >= pagination.last_page"
          class="px-3 py-1 text-sm border rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div 
      v-if="showCreateModal || editingProject"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
      @click.self="closeModal"
    >
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white max-w-2xl">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ editingProject ? 'Edit Project' : 'Create New Project' }}
          </h3>
          
          <!-- Simplified form for now -->
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Project Name</label>
              <input
                v-model="projectForm.name"
                type="text"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Enter project name"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="projectForm.description"
                rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Enter project description"
              ></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select
                  v-model="projectForm.status"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                  <option value="planning">Planning</option>
                  <option value="active">Active</option>
                  <option value="on_hold">On Hold</option>
                  <option value="completed">Completed</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <select
                  v-model="projectForm.priority"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                  <option value="critical">Critical</option>
                </select>
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Planned Budget</label>
              <input
                v-model="projectForm.planned_budget"
                type="number"
                min="0"
                step="1000"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Enter planned budget"
              />
            </div>
          </div>
          
          <div class="flex justify-end space-x-3 mt-6">
            <button
              @click="closeModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="saveProject"
              :disabled="!projectForm.name"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ editingProject ? 'Update' : 'Create' }} Project
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { 
  ClipboardList, Plus, RefreshCw, CheckCircle, DollarSign, TrendingUp, Search,
  LayoutGrid, List, Edit, Trash2, Building, User, Calendar, Clock
} from 'lucide-vue-next'
import { useProjectStore } from '@/modules/projects/stores/project.store'
import type { Project, ProjectFilters, CreateProjectData, UpdateProjectData } from '@/modules/projects/types/project.types'

defineOptions({ name: 'ProjectsPage' })

// Store
const projectStore = useProjectStore()

// Direct access to store properties
const projects = computed(() => projectStore.projects)
const statistics = computed(() => projectStore.statistics)
const isLoading = computed(() => projectStore.isLoading)
const error = computed(() => projectStore.error)
const pagination = computed(() => projectStore.pagination)

// Local state
const viewMode = ref<'grid' | 'list'>('grid')
const showCreateModal = ref(false)
const editingProject = ref<Project | null>(null)

const filters = ref<ProjectFilters>({
  search: '',
  status: '',
  priority: '',
  project_type: '',
  overdue: false,
  per_page: 12
})

const projectForm = ref<CreateProjectData & UpdateProjectData>({
  name: '',
  description: '',
  status: 'planning',
  priority: 'medium',
  project_type: 'commercial',
  planned_budget: 0
})

// Methods
const loadProjects = async () => {
  await Promise.all([
    projectStore.loadProjects(filters.value),
    projectStore.loadStatistics()
  ])
}

const refreshProjects = async () => {
  await loadProjects()
}

const applyFilters = () => {
  projectStore.loadProjects(filters.value)
}

const clearFilters = () => {
  filters.value = {
    search: '',
    status: '',
    priority: '',
    project_type: '',
    overdue: false,
    per_page: 12
  }
  applyFilters()
}

let searchTimeout: ReturnType<typeof setTimeout>
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
}

const selectProject = (project: Project) => {
  // Navigate to project detail page
  console.log('Selected project:', project)
}

const editProject = (project: Project) => {
  editingProject.value = project
  projectForm.value = {
    name: project.name,
    description: project.description,
    status: project.status.value,
    priority: project.priority.value,
    project_type: project.project_type.value,
    planned_budget: project.planned_budget
  }
}

const closeModal = () => {
  showCreateModal.value = false
  editingProject.value = null
  projectForm.value = {
    name: '',
    description: '',
    status: 'planning',
    priority: 'medium',
    project_type: 'commercial',
    planned_budget: 0
  }
}

const saveProject = async () => {
  if (editingProject.value) {
    await projectStore.updateProject(editingProject.value.id, projectForm.value)
  } else {
    await projectStore.createProject(projectForm.value)
  }
  closeModal()
}

const deleteProjectConfirm = async (project: Project) => {
  if (confirm(`Are you sure you want to delete "${project.name}"?`)) {
    await projectStore.deleteProject(project.id)
  }
}

const changePage = (page: number) => {
  const newFilters = { ...filters.value }
  // In real implementation, add page parameter
  projectStore.loadProjects(newFilters)
}

const getStatusClasses = (status: string): string => {
  const classes: Record<string, string> = {
    'planning': 'bg-gray-100 text-gray-800',
    'active': 'bg-blue-100 text-blue-800',
    'on_hold': 'bg-yellow-100 text-yellow-800',
    'completed': 'bg-green-100 text-green-800',
    'cancelled': 'bg-red-100 text-red-800'
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

const formatMoney = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}

// Lifecycle
onMounted(() => {
  loadProjects()
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