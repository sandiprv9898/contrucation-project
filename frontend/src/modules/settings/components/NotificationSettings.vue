<template>
  <VCard>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold flex items-center gap-2">
            <Bell class="w-5 h-5" />
            Notification Settings
          </h3>
          <p class="text-sm text-muted-foreground">
            Configure email notifications and communication preferences
          </p>
        </div>
        <VBadge v-if="hasUnsavedChanges" variant="secondary" class="text-xs">
          Unsaved Changes
        </VBadge>
      </div>
    </template>

    <div class="space-y-6">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Email Settings -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Email Configuration</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <VLabel for="email_driver">Email Driver</VLabel>
              <VSelect 
                id="email_driver"
                v-model="formData.email_driver" 
                :disabled="isSaving"
                placeholder="Select email driver"
              >
                <option value="smtp">SMTP</option>
                <option value="mailgun">Mailgun</option>
                <option value="ses">Amazon SES</option>
                <option value="sendmail">Sendmail</option>
              </VSelect>
            </div>

            <div class="space-y-2">
              <VLabel for="smtp_host">SMTP Host</VLabel>
              <VInput
                id="smtp_host"
                v-model="formData.smtp_host"
                placeholder="mail.example.com"
                :disabled="isSaving"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="smtp_port">SMTP Port</VLabel>
              <VInput
                id="smtp_port"
                v-model.number="formData.smtp_port"
                type="number"
                placeholder="587"
                :disabled="isSaving"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="smtp_username">SMTP Username</VLabel>
              <VInput
                id="smtp_username"
                v-model="formData.smtp_username"
                placeholder="notifications@example.com"
                :disabled="isSaving"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="smtp_encryption">SMTP Encryption</VLabel>
              <VSelect 
                id="smtp_encryption"
                v-model="formData.smtp_encryption" 
                :disabled="isSaving"
                placeholder="Select encryption"
              >
                <option value="tls">TLS</option>
                <option value="ssl">SSL</option>
                <option value="none">None</option>
              </VSelect>
            </div>

            <div class="space-y-2">
              <VLabel for="from_email">From Email Address</VLabel>
              <VInput
                id="from_email"
                v-model="formData.from_email"
                type="email"
                placeholder="noreply@example.com"
                :disabled="isSaving"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="from_name">From Name</VLabel>
              <VInput
                id="from_name"
                v-model="formData.from_name"
                placeholder="Construction Platform"
                :disabled="isSaving"
              />
            </div>
          </div>
        </div>

        <!-- Notification Types -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Notification Types</h3>
          
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">Task Assignments</h4>
                <p class="text-sm text-gray-500">Notify users when tasks are assigned to them</p>
              </div>
              <VCheckbox
                v-model="formData.notify_task_assignment"
                :disabled="isSaving"
              />
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">Project Updates</h4>
                <p class="text-sm text-gray-500">Notify team members of project status changes</p>
              </div>
              <VCheckbox
                v-model="formData.notify_project_updates"
                :disabled="isSaving"
              />
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">Deadline Reminders</h4>
                <p class="text-sm text-gray-500">Send reminders before task and project deadlines</p>
              </div>
              <VCheckbox
                v-model="formData.notify_deadline_reminders"
                :disabled="isSaving"
              />
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">System Maintenance</h4>
                <p class="text-sm text-gray-500">Notify all users of scheduled maintenance</p>
              </div>
              <VCheckbox
                v-model="formData.notify_system_maintenance"
                :disabled="isSaving"
              />
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">Security Alerts</h4>
                <p class="text-sm text-gray-500">Notify administrators of security events</p>
              </div>
              <VCheckbox
                v-model="formData.notify_security_alerts"
                :disabled="isSaving"
              />
            </div>
          </div>
        </div>

        <!-- Reminder Settings -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Reminder Settings</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <VLabel for="deadline_reminder_days">Deadline Reminder (days before)</VLabel>
              <VInput
                id="deadline_reminder_days"
                v-model.number="formData.deadline_reminder_days"
                type="number"
                min="1"
                max="30"
                :disabled="isSaving"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="overdue_reminder_frequency">Overdue Reminder Frequency</VLabel>
              <VSelect 
                id="overdue_reminder_frequency"
                v-model="formData.overdue_reminder_frequency" 
                :disabled="isSaving"
                placeholder="Select frequency"
              >
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="disabled">Disabled</option>
              </VSelect>
            </div>

            <div class="space-y-2">
              <VLabel for="digest_frequency">Email Digest Frequency</VLabel>
              <VSelect 
                id="digest_frequency"
                v-model="formData.digest_frequency" 
                :disabled="isSaving"
                placeholder="Select frequency"
              >
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="disabled">Disabled</option>
              </VSelect>
            </div>

            <div class="space-y-2">
              <VLabel for="digest_time">Digest Send Time</VLabel>
              <VInput
                id="digest_time"
                v-model="formData.digest_time"
                type="time"
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

        <div class="flex justify-between items-center pt-4">
          <VButton
            type="button"
            variant="outline"
            @click="testEmailConfiguration"
            :disabled="isSaving"
          >
            <Mail class="w-4 h-4 mr-2" />
            Test Email Configuration
          </VButton>
          
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
import { Bell, Save, AlertTriangle, Mail } from 'lucide-vue-next'
import type { NotificationSettingsData } from '../types/settings.types'

defineOptions({ name: 'NotificationSettings' })

const settingsStore = useSettingsStore()
const { validateField, getFieldError } = useSettings()

const isSaving = computed(() => settingsStore.isSaving)
const error = computed(() => settingsStore.error)
const hasUnsavedChanges = computed(() => settingsStore.hasUnsavedChanges)

const formData = reactive<NotificationSettingsData>({
  email_driver: 'smtp',
  smtp_host: '',
  smtp_port: 587,
  smtp_username: '',
  smtp_encryption: 'tls',
  from_email: '',
  from_name: 'Construction Platform',
  notify_task_assignment: true,
  notify_project_updates: true,
  notify_deadline_reminders: true,
  notify_system_maintenance: true,
  notify_security_alerts: true,
  deadline_reminder_days: 3,
  overdue_reminder_frequency: 'daily',
  digest_frequency: 'weekly',
  digest_time: '09:00'
})

const autoSaveTimeout = ref<NodeJS.Timeout | null>(null)

const handleSubmit = async () => {
  await settingsStore.updateSettings('notifications', formData)
}

const testEmailConfiguration = async () => {
  try {
    // This would send a test email through the API
    await settingsStore.testEmailConfiguration(formData)
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
      await settingsStore.updateSettings('notifications', formData)
    }
  }, 2000)
  
  settingsStore.markHasUnsavedChanges()
}

// Watch for changes and trigger auto-save
const watchFields = () => {
  const originalValues = { ...formData }
  
  const checkForChanges = () => {
    const hasChanges = Object.keys(formData).some(key => {
      return formData[key as keyof NotificationSettingsData] !== originalValues[key as keyof NotificationSettingsData]
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
  
  // Load existing notification settings
  const notificationSettings = settingsStore.getSettingsByCategory('notifications')
  if (notificationSettings) {
    Object.assign(formData, notificationSettings)
  }
  
  watchFields()
})

onUnmounted(() => {
  if (autoSaveTimeout.value) {
    clearTimeout(autoSaveTimeout.value)
  }
})
</script>