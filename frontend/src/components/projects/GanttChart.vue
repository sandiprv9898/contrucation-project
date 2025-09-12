<template>
  <div class="gantt-chart-container">
    <!-- Header Section -->
    <div class="gantt-header">
      <div class="gantt-header-left">
        <h2 class="text-2xl font-bold">Project Timeline</h2>
        <p class="text-sm text-muted-foreground">Gantt chart with task dependencies and scheduling</p>
      </div>
      <div class="gantt-header-right flex items-center gap-4">
        <div class="flex items-center gap-6 text-sm">
          <div class="flex items-center gap-2">
            <Calendar class="h-4 w-4 text-blue-600" />
            <span>Total Tasks</span>
            <strong>{{ tasks.length }}</strong>
          </div>
          <div class="flex items-center gap-2">
            <Clock class="h-4 w-4 text-yellow-600" />
            <span>In Progress</span>
            <strong>{{ inProgressCount }}</strong>
          </div>
          <div class="flex items-center gap-2">
            <CheckCircle class="h-4 w-4 text-green-600" />
            <span>Completed</span>
            <strong>{{ completedCount }}</strong>
          </div>
          <div class="flex items-center gap-2">
            <AlertTriangle class="h-4 w-4 text-red-600" />
            <span>Overdue</span>
            <strong>{{ overdueCount }}</strong>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <VButton @click="toggleView" variant="outline" size="sm">
            <LayoutGrid v-if="viewMode === 'timeline'" class="h-4 w-4 mr-2" />
            <BarChart3 v-else class="h-4 w-4 mr-2" />
            {{ viewMode === 'timeline' ? 'Weeks' : 'Timeline' }}
          </VButton>
          <VButton @click="handleRefresh" variant="default" size="sm">
            <RefreshCw class="h-4 w-4 mr-2" />
            Refresh
          </VButton>
        </div>
      </div>
    </div>

    <!-- Main Gantt Chart -->
    <div class="gantt-wrapper">
      <div class="gantt-split-view">
        <!-- Left Panel: Task List -->
        <div class="gantt-task-panel">
          <div class="gantt-task-header">
            <div class="task-column task-name">Task Name</div>
            <div class="task-column task-duration">Duration</div>
            <div class="task-column task-start">Start</div>
            <div class="task-column task-end">End</div>
          </div>
          <div class="gantt-task-list">
            <div
              v-for="(task, index) in displayTasks"
              :key="task.id"
              class="gantt-task-row"
              :class="{ 
                'task-parent': task.children?.length > 0,
                'task-child': task.parentId,
                'task-selected': selectedTask?.id === task.id
              }"
              @click="selectTask(task)"
            >
              <div class="task-column task-name">
                <div class="flex items-center gap-2" :style="{ paddingLeft: `${task.level * 20}px` }">
                  <ChevronRight 
                    v-if="task.children?.length > 0"
                    class="h-4 w-4 cursor-pointer transition-transform"
                    :class="{ 'rotate-90': expandedTasks.has(task.id) }"
                    @click.stop="toggleExpand(task.id)"
                  />
                  <span v-else class="w-4"></span>
                  <Folder v-if="task.type === 'milestone'" class="h-4 w-4 text-blue-600" />
                  <FileText v-else-if="task.type === 'task'" class="h-4 w-4 text-gray-600" />
                  <span class="task-name-text">{{ task.name }}</span>
                </div>
              </div>
              <div class="task-column task-duration">{{ task.duration }} days</div>
              <div class="task-column task-start">{{ formatDate(task.startDate) }}</div>
              <div class="task-column task-end">{{ formatDate(task.endDate) }}</div>
            </div>
          </div>
        </div>

        <!-- Right Panel: Timeline -->
        <div class="gantt-timeline-panel" ref="timelinePanel">
          <div class="gantt-timeline-header">
            <div class="timeline-months">
              <div v-for="month in visibleMonths" :key="month.key" class="timeline-month">
                {{ month.label }}
              </div>
            </div>
            <div class="timeline-weeks">
              <div v-for="week in visibleWeeks" :key="week.key" class="timeline-week">
                {{ week.label }}
              </div>
            </div>
          </div>
          <div class="gantt-timeline-body">
            <div class="timeline-grid">
              <div v-for="week in visibleWeeks" :key="week.key" class="timeline-grid-column"></div>
            </div>
            <div class="timeline-tasks">
              <div
                v-for="(task, index) in displayTasks"
                :key="task.id"
                class="timeline-task-row"
              >
                <div
                  class="timeline-task-bar"
                  :style="getTaskBarStyle(task)"
                  :class="getTaskBarClass(task)"
                  @mousedown="startDrag(task, $event)"
                  @mouseenter="showTooltip(task, $event)"
                  @mouseleave="hideTooltip"
                >
                  <div class="task-bar-progress" :style="{ width: `${task.progress}%` }"></div>
                  <span class="task-bar-label">{{ task.name }}</span>
                </div>
                <!-- Dependencies Lines -->
                <svg
                  v-if="task.dependencies?.length > 0"
                  class="dependency-lines"
                  :style="{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%', pointerEvents: 'none' }"
                >
                  <path
                    v-for="dep in getTaskDependencyPaths(task)"
                    :key="dep.id"
                    :d="dep.path"
                    class="dependency-path"
                    :stroke="dep.color"
                    stroke-width="2"
                    fill="none"
                    marker-end="url(#arrow)"
                  />
                  <defs>
                    <marker id="arrow" markerWidth="10" markerHeight="10" refX="8" refY="3" orient="auto">
                      <polygon points="0 0, 10 3, 0 6" fill="#6b7280" />
                    </marker>
                  </defs>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Task Dependencies Panel -->
    <div v-if="selectedTask" class="gantt-dependencies-panel">
      <div class="dependencies-header">
        <h3 class="text-lg font-semibold">Task Dependencies</h3>
        <VButton @click="editDependencies" variant="outline" size="sm">
          Edit Dependencies
        </VButton>
      </div>
      <div class="dependencies-content">
        <div class="dependency-item" v-for="dep in selectedTaskDependencies" :key="dep.id">
          <div class="flex items-center gap-2">
            <ArrowRight class="h-4 w-4 text-blue-600" />
            <span class="font-medium">{{ dep.name }}</span>
            <VBadge variant="outline" size="sm">({{ dep.dependencyCount }} dependencies)</VBadge>
          </div>
          <div class="text-sm text-muted-foreground ml-6">
            Depends on: {{ dep.dependsOn.join(', ') }}
          </div>
        </div>
      </div>
    </div>

    <!-- Tooltip -->
    <div 
      v-if="tooltip.visible"
      class="gantt-tooltip"
      :style="{ top: tooltip.y + 'px', left: tooltip.x + 'px' }"
    >
      <div class="font-semibold">{{ tooltip.task?.name }}</div>
      <div class="text-sm">
        <div>Start: {{ formatDate(tooltip.task?.startDate) }}</div>
        <div>End: {{ formatDate(tooltip.task?.endDate) }}</div>
        <div>Progress: {{ tooltip.task?.progress }}%</div>
        <div>Duration: {{ tooltip.task?.duration }} days</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { 
  Calendar, Clock, CheckCircle, AlertTriangle, RefreshCw, LayoutGrid, BarChart3,
  ChevronRight, Folder, FileText, ArrowRight
} from 'lucide-vue-next'
import { VButton, VBadge } from '@/components/ui'
import type { GanttTask, TaskDependency } from '@/types/models/gantt'

interface Props {
  projectId: string
  tasks: GanttTask[]
  dependencies?: TaskDependency[]
}

const props = defineProps<Props>()
const emit = defineEmits<{
  'update:task': [task: GanttTask]
  'update:dependencies': [dependencies: TaskDependency[]]
  'refresh': []
}>()

// State
const viewMode = ref<'timeline' | 'weeks'>('timeline')
const selectedTask = ref<GanttTask | null>(null)
const expandedTasks = ref(new Set<string>())
const timelinePanel = ref<HTMLElement>()
const dragState = ref({
  isDragging: false,
  task: null as GanttTask | null,
  startX: 0,
  startLeft: 0
})
const tooltip = ref({
  visible: false,
  task: null as GanttTask | null,
  x: 0,
  y: 0
})

// Computed
const displayTasks = computed(() => {
  const result: GanttTask[] = []
  const processTask = (task: GanttTask, level = 0) => {
    result.push({ ...task, level })
    if (task.children && expandedTasks.value.has(task.id)) {
      task.children.forEach(child => processTask(child, level + 1))
    }
  }
  props.tasks.forEach(task => processTask(task))
  return result
})

const inProgressCount = computed(() => 
  props.tasks.filter(t => t.status === 'in_progress').length
)

const completedCount = computed(() =>
  props.tasks.filter(t => t.status === 'completed').length
)

const overdueCount = computed(() =>
  props.tasks.filter(t => {
    const now = new Date()
    const end = new Date(t.endDate)
    return end < now && t.status !== 'completed'
  }).length
)

const visibleMonths = computed(() => {
  // Calculate visible months based on timeline range
  const months = []
  const startDate = new Date(Math.min(...props.tasks.map(t => new Date(t.startDate).getTime())))
  const endDate = new Date(Math.max(...props.tasks.map(t => new Date(t.endDate).getTime())))
  
  let current = new Date(startDate.getFullYear(), startDate.getMonth(), 1)
  while (current <= endDate) {
    months.push({
      key: `${current.getFullYear()}-${current.getMonth()}`,
      label: current.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
    })
    current.setMonth(current.getMonth() + 1)
  }
  
  return months
})

const visibleWeeks = computed(() => {
  // Calculate visible weeks based on timeline range
  const weeks = []
  const startDate = new Date(Math.min(...props.tasks.map(t => new Date(t.startDate).getTime())))
  const endDate = new Date(Math.max(...props.tasks.map(t => new Date(t.endDate).getTime())))
  
  let current = new Date(startDate)
  current.setDate(current.getDate() - current.getDay()) // Start from Sunday
  
  let weekNum = 1
  while (current <= endDate) {
    weeks.push({
      key: `week-${weekNum}`,
      label: `Week ${weekNum}`,
      date: new Date(current)
    })
    current.setDate(current.getDate() + 7)
    weekNum++
  }
  
  return weeks
})

const selectedTaskDependencies = computed(() => {
  if (!selectedTask.value) return []
  return props.dependencies?.filter(d => 
    d.taskId === selectedTask.value?.id || d.dependsOnId === selectedTask.value?.id
  ) || []
})

// Methods
const formatDate = (date: string | Date) => {
  return new Date(date).toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric',
    year: 'numeric'
  })
}

const toggleView = () => {
  viewMode.value = viewMode.value === 'timeline' ? 'weeks' : 'timeline'
}

const handleRefresh = () => {
  emit('refresh')
}

const selectTask = (task: GanttTask) => {
  selectedTask.value = task
}

const toggleExpand = (taskId: string) => {
  if (expandedTasks.value.has(taskId)) {
    expandedTasks.value.delete(taskId)
  } else {
    expandedTasks.value.add(taskId)
  }
}

const getTaskBarStyle = (task: GanttTask) => {
  const startDate = new Date(Math.min(...props.tasks.map(t => new Date(t.startDate).getTime())))
  const totalDays = visibleWeeks.value.length * 7
  const taskStart = new Date(task.startDate)
  const taskEnd = new Date(task.endDate)
  
  const startDiff = Math.floor((taskStart.getTime() - startDate.getTime()) / (1000 * 60 * 60 * 24))
  const duration = Math.floor((taskEnd.getTime() - taskStart.getTime()) / (1000 * 60 * 60 * 24)) + 1
  
  const left = (startDiff / totalDays) * 100
  const width = (duration / totalDays) * 100
  
  return {
    left: `${left}%`,
    width: `${width}%`
  }
}

const getTaskBarClass = (task: GanttTask) => {
  const classes = ['task-bar']
  
  if (task.status === 'completed') {
    classes.push('task-completed')
  } else if (task.status === 'in_progress') {
    classes.push('task-in-progress')
  } else if (new Date(task.endDate) < new Date() && task.status !== 'completed') {
    classes.push('task-overdue')
  } else {
    classes.push('task-pending')
  }
  
  if (task.type === 'milestone') {
    classes.push('task-milestone')
  }
  
  if (task.critical) {
    classes.push('task-critical')
  }
  
  return classes.join(' ')
}

const getTaskDependencyPaths = (task: GanttTask) => {
  // Calculate SVG paths for dependencies
  const paths = []
  const dependencies = props.dependencies?.filter(d => d.taskId === task.id) || []
  
  dependencies.forEach(dep => {
    const dependsOnTask = props.tasks.find(t => t.id === dep.dependsOnId)
    if (dependsOnTask) {
      // Calculate path coordinates based on task positions
      paths.push({
        id: dep.id,
        path: calculateDependencyPath(task, dependsOnTask),
        color: dep.type === 'critical' ? '#ef4444' : '#6b7280'
      })
    }
  })
  
  return paths
}

const calculateDependencyPath = (fromTask: GanttTask, toTask: GanttTask) => {
  // Simplified path calculation - would need real positioning logic
  return `M 0 0 L 100 0`
}

const startDrag = (task: GanttTask, event: MouseEvent) => {
  dragState.value = {
    isDragging: true,
    task,
    startX: event.clientX,
    startLeft: event.offsetX
  }
  
  document.addEventListener('mousemove', handleDrag)
  document.addEventListener('mouseup', endDrag)
}

const handleDrag = (event: MouseEvent) => {
  if (!dragState.value.isDragging || !dragState.value.task) return
  
  const deltaX = event.clientX - dragState.value.startX
  // Update task position based on drag
  // This would update the task dates based on the new position
}

const endDrag = () => {
  if (dragState.value.task) {
    emit('update:task', dragState.value.task)
  }
  
  dragState.value = {
    isDragging: false,
    task: null,
    startX: 0,
    startLeft: 0
  }
  
  document.removeEventListener('mousemove', handleDrag)
  document.removeEventListener('mouseup', endDrag)
}

const showTooltip = (task: GanttTask, event: MouseEvent) => {
  tooltip.value = {
    visible: true,
    task,
    x: event.clientX + 10,
    y: event.clientY - 10
  }
}

const hideTooltip = () => {
  tooltip.value.visible = false
}

const editDependencies = () => {
  // Open dependencies editor
}

// Lifecycle
onMounted(() => {
  // Initialize expanded state for parent tasks
  props.tasks.forEach(task => {
    if (task.children?.length > 0) {
      expandedTasks.value.add(task.id)
    }
  })
})

onUnmounted(() => {
  document.removeEventListener('mousemove', handleDrag)
  document.removeEventListener('mouseup', endDrag)
})
</script>

<style scoped>
.gantt-chart-container {
  @apply bg-white rounded-lg shadow-sm border;
}

.gantt-header {
  @apply flex items-center justify-between p-4 border-b;
}

.gantt-header-left h2 {
  @apply text-2xl font-bold;
}

.gantt-wrapper {
  @apply overflow-hidden;
}

.gantt-split-view {
  @apply flex;
}

.gantt-task-panel {
  @apply w-2/5 border-r bg-gray-50;
}

.gantt-task-header {
  @apply flex bg-gray-100 border-b font-semibold text-sm;
}

.task-column {
  @apply px-3 py-2;
}

.task-name {
  @apply flex-1;
}

.task-duration {
  @apply w-20 text-center;
}

.task-start,
.task-end {
  @apply w-24;
}

.gantt-task-list {
  @apply max-h-[500px] overflow-y-auto;
}

.gantt-task-row {
  @apply flex items-center border-b hover:bg-white cursor-pointer transition-colors;
}

.gantt-task-row.task-selected {
  @apply bg-blue-50;
}

.gantt-task-row.task-parent {
  @apply font-semibold;
}

.gantt-task-row.task-child {
  @apply text-sm;
}

.task-name-text {
  @apply truncate;
}

.gantt-timeline-panel {
  @apply flex-1 overflow-x-auto;
}

.gantt-timeline-header {
  @apply bg-gray-100 border-b sticky top-0 z-10;
}

.timeline-months {
  @apply flex border-b;
}

.timeline-month {
  @apply px-4 py-2 text-sm font-semibold border-r;
  min-width: 200px;
}

.timeline-weeks {
  @apply flex;
}

.timeline-week {
  @apply px-2 py-1 text-xs text-center border-r;
  min-width: 50px;
}

.gantt-timeline-body {
  @apply relative min-h-[400px];
}

.timeline-grid {
  @apply absolute inset-0 flex;
}

.timeline-grid-column {
  @apply border-r border-gray-200;
  min-width: 50px;
  flex: 1;
}

.timeline-tasks {
  @apply relative z-20;
}

.timeline-task-row {
  @apply relative h-10 border-b border-transparent;
}

.timeline-task-bar {
  @apply absolute top-2 h-6 rounded cursor-move flex items-center px-2 text-xs text-white font-medium overflow-hidden;
  transition: all 0.2s;
}

.task-bar:hover {
  @apply shadow-lg transform scale-105;
}

.task-bar-progress {
  @apply absolute inset-0 bg-black bg-opacity-20;
}

.task-bar-label {
  @apply relative z-10 truncate;
}

.task-pending {
  @apply bg-gray-400;
}

.task-in-progress {
  @apply bg-blue-500;
}

.task-completed {
  @apply bg-green-500;
}

.task-overdue {
  @apply bg-red-500;
}

.task-milestone {
  @apply bg-purple-500;
}

.task-critical {
  @apply ring-2 ring-red-400 ring-offset-2;
}

.dependency-path {
  @apply opacity-60 hover:opacity-100 transition-opacity;
}

.gantt-dependencies-panel {
  @apply border-t p-4 bg-gray-50;
}

.dependencies-header {
  @apply flex items-center justify-between mb-4;
}

.dependency-item {
  @apply py-2 border-b last:border-0;
}

.gantt-tooltip {
  @apply absolute z-50 bg-gray-900 text-white p-3 rounded-lg shadow-lg pointer-events-none;
  min-width: 200px;
}
</style>