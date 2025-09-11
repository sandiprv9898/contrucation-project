<template>
  <div 
    class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow cursor-pointer"
    @click="$emit('click', task)"
  >
    <!-- Header -->
    <div class="flex items-start justify-between mb-3">
      <h3 class="font-medium text-gray-900 truncate flex-1 mr-2">{{ task.name }}</h3>
      
      <div class="flex items-center space-x-1">
        <button
          @click.stop="$emit('edit', task)"
          class="text-gray-400 hover:text-blue-600 transition-colors"
          title="Edit task"
        >
          <Edit class="w-4 h-4" />
        </button>
        
        <button
          @click.stop="$emit('delete', task)"
          class="text-gray-400 hover:text-red-600 transition-colors"
          title="Delete task"
        >
          <Trash2 class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Description -->
    <p v-if="task.description" class="text-sm text-gray-600 mb-3 line-clamp-2">
      {{ task.description }}
    </p>

    <!-- Status and Priority -->
    <div class="flex items-center space-x-2 mb-3">
      <span 
        :class="[
          'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
          getStatusClasses(task.status.value)
        ]"
      >
        {{ task.status.label }}
      </span>
      
      <span 
        :class="[
          'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
          getPriorityClasses(task.priority.value)
        ]"
      >
        {{ task.priority.label }}
      </span>
    </div>

    <!-- Progress Bar -->
    <div class="mb-3">
      <div class="flex justify-between items-center mb-1">
        <span class="text-xs text-gray-500">Progress</span>
        <span class="text-xs font-medium text-gray-700">{{ task.progress_percentage }}%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2">
        <div 
          class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
          :style="{ width: `${task.progress_percentage}%` }"
        ></div>
      </div>
    </div>

    <!-- Meta Information -->
    <div class="space-y-2">
      <!-- Project -->
      <div v-if="task.project" class="flex items-center text-xs text-gray-500">
        <Folder class="w-3 h-3 mr-1" />
        <span class="truncate">{{ task.project.name }}</span>
      </div>
      
      <!-- Assignee -->
      <div v-if="task.assigned_to" class="flex items-center text-xs text-gray-500">
        <User class="w-3 h-3 mr-1" />
        <span class="truncate">{{ task.assigned_to.name }}</span>
      </div>
      
      <!-- Due Date -->
      <div v-if="task.due_date" class="flex items-center text-xs" :class="{ 'text-red-500': task.is_overdue, 'text-gray-500': !task.is_overdue }">
        <Calendar class="w-3 h-3 mr-1" />
        <span>{{ formatDate(task.due_date) }}</span>
        <span v-if="task.is_overdue" class="ml-1 font-medium">(Overdue)</span>
        <span v-else-if="task.is_due_soon" class="ml-1 font-medium text-yellow-600">(Due Soon)</span>
      </div>
      
      <!-- Time Tracking -->
      <div v-if="task.estimated_hours || task.actual_hours" class="flex items-center text-xs text-gray-500">
        <Clock class="w-3 h-3 mr-1" />
        <span>{{ task.actual_hours || 0 }}h / {{ task.estimated_hours || 0 }}h</span>
      </div>
    </div>

    <!-- Quick Actions Footer -->
    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
      <div class="flex items-center space-x-1">
        <button
          v-if="task.sub_tasks_count"
          class="text-xs text-gray-500 hover:text-gray-700"
          title="Subtasks"
        >
          <span class="flex items-center space-x-1">
            <span>{{ task.sub_tasks_count }}</span>
            <span>subtasks</span>
          </span>
        </button>
        
        <button
          v-if="task.comments_count"
          class="text-xs text-gray-500 hover:text-gray-700 ml-2"
          title="Comments"
        >
          <span class="flex items-center space-x-1">
            <span>{{ task.comments_count }}</span>
            <span>comments</span>
          </span>
        </button>
      </div>
      
      <button
        @click.stop="$emit('status-change', task, getNextStatus(task.status.value))"
        class="text-xs text-blue-600 hover:text-blue-800 font-medium"
        :title="getNextStatusLabel(task.status.value)"
      >
        {{ getNextStatusLabel(task.status.value) }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Calendar, User, Folder, Clock, Edit, Trash2 } from 'lucide-vue-next'
import type { Task } from '../types/task.types'

interface Props {
  task: Task
}

interface Emits {
  (e: 'click', task: Task): void
  (e: 'edit', task: Task): void
  (e: 'delete', task: Task): void
  (e: 'status-change', task: Task, newStatus: string): void
}

defineProps<Props>()
defineEmits<Emits>()

// Methods
const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString()
}

const getStatusClasses = (status: string): string => {
  const classes = {
    'not_started': 'bg-gray-100 text-gray-800',
    'in_progress': 'bg-blue-100 text-blue-800',
    'review': 'bg-yellow-100 text-yellow-800',
    'completed': 'bg-green-100 text-green-800',
    'cancelled': 'bg-red-100 text-red-800',
    'on_hold': 'bg-orange-100 text-orange-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPriorityClasses = (priority: string): string => {
  const classes = {
    'low': 'bg-green-100 text-green-800',
    'medium': 'bg-yellow-100 text-yellow-800',
    'high': 'bg-orange-100 text-orange-800',
    'critical': 'bg-red-100 text-red-800'
  }
  return classes[priority] || 'bg-gray-100 text-gray-800'
}

const getNextStatus = (currentStatus: string): string => {
  const statusFlow = {
    'not_started': 'in_progress',
    'in_progress': 'review',
    'review': 'completed',
    'completed': 'completed',
    'cancelled': 'not_started',
    'on_hold': 'in_progress'
  }
  return statusFlow[currentStatus] || 'in_progress'
}

const getNextStatusLabel = (currentStatus: string): string => {
  const labels = {
    'not_started': 'Start Task',
    'in_progress': 'Send for Review',
    'review': 'Complete Task',
    'completed': 'Completed',
    'cancelled': 'Restart Task',
    'on_hold': 'Resume Task'
  }
  return labels[currentStatus] || 'Update Status'
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>