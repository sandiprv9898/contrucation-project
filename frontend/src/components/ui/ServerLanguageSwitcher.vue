<template>
  <div class="relative">
    <!-- Language Switcher Button -->
    <VButton 
      variant="ghost" 
      size="sm"
      @click="toggleDropdown"
      :disabled="isLoading"
      class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-100 transition-colors"
      :class="{ 'bg-gray-100': isOpen }"
    >
      <span class="text-lg">{{ currentLanguage?.flag_emoji || '🌐' }}</span>
      <span class="font-medium text-sm hidden sm:inline">{{ currentLanguage?.native_name || 'Language' }}</span>
      <ChevronDown 
        class="h-4 w-4 transition-transform duration-200"
        :class="{ 'rotate-180': isOpen }"
      />
    </VButton>

    <!-- Language Dropdown -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div 
        v-if="isOpen"
        class="absolute right-0 top-full mt-2 w-64 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50"
        @click.stop
      >
        <div class="py-1">
          <!-- Header -->
          <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-gray-100">
            Select Language
          </div>
          
          <!-- Language Options -->
          <div class="max-h-80 overflow-y-auto">
            <button
              v-for="language in availableLanguages"
              :key="language.code"
              @click="changeLanguage(language.code)"
              :disabled="isLoading"
              class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors group"
              :class="{ 
                'bg-blue-50 text-blue-700': currentLanguage?.code === language.code,
                'text-gray-700': currentLanguage?.code !== language.code,
                'opacity-50 cursor-not-allowed': isLoading
              }"
            >
              <!-- Flag -->
              <span class="text-lg">{{ language.flag_emoji }}</span>
              
              <!-- Language Info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                  <span class="font-medium text-sm">{{ language.native_name }}</span>
                  <Check 
                    v-if="currentLanguage?.code === language.code"
                    class="h-4 w-4 text-blue-600"
                  />
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-500">{{ language.name }}</span>
                  <span v-if="showStats && languageStats[language.code]" class="text-xs text-gray-400">
                    {{ languageStats[language.code].completion_percentage }}%
                  </span>
                </div>
              </div>
            </button>
          </div>

          <!-- Loading indicator -->
          <div v-if="isLoading" class="px-4 py-3 border-t border-gray-100">
            <div class="flex items-center justify-center gap-2 text-xs text-gray-500">
              <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-blue-600"></div>
              <span>Loading translations...</span>
            </div>
          </div>

          <!-- Current Language Stats -->
          <div v-else-if="showStats && currentLanguage && languageStats[currentLanguage.code]" 
               class="px-4 py-3 bg-gray-50 border-t border-gray-100">
            <div class="text-xs text-gray-600 space-y-2">
              <div class="flex justify-between items-center">
                <span class="font-medium">Translation Status</span>
                <span class="text-blue-600 font-semibold">{{ languageStats[currentLanguage.code].completion_percentage }}%</span>
              </div>
              
              <!-- Progress bar -->
              <div class="w-full bg-gray-200 rounded-full h-1.5">
                <div 
                  class="bg-blue-600 h-1.5 rounded-full transition-all duration-300"
                  :style="{ width: `${languageStats[currentLanguage.code].completion_percentage}%` }"
                ></div>
              </div>
              
              <!-- Stats details -->
              <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="text-center">
                  <div class="font-medium text-gray-900">{{ languageStats[currentLanguage.code].translated_keys }}</div>
                  <div class="text-gray-500">Translated</div>
                </div>
                <div class="text-center">
                  <div class="font-medium text-gray-900">{{ languageStats[currentLanguage.code].construction_terms }}</div>
                  <div class="text-gray-500">Construction</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div v-if="!isLoading && currentLanguage" class="px-4 py-2 text-xs text-gray-500 border-t border-gray-100">
            <span>Current: {{ currentLanguage.name }}</span>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Backdrop -->
    <div 
      v-if="isOpen"
      class="fixed inset-0 z-40"
      @click="closeDropdown"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted, reactive } from 'vue'
import VButton from '@/components/ui/VButton.vue'
import { ChevronDown, Check } from 'lucide-vue-next'
import { useServerI18n } from '@/composables/useServerI18n'
import { localizationApi } from '@/services/localizationApi'

// Props
interface Props {
  showStats?: boolean
  placement?: 'left' | 'right'
}

const props = withDefaults(defineProps<Props>(), {
  showStats: false,
  placement: 'right'
})

// Emits
const emit = defineEmits<{
  languageChanged: [locale: string]
}>()

// Composables
const {
  currentLanguage,
  availableLanguages,
  isLoading,
  error,
  changeLanguage: changeServerLanguage,
  initialize
} = useServerI18n()

// State
const isOpen = ref(false)
const languageStats = reactive<Record<string, any>>({})

// Methods
const toggleDropdown = async () => {
  if (isLoading.value) return
  
  isOpen.value = !isOpen.value
  
  // Load stats when opening dropdown if needed
  if (isOpen.value && props.showStats && !Object.keys(languageStats).length) {
    await loadLanguageStats()
  }
}

const closeDropdown = () => {
  isOpen.value = false
}

const changeLanguage = async (languageCode: string) => {
  if (isLoading.value || languageCode === currentLanguage.value?.code) {
    closeDropdown()
    return
  }

  try {
    await changeServerLanguage(languageCode)
    emit('languageChanged', languageCode)
    console.log(`Language changed to: ${languageCode}`)
  } catch (error) {
    console.error('Failed to change language:', error)
  } finally {
    closeDropdown()
  }
}

const loadLanguageStats = async () => {
  if (!availableLanguages.value.length) return
  
  try {
    // Load stats for all available languages
    const statsPromises = availableLanguages.value.map(async (lang) => {
      try {
        const stats = await localizationApi.getLanguageStats(lang.code)
        languageStats[lang.code] = stats.stats
      } catch (error) {
        console.error(`Failed to load stats for ${lang.code}:`, error)
      }
    })
    
    await Promise.allSettled(statsPromises)
  } catch (error) {
    console.error('Failed to load language statistics:', error)
  }
}

// Keyboard navigation
const handleKeydown = (event: KeyboardEvent) => {
  if (isOpen.value) {
    if (event.key === 'Escape') {
      closeDropdown()
    }
  }
}

// Lifecycle
onMounted(async () => {
  document.addEventListener('keydown', handleKeydown)
  
  // Note: Initialize is handled by MainLayout - no need to call it here
  // This prevents duplicate API calls
  
  // Load stats if needed (only if showing stats and initialization is complete)
  if (props.showStats && availableLanguages.value.length > 0) {
    await loadLanguageStats()
  }
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<style scoped>
/* RTL support */
:deep([dir="rtl"]) .absolute.right-0 {
  @apply left-0 right-auto;
}

:deep([dir="rtl"]) .text-left {
  @apply text-right;
}

/* Custom scrollbar for the dropdown */
.max-h-80::-webkit-scrollbar {
  width: 6px;
}

.max-h-80::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.max-h-80::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.max-h-80::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Focus styles for accessibility */
button:focus-visible {
  @apply ring-2 ring-blue-500 ring-offset-2;
}
</style>