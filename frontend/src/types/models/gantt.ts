export interface GanttTask {
  id: string
  name: string
  description?: string
  type: 'task' | 'milestone' | 'phase'
  status: 'pending' | 'in_progress' | 'completed' | 'on_hold' | 'cancelled'
  startDate: Date | string
  endDate: Date | string
  duration: number // in days
  progress: number // 0-100
  assignees?: string[]
  parentId?: string
  children?: GanttTask[]
  level?: number
  critical?: boolean
  color?: string
  projectId: string
  phaseId?: string
  dependencies?: string[] // task IDs this task depends on
  resources?: GanttResource[]
  estimatedHours?: number
  actualHours?: number
  cost?: number
  priority: 'low' | 'medium' | 'high' | 'critical'
  tags?: string[]
  customFields?: Record<string, any>
  createdAt: Date | string
  updatedAt: Date | string
}

export interface TaskDependency {
  id: string
  taskId: string
  dependsOnId: string
  type: 'finish_to_start' | 'start_to_start' | 'finish_to_finish' | 'start_to_finish'
  lag?: number // days of lag/lead time
  critical?: boolean
  name?: string
  dependencyCount?: number
  dependsOn?: string[]
}

export interface GanttResource {
  id: string
  name: string
  type: 'person' | 'equipment' | 'material'
  allocation: number // percentage
  cost?: number
  availability?: {
    startDate: Date | string
    endDate: Date | string
  }
}

export interface GanttView {
  scale: 'day' | 'week' | 'month' | 'quarter' | 'year'
  startDate: Date | string
  endDate: Date | string
  showWeekends: boolean
  showDependencies: boolean
  showCriticalPath: boolean
  showProgress: boolean
  showResources: boolean
  showMilestones: boolean
  groupBy?: 'phase' | 'assignee' | 'status' | 'priority'
  filters?: GanttFilters
}

export interface GanttFilters {
  status?: string[]
  assignees?: string[]
  phases?: string[]
  dateRange?: {
    start: Date | string
    end: Date | string
  }
  search?: string
  showCompleted?: boolean
  showOverdue?: boolean
  priority?: string[]
  tags?: string[]
}

export interface GanttExportOptions {
  format: 'pdf' | 'png' | 'excel' | 'mpp' // mpp for MS Project
  includeDetails: boolean
  includeDependencies: boolean
  includeResources: boolean
  dateRange?: {
    start: Date | string
    end: Date | string
  }
  paperSize?: 'A4' | 'A3' | 'letter' | 'legal'
  orientation?: 'portrait' | 'landscape'
}

export interface GanttChartProps {
  tasks: GanttTask[]
  dependencies?: TaskDependency[]
  view?: GanttView
  readonly?: boolean
  showToolbar?: boolean
  showLegend?: boolean
  onTaskUpdate?: (task: GanttTask) => void
  onTaskClick?: (task: GanttTask) => void
  onDependencyUpdate?: (dependency: TaskDependency) => void
  onExport?: (options: GanttExportOptions) => void
}

export interface TimelineRange {
  start: Date
  end: Date
  workingDays: number
  totalDays: number
}

export interface CriticalPath {
  tasks: string[] // task IDs in critical path
  totalDuration: number
  startDate: Date | string
  endDate: Date | string
  slack: number
}