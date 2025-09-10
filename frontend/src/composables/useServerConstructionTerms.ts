import { ref, computed, reactive } from 'vue'
import { localizationApi, type ConstructionTerm } from '@/services/localizationApi'
import { useServerI18n } from '@/composables/useServerI18n'

// Global cache for construction terms
const termsCache = reactive<Record<string, Record<string, Record<string, ConstructionTerm>>>>({})
const isLoadingTerms = ref(false)
const termsError = ref<string | null>(null)

export function useServerConstructionTerms() {
  const { currentLanguage, locale } = useServerI18n()

  /**
   * Load construction terms for a specific language and category
   */
  const loadConstructionTerms = async (languageCode?: string, category?: string) => {
    const targetLanguage = languageCode || currentLanguage.value?.code || 'en'
    const cacheKey = `${targetLanguage}${category ? `:${category}` : ''}`
    
    // Return cached data if available
    if (termsCache[cacheKey] && !category) {
      return termsCache[cacheKey]
    }

    isLoadingTerms.value = true
    termsError.value = null

    try {
      const response = await localizationApi.getConstructionTerms(targetLanguage, category)
      
      if (!termsCache[targetLanguage]) {
        termsCache[targetLanguage] = {}
      }
      
      // Store the terms in cache
      Object.assign(termsCache[targetLanguage], response.terms)
      
      return response.terms
    } catch (error) {
      termsError.value = `Failed to load construction terms: ${error}`
      console.error('Construction terms loading error:', error)
      throw error
    } finally {
      isLoadingTerms.value = false
    }
  }

  /**
   * Get a specific construction term
   */
  const getConstructionTerm = (category: string, termKey: string, languageCode?: string): ConstructionTerm | null => {
    const targetLanguage = languageCode || currentLanguage.value?.code || 'en'
    
    if (termsCache[targetLanguage] && 
        termsCache[targetLanguage][category] && 
        termsCache[targetLanguage][category][termKey]) {
      return termsCache[targetLanguage][category][termKey]
    }
    
    return null
  }

  /**
   * Get all terms for a category
   */
  const getCategoryTerms = (category: string, languageCode?: string): Record<string, ConstructionTerm> => {
    const targetLanguage = languageCode || currentLanguage.value?.code || 'en'
    
    if (termsCache[targetLanguage] && termsCache[targetLanguage][category]) {
      return termsCache[targetLanguage][category]
    }
    
    return {}
  }

  /**
   * Get all available categories
   */
  const getAvailableCategories = (languageCode?: string): string[] => {
    const targetLanguage = languageCode || currentLanguage.value?.code || 'en'
    
    if (termsCache[targetLanguage]) {
      return Object.keys(termsCache[targetLanguage])
    }
    
    return []
  }

  /**
   * Search construction terms
   */
  const searchConstructionTerms = async (
    query: string, 
    languageCode?: string,
    categories?: string[]
  ): Promise<any[]> => {
    const targetLanguage = languageCode || currentLanguage.value?.code || 'en'
    
    try {
      const namespaces = ['construction']
      const results = await localizationApi.searchTranslations(query, targetLanguage, namespaces)
      
      // Filter results by categories if specified
      if (categories && categories.length > 0) {
        return results.filter(result => 
          result.group && categories.includes(result.group)
        )
      }
      
      return results.filter(result => result.is_construction_term)
    } catch (error) {
      console.error('Construction terms search error:', error)
      return []
    }
  }

  /**
   * Get term with pronunciation and metadata
   */
  const getTermDetails = (
    category: string, 
    termKey: string, 
    languageCode?: string
  ): {
    term: string
    pronunciation?: string
    usageContext?: string
    relatedTerms: string[]
    safetyNotes?: string
    category: string
  } | null => {
    const termData = getConstructionTerm(category, termKey, languageCode)
    
    if (!termData) return null
    
    return {
      term: termData.value,
      pronunciation: termData.pronunciation || undefined,
      usageContext: termData.metadata?.usage_context || undefined,
      relatedTerms: termData.metadata?.related_terms || [],
      safetyNotes: termData.metadata?.safety_notes || undefined,
      category: category
    }
  }

  /**
   * Get term with fallback
   */
  const getTermWithFallback = (
    category: string, 
    termKey: string, 
    languageCode?: string,
    fallbackLanguage: string = 'en'
  ): string => {
    // Try to get the term in the requested language
    let term = getConstructionTerm(category, termKey, languageCode)
    
    if (term) {
      return term.value
    }
    
    // Fallback to specified fallback language
    if (languageCode !== fallbackLanguage) {
      term = getConstructionTerm(category, termKey, fallbackLanguage)
      if (term) {
        return term.value
      }
    }
    
    // Ultimate fallback to the term key
    return termKey
  }

  /**
   * Check if terms are loaded for a language
   */
  const areTermsLoaded = (languageCode?: string): boolean => {
    const targetLanguage = languageCode || currentLanguage.value?.code || 'en'
    return Boolean(termsCache[targetLanguage] && Object.keys(termsCache[targetLanguage]).length > 0)
  }

  /**
   * Clear terms cache
   */
  const clearTermsCache = (languageCode?: string) => {
    if (languageCode) {
      delete termsCache[languageCode]
    } else {
      Object.keys(termsCache).forEach(key => delete termsCache[key])
    }
  }

  /**
   * Preload terms for current language
   */
  const preloadTerms = async () => {
    const targetLanguage = currentLanguage.value?.code
    if (targetLanguage && !areTermsLoaded(targetLanguage)) {
      await loadConstructionTerms(targetLanguage)
    }
  }

  // Computed properties
  const currentLanguageTerms = computed(() => {
    const targetLanguage = currentLanguage.value?.code || 'en'
    return termsCache[targetLanguage] || {}
  })

  const currentCategories = computed(() => {
    return getAvailableCategories()
  })

  const isReady = computed(() => {
    return areTermsLoaded() && !isLoadingTerms.value
  })

  return {
    // State
    termsCache: computed(() => termsCache),
    currentLanguageTerms,
    currentCategories,
    isLoadingTerms: computed(() => isLoadingTerms.value),
    termsError: computed(() => termsError.value),
    isReady,

    // Methods
    loadConstructionTerms,
    getConstructionTerm,
    getCategoryTerms,
    getAvailableCategories,
    searchConstructionTerms,
    getTermDetails,
    getTermWithFallback,
    areTermsLoaded,
    clearTermsCache,
    preloadTerms
  }
}