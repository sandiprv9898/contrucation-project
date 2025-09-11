<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <div class="flex items-center gap-3">
              <div class="p-2 bg-blue-500 rounded-lg">
                <Bell class="h-6 w-6 text-white" />
              </div>
              <div>
                <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
                <p class="text-gray-600">Stay updated with your tasks and projects</p>
              </div>
            </div>
          </div>
          
          <div class="flex items-center gap-4">
            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 text-center">
              <div class="bg-white rounded-lg px-4 py-3 shadow-sm border">
                <div class="text-2xl font-bold text-blue-600">{{ unreadCount }}</div>
                <div class="text-xs text-gray-500">Unread</div>
              </div>
              <div class="bg-white rounded-lg px-4 py-3 shadow-sm border">
                <div class="text-2xl font-bold text-gray-900">{{ notifications.length }}</div>
                <div class="text-xs text-gray-500">Total</div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="flex items-center gap-2">
              <button
                @click="refreshNotifications"
                :disabled="loading"
                class="p-2 text-gray-500 hover:text-gray-700 hover:bg-white rounded-lg transition-colors disabled:opacity-50 border border-gray-200"
                title="Refresh notifications"
              >
                <RotateCcw class="h-5 w-5" :class="{ 'animate-spin': loading }" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Controls -->
      <div class="mb-6 bg-white rounded-lg p-4 shadow-sm border">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <!-- Filters & Sort -->
          <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
              <Filter class="h-4 w-4 text-gray-500" />
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
              <ArrowUpDown class="h-4 w-4 text-gray-500" />
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

            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-500">View:</span>
              <div class="flex bg-gray-100 rounded-lg p-1">
                <button
                  @click="viewMode = 'list'"
                  :class="viewMode === 'list' 
                    ? 'bg-white text-gray-900 shadow-sm' 
                    : 'text-gray-500 hover:text-gray-700'"
                  class="px-3 py-1 text-sm font-medium rounded-md transition-colors"
                >
                  <List class="h-4 w-4" />
                </button>
                <button
                  @click="viewMode = 'compact'"
                  :class="viewMode === 'compact' 
                    ? 'bg-white text-gray-900 shadow-sm' 
                    : 'text-gray-500 hover:text-gray-700'"
                  class="px-3 py-1 text-sm font-medium rounded-md transition-colors"
                >
                  <Grid3X3 class="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>

          <!-- Bulk Actions -->
          <div v-if="selectedNotifications.length > 0" class="flex items-center gap-2">
            <span class="text-sm text-gray-600">{{ selectedNotifications.length }} selected</span>
            <button
              @click="markSelectedAsRead"
              :disabled="loading"
              class="px-3 py-2 text-sm font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors disabled:opacity-50"
            >
              Mark Read
            </button>
            <button
              @click="selectedNotifications = []"
              class="px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-lg transition-colors"
            >
              Cancel
            </button>
          </div>

          <!-- Global Actions -->
          <div v-else class="flex items-center gap-2">
            <button
              v-if="hasUnread"
              @click="markAllAsRead"
              :disabled="loading"
              class="px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors disabled:opacity-50"
            >
              Mark All Read
            </button>
            
            <button
              @click="toggleSelectAll"
              class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-lg transition-colors"
            >
              {{ selectedNotifications.length === filteredNotifications.length && filteredNotifications.length > 0 ? 'Deselect All' : 'Select All' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading && notifications.length === 0" class="text-center py-16">
        <div class="animate-spin h-12 w-12 border-2 border-blue-500 border-t-transparent rounded-full mx-auto mb-4"></div>
        <p class="text-lg text-gray-500">Loading notifications...</p>
        <p class="text-sm text-gray-400 mt-1">This may take a moment</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredNotifications.length === 0" class="text-center py-16">
        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
          <Bell class="h-12 w-12 text-gray-400" />
        </div>
        <h3 class="text-xl font-medium text-gray-900 mb-2">
          {{ selectedFilter === 'all' ? 'No notifications yet' : 'No matching notifications' }}
        </h3>
        <p class="text-gray-500 max-w-sm mx-auto">
          {{ selectedFilter === 'all' 
            ? "You'll receive notifications here when there's activity on your tasks and projects." 
            : 'Try adjusting your filters to see more notifications.' 
          }}
        </p>
        <div class="mt-6">
          <button
            v-if="selectedFilter !== 'all'"
            @click="selectedFilter = 'all'; applyFilter()"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-800"
          >
            <RotateCcw class="h-4 w-4" />
            Clear filters
          </button>
        </div>
      </div>

      <!-- Notifications List -->
      <div v-else class="space-y-3">
        <!-- List View -->
        <div v-if="viewMode === 'list'" class="space-y-3">
          <div
            v-for="notification in paginatedNotifications"
            :key="notification.id"
            class="bg-white rounded-lg border border-gray-200 hover:shadow-md transition-all duration-200 group"
            :class="{ 
              'border-l-4 border-l-blue-500 bg-blue-50/30': !notification.is_read,
              'ring-2 ring-blue-200': selectedNotifications.includes(notification.id)
            }"
          >
            <div class="p-6">
              <div class="flex items-start gap-4">
                <!-- Checkbox -->
                <div class="flex items-center">
                  <input
                    type="checkbox"
                    :value="notification.id"
                    v-model="selectedNotifications"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  >
                </div>

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
                    <div class="flex-1 cursor-pointer" @click="showNotificationDetails(notification)">
                      <!-- Title & Badges -->
                      <div class="flex items-center gap-2 mb-2">
                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                          {{ notification.title }}
                        </h3>
                        <span
                          v-if="!notification.is_read"
                          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 animate-pulse"
                        >
                          New
                        </span>
                        <span
                          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                          :class="getTypeBadgeClass(notification.type)"
                        >
                          {{ getTypeLabel(notification.type) }}
                        </span>
                      </div>

                      <!-- Message -->
                      <p
                        v-if="notification.message"
                        class="text-gray-600 mb-3 line-clamp-2"
                      >
                        {{ notification.message }}
                      </p>

                      <!-- Task Info -->
                      <div v-if="notification.task" class="flex items-center gap-2 text-sm text-gray-500 mb-3">
                        <Briefcase class="h-4 w-4" />
                        <span class="font-medium">{{ notification.task.name }}</span>
                        <span
                          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                          :class="getStatusBadgeClass(notification.task.status)"
                        >
                          {{ formatTaskStatus(notification.task.status) }}
                        </span>
                      </div>

                      <!-- Time & Read Status -->
                      <div class="flex items-center gap-4 text-sm text-gray-500">
                        <div class="flex items-center gap-1">
                          <Clock class="h-4 w-4" />
                          <span>{{ formatTime(notification.created_at) }}</span>
                        </div>
                        <span v-if="notification.read_at" class="flex items-center gap-1">
                          <CheckCircle class="h-4 w-4 text-green-500" />
                          Read {{ formatTime(notification.read_at) }}
                        </span>
                      </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                      <button
                        @click.stop="showNotificationDetails(notification)"
                        class="p-2 text-gray-400 hover:text-blue-500 rounded-lg hover:bg-blue-50 transition-colors"
                        title="View details"
                      >
                        <Eye class="h-4 w-4" />
                      </button>

                      <button
                        v-if="!notification.is_read"
                        @click.stop="markAsRead(notification.id)"
                        class="p-2 text-gray-400 hover:text-green-500 rounded-lg hover:bg-green-50 transition-colors"
                        title="Mark as read"
                      >
                        <Check class="h-4 w-4" />
                      </button>

                      <button
                        v-if="notification.task_id"
                        @click.stop="goToTask(notification)"
                        class="p-2 text-gray-400 hover:text-purple-500 rounded-lg hover:bg-purple-50 transition-colors"
                        title="Go to task"
                      >
                        <ExternalLink class="h-4 w-4" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Compact View -->
        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="notification in paginatedNotifications"
            :key="notification.id"
            class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-all duration-200 cursor-pointer group"
            :class="{ 
              'border-l-4 border-l-blue-500 bg-blue-50/30': !notification.is_read,
              'ring-2 ring-blue-200': selectedNotifications.includes(notification.id)
            }"
            @click="showNotificationDetails(notification)"
          >
            <div class="flex items-start gap-3">
              <input
                type="checkbox"
                :value="notification.id"
                v-model="selectedNotifications"
                @click.stop
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1"
              >

              <div
                class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                :class="getIconBackground(notification.type)"
              >
                <component
                  :is="getIconComponent(notification.type)"
                  class="h-5 w-5 text-white"
                />
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1 mb-1">
                  <h3 class="font-medium text-gray-900 text-sm truncate group-hover:text-blue-600 transition-colors">
                    {{ notification.title }}
                  </h3>
                  <span
                    v-if="!notification.is_read"
                    class="w-2 h-2 bg-blue-500 rounded-full animate-pulse flex-shrink-0"
                  ></span>
                </div>

                <p class="text-xs text-gray-600 mb-2 line-clamp-2">
                  {{ notification.message || 'No message' }}
                </p>

                <div class="flex items-center justify-between">
                  <span
                    class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium"
                    :class="getTypeBadgeClass(notification.type)"
                  >
                    {{ getTypeLabel(notification.type) }}
                  </span>
                  
                  <span class="text-xs text-gray-500">
                    {{ formatTime(notification.created_at) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="mt-8 bg-white rounded-lg border border-gray-200 px-6 py-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="text-sm text-gray-500">
            Showing {{ ((currentPage - 1) * itemsPerPage) + 1 }} to 
            {{ Math.min(currentPage * itemsPerPage, filteredNotifications.length) }} of 
            {{ filteredNotifications.length }} notifications
          </div>

          <div class="flex items-center gap-2">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors"
            >
              <ChevronLeft class="h-4 w-4" />
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
                  : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 border border-gray-300'"
              >
                {{ page }}
              </button>
            </div>

            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors"
            >
              Next
              <ChevronRight class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Notification Details Modal -->
    <NotificationDetailsModal
      :is-open="showDetailsModal"
      :notification="selectedNotificationForDetails"
      @close="showDetailsModal = false"
      @updated="refreshNotifications"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotifications } from '@/modules/tasks/composables/useNotifications'
import NotificationDetailsModal from '@/components/notifications/NotificationDetailsModal.vue'
import { 
  Bell, 
  RotateCcw, 
  Check,
  UserPlus, 
  Clock, 
  RefreshCw, 
  MessageCircle, 
  Timer, 
  Paperclip,
  Filter,
  ArrowUpDown,
  List,
  Grid3X3,
  Eye,
  ExternalLink,
  Briefcase,
  CheckCircle,
  ChevronLeft,
  ChevronRight
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
  formatTime,
} = useNotifications()

// View and interaction state
const selectedFilter = ref('all')
const selectedSort = ref('newest')
const currentPage = ref(1)
const itemsPerPage = ref(12)
const viewMode = ref('list') // 'list' | 'compact'
const selectedNotifications = ref<string[]>([])
const showDetailsModal = ref(false)
const selectedNotificationForDetails = ref(null)

// Computed properties
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

// Methods
const refreshNotifications = async () => {
  await getNotifications()
}

const applyFilter = () => {
  currentPage.value = 1
  selectedNotifications.value = []
}

const applySort = () => {
  currentPage.value = 1
}

const showNotificationDetails = (notification: any) => {
  selectedNotificationForDetails.value = notification
  showDetailsModal.value = true
}

const goToTask = async (notification: any) => {
  if (!notification.is_read) {
    await markAsRead(notification.id)
  }
  
  if (notification.task_id) {
    router.push(`/app/tasks/${notification.task_id}`)
  }
}

const toggleSelectAll = () => {
  if (selectedNotifications.value.length === filteredNotifications.value.length && filteredNotifications.value.length > 0) {
    selectedNotifications.value = []
  } else {
    selectedNotifications.value = filteredNotifications.value.map(n => n.id)
  }
}

const markSelectedAsRead = async () => {
  for (const id of selectedNotifications.value) {
    await markAsRead(id)
  }
  selectedNotifications.value = []
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

// Icon and styling helpers
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

onMounted(() => {
  getNotifications()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>