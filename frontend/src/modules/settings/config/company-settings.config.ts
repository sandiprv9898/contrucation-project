/**
 * Dynamic Company Settings Configuration
 * 
 * This file allows customization of the Company Settings interface
 * without modifying the core components.
 */

export interface FieldConfig {
  key: string
  label: string
  type: 'text' | 'email' | 'url' | 'tel' | 'date' | 'select' | 'textarea' | 'file' | 'color'
  required?: boolean
  placeholder?: string
  options?: { value: string; label: string }[]
  validation?: RegExp | ((value: string) => boolean)
  hint?: string
  disabled?: boolean
  visible?: boolean
  gridCols?: 1 | 2 // For responsive grid layout
}

export interface TabConfig {
  id: string
  name: string
  icon: string // Lucide icon name
  enabled: boolean
  fields: FieldConfig[]
  customComponent?: string // Optional custom component path
}

export interface CompanySettingsConfig {
  title: string
  subtitle: string
  tabs: TabConfig[]
  features: {
    autoSave: boolean
    autoSaveDelay: number
    showUnsavedIndicator: boolean
    allowLogoUpload: boolean
    showStatusIndicators: boolean
    enableValidation: boolean
  }
  branding: {
    primaryColor: string
    secondaryColor: string
    accentColor: string
  }
  permissions: {
    canEdit: (user: any) => boolean
    canDelete: (user: any) => boolean
    canExport: (user: any) => boolean
  }
}

/**
 * Default Company Settings Configuration
 * This can be overridden by environment-specific configs
 */
export const defaultCompanySettingsConfig: CompanySettingsConfig = {
  title: "Company Settings",
  subtitle: "Manage your company profile, branding, and showcase",
  
  tabs: [
    {
      id: 'basic',
      name: 'Basic Info',
      icon: 'User',
      enabled: true,
      fields: [
        {
          key: 'name',
          label: 'Company Name',
          type: 'text',
          required: true,
          placeholder: 'Enter company name',
          gridCols: 1
        },
        {
          key: 'legal_name',
          label: 'Legal Name',
          type: 'text',
          placeholder: 'Enter legal company name',
          gridCols: 1
        },
        {
          key: 'industry_type',
          label: 'Industry Type',
          type: 'select',
          options: [
            { value: 'construction', label: 'General Construction' },
            { value: 'residential', label: 'Residential Construction' },
            { value: 'commercial', label: 'Commercial Construction' },
            { value: 'industrial', label: 'Industrial Construction' },
            { value: 'infrastructure', label: 'Infrastructure' },
            { value: 'renovation', label: 'Renovation & Remodeling' },
            { value: 'electrical', label: 'Electrical Contracting' },
            { value: 'plumbing', label: 'Plumbing' },
            { value: 'hvac', label: 'HVAC' },
            { value: 'roofing', label: 'Roofing' },
            { value: 'architecture', label: 'Architecture' },
            { value: 'engineering', label: 'Engineering' },
            { value: 'other', label: 'Other' }
          ],
          gridCols: 1
        },
        {
          key: 'company_size',
          label: 'Company Size',
          type: 'select',
          options: [
            { value: 'startup', label: 'Startup (1-10 employees)' },
            { value: 'small', label: 'Small (11-50 employees)' },
            { value: 'medium', label: 'Medium (51-200 employees)' },
            { value: 'large', label: 'Large (201-1000 employees)' },
            { value: 'enterprise', label: 'Enterprise (1000+ employees)' }
          ],
          gridCols: 1
        },
        {
          key: 'founded_date',
          label: 'Founded Date',
          type: 'date',
          gridCols: 1
        },
        {
          key: 'website',
          label: 'Website',
          type: 'url',
          placeholder: 'https://company.com',
          gridCols: 1
        },
        {
          key: 'description',
          label: 'Description',
          type: 'textarea',
          placeholder: 'Describe your company, its mission, and services...',
          gridCols: 2
        },
        {
          key: 'business_registration',
          label: 'Business Registration Number',
          type: 'text',
          placeholder: 'Enter registration number',
          gridCols: 1
        },
        {
          key: 'tax_identification',
          label: 'Tax Identification Number',
          type: 'text',
          placeholder: '12-3456789',
          gridCols: 1
        }
      ]
    },
    {
      id: 'branding',
      name: 'Branding',
      icon: 'Palette',
      enabled: true,
      fields: [
        {
          key: 'logo_url',
          label: 'Company Logo',
          type: 'file',
          hint: 'PNG, JPG, or SVG up to 5MB. Recommended: 200x200px',
          gridCols: 2
        },
        {
          key: 'primary_color',
          label: 'Primary Color',
          type: 'color',
          placeholder: '#f97316',
          gridCols: 1
        },
        {
          key: 'secondary_color',
          label: 'Secondary Color',
          type: 'color',
          placeholder: '#475569',
          gridCols: 1
        },
        {
          key: 'accent_color',
          label: 'Accent Color',
          type: 'color',
          placeholder: '#22c55e',
          gridCols: 1
        }
      ]
    },
    {
      id: 'contact',
      name: 'Contact',
      icon: 'Phone',
      enabled: true,
      fields: [
        {
          key: 'email',
          label: 'Company Email',
          type: 'email',
          required: true,
          placeholder: 'contact@company.com',
          gridCols: 1
        },
        {
          key: 'phone',
          label: 'Phone Number',
          type: 'tel',
          placeholder: '+1 (555) 123-4567',
          gridCols: 1
        },
        {
          key: 'fax',
          label: 'Fax Number',
          type: 'tel',
          placeholder: '+1 (555) 123-4568',
          gridCols: 1
        },
        {
          key: 'address_line_1',
          label: 'Street Address',
          type: 'text',
          required: true,
          placeholder: '123 Main Street',
          gridCols: 2
        },
        {
          key: 'address_line_2',
          label: 'Address Line 2',
          type: 'text',
          placeholder: 'Suite 100',
          gridCols: 2
        },
        {
          key: 'city',
          label: 'City',
          type: 'text',
          required: true,
          placeholder: 'New York',
          gridCols: 1
        },
        {
          key: 'state_province',
          label: 'State/Province',
          type: 'text',
          required: true,
          placeholder: 'NY',
          gridCols: 1
        },
        {
          key: 'postal_code',
          label: 'Postal Code',
          type: 'text',
          required: true,
          placeholder: '10001',
          gridCols: 1
        }
      ]
    },
    {
      id: 'portfolio',
      name: 'Portfolio',
      icon: 'Briefcase',
      enabled: true,
      fields: [
        // This will be dynamically populated or use a custom component
      ],
      customComponent: '@/modules/settings/components/company/CompanyPortfolioManager.vue'
    },
    {
      id: 'legal',
      name: 'Legal',
      icon: 'Scale',
      enabled: true,
      fields: [
        {
          key: 'privacy_policy_url',
          label: 'Privacy Policy URL',
          type: 'url',
          placeholder: 'https://company.com/privacy',
          gridCols: 1
        },
        {
          key: 'terms_of_service_url',
          label: 'Terms of Service URL',
          type: 'url',
          placeholder: 'https://company.com/terms',
          gridCols: 1
        },
        {
          key: 'data_retention_days',
          label: 'Data Retention (Days)',
          type: 'text',
          placeholder: '2555',
          validation: /^\d+$/,
          hint: 'Number of days to retain user data',
          gridCols: 1
        }
      ]
    }
  ],

  features: {
    autoSave: true,
    autoSaveDelay: 2000,
    showUnsavedIndicator: true,
    allowLogoUpload: true,
    showStatusIndicators: true,
    enableValidation: true
  },

  branding: {
    primaryColor: '#f97316',
    secondaryColor: '#475569',
    accentColor: '#22c55e'
  },

  permissions: {
    canEdit: (user) => user?.role === 'admin' || user?.role === 'project_manager',
    canDelete: (user) => user?.role === 'admin',
    canExport: (user) => user?.role === 'admin' || user?.role === 'project_manager'
  }
}

/**
 * Environment-specific configuration overrides
 */
export const getCompanySettingsConfig = async (): Promise<CompanySettingsConfig> => {
  const env = import.meta.env.NODE_ENV || 'development'
  let config = { ...defaultCompanySettingsConfig }

  try {
    // Load environment-specific overrides
    if (env === 'production') {
      const { productionConfigOverrides } = await import('./environments/production.config')
      config = mergeConfigs(config, productionConfigOverrides)
    } else if (env === 'development') {
      const { developmentConfigOverrides } = await import('./environments/development.config')
      config = mergeConfigs(config, developmentConfigOverrides)
    }
  } catch (error) {
    console.warn(`Failed to load ${env} config overrides:`, error)
  }

  // Load custom company-specific overrides if they exist
  try {
    const customConfig = await loadCustomConfig()
    if (customConfig) {
      config = mergeConfigs(config, customConfig)
    }
  } catch (error) {
    // Custom config is optional, so we don't warn here
  }

  return config
}

/**
 * Deep merge configuration objects
 */
function mergeConfigs(base: CompanySettingsConfig, override: Partial<CompanySettingsConfig>): CompanySettingsConfig {
  const merged = { ...base }

  // Merge features
  if (override.features) {
    merged.features = { ...merged.features, ...override.features }
  }

  // Merge branding
  if (override.branding) {
    merged.branding = { ...merged.branding, ...override.branding }
  }

  // Merge permissions
  if (override.permissions) {
    merged.permissions = { ...merged.permissions, ...override.permissions }
  }

  // Merge tabs (more complex merge)
  if (override.tabs) {
    const baseTabsMap = new Map(merged.tabs.map(tab => [tab.id, tab]))
    
    override.tabs.forEach(overrideTab => {
      if (baseTabsMap.has(overrideTab.id)) {
        // Merge existing tab
        const baseTab = baseTabsMap.get(overrideTab.id)!
        baseTabsMap.set(overrideTab.id, {
          ...baseTab,
          ...overrideTab,
          fields: overrideTab.fields || baseTab.fields
        })
      } else {
        // Add new tab
        baseTabsMap.set(overrideTab.id, overrideTab)
      }
    })
    
    merged.tabs = Array.from(baseTabsMap.values())
  }

  // Merge other top-level properties
  if (override.title) merged.title = override.title
  if (override.subtitle) merged.subtitle = override.subtitle

  return merged
}

/**
 * Load custom company-specific configuration
 * This allows individual companies to override default settings
 */
async function loadCustomConfig(): Promise<Partial<CompanySettingsConfig> | null> {
  try {
    // Try to load from localStorage first (for runtime customization)
    const localConfig = localStorage.getItem('company-settings-config')
    if (localConfig) {
      return JSON.parse(localConfig)
    }

    // Try to load from a custom config file (if it exists)
    const { customConfig } = await import('./custom.config')
    return customConfig
  } catch {
    return null
  }
}

/**
 * Save custom configuration to localStorage
 */
export const saveCustomConfig = (config: Partial<CompanySettingsConfig>) => {
  localStorage.setItem('company-settings-config', JSON.stringify(config))
}

/**
 * Clear custom configuration
 */
export const clearCustomConfig = () => {
  localStorage.removeItem('company-settings-config')
}

/**
 * Utility function to get enabled tabs
 */
export const getEnabledTabs = (config: CompanySettingsConfig) => {
  return config.tabs.filter(tab => tab.enabled)
}

/**
 * Utility function to get visible fields for a tab
 */
export const getVisibleFields = (tab: TabConfig) => {
  return tab.fields.filter(field => field.visible !== false)
}