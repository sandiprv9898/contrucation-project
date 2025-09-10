<template>
  <VCard>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold flex items-center gap-2">
            <HardDrive class="w-5 h-5" />
            Backup & Recovery Settings
          </h3>
          <p class="text-sm text-muted-foreground">
            Configure automated backups, data retention, and recovery options
          </p>
        </div>
        <VBadge v-if="hasUnsavedChanges" variant="secondary" class="text-xs">
          Unsaved Changes
        </VBadge>
      </div>
    </template>

    <div class="space-y-6">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Backup Configuration -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Backup Configuration</h3>
          
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">Enable Automated Backups</h4>
                <p class="text-sm text-gray-500">Automatically create backups on a schedule</p>
              </div>
              <VCheckbox
                v-model="formData.enable_backups"
                :disabled="isSaving"
              />
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">Include File Attachments</h4>
                <p class="text-sm text-gray-500">Include uploaded files and documents in backups</p>
              </div>
              <VCheckbox
                v-model="formData.include_files"
                :disabled="isSaving || !formData.enable_backups"
              />
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">Compress Backups</h4>
                <p class="text-sm text-gray-500">Use compression to reduce backup file sizes</p>
              </div>
              <VCheckbox
                v-model="formData.compress_backups"
                :disabled="isSaving || !formData.enable_backups"
              />
            </div>
          </div>

          <div v-if="formData.enable_backups" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <VLabel for="backup_frequency">Backup Frequency</VLabel>
              <VSelect 
                id="backup_frequency"
                v-model="formData.backup_frequency" 
                :disabled="isSaving"
                placeholder="Select frequency"
              >
                <option value="hourly">Hourly</option>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
              </VSelect>
            </div>

            <div class="space-y-2">
              <VLabel for="backup_time">Backup Time</VLabel>
              <VInput
                id="backup_time"
                v-model="formData.backup_time"
                type="time"
                :disabled="isSaving"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="backup_retention_days">Retention Period (days)</VLabel>
              <VInput
                id="backup_retention_days"
                v-model.number="formData.backup_retention_days"
                type="number"
                min="7"
                max="365"
                :disabled="isSaving"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="max_backup_size">Max Backup Size (GB)</VLabel>
              <VInput
                id="max_backup_size"
                v-model.number="formData.max_backup_size"
                type="number"
                min="1"
                max="1000"
                :disabled="isSaving"
              />
            </div>
          </div>
        </div>

        <!-- Storage Configuration -->
        <div v-if="formData.enable_backups" class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Storage Configuration</h3>
          
          <div class="space-y-4">
            <div class="space-y-2">
              <VLabel for="storage_driver">Storage Driver</VLabel>
              <VSelect 
                id="storage_driver"
                v-model="formData.storage_driver" 
                :disabled="isSaving"
                placeholder="Select storage driver"
              >
                <option value="local">Local Storage</option>
                <option value="s3">Amazon S3</option>
                <option value="gcs">Google Cloud Storage</option>
                <option value="azure">Azure Storage</option>
                <option value="ftp">FTP Server</option>
              </VSelect>
            </div>

            <!-- S3 Configuration -->
            <div v-if="formData.storage_driver === 's3'" class="space-y-4 pl-4 border-l-2 border-blue-200">
              <h4 class="font-medium text-blue-900">Amazon S3 Configuration</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                  <VLabel for="s3_bucket">S3 Bucket Name</VLabel>
                  <VInput
                    id="s3_bucket"
                    v-model="formData.s3_bucket"
                    placeholder="my-backup-bucket"
                    :disabled="isSaving"
                  />
                </div>
                <div class="space-y-2">
                  <VLabel for="s3_region">S3 Region</VLabel>
                  <VInput
                    id="s3_region"
                    v-model="formData.s3_region"
                    placeholder="us-east-1"
                    :disabled="isSaving"
                  />
                </div>
                <div class="space-y-2">
                  <VLabel for="s3_access_key">Access Key ID</VLabel>
                  <VInput
                    id="s3_access_key"
                    v-model="formData.s3_access_key"
                    type="password"
                    :disabled="isSaving"
                  />
                </div>
                <div class="space-y-2">
                  <VLabel for="s3_secret_key">Secret Access Key</VLabel>
                  <VInput
                    id="s3_secret_key"
                    v-model="formData.s3_secret_key"
                    type="password"
                    :disabled="isSaving"
                  />
                </div>
              </div>
            </div>

            <!-- Local Storage Configuration -->
            <div v-if="formData.storage_driver === 'local'" class="space-y-4 pl-4 border-l-2 border-green-200">
              <h4 class="font-medium text-green-900">Local Storage Configuration</h4>
              <div class="space-y-2">
                <VLabel for="local_backup_path">Backup Directory Path</VLabel>
                <VInput
                  id="local_backup_path"
                  v-model="formData.local_backup_path"
                  placeholder="/var/backups/construction-platform"
                  :disabled="isSaving"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Recovery Options -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Recovery Options</h3>
          
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">Enable Point-in-Time Recovery</h4>
                <p class="text-sm text-gray-500">Allow restoration to specific timestamps</p>
              </div>
              <VCheckbox
                v-model="formData.enable_point_in_time_recovery"
                :disabled="isSaving"
              />
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">Automatic Integrity Checks</h4>
                <p class="text-sm text-gray-500">Verify backup integrity automatically</p>
              </div>
              <VCheckbox
                v-model="formData.enable_integrity_checks"
                :disabled="isSaving"
              />
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">Recovery Mode Notifications</h4>
                <p class="text-sm text-gray-500">Notify administrators when system is in recovery mode</p>
              </div>
              <VCheckbox
                v-model="formData.notify_recovery_mode"
                :disabled="isSaving"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <VLabel for="recovery_window_hours">Recovery Window (hours)</VLabel>
              <VInput
                id="recovery_window_hours"
                v-model.number="formData.recovery_window_hours"
                type="number"
                min="1"
                max="168"
                :disabled="isSaving"
              />
              <p class="text-xs text-gray-500">How far back in time recovery is possible</p>
            </div>

            <div class="space-y-2">
              <VLabel for="max_recovery_attempts">Max Recovery Attempts</VLabel>
              <VInput
                id="max_recovery_attempts"
                v-model.number="formData.max_recovery_attempts"
                type="number"
                min="1"
                max="10"
                :disabled="isSaving"
              />
            </div>
          </div>
        </div>

        <!-- Backup Status -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Backup Status</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 border rounded-lg">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-600">Last Backup</span>
                <Calendar class="w-4 h-4 text-gray-400" />
              </div>
              <p class="text-lg font-semibold text-gray-900 mt-1">{{ lastBackupDate || 'Never' }}</p>
            </div>

            <div class="p-4 border rounded-lg">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-600">Backup Size</span>
                <HardDrive class="w-4 h-4 text-gray-400" />
              </div>
              <p class="text-lg font-semibold text-gray-900 mt-1">{{ lastBackupSize || '0 MB' }}</p>
            </div>

            <div class="p-4 border rounded-lg">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-600">Next Backup</span>
                <Clock class="w-4 h-4 text-gray-400" />
              </div>
              <p class="text-lg font-semibold text-gray-900 mt-1">{{ nextBackupDate || 'Not scheduled' }}</p>
            </div>
          </div>
        </div>

        <VAlert v-if="error" variant="error" class="mt-4" show-icon>
          <div>
            <h4 class="font-medium">Error</h4>
            <p>{{ error }}</p>
          </div>
        </VAlert>

        <div class="flex justify-between items-center pt-4">
          <div class="space-x-2">
            <VButton
              type="button"
              variant="outline"
              @click="runBackupNow"
              :disabled="isSaving || !formData.enable_backups"
            >
              <Play class="w-4 h-4 mr-2" />
              Run Backup Now
            </VButton>
            
            <VButton
              type="button"
              variant="outline"
              @click="testConnection"
              :disabled="isSaving || !formData.enable_backups"
            >
              <TestTube class="w-4 h-4 mr-2" />
              Test Connection
            </VButton>
          </div>
          
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
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useSettingsStore } from '../stores/settings.store'
import { useSettings } from '../composables/useSettings'
import { 
  VCard, 
  VButton,
  VInput,
  VLabel,
  VSelect,
  VCheckbox,
  VAlert,
  VBadge
} from '@/components/ui'
import { 
  HardDrive, 
  Save, 
  AlertTriangle, 
  Calendar, 
  Clock, 
  Play,
  TestTube
} from 'lucide-vue-next'
import type { BackupSettingsData } from '../types/settings.types'

defineOptions({ name: 'BackupSettings' })

const settingsStore = useSettingsStore()
const { validateField, getFieldError } = useSettings()

const isSaving = computed(() => settingsStore.isSaving)
const error = computed(() => settingsStore.error)
const hasUnsavedChanges = computed(() => settingsStore.hasUnsavedChanges)

// Sample status data - in real app, this would come from the API
const lastBackupDate = ref('2024-09-09 02:00 AM')
const lastBackupSize = ref('2.4 GB')
const nextBackupDate = ref('2024-09-10 02:00 AM')

const formData = reactive<BackupSettingsData>({
  enable_backups: true,
  include_files: true,
  compress_backups: true,
  backup_frequency: 'daily',
  backup_time: '02:00',
  backup_retention_days: 30,
  max_backup_size: 10,
  storage_driver: 'local',
  s3_bucket: '',
  s3_region: 'us-east-1',
  s3_access_key: '',
  s3_secret_key: '',
  local_backup_path: '/var/backups/construction-platform',
  enable_point_in_time_recovery: true,
  enable_integrity_checks: true,
  notify_recovery_mode: true,
  recovery_window_hours: 72,
  max_recovery_attempts: 3
})

const autoSaveTimeout = ref<NodeJS.Timeout | null>(null)

const handleSubmit = async () => {
  await settingsStore.updateSettings('backup', formData)
}

const runBackupNow = async () => {
  try {
    // This would trigger an immediate backup through the API
    await settingsStore.runBackupNow()
    // Show success message
  } catch (error) {
    // Error handling is done in the store
  }
}

const testConnection = async () => {
  try {
    // This would test the backup storage connection
    await settingsStore.testBackupConnection(formData)
    // Show success message
  } catch (error) {
    // Error handling is done in the store
  }
}

const debouncedSave = () => {
  if (autoSaveTimeout.value) {
    clearTimeout(autoSaveTimeout.value)
  }
  
  autoSaveTimeout.value = setTimeout(async () => {
    if (hasUnsavedChanges.value) {
      await settingsStore.updateSettings('backup', formData)
    }
  }, 2000)
  
  settingsStore.markHasUnsavedChanges()
}

// Watch for changes and trigger auto-save
const watchFields = () => {
  const originalValues = { ...formData }
  
  const checkForChanges = () => {
    const hasChanges = Object.keys(formData).some(key => {
      return formData[key as keyof BackupSettingsData] !== originalValues[key as keyof BackupSettingsData]
    })
    
    if (hasChanges) {
      debouncedSave()
    }
  }
  
  // Simple change detection
  setInterval(checkForChanges, 500)
}

onMounted(async () => {
  await settingsStore.loadSettings()
  
  // Load existing backup settings
  const backupSettings = settingsStore.getSettingsByCategory('backup')
  if (backupSettings) {
    Object.assign(formData, backupSettings)
  }
  
  watchFields()
})

onUnmounted(() => {
  if (autoSaveTimeout.value) {
    clearTimeout(autoSaveTimeout.value)
  }
})
</script>