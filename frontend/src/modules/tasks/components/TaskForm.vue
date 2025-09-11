<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <!-- Task Name -->
    <div>
      <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
        Task Name *
      </label>
      <input
        id="name"
        v-model="formData.name"
        type="text"
        required
        placeholder="Enter task name..."
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        :class="{ 'border-red-500': errors.name }"
      />
      <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
    </div>

    <!-- Description -->
    <div>
      <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
        Description
      </label>
      <textarea
        id="description"
        v-model="formData.description"
        rows="3"
        placeholder="Describe the task..."
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
      ></textarea>
    </div>

    <!-- Project and Phase -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">
          Project *
        </label>
        <select
          id="project_id"
          v-model="formData.project_id"
          required
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          :class="{ 'border-red-500': errors.project_id }"
        >
          <option value="">Select a project...</option>
          <option value="proj-1">Downtown Office Building</option>
          <option value="proj-2">Residential Complex</option>
          <option value="proj-3">Shopping Center Renovation</option>
        </select>
        <p v-if="errors.project_id" class="mt-1 text-sm text-red-600">{{ errors.project_id }}</p>
      </div>

      <div>
        <label for="phase_id" class="block text-sm font-medium text-gray-700 mb-2">
          Phase
        </label>
        <select
          id="phase_id"
          v-model="formData.phase_id"
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="">Select a phase...</option>
          <option value="phase-1">Foundation Phase</option>
          <option value="phase-2">Structure Phase</option>
          <option value="phase-3">Electrical Phase</option>
          <option value="phase-4">Finishing Phase</option>
        </select>
      </div>
    </div>

    <!-- Status and Priority -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
          Status
        </label>
        <select
          id="status"
          v-model="formData.status"
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="not_started">Not Started</option>
          <option value="in_progress">In Progress</option>
          <option value="review">Review</option>
          <option value="completed">Completed</option>
          <option value="on_hold">On Hold</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>

      <div>
        <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
          Priority
        </label>
        <select
          id="priority"
          v-model="formData.priority"
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
          <option value="critical">Critical</option>
        </select>
      </div>
    </div>

    <!-- Task Type and Assignment -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label for="task_type" class="block text-sm font-medium text-gray-700 mb-2">
          Task Type
        </label>
        <select
          id="task_type"
          v-model="formData.task_type"
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="general">General</option>
          <option value="construction">Construction</option>
          <option value="inspection">Inspection</option>
          <option value="planning">Planning</option>
          <option value="documentation">Documentation</option>
          <option value="maintenance">Maintenance</option>
        </select>
      </div>

      <div>
        <label for="assigned_to_id" class="block text-sm font-medium text-gray-700 mb-2">
          Assign To
        </label>
        <select
          id="assigned_to_id"
          v-model="formData.assigned_to_id"
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

    <!-- Dates -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
          Start Date
        </label>
        <input
          id="start_date"
          v-model="formData.start_date"
          type="date"
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        />
      </div>

      <div>
        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
          Due Date
        </label>
        <input
          id="due_date"
          v-model="formData.due_date"
          type="date"
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        />
      </div>
    </div>

    <!-- Estimated Hours -->
    <div>
      <label for="estimated_hours" class="block text-sm font-medium text-gray-700 mb-2">
        Estimated Hours
      </label>
      <input
        id="estimated_hours"
        v-model="formData.estimated_hours"
        type="number"
        min="0"
        step="0.5"
        placeholder="0.0"
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
      />
    </div>

    <!-- Parent Task -->
    <div v-if="!isEditMode">
      <label for="parent_task_id" class="block text-sm font-medium text-gray-700 mb-2">
        Parent Task (Optional)
      </label>
      <select
        id="parent_task_id"
        v-model="formData.parent_task_id"
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
      >
        <option value="">None - Top Level Task</option>
        <option value="task-1">Foundation excavation and preparation</option>
        <option value="task-2">Steel frame installation</option>
      </select>
    </div>

    <!-- Form Actions -->
    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
      <button
        type="button"
        @click="$emit('cancelled')"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
      >
        Cancel
      </button>
      
      <button
        type="submit"
        :disabled="isSubmitting"
        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span v-if="isSubmitting" class="flex items-center">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
          {{ isEditMode ? 'Updating...' : 'Creating...' }}
        </span>
        <span v-else>
          {{ isEditMode ? 'Update Task' : 'Create Task' }}
        </span>
      </button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useTaskStore } from '../stores/task.store'
import type { Task, CreateTaskData, UpdateTaskData } from '../types/task.types'

interface Props {
  task?: Task | null
}

interface Emits {
  (e: 'saved', task: Task): void
  (e: 'cancelled'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const taskStore = useTaskStore()

// Form state
const isSubmitting = ref(false)
const errors = ref<Record<string, string>>({})

const isEditMode = computed(() => !!props.task)

// Form data
const formData = ref<CreateTaskData & UpdateTaskData>({
  name: '',
  description: '',
  project_id: '',
  phase_id: '',
  status: 'not_started',
  priority: 'medium',
  task_type: 'general',
  assigned_to_id: '',
  start_date: '',
  due_date: '',
  estimated_hours: undefined,
  parent_task_id: ''
})

// Watch for task changes (edit mode)
watch(() => props.task, (newTask) => {
  if (newTask) {
    formData.value = {
      name: newTask.name,
      description: newTask.description || '',
      project_id: newTask.project?.id || '',
      phase_id: newTask.phase?.id || '',
      status: newTask.status.value,
      priority: newTask.priority.value,
      task_type: newTask.task_type.value,
      assigned_to_id: newTask.assigned_to?.id || '',
      start_date: newTask.start_date || '',
      due_date: newTask.due_date || '',
      estimated_hours: newTask.estimated_hours,
      parent_task_id: newTask.parent_task?.id || ''
    }
  }
}, { immediate: true })

// Validation
const validateForm = (): boolean => {
  errors.value = {}
  
  if (!formData.value.name?.trim()) {
    errors.value.name = 'Task name is required'
  }
  
  if (!formData.value.project_id) {
    errors.value.project_id = 'Project is required'
  }
  
  if (formData.value.start_date && formData.value.due_date) {
    if (new Date(formData.value.start_date) > new Date(formData.value.due_date)) {
      errors.value.due_date = 'Due date must be after start date'
    }
  }
  
  return Object.keys(errors.value).length === 0
}

// Submit handler
const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }
  
  isSubmitting.value = true
  
  try {
    let result: Task | null = null
    
    if (isEditMode.value && props.task) {
      // Update existing task
      result = await taskStore.updateTask(props.task.id, formData.value)
    } else {
      // Create new task
      result = await taskStore.createTask(formData.value)
    }
    
    if (result) {
      emit('saved', result)
      
      // Reset form if creating new task
      if (!isEditMode.value) {
        resetForm()
      }
    }
  } catch (error) {
    console.error('Form submission error:', error)
    // Handle error (could show toast notification)
  } finally {
    isSubmitting.value = false
  }
}

// Reset form
const resetForm = () => {
  formData.value = {
    name: '',
    description: '',
    project_id: '',
    phase_id: '',
    status: 'not_started',
    priority: 'medium',
    task_type: 'general',
    assigned_to_id: '',
    start_date: '',
    due_date: '',
    estimated_hours: undefined,
    parent_task_id: ''
  }
  errors.value = {}
}
</script>