<template>
  <div class="min-h-screen bg-gray-50">
    <div v-if="loading" class="flex items-center justify-center h-64">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
    
    <div v-else-if="error" class="flex items-center justify-center h-64">
      <div class="text-center">
        <p class="text-red-600 mb-4">{{ error }}</p>
        <button 
          @click="fetchTask"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          Retry
        </button>
      </div>
    </div>
    
    <TaskDetail v-else-if="task" :task="task" @closed="goBack" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import TaskDetail from '@/modules/tasks/components/TaskDetail.vue'
import { taskService } from '@/modules/tasks/services/task.service'
import type { Task } from '@/modules/tasks/types/task.types'

const route = useRoute()
const router = useRouter()
const task = ref<Task | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

const taskId = computed(() => route.params.id as string)

const fetchTask = async () => {
  try {
    loading.value = true
    error.value = null
    task.value = await taskService.getTask(taskId.value)
  } catch (err) {
    error.value = 'Failed to load task details. Please try again.'
    console.error('Error fetching task:', err)
  } finally {
    loading.value = false
  }
}

const goBack = () => {
  router.push('/app/tasks')
}

onMounted(() => {
  fetchTask()
})
</script>