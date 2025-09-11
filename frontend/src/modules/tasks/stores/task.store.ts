import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { taskService } from '../services/task.service'
import type { 
  Task, 
  TaskListResponse, 
  TaskFilters, 
  CreateTaskData, 
  UpdateTaskData, 
  TaskStatistics,
  BulkUpdateData 
} from '../types/task.types'

export const useTaskStore = defineStore('tasks', () => {
  // State
  const tasks = ref<Task[]>([])
  const currentTask = ref<Task | null>(null)
  const statistics = ref<TaskStatistics | null>(null)
  const isLoading = ref(false)
  const isCreating = ref(false)
  const isUpdating = ref(false)
  const isDeleting = ref(false)
  const error = ref<string | null>(null)
  const lastFilters = ref<TaskFilters>({})
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 50,
    total: 0
  })

  // Computed
  const tasksByStatus = computed(() => {
    const grouped: Record<string, Task[]> = {}
    tasks.value.forEach(task => {
      const status = task.status.value
      if (!grouped[status]) {
        grouped[status] = []
      }
      grouped[status].push(task)
    })
    return grouped
  })

  const tasksByPriority = computed(() => {
    const grouped: Record<string, Task[]> = {}
    tasks.value.forEach(task => {
      const priority = task.priority.value
      if (!grouped[priority]) {
        grouped[priority] = []
      }
      grouped[priority].push(task)
    })
    return grouped
  })

  const overdueTasks = computed(() => 
    tasks.value.filter(task => task.is_overdue)
  )

  const dueSoonTasks = computed(() => 
    tasks.value.filter(task => task.is_due_soon)
  )

  const myTasks = computed(() => {
    // This would use auth store to get current user ID
    // For now, return all assigned tasks
    return tasks.value.filter(task => task.assigned_to)
  })

  const unassignedTasks = computed(() => 
    tasks.value.filter(task => !task.assigned_to)
  )

  // Actions
  const loadTasks = async (filters: TaskFilters = {}): Promise<void> => {
    try {
      isLoading.value = true
      error.value = null
      lastFilters.value = filters

      const response = await taskService.getTasks(filters)
      tasks.value = response.data
      pagination.value = {
        current_page: response.meta.current_page,
        last_page: response.meta.last_page,
        per_page: response.meta.per_page,
        total: response.meta.total
      }
    } catch (err: any) {
      error.value = err.message || 'Failed to load tasks'
      console.error('Load tasks error:', err)
    } finally {
      isLoading.value = false
    }
  }

  const refreshTasks = async (): Promise<void> => {
    await loadTasks(lastFilters.value)
  }

  const loadTask = async (id: string): Promise<void> => {
    try {
      isLoading.value = true
      error.value = null
      currentTask.value = await taskService.getTask(id)
    } catch (err: any) {
      error.value = err.message || 'Failed to load task'
      console.error('Load task error:', err)
    } finally {
      isLoading.value = false
    }
  }

  const createTask = async (data: CreateTaskData): Promise<Task | null> => {
    try {
      isCreating.value = true
      error.value = null
      
      const newTask = await taskService.createTask(data)
      
      // Add to tasks list if it matches current filters
      tasks.value.unshift(newTask)
      pagination.value.total += 1
      
      return newTask
    } catch (err: any) {
      error.value = err.message || 'Failed to create task'
      console.error('Create task error:', err)
      return null
    } finally {
      isCreating.value = false
    }
  }

  const updateTask = async (id: string, data: UpdateTaskData): Promise<Task | null> => {
    try {
      isUpdating.value = true
      error.value = null
      
      const updatedTask = await taskService.updateTask(id, data)
      
      // Update in tasks list
      const index = tasks.value.findIndex(task => task.id === id)
      if (index !== -1) {
        tasks.value[index] = updatedTask
      }
      
      // Update current task if it's the same
      if (currentTask.value?.id === id) {
        currentTask.value = updatedTask
      }
      
      return updatedTask
    } catch (err: any) {
      error.value = err.message || 'Failed to update task'
      console.error('Update task error:', err)
      return null
    } finally {
      isUpdating.value = false
    }
  }

  const deleteTask = async (id: string): Promise<boolean> => {
    try {
      isDeleting.value = true
      error.value = null
      
      await taskService.deleteTask(id)
      
      // Remove from tasks list
      const index = tasks.value.findIndex(task => task.id === id)
      if (index !== -1) {
        tasks.value.splice(index, 1)
        pagination.value.total -= 1
      }
      
      // Clear current task if it's the same
      if (currentTask.value?.id === id) {
        currentTask.value = null
      }
      
      return true
    } catch (err: any) {
      error.value = err.message || 'Failed to delete task'
      console.error('Delete task error:', err)
      return false
    } finally {
      isDeleting.value = false
    }
  }

  const updateTaskStatus = async (id: string, status: string): Promise<boolean> => {
    try {
      console.log('🔄 [TASK STORE] updateTaskStatus called:', { id, status })
      const updatedTask = await taskService.updateTaskStatus(id, status)
      console.log('✅ [TASK STORE] updateTaskStatus success:', updatedTask)
      
      // Update in tasks list
      const index = tasks.value.findIndex(task => task.id === id)
      if (index !== -1) {
        tasks.value[index] = updatedTask
      }
      
      // Update current task if it's the same
      if (currentTask.value?.id === id) {
        currentTask.value = updatedTask
      }
      
      return true
    } catch (err: any) {
      error.value = err.message || 'Failed to update task status'
      console.error('❌ [TASK STORE] Update task status error:', err)
      return false
    }
  }

  const updateTaskProgress = async (id: string, progress: number): Promise<boolean> => {
    try {
      const result = await taskService.updateTaskProgress(id, progress)
      
      // Update in tasks list
      const index = tasks.value.findIndex(task => task.id === id)
      if (index !== -1) {
        tasks.value[index].progress_percentage = result.progress_percentage
        tasks.value[index].status = { 
          value: result.status, 
          label: result.status.charAt(0).toUpperCase() + result.status.slice(1).replace('_', ' '), 
          color: result.status === 'completed' ? 'green' : result.status === 'in_progress' ? 'blue' : 'gray' 
        }
      }
      
      // Update current task if it's the same
      if (currentTask.value?.id === id) {
        currentTask.value.progress_percentage = result.progress_percentage
      }
      
      return true
    } catch (err: any) {
      error.value = err.message || 'Failed to update task progress'
      console.error('Update task progress error:', err)
      return false
    }
  }

  const assignTask = async (id: string, userId: string | null): Promise<boolean> => {
    try {
      console.log('👤 [TASK STORE] assignTask called:', { id, userId })
      console.trace('👤 [TASK STORE] assignTask call stack:')
      const updatedTask = await taskService.assignTask(id, userId)
      console.log('✅ [TASK STORE] assignTask success:', updatedTask)
      
      // Update in tasks list
      const index = tasks.value.findIndex(task => task.id === id)
      if (index !== -1) {
        tasks.value[index] = updatedTask
      }
      
      // Update current task if it's the same
      if (currentTask.value?.id === id) {
        currentTask.value = updatedTask
      }
      
      return true
    } catch (err: any) {
      error.value = err.message || 'Failed to assign task'
      console.error('❌ [TASK STORE] Assign task error:', err)
      return false
    }
  }

  const logTime = async (id: string, hours: number): Promise<boolean> => {
    try {
      const result = await taskService.logTime(id, hours)
      
      // Update in tasks list
      const index = tasks.value.findIndex(task => task.id === id)
      if (index !== -1) {
        tasks.value[index].actual_hours = result.actual_hours
      }
      
      // Update current task if it's the same
      if (currentTask.value?.id === id) {
        currentTask.value.actual_hours = result.actual_hours
      }
      
      return true
    } catch (err: any) {
      error.value = err.message || 'Failed to log time'
      console.error('Log time error:', err)
      return false
    }
  }

  const bulkUpdateTasks = async (taskIds: string[], updates: BulkUpdateData): Promise<boolean> => {
    try {
      const result = await taskService.bulkUpdateTasks(taskIds, updates)
      
      // Refresh tasks to get updated data
      await refreshTasks()
      
      return result.updated_count > 0
    } catch (err: any) {
      error.value = err.message || 'Failed to bulk update tasks'
      console.error('Bulk update tasks error:', err)
      return false
    }
  }

  const duplicateTask = async (id: string, overrides: Partial<CreateTaskData> = {}): Promise<Task | null> => {
    try {
      const duplicatedTask = await taskService.duplicateTask(id, overrides)
      
      // Add to tasks list
      tasks.value.unshift(duplicatedTask)
      pagination.value.total += 1
      
      return duplicatedTask
    } catch (err: any) {
      error.value = err.message || 'Failed to duplicate task'
      console.error('Duplicate task error:', err)
      return null
    }
  }

  const loadStatistics = async (projectId?: string): Promise<void> => {
    try {
      isLoading.value = true
      error.value = null
      statistics.value = await taskService.getTaskStatistics(projectId)
    } catch (err: any) {
      error.value = err.message || 'Failed to load task statistics'
      console.error('Load statistics error:', err)
    } finally {
      isLoading.value = false
    }
  }

  const searchTasks = async (query: string): Promise<Task[]> => {
    try {
      return await taskService.searchTasks(query)
    } catch (err: any) {
      error.value = err.message || 'Failed to search tasks'
      console.error('Search tasks error:', err)
      return []
    }
  }

  const loadProjectTasks = async (projectId: string): Promise<void> => {
    try {
      isLoading.value = true
      error.value = null
      const projectTasks = await taskService.getProjectTasks(projectId)
      tasks.value = projectTasks
    } catch (err: any) {
      error.value = err.message || 'Failed to load project tasks'
      console.error('Load project tasks error:', err)
    } finally {
      isLoading.value = false
    }
  }

  const loadUserTasks = async (userId: string): Promise<void> => {
    try {
      isLoading.value = true
      error.value = null
      const userTasks = await taskService.getUserTasks(userId)
      tasks.value = userTasks
    } catch (err: any) {
      error.value = err.message || 'Failed to load user tasks'
      console.error('Load user tasks error:', err)
    } finally {
      isLoading.value = false
    }
  }

  const loadOverdueTasks = async (): Promise<void> => {
    try {
      isLoading.value = true
      error.value = null
      const overdue = await taskService.getOverdueTasks()
      tasks.value = overdue
    } catch (err: any) {
      error.value = err.message || 'Failed to load overdue tasks'
      console.error('Load overdue tasks error:', err)
    } finally {
      isLoading.value = false
    }
  }

  const loadTasksDueSoon = async (days = 7): Promise<void> => {
    try {
      isLoading.value = true
      error.value = null
      const dueSoon = await taskService.getTasksDueSoon(days)
      tasks.value = dueSoon
    } catch (err: any) {
      error.value = err.message || 'Failed to load tasks due soon'
      console.error('Load tasks due soon error:', err)
    } finally {
      isLoading.value = false
    }
  }

  const clearError = (): void => {
    error.value = null
  }

  const clearCurrentTask = (): void => {
    currentTask.value = null
  }

  const clearTasks = (): void => {
    tasks.value = []
    pagination.value = {
      current_page: 1,
      last_page: 1,
      per_page: 50,
      total: 0
    }
  }

  // Getters
  const getTaskById = (id: string): Task | undefined => {
    return tasks.value.find(task => task.id === id)
  }

  const getTasksByProject = (projectId: string): Task[] => {
    return tasks.value.filter(task => task.project?.id === projectId)
  }

  const getTasksByAssignee = (userId: string): Task[] => {
    return tasks.value.filter(task => task.assigned_to?.id === userId)
  }

  return {
    // State
    tasks,
    currentTask,
    statistics,
    isLoading,
    isCreating,
    isUpdating,
    isDeleting,
    error,
    lastFilters,
    pagination,

    // Computed
    tasksByStatus,
    tasksByPriority,
    overdueTasks,
    dueSoonTasks,
    myTasks,
    unassignedTasks,

    // Actions
    loadTasks,
    refreshTasks,
    loadTask,
    createTask,
    updateTask,
    deleteTask,
    updateTaskStatus,
    updateTaskProgress,
    assignTask,
    logTime,
    bulkUpdateTasks,
    duplicateTask,
    loadStatistics,
    searchTasks,
    loadProjectTasks,
    loadUserTasks,
    loadOverdueTasks,
    loadTasksDueSoon,
    clearError,
    clearCurrentTask,
    clearTasks,

    // Getters
    getTaskById,
    getTasksByProject,
    getTasksByAssignee
  }
})