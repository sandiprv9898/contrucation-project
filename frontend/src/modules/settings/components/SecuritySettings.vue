<template>
  <VCard>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold flex items-center gap-2">
            <Shield class="w-5 h-5" />
            Security Settings
          </h3>
          <p class="text-sm text-muted-foreground">
            Configure security policies, authentication, and access controls
          </p>
        </div>
        <VBadge v-if="hasUnsavedChanges" variant="secondary" class="text-xs">
          Unsaved Changes
        </VBadge>
      </div>
    </template>

    <div class="space-y-6">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Authentication Settings -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Authentication Settings</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <VLabel for="password_min_length">Minimum Password Length</VLabel>
              <VInput
                id="password_min_length"
                v-model.number="formData.password_min_length"
                type="number"
                min="8"
                max="50"
                :disabled="isSaving"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="password_expiry_days">Password Expiry (days)</VLabel>
              <VInput
                id="password_expiry_days"
                v-model.number="formData.password_expiry_days"
                type="number"
                min="30"
                max="365"
                :disabled="isSaving"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="max_login_attempts">Max Login Attempts</VLabel>
              <VInput
                id="max_login_attempts"
                v-model.number="formData.max_login_attempts"
                type="number"
                min="3"
                max="10"
                :disabled="isSaving"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="lockout_duration">Lockout Duration (minutes)</VLabel>
              <VInput
                id="lockout_duration"
                v-model.number="formData.lockout_duration"
                type="number"
                min="5"
                max="1440"
                :disabled="isSaving"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="session_timeout">Session Timeout (minutes)</VLabel>
              <VInput
                id="session_timeout"
                v-model.number="formData.session_timeout"
                type="number"
                min="15"
                max="480"
                :disabled="isSaving"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="concurrent_sessions">Max Concurrent Sessions</VLabel>
              <VInput
                id="concurrent_sessions"
                v-model.number="formData.concurrent_sessions"
                type="number"
                min="1"
                max="10"
                :disabled="isSaving"
              />
            </div>
          </div>
        </div>

        <!-- Password Requirements -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Password Requirements</h3>
          
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">Require Uppercase Letters</h4>
                <p class="text-sm text-gray-500">Password must contain at least one uppercase letter</p>
              </div>
              <VCheckbox
                v-model="formData.require_uppercase"
                :disabled="isSaving"
              />
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">Require Numbers</h4>
                <p class="text-sm text-gray-500">Password must contain at least one number</p>
              </div>
              <VCheckbox
                v-model="formData.require_numbers"
                :disabled="isSaving"
              />
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">Require Special Characters</h4>
                <p class="text-sm text-gray-500">Password must contain special characters (!@#$%^&*)</p>
              </div>
              <VCheckbox
                v-model="formData.require_special_chars"
                :disabled="isSaving"
              />
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">Prevent Password Reuse</h4>
                <p class="text-sm text-gray-500">Users cannot reuse their last passwords</p>
              </div>
              <VCheckbox
                v-model="formData.prevent_password_reuse"
                :disabled="isSaving"
              />
            </div>
          </div>

          <div v-if="formData.prevent_password_reuse" class="ml-4">
            <div class="space-y-2">
              <VLabel for="password_history_limit">Password History Limit</VLabel>
              <VInput
                id="password_history_limit"
                v-model.number="formData.password_history_limit"
                type="number"
                min="3"
                max="24"
                :disabled="isSaving"
                class="max-w-xs"
              />
              <p class="text-sm text-gray-500">Number of previous passwords to remember</p>
            </div>
          </div>
        </div>

        <!-- Two-Factor Authentication -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Two-Factor Authentication</h3>
          
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">Enable Two-Factor Authentication</h4>
                <p class="text-sm text-gray-500">Require 2FA for all users</p>
              </div>
              <VCheckbox
                v-model="formData.enable_2fa"
                :disabled="isSaving"
              />
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">Force 2FA for Administrators</h4>
                <p class="text-sm text-gray-500">Require 2FA for users with admin privileges</p>
              </div>
              <VCheckbox
                v-model="formData.force_2fa_admin"
                :disabled="isSaving"
              />
            </div>
          </div>

          <div v-if="formData.enable_2fa || formData.force_2fa_admin" class="ml-4 space-y-4">
            <div class="space-y-2">
              <VLabel for="totp_issuer">TOTP Issuer Name</VLabel>
              <VInput
                id="totp_issuer"
                v-model="formData.totp_issuer"
                placeholder="Construction Platform"
                :disabled="isSaving"
                class="max-w-md"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="backup_codes_count">Backup Codes Count</VLabel>
              <VInput
                id="backup_codes_count"
                v-model.number="formData.backup_codes_count"
                type="number"
                min="5"
                max="20"
                :disabled="isSaving"
                class="max-w-xs"
              />
            </div>
          </div>
        </div>

        <!-- Access Control -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Access Control</h3>
          
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">IP Whitelist</h4>
                <p class="text-sm text-gray-500">Restrict access to specific IP addresses</p>
              </div>
              <VCheckbox
                v-model="formData.enable_ip_whitelist"
                :disabled="isSaving"
              />
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">API Rate Limiting</h4>
                <p class="text-sm text-gray-500">Enable rate limiting for API endpoints</p>
              </div>
              <VCheckbox
                v-model="formData.enable_rate_limiting"
                :disabled="isSaving"
              />
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg">
              <div>
                <h4 class="font-medium">Security Headers</h4>
                <p class="text-sm text-gray-500">Enable security headers (CSRF, XSS Protection, etc.)</p>
              </div>
              <VCheckbox
                v-model="formData.enable_security_headers"
                :disabled="isSaving"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-if="formData.enable_rate_limiting" class="space-y-2">
              <VLabel for="rate_limit_requests">Rate Limit (requests per minute)</VLabel>
              <VInput
                id="rate_limit_requests"
                v-model.number="formData.rate_limit_requests"
                type="number"
                min="10"
                max="1000"
                :disabled="isSaving"
              />
            </div>

            <div class="space-y-2">
              <VLabel for="audit_log_retention">Audit Log Retention (days)</VLabel>
              <VInput
                id="audit_log_retention"
                v-model.number="formData.audit_log_retention"
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
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useSettingsStore } from '../stores/settings.store'
import { useSettings } from '../composables/useSettings'
import { 
  VCard, 
  VButton,
  VInput,
  VLabel,
  VCheckbox,
  VAlert,
  VBadge
} from '@/components/ui'
import { Shield, Save, AlertTriangle } from 'lucide-vue-next'
import type { SecuritySettingsData } from '../types/settings.types'

defineOptions({ name: 'SecuritySettings' })

const settingsStore = useSettingsStore()
const { validateField, getFieldError } = useSettings()

const isSaving = computed(() => settingsStore.isSaving)
const error = computed(() => settingsStore.error)
const hasUnsavedChanges = computed(() => settingsStore.hasUnsavedChanges)

const formData = reactive<SecuritySettingsData>({
  password_min_length: 8,
  password_expiry_days: 90,
  max_login_attempts: 5,
  lockout_duration: 30,
  session_timeout: 120,
  concurrent_sessions: 3,
  require_uppercase: true,
  require_numbers: true,
  require_special_chars: true,
  prevent_password_reuse: true,
  password_history_limit: 5,
  enable_2fa: false,
  force_2fa_admin: true,
  totp_issuer: 'Construction Platform',
  backup_codes_count: 10,
  enable_ip_whitelist: false,
  enable_rate_limiting: true,
  enable_security_headers: true,
  rate_limit_requests: 60,
  audit_log_retention: 365
})

const autoSaveTimeout = ref<NodeJS.Timeout | null>(null)

const handleSubmit = async () => {
  await settingsStore.updateSettings('security', formData)
}

const debouncedSave = () => {
  if (autoSaveTimeout.value) {
    clearTimeout(autoSaveTimeout.value)
  }
  
  autoSaveTimeout.value = setTimeout(async () => {
    if (hasUnsavedChanges.value) {
      await settingsStore.updateSettings('security', formData)
    }
  }, 2000)
  
  settingsStore.markHasUnsavedChanges()
}

// Watch for changes and trigger auto-save
const watchFields = () => {
  const originalValues = { ...formData }
  
  const checkForChanges = () => {
    const hasChanges = Object.keys(formData).some(key => {
      return formData[key as keyof SecuritySettingsData] !== originalValues[key as keyof SecuritySettingsData]
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
  
  // Load existing security settings
  const securitySettings = settingsStore.getSettingsByCategory('security')
  if (securitySettings) {
    Object.assign(formData, securitySettings)
  }
  
  watchFields()
})

onUnmounted(() => {
  if (autoSaveTimeout.value) {
    clearTimeout(autoSaveTimeout.value)
  }
})
</script>