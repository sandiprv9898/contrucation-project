/**
 * Production Environment Configuration Override
 * 
 * This file contains production-specific settings that override the default configuration
 */

import type { CompanySettingsConfig } from '../company-settings.config'

export const productionConfigOverrides: Partial<CompanySettingsConfig> = {
  features: {
    autoSave: true,
    autoSaveDelay: 1000, // Faster auto-save in production
    showUnsavedIndicator: true,
    allowLogoUpload: true,
    showStatusIndicators: false, // Hide progress indicators in production
    enableValidation: true
  },

  // Production might have stricter permissions
  permissions: {
    canEdit: (user) => user?.role === 'admin' || (user?.role === 'project_manager' && user?.permissions?.includes('manage_company')),
    canDelete: (user) => user?.role === 'admin',
    canExport: (user) => user?.role === 'admin' || user?.role === 'project_manager'
  },

  // Production branding
  branding: {
    primaryColor: '#f97316',
    secondaryColor: '#475569', 
    accentColor: '#22c55e'
  }
}