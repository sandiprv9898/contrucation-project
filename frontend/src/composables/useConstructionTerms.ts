/**
 * Construction Industry Terminology Composable
 * 
 * Provides access to construction-specific terminology and translations
 * with search, filtering, and category-based access functionality.
 */

import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

export type ConstructionCategory = 
  | 'materials'
  | 'equipment' 
  | 'safety'
  | 'phases'
  | 'trades'
  | 'measurements'
  | 'documentation'
  | 'quality'
  | 'safety_procedures'
  | 'project_management'

export interface ConstructionTerm {
  key: string
  translation: string
  category: ConstructionCategory
  searchableText: string
}

export function useConstructionTerms() {
  const { t } = useI18n()
  const searchQuery = ref('')
  const selectedCategory = ref<ConstructionCategory | 'all'>('all')

  /**
   * Get all terms for a specific category
   */
  const getTermsByCategory = (category: ConstructionCategory): ConstructionTerm[] => {
    const categoryObj = t(`construction.${category}`, {}, { returnObjects: true }) as Record<string, string>
    
    return Object.entries(categoryObj).map(([key, value]) => ({
      key,
      translation: value,
      category,
      searchableText: `${key} ${value}`.toLowerCase()
    }))
  }

  /**
   * Get all construction terms across all categories
   */
  const allTerms = computed(() => {
    const categories: ConstructionCategory[] = [
      'materials', 'equipment', 'safety', 'phases', 'trades', 
      'measurements', 'documentation', 'quality', 'safety_procedures', 'project_management'
    ]
    
    return categories.flatMap(category => getTermsByCategory(category))
  })

  /**
   * Filtered terms based on search query and selected category
   */
  const filteredTerms = computed(() => {
    let terms = allTerms.value

    // Filter by category
    if (selectedCategory.value !== 'all') {
      terms = terms.filter(term => term.category === selectedCategory.value)
    }

    // Filter by search query
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase()
      terms = terms.filter(term => term.searchableText.includes(query))
    }

    return terms
  })

  /**
   * Get translation for a specific construction term
   */
  const getConstructionTerm = (category: ConstructionCategory, key: string): string => {
    return t(`construction.${category}.${key}`)
  }

  /**
   * Get all available categories with their display names
   */
  const categories = computed(() => [
    { key: 'all', label: t('common.labels.all', 'All') },
    { key: 'materials', label: t('construction.categories.materials', 'Materials') },
    { key: 'equipment', label: t('construction.categories.equipment', 'Equipment') },
    { key: 'safety', label: t('construction.categories.safety', 'Safety') },
    { key: 'phases', label: t('construction.categories.phases', 'Project Phases') },
    { key: 'trades', label: t('construction.categories.trades', 'Trades') },
    { key: 'measurements', label: t('construction.categories.measurements', 'Measurements') },
    { key: 'documentation', label: t('construction.categories.documentation', 'Documentation') },
    { key: 'quality', label: t('construction.categories.quality', 'Quality') },
    { key: 'safety_procedures', label: t('construction.categories.safety_procedures', 'Safety Procedures') },
    { key: 'project_management', label: t('construction.categories.project_management', 'Project Management') }
  ])

  /**
   * Common materials terms for quick access
   */
  const commonMaterials = computed(() => [
    { key: 'concrete', label: t('construction.materials.concrete') },
    { key: 'steel', label: t('construction.materials.steel') },
    { key: 'lumber', label: t('construction.materials.lumber') },
    { key: 'brick', label: t('construction.materials.brick') },
    { key: 'cement', label: t('construction.materials.cement') }
  ])

  /**
   * Common equipment terms for quick access
   */
  const commonEquipment = computed(() => [
    { key: 'excavator', label: t('construction.equipment.excavator') },
    { key: 'crane', label: t('construction.equipment.crane') },
    { key: 'bulldozer', label: t('construction.equipment.bulldozer') },
    { key: 'concrete_mixer', label: t('construction.equipment.concrete_mixer') },
    { key: 'forklift', label: t('construction.equipment.forklift') }
  ])

  /**
   * Essential safety equipment
   */
  const safetyEquipment = computed(() => [
    { key: 'hard_hat', label: t('construction.safety.hard_hat') },
    { key: 'safety_vest', label: t('construction.safety.safety_vest') },
    { key: 'work_boots', label: t('construction.safety.work_boots') },
    { key: 'safety_glasses', label: t('construction.safety.safety_glasses') },
    { key: 'work_gloves', label: t('construction.safety.work_gloves') }
  ])

  /**
   * Project phases in chronological order
   */
  const projectPhases = computed(() => [
    { key: 'planning', label: t('construction.phases.planning') },
    { key: 'design', label: t('construction.phases.design') },
    { key: 'permits', label: t('construction.phases.permits') },
    { key: 'site_preparation', label: t('construction.phases.site_preparation') },
    { key: 'excavation', label: t('construction.phases.excavation') },
    { key: 'foundation', label: t('construction.phases.foundation') },
    { key: 'structural', label: t('construction.phases.structural') },
    { key: 'framing', label: t('construction.phases.framing') },
    { key: 'roofing', label: t('construction.phases.roofing') },
    { key: 'interior_finish', label: t('construction.phases.interior_finish') },
    { key: 'inspection', label: t('construction.phases.inspection') },
    { key: 'closeout', label: t('construction.phases.closeout') }
  ])

  /**
   * Common trade specializations
   */
  const trades = computed(() => [
    { key: 'general_contractor', label: t('construction.trades.general_contractor') },
    { key: 'electrician', label: t('construction.trades.electrician') },
    { key: 'plumber', label: t('construction.trades.plumber') },
    { key: 'carpenter', label: t('construction.trades.carpenter') },
    { key: 'mason', label: t('construction.trades.mason') },
    { key: 'painter', label: t('construction.trades.painter') }
  ])

  /**
   * Search terms by query string
   */
  const searchTerms = (query: string): ConstructionTerm[] => {
    if (!query) return []
    
    const searchQuery = query.toLowerCase()
    return allTerms.value.filter(term => 
      term.searchableText.includes(searchQuery)
    ).slice(0, 20) // Limit results for performance
  }

  /**
   * Get term suggestions based on partial input
   */
  const getTermSuggestions = (input: string): string[] => {
    if (!input || input.length < 2) return []
    
    const query = input.toLowerCase()
    const suggestions = allTerms.value
      .filter(term => term.key.includes(query) || term.translation.toLowerCase().includes(query))
      .map(term => term.translation)
      .slice(0, 10)
    
    return [...new Set(suggestions)] // Remove duplicates
  }

  /**
   * Check if a term exists in the construction dictionary
   */
  const isConstructionTerm = (term: string): boolean => {
    const query = term.toLowerCase()
    return allTerms.value.some(t => 
      t.key.toLowerCase() === query || t.translation.toLowerCase() === query
    )
  }

  return {
    // Reactive state
    searchQuery,
    selectedCategory,
    
    // Computed properties
    allTerms,
    filteredTerms,
    categories,
    commonMaterials,
    commonEquipment,
    safetyEquipment,
    projectPhases,
    trades,
    
    // Methods
    getTermsByCategory,
    getConstructionTerm,
    searchTerms,
    getTermSuggestions,
    isConstructionTerm
  }
}