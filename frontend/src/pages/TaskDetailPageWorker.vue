<template>
  <div class="worker-mode">
    <div v-if="isLoading" class="min-h-screen bg-gray-50 flex items-center justify-center">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
        <p class="text-lg text-gray-600">Loading task details...</p>
      </div>
    </div>
    
    <div v-else-if="error" class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
      <div class="text-center bg-white rounded-2xl p-8 shadow-lg max-w-md">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <AlertCircle class="w-8 h-8 text-red-600" />
        </div>
        <h2 class="text-xl font-bold text-gray-900 mb-2">Oops! Something went wrong</h2>
        <p class="text-gray-600 mb-6">{{ error.message || 'Failed to load task details' }}</p>
        <button
          @click="loadTask"
          class="worker-btn worker-btn-primary w-full"
        >
          Try Again
        </button>
      </div>
    </div>
    
    <div v-else-if="!task" class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
      <div class="text-center bg-white rounded-2xl p-8 shadow-lg max-w-md">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <FileText class="w-8 h-8 text-gray-600" />
        </div>
        <h2 class="text-xl font-bold text-gray-900 mb-2">Task not found</h2>
        <p class="text-gray-600 mb-6">The task you're looking for doesn't exist or you don't have access to it.</p>
        <button
          @click="$router.push('/app/worker/tasks')"
          class="worker-btn worker-btn-primary w-full"
        >
          Back to My Tasks
        </button>
      </div>
    </div>
    
    <TaskDetailWorker
      v-else
      :task="task"
      @updated="loadTask"
      @closed="$router.push('/app/worker/tasks')"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { AlertCircle, FileText } from 'lucide-vue-next'
import { useTaskStore } from '@/modules/tasks/stores/task.store'
import TaskDetailWorker from '@/modules/tasks/components/TaskDetailWorker.vue'
import type { Task } from '@/modules/tasks/types/task.types'

defineOptions({ name: 'TaskDetailPageWorker' })

const route = useRoute()
const taskStore = useTaskStore()

// State
const task = ref<Task | null>(null)
const isLoading = ref(false)
const error = ref<Error | null>(null)

// Methods
const loadTask = async () => {
  try {
    isLoading.value = true
    error.value = null
    
    const taskId = route.params.id as string
    if (!taskId) {
      throw new Error('Task ID is required')
    }
    
    const loadedTask = await taskStore.getTaskById(taskId)
    task.value = loadedTask
    
  } catch (err: any) {
    console.error('Failed to load task:', err)
    error.value = err
  } finally {
    isLoading.value = false
  }
}

// Watch for route parameter changes
watch(
  () => route.params.id,
  (newId) => {
    if (newId) {
      loadTask()
    }
  },
  { immediate: true }
)

// Lifecycle
onMounted(() => {
  if (route.params.id) {
    loadTask()
  }
})
</script>

<style scoped>
.worker-mode {
  /* Apply worker-specific styling */
  @apply min-h-screen bg-gray-50;
}

/* Ensure touch-friendly interactions */
.worker-mode button {
  @apply min-h-[48px] min-w-[48px];
}

.worker-mode input,
.worker-mode textarea,
.worker-mode select {
  @apply min-h-[48px];
}
</style>