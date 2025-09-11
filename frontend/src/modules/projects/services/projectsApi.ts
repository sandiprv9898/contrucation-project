import { api } from '@/services/api'
import type {
  Project,
  ProjectListItem,
  CreateProjectRequest,
  UpdateProjectRequest,
  ProjectFilters,
  ProjectsResponse,
  ProjectStatistics
} from '../types/projects.types'

export const projectsApi = {
  // Get projects with filtering and pagination
  async getProjects(filters: ProjectFilters & {
    page?: number
    per_page?: number
    sort_by?: string
    sort_direction?: 'asc' | 'desc'
  } = {}): Promise<ProjectsResponse> {
    const params = new URLSearchParams()
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, String(value))
      }
    })

    const response = await api.get(`/projects?${params}`)
    return response.data
  },

  // Get single project
  async getProject(id: string): Promise<Project> {
    const response = await api.get(`/projects/${id}`)
    return response.data.data
  },

  // Create project
  async createProject(data: CreateProjectRequest): Promise<Project> {
    const response = await api.post('/projects', data)
    return response.data.data
  },

  // Update project
  async updateProject(id: string, data: UpdateProjectRequest): Promise<Project> {
    const response = await api.put(`/projects/${id}`, data)
    return response.data.data
  },

  // Delete project
  async deleteProject(id: string): Promise<void> {
    await api.delete(`/projects/${id}`)
  },

  // Update project status
  async updateProjectStatus(id: string, status: string): Promise<Project> {
    const response = await api.patch(`/projects/${id}/status`, { status })
    return response.data.data
  },

  // Update project progress
  async updateProjectProgress(id: string): Promise<{ progress_percentage: number }> {
    const response = await api.post(`/projects/${id}/progress`)
    return response.data.data
  },

  // Get projects by company
  async getProjectsByCompany(companyId: string): Promise<ProjectListItem[]> {
    const response = await api.get(`/projects/company/${companyId}`)
    return response.data.data
  },

  // Get projects by manager
  async getProjectsByManager(managerId: string): Promise<ProjectListItem[]> {
    const response = await api.get(`/projects/manager/${managerId}`)
    return response.data.data
  },

  // Search projects
  async searchProjects(query: string): Promise<ProjectListItem[]> {
    const response = await api.get('/projects/search', {
      params: { query }
    })
    return response.data.data
  },

  // Get overdue projects
  async getOverdueProjects(): Promise<ProjectListItem[]> {
    const response = await api.get('/projects/overdue')
    return response.data.data
  },

  // Get project statistics
  async getProjectStatistics(): Promise<ProjectStatistics> {
    const response = await api.get('/projects/statistics')
    return response.data.data
  },

  // Utility functions for frontend
  getStatusColor(status: string): string {
    const colors = {
      draft: 'gray',
      active: 'blue',
      on_hold: 'yellow',
      completed: 'green',
      cancelled: 'red'
    }
    return colors[status as keyof typeof colors] || 'gray'
  },

  getPriorityColor(priority: string): string {
    const colors = {
      low: 'gray',
      medium: 'blue',
      high: 'yellow',
      urgent: 'red'
    }
    return colors[priority as keyof typeof colors] || 'gray'
  },

  formatCurrency(amount: number): string {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount)
  },

  formatDate(date: string): string {
    return new Date(date).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  },

  formatProgress(percentage: number): string {
    return `${Math.round(percentage)}%`
  }
}