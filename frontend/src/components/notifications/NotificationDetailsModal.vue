<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 overflow-y-auto"
      @click="closeModal"
    >
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black/50 transition-opacity"></div>
      
      <!-- Modal -->
      <div class="flex min-h-full items-center justify-center p-4">
        <div
          class="relative w-full max-w-lg transform rounded-xl bg-white shadow-xl transition-all"
          @click.stop
        >
          <!-- Header -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
              <div
                class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                :class="getIconBackground(notification?.type || '')"
              >
                <component
                  :is="getIconComponent(notification?.type || '')"
                  class="h-5 w-5 text-white"
                />
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900">
                  {{ notification?.title }}
                </h3>
                <div class="flex items-center gap-2 mt-1">
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                    :class="getTypeBadgeClass(notification?.type || '')"
                  >
                    {{ getTypeLabel(notification?.type || '') }}
                  </span>
                  <span
                    v-if="!notification?.is_read"
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"
                  >
                    Unread
                  </span>
                </div>
              </div>
            </div>
            
            <button
              @click="closeModal"
              class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors"
            >
              <X class="h-5 w-5" />
            </button>
          </div>
          
          <!-- Content -->
          <div class="p-6 space-y-6">
            <!-- Message -->
            <div v-if="notification?.message">
              <h4 class="text-sm font-medium text-gray-900 mb-2">Message</h4>
              <p class="text-gray-700 bg-gray-50 rounded-lg p-3">
                {{ notification.message }}
              </p>
            </div>
            
            <!-- Task Information -->
            <div v-if="notification?.task" class="space-y-3">
              <h4 class="text-sm font-medium text-gray-900">Related Task</h4>
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-start justify-between mb-2">
                  <h5 class="font-medium text-gray-900">{{ notification.task.name }}</h5>
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                    :class="getStatusBadgeClass(notification.task.status)"
                  >
                    {{ formatTaskStatus(notification.task.status) }}
                  </span>
                </div>
                
                <button
                  @click="goToTask"
                  class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 font-medium"
                >
                  <ExternalLink class="h-4 w-4" />
                  View Task Details
                </button>
              </div>
            </div>
            
            <!-- Additional Data -->
            <div v-if="notification?.data && Object.keys(notification.data).length > 0">
              <h4 class="text-sm font-medium text-gray-900 mb-2">Additional Details</h4>
              <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                <div
                  v-for="(value, key) in notification.data"
                  :key="key"
                  class="flex justify-between text-sm"
                >
                  <span class="text-gray-600 capitalize">{{ String(key).replace('_', ' ') }}:</span>
                  <span class="text-gray-900 font-medium">{{ formatDataValue(value) }}</span>
                </div>
              </div>
            </div>
            
            <!-- Timeline -->
            <div>
              <h4 class="text-sm font-medium text-gray-900 mb-3">Timeline</h4>
              <div class="space-y-3">
                <div class="flex items-center gap-3">
                  <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                  <div class="flex-1 text-sm">
                    <span class="text-gray-600">Created:</span>
                    <span class="text-gray-900 ml-1">{{ formatTime(notification?.created_at || '') }}</span>
                  </div>
                </div>
                
                <div v-if="notification?.read_at" class="flex items-center gap-3">
                  <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                  <div class="flex-1 text-sm">
                    <span class="text-gray-600">Read:</span>
                    <span class="text-gray-900 ml-1">{{ formatTime(notification.read_at) }}</span>
                  </div>
                </div>
                
                <div v-else class="flex items-center gap-3">
                  <div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
                  <div class="flex-1 text-sm text-yellow-600">
                    Not yet read
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Footer -->
          <div class="flex items-center justify-between p-6 border-t border-gray-200 bg-gray-50">
            <div class="flex items-center gap-2">
              <button
                v-if="!notification?.is_read"
                @click="markAsRead"
                :disabled="loading"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-lg transition-colors disabled:opacity-50"
              >
                <Check class="h-4 w-4" />
                Mark as Read
              </button>
            </div>
            
            <button
              @click="closeModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useNotifications } from '@/modules/tasks/composables/useNotifications'
import { 
  X, 
  ExternalLink, 
  Check,
  UserPlus, 
  Clock, 
  RefreshCw, 
  MessageCircle, 
  Timer, 
  Paperclip,
  Bell
} from 'lucide-vue-next'

interface NotificationDetailsModalProps {
  isOpen: boolean
  notification: any | null
}

const props = defineProps<NotificationDetailsModalProps>()

const emit = defineEmits<{
  close: []
  updated: []
}>()

const router = useRouter()
const { loading, markAsRead: markNotificationAsRead, formatTime } = useNotifications()

const closeModal = () => {
  emit('close')
}

const goToTask = async () => {
  if (props.notification?.task_id) {
    // Mark as read before navigating
    if (!props.notification.is_read) {
      await markAsRead()
    }
    
    router.push(`/app/tasks/${props.notification.task_id}`)
    closeModal()
  }
}

const markAsRead = async () => {
  if (props.notification?.id) {
    await markNotificationAsRead(props.notification.id)
    emit('updated')
  }
}

const formatTaskStatus = (status: string): string => {
  switch (status) {
    case 'in_progress': return 'In Progress'
    case 'pending': return 'Pending'
    case 'completed': return 'Completed'
    case 'cancelled': return 'Cancelled'
    case 'on_hold': return 'On Hold'
    case 'review': return 'Under Review'
    case 'approved': return 'Approved'
    case 'rejected': return 'Rejected'
    default: return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
  }
}

const getIconComponent = (type: string) => {
  switch (type) {
    case 'assignment': return UserPlus
    case 'due_date': return Clock
    case 'status_change': return RefreshCw
    case 'comment': return MessageCircle
    case 'time_log': return Timer
    case 'attachment': return Paperclip
    default: return Bell
  }
}

const getIconBackground = (type: string): string => {
  switch (type) {
    case 'assignment': return 'bg-blue-500'
    case 'due_date': return 'bg-orange-500'
    case 'status_change': return 'bg-green-500'
    case 'comment': return 'bg-purple-500'
    case 'time_log': return 'bg-indigo-500'
    case 'attachment': return 'bg-gray-500'
    default: return 'bg-gray-500'
  }
}

const getTypeLabel = (type: string): string => {
  switch (type) {
    case 'assignment': return 'Assignment'
    case 'due_date': return 'Due Date'
    case 'status_change': return 'Status Change'
    case 'comment': return 'Comment'
    case 'time_log': return 'Time Log'
    case 'attachment': return 'Attachment'
    default: return 'Notification'
  }
}

const getTypeBadgeClass = (type: string): string => {
  switch (type) {
    case 'assignment': return 'bg-blue-100 text-blue-800'
    case 'due_date': return 'bg-orange-100 text-orange-800'
    case 'status_change': return 'bg-green-100 text-green-800'
    case 'comment': return 'bg-purple-100 text-purple-800'
    case 'time_log': return 'bg-indigo-100 text-indigo-800'
    case 'attachment': return 'bg-gray-100 text-gray-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const getStatusBadgeClass = (status: string): string => {
  switch (status) {
    case 'completed': return 'bg-green-100 text-green-800 border border-green-200'
    case 'in_progress': return 'bg-blue-100 text-blue-800 border border-blue-200'
    case 'pending': return 'bg-amber-100 text-amber-800 border border-amber-200'
    case 'cancelled': return 'bg-red-100 text-red-800 border border-red-200'
    case 'on_hold': return 'bg-orange-100 text-orange-800 border border-orange-200'
    case 'review': return 'bg-purple-100 text-purple-800 border border-purple-200'
    case 'approved': return 'bg-emerald-100 text-emerald-800 border border-emerald-200'
    case 'rejected': return 'bg-rose-100 text-rose-800 border border-rose-200'
    default: return 'bg-gray-100 text-gray-800 border border-gray-200'
  }
}

const formatDataValue = (value: any): string => {
  if (typeof value === 'object') {
    return JSON.stringify(value)
  }
  return String(value)
}
</script>