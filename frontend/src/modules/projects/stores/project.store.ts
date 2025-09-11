import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { projectService } from '../services/project.service'
import type { 
  Project, 
  ProjectListResponse, 
  ProjectFilters, 
  CreateProjectData, 
  UpdateProjectData, 
  ProjectStatistics
} from '../types/project.types'

export const useProjectStore = defineStore('projects', () => {
  // State
  const projects = ref<Project[]>([])
  const currentProject = ref<Project | null>(null)
  const statistics = ref<ProjectStatistics | null>(null)
  const isLoading = ref(false)
  const isCreating = ref(false)
  const isUpdating = ref(false)
  const isDeleting = ref(false)
  const error = ref<string | null>(null)
  const lastFilters = ref<ProjectFilters>({})
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 50,
    total: 0
  })

  // Computed
  const projectsByStatus = computed(() => {
    const grouped: Record<string, Project[]> = {}
    projects.value.forEach(project => {
      const status = project.status.value
      if (!grouped[status]) {
        grouped[status] = []
      }
      grouped[status].push(project)
    })
    return grouped
  })

  const projectsByPriority = computed(() => {
    const grouped: Record<string, Project[]> = {}
    projects.value.forEach(project => {
      const priority = project.priority.value
      if (!grouped[priority]) {
        grouped[priority] = []
      }
      grouped[priority].push(project)
    })
    return grouped
  })

  const activeProjects = computed(() => 
    projects.value.filter(project => project.status.value === 'active')
  )

  const overdueProjects = computed(() => 
    projects.value.filter(project => project.is_overdue)
  )

  const completedProjects = computed(() => 
    projects.value.filter(project => project.status.value === 'completed')
  )

  const totalBudget = computed(() => 
    projects.value.reduce((sum, project) => sum + project.planned_budget, 0)
  )

  const totalActualBudget = computed(() => 
    projects.value.reduce((sum, project) => sum + project.actual_budget, 0)
  )

  const averageProgress = computed(() => {
    if (projects.value.length === 0) return 0
    const totalProgress = projects.value.reduce((sum, project) => sum + project.progress_percentage, 0)
    return Math.round(totalProgress / projects.value.length)
  })

  // Actions
  const loadProjects = async (filters: ProjectFilters = {}): Promise<void> => {
    try {
      isLoading.value = true
      error.value = null
      lastFilters.value = filters

      const response = await projectService.getProjects(filters)
      projects.value = response.data
      pagination.value = {
        current_page: response.meta.current_page,
        last_page: response.meta.last_page,
        per_page: response.meta.per_page,
        total: response.meta.total
      }
    } catch (err: any) {
      error.value = err.message || 'Failed to load projects'
      console.error('Load projects error:', err)
    } finally {
      isLoading.value = false
    }
  }

  const refreshProjects = async (): Promise<void> => {
    await loadProjects(lastFilters.value)
  }

  const loadProject = async (id: string): Promise<void> => {
    try {
      isLoading.value = true
      error.value = null
      currentProject.value = await projectService.getProject(id)
    } catch (err: any) {
      error.value = err.message || 'Failed to load project'
      console.error('Load project error:', err)
    } finally {
      isLoading.value = false
    }
  }

  const createProject = async (data: CreateProjectData): Promise<Project | null> => {
    try {
      isCreating.value = true
      error.value = null
      
      const newProject = await projectService.createProject(data)
      
      // Add to projects list if it matches current filters
      projects.value.unshift(newProject)
      pagination.value.total += 1
      
      return newProject
    } catch (err: any) {
      error.value = err.message || 'Failed to create project'
      console.error('Create project error:', err)
      return null
    } finally {
      isCreating.value = false
    }
  }

  const updateProject = async (id: string, data: UpdateProjectData): Promise<Project | null> => {
    try {
      isUpdating.value = true
      error.value = null
      
      const updatedProject = await projectService.updateProject(id, data)
      
      // Update in projects list
      const index = projects.value.findIndex(project => project.id === id)
      if (index !== -1) {
        projects.value[index] = updatedProject
      }
      
      // Update current project if it's the same
      if (currentProject.value?.id === id) {
        currentProject.value = updatedProject
      }
      
      return updatedProject
    } catch (err: any) {
      error.value = err.message || 'Failed to update project'
      console.error('Update project error:', err)
      return null
    } finally {
      isUpdating.value = false
    }
  }

  const deleteProject = async (id: string): Promise<boolean> => {
    try {
      isDeleting.value = true
      error.value = null
      
      await projectService.deleteProject(id)
      
      // Remove from projects list
      const index = projects.value.findIndex(project => project.id === id)
      if (index !== -1) {
        projects.value.splice(index, 1)
        pagination.value.total -= 1
      }
      
      // Clear current project if it's the same
      if (currentProject.value?.id === id) {
        currentProject.value = null
      }
      
      return true
    } catch (err: any) {
      error.value = err.message || 'Failed to delete project'
      console.error('Delete project error:', err)
      return false
    } finally {
      isDeleting.value = false
    }
  }

  const loadStatistics = async (): Promise<void> => {
    try {
      isLoading.value = true
      error.value = null
      statistics.value = await projectService.getProjectStatistics()
    } catch (err: any) {
      error.value = err.message || 'Failed to load project statistics'
      console.error('Load statistics error:', err)
    } finally {
      isLoading.value = false
    }
  }

  const searchProjects = async (query: string): Promise<Project[]> => {
    try {
      const response = await projectService.getProjects({ search: query })
      return response.data
    } catch (err: any) {
      error.value = err.message || 'Failed to search projects'
      console.error('Search projects error:', err)
      return []
    }
  }

  const clearError = (): void => {
    error.value = null
  }

  const clearCurrentProject = (): void => {
    currentProject.value = null
  }

  const clearProjects = (): void => {
    projects.value = []
    pagination.value = {
      current_page: 1,
      last_page: 1,
      per_page: 50,
      total: 0
    }
  }

  // Getters
  const getProjectById = (id: string): Project | undefined => {
    return projects.value.find(project => project.id === id)
  }

  const getProjectsByManager = (managerId: string): Project[] => {
    return projects.value.filter(project => project.project_manager?.id === managerId)
  }

  const getProjectsByStatus = (status: string): Project[] => {
    return projects.value.filter(project => project.status.value === status)
  }

  const getProjectsByClient = (clientId: string): Project[] => {
    return projects.value.filter(project => project.client_company?.id === clientId)
  }

  return {
    // State
    projects,
    currentProject,
    statistics,
    isLoading,
    isCreating,
    isUpdating,
    isDeleting,
    error,
    lastFilters,
    pagination,

    // Computed
    projectsByStatus,
    projectsByPriority,
    activeProjects,
    overdueProjects,
    completedProjects,
    totalBudget,
    totalActualBudget,
    averageProgress,

    // Actions
    loadProjects,
    refreshProjects,
    loadProject,
    createProject,
    updateProject,
    deleteProject,
    loadStatistics,
    searchProjects,
    clearError,
    clearCurrentProject,
    clearProjects,

    // Getters
    getProjectById,
    getProjectsByManager,
    getProjectsByStatus,
    getProjectsByClient
  }
})