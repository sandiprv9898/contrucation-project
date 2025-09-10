<template>
  <div class="relative inline-block">
    <!-- Trigger element -->
    <div
      ref="triggerRef"
      @mouseenter="showTooltip"
      @mouseleave="hideTooltip"
      @focus="showTooltip"
      @blur="hideTooltip"
      class="cursor-help underline decoration-dotted decoration-gray-400 hover:decoration-blue-500"
      :class="triggerClasses"
    >
      <slot>{{ displayText }}</slot>
    </div>

    <!-- Tooltip -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div 
        v-if="isVisible && termData"
        ref="tooltipRef"
        class="absolute z-50 w-80 p-4 bg-white rounded-lg shadow-xl border border-gray-200"
        :class="positionClasses"
        role="tooltip"
        :aria-describedby="`tooltip-${termKey}`"
      >
        <!-- Header -->
        <div class="flex items-start justify-between mb-3">
          <div>
            <h4 class="font-semibold text-gray-900 text-sm">
              {{ termData.translation }}
            </h4>
            <p class="text-xs text-gray-500 mt-1">
              {{ categoryLabel }}
            </p>
          </div>
          <div class="flex items-center gap-1 ml-3">
            <span class="text-lg">{{ getCurrentFlag() }}</span>
            <Info class="h-4 w-4 text-blue-500" />
          </div>
        </div>

        <!-- Term in original language (if different) -->
        <div v-if="showOriginalTerm" class="mb-3 p-2 bg-gray-50 rounded">
          <p class="text-xs text-gray-600 mb-1">
            {{ $t('construction.labels.original_term', 'Original Term') }}:
          </p>
          <p class="font-mono text-sm text-gray-900">{{ termKey.replace('_', ' ') }}</p>
        </div>

        <!-- Description (if available) -->
        <div v-if="description" class="mb-3">
          <p class="text-sm text-gray-700 leading-relaxed">{{ description }}</p>
        </div>

        <!-- Pronunciation guide (for non-Latin scripts) -->
        <div v-if="pronunciation" class="mb-3 p-2 bg-blue-50 rounded">
          <p class="text-xs text-blue-600 mb-1">
            {{ $t('construction.labels.pronunciation', 'Pronunciation') }}:
          </p>
          <p class="text-sm text-blue-800 font-medium">{{ pronunciation }}</p>
        </div>

        <!-- Usage context -->
        <div v-if="usageContext" class="mb-3">
          <p class="text-xs text-gray-600 mb-1">
            {{ $t('construction.labels.usage', 'Usage Context') }}:
          </p>
          <p class="text-sm text-gray-700">{{ usageContext }}</p>
        </div>

        <!-- Related terms -->
        <div v-if="relatedTerms.length > 0" class="border-t border-gray-100 pt-3">
          <p class="text-xs text-gray-600 mb-2">
            {{ $t('construction.labels.related_terms', 'Related Terms') }}:
          </p>
          <div class="flex flex-wrap gap-1">
            <span
              v-for="related in relatedTerms"
              :key="related.key"
              class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200 cursor-pointer transition-colors"
              @click="$emit('termClicked', related.key, related.category)"
            >
              {{ related.translation }}
            </span>
          </div>
        </div>

        <!-- Footer with category info -->
        <div class="mt-3 pt-2 border-t border-gray-100">
          <p class="text-xs text-gray-500">
            {{ $t('construction.categories.label', 'Category') }}: 
            <span class="font-medium">{{ categoryLabel }}</span>
          </p>
        </div>

        <!-- Tooltip arrow -->
        <div 
          class="absolute w-3 h-3 bg-white border border-gray-200 transform rotate-45"
          :class="arrowClasses"
        ></div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import { Info } from 'lucide-vue-next'
import { useConstructionTerms, type ConstructionCategory } from '@/composables/useConstructionTerms'
import { getLocaleConfig } from '@/locales/config/supported-locales'

// Props
interface Props {
  termKey: string
  category: ConstructionCategory
  placement?: 'top' | 'bottom' | 'left' | 'right' | 'auto'
  showOriginalTerm?: boolean
  description?: string
  pronunciation?: string
  usageContext?: string
  triggerClass?: string
  delay?: number
}

const props = withDefaults(defineProps<Props>(), {
  placement: 'auto',
  showOriginalTerm: true,
  delay: 300
})

// Emits
const emit = defineEmits<{
  termClicked: [termKey: string, category: ConstructionCategory]
  tooltipShown: [termKey: string]
  tooltipHidden: [termKey: string]
}>()

// Composables
const { t, locale } = useI18n()
const { getConstructionTerm, getTermsByCategory } = useConstructionTerms()

// Refs
const triggerRef = ref<HTMLElement>()
const tooltipRef = ref<HTMLElement>()
const showTimeout = ref<NodeJS.Timeout>()
const hideTimeout = ref<NodeJS.Timeout>()

// State
const isVisible = ref(false)
const actualPlacement = ref<'top' | 'bottom' | 'left' | 'right'>('top')

// Computed
const displayText = computed(() => {
  return getConstructionTerm(props.category, props.termKey)
})

const termData = computed(() => {
  const terms = getTermsByCategory(props.category)
  return terms.find(term => term.key === props.termKey)
})

const categoryLabel = computed(() => {
  return t(`construction.categories.${props.category}`, props.category)
})

const triggerClasses = computed(() => {
  return [
    props.triggerClass,
    'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 rounded'
  ].filter(Boolean).join(' ')
})

const positionClasses = computed(() => {
  const baseClasses = 'absolute z-50'
  
  switch (actualPlacement.value) {
    case 'top':
      return `${baseClasses} bottom-full left-1/2 transform -translate-x-1/2 mb-2`
    case 'bottom':
      return `${baseClasses} top-full left-1/2 transform -translate-x-1/2 mt-2`
    case 'left':
      return `${baseClasses} right-full top-1/2 transform -translate-y-1/2 mr-2`
    case 'right':
      return `${baseClasses} left-full top-1/2 transform -translate-y-1/2 ml-2`
    default:
      return `${baseClasses} bottom-full left-1/2 transform -translate-x-1/2 mb-2`
  }
})

const arrowClasses = computed(() => {
  switch (actualPlacement.value) {
    case 'top':
      return 'top-full left-1/2 transform -translate-x-1/2 -mt-1.5 border-t-0 border-l-0'
    case 'bottom':
      return 'bottom-full left-1/2 transform -translate-x-1/2 -mb-1.5 border-b-0 border-r-0'
    case 'left':
      return 'left-full top-1/2 transform -translate-y-1/2 -ml-1.5 border-l-0 border-b-0'
    case 'right':
      return 'right-full top-1/2 transform -translate-y-1/2 -mr-1.5 border-r-0 border-t-0'
    default:
      return 'top-full left-1/2 transform -translate-x-1/2 -mt-1.5 border-t-0 border-l-0'
  }
})

const relatedTerms = computed(() => {
  const allCategoryTerms = getTermsByCategory(props.category)
  const currentTerm = props.termKey.toLowerCase()
  
  // Find related terms based on semantic similarity (simplified approach)
  return allCategoryTerms
    .filter(term => {
      const termKey = term.key.toLowerCase()
      // Don't include the current term
      if (termKey === currentTerm) return false
      
      // Simple relatedness checks
      const isRelated = 
        termKey.includes(currentTerm.split('_')[0]) || // Share first word
        currentTerm.includes(termKey.split('_')[0]) || // Share first word reverse
        (termKey.includes('safety') && currentTerm.includes('safety')) || // Safety related
        (termKey.includes('concrete') && currentTerm.includes('concrete')) || // Material related
        (termKey.includes('electrical') && currentTerm.includes('electrical')) // Trade related
      
      return isRelated
    })
    .slice(0, 4) // Limit to 4 related terms
})

// Methods
const getCurrentFlag = () => {
  const localeConfig = getLocaleConfig(locale.value)
  return localeConfig?.flag || '🇺🇸'
}

const calculateOptimalPlacement = (): 'top' | 'bottom' | 'left' | 'right' => {
  if (props.placement !== 'auto') {
    return props.placement as 'top' | 'bottom' | 'left' | 'right'
  }
  
  if (!triggerRef.value) return 'top'
  
  const rect = triggerRef.value.getBoundingClientRect()
  const viewportHeight = window.innerHeight
  const viewportWidth = window.innerWidth
  
  const spaceAbove = rect.top
  const spaceBelow = viewportHeight - rect.bottom
  const spaceLeft = rect.left
  const spaceRight = viewportWidth - rect.right
  
  // Determine optimal placement based on available space
  if (spaceBelow > 200 && spaceBelow > spaceAbove) {
    return 'bottom'
  } else if (spaceAbove > 200) {
    return 'top'
  } else if (spaceRight > 320) {
    return 'right'
  } else if (spaceLeft > 320) {
    return 'left'
  }
  
  return 'bottom' // Fallback
}

const showTooltip = async () => {
  clearTimeout(hideTimeout.value)
  
  showTimeout.value = setTimeout(async () => {
    actualPlacement.value = calculateOptimalPlacement()
    isVisible.value = true
    
    await nextTick()
    emit('tooltipShown', props.termKey)
  }, props.delay)
}

const hideTooltip = () => {
  clearTimeout(showTimeout.value)
  
  hideTimeout.value = setTimeout(() => {
    isVisible.value = false
    emit('tooltipHidden', props.termKey)
  }, 100)
}

// Cleanup timeouts on unmount
const cleanup = () => {
  clearTimeout(showTimeout.value)
  clearTimeout(hideTimeout.value)
}

// Expose cleanup for parent components
defineExpose({ cleanup })
</script>

<style scoped>
/* Ensure tooltip appears above other elements */
.z-50 {
  z-index: 50;
}

/* Smooth transitions */
.transition {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Custom focus styles for accessibility */
.focus\:ring-2:focus {
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

/* RTL support */
[dir="rtl"] .left-1\/2 {
  @apply right-1/2;
}

[dir="rtl"] .ml-2 {
  @apply mr-2 ml-0;
}

[dir="rtl"] .mr-2 {
  @apply ml-2 mr-0;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .w-80 {
    @apply w-72;
  }
}

@media (max-width: 480px) {
  .w-80 {
    @apply w-64;
  }
}
</style>