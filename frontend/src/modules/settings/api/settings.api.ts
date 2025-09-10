import { apiClient } from '@/modules/shared/api/client'
import { API_CONFIG } from '@/modules/shared/constants/api'
import type {
  SystemSettings,
  SettingsUpdateRequest,
  SettingsResponse,
  SettingsUpdateResponse,
  SettingsExport,
  SettingsImport,
  SettingCategory,
  SettingsPermission,
  SettingValidation,
  SettingsAuditLog
} from '../types/settings.types'

export class SettingsApi {
  /**
   * Get all settings with permissions and validations
   */
  static async getSettings(): Promise<SettingsResponse> {
    console.log('🔧 [SETTINGS API] Getting all settings...')
    
    const response = await apiClient.get<SettingsResponse>(
      `${API_CONFIG.ENDPOINTS.SETTINGS?.LIST || '/api/v1/settings'}`
    )
    
    console.log('✅ [SETTINGS API] Settings retrieved successfully:', response)
    return response
  }

  /**
   * Get settings for a specific category
   */
  static async getSettingsByCategory(category: SettingCategory): Promise<Partial<SystemSettings>> {
    console.log('🔧 [SETTINGS API] Getting settings for category:', category)
    
    const response = await apiClient.get<{ data: Partial<SystemSettings> }>(
      `${API_CONFIG.ENDPOINTS.SETTINGS?.LIST || '/api/v1/settings'}/${category}`
    )
    
    console.log('✅ [SETTINGS API] Category settings retrieved:', response)
    return response.data
  }

  /**
   * Update settings for a specific category
   */
  static async updateSettings(category: SettingCategory, settings: any): Promise<SettingsUpdateResponse> {
    console.log('🔧 [SETTINGS API] Updating settings for category:', category, settings)
    
    const response = await apiClient.put<{ message: string; data: any[] }>(
      `${API_CONFIG.ENDPOINTS.SETTINGS?.LIST || '/api/v1/settings'}/${category}`,
      { settings }
    )
    
    console.log('✅ [SETTINGS API] Settings updated successfully:', response)
    
    // Transform backend response to frontend format
    const transformedSettings: Record<string, any> = {}
    if (response.data && Array.isArray(response.data)) {
      response.data.forEach((setting: any) => {
        transformedSettings[setting.key] = setting.value
      })
    }
    
    return {
      success: true,
      updated_settings: {
        [category]: transformedSettings
      },
      message: response.message
    } as SettingsUpdateResponse
  }

  /**
   * Upload company logo
   */
  static async uploadCompanyLogo(file: File): Promise<{ logo_url: string }> {
    console.log('🔧 [SETTINGS API] Uploading company logo...')
    
    const formData = new FormData()
    formData.append('logo', file)

    const response = await apiClient.post<{ data: { logo_url: string } }>(
      `${API_CONFIG.ENDPOINTS.SETTINGS?.LIST || '/api/v1/settings'}/company/logo`,
      formData
    )
    
    console.log('✅ [SETTINGS API] Logo uploaded successfully:', response)
    return response.data
  }

  /**
   * Get settings permissions for current user
   */
  static async getSettingsPermissions(): Promise<SettingsPermission[]> {
    console.log('🔧 [SETTINGS API] Getting settings permissions...')
    
    const response = await apiClient.get<{ data: Record<string, any>, user_roles: string[] }>(
      `${API_CONFIG.ENDPOINTS.SETTINGS?.LIST || '/api/v1/settings'}/permissions`
    )
    
    console.log('✅ [SETTINGS API] Permissions retrieved:', response)
    
    // Convert the backend format to frontend format
    const permissions: SettingsPermission[] = []
    if (response.data && typeof response.data === 'object') {
      Object.entries(response.data).forEach(([category, perms]: [string, any]) => {
        permissions.push({
          category: category as any,
          can_read: perms.can_read || false,
          can_write: perms.can_write || false
        })
      })
    }
    
    return permissions
  }

  /**
   * Get validation rules for settings
   */
  static async getSettingsValidations(): Promise<SettingValidation[]> {
    console.log('🔧 [SETTINGS API] Getting settings validations...')
    
    const response = await apiClient.get<{ data: Record<string, Record<string, string>> }>(
      `${API_CONFIG.ENDPOINTS.SETTINGS?.LIST || '/api/v1/settings'}/validations`
    )
    
    console.log('✅ [SETTINGS API] Validations retrieved:', response)
    
    // Convert the backend format to frontend format
    const validations: SettingValidation[] = []
    if (response.data && typeof response.data === 'object') {
      Object.entries(response.data).forEach(([category, rules]) => {
        Object.entries(rules).forEach(([key, rule]) => {
          validations.push({
            key: `${category}.${key}`,
            rule: rule,
            message: `Invalid value for ${key}`
          })
        })
      })
    }
    
    return validations
  }

  /**
   * Export settings to file
   */
  static async exportSettings(categories?: SettingCategory[]): Promise<SettingsExport> {
    console.log('🔧 [SETTINGS API] Exporting settings...', categories)
    
    const params = categories ? { categories: categories.join(',') } : {}
    
    const response = await apiClient.get<SettingsExport>(
      `${API_CONFIG.ENDPOINTS.SETTINGS?.LIST || '/api/v1/settings'}/export`,
      params
    )
    
    console.log('✅ [SETTINGS API] Settings exported:', response)
    return response
  }

  /**
   * Import settings from file
   */
  static async importSettings(importData: SettingsImport): Promise<SettingsUpdateResponse> {
    console.log('🔧 [SETTINGS API] Importing settings...', importData)
    
    const formData = new FormData()
    formData.append('file', importData.file)
    formData.append('overwrite_existing', String(importData.overwrite_existing))
    formData.append('categories', importData.categories.join(','))

    const response = await apiClient.post<SettingsUpdateResponse>(
      `${API_CONFIG.ENDPOINTS.SETTINGS?.LIST || '/api/v1/settings'}/import`,
      formData
    )
    
    console.log('✅ [SETTINGS API] Settings imported:', response)
    return response
  }

  /**
   * Get settings audit log
   */
  static async getSettingsAuditLog(category?: SettingCategory, limit = 50): Promise<SettingsAuditLog[]> {
    console.log('🔧 [SETTINGS API] Getting settings audit log...', { category, limit })
    
    const params: Record<string, any> = { limit }
    if (category) {
      params.category = category
    }
    
    const response = await apiClient.get<{ data: SettingsAuditLog[] }>(
      `${API_CONFIG.ENDPOINTS.SETTINGS?.LIST || '/api/v1/settings'}/audit-log`,
      params
    )
    
    console.log('✅ [SETTINGS API] Audit log retrieved:', response)
    return response.data
  }

  /**
   * Get system health status
   */
  static async getSystemHealth(): Promise<{
    status: 'healthy' | 'warning' | 'error'
    checks: Record<string, boolean>
    message: string
    timestamp: string
  }> {
    console.log('🔧 [SETTINGS API] Getting system health...')
    
    const response = await apiClient.get<{
      data: {
        status: 'healthy' | 'warning' | 'error'
        checks: Record<string, boolean>
        message: string
        timestamp: string
      }
    }>(`${API_CONFIG.ENDPOINTS.SETTINGS?.LIST || '/api/v1/settings'}/system/health`)
    
    console.log('✅ [SETTINGS API] System health retrieved:', response)
    return response.data
  }

  /**
   * Toggle maintenance mode
   */
  static async toggleMaintenanceMode(enabled: boolean, message?: string, allowedIps?: string[]): Promise<{
    maintenance_enabled: boolean
    message: string
    allowed_ips: string[]
  }> {
    console.log('🔧 [SETTINGS API] Toggling maintenance mode...', { enabled, message, allowedIps })
    
    const response = await apiClient.post<{
      data: {
        maintenance_enabled: boolean
        message: string
        allowed_ips: string[]
      }
    }>(`${API_CONFIG.ENDPOINTS.SETTINGS?.LIST || '/api/v1/settings'}/system/maintenance`, {
      enabled,
      message,
      allowed_ips: allowedIps
    })
    
    console.log('✅ [SETTINGS API] Maintenance mode toggled:', response)
    return response.data
  }

  /**
   * Test notification configuration
   */
  static async testNotificationConfiguration(
    channel: 'email' | 'sms' | 'push',
    recipient?: string
  ): Promise<{ success: boolean; message: string }> {
    console.log('🔧 [SETTINGS API] Testing notification configuration...', { channel, recipient })
    
    const response = await apiClient.post<{
      data: { success: boolean; message: string }
    }>(`${API_CONFIG.ENDPOINTS.SETTINGS?.LIST || '/api/v1/settings'}/notifications/test`, {
      channel,
      recipient
    })
    
    console.log('✅ [SETTINGS API] Notification test result:', response)
    return response.data
  }

  /**
   * Validate backup configuration
   */
  static async validateBackupConfiguration(): Promise<{
    valid: boolean
    errors: string[]
    warnings: string[]
    storage_accessible: boolean
    last_backup: string | null
  }> {
    console.log('🔧 [SETTINGS API] Validating backup configuration...')
    
    const response = await apiClient.get<{
      data: {
        valid: boolean
        errors: string[]
        warnings: string[]
        storage_accessible: boolean
        last_backup: string | null
      }
    }>(`${API_CONFIG.ENDPOINTS.SETTINGS?.LIST || '/api/v1/settings'}/backup/validate`)
    
    console.log('✅ [SETTINGS API] Backup validation result:', response)
    return response.data
  }

  /**
   * Trigger manual backup
   */
  static async triggerManualBackup(): Promise<{
    backup_id: string
    status: 'queued' | 'in_progress' | 'completed' | 'failed'
    message: string
  }> {
    console.log('🔧 [SETTINGS API] Triggering manual backup...')
    
    const response = await apiClient.post<{
      data: {
        backup_id: string
        status: 'queued' | 'in_progress' | 'completed' | 'failed'
        message: string
      }
    }>(`${API_CONFIG.ENDPOINTS.SETTINGS?.LIST || '/api/v1/settings'}/backup/trigger`)
    
    console.log('✅ [SETTINGS API] Manual backup triggered:', response)
    return response.data
  }

  /**
   * Reset settings to defaults for a category
   */
  static async resetSettingsToDefaults(category: SettingCategory): Promise<SettingsUpdateResponse> {
    console.log('🔧 [SETTINGS API] Resetting settings to defaults:', category)
    
    const response = await apiClient.post<SettingsUpdateResponse>(
      `${API_CONFIG.ENDPOINTS.SETTINGS?.LIST || '/api/v1/settings'}/${category}/reset`
    )
    
    console.log('✅ [SETTINGS API] Settings reset to defaults:', response)
    return response
  }
}