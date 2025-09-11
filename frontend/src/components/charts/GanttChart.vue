<template>
  <div class="gantt-chart bg-white rounded-lg shadow-sm border">
    <!-- Header -->
    <div class="p-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Project Timeline</h2>
          <p class="text-sm text-gray-500">Gantt chart with task dependencies</p>
        </div>
        <div class="flex items-center space-x-3">
          <!-- View Controls -->
          <select v-model="viewMode" class="text-sm border border-gray-300 rounded-md px-2 py-1">
            <option value="days">Days</option>
            <option value="weeks">Weeks</option>
            <option value="months">Months</option>
          </select>
          
          <!-- Zoom Controls -->
          <div class="flex items-center space-x-1">
            <button 
              @click="zoomOut"
              class="p-1 text-gray-500 hover:text-gray-700 border rounded"
            >
              <ZoomOut class="w-4 h-4" />
            </button>
            <button 
              @click="zoomIn"
              class="p-1 text-gray-500 hover:text-gray-700 border rounded"
            >
              <ZoomIn class="w-4 h-4" />
            </button>
          </div>

          <!-- Today Button -->
          <button 
            @click="scrollToToday"
            class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600"
          >
            Today
          </button>
        </div>
      </div>
    </div>

    <!-- Chart Container -->
    <div class="gantt-container">
      <!-- Left Panel - Task List -->
      <div class="gantt-left-panel">
        <div class="gantt-header-left">
          <div class="px-4 py-3 bg-gray-50 border-b font-medium text-sm text-gray-700">
            Tasks
          </div>
        </div>
        
        <div class="gantt-task-list">
          <div
            v-for="task in processedTasks"
            :key="task.id"
            :class="[
              'gantt-task-row',
              task.level > 0 ? 'gantt-subtask' : ''
            ]"
            :style="{ '--level': task.level }"
          >
            <div class="flex items-center px-4 py-3 text-sm">
              <!-- Expand/Collapse for parent tasks -->
              <button
                v-if="task.children?.length > 0"
                @click="toggleTask(task.id)"
                class="w-4 h-4 mr-2 flex-shrink-0"
              >
                <ChevronRight 
                  :class="['w-4 h-4 transition-transform', 
                    expandedTasks.has(task.id) ? 'rotate-90' : '']"
                />
              </button>
              <div v-else class="w-4 h-4 mr-2"></div>
              
              <!-- Task Status Icon -->
              <div class="w-3 h-3 rounded-full mr-3 flex-shrink-0" :class="getStatusColor(task.status)"></div>
              
              <!-- Task Name -->
              <div class="flex-1 min-w-0">
                <div class="font-medium text-gray-900 truncate">{{ task.name }}</div>
                <div class="text-xs text-gray-500">
                  {{ formatDate(task.start_date) }} - {{ formatDate(task.due_date) }}
                </div>
              </div>
              
              <!-- Progress -->
              <div class="text-xs text-gray-500 ml-2">
                {{ task.progress_percentage }}%
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Panel - Timeline -->
      <div class="gantt-right-panel">
        <!-- Timeline Header -->
        <div class="gantt-timeline-header bg-gray-50 border-b">
          <div 
            class="gantt-timeline-scale"
            :style="{ width: timelineWidth + 'px' }"
          >
            <!-- Date Headers -->
            <div class="flex">
              <div
                v-for="date in dateRange"
                :key="date.key"
                :class="[
                  'gantt-date-header',
                  date.isToday ? 'gantt-today-header' : ''
                ]"
                :style="{ width: dateColumnWidth + 'px' }"
              >
                <div class="text-xs font-medium">{{ date.label }}</div>
                <div v-if="viewMode === 'days'" class="text-xs text-gray-500">{{ date.dayLabel }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Timeline Content -->
        <div class="gantt-timeline-content" ref="timelineContent">
          <div 
            class="gantt-timeline-grid"
            :style="{ width: timelineWidth + 'px' }"
          >
            <!-- Grid Lines -->
            <div class="gantt-grid-lines">
              <div
                v-for="date in dateRange"
                :key="`grid-${date.key}`"
                :class="[
                  'gantt-grid-line',
                  date.isToday ? 'gantt-today-line' : ''
                ]"
                :style="{ left: date.position + 'px' }"
              ></div>
            </div>

            <!-- Task Bars -->
            <div class="gantt-bars">
              <div
                v-for="task in processedTasks"
                :key="task.id"
                class="gantt-task-bar-container"
              >
                <!-- Task Bar -->
                <div
                  :class="[
                    'gantt-task-bar',
                    getTaskBarClass(task)
                  ]"
                  :style="{
                    left: getTaskPosition(task) + 'px',
                    width: getTaskWidth(task) + 'px'
                  }"
                  @click="selectTask(task)"
                >
                  <!-- Progress Bar -->
                  <div 
                    class="gantt-progress-bar"
                    :style="{ width: task.progress_percentage + '%' }"
                  ></div>
                  
                  <!-- Task Label -->
                  <div class="gantt-task-label">
                    {{ task.name }}
                  </div>
                </div>

                <!-- Dependencies -->
                <div
                  v-for="dependency in getTaskDependencies(task)"
                  :key="`dep-${task.id}-${dependency.id}`"
                  class="gantt-dependency-line"
                  :style="getDependencyLineStyle(task, dependency)"
                ></div>
              </div>
            </div>

            <!-- Today Line -->
            <div 
              class="gantt-today-indicator"
              :style="{ left: getTodayPosition() + 'px' }"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Task Details Modal -->
    <div
      v-if="selectedTask"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="selectedTask = null"
    >
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ selectedTask.name }}</h3>
        
        <div class="space-y-3">
          <div class="flex justify-between">
            <span class="text-sm text-gray-500">Status:</span>
            <span :class="['text-sm font-medium', getStatusTextColor(selectedTask.status)]">
              {{ selectedTask.status.replace('_', ' ').toUpperCase() }}
            </span>
          </div>
          
          <div class="flex justify-between">
            <span class="text-sm text-gray-500">Progress:</span>
            <span class="text-sm font-medium">{{ selectedTask.progress_percentage }}%</span>
          </div>
          
          <div class="flex justify-between">
            <span class="text-sm text-gray-500">Start Date:</span>
            <span class="text-sm">{{ formatDate(selectedTask.start_date) }}</span>
          </div>
          
          <div class="flex justify-between">
            <span class="text-sm text-gray-500">Due Date:</span>
            <span class="text-sm">{{ formatDate(selectedTask.due_date) }}</span>
          </div>
          
          <div class="flex justify-between">
            <span class="text-sm text-gray-500">Duration:</span>
            <span class="text-sm">{{ getTaskDuration(selectedTask) }} days</span>
          </div>
          
          <div v-if="selectedTask.dependencies?.length > 0">
            <span class="text-sm text-gray-500">Dependencies:</span>
            <div class="mt-1 space-y-1">
              <div
                v-for="depId in selectedTask.dependencies"
                :key="depId"
                class="text-sm text-blue-600"
              >
                {{ getTaskById(depId)?.name || 'Unknown Task' }}
              </div>
            </div>
          </div>
        </div>
        
        <div class="flex space-x-3 mt-6">
          <button
            @click="selectedTask = null"
            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg"
          >
            Close
          </button>
          <button
            @click="openTask(selectedTask)"
            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg"
          >
            View Details
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { ZoomIn, ZoomOut, ChevronRight } from 'lucide-vue-next'

interface GanttTask {
  id: string
  name: string
  start_date: string
  due_date: string
  status: 'not_started' | 'in_progress' | 'review' | 'completed' | 'cancelled' | 'on_hold'
  progress_percentage: number
  dependencies?: string[]
  parent_id?: string | null
  children?: GanttTask[]
  level?: number
  priority?: 'low' | 'medium' | 'high' | 'critical'
}

interface Props {
  tasks: GanttTask[]
}

const props = defineProps<Props>()

// State
const viewMode = ref<'days' | 'weeks' | 'months'>('days')
const zoomLevel = ref(1)
const selectedTask = ref<GanttTask | null>(null)
const expandedTasks = ref(new Set<string>())
const timelineContent = ref<HTMLElement>()

// Constants
const MIN_DATE_COLUMN_WIDTH = 30
const MAX_DATE_COLUMN_WIDTH = 150
const TASK_ROW_HEIGHT = 48
const BASE_DATE_COLUMN_WIDTH = 60

// Computed
const dateColumnWidth = computed(() => {
  const baseWidth = BASE_DATE_COLUMN_WIDTH * zoomLevel.value
  return Math.max(MIN_DATE_COLUMN_WIDTH, Math.min(MAX_DATE_COLUMN_WIDTH, baseWidth))
})

const processedTasks = computed(() => {
  const buildTaskTree = (tasks: GanttTask[], parentId: string | null = null, level = 0): GanttTask[] => {
    const result: GanttTask[] = []
    
    const parentTasks = tasks.filter(task => task.parent_id === parentId)
    
    for (const task of parentTasks) {
      const taskWithLevel = { ...task, level }
      const children = buildTaskTree(tasks, task.id, level + 1)
      
      if (children.length > 0) {
        taskWithLevel.children = children
      }
      
      result.push(taskWithLevel)
      
      // Add children if parent is expanded
      if (expandedTasks.value.has(task.id)) {
        result.push(...children)
      }
    }
    
    return result
  }
  
  return buildTaskTree(props.tasks)
})

const dateRange = computed(() => {
  if (!props.tasks.length) return []
  
  const startDates = props.tasks.map(task => new Date(task.start_date))
  const endDates = props.tasks.map(task => new Date(task.due_date))
  
  const minDate = new Date(Math.min(...startDates.map(d => d.getTime())))
  const maxDate = new Date(Math.max(...endDates.map(d => d.getTime())))
  
  // Add buffer
  minDate.setDate(minDate.getDate() - 7)
  maxDate.setDate(maxDate.getDate() + 7)
  
  const dates = []
  const currentDate = new Date(minDate)
  const today = new Date()
  
  while (currentDate <= maxDate) {
    const isToday = currentDate.toDateString() === today.toDateString()
    
    dates.push({
      key: currentDate.toISOString().split('T')[0],
      label: getDateLabel(new Date(currentDate)),
      dayLabel: currentDate.toLocaleDateString('en', { weekday: 'short' }),
      date: new Date(currentDate),
      position: (dates.length) * dateColumnWidth.value,
      isToday
    })
    
    // Increment based on view mode
    if (viewMode.value === 'days') {
      currentDate.setDate(currentDate.getDate() + 1)
    } else if (viewMode.value === 'weeks') {
      currentDate.setDate(currentDate.getDate() + 7)
    } else {
      currentDate.setMonth(currentDate.getMonth() + 1)
    }
  }
  
  return dates
})

const timelineWidth = computed(() => {
  return dateRange.value.length * dateColumnWidth.value
})

// Methods
const toggleTask = (taskId: string) => {
  if (expandedTasks.value.has(taskId)) {
    expandedTasks.value.delete(taskId)
  } else {
    expandedTasks.value.add(taskId)
  }
}

const getStatusColor = (status: string) => {
  const colors = {
    'not_started': 'bg-gray-400',
    'in_progress': 'bg-blue-500',
    'review': 'bg-yellow-500',
    'completed': 'bg-green-500',
    'cancelled': 'bg-red-500',
    'on_hold': 'bg-orange-500'
  }
  return colors[status] || 'bg-gray-400'
}

const getStatusTextColor = (status: string) => {
  const colors = {
    'not_started': 'text-gray-600',
    'in_progress': 'text-blue-600',
    'review': 'text-yellow-600',
    'completed': 'text-green-600',
    'cancelled': 'text-red-600',
    'on_hold': 'text-orange-600'
  }
  return colors[status] || 'text-gray-600'
}

const getTaskBarClass = (task: GanttTask) => {
  const baseClasses = 'gantt-task-bar'
  const statusClasses = {
    'not_started': 'gantt-task-not-started',
    'in_progress': 'gantt-task-in-progress',
    'review': 'gantt-task-review',
    'completed': 'gantt-task-completed',
    'cancelled': 'gantt-task-cancelled',
    'on_hold': 'gantt-task-on-hold'
  }
  return `${baseClasses} ${statusClasses[task.status] || statusClasses.not_started}`
}

const getTaskPosition = (task: GanttTask) => {
  const taskStart = new Date(task.start_date)
  const timelineStart = dateRange.value[0]?.date
  
  if (!timelineStart) return 0
  
  const daysDiff = Math.floor((taskStart.getTime() - timelineStart.getTime()) / (1000 * 60 * 60 * 24))
  return Math.max(0, daysDiff * dateColumnWidth.value)
}

const getTaskWidth = (task: GanttTask) => {
  const taskStart = new Date(task.start_date)
  const taskEnd = new Date(task.due_date)
  const daysDiff = Math.max(1, Math.ceil((taskEnd.getTime() - taskStart.getTime()) / (1000 * 60 * 60 * 24)))
  
  return daysDiff * dateColumnWidth.value
}

const getTaskDuration = (task: GanttTask) => {
  const taskStart = new Date(task.start_date)
  const taskEnd = new Date(task.due_date)
  return Math.max(1, Math.ceil((taskEnd.getTime() - taskStart.getTime()) / (1000 * 60 * 60 * 24)))
}

const getTaskDependencies = (task: GanttTask) => {
  if (!task.dependencies) return []
  
  return task.dependencies
    .map(depId => props.tasks.find(t => t.id === depId))
    .filter(Boolean) as GanttTask[]
}

const getTaskById = (taskId: string) => {
  return props.tasks.find(t => t.id === taskId)
}

const getDependencyLineStyle = (fromTask: GanttTask, toTask: GanttTask) => {
  // This is a simplified dependency line - in a real implementation,
  // you'd calculate the exact path between task bars
  const fromPos = getTaskPosition(fromTask)
  const toPos = getTaskPosition(toTask) + getTaskWidth(toTask)
  
  return {
    position: 'absolute',
    left: Math.min(fromPos, toPos) + 'px',
    width: Math.abs(toPos - fromPos) + 'px',
    height: '2px',
    backgroundColor: '#3b82f6',
    top: '50%',
    zIndex: 10
  }
}

const getTodayPosition = () => {
  const today = new Date()
  const timelineStart = dateRange.value[0]?.date
  
  if (!timelineStart) return 0
  
  const daysDiff = Math.floor((today.getTime() - timelineStart.getTime()) / (1000 * 60 * 60 * 24))
  return daysDiff * dateColumnWidth.value
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const getDateLabel = (date: Date) => {
  if (viewMode.value === 'days') {
    return date.getDate().toString()
  } else if (viewMode.value === 'weeks') {
    const weekStart = new Date(date)
    weekStart.setDate(date.getDate() - date.getDay())
    return `Week ${Math.ceil(date.getDate() / 7)}`
  } else {
    return date.toLocaleDateString('en', { month: 'short' })
  }
}

const selectTask = (task: GanttTask) => {
  selectedTask.value = task
}

const openTask = (task: GanttTask) => {
  // Emit event or navigate to task detail
  selectedTask.value = null
  // You can emit an event here to handle task navigation
  // emit('task-selected', task)
}

const scrollToToday = () => {
  if (!timelineContent.value) return
  
  const todayPosition = getTodayPosition()
  timelineContent.value.scrollLeft = Math.max(0, todayPosition - timelineContent.value.clientWidth / 2)
}

const zoomIn = () => {
  zoomLevel.value = Math.min(3, zoomLevel.value * 1.2)
}

const zoomOut = () => {
  zoomLevel.value = Math.max(0.5, zoomLevel.value / 1.2)
}

// Lifecycle
onMounted(() => {
  // Expand all top-level tasks by default
  props.tasks
    .filter(task => !task.parent_id)
    .forEach(task => expandedTasks.value.add(task.id))
    
  nextTick(() => {
    scrollToToday()
  })
})
</script>

<style scoped>
.gantt-container {
  display: flex;
  max-height: 600px;
  overflow: hidden;
}

.gantt-left-panel {
  width: 300px;
  border-right: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
}

.gantt-right-panel {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.gantt-header-left {
  flex-shrink: 0;
}

.gantt-task-list {
  overflow-y: auto;
  flex: 1;
}

.gantt-task-row {
  height: 48px;
  border-bottom: 1px solid #f3f4f6;
  display: flex;
  align-items: center;
}

.gantt-subtask {
  --indent: calc(var(--level) * 20px);
  padding-left: var(--indent);
}

.gantt-timeline-header {
  flex-shrink: 0;
  height: 60px;
  overflow: hidden;
}

.gantt-timeline-content {
  flex: 1;
  overflow: auto;
  position: relative;
}

.gantt-timeline-scale {
  position: relative;
}

.gantt-date-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 60px;
  border-right: 1px solid #e5e7eb;
  padding: 4px;
}

.gantt-today-header {
  background-color: #dbeafe;
  border-color: #3b82f6;
}

.gantt-timeline-grid {
  position: relative;
  height: 100%;
}

.gantt-grid-lines {
  position: absolute;
  inset: 0;
}

.gantt-grid-line {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 1px;
  background-color: #e5e7eb;
}

.gantt-today-line {
  background-color: #3b82f6;
  width: 2px;
  z-index: 20;
}

.gantt-bars {
  position: absolute;
  inset: 0;
}

.gantt-task-bar-container {
  position: relative;
  height: 48px;
}

.gantt-task-bar {
  position: absolute;
  height: 24px;
  top: 12px;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  min-width: 20px;
  overflow: hidden;
  transition: all 0.2s;
}

.gantt-task-bar:hover {
  opacity: 0.8;
  transform: scale(1.02);
}

.gantt-task-not-started {
  background-color: #9ca3af;
}

.gantt-task-in-progress {
  background-color: #3b82f6;
}

.gantt-task-review {
  background-color: #f59e0b;
}

.gantt-task-completed {
  background-color: #10b981;
}

.gantt-task-cancelled {
  background-color: #ef4444;
}

.gantt-task-on-hold {
  background-color: #f97316;
}

.gantt-progress-bar {
  height: 100%;
  background-color: rgba(255, 255, 255, 0.3);
  border-radius: inherit;
}

.gantt-task-label {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  padding: 0 8px;
  color: white;
  font-size: 12px;
  font-weight: 500;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.gantt-dependency-line {
  position: absolute;
  height: 2px;
  background-color: #3b82f6;
  z-index: 10;
}

.gantt-today-indicator {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 2px;
  background-color: #ef4444;
  z-index: 30;
  pointer-events: none;
}
</style>