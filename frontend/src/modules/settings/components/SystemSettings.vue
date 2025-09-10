<template>
  <VCard>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold flex items-center gap-2">
            <Settings class="w-5 h-5" />
            System Settings
          </h3>
          <p class="text-sm text-muted-foreground">
            Configure system-wide preferences and operational settings
          </p>
        </div>
        <VBadge v-if="hasUnsavedChanges" variant="secondary" class="text-xs">
          Unsaved Changes
        </VBadge>
      </div>
    </template>

    <div class="space-y-6">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Application Settings -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Application Settings</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <VLabel for="app_name">Application Name</VLabel>
              <VInput
                id="app_name"
                v-model="formData.app_name"
                placeholder="Construction Management Platform"
                :disabled="isSaving"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="app_version">Application Version</VLabel>
              <VInput
                id="app_version"
                v-model="formData.app_version"
                placeholder="1.0.0"
                :disabled="isSaving"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="maintenance_mode">Maintenance Mode</VLabel>
              <VSelect 
                id="maintenance_mode"
                v-model="formData.maintenance_mode" 
                :disabled="isSaving"
                placeholder="Select maintenance mode"
              >
                <option value="disabled">Disabled</option>
                <option value="enabled">Enabled</option>
                <option value="scheduled">Scheduled</option>
              </VSelect>
            </div>

            <div class="space-y-2">
              <VLabel for="debug_mode">Debug Mode</VLabel>
              <VSelect 
                id="debug_mode"
                v-model="formData.debug_mode" 
                :disabled="isSaving"
                placeholder="Select debug mode"
              >
                <option value="false">Disabled</option>
                <option value="true">Enabled</option>
              </VSelect>
            </div>
          </div>
        </div>

        <!-- Performance Settings -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Performance Settings</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <VLabel for="cache_driver">Cache Driver</VLabel>
              <VSelect 
                id="cache_driver"
                v-model="formData.cache_driver" 
                :disabled="isSaving"
                placeholder="Select cache driver"
              >
                <option value="file">File</option>
                <option value="redis">Redis</option>
                <option value="memcached">Memcached</option>
              </VSelect>
            </div>

            <div class="space-y-2">
              <VLabel for="session_lifetime">Session Lifetime (minutes)</VLabel>
              <VInput
                id="session_lifetime"
                v-model.number="formData.session_lifetime"
                type="number"
                min="1"
                max="43200"
                :disabled="isSaving"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="queue_driver">Queue Driver</VLabel>
              <VSelect 
                id="queue_driver"
                v-model="formData.queue_driver" 
                :disabled="isSaving"
                placeholder="Select queue driver"
              >
                <option value="sync">Sync</option>
                <option value="redis">Redis</option>
                <option value="database">Database</option>
              </VSelect>
            </div>

            <div class="space-y-2">
              <VLabel for="max_upload_size">Max Upload Size (MB)</VLabel>
              <VInput
                id="max_upload_size"
                v-model.number="formData.max_upload_size"
                type="number"
                min="1"
                max="1024"
                :disabled="isSaving"
              />
            </div>
          </div>
        </div>

        <!-- Logging Settings -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Logging Settings</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <VLabel for="log_channel">Log Channel</VLabel>
              <VSelect 
                id="log_channel"
                v-model="formData.log_channel" 
                :disabled="isSaving"
                placeholder="Select log channel"
              >
                <option value="single">Single File</option>
                <option value="daily">Daily Files</option>
                <option value="stack">Stack</option>
              </VSelect>
            </div>

            <div class="space-y-2">
              <VLabel for="log_level">Log Level</VLabel>
              <VSelect 
                id="log_level"
                v-model="formData.log_level" 
                :disabled="isSaving"
                placeholder="Select log level"
              >
                <option value="emergency">Emergency</option>
                <option value="alert">Alert</option>
                <option value="critical">Critical</option>
                <option value="error">Error</option>
                <option value="warning">Warning</option>
                <option value="notice">Notice</option>
                <option value="info">Info</option>
                <option value="debug">Debug</option>
              </VSelect>
            </div>

            <div class="space-y-2">
              <VLabel for="log_max_files">Max Log Files</VLabel>
              <VInput
                id="log_max_files"
                v-model.number="formData.log_max_files"
                type="number"
                min="1"
                max="365"
                :disabled="isSaving"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="audit_retention_days">Audit Retention (days)</VLabel>
              <VInput
                id="audit_retention_days"
                v-model.number="formData.audit_retention_days"
                type="number"
                min="30"
                max="2555"
                :disabled="isSaving"
              />
            </div>
          </div>
        </div>

        <VAlert v-if="error" variant="error" class="mt-4" show-icon>
          <div>
            <h4 class="font-medium">Error</h4>
            <p>{{ error }}</p>
          </div>
        </VAlert>

        <div class="flex justify-end pt-4">
          <VButton
            type="submit"
            :loading="isSaving"
            :disabled="!hasUnsavedChanges"
            class="min-w-[100px]"
          >
            <Save class="w-4 h-4 mr-2" />
            {{ isSaving ? 'Saving...' : 'Save Changes' }}
          </VButton>
        </div>
      </form>
    </div>
  </VCard>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue'
import { useSettingsStore } from '../stores/settings.store'
import { useSettings } from '../composables/useSettings'
import { 
  VCard, 
  VButton,
  VInput,
  VLabel,
  VSelect,
  VAlert,
  VBadge
} from '@/components/ui'
import { Settings, Save, AlertTriangle } from 'lucide-vue-next'
import type { SystemSettingsData } from '../types/settings.types'

defineOptions({ name: 'SystemSettings' })

const settingsStore = useSettingsStore()
const { validateField, getFieldError } = useSettings()

const isSaving = computed(() => settingsStore.isSaving)
const error = computed(() => settingsStore.error)
const hasUnsavedChanges = computed(() => settingsStore.hasUnsavedChanges)

const formData = reactive<SystemSettingsData>({
  app_name: 'Construction Management Platform',
  app_version: '1.0.0',
  maintenance_mode: 'disabled',
  debug_mode: 'false',
  cache_driver: 'redis',
  session_lifetime: 120,
  queue_driver: 'redis',
  max_upload_size: 10,
  log_channel: 'daily',
  log_level: 'info',
  log_max_files: 30,
  audit_retention_days: 365
})

const autoSaveTimeout = ref<NodeJS.Timeout | null>(null)

const handleSubmit = async () => {
  await settingsStore.updateSettings('system', formData)
}

const debouncedSave = () => {
  if (autoSaveTimeout.value) {
    clearTimeout(autoSaveTimeout.value)
  }
  
  // Track individual field changes in the store
  const originalSettings = settingsStore.getSettingsByCategory('system')
  if (originalSettings) {
    Object.keys(formData).forEach(key => {
      const currentValue = formData[key as keyof SystemSettingsData]
      const originalValue = originalSettings[key as keyof typeof originalSettings]
      if (currentValue !== originalValue) {
        settingsStore.updateLocalSetting('system', key, currentValue)
      }
    })
  }
  
  autoSaveTimeout.value = setTimeout(async () => {
    if (hasUnsavedChanges.value) {
      await settingsStore.updateSettings('system', formData)
    }
  }, 2000)
}

// Watch for changes and trigger auto-save
const watchFields = () => {
  const originalValues = { ...formData }
  
  const checkForChanges = () => {
    const hasChanges = Object.keys(formData).some(key => {
      return formData[key as keyof SystemSettingsData] !== originalValues[key as keyof SystemSettingsData]
    })
    
    if (hasChanges) {
      debouncedSave()
    }
  }
  
  // Simple change detection
  setInterval(checkForChanges, 500)
}

// Define loadFormData before using it in the watcher
const loadFormData = () => {
  const systemSettings = settingsStore.getSettingsByCategory('system')
  if (systemSettings) {
    // Clear existing form data and populate with fresh settings
    Object.keys(formData).forEach(key => delete (formData as any)[key])
    Object.assign(formData, systemSettings)
    console.log('🔧 [SystemSettings] Form data updated:', formData)
  }
}

// Watch for settings changes and update form data
watch(
  () => settingsStore.systemSettings,
  (newSystemSettings) => {
    if (newSystemSettings) {
      loadFormData()
    }
  },
  { immediate: true }
)

onMounted(async () => {
  await settingsStore.loadSettings()
  loadFormData()
  watchFields()
})

onUnmounted(() => {
  if (autoSaveTimeout.value) {
    clearTimeout(autoSaveTimeout.value)
  }
})
</script>