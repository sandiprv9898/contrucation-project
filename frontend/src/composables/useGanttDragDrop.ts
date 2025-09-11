import { ref, Ref } from 'vue'

interface DragTask {
  id: string
  name: string
  start_date: string
  due_date: string
  status: string
  progress_percentage: number
  dependencies?: string[]
}

export const useGanttDragDrop = (
  tasks: Ref<DragTask[]>,
  dateColumnWidth: Ref<number>,
  timelineStartDate: Ref<Date | null>
) => {
  const draggedTask = ref<DragTask | null>(null)
  const dragOffset = ref({ x: 0, y: 0 })
  const isDragging = ref(false)
  const dragPreview = ref({ x: 0, y: 0, width: 0 })

  const calculateTaskDuration = (task: DragTask): number => {
    const start = new Date(task.start_date)
    const end = new Date(task.due_date)
    return Math.max(1, Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)))
  }

  const calculateDateFromPosition = (x: number): Date => {
    if (!timelineStartDate.value) return new Date()
    
    const dayOffset = Math.floor(x / dateColumnWidth.value)
    const newDate = new Date(timelineStartDate.value)
    newDate.setDate(newDate.getDate() + dayOffset)
    return newDate
  }

  const calculatePositionFromDate = (date: Date): number => {
    if (!timelineStartDate.value) return 0
    
    const daysDiff = Math.floor((date.getTime() - timelineStartDate.value.getTime()) / (1000 * 60 * 60 * 24))
    return Math.max(0, daysDiff * dateColumnWidth.value)
  }

  const validateTaskMove = (task: DragTask, newStartDate: Date): { valid: boolean; message?: string } => {
    // Check if new start date conflicts with dependencies
    if (task.dependencies && task.dependencies.length > 0) {
      for (const depId of task.dependencies) {
        const dependentTask = tasks.value.find(t => t.id === depId)
        if (dependentTask) {
          const depEndDate = new Date(dependentTask.due_date)
          if (newStartDate < depEndDate) {
            return {
              valid: false,
              message: `Cannot start before dependency "${dependentTask.name}" is completed`
            }
          }
        }
      }
    }

    // Check if move affects tasks that depend on this task
    const dependentTasks = tasks.value.filter(t => 
      t.dependencies && t.dependencies.includes(task.id)
    )

    if (dependentTasks.length > 0) {
      const taskDuration = calculateTaskDuration(task)
      const newEndDate = new Date(newStartDate)
      newEndDate.setDate(newEndDate.getDate() + taskDuration - 1)

      for (const depTask of dependentTasks) {
        const depStartDate = new Date(depTask.start_date)
        if (newEndDate >= depStartDate) {
          return {
            valid: false,
            message: `Cannot end after dependent task "${depTask.name}" starts`
          }
        }
      }
    }

    return { valid: true }
  }

  const startDrag = (task: DragTask, event: MouseEvent, taskElement: HTMLElement) => {
    if (task.status === 'completed') {
      // Don't allow dragging completed tasks
      return false
    }

    draggedTask.value = task
    isDragging.value = true

    const rect = taskElement.getBoundingClientRect()
    dragOffset.value = {
      x: event.clientX - rect.left,
      y: event.clientY - rect.top
    }

    dragPreview.value = {
      x: event.clientX - dragOffset.value.x,
      y: event.clientY - dragOffset.value.y,
      width: rect.width
    }

    // Add global mouse move and up listeners
    document.addEventListener('mousemove', handleDragMove)
    document.addEventListener('mouseup', handleDragEnd)

    // Prevent default drag behavior
    event.preventDefault()
    return true
  }

  const handleDragMove = (event: MouseEvent) => {
    if (!isDragging.value || !draggedTask.value) return

    dragPreview.value = {
      x: event.clientX - dragOffset.value.x,
      y: event.clientY - dragOffset.value.y,
      width: dragPreview.value.width
    }
  }

  const handleDragEnd = (event: MouseEvent) => {
    if (!isDragging.value || !draggedTask.value) return

    const timelineElement = document.querySelector('.gantt-timeline-content')
    if (!timelineElement) return

    const rect = timelineElement.getBoundingClientRect()
    const relativeX = event.clientX - rect.left + timelineElement.scrollLeft
    
    // Calculate new start date
    const newStartDate = calculateDateFromPosition(relativeX)
    const validation = validateTaskMove(draggedTask.value, newStartDate)

    if (validation.valid) {
      // Update task dates
      const taskDuration = calculateTaskDuration(draggedTask.value)
      const newEndDate = new Date(newStartDate)
      newEndDate.setDate(newEndDate.getDate() + taskDuration - 1)

      const taskIndex = tasks.value.findIndex(t => t.id === draggedTask.value!.id)
      if (taskIndex !== -1) {
        tasks.value[taskIndex] = {
          ...tasks.value[taskIndex],
          start_date: newStartDate.toISOString().split('T')[0],
          due_date: newEndDate.toISOString().split('T')[0]
        }
      }

      return {
        success: true,
        task: draggedTask.value,
        newStartDate: newStartDate.toISOString().split('T')[0],
        newDueDate: newEndDate.toISOString().split('T')[0]
      }
    } else {
      return {
        success: false,
        message: validation.message
      }
    }

    // Cleanup
    cleanup()
  }

  const cleanup = () => {
    isDragging.value = false
    draggedTask.value = null
    dragOffset.value = { x: 0, y: 0 }
    
    // Remove global listeners
    document.removeEventListener('mousemove', handleDragMove)
    document.removeEventListener('mouseup', handleDragEnd)
  }

  const cancelDrag = () => {
    cleanup()
  }

  return {
    draggedTask,
    isDragging,
    dragPreview,
    startDrag,
    cancelDrag,
    validateTaskMove,
    calculateDateFromPosition,
    calculatePositionFromDate
  }
}