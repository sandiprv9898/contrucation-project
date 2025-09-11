// Task Management Module Exports

// Types
export * from './types/task.types'

// Services
export { taskService } from './services/task.service'

// Stores
export { useTaskStore } from './stores/task.store'

// Components
export { default as TaskForm } from './components/TaskForm.vue'
export { default as TaskDetail } from './components/TaskDetail.vue'
export { default as TaskCard } from './components/TaskCard.vue'
// export { default as TaskKanban } from './components/TaskKanban.vue'

export default {
  name: 'TaskManagement',
  version: '1.0.0'
}