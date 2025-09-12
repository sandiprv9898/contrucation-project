import { defineStore } from 'pinia'
import { ref, computed, readonly } from 'vue'
import { ganttApi } from '@/services/api/ganttApi'
import type { 
  GanttTask, 
  TaskDependency, 
  GanttView, 
  GanttFilters,
  CriticalPath,
  GanttExportOptions
} from '@/types/models/gantt'

export const useGanttStore = defineStore('gantt', () => {
  // State
  const tasks = ref<GanttTask[]>([])
  const dependencies = ref<TaskDependency[]>([])
  const criticalPath = ref<CriticalPath | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const selectedTask = ref<GanttTask | null>(null)
  const currentView = ref<GanttView>({
    scale: 'week',
    startDate: new Date(),
    endDate: new Date(Date.now() + 90 * 24 * 60 * 60 * 1000), // 90 days
    showWeekends: true,
    showDependencies: true,
    showCriticalPath: false,
    showProgress: true,
    showResources: false,
    showMilestones: true
  })
  const filters = ref<GanttFilters>({
    showCompleted: true,
    showOverdue: true
  })

  // Computed
  const filteredTasks = computed(() => {
    let result = [...tasks.value]

    // Apply filters
    if (filters.value.status?.length) {
      result = result.filter(t => filters.value.status?.includes(t.status))
    }

    if (filters.value.assignees?.length) {
      result = result.filter(t => 
        t.assignees?.some(a => filters.value.assignees?.includes(a))
      )
    }

    if (filters.value.priority?.length) {
      result = result.filter(t => filters.value.priority?.includes(t.priority))
    }

    if (!filters.value.showCompleted) {
      result = result.filter(t => t.status !== 'completed')
    }

    if (filters.value.search) {
      const search = filters.value.search.toLowerCase()
      result = result.filter(t => 
        t.name.toLowerCase().includes(search) ||
        t.description?.toLowerCase().includes(search)
      )
    }

    if (filters.value.dateRange) {
      const start = new Date(filters.value.dateRange.start)
      const end = new Date(filters.value.dateRange.end)
      result = result.filter(t => {
        const taskStart = new Date(t.startDate)
        const taskEnd = new Date(t.endDate)
        return taskStart <= end && taskEnd >= start
      })
    }

    return result
  })

  const taskTree = computed(() => {
    const map = new Map<string, GanttTask>()
    const roots: GanttTask[] = []

    // First pass: create map
    filteredTasks.value.forEach(task => {
      map.set(task.id, { ...task, children: [] })
    })

    // Second pass: build tree
    filteredTasks.value.forEach(task => {
      const node = map.get(task.id)!
      if (task.parentId) {
        const parent = map.get(task.parentId)
        if (parent) {
          parent.children = parent.children || []
          parent.children.push(node)
        } else {
          roots.push(node)
        }
      } else {
        roots.push(node)
      }
    })

    return roots
  })

  const statistics = computed(() => {
    const total = tasks.value.length
    const completed = tasks.value.filter(t => t.status === 'completed').length
    const inProgress = tasks.value.filter(t => t.status === 'in_progress').length
    const pending = tasks.value.filter(t => t.status === 'pending').length
    const overdue = tasks.value.filter(t => {
      const now = new Date()
      const end = new Date(t.endDate)
      return end < now && t.status !== 'completed'
    }).length

    const completionRate = total > 0 ? Math.round((completed / total) * 100) : 0
    
    const totalEstimatedHours = tasks.value.reduce((sum, t) => sum + (t.estimatedHours || 0), 0)
    const totalActualHours = tasks.value.reduce((sum, t) => sum + (t.actualHours || 0), 0)
    const totalCost = tasks.value.reduce((sum, t) => sum + (t.cost || 0), 0)

    return {
      total,
      completed,
      inProgress,
      pending,
      overdue,
      completionRate,
      totalEstimatedHours,
      totalActualHours,
      totalCost,
      efficiency: totalEstimatedHours > 0 
        ? Math.round((totalActualHours / totalEstimatedHours) * 100) 
        : 0
    }
  })

  const criticalTasks = computed(() => {
    if (!criticalPath.value) return []
    return tasks.value.filter(t => criticalPath.value?.tasks.includes(t.id))
  })

  // Actions
  const fetchGanttData = async (projectId: string) => {
    loading.value = true
    error.value = null
    
    try {
      const [ganttData, deps, critical] = await Promise.all([
        ganttApi.getGanttTasks(projectId, currentView.value),
        ganttApi.getTaskDependencies(projectId),
        ganttApi.getCriticalPath(projectId)
      ])
      
      tasks.value = ganttData || []
      dependencies.value = deps || []
      criticalPath.value = critical
    } catch (e: any) {
      error.value = e.message || 'Failed to fetch Gantt data'
      console.error('Gantt fetch error:', e)
    } finally {
      loading.value = false
    }
  }

  const updateTaskDates = async (taskId: string, startDate: string, endDate: string) => {
    try {
      const updated = await ganttApi.updateTaskDates(taskId, { startDate, endDate })
      const index = tasks.value.findIndex(t => t.id === taskId)
      if (index !== -1) {
        tasks.value[index] = updated
      }
      return updated
    } catch (e: any) {
      error.value = e.message || 'Failed to update task dates'
      throw e
    }
  }

  const updateTaskProgress = async (taskId: string, progress: number) => {
    try {
      const updated = await ganttApi.updateTaskProgress(taskId, progress)
      const index = tasks.value.findIndex(t => t.id === taskId)
      if (index !== -1) {
        tasks.value[index] = updated
      }
      return updated
    } catch (e: any) {
      error.value = e.message || 'Failed to update task progress'
      throw e
    }
  }

  const addDependency = async (dependency: Partial<TaskDependency>) => {
    try {
      const created = await ganttApi.createDependency(dependency)
      dependencies.value.push(created)
      return created
    } catch (e: any) {
      error.value = e.message || 'Failed to create dependency'
      throw e
    }
  }

  const removeDependency = async (dependencyId: string) => {
    try {
      await ganttApi.deleteDependency(dependencyId)
      const index = dependencies.value.findIndex(d => d.id === dependencyId)
      if (index !== -1) {
        dependencies.value.splice(index, 1)
      }
    } catch (e: any) {
      error.value = e.message || 'Failed to remove dependency'
      throw e
    }
  }

  const autoScheduleTasks = async (projectId: string, options?: {
    respectDependencies?: boolean
    optimizeResources?: boolean
    avoidWeekends?: boolean
  }) => {
    loading.value = true
    try {
      const result = await ganttApi.autoSchedule(projectId, options)
      tasks.value = result.tasks
      return result
    } catch (e: any) {
      error.value = e.message || 'Failed to auto-schedule tasks'
      throw e
    } finally {
      loading.value = false
    }
  }

  const exportGantt = async (projectId: string, options: GanttExportOptions) => {
    try {
      const blob = await ganttApi.exportGantt(projectId, options)
      
      // Create download link
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `gantt-chart-${projectId}.${options.format}`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)
      
      return true
    } catch (e: any) {
      error.value = e.message || 'Failed to export Gantt chart'
      throw e
    }
  }

  const importGantt = async (projectId: string, file: File, format: 'mpp' | 'xml' | 'csv') => {
    loading.value = true
    try {
      const result = await ganttApi.importGantt(projectId, file, format)
      tasks.value = result.tasks
      dependencies.value = result.dependencies
      return result
    } catch (e: any) {
      error.value = e.message || 'Failed to import Gantt data'
      throw e
    } finally {
      loading.value = false
    }
  }

  const selectTask = (task: GanttTask | null) => {
    selectedTask.value = task
  }

  const updateView = (view: Partial<GanttView>) => {
    currentView.value = { ...currentView.value, ...view }
  }

  const updateFilters = (newFilters: Partial<GanttFilters>) => {
    filters.value = { ...filters.value, ...newFilters }
  }

  const validateDependencies = async (projectId: string) => {
    try {
      const result = await ganttApi.validateDependencies(projectId)
      return result
    } catch (e: any) {
      error.value = e.message || 'Failed to validate dependencies'
      throw e
    }
  }

  const getResourceAllocation = async (projectId: string, dateRange?: { 
    start: string
    end: string 
  }) => {
    try {
      const result = await ganttApi.getResourceAllocation(projectId, dateRange)
      return result
    } catch (e: any) {
      error.value = e.message || 'Failed to get resource allocation'
      throw e
    }
  }

  const reset = () => {
    tasks.value = []
    dependencies.value = []
    criticalPath.value = null
    selectedTask.value = null
    error.value = null
  }

  return {
    // State
    tasks: readonly(tasks),
    dependencies: readonly(dependencies),
    criticalPath: readonly(criticalPath),
    loading: readonly(loading),
    error: readonly(error),
    selectedTask: readonly(selectedTask),
    currentView: readonly(currentView),
    filters: readonly(filters),
    
    // Computed
    filteredTasks,
    taskTree,
    statistics,
    criticalTasks,
    
    // Actions
    fetchGanttData,
    updateTaskDates,
    updateTaskProgress,
    addDependency,
    removeDependency,
    autoScheduleTasks,
    exportGantt,
    importGantt,
    selectTask,
    updateView,
    updateFilters,
    validateDependencies,
    getResourceAllocation,
    reset
  }
})