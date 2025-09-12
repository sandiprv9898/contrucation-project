<template>
  <div class="project-gantt-page">
    <!-- Page Header -->
    <div class="page-header">
      <div class="page-header-content">
        <div class="page-title">
          <h1 class="text-3xl font-bold">Timeline & Dependencies</h1>
          <div class="breadcrumbs flex items-center gap-2 text-sm text-muted-foreground mt-1">
            <router-link to="/projects" class="hover:text-primary">Projects</router-link>
            <ChevronRight class="h-4 w-4" />
            <span>{{ project?.name }}</span>
            <ChevronRight class="h-4 w-4" />
            <span>Gantt Chart</span>
          </div>
        </div>
        <div class="page-actions flex items-center gap-3">
          <VDropdownMenu align="end">
            <template #trigger>
              <VButton variant="outline" size="sm">
                <Download class="h-4 w-4 mr-2" />
                Export
              </VButton>
            </template>
            <template #content>
              <VDropdownMenuItem @click="exportGantt('pdf')">
                <FileText class="h-4 w-4 mr-2" />
                Export as PDF
              </VDropdownMenuItem>
              <VDropdownMenuItem @click="exportGantt('excel')">
                <FileSpreadsheet class="h-4 w-4 mr-2" />
                Export as Excel
              </VDropdownMenuItem>
              <VDropdownMenuItem @click="exportGantt('png')">
                <Image class="h-4 w-4 mr-2" />
                Export as Image
              </VDropdownMenuItem>
              <hr class="my-1" />
              <VDropdownMenuItem @click="exportGantt('mpp')">
                <Package class="h-4 w-4 mr-2" />
                Export to MS Project
              </VDropdownMenuItem>
            </template>
          </VDropdownMenu>
          
          <VButton variant="outline" size="sm" @click="openImportDialog">
            <Upload class="h-4 w-4 mr-2" />
            Import
          </VButton>
          
          <VButton variant="outline" size="sm" @click="autoSchedule">
            <Wand2 class="h-4 w-4 mr-2" />
            Auto-Schedule
          </VButton>
          
          <VButton variant="default" size="sm" @click="openTaskDialog">
            <Plus class="h-4 w-4 mr-2" />
            Add Task
          </VButton>
        </div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="gantt-toolbar">
      <div class="toolbar-left flex items-center gap-3">
        <div class="view-controls flex items-center gap-2">
          <VButton 
            v-for="scale in ['day', 'week', 'month', 'quarter', 'year']" 
            :key="scale"
            @click="changeScale(scale)"
            :variant="currentView.scale === scale ? 'default' : 'outline'"
            size="sm"
          >
            {{ scale.charAt(0).toUpperCase() + scale.slice(1) }}
          </VButton>
        </div>
        
        <div class="divider h-6 w-px bg-border"></div>
        
        <div class="display-options flex items-center gap-2">
          <VLabel class="flex items-center gap-2 cursor-pointer">
            <VCheckbox 
              v-model="currentView.showWeekends"
              @change="updateView"
            />
            <span class="text-sm">Weekends</span>
          </VLabel>
          
          <VLabel class="flex items-center gap-2 cursor-pointer">
            <VCheckbox 
              v-model="currentView.showDependencies"
              @change="updateView"
            />
            <span class="text-sm">Dependencies</span>
          </VLabel>
          
          <VLabel class="flex items-center gap-2 cursor-pointer">
            <VCheckbox 
              v-model="currentView.showCriticalPath"
              @change="updateView"
            />
            <span class="text-sm">Critical Path</span>
          </VLabel>
          
          <VLabel class="flex items-center gap-2 cursor-pointer">
            <VCheckbox 
              v-model="currentView.showMilestones"
              @change="updateView"
            />
            <span class="text-sm">Milestones</span>
          </VLabel>
        </div>
      </div>
      
      <div class="toolbar-right flex items-center gap-3">
        <VInput
          v-model="searchQuery"
          placeholder="Search tasks..."
          class="w-64"
          @input="handleSearch"
        >
          <template #prefix>
            <Search class="h-4 w-4 text-muted-foreground" />
          </template>
        </VInput>
        
        <VSelect v-model="groupBy" @change="handleGroupBy" class="w-40">
          <option value="">No Grouping</option>
          <option value="phase">By Phase</option>
          <option value="assignee">By Assignee</option>
          <option value="status">By Status</option>
          <option value="priority">By Priority</option>
        </VSelect>
        
        <VButton variant="ghost" size="icon" @click="toggleFullscreen">
          <Maximize2 v-if="!isFullscreen" class="h-4 w-4" />
          <Minimize2 v-else class="h-4 w-4" />
        </VButton>
      </div>
    </div>

    <!-- Main Gantt Chart Component -->
    <div class="gantt-container" :class="{ 'fullscreen': isFullscreen }">
      <GanttChart
        v-if="!loading && tasks.length > 0"
        :project-id="projectId"
        :tasks="tasks"
        :dependencies="dependencies"
        @update:task="handleTaskUpdate"
        @update:dependencies="handleDependencyUpdate"
        @refresh="loadGanttData"
      />
      
      <!-- Loading State -->
      <div v-else-if="loading" class="flex items-center justify-center h-96">
        <div class="text-center">
          <Loader2 class="h-8 w-8 animate-spin mx-auto mb-4 text-primary" />
          <p class="text-muted-foreground">Loading Gantt chart...</p>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-else class="flex items-center justify-center h-96">
        <div class="text-center">
          <BarChart3 class="h-16 w-16 mx-auto mb-4 text-muted-foreground" />
          <h3 class="text-lg font-semibold mb-2">No Tasks Yet</h3>
          <p class="text-muted-foreground mb-4">Create your first task to see the Gantt chart</p>
          <VButton @click="openTaskDialog">
            <Plus class="h-4 w-4 mr-2" />
            Create Task
          </VButton>
        </div>
      </div>
    </div>

    <!-- Task Creation/Edit Dialog -->
    <VModal v-if="taskDialogOpen" @close="taskDialogOpen = false">
      <div class="max-w-2xl p-6">
        <h3 class="text-lg font-semibold mb-4">{{ editingTask ? 'Edit Task' : 'Create New Task' }}</h3>
        <div class="space-y-4">
          <!-- Task form fields here -->
        </div>
        <div class="flex justify-end gap-3 mt-6">
          <VButton variant="outline" @click="taskDialogOpen = false">Cancel</VButton>
          <VButton @click="saveTask">Save Task</VButton>
        </div>
      </div>
    </VModal>

    <!-- Import Dialog -->
    <VModal v-if="importDialogOpen" @close="importDialogOpen = false">
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-4">Import Gantt Data</h3>
        <div class="space-y-4">
          <div class="upload-area border-2 border-dashed rounded-lg p-8 text-center">
            <Upload class="h-12 w-12 mx-auto mb-4 text-muted-foreground" />
            <p class="text-sm text-muted-foreground mb-2">
              Drag and drop your file here, or click to browse
            </p>
            <p class="text-xs text-muted-foreground">
              Supports: MS Project (.mpp), XML, CSV
            </p>
            <input 
              type="file" 
              ref="fileInput"
              accept=".mpp,.xml,.csv"
              @change="handleFileSelect"
              class="hidden"
            />
            <VButton 
              variant="outline" 
              size="sm" 
              class="mt-4"
              @click="$refs.fileInput.click()"
            >
              Select File
            </VButton>
          </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
          <VButton variant="outline" @click="importDialogOpen = false">Cancel</VButton>
          <VButton @click="importFile" :disabled="!selectedFile">Import</VButton>
        </div>
      </div>
    </VModal>

    <!-- Auto-Schedule Options Dialog -->
    <VModal v-if="autoScheduleDialogOpen" @close="autoScheduleDialogOpen = false">
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-4">Auto-Schedule Options</h3>
        <div class="space-y-4">
          <VLabel class="flex items-center gap-2">
            <VCheckbox v-model="autoScheduleOptions.respectDependencies" />
            <span>Respect task dependencies</span>
          </VLabel>
          <VLabel class="flex items-center gap-2">
            <VCheckbox v-model="autoScheduleOptions.optimizeResources" />
            <span>Optimize resource allocation</span>
          </VLabel>
          <VLabel class="flex items-center gap-2">
            <VCheckbox v-model="autoScheduleOptions.avoidWeekends" />
            <span>Avoid weekends</span>
          </VLabel>
        </div>
        <div class="flex justify-end gap-3 mt-6">
          <VButton variant="outline" @click="autoScheduleDialogOpen = false">Cancel</VButton>
          <VButton @click="performAutoSchedule">Schedule</VButton>
        </div>
      </div>
    </VModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useGanttStore } from '@/stores/gantt'
import { useProjectStore } from '@/modules/projects/stores/project.store'
import GanttChart from '@/components/projects/GanttChart.vue'
import { VButton, VInput, VLabel, VCheckbox, VSelect, VDropdownMenu, VDropdownMenuItem, VModal } from '@/components/ui'
import { 
  ChevronRight, Download, Upload, Plus, Wand2, Search, 
  Maximize2, Minimize2, Loader2, BarChart3, FileText, 
  FileSpreadsheet, Image, Package
} from 'lucide-vue-next'
import { useNotifications } from '@/composables/useNotifications'
import type { GanttTask, GanttExportOptions } from '@/types/models/gantt'

const route = useRoute()
const ganttStore = useGanttStore()
const projectStore = useProjectStore()
const { showSuccess, showError } = useNotifications()

// State
const projectId = computed(() => route.params.projectId as string)
const project = computed(() => projectStore.currentProject)
const tasks = computed(() => ganttStore.filteredTasks)
const dependencies = computed(() => ganttStore.dependencies)
const loading = computed(() => ganttStore.loading)
const currentView = computed(() => ganttStore.currentView)

const searchQuery = ref('')
const groupBy = ref('')
const isFullscreen = ref(false)
const taskDialogOpen = ref(false)
const importDialogOpen = ref(false)
const autoScheduleDialogOpen = ref(false)
const editingTask = ref<GanttTask | null>(null)
const selectedFile = ref<File | null>(null)
const autoScheduleOptions = ref({
  respectDependencies: true,
  optimizeResources: false,
  avoidWeekends: true
})

// Methods
const loadGanttData = async () => {
  try {
    await ganttStore.fetchGanttData(projectId.value)
  } catch (error) {
    console.error('Failed to load Gantt data:', error)
  }
}

const changeScale = (scale: string) => {
  ganttStore.updateView({ scale })
}

const updateView = () => {
  ganttStore.updateView({
    showWeekends: currentView.value.showWeekends,
    showDependencies: currentView.value.showDependencies,
    showCriticalPath: currentView.value.showCriticalPath,
    showMilestones: currentView.value.showMilestones
  })
}

const handleSearch = () => {
  ganttStore.updateFilters({ search: searchQuery.value })
}

const handleGroupBy = () => {
  ganttStore.updateView({ groupBy: groupBy.value })
}

const toggleFullscreen = () => {
  isFullscreen.value = !isFullscreen.value
  
  if (isFullscreen.value) {
    document.documentElement.requestFullscreen()
  } else {
    document.exitFullscreen()
  }
}

const exportGantt = async (format: string) => {
  const options: GanttExportOptions = {
    format: format as any,
    includeDetails: true,
    includeDependencies: true,
    includeResources: true,
    paperSize: 'A4',
    orientation: 'landscape'
  }
  
  try {
    await ganttStore.exportGantt(projectId.value, options)
    showSuccess('Export Successful', `Gantt chart exported as ${format.toUpperCase()}`)
  } catch (error) {
    showError('Export Failed', 'Failed to export Gantt chart')
  }
}

const openTaskDialog = (task?: GanttTask) => {
  editingTask.value = task || null
  taskDialogOpen.value = true
}

const saveTask = async () => {
  // Implementation for saving task
  taskDialogOpen.value = false
}

const openImportDialog = () => {
  importDialogOpen.value = true
  selectedFile.value = null
}

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  selectedFile.value = target.files?.[0] || null
}

const importFile = async () => {
  if (!selectedFile.value) return
  
  const format = selectedFile.value.name.split('.').pop() as 'mpp' | 'xml' | 'csv'
  
  try {
    await ganttStore.importGantt(projectId.value, selectedFile.value, format)
    importDialogOpen.value = false
    showSuccess('Import Successful', 'Gantt data imported successfully')
  } catch (error) {
    showError('Import Failed', 'Failed to import Gantt data')
  }
}

const autoSchedule = () => {
  autoScheduleDialogOpen.value = true
}

const performAutoSchedule = async () => {
  try {
    await ganttStore.autoScheduleTasks(projectId.value, autoScheduleOptions.value)
    autoScheduleDialogOpen.value = false
    showSuccess('Auto-Schedule Complete', 'Tasks have been automatically scheduled')
  } catch (error) {
    showError('Auto-Schedule Failed', 'Failed to auto-schedule tasks')
  }
}

const handleTaskUpdate = async (task: GanttTask) => {
  try {
    await ganttStore.updateTaskDates(
      task.id, 
      task.startDate as string, 
      task.endDate as string
    )
    showSuccess('Task Updated', 'Task dates updated successfully')
  } catch (error) {
    showError('Update Failed', 'Failed to update task')
  }
}

const handleDependencyUpdate = async (dependencies: any) => {
  // Handle dependency updates
}

// Lifecycle
onMounted(() => {
  loadGanttData()
})

// Watch for project changes
watch(() => route.params.projectId, () => {
  if (route.params.projectId) {
    loadGanttData()
  }
})
</script>

<style scoped>
.project-gantt-page {
  @apply flex flex-col h-full bg-background;
}

.page-header {
  @apply bg-white border-b px-6 py-4;
}

.page-header-content {
  @apply flex items-center justify-between;
}

.gantt-toolbar {
  @apply bg-white border-b px-6 py-3 flex items-center justify-between;
}

.gantt-container {
  @apply flex-1 overflow-hidden;
}

.gantt-container.fullscreen {
  @apply fixed inset-0 z-50 bg-background;
}

.upload-area:hover {
  @apply border-primary bg-primary/5;
}
</style>