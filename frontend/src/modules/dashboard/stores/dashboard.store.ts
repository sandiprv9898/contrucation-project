import { defineStore } from 'pinia'
import { ref } from 'vue'
import { dashboardService, type DashboardStats, type RecentActivity } from '../services/dashboard.service'

export const useDashboardStore = defineStore('dashboard', () => {
  const stats = ref<DashboardStats | null>(null)
  const recentActivity = ref<RecentActivity[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const loadStats = async (): Promise<void> => {
    try {
      isLoading.value = true
      error.value = null
      stats.value = await dashboardService.getStats()
    } catch (err: any) {
      error.value = err.message || 'Failed to load dashboard stats'
      console.error('Dashboard stats error:', err)
    } finally {
      isLoading.value = false
    }
  }

  const loadRecentActivity = async (): Promise<void> => {
    try {
      isLoading.value = true
      error.value = null
      recentActivity.value = await dashboardService.getRecentActivity()
    } catch (err: any) {
      error.value = err.message || 'Failed to load recent activity'
      console.error('Recent activity error:', err)
    } finally {
      isLoading.value = false
    }
  }

  const loadDashboardData = async (): Promise<void> => {
    await Promise.allSettled([
      loadStats(),
      loadRecentActivity()
    ])
  }

  return {
    stats,
    recentActivity,
    isLoading,
    error,
    loadStats,
    loadRecentActivity,
    loadDashboardData
  }
})