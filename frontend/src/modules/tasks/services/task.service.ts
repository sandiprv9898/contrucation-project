import { apiClient } from '@/modules/shared/api/client'
import type {
  Task,
  TaskListResponse,
  TaskFilters,
  CreateTaskData,
  UpdateTaskData,
  TaskStatistics,
  BulkUpdateData,
  TaskDependency,
  TaskComment
} from '../types/task.types'

class TaskService {
  private baseUrl = '/tasks'

  async getTasks(filters: TaskFilters = {}): Promise<TaskListResponse> {
    try {
      return await apiClient.get<TaskListResponse>(this.baseUrl, filters)
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async getTask(id: string): Promise<Task> {
    try {
      const response = await apiClient.get<{ data: Task }>(`${this.baseUrl}/${id}`)
      return response.data
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async createTask(data: CreateTaskData): Promise<Task> {
    try {
      const response = await apiClient.post<{ data: Task }>(this.baseUrl, data)
      return response.data
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async updateTask(id: string, data: UpdateTaskData): Promise<Task> {
    try {
      const response = await apiClient.put<{ data: Task }>(`${this.baseUrl}/${id}`, data)
      return response.data
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async deleteTask(id: string): Promise<void> {
    try {
      await apiClient.delete(`${this.baseUrl}/${id}`)
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async updateTaskStatus(id: string, status: string): Promise<Task> {
    try {
      const response = await apiClient.patch<{ data: Task }>(`${this.baseUrl}/${id}/status`, { status })
      return response.data
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async updateTaskProgress(id: string, progress: number): Promise<{ progress_percentage: number, status: string }> {
    try {
      const response = await apiClient.patch<{ data: { progress_percentage: number, status: string } }>(
        `${this.baseUrl}/${id}/progress`, 
        { progress_percentage: progress }
      )
      return response.data
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async assignTask(id: string, userId: string | null): Promise<Task> {
    try {
      const response = await apiClient.patch<{ data: Task }>(`${this.baseUrl}/${id}/assign`, { assigned_to_id: userId })
      return response.data
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async logTime(id: string, hours: number): Promise<{ actual_hours: number, estimated_hours: number, time_variance: number }> {
    try {
      const response = await apiClient.post<{ data: { actual_hours: number, estimated_hours: number, time_variance: number } }>(
        `${this.baseUrl}/${id}/time`, 
        { hours }
      )
      return response.data
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async getProjectTasks(projectId: string): Promise<Task[]> {
    try {
      const response = await apiClient.get<{ data: Task[] }>(`${this.baseUrl}/project/${projectId}`)
      return response.data
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async getUserTasks(userId: string): Promise<Task[]> {
    try {
      const response = await apiClient.get<{ data: Task[] }>(`${this.baseUrl}/assignee/${userId}`)
      return response.data
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async searchTasks(query: string): Promise<Task[]> {
    try {
      const response = await apiClient.get<{ data: Task[] }>(`${this.baseUrl}/search`, { query })
      return response.data
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async getOverdueTasks(): Promise<Task[]> {
    try {
      const response = await apiClient.get<{ data: Task[] }>(`${this.baseUrl}/overdue`)
      return response.data
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async getTasksDueSoon(days = 7): Promise<Task[]> {
    try {
      const response = await apiClient.get<{ data: Task[] }>(`${this.baseUrl}/due-soon`, { days })
      return response.data
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async getTaskStatistics(projectId?: string): Promise<TaskStatistics> {
    try {
      const params = projectId ? { project_id: projectId } : {}
      const response = await apiClient.get<{ data: TaskStatistics }>(`${this.baseUrl}/statistics`, params)
      return response.data
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async getTaskHierarchy(id: string): Promise<Task[]> {
    try {
      const response = await apiClient.get<{ data: Task[] }>(`${this.baseUrl}/${id}/hierarchy`)
      return response.data
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async duplicateTask(id: string, overrides: Partial<CreateTaskData> = {}): Promise<Task> {
    try {
      const response = await apiClient.post<{ data: Task }>(`${this.baseUrl}/${id}/duplicate`, overrides)
      return response.data
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async bulkUpdateTasks(taskIds: string[], updates: BulkUpdateData): Promise<{ updated_count: number }> {
    try {
      const response = await apiClient.post<{ updated_count: number }>(`${this.baseUrl}/bulk-update`, {
        task_ids: taskIds,
        updates
      })
      return response
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async getTaskDependencies(id: string): Promise<TaskDependency[]> {
    try {
      const response = await apiClient.get<{ data: TaskDependency[] }>(`${this.baseUrl}/${id}/dependencies`)
      return response.data
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

  async getTaskComments(id: string): Promise<TaskComment[]> {
    try {
      const response = await apiClient.get<{ data: TaskComment[] }>(`${this.baseUrl}/${id}/comments`)
      return response.data
    } catch (error) {
      console.error('Task API error:', error)
      throw error
    }
  }

}

export const taskService = new TaskService()