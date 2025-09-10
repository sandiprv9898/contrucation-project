<template>
  <!-- Smart Component Switcher -->
  <div>
    <!-- Development Controls (only shown in development) -->
    <div 
      v-if="showDevControls" 
      class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6"
    >
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-sm font-medium text-yellow-800">Development Controls</h3>
          <p class="text-xs text-yellow-600">Switch between static and dynamic components</p>
        </div>
        <div class="flex items-center gap-3">
          <label class="text-sm text-yellow-700">
            <input 
              v-model="useDynamicComponent" 
              type="checkbox" 
              class="mr-2"
            >
            Use Dynamic Component
          </label>
          <button 
            @click="resetConfiguration"
            class="text-xs px-2 py-1 bg-yellow-200 text-yellow-800 rounded hover:bg-yellow-300"
          >
            Reset Config
          </button>
        </div>
      </div>
    </div>

    <!-- Dynamic Component (New System) -->
    <Suspense v-if="useDynamicComponent">
      <template #default>
        <DynamicCompanySettings
          :settings="settings"
          :can-write="canWrite"
        />
      </template>
      <template #fallback>
        <div class="animate-pulse bg-gray-200 h-96 rounded-lg flex items-center justify-center">
          <span class="text-gray-500">Loading dynamic configuration...</span>
        </div>
      </template>
    </Suspense>

    <!-- Static Component (Legacy System) -->
    <CompanySettings
      v-else
      :settings="settings"
      :can-write="canWrite"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { clearCustomConfig } from '../config/company-settings.config'
import CompanySettings from './CompanySettings.vue'
import DynamicCompanySettings from './DynamicCompanySettings.vue'
import type { CompanySettings as CompanySettingsType } from '../types/settings.types'

interface Props {
  settings: CompanySettingsType | null
  canWrite: boolean
  forceMode?: 'static' | 'dynamic' | 'auto'
}

const props = withDefaults(defineProps<Props>(), {
  forceMode: 'auto'
})

// State
const useDynamicComponent = ref(false)

// Computed
const showDevControls = computed(() => {
  return import.meta.env.NODE_ENV === 'development' && props.forceMode === 'auto'
})

// Methods
function resetConfiguration() {
  if (confirm('Reset all dynamic configuration to defaults?')) {
    clearCustomConfig()
    location.reload()
  }
}

// Initialize
onMounted(() => {
  // Determine which component to use
  switch (props.forceMode) {
    case 'static':
      useDynamicComponent.value = false
      break
    case 'dynamic':
      useDynamicComponent.value = true
      break
    case 'auto':
    default:
      // Use dynamic by default in development, static in production
      useDynamicComponent.value = import.meta.env.NODE_ENV === 'development'
      break
  }

  // Allow override via URL parameter
  const urlParams = new URLSearchParams(window.location.search)
  const dynamicParam = urlParams.get('dynamic')
  if (dynamicParam !== null) {
    useDynamicComponent.value = dynamicParam === 'true'
  }

  console.log(`🎛️ Company Settings: Using ${useDynamicComponent.value ? 'Dynamic' : 'Static'} component`)
})
</script>