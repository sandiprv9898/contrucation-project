// Task Management Types

export interface TaskStatus {
  value: string
  label: string
  color: string
}

export interface TaskPriority {
  value: string
  label: string
  color: string
  weight: number
}

export interface TaskType {
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

export interface Project {
  id: string
  name: string
  status: string
}

export interface ProjectPhase {
  id: string
  name: string
}

export interface Task {
  id: string
  name: string
  description?: string
  status: TaskStatus
  priority: TaskPriority
  task_type: TaskType
  project?: Project
  phase?: ProjectPhase
  parent_task?: {
    id: string
    name: string
  }
  assigned_to?: User
  created_by?: User
  estimated_hours?: number
  actual_hours?: number
  progress_percentage: number
  start_date?: string
  due_date?: string
  completed_at?: string
  task_order: number
  
  // Computed fields
  is_overdue: boolean
  is_due_soon: boolean
  is_top_level: boolean
  level: number
  sub_tasks_count?: number
  comments_count?: number
  dependencies_count?: number
  
  created_at: string
  updated_at: string
}

export interface TaskListResponse {
  data: Task[]
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

export interface TaskDependency {
  id: string
  dependency_type: {
    value: string
    label: string
    description: string
  }
  lag_days: number
  task?: {
    id: string
    name: string
    status: string
  }
  prerequisite_task?: {
    id: string
    name: string
    status: string
  }
  is_blocking: boolean
  earliest_start_date?: string
  created_at: string
  updated_at: string
}

export interface TaskComment {
  id: string
  comment: string
  is_internal: boolean
  user?: User
  task?: {
    id: string
    name: string
  }
  time_ago: string
  is_recent: boolean
  can_edit: boolean
  can_delete: boolean
  created_at: string
  updated_at: string
}

export interface CreateTaskData {
  name: string
  description?: string
  project_id: string
  phase_id?: string
  parent_task_id?: string
  task_type?: string
  priority?: string
  status?: string
  assigned_to_id?: string
  estimated_hours?: number
  start_date?: string
  due_date?: string
  task_order?: number
  metadata?: Record<string, unknown>
}

export interface UpdateTaskData {
  name?: string
  description?: string
  phase_id?: string
  parent_task_id?: string
  task_type?: string
  priority?: string
  status?: string
  assigned_to_id?: string
  estimated_hours?: number
  actual_hours?: number
  progress_percentage?: number
  start_date?: string
  due_date?: string
  task_order?: number
  metadata?: Record<string, unknown>
}

export interface TaskFilters {
  status?: string
  priority?: string
  task_type?: string
  project_id?: string
  phase_id?: string
  assigned_to_id?: string
  created_by_id?: string
  parent_task_id?: string
  start_date_from?: string
  start_date_to?: string
  due_date_from?: string
  due_date_to?: string
  hours_min?: number
  hours_max?: number
  progress_min?: number
  progress_max?: number
  search?: string
  overdue?: boolean
  due_soon?: boolean
  due_soon_days?: number
  top_level_only?: boolean
  subtasks_only?: boolean
  sort_by?: string
  sort_direction?: 'asc' | 'desc'
  per_page?: number
}

export interface TaskStatistics {
  total_tasks: number
  by_status: Record<string, number>
  by_priority: Record<string, number>
  by_type: Record<string, number>
  overdue_tasks: number
  due_soon_tasks: number
  avg_completion_time: number
  total_estimated_hours: number
  total_actual_hours: number
  progress_percentage: number
}

export interface BulkUpdateData {
  status?: string
  priority?: string
  assigned_to_id?: string
}