import { ref, computed } from 'vue'
import { apiClient } from '@/modules/shared/api/client'

interface TaskNotification {
  id: string
  user_id: string
  task_id: string
  type: 'assignment' | 'due_date' | 'status_change' | 'comment' | 'time_log' | 'attachment'
  title: string
  message: string | null
  data: Record<string, any> | null
  is_read: boolean
  read_at: string | null
  created_at: string
  updated_at: string
  time_ago: string
  icon: string
  color: string
  task?: {
    id: string
    name: string
    status: string
  }
}

interface NotificationStats {
  total: number
  unread: number
  by_type: Record<string, number>
  today: number
  this_week: number
}

export function useNotifications() {
  const loading = ref(false)
  const error = ref<string | null>(null)
  const notifications = ref<TaskNotification[]>([])
  const unreadCount = ref(0)
  const stats = ref<NotificationStats | null>(null)

  // Get user's notifications
  const getNotifications = async (page = 1): Promise<TaskNotification[]> => {
    loading.value = true
    error.value = null

    try {
      const response = await apiClient.get<{
        data: TaskNotification[]
        meta: {
          current_page: number
          per_page: number
          total: number
          unread_count: number
        }
      }>('/notifications', { params: { page } })
      
      notifications.value = response.data
      unreadCount.value = response.meta.unread_count
      return response.data
    } catch (err: any) {
      error.value = err.message || 'Failed to get notifications'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Get unread count
  const getUnreadCount = async (): Promise<number> => {
    try {
      const response = await apiClient.get<{ data: { count: number } }>('/notifications/unread-count')
      unreadCount.value = response.data.count
      return response.data.count
    } catch (err: any) {
      error.value = err.message || 'Failed to get unread count'
      return 0
    }
  }

  // Mark notification as read
  const markAsRead = async (notificationId: string): Promise<void> => {
    try {
      await apiClient.put(`/notifications/${notificationId}/read`)
      
      // Update local state
      const notification = notifications.value.find(n => n.id === notificationId)
      if (notification && !notification.is_read) {
        notification.is_read = true
        notification.read_at = new Date().toISOString()
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
    } catch (err: any) {
      error.value = err.message || 'Failed to mark notification as read'
      throw err
    }
  }

  // Mark all notifications as read
  const markAllAsRead = async (): Promise<void> => {
    loading.value = true
    error.value = null

    try {
      await apiClient.post('/notifications/mark-all-read')
      
      // Update local state
      notifications.value.forEach(notification => {
        if (!notification.is_read) {
          notification.is_read = true
          notification.read_at = new Date().toISOString()
        }
      })
      unreadCount.value = 0
    } catch (err: any) {
      error.value = err.message || 'Failed to mark all notifications as read'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Delete notification
  const deleteNotification = async (notificationId: string): Promise<void> => {
    try {
      await apiClient.delete(`/notifications/${notificationId}`)
      
      // Update local state
      const index = notifications.value.findIndex(n => n.id === notificationId)
      if (index !== -1) {
        const notification = notifications.value[index]
        if (!notification.is_read) {
          unreadCount.value = Math.max(0, unreadCount.value - 1)
        }
        notifications.value.splice(index, 1)
      }
    } catch (err: any) {
      error.value = err.message || 'Failed to delete notification'
      throw err
    }
  }

  // Get notification statistics
  const getStats = async (): Promise<NotificationStats> => {
    loading.value = true
    error.value = null

    try {
      const response = await apiClient.get<{ data: NotificationStats }>('/notifications/statistics')
      stats.value = response.data
      return response.data
    } catch (err: any) {
      error.value = err.message || 'Failed to get notification statistics'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Get icon for notification type
  const getNotificationIcon = (type: string): string => {
    switch (type) {
      case 'assignment': return 'user-plus'
      case 'due_date': return 'clock'
      case 'status_change': return 'refresh-cw'
      case 'comment': return 'message-circle'
      case 'time_log': return 'timer'
      case 'attachment': return 'paperclip'
      default: return 'bell'
    }
  }

  // Get color for notification type
  const getNotificationColor = (type: string): string => {
    switch (type) {
      case 'assignment': return 'blue'
      case 'due_date': return 'orange'
      case 'status_change': return 'green'
      case 'comment': return 'purple'
      case 'time_log': return 'indigo'
      case 'attachment': return 'gray'
      default: return 'gray'
    }
  }

  // Format notification time
  const formatTime = (dateString: string): string => {
    const date = new Date(dateString)
    const now = new Date()
    const diff = now.getTime() - date.getTime()
    const minutes = Math.floor(diff / 60000)
    const hours = Math.floor(minutes / 60)
    const days = Math.floor(hours / 24)

    if (minutes < 1) return 'Just now'
    if (minutes < 60) return `${minutes}m ago`
    if (hours < 24) return `${hours}h ago`
    if (days < 7) return `${days}d ago`
    return date.toLocaleDateString()
  }

  // Computed properties
  const hasUnread = computed(() => unreadCount.value > 0)
  const unreadNotifications = computed(() => notifications.value.filter(n => !n.is_read))
  const readNotifications = computed(() => notifications.value.filter(n => n.is_read))

  return {
    // State
    loading,
    error,
    notifications,
    unreadCount,
    stats,
    
    // Computed
    hasUnread,
    unreadNotifications,
    readNotifications,
    
    // Methods
    getNotifications,
    getUnreadCount,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    getStats,
    getNotificationIcon,
    getNotificationColor,
    formatTime,
  }
}