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
      console.warn('Task API unavailable, using mock data:', error)
      return this.getMockTaskList(filters)
    }
  }

  async getTask(id: string): Promise<Task> {
    try {
      const response = await apiClient.get<{ data: Task }>(`${this.baseUrl}/${id}`)
      return response.data
    } catch (error) {
      console.warn('Task API unavailable, using mock data:', error)
      return this.getMockTask(id)
    }
  }

  async createTask(data: CreateTaskData): Promise<Task> {
    try {
      const response = await apiClient.post<{ data: Task }>(this.baseUrl, data)
      return response.data
    } catch (error) {
      console.warn('Task API unavailable, simulating create:', error)
      return this.getMockTask('new-task')
    }
  }

  async updateTask(id: string, data: UpdateTaskData): Promise<Task> {
    try {
      const response = await apiClient.put<{ data: Task }>(`${this.baseUrl}/${id}`, data)
      return response.data
    } catch (error) {
      console.warn('Task API unavailable, simulating update:', error)
      return this.getMockTask(id)
    }
  }

  async deleteTask(id: string): Promise<void> {
    try {
      await apiClient.delete(`${this.baseUrl}/${id}`)
    } catch (error) {
      console.warn('Task API unavailable, simulating delete:', error)
    }
  }

  async updateTaskStatus(id: string, status: string): Promise<Task> {
    try {
      const response = await apiClient.patch<{ data: Task }>(`${this.baseUrl}/${id}/status`, { status })
      return response.data
    } catch (error) {
      console.warn('Task API unavailable, simulating status update:', error)
      return this.getMockTask(id)
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
      console.warn('Task API unavailable, simulating progress update:', error)
      return { progress_percentage: progress, status: 'in_progress' }
    }
  }

  async assignTask(id: string, userId: string | null): Promise<Task> {
    try {
      const response = await apiClient.patch<{ data: Task }>(`${this.baseUrl}/${id}/assign`, { assigned_to_id: userId })
      return response.data
    } catch (error) {
      console.warn('Task API unavailable, simulating assignment:', error)
      return this.getMockTask(id)
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
      console.warn('Task API unavailable, simulating time log:', error)
      return { actual_hours: hours, estimated_hours: 8, time_variance: 0 }
    }
  }

  async getProjectTasks(projectId: string): Promise<Task[]> {
    try {
      const response = await apiClient.get<{ data: Task[] }>(`${this.baseUrl}/project/${projectId}`)
      return response.data
    } catch (error) {
      console.warn('Task API unavailable, using mock data:', error)
      return this.getMockTaskList({ project_id: projectId }).data
    }
  }

  async getUserTasks(userId: string): Promise<Task[]> {
    try {
      const response = await apiClient.get<{ data: Task[] }>(`${this.baseUrl}/assignee/${userId}`)
      return response.data
    } catch (error) {
      console.warn('Task API unavailable, using mock data:', error)
      return this.getMockTaskList({ assigned_to_id: userId }).data
    }
  }

  async searchTasks(query: string): Promise<Task[]> {
    try {
      const response = await apiClient.get<{ data: Task[] }>(`${this.baseUrl}/search`, { query })
      return response.data
    } catch (error) {
      console.warn('Task API unavailable, using mock data:', error)
      return this.getMockTaskList({ search: query }).data
    }
  }

  async getOverdueTasks(): Promise<Task[]> {
    try {
      const response = await apiClient.get<{ data: Task[] }>(`${this.baseUrl}/overdue`)
      return response.data
    } catch (error) {
      console.warn('Task API unavailable, using mock data:', error)
      return this.getMockTaskList({ overdue: true }).data
    }
  }

  async getTasksDueSoon(days = 7): Promise<Task[]> {
    try {
      const response = await apiClient.get<{ data: Task[] }>(`${this.baseUrl}/due-soon`, { days })
      return response.data
    } catch (error) {
      console.warn('Task API unavailable, using mock data:', error)
      return this.getMockTaskList({ due_soon: true, due_soon_days: days }).data
    }
  }

  async getTaskStatistics(projectId?: string): Promise<TaskStatistics> {
    try {
      const params = projectId ? { project_id: projectId } : {}
      const response = await apiClient.get<{ data: TaskStatistics }>(`${this.baseUrl}/statistics`, params)
      return response.data
    } catch (error) {
      console.warn('Task API unavailable, using mock data:', error)
      return this.getMockStatistics()
    }
  }

  async getTaskHierarchy(id: string): Promise<Task[]> {
    try {
      const response = await apiClient.get<{ data: Task[] }>(`${this.baseUrl}/${id}/hierarchy`)
      return response.data
    } catch (error) {
      console.warn('Task API unavailable, using mock data:', error)
      return [this.getMockTask(id)]
    }
  }

  async duplicateTask(id: string, overrides: Partial<CreateTaskData> = {}): Promise<Task> {
    try {
      const response = await apiClient.post<{ data: Task }>(`${this.baseUrl}/${id}/duplicate`, overrides)
      return response.data
    } catch (error) {
      console.warn('Task API unavailable, simulating duplicate:', error)
      return this.getMockTask('duplicated-task')
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
      console.warn('Task API unavailable, simulating bulk update:', error)
      return { updated_count: taskIds.length }
    }
  }

  async getTaskDependencies(id: string): Promise<TaskDependency[]> {
    try {
      const response = await apiClient.get<{ data: TaskDependency[] }>(`${this.baseUrl}/${id}/dependencies`)
      return response.data
    } catch (error) {
      console.warn('Task API unavailable, using mock data:', error)
      return []
    }
  }

  async getTaskComments(id: string): Promise<TaskComment[]> {
    try {
      const response = await apiClient.get<{ data: TaskComment[] }>(`${this.baseUrl}/${id}/comments`)
      return response.data
    } catch (error) {
      console.warn('Task API unavailable, using mock data:', error)
      return []
    }
  }

  // Mock data methods for offline development
  private getMockTaskList(filters: TaskFilters = {}): TaskListResponse {
    const mockTasks: Task[] = [
      {
        id: '1',
        name: 'Foundation excavation and preparation',
        description: 'Prepare the foundation area including excavation and concrete pouring',
        status: { value: 'in_progress', label: 'In Progress', color: 'blue' },
        priority: { value: 'high', label: 'High', color: 'red', weight: 3 },
        task_type: { value: 'construction', label: 'Construction', color: 'orange', category: 'field' },
        project: { id: 'proj-1', name: 'Downtown Office Building', status: 'active' },
        phase: { id: 'phase-1', name: 'Foundation Phase' },
        assigned_to: { id: 'user-1', name: 'John Smith', email: 'john@example.com', role: 'field_worker' },
        created_by: { id: 'user-2', name: 'Jane Manager', email: 'jane@example.com', role: 'project_manager' },
        estimated_hours: 24,
        actual_hours: 16,
        progress_percentage: 65,
        start_date: '2025-09-10',
        due_date: '2025-09-15',
        task_order: 1,
        is_overdue: false,
        is_due_soon: true,
        is_top_level: true,
        level: 0,
        sub_tasks_count: 3,
        comments_count: 2,
        dependencies_count: 1,
        created_at: '2025-09-10T08:00:00Z',
        updated_at: '2025-09-11T14:30:00Z'
      },
      {
        id: '2',
        name: 'Steel frame installation',
        description: 'Install steel frame structure for the main building',
        status: { value: 'not_started', label: 'Not Started', color: 'gray' },
        priority: { value: 'medium', label: 'Medium', color: 'yellow', weight: 2 },
        task_type: { value: 'construction', label: 'Construction', color: 'orange', category: 'field' },
        project: { id: 'proj-1', name: 'Downtown Office Building', status: 'active' },
        phase: { id: 'phase-2', name: 'Structure Phase' },
        assigned_to: { id: 'user-3', name: 'Mike Worker', email: 'mike@example.com', role: 'field_worker' },
        created_by: { id: 'user-2', name: 'Jane Manager', email: 'jane@example.com', role: 'project_manager' },
        estimated_hours: 40,
        actual_hours: 0,
        progress_percentage: 0,
        start_date: '2025-09-16',
        due_date: '2025-09-25',
        task_order: 2,
        is_overdue: false,
        is_due_soon: false,
        is_top_level: true,
        level: 0,
        sub_tasks_count: 5,
        comments_count: 0,
        dependencies_count: 2,
        created_at: '2025-09-10T08:00:00Z',
        updated_at: '2025-09-10T08:00:00Z'
      },
      {
        id: '3',
        name: 'Electrical wiring inspection',
        description: 'Safety inspection of electrical installations',
        status: { value: 'completed', label: 'Completed', color: 'green' },
        priority: { value: 'critical', label: 'Critical', color: 'red', weight: 4 },
        task_type: { value: 'inspection', label: 'Inspection', color: 'purple', category: 'quality' },
        project: { id: 'proj-2', name: 'Residential Complex', status: 'active' },
        phase: { id: 'phase-3', name: 'Electrical Phase' },
        assigned_to: { id: 'user-4', name: 'Sarah Inspector', email: 'sarah@example.com', role: 'supervisor' },
        created_by: { id: 'user-2', name: 'Jane Manager', email: 'jane@example.com', role: 'project_manager' },
        estimated_hours: 4,
        actual_hours: 3.5,
        progress_percentage: 100,
        start_date: '2025-09-08',
        due_date: '2025-09-09',
        completed_at: '2025-09-09T16:00:00Z',
        task_order: 1,
        is_overdue: false,
        is_due_soon: false,
        is_top_level: true,
        level: 0,
        sub_tasks_count: 0,
        comments_count: 1,
        dependencies_count: 0,
        created_at: '2025-09-08T08:00:00Z',
        updated_at: '2025-09-09T16:00:00Z'
      }
    ]

    // Apply filters to mock data
    let filteredTasks = mockTasks

    if (filters.status) {
      filteredTasks = filteredTasks.filter(task => task.status.value === filters.status)
    }
    if (filters.priority) {
      filteredTasks = filteredTasks.filter(task => task.priority.value === filters.priority)
    }
    if (filters.project_id) {
      filteredTasks = filteredTasks.filter(task => task.project?.id === filters.project_id)
    }
    if (filters.assigned_to_id) {
      filteredTasks = filteredTasks.filter(task => task.assigned_to?.id === filters.assigned_to_id)
    }
    if (filters.search) {
      const query = filters.search.toLowerCase()
      filteredTasks = filteredTasks.filter(task => 
        task.name.toLowerCase().includes(query) || 
        task.description?.toLowerCase().includes(query)
      )
    }
    if (filters.overdue) {
      filteredTasks = filteredTasks.filter(task => task.is_overdue)
    }
    if (filters.due_soon) {
      filteredTasks = filteredTasks.filter(task => task.is_due_soon)
    }

    return {
      data: filteredTasks,
      meta: {
        current_page: 1,
        from: 1,
        last_page: 1,
        per_page: filters.per_page || 50,
        to: filteredTasks.length,
        total: filteredTasks.length
      },
      links: {
        first: '/tasks?page=1',
        last: '/tasks?page=1'
      }
    }
  }

  private getMockTask(id: string): Task {
    return {
      id,
      name: 'Sample Task',
      description: 'This is a sample task for demonstration',
      status: { value: 'not_started', label: 'Not Started', color: 'gray' },
      priority: { value: 'medium', label: 'Medium', color: 'yellow', weight: 2 },
      task_type: { value: 'general', label: 'General', color: 'blue', category: 'general' },
      project: { id: 'proj-1', name: 'Sample Project', status: 'active' },
      estimated_hours: 8,
      actual_hours: 0,
      progress_percentage: 0,
      task_order: 1,
      is_overdue: false,
      is_due_soon: false,
      is_top_level: true,
      level: 0,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    }
  }

  private getMockStatistics(): TaskStatistics {
    return {
      total_tasks: 25,
      by_status: {
        'not_started': 8,
        'in_progress': 12,
        'review': 2,
        'completed': 15,
        'cancelled': 1,
        'on_hold': 2
      },
      by_priority: {
        'low': 5,
        'medium': 12,
        'high': 6,
        'critical': 2
      },
      by_type: {
        'construction': 15,
        'inspection': 4,
        'planning': 3,
        'documentation': 3
      },
      overdue_tasks: 3,
      due_soon_tasks: 7,
      avg_completion_time: 18.5,
      total_estimated_hours: 320,
      total_actual_hours: 285,
      progress_percentage: 68
    }
  }
}

export const taskService = new TaskService()