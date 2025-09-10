/**
 * Development Environment Configuration Override
 * 
 * This file contains development-specific settings for easier debugging and testing
 */

import type { CompanySettingsConfig, FieldConfig } from '../company-settings.config'

export const developmentConfigOverrides: Partial<CompanySettingsConfig> = {
  features: {
    autoSave: false, // Disable auto-save in development for testing
    autoSaveDelay: 3000, // Slower auto-save for debugging
    showUnsavedIndicator: true,
    allowLogoUpload: true,
    showStatusIndicators: true, // Show progress indicators in development
    enableValidation: false // Disable validation for easier testing
  },

  // Development has more relaxed permissions for testing
  permissions: {
    canEdit: (user) => true, // Everyone can edit in development
    canDelete: (user) => user?.role === 'admin',
    canExport: (user) => true // Everyone can export in development
  },

  // Add debug fields in development
  tabs: [
    // ... existing tabs will be merged
    {
      id: 'debug',
      name: 'Debug',
      icon: 'Bug',
      enabled: true,
      fields: [
        {
          key: 'debug_mode',
          label: 'Debug Mode',
          type: 'select',
          options: [
            { value: 'off', label: 'Off' },
            { value: 'basic', label: 'Basic Logging' },
            { value: 'verbose', label: 'Verbose Logging' },
            { value: 'trace', label: 'Full Trace' }
          ],
          gridCols: 1
        } as FieldConfig,
        {
          key: 'test_data',
          label: 'Test Data',
          type: 'textarea',
          placeholder: 'Enter test data...',
          gridCols: 2
        } as FieldConfig
      ]
    }
  ]
}