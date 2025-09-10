import { computed, ref, watch } from 'vue'
import { useSettingsStore } from '../stores/settings.store'
import type { SettingCategory } from '../types/settings.types'

export function useSettings(category?: SettingCategory) {
  const store = useSettingsStore()
  const validationErrors = ref<Record<string, string[]>>({})

  // Computed properties
  const isLoading = computed(() => store.state.isLoading)
  const isSaving = computed(() => store.state.isSaving)
  const error = computed(() => store.state.error)
  const hasUnsavedChanges = computed(() => store.state.hasUnsavedChanges)

  const canRead = computed(() => {
    if (!category) return true
    return store.canReadCategory(category)
  })

  const canWrite = computed(() => {
    if (!category) return true
    return store.canWriteCategory(category)
  })

  const categorySettings = computed(() => {
    if (!category || !store.state.settings) return null
    return store.state.settings[category]
  })

  const categoryUnsavedChanges = computed(() => {
    if (!category) return {}
    return store.unsavedChanges[category] || {}
  })

  const hasCategoryUnsavedChanges = computed(() => {
    return Object.keys(categoryUnsavedChanges.value).length > 0
  })

  // Methods
  async function loadSettings() {
    try {
      await store.loadSettings()
    } catch (error) {
      console.error('Failed to load settings:', error)
    }
  }

  async function saveSettings() {
    if (!category) {
      await store.saveAllUnsavedChanges()
      return
    }

    const changes = categoryUnsavedChanges.value
    if (Object.keys(changes).length === 0) return

    try {
      await store.updateCategorySettings(category, changes)
      validationErrors.value = {}
    } catch (error: any) {
      if (error.response?.data?.validation_errors) {
        validationErrors.value = error.response.data.validation_errors
      }
      throw error
    }
  }

  function updateSetting(key: string, value: any) {
    if (!category) return
    store.updateLocalSetting(category, key, value)
  }

  function discardChanges() {
    if (category) {
      store.discardUnsavedChanges(category)
    } else {
      store.discardUnsavedChanges()
    }
    validationErrors.value = {}
  }

  async function resetToDefaults() {
    if (!category) return
    try {
      await store.resetCategoryToDefaults(category)
      validationErrors.value = {}
    } catch (error) {
      console.error(`Failed to reset ${category} to defaults:`, error)
      throw error
    }
  }

  function validateField(key: string, value: any): string[] {
    const errors: string[] = []
    
    if (!category) return errors

    const validations = store.validationsByCategory[category] || []
    const validation = validations.find(v => v.key === key)
    
    if (!validation) return errors

    // Required validation
    if (validation.required && (!value || (typeof value === 'string' && value.trim() === ''))) {
      errors.push(`${validation.key} is required`)
    }

    // Type-specific validations
    if (value) {
      switch (validation.type) {
        case 'string':
          if (typeof value !== 'string') {
            errors.push(`${validation.key} must be a string`)
          } else {
            if (validation.min_length && value.length < validation.min_length) {
              errors.push(`${validation.key} must be at least ${validation.min_length} characters`)
            }
            if (validation.max_length && value.length > validation.max_length) {
              errors.push(`${validation.key} must be no more than ${validation.max_length} characters`)
            }
            if (validation.pattern && !new RegExp(validation.pattern).test(value)) {
              errors.push(`${validation.key} format is invalid`)
            }
          }
          break

        case 'number':
          const numValue = Number(value)
          if (isNaN(numValue)) {
            errors.push(`${validation.key} must be a number`)
          } else {
            if (validation.min_value !== undefined && numValue < validation.min_value) {
              errors.push(`${validation.key} must be at least ${validation.min_value}`)
            }
            if (validation.max_value !== undefined && numValue > validation.max_value) {
              errors.push(`${validation.key} must be no more than ${validation.max_value}`)
            }
          }
          break

        case 'select':
          if (validation.options && !validation.options.includes(String(value))) {
            errors.push(`${validation.key} must be one of: ${validation.options.join(', ')}`)
          }
          break
      }
    }

    return errors
  }

  function getFieldError(key: string): string | null {
    const errors = validationErrors.value[key]
    return errors && errors.length > 0 ? errors[0] : null
  }

  function clearFieldError(key: string) {
    if (validationErrors.value[key]) {
      delete validationErrors.value[key]
    }
  }

  function clearAllErrors() {
    validationErrors.value = {}
  }

  function clearError() {
    store.clearError()
  }

  // Auto-save functionality
  function enableAutoSave(delay = 2000) {
    if (!category) return

    watch(
      categoryUnsavedChanges,
      (changes) => {
        if (Object.keys(changes).length > 0) {
          store.debouncedSave(category, delay)
        }
      },
      { deep: true }
    )
  }

  return {
    // State
    isLoading,
    isSaving,
    error,
    hasUnsavedChanges,
    canRead,
    canWrite,
    categorySettings,
    categoryUnsavedChanges,
    hasCategoryUnsavedChanges,
    validationErrors: computed(() => validationErrors.value),

    // Methods
    loadSettings,
    saveSettings,
    updateSetting,
    discardChanges,
    resetToDefaults,
    validateField,
    getFieldError,
    clearFieldError,
    clearAllErrors,
    clearError,
    enableAutoSave
  }
}

// Utility functions for settings
export function formatCurrency(amount: number, currency: string): string {
  try {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currency
    }).format(amount)
  } catch (error) {
    return `${currency} ${amount.toFixed(2)}`
  }
}

export function formatDateTime(dateString: string, options: Intl.DateTimeFormatOptions = {}): string {
  try {
    return new Intl.DateTimeFormat('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      ...options
    }).format(new Date(dateString))
  } catch (error) {
    return dateString
  }
}

export function validateEmail(email: string): boolean {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

export function validateUrl(url: string): boolean {
  try {
    new URL(url)
    return true
  } catch {
    return false
  }
}

export function validatePhone(phone: string): boolean {
  const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/
  return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''))
}

export function validateHexColor(color: string): boolean {
  const hexRegex = /^#[0-9A-F]{6}$/i
  return hexRegex.test(color)
}

export function generateColorVariants(baseColor: string): { light: string; dark: string } {
  // Simple color variant generation
  // In a real implementation, you might use a color manipulation library
  const hex = baseColor.replace('#', '')
  const r = parseInt(hex.substr(0, 2), 16)
  const g = parseInt(hex.substr(2, 2), 16)
  const b = parseInt(hex.substr(4, 2), 16)
  
  const light = `#${Math.min(255, r + 40).toString(16).padStart(2, '0')}${Math.min(255, g + 40).toString(16).padStart(2, '0')}${Math.min(255, b + 40).toString(16).padStart(2, '0')}`
  const dark = `#${Math.max(0, r - 40).toString(16).padStart(2, '0')}${Math.max(0, g - 40).toString(16).padStart(2, '0')}${Math.max(0, b - 40).toString(16).padStart(2, '0')}`
  
  return { light, dark }
}