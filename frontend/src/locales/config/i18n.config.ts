/**
 * Vue i18n Configuration
 * 
 * This file sets up the internationalization system for the application
 */

import { createI18n } from 'vue-i18n'
import { defaultLocale, enabledLocales, getLocaleDirection } from './supported-locales'

// Import translation files
const messages = {} as Record<string, any>

// Dynamically import translation files for enabled locales
const importTranslations = async () => {
  const translations: Record<string, any> = {}
  
  for (const locale of enabledLocales) {
    try {
      // Import common translations
      const common = await import(`../${locale.code}/common.json`)
      const navigation = await import(`../${locale.code}/navigation.json`)
      const forms = await import(`../${locale.code}/forms.json`)
      const construction = await import(`../${locale.code}/construction.json`)
      
      // Import page translations
      const dashboard = await import(`../${locale.code}/pages/dashboard.json`)
      const projects = await import(`../${locale.code}/pages/projects.json`)
      const settings = await import(`../${locale.code}/pages/settings.json`)
      const company = await import(`../${locale.code}/pages/company.json`)
      
      // Import module translations
      const auth = await import(`../${locale.code}/modules/auth.json`)
      const users = await import(`../${locale.code}/modules/users.json`)
      const settingsModule = await import(`../${locale.code}/modules/settings.json`)
      
      translations[locale.code] = {
        common: common.default || common,
        navigation: navigation.default || navigation,
        forms: forms.default || forms,
        construction: construction.default || construction,
        pages: {
          dashboard: dashboard.default || dashboard,
          projects: projects.default || projects,
          settings: settings.default || settings,
          company: company.default || company
        },
        modules: {
          auth: auth.default || auth,
          users: users.default || users,
          settings: settingsModule.default || settingsModule
        }
      }
    } catch (error) {
      console.warn(`Failed to load translations for locale: ${locale.code}`, error)
      // Create empty structure for missing translations
      translations[locale.code] = {
        common: {},
        navigation: {},
        forms: {},
        construction: {},
        pages: {},
        modules: {}
      }
    }
  }
  
  return translations
}

// Locale detection and persistence
export const getStoredLocale = (): string => {
  try {
    return localStorage.getItem('app-locale') || defaultLocale
  } catch {
    return defaultLocale
  }
}

export const setStoredLocale = (locale: string): void => {
  try {
    localStorage.setItem('app-locale', locale)
  } catch (error) {
    console.warn('Failed to store locale preference:', error)
  }
}

export const detectBrowserLocale = (): string => {
  if (typeof navigator === 'undefined') return defaultLocale
  
  const browserLocale = navigator.language || (navigator as any).userLanguage
  const shortLocale = browserLocale.split('-')[0]
  
  // Check if we support the full locale or just the language part
  const supportedCodes = enabledLocales.map(l => l.code)
  if (supportedCodes.includes(browserLocale)) {
    return browserLocale
  } else if (supportedCodes.includes(shortLocale)) {
    return shortLocale
  }
  
  return defaultLocale
}

export const getInitialLocale = (): string => {
  // Priority: stored > URL parameter > browser > default
  
  // Check URL parameter
  if (typeof window !== 'undefined') {
    const urlParams = new URLSearchParams(window.location.search)
    const urlLocale = urlParams.get('locale')
    if (urlLocale && enabledLocales.find(l => l.code === urlLocale)) {
      setStoredLocale(urlLocale)
      return urlLocale
    }
  }
  
  // Check stored preference
  const storedLocale = getStoredLocale()
  if (enabledLocales.find(l => l.code === storedLocale)) {
    return storedLocale
  }
  
  // Check browser preference
  const browserLocale = detectBrowserLocale()
  if (enabledLocales.find(l => l.code === browserLocale)) {
    setStoredLocale(browserLocale)
    return browserLocale
  }
  
  return defaultLocale
}

// i18n configuration
export const createAppI18n = async () => {
  const translations = await importTranslations()
  const initialLocale = getInitialLocale()
  
  const i18n = createI18n({
    locale: initialLocale,
    fallbackLocale: defaultLocale,
    messages: translations,
    legacy: false, // Use Composition API
    globalInjection: true,
    warnHtmlMessage: false,
    silentTranslationWarn: true,
    silentFallbackWarn: true,
    formatFallbackMessages: true,
    
    // Number formats
    numberFormats: {
      en: {
        currency: {
          style: 'currency',
          currency: 'USD',
          notation: 'standard'
        },
        decimal: {
          style: 'decimal',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        },
        percent: {
          style: 'percent',
          useGrouping: false
        }
      },
      es: {
        currency: {
          style: 'currency',
          currency: 'EUR',
          notation: 'standard'
        },
        decimal: {
          style: 'decimal',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        },
        percent: {
          style: 'percent',
          useGrouping: false
        }
      },
      fr: {
        currency: {
          style: 'currency',
          currency: 'EUR',
          notation: 'standard'
        },
        decimal: {
          style: 'decimal',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        },
        percent: {
          style: 'percent',
          useGrouping: false
        }
      },
      de: {
        currency: {
          style: 'currency',
          currency: 'EUR',
          notation: 'standard'
        },
        decimal: {
          style: 'decimal',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        },
        percent: {
          style: 'percent',
          useGrouping: false
        }
      },
      ar: {
        currency: {
          style: 'currency',
          currency: 'SAR',
          notation: 'standard'
        },
        decimal: {
          style: 'decimal',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        },
        percent: {
          style: 'percent',
          useGrouping: false
        }
      }
    },
    
    // Date/time formats
    datetimeFormats: {
      en: {
        short: {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        },
        long: {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          weekday: 'short',
          hour: 'numeric',
          minute: 'numeric'
        }
      },
      es: {
        short: {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        },
        long: {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          weekday: 'short',
          hour: 'numeric',
          minute: 'numeric',
          hour12: false
        }
      },
      fr: {
        short: {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        },
        long: {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          weekday: 'short',
          hour: 'numeric',
          minute: 'numeric',
          hour12: false
        }
      },
      de: {
        short: {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        },
        long: {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          weekday: 'short',
          hour: 'numeric',
          minute: 'numeric',
          hour12: false
        }
      },
      ar: {
        short: {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        },
        long: {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          weekday: 'short',
          hour: 'numeric',
          minute: 'numeric'
        }
      }
    }
  })
  
  // Set document direction based on locale
  if (typeof document !== 'undefined') {
    const direction = getLocaleDirection(initialLocale)
    document.documentElement.dir = direction
    document.documentElement.lang = initialLocale
  }
  
  return i18n
}

// Helper function to change locale
export const changeLocale = async (i18nInstance: any, newLocale: string) => {
  // Load new locale messages if not already loaded
  if (!i18nInstance.global.availableLocales.includes(newLocale)) {
    try {
      const translations = await importTranslations()
      i18nInstance.global.setLocaleMessage(newLocale, translations[newLocale])
    } catch (error) {
      console.error(`Failed to load locale ${newLocale}:`, error)
      return false
    }
  }
  
  // Change locale
  i18nInstance.global.locale.value = newLocale
  setStoredLocale(newLocale)
  
  // Update document attributes
  if (typeof document !== 'undefined') {
    const direction = getLocaleDirection(newLocale)
    document.documentElement.dir = direction
    document.documentElement.lang = newLocale
  }
  
  return true
}

// Pluralization rules for different languages
export const pluralizationRules = {
  ar: (choice: number) => {
    if (choice === 0) return 0      // zero
    if (choice === 1) return 1      // one
    if (choice === 2) return 2      // two
    if (choice >= 3 && choice <= 10) return 3  // few
    if (choice >= 11 && choice <= 99) return 4 // many
    return 5                        // other
  }
  // Add more complex pluralization rules as needed
}