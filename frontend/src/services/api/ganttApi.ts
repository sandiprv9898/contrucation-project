import { api } from '@/services/api/client'
import type { 
  GanttTask, 
  TaskDependency, 
  GanttView, 
  GanttFilters, 
  GanttExportOptions,
  CriticalPath
} from '@/types/models/gantt'

export const ganttApi = {
  // Fetch Gantt chart data for a project
  async getProjectGantt(projectId: string, filters?: GanttFilters) {
    const { data } = await api.get(`/projects/${projectId}/gantt`, { 
      params: filters 
    })
    return data
  },

  // Get all tasks in Gantt format
  async getGanttTasks(projectId: string, view?: GanttView) {
    const { data } = await api.get(`/projects/${projectId}/gantt/tasks`, {
      params: view
    })
    return data
  },

  // Get task dependencies
  async getTaskDependencies(projectId: string) {
    const { data } = await api.get(`/projects/${projectId}/gantt/dependencies`)
    return data
  },

  // Update task dates (drag and drop)
  async updateTaskDates(taskId: string, dates: { startDate: string; endDate: string }) {
    const { data } = await api.patch(`/tasks/${taskId}/dates`, dates)
    return data
  },

  // Update task progress
  async updateTaskProgress(taskId: string, progress: number) {
    const { data } = await api.patch(`/tasks/${taskId}/progress`, { progress })
    return data
  },

  // Create task dependency
  async createDependency(dependency: Partial<TaskDependency>) {
    const { data } = await api.post('/tasks/dependencies', dependency)
    return data
  },

  // Update task dependency
  async updateDependency(dependencyId: string, updates: Partial<TaskDependency>) {
    const { data } = await api.put(`/tasks/dependencies/${dependencyId}`, updates)
    return data
  },

  // Delete task dependency
  async deleteDependency(dependencyId: string) {
    const { data } = await api.delete(`/tasks/dependencies/${dependencyId}`)
    return data
  },

  // Get critical path
  async getCriticalPath(projectId: string): Promise<CriticalPath> {
    const { data } = await api.get(`/projects/${projectId}/gantt/critical-path`)
    return data
  },

  // Auto-schedule tasks
  async autoSchedule(projectId: string, options?: { 
    respectDependencies?: boolean
    optimizeResources?: boolean
    avoidWeekends?: boolean
  }) {
    const { data } = await api.post(`/projects/${projectId}/gantt/auto-schedule`, options)
    return data
  },

  // Export Gantt chart
  async exportGantt(projectId: string, options: GanttExportOptions) {
    const { data } = await api.post(
      `/projects/${projectId}/gantt/export`,
      options,
      { responseType: 'blob' }
    )
    return data
  },

  // Bulk update tasks
  async bulkUpdateTasks(updates: Array<{ id: string; changes: Partial<GanttTask> }>) {
    const { data } = await api.post('/tasks/bulk-update-gantt', { updates })
    return data
  },

  // Get resource allocation
  async getResourceAllocation(projectId: string, dateRange?: { start: string; end: string }) {
    const { data } = await api.get(`/projects/${projectId}/gantt/resources`, {
      params: dateRange
    })
    return data
  },

  // Validate dependencies (check for circular dependencies)
  async validateDependencies(projectId: string) {
    const { data } = await api.get(`/projects/${projectId}/gantt/validate-dependencies`)
    return data
  },

  // Get Gantt statistics
  async getGanttStatistics(projectId: string) {
    const { data } = await api.get(`/projects/${projectId}/gantt/statistics`)
    return data
  },

  // Import from MS Project or other formats
  async importGantt(projectId: string, file: File, format: 'mpp' | 'xml' | 'csv') {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('format', format)
    
    const { data } = await api.post(
      `/projects/${projectId}/gantt/import`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }
    )
    return data
  }
}