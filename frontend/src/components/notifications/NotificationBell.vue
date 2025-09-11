<template>
  <div class="relative">
    <!-- Notification Bell Button -->
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
      :class="{ 'text-blue-600': hasUnread }"
    >
      <Bell class="h-6 w-6" />
      
      <!-- Unread Badge -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-medium"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Notifications Dropdown -->
    <Teleport to="body">
      <div
        v-if="showDropdown"
        class="fixed inset-0 z-50"
        @click="closeDropdown"
      >
        <div
          class="absolute right-4 top-16 w-96 bg-white rounded-lg shadow-xl border border-gray-200 max-h-96 flex flex-col"
          @click.stop
        >
          <!-- Header -->
          <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
            <div class="flex items-center gap-2">
              <button
                v-if="hasUnread"
                @click="markAllAsRead"
                :disabled="loading"
                class="text-sm text-blue-600 hover:text-blue-800 disabled:opacity-50"
              >
                Mark all read
              </button>
              <button
                @click="viewAllNotifications"
                class="text-sm text-gray-500 hover:text-gray-700"
              >
                View all
              </button>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="p-4 text-center text-gray-500">
            <div class="animate-spin h-6 w-6 border-2 border-blue-500 border-t-transparent rounded-full mx-auto"></div>
            <p class="mt-2">Loading notifications...</p>
          </div>

          <!-- Empty State -->
          <div v-else-if="notifications.length === 0" class="p-8 text-center text-gray-500">
            <Bell class="h-12 w-12 mx-auto mb-4 text-gray-300" />
            <p class="text-lg font-medium">No notifications</p>
            <p class="text-sm">You're all caught up!</p>
          </div>

          <!-- Notifications List -->
          <div v-else class="flex-1 overflow-y-auto">
            <div
              v-for="notification in displayedNotifications"
              :key="notification.id"
              class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
              :class="{ 'bg-blue-50': !notification.is_read }"
              @click="handleNotificationClick(notification)"
            >
              <div class="flex items-start gap-3">
                <!-- Icon -->
                <div
                  class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                  :class="getIconBackground(notification.type)"
                >
                  <component
                    :is="getIconComponent(notification.type)"
                    class="h-5 w-5 text-white"
                  />
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900">
                        {{ notification.title }}
                      </p>
                      <p
                        v-if="notification.message"
                        class="text-sm text-gray-600 mt-1 line-clamp-2"
                      >
                        {{ notification.message }}
                      </p>
                      <p class="text-xs text-gray-500 mt-2">
                        {{ formatTime(notification.created_at) }}
                      </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-1 ml-2">
                      <!-- Unread Indicator -->
                      <div
                        v-if="!notification.is_read"
                        class="w-2 h-2 bg-blue-500 rounded-full"
                      ></div>

                      <!-- Delete Button -->
                      <button
                        @click.stop="deleteNotification(notification.id)"
                        class="p-1 text-gray-400 hover:text-red-500 rounded transition-colors opacity-0 group-hover:opacity-100"
                      >
                        <X class="h-4 w-4" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div v-if="notifications.length > 0" class="p-3 border-t border-gray-200 text-center">
            <button
              @click="viewAllNotifications"
              class="text-sm text-blue-600 hover:text-blue-800 font-medium"
            >
              View all {{ notifications.length }} notifications
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotifications } from '@/modules/tasks/composables/useNotifications'
import { Bell, X, UserPlus, Clock, RefreshCw, MessageCircle, Timer, Paperclip } from 'lucide-vue-next'

const router = useRouter()
const {
  loading,
  notifications,
  unreadCount,
  hasUnread,
  getNotifications,
  getUnreadCount,
  markAsRead,
  markAllAsRead,
  deleteNotification,
  formatTime,
} = useNotifications()

const showDropdown = ref(false)
const refreshInterval = ref<NodeJS.Timeout | null>(null)

// Get first 5 notifications for dropdown
const displayedNotifications = computed(() => notifications.value.slice(0, 5))

const toggleDropdown = async () => {
  if (!showDropdown.value) {
    await getNotifications()
  }
  showDropdown.value = !showDropdown.value
}

const closeDropdown = () => {
  showDropdown.value = false
}

const handleNotificationClick = async (notification: any) => {
  // Mark as read if unread
  if (!notification.is_read) {
    await markAsRead(notification.id)
  }

  // Navigate to related task
  if (notification.task_id) {
    router.push(`/tasks/${notification.task_id}`)
    closeDropdown()
  }
}

const viewAllNotifications = () => {
  router.push('/app/notifications')
  closeDropdown()
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

// Refresh unread count periodically
const startRefresh = () => {
  refreshInterval.value = setInterval(() => {
    if (!showDropdown.value) {
      getUnreadCount()
    }
  }, 30000) // Refresh every 30 seconds
}

const stopRefresh = () => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
    refreshInterval.value = null
  }
}

onMounted(() => {
  getUnreadCount()
  startRefresh()
})

onUnmounted(() => {
  stopRefresh()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.group:hover .group-hover\:opacity-100 {
  opacity: 1;
}
</style>