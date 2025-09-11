<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Projects</h1>
        <p class="mt-1 text-sm text-gray-600">Manage construction projects and track progress</p>
      </div>
      <VButton @click="router.push('/app/projects/create')">
        <Plus class="w-4 h-4 mr-2" />
        Create Project
      </VButton>
    </div>

    <!-- Filters -->
    <VCard>
      <div class="p-4 border-b border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Search -->
          <VValidatedField
            v-model="searchQuery"
            type="text"
            placeholder="Search projects..."
            class="col-span-full md:col-span-1"
            @input="handleSearch"
          >
            <template #prefix>
              <Search class="w-4 h-4 text-gray-400" />
            </template>
          </VValidatedField>

          <!-- Status Filter -->
          <VValidatedField
            v-model="filters.status"
            type="select"
            placeholder="All Statuses"
            :options="statusOptions"
            @change="applyFilters"
          />

          <!-- Priority Filter -->
          <VValidatedField
            v-model="filters.priority"
            type="select"
            placeholder="All Priorities"
            :options="priorityOptions"
            @change="applyFilters"
          />

          <!-- Project Type Filter -->
          <VValidatedField
            v-model="filters.project_type"
            type="select"
            placeholder="All Types"
            :options="typeOptions"
            @change="applyFilters"
          />
        </div>

        <!-- Advanced Filters -->
        <div v-if="showAdvancedFilters" class="mt-4 pt-4 border-t border-gray-200">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Date Range Filters -->
            <VValidatedField
              v-model="filters.start_date_from"
              type="date"
              label="Start Date From"
              @change="applyFilters"
            />
            <VValidatedField
              v-model="filters.start_date_to"
              type="date"
              label="Start Date To"
              @change="applyFilters"
            />
            <VValidatedField
              v-model="filters.overdue"
              type="checkbox"
              label="Show Overdue Only"
              @change="applyFilters"
            />
          </div>
        </div>

        <!-- Filter Actions -->
        <div class="mt-4 flex items-center justify-between">
          <button
            @click="showAdvancedFilters = !showAdvancedFilters"
            class="text-sm text-blue-600 hover:text-blue-700"
          >
            {{ showAdvancedFilters ? 'Hide' : 'Show' }} Advanced Filters
          </button>
          <VButton
            variant="outline"
            size="sm"
            @click="clearAllFilters"
          >
            Clear Filters
          </VButton>
        </div>
      </div>
    </VCard>

    <!-- Projects Data Table -->
    <VCard>
      <div class="overflow-hidden">
        <VDataTable
          :data="projects"
          :columns="columns"
          :loading="loading"
          :sort-by="sortBy"
          :sort-direction="sortDirection"
          @update:sort="handleSort"
        >
          <!-- Custom column templates -->
          <template #status="{ row }">
            <ProjectStatusBadge :status="row.status.value" :color="row.status.color" />
          </template>

          <template #priority="{ row }">
            <ProjectPriorityBadge :priority="row.priority.value" :color="row.priority.color" />
          </template>

          <template #progress="{ row }">
            <ProjectProgress :percentage="row.progress_percentage" />
          </template>

          <template #budget="{ row }">
            <div class="text-right">
              <div class="font-medium">{{ formatCurrency(row.actual_budget) }}</div>
              <div class="text-sm text-gray-500">of {{ formatCurrency(row.planned_budget || 0) }}</div>
              <div v-if="row.is_over_budget" class="text-xs text-red-600">
                Over budget by {{ formatCurrency(row.budget_variance) }}
              </div>
            </div>
          </template>

          <template #dates="{ row }">
            <div class="text-sm">
              <div v-if="row.start_date">Start: {{ formatDate(row.start_date) }}</div>
              <div v-if="row.end_date" :class="{ 'text-red-600': row.is_overdue }">
                End: {{ formatDate(row.end_date) }}
              </div>
              <div v-if="row.is_overdue" class="text-xs text-red-600 font-medium">
                OVERDUE
              </div>
            </div>
          </template>

          <template #tasks="{ row }">
            <div class="text-sm">
              <div class="font-medium">{{ row.completed_tasks_count }}/{{ row.tasks_count || 0 }}</div>
              <div class="text-gray-500">tasks completed</div>
            </div>
          </template>

          <template #actions="{ row }">
            <div class="flex items-center space-x-2">
              <VButton
                size="sm"
                variant="outline"
                @click="viewProject(row.id)"
                :title="`View ${row.name}`"
              >
                <Eye class="w-4 h-4" />
              </VButton>
              <VButton
                v-if="row.can_be_edited"
                size="sm"
                variant="outline"
                @click="editProject(row.id)"
                :title="`Edit ${row.name}`"
              >
                <Edit class="w-4 h-4" />
              </VButton>
              <VButton
                v-if="row.can_be_deleted"
                size="sm"
                variant="outline"
                @click="confirmDelete(row)"
                :title="`Delete ${row.name}`"
                class="text-red-600 hover:text-red-700"
              >
                <Trash2 class="w-4 h-4" />
              </VButton>
            </div>
          </template>
        </VDataTable>
      </div>

      <!-- Pagination -->
      <div class="px-4 py-3 border-t border-gray-200">
        <VPagination
          :current-page="pagination.current_page"
          :total-pages="pagination.last_page"
          :per-page="pagination.per_page"
          :total="pagination.total"
          @page-change="goToPage"
          @per-page-change="handlePerPageChange"
        />
      </div>
    </VCard>

    <!-- Delete Confirmation Modal -->
    <VModal
      v-model="showDeleteModal"
      title="Delete Project"
      :loading="loading"
    >
      <p class="text-gray-600">
        Are you sure you want to delete <strong>{{ projectToDelete?.name }}</strong>?
        This action cannot be undone.
      </p>
      
      <template #actions>
        <VButton variant="outline" @click="showDeleteModal = false">
          Cancel
        </VButton>
        <VButton
          variant="danger"
          @click="handleDelete"
          :loading="loading"
        >
          Delete Project
        </VButton>
      </template>
    </VModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { VCard, VButton, VValidatedField, VDataTable, VPagination, VModal } from '@/components/ui'
import { Plus, Search, Eye, Edit, Trash2 } from 'lucide-vue-next'
import { useProjects } from '../composables/useProjects'
import { projectsApi } from '../services/projectsApi'
import ProjectStatusBadge from '../components/ProjectStatusBadge.vue'
import ProjectPriorityBadge from '../components/ProjectPriorityBadge.vue'
import ProjectProgress from '../components/ProjectProgress.vue'
import type { ProjectListItem } from '../types/projects.types'

const router = useRouter()

// Composables
const {
  projects,
  loading,
  pagination,
  filters,
  sortBy,
  sortDirection,
  fetchProjects,
  deleteProject,
  applyFilters,
  clearFilters,
  setSorting,
  goToPage
} = useProjects()

// Local state
const searchQuery = ref('')
const showAdvancedFilters = ref(false)
const showDeleteModal = ref(false)
const projectToDelete = ref<ProjectListItem | null>(null)

// Table columns (50-row pagination as per project requirements)
const columns = [
  { key: 'name', label: 'Project Name', sortable: true, searchable: true },
  { key: 'client.name', label: 'Client', sortable: true },
  { key: 'manager.name', label: 'Manager', sortable: true },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'priority', label: 'Priority', sortable: true },
  { key: 'progress', label: 'Progress', sortable: false },
  { key: 'budget', label: 'Budget', sortable: true },
  { key: 'dates', label: 'Timeline', sortable: false },
  { key: 'tasks', label: 'Tasks', sortable: false },
  { key: 'actions', label: 'Actions', sortable: false, width: '120px' }
]

// Filter options
const statusOptions = [
  { value: 'draft', label: 'Draft' },
  { value: 'active', label: 'Active' },
  { value: 'on_hold', label: 'On Hold' },
  { value: 'completed', label: 'Completed' },
  { value: 'cancelled', label: 'Cancelled' }
]

const priorityOptions = [
  { value: 'low', label: 'Low' },
  { value: 'medium', label: 'Medium' },
  { value: 'high', label: 'High' },
  { value: 'urgent', label: 'Urgent' }
]

const typeOptions = [
  { value: 'construction', label: 'Construction' },
  { value: 'renovation', label: 'Renovation' },
  { value: 'maintenance', label: 'Maintenance' },
  { value: 'inspection', label: 'Inspection' },
  { value: 'consulting', label: 'Consulting' }
]

// Methods
const handleSearch = (() => {
  let timeout: NodeJS.Timeout | null = null
  return () => {
    if (timeout) clearTimeout(timeout)
    timeout = setTimeout(() => {
      filters.search = searchQuery.value
      applyFilters({ search: searchQuery.value })
    }, 300) // Debounce search
  }
})()

const handleSort = (column: string, direction: 'asc' | 'desc') => {
  setSorting(column, direction)
}

const handlePerPageChange = (perPage: number) => {
  pagination.per_page = perPage
  pagination.current_page = 1
  fetchProjects()
}

const clearAllFilters = () => {
  searchQuery.value = ''
  clearFilters()
}

const viewProject = (id: string) => {
  router.push(`/app/projects/${id}`)
}

const editProject = (id: string) => {
  router.push(`/app/projects/${id}/edit`)
}

const confirmDelete = (project: ProjectListItem) => {
  projectToDelete.value = project
  showDeleteModal.value = true
}

const handleDelete = async () => {
  if (projectToDelete.value) {
    const success = await deleteProject(projectToDelete.value.id)
    if (success) {
      showDeleteModal.value = false
      projectToDelete.value = null
    }
  }
}

const formatCurrency = (amount: number) => {
  return projectsApi.formatCurrency(amount)
}

const formatDate = (date: string) => {
  return projectsApi.formatDate(date)
}

// Initialize component
onMounted(() => {
  fetchProjects()
})
</script>