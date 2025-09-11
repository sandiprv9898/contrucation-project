import { ref, reactive } from 'vue'

export interface Notification {
  id: string
  type: 'success' | 'error' | 'warning' | 'info'
  title: string
  message: string
  duration?: number
  persistent?: boolean
}

// Global notification state
const notifications = ref<Notification[]>([])
const timeouts = new Map<string, NodeJS.Timeout>()

export const useNotifications = () => {
  const showNotification = (
    type: Notification['type'],
    title: string,
    message: string,
    options: { duration?: number; persistent?: boolean } = {}
  ) => {
    const id = Date.now().toString() + Math.random().toString(36).substr(2, 9)
    const duration = options.duration ?? 5000
    const persistent = options.persistent ?? false

    const notification: Notification = {
      id,
      type,
      title,
      message,
      duration,
      persistent
    }

    // Add to notifications list
    notifications.value.unshift(notification)

    // Auto-remove after duration (unless persistent)
    if (!persistent && duration > 0) {
      const timeout = setTimeout(() => {
        hideNotification(id)
      }, duration)
      
      timeouts.set(id, timeout)
    }

    return id
  }

  const hideNotification = (id: string) => {
    // Remove from notifications
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }

    // Clear timeout
    const timeout = timeouts.get(id)
    if (timeout) {
      clearTimeout(timeout)
      timeouts.delete(id)
    }
  }

  const hideAllNotifications = () => {
    notifications.value = []
    timeouts.forEach(timeout => clearTimeout(timeout))
    timeouts.clear()
  }

  // Convenience methods
  const showSuccess = (title: string, message: string, options?: { duration?: number; persistent?: boolean }) => {
    return showNotification('success', title, message, options)
  }

  const showError = (title: string, message: string, options?: { duration?: number; persistent?: boolean }) => {
    return showNotification('error', title, message, options)
  }

  const showWarning = (title: string, message: string, options?: { duration?: number; persistent?: boolean }) => {
    return showNotification('warning', title, message, options)
  }

  const showInfo = (title: string, message: string, options?: { duration?: number; persistent?: boolean }) => {
    return showNotification('info', title, message, options)
  }

  return {
    notifications,
    showNotification,
    hideNotification,
    hideAllNotifications,
    showSuccess,
    showError,
    showWarning,
    showInfo
  }
}

// Default export for direct usage
export default useNotifications