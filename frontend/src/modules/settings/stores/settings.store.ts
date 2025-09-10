import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'
import { SettingsApi } from '../api/settings.api'
import type {
  SystemSettings,
  SettingsState,
  SettingCategory,
  SettingsPermission,
  SettingValidation,
  CompanySettings,
  SystemPreferences,
  NotificationSettings,
  SecuritySettings,
  BackupSettings,
  SettingsAuditLog
} from '../types/settings.types'

export const useSettingsStore = defineStore('settings', () => {
  // State
  const state = ref<SettingsState>({
    settings: null,
    permissions: [],
    validations: [],
    isLoading: false,
    isSaving: false,
    error: null,
    hasUnsavedChanges: false,
    lastUpdated: null
  })

  const unsavedChanges = ref<Record<SettingCategory, any>>({})
  const debounceTimers = ref<Record<string, NodeJS.Timeout>>({})

  // Computed
  const isLoaded = computed(() => state.value.settings !== null)
  
  const companySettings = computed(() => state.value.settings?.company || null)
  const systemSettings = computed(() => state.value.settings?.system || null)
  const notificationSettings = computed(() => state.value.settings?.notifications || null)
  const securitySettings = computed(() => state.value.settings?.security || null)
  const backupSettings = computed(() => state.value.settings?.backup || null)

  const categoryPermissions = computed(() => {
    const perms: Record<SettingCategory, { can_read: boolean; can_write: boolean }> = {
      company: { can_read: false, can_write: false },
      system: { can_read: false, can_write: false },
      notifications: { can_read: false, can_write: false },
      security: { can_read: false, can_write: false },
      backup: { can_read: false, can_write: false }
    }

    // Safely process permissions if they exist and are an array
    if (Array.isArray(state.value.permissions)) {
      state.value.permissions.forEach(permission => {
        if (!perms[permission.category]) {
          perms[permission.category] = { can_read: false, can_write: false }
        }
        if (permission.can_read) perms[permission.category].can_read = true
        if (permission.can_write) perms[permission.category].can_write = true
      })
    }

    return perms
  })

  const canReadCategory = computed(() => (category: SettingCategory) => {
    return categoryPermissions.value[category]?.can_read || false
  })

  const canWriteCategory = computed(() => (category: SettingCategory) => {
    return categoryPermissions.value[category]?.can_write || false
  })

  const validationsByCategory = computed(() => {
    const validations: Record<SettingCategory, SettingValidation[]> = {
      company: [],
      system: [],
      notifications: [],
      security: [],
      backup: []
    }

    // Safely process validations if they exist and are an array
    if (Array.isArray(state.value.validations)) {
      state.value.validations.forEach(validation => {
        const category = validation.key.split('.')[0] as SettingCategory
        if (validations[category]) {
          validations[category].push(validation)
        }
      })
    }

    return validations
  })

  // Actions
  async function loadSettings() {
    // Prevent multiple simultaneous loads
    if (state.value.isLoading) return
    
    // Don't reload if settings are already loaded and recent (within last 5 minutes)
    // Only use cache if we actually have settings data
    if (state.value.settings && state.value.lastUpdated && Object.keys(state.value.settings).length > 0) {
      const lastUpdateTime = new Date(state.value.lastUpdated).getTime()
      const fiveMinutesAgo = Date.now() - (5 * 60 * 1000)
      if (lastUpdateTime > fiveMinutesAgo) {
        console.log('🔧 [SETTINGS STORE] Using cached settings (loaded less than 5 minutes ago)')
        return
      }
    }

    try {
      state.value.isLoading = true
      state.value.error = null

      console.log('🔧 [SETTINGS STORE] Loading settings...')

      // Load all settings, permissions, and validations in parallel
      const [settingsResponse, permissions, validations] = await Promise.all([
        SettingsApi.getSettings(),
        SettingsApi.getSettingsPermissions(),
        SettingsApi.getSettingsValidations()
      ])

      state.value.settings = settingsResponse.data
      state.value.permissions = permissions
      state.value.validations = validations
      state.value.lastUpdated = new Date().toISOString()

      console.log('✅ [SETTINGS STORE] Settings loaded successfully')
    } catch (error) {
      console.error('❌ [SETTINGS STORE] Failed to load settings:', error)
      state.value.error = error instanceof Error ? error.message : 'Failed to load settings'
    } finally {
      state.value.isLoading = false
    }
  }

  /**
   * Get settings for a specific category (synchronous - only returns if already loaded)
   */
  function getSettingsByCategory(category: SettingCategory) {
    // Only return data if settings are already loaded
    if (!isLoaded.value) {
      return null
    }

    // Return the category-specific settings
    switch (category) {
      case 'company':
        return companySettings.value
      case 'system':
        return systemSettings.value
      case 'notifications':
        return notificationSettings.value
      case 'security':
        return securitySettings.value
      case 'backup':
        return backupSettings.value
      default:
        return null
    }
  }

  async function updateCategorySettings(
    category: SettingCategory,
    settings: Partial<CompanySettings | SystemPreferences | NotificationSettings | SecuritySettings | BackupSettings>
  ) {
    try {
      state.value.isSaving = true
      state.value.error = null

      console.log(`🔧 [SETTINGS STORE] Updating ${category} settings...`, settings)

      const response = await SettingsApi.updateSettings(category, settings)

      if (response.success && state.value.settings) {
        // Update local state with new settings
        state.value.settings[category] = {
          ...state.value.settings[category],
          ...response.updated_settings[category]
        }
        
        state.value.lastUpdated = new Date().toISOString()
        
        // Clear unsaved changes for this category
        delete unsavedChanges.value[category]
        updateUnsavedChangesState()
      }

      console.log(`✅ [SETTINGS STORE] ${category} settings updated successfully`)
      return response
    } catch (error) {
      console.error(`❌ [SETTINGS STORE] Failed to update ${category} settings:`, error)
      state.value.error = error instanceof Error ? error.message : `Failed to update ${category} settings`
      throw error
    } finally {
      state.value.isSaving = false
    }
  }

  function updateLocalSetting(category: SettingCategory, key: string, value: any) {
    // Update unsaved changes tracking
    if (!unsavedChanges.value[category]) {
      unsavedChanges.value[category] = {}
    }
    unsavedChanges.value[category][key] = value
    updateUnsavedChangesState()

    // Update local state immediately for UI responsiveness
    if (state.value.settings && state.value.settings[category]) {
      ;(state.value.settings[category] as any)[key] = value
    }
  }

  function debouncedSave(category: SettingCategory, delay = 2000) {
    const timerId = `${category}_save`
    
    // Clear existing timer
    if (debounceTimers.value[timerId]) {
      clearTimeout(debounceTimers.value[timerId])
    }

    // Set new timer
    debounceTimers.value[timerId] = setTimeout(async () => {
      if (unsavedChanges.value[category] && Object.keys(unsavedChanges.value[category]).length > 0) {
        try {
          await updateCategorySettings(category, unsavedChanges.value[category])
        } catch (error) {
          console.error(`❌ [SETTINGS STORE] Auto-save failed for ${category}:`, error)
        }
      }
    }, delay)
  }

  async function saveAllUnsavedChanges() {
    const categories = Object.keys(unsavedChanges.value) as SettingCategory[]
    console.log('🔧 [SETTINGS STORE] Saving all unsaved changes for categories:', categories)
    console.log('🔧 [SETTINGS STORE] Unsaved changes:', unsavedChanges.value)
    
    const promises = categories.map(category => {
      if (unsavedChanges.value[category] && Object.keys(unsavedChanges.value[category]).length > 0) {
        console.log(`🔧 [SETTINGS STORE] Saving changes for ${category}:`, unsavedChanges.value[category])
        return updateCategorySettings(category, unsavedChanges.value[category])
      }
      return Promise.resolve()
    })

    try {
      await Promise.all(promises)
      console.log('✅ [SETTINGS STORE] All unsaved changes saved successfully')
    } catch (error) {
      console.error('❌ [SETTINGS STORE] Failed to save all changes:', error)
      throw error
    }
  }

  function discardUnsavedChanges(category?: SettingCategory) {
    if (category) {
      delete unsavedChanges.value[category]
      // Reload settings from server for this category if needed
      // This is a simplified approach - in production you might want to be more granular
    } else {
      unsavedChanges.value = {}
      // Reload all settings from server
      loadSettings()
    }
    updateUnsavedChangesState()
  }

  async function uploadCompanyLogo(file: File) {
    try {
      state.value.isSaving = true
      const result = await SettingsApi.uploadCompanyLogo(file)
      
      // Update company settings with new logo URL
      if (state.value.settings?.company) {
        state.value.settings.company.logo_url = result.logo_url
      }

      return result.logo_url
    } catch (error) {
      console.error('❌ [SETTINGS STORE] Failed to upload logo:', error)
      throw error
    } finally {
      state.value.isSaving = false
    }
  }

  async function exportSettings(categories?: SettingCategory[]) {
    try {
      const exportData = await SettingsApi.exportSettings(categories)
      
      // Create and download file
      const blob = new Blob([JSON.stringify(exportData, null, 2)], { 
        type: 'application/json' 
      })
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `settings-export-${new Date().toISOString().split('T')[0]}.json`
      document.body.appendChild(a)
      a.click()
      document.body.removeChild(a)
      URL.revokeObjectURL(url)

      return exportData
    } catch (error) {
      console.error('❌ [SETTINGS STORE] Failed to export settings:', error)
      throw error
    }
  }

  async function importSettings(file: File, overwriteExisting: boolean, categories: SettingCategory[]) {
    try {
      state.value.isSaving = true
      const result = await SettingsApi.importSettings({
        file,
        overwrite_existing: overwriteExisting,
        categories
      })

      // Reload settings after import
      await loadSettings()
      
      return result
    } catch (error) {
      console.error('❌ [SETTINGS STORE] Failed to import settings:', error)
      throw error
    } finally {
      state.value.isSaving = false
    }
  }

  async function getAuditLog(category?: SettingCategory, limit = 50): Promise<SettingsAuditLog[]> {
    try {
      return await SettingsApi.getSettingsAuditLog(category, limit)
    } catch (error) {
      console.error('❌ [SETTINGS STORE] Failed to get audit log:', error)
      throw error
    }
  }

  async function resetCategoryToDefaults(category: SettingCategory) {
    try {
      state.value.isSaving = true
      const result = await SettingsApi.resetSettingsToDefaults(category)
      
      if (result.success && state.value.settings) {
        // Update local state with reset settings
        state.value.settings[category] = result.updated_settings[category] as any
        
        // Clear unsaved changes for this category
        delete unsavedChanges.value[category]
        updateUnsavedChangesState()
      }

      return result
    } catch (error) {
      console.error(`❌ [SETTINGS STORE] Failed to reset ${category} to defaults:`, error)
      throw error
    } finally {
      state.value.isSaving = false
    }
  }

  function updateUnsavedChangesState() {
    const hasChanges = Object.keys(unsavedChanges.value).some(category => 
      unsavedChanges.value[category as SettingCategory] && 
      Object.keys(unsavedChanges.value[category as SettingCategory]).length > 0
    )
    state.value.hasUnsavedChanges = hasChanges
  }

  function clearError() {
    state.value.error = null
  }

  async function forceReloadSettings() {
    // Force reload by clearing cache and reloading
    state.value.settings = null
    state.value.lastUpdated = null
    await loadSettings()
  }

  function markHasUnsavedChanges() {
    state.value.hasUnsavedChanges = true
  }

  async function updateSettings(category: SettingCategory, settings: any) {
    return await updateCategorySettings(category, settings)
  }

  async function testEmailConfiguration(settings: any) {
    try {
      const response = await SettingsApi.testNotificationConfiguration('email')
      return response
    } catch (error) {
      console.error('❌ [SETTINGS STORE] Failed to test email configuration:', error)
      throw error
    }
  }

  async function runBackupNow() {
    try {
      state.value.isSaving = true
      const response = await SettingsApi.triggerManualBackup()
      console.log('✅ [SETTINGS STORE] Manual backup triggered successfully')
      return response
    } catch (error) {
      console.error('❌ [SETTINGS STORE] Failed to trigger manual backup:', error)
      throw error
    } finally {
      state.value.isSaving = false
    }
  }

  async function testBackupConnection(settings: any) {
    try {
      const response = await SettingsApi.validateBackupConfiguration()
      return response
    } catch (error) {
      console.error('❌ [SETTINGS STORE] Failed to test backup connection:', error)
      throw error
    }
  }

  // Computed properties for easier access
  const isSaving = computed(() => state.value.isSaving)
  const error = computed(() => state.value.error)
  const hasUnsavedChanges = computed(() => state.value.hasUnsavedChanges)

  // Watchers
  watch(
    () => state.value.hasUnsavedChanges,
    (hasChanges) => {
      // Show warning before page unload if there are unsaved changes
      if (hasChanges) {
        const handleBeforeUnload = (e: BeforeUnloadEvent) => {
          e.preventDefault()
          e.returnValue = ''
        }
        window.addEventListener('beforeunload', handleBeforeUnload)
        
        return () => {
          window.removeEventListener('beforeunload', handleBeforeUnload)
        }
      }
    },
    { immediate: true }
  )

  return {
    // State
    state: computed(() => state.value),
    unsavedChanges: computed(() => unsavedChanges.value),
    
    // Computed
    isLoaded,
    isSaving,
    error,
    hasUnsavedChanges,
    companySettings,
    systemSettings,
    notificationSettings,
    securitySettings,
    backupSettings,
    categoryPermissions,
    canReadCategory,
    canWriteCategory,
    validationsByCategory,

    // Actions
    loadSettings,
    forceReloadSettings,
    getSettingsByCategory,
    updateCategorySettings,
    updateSettings,
    updateLocalSetting,
    debouncedSave,
    saveAllUnsavedChanges,
    discardUnsavedChanges,
    uploadCompanyLogo,
    exportSettings,
    importSettings,
    getAuditLog,
    resetCategoryToDefaults,
    clearError,
    markHasUnsavedChanges,
    testEmailConfiguration,
    runBackupNow,
    testBackupConnection
  }
})