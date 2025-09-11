import { ref, computed, reactive } from 'vue'
import { projectsApi } from '../services/projectsApi'
import type {
  Project,
  ProjectListItem,
  CreateProjectRequest,
  UpdateProjectRequest,
  ProjectFilters,
  ProjectsResponse
} from '../types/projects.types'

export function useProjects() {
  // State
  const projects = ref<ProjectListItem[]>([])
  const currentProject = ref<Project | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  
  // Pagination state
  const pagination = reactive({
    current_page: 1,
    per_page: 50,
    total: 0,
    last_page: 1,
    from: 0,
    to: 0
  })

  // Filters state
  const filters = reactive<ProjectFilters>({
    status: undefined,
    priority: undefined,
    project_type: undefined,
    client_company_id: undefined,
    project_manager_id: undefined,
    search: undefined,
    overdue: undefined
  })

  // Sorting state
  const sortBy = ref('created_at')
  const sortDirection = ref<'asc' | 'desc'>('desc')

  // Computed
  const hasProjects = computed(() => projects.value.length > 0)
  const isEmpty = computed(() => !loading.value && projects.value.length === 0)
  const hasNextPage = computed(() => pagination.current_page < pagination.last_page)
  const hasPreviousPage = computed(() => pagination.current_page > 1)

  // Methods
  const fetchProjects = async () => {
    loading.value = true
    error.value = null

    try {
      const response: ProjectsResponse = await projectsApi.getProjects({
        ...filters,
        page: pagination.current_page,
        per_page: pagination.per_page,
        sort_by: sortBy.value,
        sort_direction: sortDirection.value
      })

      projects.value = response.data
      
      // Update pagination
      Object.assign(pagination, response.meta)
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch projects'
      console.error('Error fetching projects:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchProject = async (id: string) => {
    loading.value = true
    error.value = null

    try {
      currentProject.value = await projectsApi.getProject(id)
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch project'
      console.error('Error fetching project:', err)
    } finally {
      loading.value = false
    }
  }

  const createProject = async (data: CreateProjectRequest): Promise<Project | null> => {
    loading.value = true
    error.value = null

    try {
      const project = await projectsApi.createProject(data)
      // Refresh the projects list
      await fetchProjects()
      return project
    } catch (err: any) {
      error.value = err.message || 'Failed to create project'
      console.error('Error creating project:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const updateProject = async (id: string, data: UpdateProjectRequest): Promise<Project | null> => {
    loading.value = true
    error.value = null

    try {
      const project = await projectsApi.updateProject(id, data)
      
      // Update current project if it's the same
      if (currentProject.value?.id === id) {
        currentProject.value = project
      }
      
      // Refresh the projects list
      await fetchProjects()
      return project
    } catch (err: any) {
      error.value = err.message || 'Failed to update project'
      console.error('Error updating project:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const deleteProject = async (id: string): Promise<boolean> => {
    loading.value = true
    error.value = null

    try {
      await projectsApi.deleteProject(id)
      
      // Remove from local list
      projects.value = projects.value.filter(project => project.id !== id)
      
      // Clear current project if it's the same
      if (currentProject.value?.id === id) {
        currentProject.value = null
      }
      
      return true
    } catch (err: any) {
      error.value = err.message || 'Failed to delete project'
      console.error('Error deleting project:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  const updateProjectStatus = async (id: string, status: string): Promise<boolean> => {
    loading.value = true
    error.value = null

    try {
      const project = await projectsApi.updateProjectStatus(id, status)
      
      // Update current project if it's the same
      if (currentProject.value?.id === id) {
        currentProject.value = project
      }
      
      // Update in local list
      const index = projects.value.findIndex(p => p.id === id)
      if (index !== -1) {
        projects.value[index] = { ...projects.value[index], status: project.status }
      }
      
      return true
    } catch (err: any) {
      error.value = err.message || 'Failed to update project status'
      console.error('Error updating project status:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  const searchProjects = async (query: string) => {
    if (!query.trim()) {
      return await fetchProjects()
    }

    loading.value = true
    error.value = null

    try {
      const results = await projectsApi.searchProjects(query)
      projects.value = results
      
      // Reset pagination for search results
      Object.assign(pagination, {
        current_page: 1,
        per_page: results.length,
        total: results.length,
        last_page: 1,
        from: 1,
        to: results.length
      })
    } catch (err: any) {
      error.value = err.message || 'Failed to search projects'
      console.error('Error searching projects:', err)
    } finally {
      loading.value = false
    }
  }

  const applyFilters = async (newFilters: Partial<ProjectFilters>) => {
    Object.assign(filters, newFilters)
    pagination.current_page = 1 // Reset to first page
    await fetchProjects()
  }

  const clearFilters = async () => {
    Object.keys(filters).forEach(key => {
      filters[key as keyof ProjectFilters] = undefined
    })
    pagination.current_page = 1
    await fetchProjects()
  }

  const setSorting = async (column: string, direction: 'asc' | 'desc') => {
    sortBy.value = column
    sortDirection.value = direction
    pagination.current_page = 1 // Reset to first page
    await fetchProjects()
  }

  const goToPage = async (page: number) => {
    if (page >= 1 && page <= pagination.last_page) {
      pagination.current_page = page
      await fetchProjects()
    }
  }

  const nextPage = async () => {
    if (hasNextPage.value) {
      await goToPage(pagination.current_page + 1)
    }
  }

  const previousPage = async () => {
    if (hasPreviousPage.value) {
      await goToPage(pagination.current_page - 1)
    }
  }

  const refreshProjects = async () => {
    await fetchProjects()
  }

  return {
    // State
    projects,
    currentProject,
    loading,
    error,
    pagination,
    filters,
    sortBy,
    sortDirection,

    // Computed
    hasProjects,
    isEmpty,
    hasNextPage,
    hasPreviousPage,

    // Methods
    fetchProjects,
    fetchProject,
    createProject,
    updateProject,
    deleteProject,
    updateProjectStatus,
    searchProjects,
    applyFilters,
    clearFilters,
    setSorting,
    goToPage,
    nextPage,
    previousPage,
    refreshProjects
  }
}