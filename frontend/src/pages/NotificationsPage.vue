<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
            <p class="text-gray-600 mt-1">Stay updated with your tasks and projects</p>
          </div>
          
          <div class="flex items-center gap-4">
            <!-- Stats -->
            <div class="flex items-center gap-6 text-sm">
              <div class="text-center">
                <div class="font-semibold text-gray-900">{{ unreadCount }}</div>
                <div class="text-gray-500">Unread</div>
              </div>
              <div class="text-center">
                <div class="font-semibold text-gray-900">{{ notifications.length }}</div>
                <div class="text-gray-500">Total</div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
              <button
                v-if="hasUnread"
                @click="markAllAsRead"
                :disabled="loading"
                class="px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors disabled:opacity-50"
              >
                Mark all read
              </button>
              
              <button
                @click="refreshNotifications"
                :disabled="loading"
                class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-50"
              >
                <RotateCcw class="h-5 w-5" :class="{ 'animate-spin': loading }" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="mb-6">
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700">Filter:</label>
            <select
              v-model="selectedFilter"
              @change="applyFilter"
              class="text-sm border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="all">All notifications</option>
              <option value="unread">Unread only</option>
              <option value="assignment">Assignments</option>
              <option value="due_date">Due dates</option>
              <option value="status_change">Status changes</option>
              <option value="comment">Comments</option>
              <option value="time_log">Time logs</option>
              <option value="attachment">Attachments</option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700">Sort:</label>
            <select
              v-model="selectedSort"
              @change="applySort"
              class="text-sm border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="newest">Newest first</option>
              <option value="oldest">Oldest first</option>
              <option value="unread">Unread first</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading && notifications.length === 0" class="text-center py-12">
        <div class="animate-spin h-8 w-8 border-2 border-blue-500 border-t-transparent rounded-full mx-auto"></div>
        <p class="mt-4 text-gray-500">Loading notifications...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredNotifications.length === 0" class="text-center py-12">
        <Bell class="h-16 w-16 mx-auto mb-4 text-gray-300" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">
          {{ selectedFilter === 'all' ? 'No notifications yet' : 'No matching notifications' }}
        </h3>
        <p class="text-gray-500">
          {{ selectedFilter === 'all' 
            ? "You'll see notifications here when there's activity on your tasks." 
            : 'Try adjusting your filters to see more notifications.' 
          }}
        </p>
      </div>

      <!-- Notifications List -->
      <div v-else class="space-y-1">
        <div
          v-for="notification in paginatedNotifications"
          :key="notification.id"
          class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-sm transition-shadow cursor-pointer group"
          :class="{ 'border-l-4 border-l-blue-500 bg-blue-50': !notification.is_read }"
          @click="handleNotificationClick(notification)"
        >
          <div class="flex items-start gap-4">
            <!-- Icon -->
            <div
              class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center"
              :class="getIconBackground(notification.type)"
            >
              <component
                :is="getIconComponent(notification.type)"
                class="h-6 w-6 text-white"
              />
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <!-- Title & Badge -->
                  <div class="flex items-center gap-2 mb-2">
                    <h3 class="text-lg font-semibold text-gray-900">
                      {{ notification.title }}
                    </h3>
                    <span
                      v-if="!notification.is_read"
                      class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"
                    >
                      New
                    </span>
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                      :class="getTypeBadgeClass(notification.type)"
                    >
                      {{ getTypeLabel(notification.type) }}
                    </span>
                  </div>

                  <!-- Message -->
                  <p
                    v-if="notification.message"
                    class="text-gray-600 mb-3"
                  >
                    {{ notification.message }}
                  </p>

                  <!-- Task Info -->
                  <div v-if="notification.task" class="flex items-center gap-2 text-sm text-gray-500 mb-3">
                    <span class="font-medium">Task:</span>
                    <span>{{ notification.task.name }}</span>
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                      :class="getStatusBadgeClass(notification.task.status)"
                    >
                      {{ notification.task.status }}
                    </span>
                  </div>

                  <!-- Time -->
                  <div class="flex items-center gap-4 text-sm text-gray-500">
                    <span>{{ formatTime(notification.created_at) }}</span>
                    <span v-if="notification.read_at">
                      Read {{ formatTime(notification.read_at) }}
                    </span>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button
                    v-if="!notification.is_read"
                    @click.stop="markAsRead(notification.id)"
                    class="p-2 text-gray-400 hover:text-blue-500 rounded-lg hover:bg-blue-50 transition-colors"
                    title="Mark as read"
                  >
                    <Check class="h-4 w-4" />
                  </button>

                  <button
                    @click.stop="deleteNotification(notification.id)"
                    class="p-2 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors"
                    title="Delete notification"
                  >
                    <Trash2 class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="mt-8">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-500">
            Showing {{ ((currentPage - 1) * itemsPerPage) + 1 }} to 
            {{ Math.min(currentPage * itemsPerPage, filteredNotifications.length) }} of 
            {{ filteredNotifications.length }} notifications
          </div>

          <div class="flex items-center gap-2">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>

            <div class="flex items-center gap-1">
              <button
                v-for="page in visiblePages"
                :key="page"
                @click="currentPage = page"
                class="px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                :class="page === currentPage 
                  ? 'bg-blue-500 text-white' 
                  : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
              >
                {{ page }}
              </button>
            </div>

            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotifications } from '@/modules/tasks/composables/useNotifications'
import { 
  Bell, 
  RotateCcw, 
  Check, 
  Trash2,
  UserPlus, 
  Clock, 
  RefreshCw, 
  MessageCircle, 
  Timer, 
  Paperclip 
} from 'lucide-vue-next'

const router = useRouter()
const {
  loading,
  notifications,
  unreadCount,
  hasUnread,
  getNotifications,
  markAsRead,
  markAllAsRead,
  deleteNotification,
  formatTime,
} = useNotifications()

// Filter and pagination
const selectedFilter = ref('all')
const selectedSort = ref('newest')
const currentPage = ref(1)
const itemsPerPage = ref(10)

const filteredNotifications = computed(() => {
  let filtered = [...notifications.value]

  // Apply filter
  if (selectedFilter.value !== 'all') {
    if (selectedFilter.value === 'unread') {
      filtered = filtered.filter(n => !n.is_read)
    } else {
      filtered = filtered.filter(n => n.type === selectedFilter.value)
    }
  }

  // Apply sort
  switch (selectedSort.value) {
    case 'oldest':
      filtered.sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
      break
    case 'unread':
      filtered.sort((a, b) => {
        if (a.is_read === b.is_read) {
          return new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
        }
        return a.is_read ? 1 : -1
      })
      break
    default: // newest
      filtered.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
  }

  return filtered
})

const totalPages = computed(() => Math.ceil(filteredNotifications.value.length / itemsPerPage.value))

const paginatedNotifications = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredNotifications.value.slice(start, end)
})

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end = Math.min(totalPages.value, currentPage.value + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

const refreshNotifications = async () => {
  await getNotifications()
}

const applyFilter = () => {
  currentPage.value = 1
}

const applySort = () => {
  currentPage.value = 1
}

const handleNotificationClick = async (notification: any) => {
  if (!notification.is_read) {
    await markAsRead(notification.id)
  }

  if (notification.task_id) {
    router.push(`/tasks/${notification.task_id}`)
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
    case 'status_change': return 'Status'
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
    case 'completed': return 'bg-green-100 text-green-800'
    case 'in_progress': return 'bg-blue-100 text-blue-800'
    case 'pending': return 'bg-yellow-100 text-yellow-800'
    case 'cancelled': return 'bg-red-100 text-red-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

onMounted(() => {
  getNotifications()
})
</script>