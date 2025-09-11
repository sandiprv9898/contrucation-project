import { ref, computed } from 'vue'
import { apiClient } from '@/modules/shared/api/client'

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

export function useTimeTracking() {
  const loading = ref(false)
  const error = ref<string | null>(null)
  const activeTimeLog = ref<TimeLog | null>(null)
  const timeLogs = ref<TimeLog[]>([])
  const stats = ref<TimeTrackingStats | null>(null)

  // Get active time log for current user
  const getActiveTimeLog = async (): Promise<TimeLog | null> => {
    loading.value = true
    error.value = null

    try {
      const response = await apiClient.get<{ data: TimeLog | null }>('/time-tracking/active')
      activeTimeLog.value = response.data
      return response.data
    } catch (err: any) {
      error.value = err.message || 'Failed to get active time log'
      throw err
    } finally {
      loading.value = false
    }
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
      
      activeTimeLog.value = response.data
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
      
      activeTimeLog.value = null
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

  // Get task time logs
  const getTaskTimeLogs = async (taskId: string, filters: Record<string, any> = {}): Promise<TimeLog[]> => {
    loading.value = true
    error.value = null

    try {
      const response = await apiClient.get<{ data: TimeLog[] }>(`/tasks/${taskId}/time-logs`, filters)
      return response.data
    } catch (err: any) {
      error.value = err.message || 'Failed to get task time logs'
      throw err
    } finally {
      loading.value = false
    }
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
      await apiClient.delete(`/time-tracking/${timeLogId}`)
    } catch (err: any) {
      error.value = err.message || 'Failed to delete time log'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Computed properties
  const isActivelyTracking = computed(() => activeTimeLog.value !== null)
  const currentTaskName = computed(() => activeTimeLog.value?.task?.name || 'Unknown Task')
  const currentDuration = computed(() => {
    if (!activeTimeLog.value?.start_time) return '00:00'
    
    const start = new Date(activeTimeLog.value.start_time)
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
    activeTimeLog,
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