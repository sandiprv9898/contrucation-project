<template>
  <div class="space-y-6">
    <!-- Enhanced Company Settings with Dynamic Configuration -->
    <div class="bg-white border border-gray-200 rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">{{ config.subtitle }}</p>
          </div>
          <div 
            v-if="config.features.showUnsavedIndicator && hasUnsavedChanges" 
            class="bg-amber-100 text-amber-800 text-xs px-3 py-1 rounded-full"
          >
            Unsaved Changes
          </div>
        </div>
      </div>
      
      <div class="p-0">
        <!-- Dynamic Tab Navigation -->
        <div class="border-b border-gray-200 px-6">
          <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button
              v-for="tab in enabledTabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center',
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              <component :is="getIconComponent(tab.icon)" class="mr-2 h-4 w-4" />
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <!-- Dynamic Tab Content -->
        <div class="p-6">
          <div 
            v-for="tab in enabledTabs" 
            :key="tab.id"
            v-show="activeTab === tab.id"
            class="space-y-8"
          >
            <!-- Custom Component for Tab (if specified) -->
            <component
              v-if="tab.customComponent"
              :is="tab.customComponent"
              :settings="settings"
              :can-write="canWrite"
              @update="handleSettingUpdate"
            />
            
            <!-- Dynamic Fields (if no custom component) -->
            <div v-else>
              <!-- Group fields by sections if needed -->
              <div v-for="section in getFieldSections(tab)" :key="section.title" class="space-y-6">
                <div v-if="section.title">
                  <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ section.title }}</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <DynamicField
                    v-for="field in section.fields"
                    :key="field.key"
                    :config="field"
                    :model-value="formData[field.key]"
                    :can-write="canWrite"
                    @update:model-value="updateField(field.key, $event)"
                    @file-upload="handleFileUpload"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Dynamic Actions (if enabled) -->
    <div v-if="config.features.showStatusIndicators" class="flex items-center justify-between text-sm text-gray-600">
      <div class="flex items-center gap-6">
        <span>Total Fields: <strong class="text-gray-900">{{ totalFields }}</strong></span>
        <span>Completed: <strong class="text-green-600">{{ completedFields }}/{{ totalFields }}</strong></span>
        <span>Progress: <strong class="text-blue-600">{{ completionPercentage }}%</strong></span>
      </div>
      
      <div class="flex items-center gap-2">
        <button
          @click="resetToDefaults"
          class="text-red-600 hover:text-red-700 text-sm"
          :disabled="!canWrite"
        >
          Reset to Defaults
        </button>
        <button
          @click="exportSettings"
          class="text-blue-600 hover:text-blue-700 text-sm"
          :disabled="!config.permissions.canExport(authStore.user)"
        >
          Export Settings
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, reactive, onMounted } from 'vue'
import { User, Palette, Phone, Briefcase, Scale } from 'lucide-vue-next'
import { useSettingsStore } from '../stores/settings.store'
import { useAuthStore } from '@/modules/auth/stores/auth.store'
import DynamicField from './DynamicField.vue'
import { 
  getCompanySettingsConfig, 
  getEnabledTabs, 
  getVisibleFields,
  type CompanySettings,
  type TabConfig,
  type FieldConfig
} from '../config/company-settings.config'

// Props
interface Props {
  settings: CompanySettings | null
  canWrite: boolean
}

const props = withDefaults(defineProps<Props>(), {
  canWrite: false
})

// Composables
const settingsStore = useSettingsStore()
const authStore = useAuthStore()

// State
const config = getCompanySettingsConfig()
const activeTab = ref(config.tabs[0]?.id || 'basic')
const formData = reactive<Partial<CompanySettings>>({})

// Computed
const enabledTabs = computed(() => getEnabledTabs(config))

const hasUnsavedChanges = computed(() => {
  return Object.keys(settingsStore.unsavedChanges?.company || {}).length > 0
})

const totalFields = computed(() => {
  return enabledTabs.value.reduce((total, tab) => {
    return total + getVisibleFields(tab).length
  }, 0)
})

const completedFields = computed(() => {
  return enabledTabs.value.reduce((completed, tab) => {
    return completed + getVisibleFields(tab).filter(field => {
      const value = formData[field.key as keyof CompanySettings]
      return value && value.toString().trim() !== ''
    }).length
  }, 0)
})

const completionPercentage = computed(() => {
  if (totalFields.value === 0) return 0
  return Math.round((completedFields.value / totalFields.value) * 100)
})

// Methods
function getIconComponent(iconName: string) {
  const icons: Record<string, any> = {
    User, Palette, Phone, Briefcase, Scale
  }
  return icons[iconName] || User
}

function getFieldSections(tab: TabConfig) {
  const visibleFields = getVisibleFields(tab)
  
  // For now, return all fields in a single section
  // This can be enhanced to group fields by semantic sections
  return [{
    title: '', // No section title for single section
    fields: visibleFields
  }]
}

function updateField(key: string, value: any) {
  formData[key as keyof CompanySettings] = value
  handleSettingUpdate(key, value)
}

function handleSettingUpdate(key: string, value: any) {
  console.log(`🔄 Dynamic field update: ${key} = ${value}`)
  settingsStore.updateLocalSetting('company', key, value)
}

function handleFileUpload(file: File, fieldKey: string) {
  console.log(`📎 File upload for ${fieldKey}:`, file.name)
  // Handle file upload logic here
  // For now, create a preview URL
  const reader = new FileReader()
  reader.onload = (e) => {
    updateField(fieldKey, e.target?.result as string)
  }
  reader.readAsDataURL(file)
}

function resetToDefaults() {
  if (confirm('Are you sure you want to reset all settings to defaults?')) {
    settingsStore.resetCategoryToDefaults('company')
  }
}

function exportSettings() {
  settingsStore.exportSettings(['company'])
}

// Initialize form data when props change
watch(
  () => props.settings,
  (newSettings) => {
    if (newSettings) {
      // Initialize all configured fields with current values
      config.tabs.forEach(tab => {
        tab.fields.forEach(field => {
          const key = field.key as keyof CompanySettings
          formData[key] = newSettings[key] || ''
        })
      })
    }
  },
  { immediate: true, deep: true }
)

// Auto-save functionality
let saveTimeout: ReturnType<typeof setTimeout> | null = null
const autoSave = () => {
  if (!config.features.autoSave) return
  
  if (saveTimeout) clearTimeout(saveTimeout)
  saveTimeout = setTimeout(() => {
    if (hasUnsavedChanges.value) {
      settingsStore.saveAllUnsavedChanges().catch(console.error)
    }
  }, config.features.autoSaveDelay)
}

// Watch for changes to trigger auto-save
watch(
  () => settingsStore.unsavedChanges,
  () => {
    if (hasUnsavedChanges.value) {
      autoSave()
    }
  },
  { deep: true }
)

// Lifecycle
onMounted(() => {
  console.log('🎛️ Dynamic Company Settings loaded with config:', config)
})
</script>