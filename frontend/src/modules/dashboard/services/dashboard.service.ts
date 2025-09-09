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
      console.warn('API unavailable, using mock data:', error)
      // Return mock data when API is unavailable
      return {
        total_users: 125,
        total_companies: 8,
        active_users: 98,
        user_roles: {
          admin: 3,
          project_manager: 12,
          supervisor: 25,
          field_worker: 85
        },
        recent_users: [
          {
            id: '1',
            name: 'John Administrator',
            email: 'admin@construction.com',
            role: 'admin',
            company_name: 'Construction Corp',
            created_at: '2 hours ago'
          },
          {
            id: '2', 
            name: 'Sarah Manager',
            email: 'sarah@construction.com',
            role: 'project_manager',
            company_name: 'Construction Corp',
            created_at: '1 day ago'
          },
          {
            id: '3',
            name: 'Mike Supervisor',
            email: 'mike@construction.com', 
            role: 'supervisor',
            company_name: 'Construction Corp',
            created_at: '3 days ago'
          }
        ]
      }
    }
  }

  async getRecentActivity(): Promise<RecentActivity[]> {
    try {
      const response = await apiClient.get<{ data: RecentActivity[] }>('/dashboard/recent-activity')
      return response.data
    } catch (error) {
      console.warn('API unavailable, using mock data:', error)
      // Return mock data when API is unavailable
      return [
        {
          id: '1',
          type: 'user_registered',
          description: 'New user John Administrator joined',
          user: {
            name: 'John Administrator',
            role: 'admin',
            company_name: 'Construction Corp'
          },
          created_at: '2 hours ago'
        },
        {
          id: '2',
          type: 'user_registered', 
          description: 'New user Sarah Manager joined',
          user: {
            name: 'Sarah Manager',
            role: 'project_manager',
            company_name: 'Construction Corp'
          },
          created_at: '1 day ago'
        },
        {
          id: '3',
          type: 'user_registered',
          description: 'New user Mike Supervisor joined',
          user: {
            name: 'Mike Supervisor', 
            role: 'supervisor',
            company_name: 'Construction Corp'
          },
          created_at: '3 days ago'
        }
      ]
    }
  }
}

export const dashboardService = new DashboardService()