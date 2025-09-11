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
      console.warn('Project API unavailable, using mock data:', error)
      return this.getMockProjectList(filters)
    }
  }

  async getProject(id: string): Promise<Project> {
    try {
      const response = await apiClient.get<{ data: Project }>(`${this.baseUrl}/${id}`)
      return response.data
    } catch (error) {
      console.warn('Project API unavailable, using mock data:', error)
      return this.getMockProject(id)
    }
  }

  async createProject(data: CreateProjectData): Promise<Project> {
    try {
      const response = await apiClient.post<{ data: Project }>(this.baseUrl, data)
      return response.data
    } catch (error) {
      console.warn('Project API unavailable, simulating create:', error)
      return this.getMockProject('new-project')
    }
  }

  async updateProject(id: string, data: UpdateProjectData): Promise<Project> {
    try {
      const response = await apiClient.put<{ data: Project }>(`${this.baseUrl}/${id}`, data)
      return response.data
    } catch (error) {
      console.warn('Project API unavailable, simulating update:', error)
      return this.getMockProject(id)
    }
  }

  async deleteProject(id: string): Promise<void> {
    try {
      await apiClient.delete(`${this.baseUrl}/${id}`)
    } catch (error) {
      console.warn('Project API unavailable, simulating delete:', error)
    }
  }

  async getProjectStatistics(): Promise<ProjectStatistics> {
    try {
      const response = await apiClient.get<{ data: ProjectStatistics }>(`${this.baseUrl}/statistics`)
      return response.data
    } catch (error) {
      console.warn('Project API unavailable, using mock data:', error)
      return this.getMockStatistics()
    }
  }

  // Mock data methods for offline development
  private getMockProjectList(filters: ProjectFilters = {}): ProjectListResponse {
    const mockProjects: Project[] = [
      {
        id: 'proj-1',
        name: 'Downtown Office Building',
        description: 'Modern 15-story office complex with retail space on ground floor',
        status: { value: 'active', label: 'Active', color: 'blue' },
        priority: { value: 'high', label: 'High', color: 'orange', weight: 3 },
        project_type: { value: 'commercial', label: 'Commercial', color: 'blue', category: 'building' },
        client_company: { id: 'company-1', name: 'Downtown Development Corp', email: 'contact@downtown-dev.com' },
        project_manager: { id: 'user-2', name: 'Jane Manager', email: 'jane@example.com', role: 'project_manager' },
        start_date: '2025-08-01',
        end_date: '2026-06-30',
        planned_budget: 2500000,
        actual_budget: 1850000,
        progress_percentage: 45,
        address: '123 Main Street, Downtown, City',
        coordinates: { latitude: 40.7589, longitude: -73.9851 },
        is_overdue: false,
        days_remaining: 265,
        budget_variance: -650000,
        budget_variance_percentage: -26,
        tasks_count: 48,
        active_tasks_count: 12,
        completed_tasks_count: 20,
        team_members_count: 8,
        created_at: '2025-08-01T08:00:00Z',
        updated_at: '2025-09-11T14:30:00Z'
      },
      {
        id: 'proj-2',
        name: 'Residential Complex Phase 1',
        description: '120-unit residential complex with amenities and parking',
        status: { value: 'active', label: 'Active', color: 'blue' },
        priority: { value: 'medium', label: 'Medium', color: 'yellow', weight: 2 },
        project_type: { value: 'residential', label: 'Residential', color: 'green', category: 'building' },
        client_company: { id: 'company-2', name: 'Riverside Living LLC', email: 'info@riverside-living.com' },
        project_manager: { id: 'user-2', name: 'Jane Manager', email: 'jane@example.com', role: 'project_manager' },
        start_date: '2025-07-15',
        end_date: '2026-12-15',
        planned_budget: 4200000,
        actual_budget: 2100000,
        progress_percentage: 30,
        address: '456 Riverside Drive, Suburb, City',
        coordinates: { latitude: 40.7505, longitude: -73.9934 },
        is_overdue: false,
        days_remaining: 460,
        budget_variance: -2100000,
        budget_variance_percentage: -50,
        tasks_count: 65,
        active_tasks_count: 18,
        completed_tasks_count: 15,
        team_members_count: 12,
        created_at: '2025-07-15T08:00:00Z',
        updated_at: '2025-09-11T14:30:00Z'
      },
      {
        id: 'proj-3',
        name: 'Shopping Center Renovation',
        description: 'Complete renovation of existing shopping center including new facades and interior',
        status: { value: 'planning', label: 'Planning', color: 'gray' },
        priority: { value: 'low', label: 'Low', color: 'green', weight: 1 },
        project_type: { value: 'renovation', label: 'Renovation', color: 'purple', category: 'retrofit' },
        client_company: { id: 'company-3', name: 'Metro Shopping Centers', email: 'projects@metro-shopping.com' },
        project_manager: { id: 'user-2', name: 'Jane Manager', email: 'jane@example.com', role: 'project_manager' },
        start_date: '2025-11-01',
        end_date: '2026-08-30',
        planned_budget: 1800000,
        actual_budget: 0,
        progress_percentage: 5,
        address: '789 Shopping Plaza, Mall District, City',
        coordinates: { latitude: 40.7614, longitude: -73.9776 },
        is_overdue: false,
        days_remaining: 51,
        budget_variance: -1800000,
        budget_variance_percentage: -100,
        tasks_count: 32,
        active_tasks_count: 2,
        completed_tasks_count: 1,
        team_members_count: 4,
        created_at: '2025-09-01T08:00:00Z',
        updated_at: '2025-09-11T14:30:00Z'
      },
      {
        id: 'proj-4',
        name: 'Historic Building Restoration',
        description: 'Restoration of 1920s historic building maintaining original architecture',
        status: { value: 'completed', label: 'Completed', color: 'green' },
        priority: { value: 'critical', label: 'Critical', color: 'red', weight: 4 },
        project_type: { value: 'restoration', label: 'Restoration', color: 'orange', category: 'heritage' },
        client_company: { id: 'company-4', name: 'Heritage Foundation', email: 'restoration@heritage.org' },
        project_manager: { id: 'user-2', name: 'Jane Manager', email: 'jane@example.com', role: 'project_manager' },
        start_date: '2024-03-01',
        end_date: '2025-08-31',
        planned_budget: 950000,
        actual_budget: 1020000,
        progress_percentage: 100,
        address: '321 Heritage Street, Historic District, City',
        coordinates: { latitude: 40.7505, longitude: -73.9857 },
        is_overdue: false,
        days_remaining: 0,
        budget_variance: 70000,
        budget_variance_percentage: 7.4,
        tasks_count: 28,
        active_tasks_count: 0,
        completed_tasks_count: 28,
        team_members_count: 6,
        created_at: '2024-03-01T08:00:00Z',
        updated_at: '2025-08-31T16:00:00Z'
      }
    ]

    // Apply filters to mock data
    let filteredProjects = mockProjects

    if (filters.status) {
      filteredProjects = filteredProjects.filter(project => project.status.value === filters.status)
    }
    if (filters.priority) {
      filteredProjects = filteredProjects.filter(project => project.priority.value === filters.priority)
    }
    if (filters.project_type) {
      filteredProjects = filteredProjects.filter(project => project.project_type.value === filters.project_type)
    }
    if (filters.project_manager_id) {
      filteredProjects = filteredProjects.filter(project => project.project_manager?.id === filters.project_manager_id)
    }
    if (filters.search) {
      const query = filters.search.toLowerCase()
      filteredProjects = filteredProjects.filter(project => 
        project.name.toLowerCase().includes(query) || 
        project.description?.toLowerCase().includes(query)
      )
    }
    if (filters.overdue) {
      filteredProjects = filteredProjects.filter(project => project.is_overdue)
    }

    return {
      data: filteredProjects,
      meta: {
        current_page: 1,
        from: 1,
        last_page: 1,
        per_page: filters.per_page || 50,
        to: filteredProjects.length,
        total: filteredProjects.length
      },
      links: {
        first: '/projects?page=1',
        last: '/projects?page=1'
      }
    }
  }

  private getMockProject(id: string): Project {
    return {
      id,
      name: 'Sample Project',
      description: 'This is a sample project for demonstration',
      status: { value: 'active', label: 'Active', color: 'blue' },
      priority: { value: 'medium', label: 'Medium', color: 'yellow', weight: 2 },
      project_type: { value: 'commercial', label: 'Commercial', color: 'blue', category: 'building' },
      planned_budget: 1000000,
      actual_budget: 500000,
      progress_percentage: 25,
      is_overdue: false,
      budget_variance: -500000,
      budget_variance_percentage: -50,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    }
  }

  private getMockStatistics(): ProjectStatistics {
    return {
      total_projects: 15,
      by_status: {
        'planning': 2,
        'active': 8,
        'on_hold': 1,
        'completed': 3,
        'cancelled': 1
      },
      by_priority: {
        'low': 3,
        'medium': 7,
        'high': 4,
        'critical': 1
      },
      by_type: {
        'commercial': 6,
        'residential': 4,
        'renovation': 3,
        'restoration': 2
      },
      overdue_projects: 2,
      total_budget: 12500000,
      total_actual_budget: 8750000,
      avg_progress: 62,
      completion_rate: 78.5,
      active_projects: 8,
      completed_projects: 3
    }
  }
}

export const projectService = new ProjectService()