import { ref, computed, reactive, watch } from 'vue'
import { localizationApi, type Language, type LanguageWithStats } from '@/services/localizationApi'

// Global state - singleton pattern
const currentLanguage = ref<Language | null>(null)
const availableLanguages = ref<Language[]>([])
const translations = reactive<Record<string, any>>({})
const constructionTerms = reactive<Record<string, any>>({})
const isLoading = ref(false)
const error = ref<string | null>(null)
const isInitialized = ref(false)

// Storage keys
const LANGUAGE_STORAGE_KEY = 'preferred-language'

// Track active API calls to prevent duplicates
let initializationPromise: Promise<void> | null = null
let languageChangePromises = new Map<string, Promise<void>>()

export function useServerI18n() {
  /**
   * Initialize the localization system - singleton pattern
   */
  const initialize = async () => {
    // If already initialized, return
    if (isInitialized.value) {
      return
    }

    // If initialization is in progress, wait for it
    if (initializationPromise) {
      return initializationPromise
    }

    // Start initialization
    initializationPromise = (async () => {
      isLoading.value = true
      error.value = null

      try {
        // Load available languages
        const languagesData = await localizationApi.getLanguages()
        availableLanguages.value = languagesData.map(item => item.language)

        // Set current language from storage or use default
        const storedLanguage = localStorage.getItem(LANGUAGE_STORAGE_KEY)
        const defaultLanguage = availableLanguages.value.find(lang => lang.is_default)
        const targetLanguage = storedLanguage
          ? availableLanguages.value.find(lang => lang.code === storedLanguage)
          : defaultLanguage

        if (targetLanguage) {
          await changeLanguage(targetLanguage.code)
        }

        isInitialized.value = true
      } catch (err) {
        error.value = 'Failed to initialize localization system'
        console.error('Localization initialization error:', err)
        throw err
      } finally {
        isLoading.value = false
        // Clear the promise after completion
        initializationPromise = null
      }
    })()

    return initializationPromise
  }

  /**
   * Change current language - prevents duplicate calls
   */
  const changeLanguage = async (languageCode: string) => {
    // If already current language, return early
    if (currentLanguage.value?.code === languageCode) {
      return
    }

    // If language change is in progress for this code, wait for it
    if (languageChangePromises.has(languageCode)) {
      return languageChangePromises.get(languageCode)!
    }

    // Start language change
    const changePromise = (async () => {
      isLoading.value = true
      error.value = null

      try {
        const language = availableLanguages.value.find(lang => lang.code === languageCode)
        if (!language) {
          throw new Error(`Language ${languageCode} not found`)
        }

        // Load translations for the language
        const [translationsData, constructionData] = await Promise.all([
          localizationApi.getTranslations(languageCode),
          localizationApi.getConstructionTerms(languageCode)
        ])

        // Update state
        currentLanguage.value = language
        
        // Clear existing translations and assign new ones
        Object.keys(translations).forEach(key => delete translations[key])
        Object.assign(translations, translationsData.translations)
        
        Object.keys(constructionTerms).forEach(key => delete constructionTerms[key])
        Object.assign(constructionTerms, constructionData.terms)

        // Store preference
        localStorage.setItem(LANGUAGE_STORAGE_KEY, languageCode)

        // Update document attributes
        document.documentElement.lang = languageCode
        document.documentElement.dir = language.direction

        console.log(`✅ Language changed to ${languageCode}`)

      } catch (err) {
        error.value = `Failed to change language to ${languageCode}`
        console.error('Language change error:', err)
        throw err
      } finally {
        isLoading.value = false
        // Clean up promise
        languageChangePromises.delete(languageCode)
      }
    })()

    languageChangePromises.set(languageCode, changePromise)
    return changePromise
  }

  /**
   * Get translation by key with dot notation support
   */
  const t = (key: string, params?: Record<string, string | number>): string => {
    const keys = key.split('.')
    let value: any = translations

    // Navigate through nested object
    for (const k of keys) {
      if (value && typeof value === 'object' && k in value) {
        value = value[k]
      } else {
        // Return key as fallback if not found
        return key
      }
    }

    if (typeof value !== 'string') {
      return key
    }

    // Replace parameters if provided
    if (params) {
      let result = value
      for (const [paramKey, paramValue] of Object.entries(params)) {
        result = result.replace(new RegExp(`{${paramKey}}`, 'g'), String(paramValue))
      }
      return result
    }

    return value
  }

  /**
   * Get construction term
   */
  const getConstructionTerm = (category: string, termKey: string): {
    value: string
    pronunciation?: string
    metadata?: any
  } => {
    if (constructionTerms[category] && constructionTerms[category][termKey]) {
      const term = constructionTerms[category][termKey]
      return {
        value: term.value,
        pronunciation: term.pronunciation || undefined,
        metadata: term.metadata
      }
    }

    return {
      value: termKey // Fallback to key
    }
  }

  /**
   * Check if a translation key exists
   */
  const hasTranslation = (key: string): boolean => {
    const keys = key.split('.')
    let value: any = translations

    for (const k of keys) {
      if (value && typeof value === 'object' && k in value) {
        value = value[k]
      } else {
        return false
      }
    }

    return typeof value === 'string'
  }

  /**
   * Get available namespaces
   */
  const getNamespaces = (): string[] => {
    return Object.keys(translations)
  }

  /**
   * Get construction categories
   */
  const getConstructionCategories = (): string[] => {
    return Object.keys(constructionTerms)
  }

  /**
   * Search translations
   */
  const searchTranslations = async (
    query: string,
    namespaces?: string[]
  ): Promise<any[]> => {
    if (!currentLanguage.value) {
      return []
    }

    try {
      return await localizationApi.searchTranslations(
        query,
        currentLanguage.value.code,
        namespaces
      )
    } catch (err) {
      console.error('Search translations error:', err)
      return []
    }
  }

  /**
   * Format number according to language settings
   */
  const formatNumber = (num: number, decimals?: number): string => {
    if (!currentLanguage.value) {
      return num.toString()
    }

    const { number_format } = currentLanguage.value
    const finalDecimals = decimals ?? number_format.decimal_places

    return new Intl.NumberFormat(currentLanguage.value.code, {
      minimumFractionDigits: finalDecimals,
      maximumFractionDigits: finalDecimals
    }).format(num)
  }

  /**
   * Format currency according to language settings
   */
  const formatCurrency = (amount: number, decimals?: number): string => {
    if (!currentLanguage.value) {
      return amount.toString()
    }

    const { currency_format } = currentLanguage.value
    const finalDecimals = decimals ?? currentLanguage.value.number_format.decimal_places

    return new Intl.NumberFormat(currentLanguage.value.code, {
      style: 'currency',
      currency: currency_format.code,
      minimumFractionDigits: finalDecimals,
      maximumFractionDigits: finalDecimals
    }).format(amount)
  }

  /**
   * Format date according to language settings
   */
  const formatDate = (date: Date | string, options?: Intl.DateTimeFormatOptions): string => {
    if (!currentLanguage.value) {
      return date.toString()
    }

    const dateObj = typeof date === 'string' ? new Date(date) : date
    return new Intl.DateTimeFormat(currentLanguage.value.code, options).format(dateObj)
  }

  /**
   * Clear cache
   */
  const clearCache = () => {
    localizationApi.clearCache()
  }

  // Computed properties
  const locale = computed(() => currentLanguage.value?.code || 'en')
  const direction = computed(() => currentLanguage.value?.direction || 'ltr')
  const isRTL = computed(() => direction.value === 'rtl')

  // Return reactive interface
  return {
    // State
    currentLanguage: computed(() => currentLanguage.value),
    availableLanguages: computed(() => availableLanguages.value),
    translations: computed(() => translations),
    constructionTerms: computed(() => constructionTerms),
    isLoading: computed(() => isLoading.value),
    error: computed(() => error.value),
    isInitialized: computed(() => isInitialized.value),
    locale,
    direction,
    isRTL,

    // Methods
    initialize,
    changeLanguage,
    t,
    getConstructionTerm,
    hasTranslation,
    getNamespaces,
    getConstructionCategories,
    searchTranslations,
    formatNumber,
    formatCurrency,
    formatDate,
    clearCache
  }
}