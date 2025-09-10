<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow mb-8 p-6">
        <div class="flex justify-between items-start">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Server-Side Localization Demo</h1>
            <p class="mt-2 text-gray-600">
              Demonstrating dynamic translations powered by Laravel backend API
            </p>
          </div>
          
          <!-- Language Switcher -->
          <ServerLanguageSwitcher :show-stats="true" />
        </div>
        
        <!-- Current Language Info -->
        <div v-if="currentLanguage" class="mt-6 p-4 bg-blue-50 rounded-lg">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
              <span class="font-medium text-blue-900">Language:</span>
              <span class="ml-2 text-blue-700">{{ currentLanguage.name }}</span>
            </div>
            <div>
              <span class="font-medium text-blue-900">Direction:</span>
              <span class="ml-2 text-blue-700">{{ currentLanguage.direction.toUpperCase() }}</span>
            </div>
            <div>
              <span class="font-medium text-blue-900">Code:</span>
              <span class="ml-2 text-blue-700 font-mono">{{ currentLanguage.code }}</span>
            </div>
          </div>
        </div>

        <!-- Error Display -->
        <div v-if="error" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
          <div class="flex">
            <div class="flex-shrink-0">
              <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-800">{{ error }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="bg-white rounded-lg shadow p-8">
        <div class="flex items-center justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
          <span class="text-gray-600">Loading translations...</span>
        </div>
      </div>

      <!-- Main Content -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- General Translations -->
        <div class="bg-white rounded-lg shadow">
          <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">General Translations</h2>
            <p class="mt-1 text-sm text-gray-500">
              Common interface translations from the server
            </p>
          </div>
          
          <div class="p-6 space-y-4">
            <!-- Common translations examples -->
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div class="space-y-2">
                <div class="font-medium text-gray-700">Navigation:</div>
                <ul class="space-y-1 text-gray-600 ml-4">
                  <li>• {{ t('navigation.dashboard') || 'Dashboard' }}</li>
                  <li>• {{ t('navigation.projects') || 'Projects' }}</li>
                  <li>• {{ t('navigation.settings') || 'Settings' }}</li>
                  <li>• {{ t('navigation.profile') || 'Profile' }}</li>
                </ul>
              </div>
              <div class="space-y-2">
                <div class="font-medium text-gray-700">Actions:</div>
                <ul class="space-y-1 text-gray-600 ml-4">
                  <li>• {{ t('common.save') || 'Save' }}</li>
                  <li>• {{ t('common.cancel') || 'Cancel' }}</li>
                  <li>• {{ t('common.delete') || 'Delete' }}</li>
                  <li>• {{ t('common.edit') || 'Edit' }}</li>
                </ul>
              </div>
            </div>

            <!-- Available namespaces -->
            <div class="pt-4 border-t border-gray-100">
              <div class="font-medium text-gray-700 mb-2">Available Namespaces:</div>
              <div class="flex flex-wrap gap-2">
                <span v-for="namespace in getNamespaces()" 
                      :key="namespace"
                      class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                  {{ namespace }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Construction Terms -->
        <div class="bg-white rounded-lg shadow">
          <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Construction Terms</h2>
            <p class="mt-1 text-sm text-gray-500">
              Industry-specific terminology with pronunciations
            </p>
          </div>
          
          <div class="p-6">
            <!-- Category Selector -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Category:</label>
              <select 
                v-model="selectedCategory"
                @change="loadCategoryTerms"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Select a category</option>
                <option v-for="category in constructionCategories" 
                        :key="category" 
                        :value="category">
                  {{ category.charAt(0).toUpperCase() + category.slice(1) }}
                </option>
              </select>
            </div>

            <!-- Terms Display -->
            <div v-if="selectedCategory && categoryTerms[selectedCategory]" class="space-y-3">
              <div v-for="(term, key) in Object.entries(categoryTerms[selectedCategory]).slice(0, 6)" 
                   :key="key"
                   class="p-3 bg-gray-50 rounded-lg">
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <div class="font-medium text-gray-900">{{ term[1].value }}</div>
                    <div class="text-xs text-gray-500 mt-1">Key: {{ term[0] }}</div>
                    <div v-if="term[1].pronunciation" class="text-xs text-blue-600 mt-1">
                      🔊 {{ term[1].pronunciation }}
                    </div>
                  </div>
                  <ConstructionTermTooltip 
                    :term="term[1].value"
                    :pronunciation="term[1].pronunciation"
                    :metadata="term[1].metadata"
                  />
                </div>
              </div>
              
              <div v-if="Object.keys(categoryTerms[selectedCategory]).length > 6" 
                   class="text-center text-sm text-gray-500 pt-2">
                ... and {{ Object.keys(categoryTerms[selectedCategory]).length - 6 }} more terms
              </div>
            </div>
            
            <div v-else-if="selectedCategory" class="text-center text-gray-500 py-8">
              <div class="animate-pulse">Loading {{ selectedCategory }} terms...</div>
            </div>
            
            <div v-else class="text-center text-gray-400 py-8">
              Select a category to view construction terms
            </div>
          </div>
        </div>
      </div>

      <!-- Search Demo -->
      <div class="mt-8 bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-xl font-semibold text-gray-900">Translation Search</h2>
          <p class="mt-1 text-sm text-gray-500">
            Search across all translations in real-time
          </p>
        </div>
        
        <div class="p-6">
          <div class="flex gap-4 mb-4">
            <div class="flex-1">
              <input
                v-model="searchQuery"
                @input="performSearch"
                type="text"
                placeholder="Search translations..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            <button
              @click="clearSearch"
              class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Clear
            </button>
          </div>

          <!-- Search Results -->
          <div v-if="searchResults.length > 0" class="space-y-2">
            <div class="text-sm text-gray-600 mb-3">
              Found {{ searchResults.length }} results for "{{ searchQuery }}"
            </div>
            <div v-for="result in searchResults.slice(0, 10)" 
                 :key="result.key"
                 class="p-3 bg-gray-50 rounded border-l-4"
                 :class="{
                   'border-green-500': result.is_construction_term,
                   'border-blue-500': !result.is_construction_term
                 }">
              <div class="flex justify-between items-start">
                <div>
                  <div class="font-medium">{{ result.value }}</div>
                  <div class="text-xs text-gray-500">
                    {{ result.key }} 
                    <span v-if="result.is_construction_term" class="text-green-600">(Construction Term)</span>
                  </div>
                </div>
                <div class="text-xs text-gray-400">{{ result.namespace }}</div>
              </div>
            </div>
            
            <div v-if="searchResults.length > 10" class="text-center text-sm text-gray-500 pt-2">
              ... and {{ searchResults.length - 10 }} more results
            </div>
          </div>
          
          <div v-else-if="searchQuery && !isSearching" class="text-center text-gray-500 py-8">
            No results found for "{{ searchQuery }}"
          </div>
          
          <div v-else-if="isSearching" class="text-center text-gray-500 py-8">
            <div class="animate-pulse">Searching...</div>
          </div>
        </div>
      </div>

      <!-- API Information -->
      <div class="mt-8 bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-xl font-semibold text-gray-900">API Status</h2>
          <p class="mt-1 text-sm text-gray-500">
            Server-side localization system information
          </p>
        </div>
        
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
              <div class="text-2xl font-bold text-blue-600">{{ availableLanguages.length }}</div>
              <div class="text-sm text-blue-700">Languages</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
              <div class="text-2xl font-bold text-green-600">{{ getNamespaces().length }}</div>
              <div class="text-sm text-green-700">Namespaces</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
              <div class="text-2xl font-bold text-purple-600">{{ constructionCategories.length }}</div>
              <div class="text-sm text-purple-700">Construction Categories</div>
            </div>
            <div class="text-center p-4 bg-yellow-50 rounded-lg">
              <div class="text-2xl font-bold text-yellow-600">✓</div>
              <div class="text-sm text-yellow-700">API Connected</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import ServerLanguageSwitcher from '@/components/ui/ServerLanguageSwitcher.vue'
import ConstructionTermTooltip from '@/components/ui/ConstructionTermTooltip.vue'
import { useServerI18n } from '@/composables/useServerI18n'
import { useServerConstructionTerms } from '@/composables/useServerConstructionTerms'

// Composables
const {
  currentLanguage,
  availableLanguages,
  isLoading,
  error,
  t,
  getNamespaces,
  searchTranslations
} = useServerI18n()

const {
  currentCategories: constructionCategories,
  loadConstructionTerms,
  getCategoryTerms
} = useServerConstructionTerms()

// Local state
const selectedCategory = ref('')
const categoryTerms = reactive<Record<string, any>>({})
const searchQuery = ref('')
const searchResults = ref<any[]>([])
const isSearching = ref(false)

// Methods
const loadCategoryTerms = async () => {
  if (!selectedCategory.value) return
  
  try {
    const terms = await loadConstructionTerms(currentLanguage.value?.code, selectedCategory.value)
    categoryTerms[selectedCategory.value] = terms[selectedCategory.value] || {}
  } catch (error) {
    console.error('Failed to load category terms:', error)
  }
}

const performSearch = async () => {
  if (!searchQuery.value.trim()) {
    searchResults.value = []
    return
  }
  
  isSearching.value = true
  
  try {
    const results = await searchTranslations(searchQuery.value.trim())
    searchResults.value = results
  } catch (error) {
    console.error('Search failed:', error)
    searchResults.value = []
  } finally {
    isSearching.value = false
  }
}

const clearSearch = () => {
  searchQuery.value = ''
  searchResults.value = []
}

// Initialize
onMounted(() => {
  // Component will initialize through composables
})
</script>