import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8002/api/v1'

// API Response interfaces
interface Language {
  id: number
  code: string
  name: string
  native_name: string
  flag_emoji: string
  direction: 'ltr' | 'rtl'
  is_active: boolean
  is_default: boolean
  sort_order: number
  date_format: string
  js_date_format: string
  number_format: {
    thousand_separator: string
    decimal_separator: string
    decimal_places: number
  }
  currency_format: {
    code: string
    position: 'before' | 'after'
    symbol: string
  }
}

interface LanguageWithStats {
  language: Language
  stats: {
    total_keys: number
    translated_keys: number
    construction_terms: number
    completion_percentage: number
    missing_keys: number
  }
}

interface TranslationsResponse {
  language: {
    code: string
    name: string
    native_name: string
    direction: 'ltr' | 'rtl'
    flag_emoji: string
  }
  translations: Record<string, any>
  meta: {
    namespaces?: string[]
    cached_at: string
  }
}

interface ConstructionTerm {
  value: string
  pronunciation: string | null
  metadata: {
    category: string
    pronunciation: string | null
    usage_context: string | null
    related_terms: string[]
    safety_notes: string | null
  }
}

interface ConstructionTermsResponse {
  language: string
  category: string | null
  terms: Record<string, Record<string, ConstructionTerm>>
  meta: {
    cached_at: string
    total_categories: number
  }
}

class LocalizationApiService {
  private cache = new Map<string, { data: any; expiry: number }>()
  private cacheExpiry = 1000 * 60 * 60 // 1 hour

  private getCacheKey(method: string, params: any): string {
    return `${method}:${JSON.stringify(params)}`
  }

  private isCacheValid(expiry: number): boolean {
    return Date.now() < expiry
  }

  private setCache(key: string, data: any): void {
    this.cache.set(key, {
      data,
      expiry: Date.now() + this.cacheExpiry
    })
  }

  private getCache<T>(key: string): T | null {
    const cached = this.cache.get(key)
    if (cached && this.isCacheValid(cached.expiry)) {
      return cached.data as T
    }
    this.cache.delete(key)
    return null
  }

  /**
   * Get all active languages with completion stats
   */
  async getLanguages(): Promise<LanguageWithStats[]> {
    const cacheKey = this.getCacheKey('languages', {})
    const cached = this.getCache<LanguageWithStats[]>(cacheKey)
    
    if (cached) {
      return cached
    }

    try {
      const response = await axios.get(`${API_BASE_URL}/localization/languages`)
      const languages = response.data.data
      this.setCache(cacheKey, languages)
      return languages
    } catch (error) {
      console.error('Failed to fetch languages:', error)
      throw new Error('Failed to fetch languages')
    }
  }

  /**
   * Get translations for a specific language
   */
  async getTranslations(
    languageCode: string,
    namespaces?: string[]
  ): Promise<TranslationsResponse> {
    const cacheKey = this.getCacheKey('translations', { languageCode, namespaces })
    const cached = this.getCache<TranslationsResponse>(cacheKey)
    
    if (cached) {
      return cached
    }

    try {
      const params = namespaces ? { namespaces: namespaces.join(',') } : {}
      const response = await axios.get(
        `${API_BASE_URL}/localization/translations/${languageCode}`,
        { params }
      )
      const translations = response.data.data
      this.setCache(cacheKey, translations)
      return translations
    } catch (error) {
      console.error(`Failed to fetch translations for ${languageCode}:`, error)
      throw new Error(`Failed to fetch translations for ${languageCode}`)
    }
  }

  /**
   * Get construction terms for a specific language
   */
  async getConstructionTerms(
    languageCode: string,
    category?: string
  ): Promise<ConstructionTermsResponse> {
    const cacheKey = this.getCacheKey('construction-terms', { languageCode, category })
    const cached = this.getCache<ConstructionTermsResponse>(cacheKey)
    
    if (cached) {
      return cached
    }

    try {
      const params = category ? { category } : {}
      const response = await axios.get(
        `${API_BASE_URL}/localization/construction-terms/${languageCode}`,
        { params }
      )
      const terms = response.data.data
      this.setCache(cacheKey, terms)
      return terms
    } catch (error) {
      console.error(`Failed to fetch construction terms for ${languageCode}:`, error)
      throw new Error(`Failed to fetch construction terms for ${languageCode}`)
    }
  }

  /**
   * Search translations
   */
  async searchTranslations(
    query: string,
    languageCode: string,
    namespaces?: string[]
  ): Promise<any[]> {
    try {
      const params = {
        q: query,
        language: languageCode,
        namespaces: namespaces?.join(',')
      }
      const response = await axios.get(`${API_BASE_URL}/localization/search`, { params })
      return response.data.data.results
    } catch (error) {
      console.error('Failed to search translations:', error)
      throw new Error('Failed to search translations')
    }
  }

  /**
   * Get specific translation by key
   */
  async getTranslation(
    languageCode: string,
    key: string,
    fallback?: string
  ): Promise<string> {
    try {
      const params = fallback ? { fallback } : {}
      const response = await axios.get(
        `${API_BASE_URL}/localization/translation/${languageCode}/${key}`,
        { params }
      )
      return response.data.data.translation
    } catch (error) {
      console.error(`Failed to get translation for key ${key}:`, error)
      return fallback || key
    }
  }

  /**
   * Get language statistics
   */
  async getLanguageStats(languageCode: string): Promise<any> {
    const cacheKey = this.getCacheKey('language-stats', { languageCode })
    const cached = this.getCache<any>(cacheKey)
    
    if (cached) {
      return cached
    }

    try {
      const response = await axios.get(
        `${API_BASE_URL}/localization/language-stats/${languageCode}`
      )
      const stats = response.data.data
      this.setCache(cacheKey, stats)
      return stats
    } catch (error) {
      console.error(`Failed to fetch stats for ${languageCode}:`, error)
      throw new Error(`Failed to fetch language statistics`)
    }
  }

  /**
   * Clear local cache
   */
  clearCache(): void {
    this.cache.clear()
  }

  /**
   * Clear cache for specific language
   */
  clearLanguageCache(languageCode: string): void {
    const keysToDelete = Array.from(this.cache.keys()).filter(key =>
      key.includes(languageCode)
    )
    keysToDelete.forEach(key => this.cache.delete(key))
  }
}

export const localizationApi = new LocalizationApiService()
export type { Language, LanguageWithStats, TranslationsResponse, ConstructionTerm, ConstructionTermsResponse }