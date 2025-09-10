<template>
  <div class="relative">
    <!-- Language Switcher Button -->
    <VButton 
      variant="ghost" 
      size="sm"
      @click="toggleDropdown"
      class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-100 transition-colors"
      :class="{ 'bg-gray-100': isOpen }"
    >
      <span class="text-lg">{{ currentLocale.flag }}</span>
      <span class="font-medium text-sm hidden sm:inline">{{ currentLocale.nativeName }}</span>
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
        class="absolute right-0 top-full mt-2 w-56 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50"
        @click.stop
      >
        <div class="py-1">
          <!-- Header -->
          <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-gray-100">
            {{ $t('common.actions.select_language', 'Select Language') }}
          </div>
          
          <!-- Language Options -->
          <div class="max-h-64 overflow-y-auto">
            <button
              v-for="locale in availableLocales"
              :key="locale.code"
              @click="changeLanguage(locale.code)"
              class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors group"
              :class="{ 
                'bg-blue-50 text-blue-700': currentLocaleCode === locale.code,
                'text-gray-700': currentLocaleCode !== locale.code
              }"
            >
              <!-- Flag -->
              <span class="text-lg">{{ locale.flag }}</span>
              
              <!-- Language Info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                  <span class="font-medium text-sm">{{ locale.nativeName }}</span>
                  <Check 
                    v-if="currentLocaleCode === locale.code"
                    class="h-4 w-4 text-blue-600"
                  />
                </div>
                <span class="text-xs text-gray-500">{{ locale.name }}</span>
              </div>
            </button>
          </div>

          <!-- Footer with additional info -->
          <div class="px-4 py-2 text-xs text-gray-500 border-t border-gray-100">
            <span>{{ $t('common.labels.current', 'Current') }}: {{ currentLocale.name }}</span>
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
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import VButton from '@/components/ui/VButton.vue'
import { ChevronDown, Check } from 'lucide-vue-next'
import { enabledLocales, type LocaleConfig, getLocaleConfig } from '@/locales/config/supported-locales'
import { setStoredLocale } from '@/locales/config/i18n.config'

// Props
interface Props {
  showFlag?: boolean
  showName?: boolean  
  variant?: 'compact' | 'full'
  placement?: 'left' | 'right'
}

const props = withDefaults(defineProps<Props>(), {
  showFlag: true,
  showName: true,
  variant: 'full',
  placement: 'right'
})

// Emits
const emit = defineEmits<{
  languageChanged: [locale: string]
}>()

// Composables
const i18n = useI18n()
const { locale } = i18n

// State
const isOpen = ref(false)
const isChanging = ref(false)

// Computed
const currentLocaleCode = computed(() => locale.value)

const currentLocale = computed(() => {
  return getLocaleConfig(currentLocaleCode.value) || enabledLocales[0]
})

const availableLocales = computed(() => {
  return enabledLocales.sort((a, b) => {
    // Current locale first
    if (a.code === currentLocaleCode.value) return -1
    if (b.code === currentLocaleCode.value) return 1
    // Then alphabetical by native name
    return a.nativeName.localeCompare(b.nativeName)
  })
})

// Methods
const toggleDropdown = () => {
  isOpen.value = !isOpen.value
}

const closeDropdown = () => {
  isOpen.value = false
}

const changeLanguage = async (localeCode: string) => {
  if (isChanging.value || localeCode === currentLocaleCode.value) {
    closeDropdown()
    return
  }

  try {
    isChanging.value = true
    
    // Check if locale is supported
    const supportedCodes = enabledLocales.map(l => l.code)
    if (!supportedCodes.includes(localeCode)) {
      console.error(`Unsupported locale: ${localeCode}`)
      return
    }
    
    // Change locale directly using i18n composable
    locale.value = localeCode
    
    // Store user preference
    setStoredLocale(localeCode)
    
    // Update document attributes
    updatePageMeta(localeCode)
    
    // Emit language changed event
    emit('languageChanged', localeCode)
    
    console.log(`Language changed to: ${localeCode}`)
  } catch (error) {
    console.error('Failed to change language:', error)
  } finally {
    isChanging.value = false
    closeDropdown()
  }
}

const updatePageMeta = (localeCode: string) => {
  const localeConfig = getLocaleConfig(localeCode)
  if (!localeConfig) return

  // Update document direction
  document.documentElement.dir = localeConfig.rtl ? 'rtl' : 'ltr'
  document.documentElement.lang = localeCode
  
  // Update meta tags if needed
  const metaDescription = document.querySelector('meta[name="description"]')
  if (metaDescription) {
    // You can add localized meta descriptions here
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
onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
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
.max-h-64::-webkit-scrollbar {
  width: 6px;
}

.max-h-64::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.max-h-64::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.max-h-64::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Animation for flag emoji */
.transition-transform {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Focus styles for accessibility */
button:focus-visible {
  @apply ring-2 ring-blue-500 ring-offset-2;
}
</style>