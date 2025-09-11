export type ProjectStatus = 'draft' | 'active' | 'on_hold' | 'completed' | 'cancelled'

export type ProjectPriority = 'low' | 'medium' | 'high' | 'urgent'

export type ProjectType = 'construction' | 'renovation' | 'maintenance' | 'inspection' | 'consulting'

export interface ProjectStatusOption {
  value: ProjectStatus
  label: string
  color: string
}

export interface ProjectPriorityOption {
  value: ProjectPriority
  label: string
  color: string
  weight: number
}

export interface ProjectTypeOption {
  value: ProjectType
  label: string
  description: string
}

export interface Company {
  id: string
  name: string
}

export interface User {
  id: string
  name: string
  email: string
}

export interface Project {
  id: string
  name: string
  description?: string
  status: {
    value: ProjectStatus
    label: string
    color: string
  }
  priority: {
    value: ProjectPriority
    label: string
    color: string
    weight: number
  }
  project_type: {
    value: ProjectType
    label: string
    description: string
  }
  client: Company
  manager: User
  start_date?: string
  end_date?: string
  planned_budget?: number
  actual_budget: number
  progress_percentage: number
  address?: string
  coordinates?: {
    lat: number
    lng: number
  }
  metadata?: Record<string, any>
  
  // Computed fields
  duration_days?: number
  budget_variance: number
  budget_variance_percentage: number
  time_elapsed_percentage: number
  is_overdue: boolean
  is_over_budget: boolean
  can_be_edited: boolean
  can_be_deleted: boolean
  
  // Counts
  phases_count?: number
  tasks_count?: number
  milestones_count?: number
  
  created_at: string
  updated_at: string
}

export interface ProjectListItem extends Omit<Project, 'client' | 'manager'> {
  client: {
    id: string
    name: string
  }
  manager: {
    id: string
    name: string
    email: string
  }
  completed_tasks_count: number
}

export interface CreateProjectRequest {
  name: string
  description?: string
  client_company_id: string
  project_manager_id: string
  project_type: ProjectType
  priority: ProjectPriority
  start_date?: string
  end_date?: string
  planned_budget?: number
  address?: string
  coordinates?: {
    lat: number
    lng: number
  }
  metadata?: Record<string, any>
}

export interface UpdateProjectRequest {
  name?: string
  description?: string
  client_company_id?: string
  project_manager_id?: string
  project_type?: ProjectType
  priority?: ProjectPriority
  status?: ProjectStatus
  start_date?: string
  end_date?: string
  planned_budget?: number
  actual_budget?: number
  progress_percentage?: number
  address?: string
  coordinates?: {
    lat: number
    lng: number
  }
  metadata?: Record<string, any>
}

export interface ProjectFilters {
  status?: ProjectStatus
  priority?: ProjectPriority
  project_type?: ProjectType
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
}

export interface ProjectsResponse {
  data: ProjectListItem[]
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

export interface ProjectStatistics {
  total_projects: number
  active_projects: number
  completed_projects: number
  overdue_projects: number
  on_hold_projects: number
  total_budget: number
  spent_budget: number
  average_progress: number
  completion_rate: number
  budget_utilization: number
  monthly_completion_trend: Array<{
    month: string
    completed: number
  }>
  budget_performance_trend: Array<{
    month: string
    planned_budget: number
    actual_budget: number
    variance_percentage: number
  }>
  priority_distribution: {
    low: number
    medium: number
    high: number
    urgent: number
  }
  type_distribution: {
    construction: number
    renovation: number
    maintenance: number
    inspection: number
    consulting: number
  }
}

// Column definitions for data table
export interface ProjectTableColumn {
  key: string
  label: string
  sortable?: boolean
  searchable?: boolean
  filterable?: boolean
  type?: 'text' | 'date' | 'number' | 'currency' | 'percentage' | 'status' | 'custom'
  width?: string
  align?: 'left' | 'center' | 'right'
}

// Form validation schema
export interface ProjectFormErrors {
  name?: string[]
  description?: string[]
  client_company_id?: string[]
  project_manager_id?: string[]
  project_type?: string[]
  priority?: string[]
  start_date?: string[]
  end_date?: string[]
  planned_budget?: string[]
  address?: string[]
  coordinates?: string[]
  metadata?: string[]
}