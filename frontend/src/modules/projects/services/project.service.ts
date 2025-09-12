import { apiClient } from '@/modules/shared/api/client'
import type {
  Project,
  ProjectListResponse,
  ProjectFilters,
  CreateProjectData,
  UpdateProjectData,
  ProjectStatistics
} from '../types/project.types'

class ProjectService {
  private baseUrl = '/projects'

  async getProjects(filters: ProjectFilters = {}): Promise<ProjectListResponse> {
    try {
      return await apiClient.get<ProjectListResponse>(this.baseUrl, filters)
    } catch (error) {
      console.error('Project API error:', error)
      throw error
    }
  }

  async getProject(id: string): Promise<Project> {
    try {
      const response = await apiClient.get<{ data: Project }>(`${this.baseUrl}/${id}`)
      return response.data
    } catch (error) {
      console.error('Project API error:', error)
      throw error
    }
  }

  async createProject(data: CreateProjectData): Promise<Project> {
    try {
      const response = await apiClient.post<{ data: Project }>(this.baseUrl, data)
      return response.data
    } catch (error) {
      console.error('Project API error:', error)
      throw error
    }
  }

  async updateProject(id: string, data: UpdateProjectData): Promise<Project> {
    try {
      const response = await apiClient.put<{ data: Project }>(`${this.baseUrl}/${id}`, data)
      return response.data
    } catch (error) {
      console.error('Project API error:', error)
      throw error
    }
  }

  async deleteProject(id: string): Promise<void> {
    try {
      await apiClient.delete(`${this.baseUrl}/${id}`)
    } catch (error) {
      console.error('Project API error:', error)
      throw error
    }
  }

  async getProjectStatistics(): Promise<ProjectStatistics> {
    try {
      const response = await apiClient.get<{ data: ProjectStatistics }>(`${this.baseUrl}/statistics`)
      return response.data
    } catch (error) {
      console.error('Project API error:', error)
      throw error
    }
  }

}

export const projectService = new ProjectService()