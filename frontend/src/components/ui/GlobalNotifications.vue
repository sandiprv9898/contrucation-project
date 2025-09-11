<template>
  <div class="fixed top-4 right-4 z-50 space-y-2 max-w-sm">
    <TransitionGroup
      name="notification"
      tag="div"
      class="space-y-2"
    >
      <div
        v-for="notification in notifications"
        :key="notification.id"
        :class="[
          'rounded-lg shadow-lg border-l-4 p-4 transition-all duration-300 transform',
          'max-w-sm w-full backdrop-blur-sm',
          getNotificationClasses(notification.type)
        ]"
      >
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <component
              :is="getNotificationIcon(notification.type)"
              :class="[
                'w-5 h-5',
                getIconClasses(notification.type)
              ]"
            />
          </div>
          <div class="ml-3 flex-1 min-w-0">
            <h3
              :class="[
                'text-sm font-medium',
                getTitleClasses(notification.type)
              ]"
            >
              {{ notification.title }}
            </h3>
            <p
              :class="[
                'text-sm mt-1',
                getMessageClasses(notification.type)
              ]"
            >
              {{ notification.message }}
            </p>
          </div>
          <div class="ml-4 flex-shrink-0">
            <button
              @click="hideNotification(notification.id)"
              :class="[
                'rounded-md inline-flex text-sm p-1 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors',
                getCloseButtonClasses(notification.type)
              ]"
            >
              <X class="w-4 h-4" />
            </button>
          </div>
        </div>
        
        <!-- Progress bar for non-persistent notifications -->
        <div
          v-if="!notification.persistent && notification.duration"
          class="mt-2 w-full bg-gray-200 rounded-full h-1 overflow-hidden"
        >
          <div
            :class="[
              'h-full rounded-full transition-all ease-linear',
              getProgressBarClasses(notification.type)
            ]"
            :style="{
              width: '100%',
              animation: `shrink ${notification.duration}ms linear forwards`
            }"
          ></div>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
import { CheckCircle, X as XIcon, AlertTriangle, Info, AlertCircle } from 'lucide-vue-next'
import { useNotifications } from '@/composables/useNotifications'
import type { Notification } from '@/composables/useNotifications'

const X = XIcon

const { notifications, hideNotification } = useNotifications()

const getNotificationClasses = (type: Notification['type']) => {
  switch (type) {
    case 'success':
      return 'bg-green-50/95 border-green-400 shadow-green-100'
    case 'error':
      return 'bg-red-50/95 border-red-400 shadow-red-100'
    case 'warning':
      return 'bg-yellow-50/95 border-yellow-400 shadow-yellow-100'
    case 'info':
    default:
      return 'bg-blue-50/95 border-blue-400 shadow-blue-100'
  }
}

const getNotificationIcon = (type: Notification['type']) => {
  switch (type) {
    case 'success':
      return CheckCircle
    case 'error':
      return AlertCircle
    case 'warning':
      return AlertTriangle
    case 'info':
    default:
      return Info
  }
}

const getIconClasses = (type: Notification['type']) => {
  switch (type) {
    case 'success':
      return 'text-green-400'
    case 'error':
      return 'text-red-400'
    case 'warning':
      return 'text-yellow-400'
    case 'info':
    default:
      return 'text-blue-400'
  }
}

const getTitleClasses = (type: Notification['type']) => {
  switch (type) {
    case 'success':
      return 'text-green-800'
    case 'error':
      return 'text-red-800'
    case 'warning':
      return 'text-yellow-800'
    case 'info':
    default:
      return 'text-blue-800'
  }
}

const getMessageClasses = (type: Notification['type']) => {
  switch (type) {
    case 'success':
      return 'text-green-700'
    case 'error':
      return 'text-red-700'
    case 'warning':
      return 'text-yellow-700'
    case 'info':
    default:
      return 'text-blue-700'
  }
}

const getCloseButtonClasses = (type: Notification['type']) => {
  switch (type) {
    case 'success':
      return 'text-green-500 hover:text-green-600 focus:ring-green-500'
    case 'error':
      return 'text-red-500 hover:text-red-600 focus:ring-red-500'
    case 'warning':
      return 'text-yellow-500 hover:text-yellow-600 focus:ring-yellow-500'
    case 'info':
    default:
      return 'text-blue-500 hover:text-blue-600 focus:ring-blue-500'
  }
}

const getProgressBarClasses = (type: Notification['type']) => {
  switch (type) {
    case 'success':
      return 'bg-green-400'
    case 'error':
      return 'bg-red-400'
    case 'warning':
      return 'bg-yellow-400'
    case 'info':
    default:
      return 'bg-blue-400'
  }
}
</script>

<style scoped>
/* Notification transition animations */
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.notification-move {
  transition: transform 0.3s ease;
}

/* Progress bar animation */
@keyframes shrink {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}
</style>