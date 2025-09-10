<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-8">
        <h1 class="text-2xl font-bold text-gray-900">Company Settings</h1>
        <!-- Inline stats -->
        <div class="flex items-center gap-6 text-sm text-gray-600">
          <span>Profile: <strong :class="profileStatus.color">{{ profileStatus.text }}</strong></span>
          <span>Branding: <strong :class="brandingStatus.color">{{ brandingStatus.text }}</strong></span>
          <span>Portfolio: <strong class="text-gray-900">{{ portfolioCount }} items</strong></span>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          v-if="hasUnsavedChanges"
          @click="saveAllSettings"
          :disabled="isSaving"
          class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
        >
          <svg v-if="isSaving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isSaving ? 'Saving...' : 'Save Changes' }}
        </button>
        <button
          @click="refreshData"
          :disabled="isLoading"
          class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
        >
          <svg :class="{ 'animate-spin': isLoading }" class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="space-y-4">
      <div class="bg-white border border-gray-200 rounded-lg shadow p-6">
        <div class="animate-pulse">
          <div class="h-6 bg-gray-200 rounded w-1/4 mb-4"></div>
          <div class="space-y-3">
            <div class="h-4 bg-gray-200 rounded"></div>
            <div class="h-4 bg-gray-200 rounded w-5/6"></div>
            <div class="h-4 bg-gray-200 rounded w-4/6"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Company Settings Interface -->
    <div v-else class="space-y-6">      
      <!-- Company Settings Component -->
      <CompanySettings
        :settings="companySettings"
        :can-write="canManageSettings"
      />
    </div>

    <!-- Success/Error Messages -->
    <div
      v-if="saveStatus.show"
      :class="[
        'rounded-md p-4 mb-4',
        saveStatus.variant === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'
      ]"
    >
      <div class="flex items-center">
        <svg
          v-if="saveStatus.variant === 'success'"
          class="h-5 w-5 text-green-400 mr-2"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <svg
          v-else
          class="h-5 w-5 text-red-400 mr-2"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span :class="saveStatus.variant === 'success' ? 'text-green-800' : 'text-red-800'">
          {{ saveStatus.message }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useSettingsStore } from '@/modules/settings/stores/settings.store'
import { useAuthStore } from '@/modules/auth/stores/auth.store'
import CompanySettings from '@/modules/settings/components/CompanySettings.vue'

// Define component name
defineOptions({ name: 'CompanySettingsPage' })

// Stores
const settingsStore = useSettingsStore()
const authStore = useAuthStore()

// State
const isLoading = ref(true)
const isSaving = ref(false)
const saveStatus = ref({
  show: false,
  variant: 'success' as 'success' | 'error',
  message: ''
})

// Computed
const companySettings = computed(() => settingsStore.state?.settings?.company || null)
const canManageSettings = computed(() => authStore.user?.role === 'admin' || authStore.user?.role === 'project_manager')

const hasUnsavedChanges = computed(() => {
  const changes = settingsStore.unsavedChanges?.company || {}
  const hasChanges = Object.keys(changes).length > 0
  console.log('🔍 Checking unsaved changes:', { changes, hasChanges })
  return hasChanges
})

// Status indicators
const profileStatus = computed(() => {
  const profile = companySettings.value
  if (!profile?.name || !profile?.email) {
    return { text: 'Incomplete', color: 'text-amber-600' }
  }
  return { text: 'Complete', color: 'text-green-600' }
})

const brandingStatus = computed(() => {
  const settings = companySettings.value
  if (!settings?.primary_color || !settings?.logo_url) {
    return { text: 'Basic', color: 'text-amber-600' }
  }
  return { text: 'Configured', color: 'text-green-600' }
})

const portfolioCount = computed(() => {
  // This would come from portfolio data - placeholder for now
  return 0
})

// Methods
async function loadSettings() {
  try {
    isLoading.value = true
    await settingsStore.loadSettings()
  } catch (error) {
    console.error('Failed to load settings:', error)
    showSaveStatus('error', 'Failed to load settings. Please refresh the page.')
  } finally {
    isLoading.value = false
  }
}

async function saveAllSettings() {
  if (!hasUnsavedChanges.value) return
  
  try {
    isSaving.value = true
    await settingsStore.saveAllUnsavedChanges()
    showSaveStatus('success', 'Company settings saved successfully!')
  } catch (error) {
    console.error('Failed to save settings:', error)
    showSaveStatus('error', 'Failed to save settings. Please try again.')
  } finally {
    isSaving.value = false
  }
}

async function refreshData() {
  await settingsStore.forceReloadSettings()
}

function showSaveStatus(variant: 'success' | 'error', message: string) {
  saveStatus.value = { show: true, variant, message }
  setTimeout(() => {
    saveStatus.value.show = false
  }, 5000)
}

// Auto-save functionality
let saveTimeout: ReturnType<typeof setTimeout> | null = null
const autoSave = () => {
  if (saveTimeout) clearTimeout(saveTimeout)
  saveTimeout = setTimeout(() => {
    if (hasUnsavedChanges.value) {
      saveAllSettings()
    }
  }, 2000) // Auto-save after 2 seconds of inactivity
}

// Watch for changes to trigger auto-save
settingsStore.$subscribe(() => {
  if (hasUnsavedChanges.value) {
    autoSave()
  }
})

// Lifecycle
onMounted(() => {
  loadSettings()
})
</script>