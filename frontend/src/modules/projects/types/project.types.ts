// Project Management Types

export interface ProjectStatus {
  value: string
  label: string
  color: string
}

export interface ProjectPriority {
  value: string
  label: string
  color: string
  weight: number
}

export interface ProjectType {
  value: string
  label: string
  color: string
  category: string
}

export interface User {
  id: string
  name: string
  email: string
  role: string
  avatar?: string
}

export interface Company {
  id: string
  name: string
  email?: string
  phone?: string
}

export interface Project {
  id: string
  name: string
  description?: string
  status: ProjectStatus
  priority: ProjectPriority
  project_type: ProjectType
  client_company?: Company
  project_manager?: User
  start_date?: string
  end_date?: string
  planned_budget: number
  actual_budget: number
  progress_percentage: number
  address?: string
  coordinates?: {
    latitude: number
    longitude: number
  }
  metadata?: Record<string, unknown>
  
  // Computed fields
  is_overdue: boolean
  days_remaining?: number
  budget_variance: number
  budget_variance_percentage: number
  tasks_count?: number
  active_tasks_count?: number
  completed_tasks_count?: number
  team_members_count?: number
  
  created_at: string
  updated_at: string
}

export interface ProjectListResponse {
  data: Project[]
  meta: {
    current_page: number
    from: number
    last_page: number
    per_page: number
    to: number
    total: number
  }
  links: {
    first: string
    last: string
    prev?: string
    next?: string
  }
}

export interface CreateProjectData {
  name: string
  description?: string
  project_type?: string
  priority?: string
  status?: string
  client_company_id?: string
  project_manager_id?: string
  start_date?: string
  end_date?: string
  planned_budget?: number
  address?: string
  coordinates?: {
    latitude: number
    longitude: number
  }
  metadata?: Record<string, unknown>
}

export interface UpdateProjectData {
  name?: string
  description?: string
  project_type?: string
  priority?: string
  status?: string
  client_company_id?: string
  project_manager_id?: string
  start_date?: string
  end_date?: string
  planned_budget?: number
  actual_budget?: number
  progress_percentage?: number
  address?: string
  coordinates?: {
    latitude: number
    longitude: number
  }
  metadata?: Record<string, unknown>
}

export interface ProjectFilters {
  status?: string
  priority?: string
  project_type?: string
  client_company_id?: string
  project_manager_id?: string
  start_date_from?: string
  start_date_to?: string
  end_date_from?: string
  end_date_to?: string
  budget_min?: number
  budget_max?: number
  progress_min?: number
  progress_max?: number
  search?: string
  overdue?: boolean
  sort_by?: string
  sort_direction?: 'asc' | 'desc'
  per_page?: number
}

export interface ProjectStatistics {
  total_projects: number
  by_status: Record<string, number>
  by_priority: Record<string, number>
  by_type: Record<string, number>
  overdue_projects: number
  total_budget: number
  total_actual_budget: number
  avg_progress: number
  completion_rate: number
  active_projects: number
  completed_projects: number
}