<template>
  <SettingsLayout>
    <template #default="{ category, settings }">
      <!-- Company Settings -->
      <CompanySettings
        v-if="category === 'company'"
        :settings="settings"
        :can-write="settingsStore.canWriteCategory('company')"
      />

      <!-- System Settings -->
      <SystemPreferences
        v-else-if="category === 'system'"
        :settings="settings"
        :can-write="settingsStore.canWriteCategory('system')"
      />

      <!-- Notification Settings -->
      <NotificationSettings
        v-else-if="category === 'notifications'"
        :settings="settings"
        :can-write="settingsStore.canWriteCategory('notifications')"
      />

      <!-- Security Settings -->
      <SecuritySettings
        v-else-if="category === 'security'"
        :settings="settings"
        :can-write="settingsStore.canWriteCategory('security')"
      />

      <!-- Backup Settings -->
      <BackupSettings
        v-else-if="category === 'backup'"
        :settings="settings"
        :can-write="settingsStore.canWriteCategory('backup')"
      />

      <!-- Fallback -->
      <div v-else class="text-center py-12">
        <h3 class="text-lg font-medium mb-2">Settings Category Not Found</h3>
        <p class="text-muted-foreground">
          The requested settings category is not available.
        </p>
      </div>
    </template>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useHead } from '@unhead/vue'
import SettingsLayout from '@/modules/settings/components/SettingsLayout.vue'
import CompanySettings from '@/modules/settings/components/CompanySettings.vue'
// Import other components as they're created
// import SystemPreferences from '@/modules/settings/components/SystemPreferences.vue'
// import NotificationSettings from '@/modules/settings/components/NotificationSettings.vue'  
// import SecuritySettings from '@/modules/settings/components/SecuritySettings.vue'
// import BackupSettings from '@/modules/settings/components/BackupSettings.vue'
import { useSettingsStore } from '@/modules/settings/stores/settings.store'

// Composables
const settingsStore = useSettingsStore()

// Meta
useHead({
  title: 'Settings - Construction Management Platform',
  meta: [
    { name: 'description', content: 'Manage your organization settings, preferences, and configurations.' }
  ]
})

// Lifecycle
onMounted(() => {
  // Load settings if not already loaded
  if (!settingsStore.isLoaded) {
    settingsStore.loadSettings()
  }
})

// Placeholder components for now
const SystemPreferences = { template: '<div class="text-center py-12">System Preferences - Coming Soon</div>' }
const NotificationSettings = { template: '<div class="text-center py-12">Notification Settings - Coming Soon</div>' }
const SecuritySettings = { template: '<div class="text-center py-12">Security Settings - Coming Soon</div>' }
const BackupSettings = { template: '<div class="text-center py-12">Backup Settings - Coming Soon</div>' }
</script>