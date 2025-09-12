<template>
  <div class="p-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Task Assignment</h1>
        <p class="text-gray-600 mt-1">Manage team workload and assign tasks efficiently</p>
      </div>
      
      <div class="flex space-x-3">
        <button
          @click="showBulkAssignModal = true"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors"
        >
          <Users class="w-4 h-4" />
          <span>Bulk Assign</span>
        </button>
        
        <button
          @click="refreshData"
          :disabled="isLoading"
          class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors disabled:opacity-50"
        >
          <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': isLoading }" />
          <span>Refresh</span>
        </button>
      </div>
    </div>

    <!-- Team Workload Overview -->
    <div class="bg-white rounded-lg shadow mb-6">
      <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
          <BarChart class="w-5 h-5 mr-2 text-blue-600" />
          Team Workload Overview
        </h2>
      </div>
      
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="member in teamMembers"
            :key="member.id"
            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
            :class="getWorkloadColorClasses(member.workload_percentage)"
          >
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center">
                <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-sm font-medium text-gray-700">
                  {{ member.name.split(' ').map(n => n[0]).join('').toUpperCase() }}
                </div>
                <div class="ml-3">
                  <h3 class="font-medium text-gray-900">{{ member.name }}</h3>
                  <p class="text-sm text-gray-500">{{ member.role }}</p>
                </div>
              </div>
              <span 
                class="text-sm font-medium px-2 py-1 rounded-full"
                :class="getWorkloadTextClasses(member.workload_percentage)"
              >
                {{ member.workload_percentage }}%
              </span>
            </div>
            
            <div class="mb-3">
              <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>Workload</span>
                <span>{{ member.assigned_tasks }} tasks</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                  class="h-2 rounded-full transition-all duration-300"
                  :class="getWorkloadBarClasses(member.workload_percentage)"
                  :style="{ width: `${Math.min(member.workload_percentage, 100)}%` }"
                ></div>
              </div>
            </div>
            
            <div class="text-xs text-gray-500 space-y-1">
              <div>Active: {{ member.active_tasks }} tasks</div>
              <div>Est. Hours: {{ member.estimated_hours }}h</div>
              <div>Available: {{ member.available_hours }}h/week</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Task Assignment Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Unassigned Tasks -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200">
          <h3 class="font-semibold text-gray-900 flex items-center">
            <AlertCircle class="w-4 h-4 mr-2 text-orange-500" />
            Unassigned Tasks ({{ unassignedTasks.length }})
          </h3>
        </div>
        
        <div class="p-4 max-h-96 overflow-y-auto">
          <div v-if="unassignedTasks.length === 0" class="text-center text-gray-500 py-8">
            <CheckCircle class="w-8 h-8 mx-auto mb-2 text-green-500" />
            All tasks assigned!
          </div>
          
          <div 
            v-for="task in unassignedTasks"
            :key="task.id"
            class="border border-gray-200 rounded-lg p-3 mb-3 cursor-move hover:shadow-sm transition-shadow"
            @dragstart="handleDragStart($event, task)"
            draggable="true"
          >
            <h4 class="font-medium text-sm text-gray-900 mb-1">{{ task.name }}</h4>
            <div class="flex items-center justify-between text-xs text-gray-500">
              <span class="flex items-center">
                <Folder class="w-3 h-3 mr-1" />
                {{ task.project?.name }}
              </span>
              <span class="flex items-center">
                <Clock class="w-3 h-3 mr-1" />
                {{ task.estimated_hours }}h
              </span>
            </div>
            <div class="flex items-center justify-between mt-2">
              <span 
                :class="getPriorityClasses(task.priority.value)"
                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
              >
                {{ task.priority.label }}
              </span>
              <button
                @click="showAssignModal(task)"
                class="text-blue-600 hover:text-blue-800 text-xs font-medium"
              >
                Assign
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Assignment Actions -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200">
          <h3 class="font-semibold text-gray-900 flex items-center">
            <Target class="w-4 h-4 mr-2 text-blue-500" />
            Quick Actions
          </h3>
        </div>
        
        <div class="p-4 space-y-4">
          <!-- Auto-assign based on workload -->
          <div>
            <button
              @click="autoAssignByWorkload"
              :disabled="unassignedTasks.length === 0 || isAutoAssigning"
              class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-lg text-sm flex items-center justify-center space-x-2 transition-colors"
            >
              <Zap class="w-4 h-4" />
              <span>{{ isAutoAssigning ? 'Auto-assigning...' : 'Auto-assign by Workload' }}</span>
            </button>
            <p class="text-xs text-gray-500 mt-1">Assigns tasks to team members with lowest workload</p>
          </div>
          
          <!-- Auto-assign based on skills -->
          <div>
            <button
              @click="autoAssignBySkills"
              :disabled="unassignedTasks.length === 0 || isAutoAssigning"
              class="w-full bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-lg text-sm flex items-center justify-center space-x-2 transition-colors"
            >
              <Brain class="w-4 h-4" />
              <span>{{ isAutoAssigning ? 'Auto-assigning...' : 'Auto-assign by Skills' }}</span>
            </button>
            <p class="text-xs text-gray-500 mt-1">Assigns tasks based on team member expertise</p>
          </div>
          
          <!-- Balance workload -->
          <div>
            <button
              @click="balanceWorkload"
              :disabled="isBalancing"
              class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-lg text-sm flex items-center justify-center space-x-2 transition-colors"
            >
              <Scale class="w-4 h-4" />
              <span>{{ isBalancing ? 'Balancing...' : 'Balance Workload' }}</span>
            </button>
            <p class="text-xs text-gray-500 mt-1">Redistributes tasks to balance team workload</p>
          </div>
        </div>
      </div>

      <!-- Task Filters -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200">
          <h3 class="font-semibold text-gray-900 flex items-center">
            <Filter class="w-4 h-4 mr-2 text-gray-500" />
            Filters
          </h3>
        </div>
        
        <div class="p-4 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Project</label>
            <select
              v-model="filters.project_id"
              @change="applyFilters"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">All Projects</option>
              <option value="proj-1">Downtown Office Building</option>
              <option value="proj-2">Residential Complex</option>
              <option value="proj-3">Shopping Center Renovation</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
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
            <label class="block text-sm font-medium text-gray-700 mb-2">Task Type</label>
            <select
              v-model="filters.task_type"
              @change="applyFilters"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">All Types</option>
              <option value="construction">Construction</option>
              <option value="inspection">Inspection</option>
              <option value="planning">Planning</option>
              <option value="documentation">Documentation</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
            <select
              v-model="filters.due_filter"
              @change="applyFilters"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">All Tasks</option>
              <option value="overdue">Overdue</option>
              <option value="due_soon">Due Soon</option>
              <option value="this_week">This Week</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Assignment Modal -->
    <div 
      v-if="assignmentModal.show"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
      @click.self="closeAssignmentModal"
    >
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Task</h3>
          
          <div class="mb-4">
            <h4 class="font-medium text-gray-700">{{ assignmentModal.task?.name }}</h4>
            <p class="text-sm text-gray-500">{{ assignmentModal.task?.project?.name }}</p>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Assign to</label>
            <select
              v-model="assignmentModal.selectedMember"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">Select team member...</option>
              <option 
                v-for="member in teamMembers"
                :key="member.id"
                :value="member.id"
              >
                {{ member.name }} ({{ member.workload_percentage }}% workload)
              </option>
            </select>
          </div>
          
          <div class="flex justify-end space-x-3">
            <button
              @click="closeAssignmentModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="confirmAssignment"
              :disabled="!assignmentModal.selectedMember"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Assign Task
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bulk Assignment Modal -->
    <div 
      v-if="showBulkAssignModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
      @click.self="showBulkAssignModal = false"
    >
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Assignment</h3>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Select Tasks ({{ selectedTasksForBulk.length }} selected)
            </label>
            <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-md p-2">
              <div 
                v-for="task in unassignedTasks"
                :key="task.id"
                class="flex items-center mb-2"
              >
                <input
                  type="checkbox"
                  :id="`bulk-task-${task.id}`"
                  v-model="selectedTasksForBulk"
                  :value="task.id"
                  class="mr-2"
                />
                <label :for="`bulk-task-${task.id}`" class="text-sm text-gray-700">
                  {{ task.name }}
                </label>
              </div>
            </div>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Assign all to</label>
            <select
              v-model="bulkAssignmentMember"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">Select team member...</option>
              <option 
                v-for="member in teamMembers"
                :key="member.id"
                :value="member.id"
              >
                {{ member.name }} ({{ member.workload_percentage }}% workload)
              </option>
            </select>
          </div>
          
          <div class="flex justify-end space-x-3">
            <button
              @click="showBulkAssignModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="confirmBulkAssignment"
              :disabled="selectedTasksForBulk.length === 0 || !bulkAssignmentMember"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Assign {{ selectedTasksForBulk.length }} Tasks
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
  Users, RefreshCw, BarChart, AlertCircle, CheckCircle, Folder, Clock, Target, 
  Zap, Brain, Scale, Filter
} from 'lucide-vue-next'
import { useTaskStore } from '@/modules/tasks/stores/task.store'
import type { Task, TaskFilters } from '@/modules/tasks/types/task.types'

defineOptions({ name: 'TaskAssignmentPage' })

// Types
interface TeamMember {
  id: string
  name: string
  email: string
  role: string
  workload_percentage: number
  assigned_tasks: number
  active_tasks: number
  estimated_hours: number
  available_hours: number
}

// Store
const taskStore = useTaskStore()

// State
const isLoading = ref(false)
const isAutoAssigning = ref(false)
const isBalancing = ref(false)
const draggedTask = ref<Task | null>(null)
const showBulkAssignModal = ref(false)
const selectedTasksForBulk = ref<string[]>([])
const bulkAssignmentMember = ref('')

const assignmentModal = ref({
  show: false,
  task: null as Task | null,
  selectedMember: ''
})

const filters = ref<TaskFilters & { due_filter?: string }>({
  project_id: '',
  priority: '',
  task_type: '',
  due_filter: ''
})

const teamMembers = ref<TeamMember[]>([])

// Computed
const unassignedTasks = computed(() => 
  taskStore.tasks.filter(task => !task.assigned_to)
)

// Methods
const refreshData = async () => {
  isLoading.value = true
  try {
    await taskStore.loadTasks({ assigned_to_id: 'null' })
    // In a real app, also load team member data
  } finally {
    isLoading.value = false
  }
}

const applyFilters = () => {
  const combinedFilters = { ...filters.value, assigned_to_id: 'null' }
  
  // Handle due date filter
  if (combinedFilters.due_filter) {
    switch (combinedFilters.due_filter) {
      case 'overdue':
        combinedFilters.overdue = true
        break
      case 'due_soon':
        combinedFilters.due_soon = true
        break
      case 'this_week':
        // Set date range for this week
        break
    }
    delete combinedFilters.due_filter
  }
  
  taskStore.loadTasks(combinedFilters)
}

const showAssignModal = (task: Task) => {
  assignmentModal.value = {
    show: true,
    task,
    selectedMember: ''
  }
}

const closeAssignmentModal = () => {
  assignmentModal.value = {
    show: false,
    task: null,
    selectedMember: ''
  }
}

const confirmAssignment = async () => {
  if (assignmentModal.value.task && assignmentModal.value.selectedMember) {
    await taskStore.assignTask(assignmentModal.value.task.id, assignmentModal.value.selectedMember)
    
    // Update team member workload (in real app this would come from backend)
    const member = teamMembers.value.find(m => m.id === assignmentModal.value.selectedMember)
    if (member) {
      member.assigned_tasks += 1
      member.active_tasks += 1
      member.estimated_hours += assignmentModal.value.task?.estimated_hours || 0
      member.workload_percentage = Math.min(100, (member.estimated_hours / member.available_hours) * 100)
    }
    
    closeAssignmentModal()
    refreshData()
  }
}

const confirmBulkAssignment = async () => {
  if (selectedTasksForBulk.value.length > 0 && bulkAssignmentMember.value) {
    for (const taskId of selectedTasksForBulk.value) {
      await taskStore.assignTask(taskId, bulkAssignmentMember.value)
    }
    
    // Update team member workload
    const member = teamMembers.value.find(m => m.id === bulkAssignmentMember.value)
    if (member) {
      const assignedTasks = selectedTasksForBulk.value.map(id => 
        unassignedTasks.value.find(task => task.id === id)
      ).filter(Boolean)
      
      member.assigned_tasks += assignedTasks.length
      member.active_tasks += assignedTasks.length
      member.estimated_hours += assignedTasks.reduce((sum, task) => sum + (task?.estimated_hours || 0), 0)
      member.workload_percentage = Math.min(100, (member.estimated_hours / member.available_hours) * 100)
    }
    
    showBulkAssignModal.value = false
    selectedTasksForBulk.value = []
    bulkAssignmentMember.value = ''
    refreshData()
  }
}

const autoAssignByWorkload = async () => {
  isAutoAssigning.value = true
  try {
    // Simple algorithm: assign to member with lowest workload
    for (const task of unassignedTasks.value) {
      const availableMember = teamMembers.value
        .filter(member => member.workload_percentage < 95) // Don't overload
        .sort((a, b) => a.workload_percentage - b.workload_percentage)[0]
      
      if (availableMember) {
        await taskStore.assignTask(task.id, availableMember.id)
        
        // Update workload
        availableMember.assigned_tasks += 1
        availableMember.active_tasks += 1
        availableMember.estimated_hours += task.estimated_hours || 0
        availableMember.workload_percentage = Math.min(100, (availableMember.estimated_hours / availableMember.available_hours) * 100)
      }
    }
    
    refreshData()
  } finally {
    isAutoAssigning.value = false
  }
}

const autoAssignBySkills = async () => {
  isAutoAssigning.value = true
  try {
    // Simple skill-based assignment
    for (const task of unassignedTasks.value) {
      let preferredMember = null
      
      // Match by task type and role
      if (task.task_type.value === 'inspection') {
        preferredMember = teamMembers.value.find(m => m.role === 'Supervisor' && m.workload_percentage < 95)
      } else if (task.task_type.value === 'construction') {
        preferredMember = teamMembers.value
          .filter(m => m.role === 'Field Worker' && m.workload_percentage < 95)
          .sort((a, b) => a.workload_percentage - b.workload_percentage)[0]
      } else {
        preferredMember = teamMembers.value
          .filter(m => m.workload_percentage < 95)
          .sort((a, b) => a.workload_percentage - b.workload_percentage)[0]
      }
      
      if (preferredMember) {
        await taskStore.assignTask(task.id, preferredMember.id)
        
        // Update workload
        preferredMember.assigned_tasks += 1
        preferredMember.active_tasks += 1
        preferredMember.estimated_hours += task.estimated_hours || 0
        preferredMember.workload_percentage = Math.min(100, (preferredMember.estimated_hours / preferredMember.available_hours) * 100)
      }
    }
    
    refreshData()
  } finally {
    isAutoAssigning.value = false
  }
}

const balanceWorkload = async () => {
  isBalancing.value = true
  try {
    // Simple workload balancing algorithm
    const overloadedMembers = teamMembers.value.filter(m => m.workload_percentage > 80)
    const underloadedMembers = teamMembers.value.filter(m => m.workload_percentage < 60)
    
    for (const overloaded of overloadedMembers) {
      const assignedTasks = taskStore.tasks.filter(task => 
        task.assigned_to?.id === overloaded.id && task.status.value === 'not_started'
      )
      
      // Move some tasks to underloaded members
      const tasksToMove = assignedTasks.slice(0, Math.ceil(assignedTasks.length * 0.3))
      
      for (const task of tasksToMove) {
        const target = underloadedMembers
          .sort((a, b) => a.workload_percentage - b.workload_percentage)[0]
        
        if (target && target.workload_percentage < 80) {
          await taskStore.assignTask(task.id, target.id)
          
          // Update workloads
          overloaded.assigned_tasks -= 1
          overloaded.active_tasks -= 1
          overloaded.estimated_hours -= task.estimated_hours || 0
          overloaded.workload_percentage = (overloaded.estimated_hours / overloaded.available_hours) * 100
          
          target.assigned_tasks += 1
          target.active_tasks += 1
          target.estimated_hours += task.estimated_hours || 0
          target.workload_percentage = (target.estimated_hours / target.available_hours) * 100
        }
      }
    }
    
    refreshData()
  } finally {
    isBalancing.value = false
  }
}

const handleDragStart = (event: DragEvent, task: Task) => {
  draggedTask.value = task
  event.dataTransfer?.setData('text/plain', task.id)
}

const getWorkloadColorClasses = (percentage: number): string => {
  if (percentage >= 90) return 'border-red-200 bg-red-50'
  if (percentage >= 80) return 'border-orange-200 bg-orange-50'
  if (percentage >= 60) return 'border-yellow-200 bg-yellow-50'
  return 'border-green-200 bg-green-50'
}

const getWorkloadTextClasses = (percentage: number): string => {
  if (percentage >= 90) return 'bg-red-100 text-red-800'
  if (percentage >= 80) return 'bg-orange-100 text-orange-800'
  if (percentage >= 60) return 'bg-yellow-100 text-yellow-800'
  return 'bg-green-100 text-green-800'
}

const getWorkloadBarClasses = (percentage: number): string => {
  if (percentage >= 90) return 'bg-red-500'
  if (percentage >= 80) return 'bg-orange-500'
  if (percentage >= 60) return 'bg-yellow-500'
  return 'bg-green-500'
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

// Lifecycle
onMounted(() => {
  refreshData()
})
</script>