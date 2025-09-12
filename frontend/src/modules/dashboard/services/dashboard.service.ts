import { apiClient } from '@/modules/shared/api/client'

export interface DashboardStats {
  total_users: number
  total_companies: number
  active_users: number
  user_roles: Record<string, number>
  recent_users: Array<{
    id: string
    name: string
    email: string
    role: string
    company_name?: string
    created_at: string
  }>
}

export interface RecentActivity {
  id: string
  type: string
  description: string
  user: {
    name: string
    role: string
    company_name?: string
  }
  created_at: string
}

class DashboardService {
  async getStats(): Promise<DashboardStats> {
    try {
      const response = await apiClient.get<{ data: DashboardStats }>('/dashboard/stats')
      return response.data
    } catch (error) {
      console.error('Dashboard API error:', error)
      throw error
    }
  }

  async getRecentActivity(): Promise<RecentActivity[]> {
    try {
      const response = await apiClient.get<{ data: RecentActivity[] }>('/dashboard/recent-activity')
      return response.data
    } catch (error) {
      console.error('Dashboard API error:', error)
      throw error
    }
  }
}

export const dashboardService = new DashboardService()