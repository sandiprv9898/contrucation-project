import { ref, computed, onMounted, onUnmounted } from 'vue'
import { apiClient } from '@/modules/shared/api/client'
import echo from '@/plugins/echo'

interface TimeLog {
  id: string
  task_id: string
  user_id: string
  start_time: string
  end_time: string | null
  duration_minutes: number
  description: string | null
  billable: boolean
  hourly_rate: number | null
  clock_in_location_lat: number | null
  clock_in_location_lng: number | null
  clock_in_address: string | null
  clock_out_location_lat: number | null
  clock_out_location_lng: number | null
  clock_out_address: string | null
  clock_in_photos: string[]
  clock_out_photos: string[]
  activity_type: string
  is_active: boolean
  task?: {
    id: string
    name: string
  }
  user?: {
    id: string
    name: string
  }
  created_at: string
  updated_at: string
  formatted_duration: string
  duration_hours: number
  billable_amount: number
  location_distance: number | null
}

interface ClockInData {
  latitude?: number
  longitude?: number
  address?: string
  photos?: File[]
  activity_type?: string
  description?: string
}

interface ClockOutData {
  latitude?: number
  longitude?: number
  address?: string
  photos?: File[]
  description?: string
  billable?: boolean
  hourly_rate?: number
}

interface TimeTrackingStats {
  total_entries: number
  total_hours: number
  billable_hours: number
  total_earnings: number
  activity_breakdown: Record<string, { count: number, hours: number }>
  daily_hours: Record<string, number>
}

// Singleton state for active time log to prevent duplicate API calls
const globalActiveTimeLog = ref<TimeLog | null>(null)
const globalLoading = ref(false)
const globalError = ref<string | null>(null)
let activeTimeLogPromise: Promise<TimeLog | null> | null = null
let lastFetchTime = 0
const CACHE_DURATION = 30000 // 30 seconds

// Cache for task time logs to prevent duplicate API calls
const taskTimeLogsCache = new Map<string, { data: TimeLog[], timestamp: number, promise?: Promise<TimeLog[]> }>()
const TASK_LOGS_CACHE_DURATION = 60000 // 60 seconds for task logs

export function useTimeTracking() {
  const loading = ref(false)
  const error = ref<string | null>(null)
  const timeLogs = ref<TimeLog[]>([])
  const stats = ref<TimeTrackingStats | null>(null)

  // WebSocket setup for real-time updates
  let timeTrackingChannel: any = null

  const setupWebSocketListeners = () => {
    if (!timeTrackingChannel) {
      timeTrackingChannel = echo.channel('time-tracking')
        .listen('time-log.started', (event: any) => {
          console.log('Time tracking started:', event)
          globalActiveTimeLog.value = event.time_log
          lastFetchTime = Date.now()
        })
        .listen('time-log.stopped', (event: any) => {
          console.log('Time tracking stopped:', event)
          globalActiveTimeLog.value = null
          lastFetchTime = Date.now()
          
          // Invalidate task time logs cache for the affected task
          if (event.time_log?.task_id) {
            invalidateTaskTimeLogsCache(event.time_log.task_id)
          }
          
          // Add to time logs if we have a local list
          if (timeLogs.value.length > 0) {
            const existingIndex = timeLogs.value.findIndex(log => log.id === event.time_log.id)
            if (existingIndex >= 0) {
              timeLogs.value[existingIndex] = event.time_log
            } else {
              timeLogs.value.unshift(event.time_log)
            }
          }
        })
    }
  }

  const cleanupWebSocketListeners = () => {
    if (timeTrackingChannel) {
      echo.leaveChannel('time-tracking')
      timeTrackingChannel = null
    }
  }

  // Setup listeners when composable is used
  onMounted(() => {
    setupWebSocketListeners()
  })

  // Cleanup when component is unmounted
  onUnmounted(() => {
    cleanupWebSocketListeners()
  })

  // Helper function to invalidate task time logs cache
  const invalidateTaskTimeLogsCache = (taskId: string) => {
    for (const [key] of taskTimeLogsCache.entries()) {
      if (key.startsWith(`${taskId}-`)) {
        taskTimeLogsCache.delete(key)
      }
    }
  }

  // Get active time log for current user with caching
  const getActiveTimeLog = async (): Promise<TimeLog | null> => {
    const now = Date.now()
    
    // Return cached result if still valid
    if (globalActiveTimeLog.value && now - lastFetchTime < CACHE_DURATION) {
      return globalActiveTimeLog.value
    }

    // Return ongoing promise if already fetching
    if (activeTimeLogPromise) {
      return activeTimeLogPromise
    }

    // Start new fetch
    activeTimeLogPromise = (async () => {
      globalLoading.value = true
      loading.value = true
      globalError.value = null
      error.value = null

      try {
        const response = await apiClient.get<{ data: TimeLog | null }>('/time-tracking/active')
        globalActiveTimeLog.value = response.data
        lastFetchTime = now
        return response.data
      } catch (err: any) {
        const errorMessage = err.message || 'Failed to get active time log'
        globalError.value = errorMessage
        error.value = errorMessage
        throw err
      } finally {
        globalLoading.value = false
        loading.value = false
        activeTimeLogPromise = null
      }
    })()

    return activeTimeLogPromise
  }

  // Clock in to a task
  const clockIn = async (taskId: string, data: ClockInData): Promise<TimeLog> => {
    loading.value = true
    error.value = null

    try {
      const formData = new FormData()
      
      if (data.latitude !== undefined) formData.append('latitude', data.latitude.toString())
      if (data.longitude !== undefined) formData.append('longitude', data.longitude.toString())
      if (data.address) formData.append('address', data.address)
      if (data.activity_type) formData.append('activity_type', data.activity_type)
      if (data.description) formData.append('description', data.description)
      
      if (data.photos) {
        data.photos.forEach((photo, index) => {
          formData.append(`photos[${index}]`, photo)
        })
      }

      const response = await apiClient.post<{ data: TimeLog, message: string }>(
        `/tasks/${taskId}/time-logs/clock-in`, 
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        }
      )
      
      globalActiveTimeLog.value = response.data
      lastFetchTime = Date.now()
      return response.data
    } catch (err: any) {
      error.value = err.message || 'Failed to clock in'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Clock out
  const clockOut = async (data: ClockOutData): Promise<TimeLog> => {
    loading.value = true
    error.value = null

    try {
      const formData = new FormData()
      
      if (data.latitude !== undefined) formData.append('latitude', data.latitude.toString())
      if (data.longitude !== undefined) formData.append('longitude', data.longitude.toString())
      if (data.address) formData.append('address', data.address)
      if (data.description) formData.append('description', data.description)
      if (data.billable !== undefined) formData.append('billable', data.billable.toString())
      if (data.hourly_rate !== undefined) formData.append('hourly_rate', data.hourly_rate.toString())
      
      if (data.photos) {
        data.photos.forEach((photo, index) => {
          formData.append(`photos[${index}]`, photo)
        })
      }

      const response = await apiClient.post<{ data: TimeLog, message: string }>(
        '/time-tracking/clock-out', 
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        }
      )
      
      globalActiveTimeLog.value = null
      lastFetchTime = Date.now()
      return response.data
    } catch (err: any) {
      error.value = err.message || 'Failed to clock out'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Get user's time logs
  const getUserTimeLogs = async (filters: Record<string, any> = {}): Promise<TimeLog[]> => {
    loading.value = true
    error.value = null

    try {
      const response = await apiClient.get<{ data: TimeLog[] }>('/time-tracking/my-logs', filters)
      timeLogs.value = response.data
      return response.data
    } catch (err: any) {
      error.value = err.message || 'Failed to get time logs'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Get task time logs with caching
  const getTaskTimeLogs = async (taskId: string, filters: Record<string, any> = {}): Promise<TimeLog[]> => {
    const now = Date.now()
    const cacheKey = `${taskId}-${JSON.stringify(filters)}`
    const cached = taskTimeLogsCache.get(cacheKey)
    
    // Return cached result if still valid
    if (cached && now - cached.timestamp < TASK_LOGS_CACHE_DURATION) {
      return cached.data
    }
    
    // Return ongoing promise if already fetching
    if (cached?.promise) {
      return cached.promise
    }
    
    // Start new fetch
    const fetchPromise = (async () => {
      loading.value = true
      error.value = null

      try {
        const response = await apiClient.get<{ data: TimeLog[] }>(`/tasks/${taskId}/time-logs`, filters)
        
        // Cache the result
        taskTimeLogsCache.set(cacheKey, {
          data: response.data,
          timestamp: now
        })
        
        return response.data
      } catch (err: any) {
        error.value = err.message || 'Failed to get task time logs'
        // Remove failed promise from cache
        taskTimeLogsCache.delete(cacheKey)
        throw err
      } finally {
        loading.value = false
      }
    })()
    
    // Store the promise in cache to prevent duplicate requests
    taskTimeLogsCache.set(cacheKey, {
      data: cached?.data || [],
      timestamp: cached?.timestamp || 0,
      promise: fetchPromise
    })
    
    return fetchPromise
  }

  // Create manual time entry
  const createManualTimeLog = async (
    taskId: string, 
    data: {
      start_time: string
      end_time: string
      description: string
      activity_type?: string
      billable?: boolean
      hourly_rate?: number
    }
  ): Promise<TimeLog> => {
    loading.value = true
    error.value = null

    try {
      const response = await apiClient.post<{ data: TimeLog, message: string }>(
        `/tasks/${taskId}/time-logs`, 
        data
      )
      
      // Invalidate cache for this task
      invalidateTaskTimeLogsCache(taskId)
      
      return response.data
    } catch (err: any) {
      error.value = err.message || 'Failed to create time log'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Get time tracking statistics
  const getStats = async (filters: Record<string, any> = {}): Promise<TimeTrackingStats> => {
    loading.value = true
    error.value = null

    try {
      const response = await apiClient.get<{ data: TimeTrackingStats }>('/time-tracking/statistics', filters)
      stats.value = response.data
      return response.data
    } catch (err: any) {
      error.value = err.message || 'Failed to get statistics'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Get activity types
  const getActivityTypes = async (): Promise<Record<string, string>> => {
    try {
      const response = await apiClient.get<{ data: Record<string, string> }>('/time-tracking/activity-types')
      return response.data
    } catch (err: any) {
      error.value = err.message || 'Failed to get activity types'
      throw err
    }
  }

  // Update time log
  const updateTimeLog = async (timeLogId: string, data: Partial<TimeLog>): Promise<TimeLog> => {
    loading.value = true
    error.value = null

    try {
      const response = await apiClient.put<{ data: TimeLog, message: string }>(`/time-tracking/${timeLogId}`, data)
      
      // Invalidate cache for the task associated with this time log
      if (response.data.task_id) {
        invalidateTaskTimeLogsCache(response.data.task_id)
      }
      
      return response.data
    } catch (err: any) {
      error.value = err.message || 'Failed to update time log'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Delete time log
  const deleteTimeLog = async (timeLogId: string): Promise<void> => {
    loading.value = true
    error.value = null

    try {
      // First get the time log to know which task cache to invalidate
      const timeLogResponse = await apiClient.get<{ data: TimeLog }>(`/time-tracking/${timeLogId}`)
      const taskId = timeLogResponse.data.task_id
      
      await apiClient.delete(`/time-tracking/${timeLogId}`)
      
      // Invalidate cache for this task
      invalidateTaskTimeLogsCache(taskId)
      
    } catch (err: any) {
      error.value = err.message || 'Failed to delete time log'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Computed properties
  const isActivelyTracking = computed(() => globalActiveTimeLog.value !== null)
  const currentTaskName = computed(() => globalActiveTimeLog.value?.task?.name || 'Unknown Task')
  const currentDuration = computed(() => {
    if (!globalActiveTimeLog.value?.start_time) return '00:00'
    
    const start = new Date(globalActiveTimeLog.value.start_time)
    const now = new Date()
    const diffInMinutes = Math.floor((now.getTime() - start.getTime()) / (1000 * 60))
    
    const hours = Math.floor(diffInMinutes / 60)
    const minutes = diffInMinutes % 60
    
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`
  })

  return {
    // State
    loading,
    error,
    activeTimeLog: globalActiveTimeLog,
    timeLogs,
    stats,
    
    // Computed
    isActivelyTracking,
    currentTaskName,
    currentDuration,
    
    // Methods
    getActiveTimeLog,
    clockIn,
    clockOut,
    getUserTimeLogs,
    getTaskTimeLogs,
    createManualTimeLog,
    getStats,
    getActivityTypes,
    updateTimeLog,
    deleteTimeLog,
  }
}