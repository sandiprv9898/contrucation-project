<template>
  <div class="flex min-h-screen bg-background">
    <!-- Settings Sidebar Navigation -->
    <div class="w-64 bg-card border-r border-border">
      <div class="p-4">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <h1 class="text-xl font-bold">Settings</h1>
          <VBadge v-if="settingsStore.state.hasUnsavedChanges" variant="destructive" class="text-xs">
            Unsaved
          </VBadge>
        </div>

        <!-- Navigation Menu -->
        <nav class="space-y-2">
          <button
            v-for="navItem in filteredNavigation"
            :key="navItem.category"
            @click="setActiveCategory(navItem.category)"
            :class="cn(
              'flex items-center gap-3 w-full p-3 text-left rounded-lg transition-colors',
              activeCategory === navItem.category
                ? 'bg-primary text-primary-foreground'
                : 'text-muted-foreground hover:bg-muted hover:text-foreground'
            )"
          >
            <component :is="navItem.icon" class="h-4 w-4" />
            <span class="font-medium">{{ navItem.label }}</span>
            <VBadge 
              v-if="hasUnsavedChanges(navItem.category)"
              variant="secondary"
              class="ml-auto text-xs"
            >
              •
            </VBadge>
          </button>
        </nav>

        <!-- Action Buttons -->
        <div class="mt-8 space-y-2">
          <VButton
            v-if="settingsStore.state.hasUnsavedChanges"
            @click="saveAllChanges"
            :disabled="settingsStore.state.isSaving"
            class="w-full h-8"
            size="sm"
          >
            <Save class="mr-2 h-4 w-4" />
            Save All Changes
          </VButton>
          
          <VButton
            v-if="settingsStore.state.hasUnsavedChanges"
            @click="discardAllChanges"
            variant="outline"
            class="w-full h-8"
            size="sm"
          >
            <X class="mr-2 h-4 w-4" />
            Discard Changes
          </VButton>
        </div>

        <!-- Import/Export -->
        <div class="mt-6 pt-4 border-t border-border">
          <div class="flex gap-2">
            <VButton
              @click="exportSettings"
              variant="outline"
              size="sm"
              class="flex-1 h-8"
            >
              <Download class="mr-2 h-4 w-4" />
              Export
            </VButton>
            <VButton
              @click="showImportDialog = true"
              variant="outline"
              size="sm"
              class="flex-1 h-8"
            >
              <Upload class="mr-2 h-4 w-4" />
              Import
            </VButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Settings Content Area -->
    <div class="flex-1 flex flex-col">
      <!-- Header with breadcrumb -->
      <div class="bg-card border-b border-border p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 text-sm text-muted-foreground">
              <Settings class="h-4 w-4" />
              <ChevronRight class="h-4 w-4" />
              <span class="font-medium text-foreground">
                {{ currentNavItem?.label }}
              </span>
            </div>
          </div>
          
          <div class="flex items-center gap-2">
            <!-- Auto-save indicator -->
            <div v-if="settingsStore.state.isSaving" class="flex items-center gap-2 text-sm text-muted-foreground">
              <div class="h-2 w-2 bg-blue-600 rounded-full animate-pulse"></div>
              Saving...
            </div>
            
            <!-- Last updated -->
            <div v-else-if="settingsStore.state.lastUpdated" class="text-sm text-muted-foreground">
              Last updated: {{ formatDate(settingsStore.state.lastUpdated) }}
            </div>

            <!-- Category actions -->
            <VButton
              v-if="canWriteCurrentCategory"
              @click="resetToDefaults"
              variant="outline"
              size="sm"
              class="h-8"
            >
              <RotateCcw class="mr-2 h-4 w-4" />
              Reset to Defaults
            </VButton>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="flex-1 overflow-auto p-6">
        <div class="max-w-4xl mx-auto">
          <!-- Error Alert -->
          <VAlert
            v-if="settingsStore.state.error"
            variant="destructive"
            class="mb-6"
            @dismiss="settingsStore.clearError"
          >
            <AlertTriangle class="h-4 w-4" />
            <span class="font-medium">Settings Error</span>
            <p>{{ settingsStore.state.error }}</p>
          </VAlert>

          <!-- Loading State -->
          <div v-if="settingsStore.state.isLoading" class="flex items-center justify-center py-12">
            <div class="flex items-center gap-3">
              <div class="h-5 w-5 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
              <span class="text-muted-foreground">Loading settings...</span>
            </div>
          </div>

          <!-- Settings Content -->
          <div v-else-if="settingsStore.isLoaded" class="space-y-8">
            <CompanySettings v-show="activeCategory === 'company'" />
            <SystemSettings v-show="activeCategory === 'system'" />
            <NotificationSettings v-show="activeCategory === 'notifications'" />
            <SecuritySettings v-show="activeCategory === 'security'" />
            <BackupSettings v-show="activeCategory === 'backup'" />
          </div>

          <!-- No permissions -->
          <div v-else-if="!canReadCurrentCategory" class="text-center py-12">
            <Lock class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
            <h3 class="text-lg font-medium mb-2">Access Restricted</h3>
            <p class="text-muted-foreground">
              You don't have permission to view {{ currentNavItem?.label.toLowerCase() }} settings.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Import Dialog -->
    <VModal
      v-model="showImportDialog"
      title="Import Settings"
      max-width="md"
    >
      <div class="space-y-4">
        <div>
          <VLabel for="import-file">Settings File</VLabel>
          <input
            id="import-file"
            ref="fileInputRef"
            type="file"
            accept=".json"
            @change="handleFileSelection"
            class="w-full mt-1 p-2 border border-border rounded-md"
          />
        </div>

        <div>
          <VLabel>Categories to Import</VLabel>
          <div class="space-y-2 mt-2">
            <div v-for="category in settingsNavigation" :key="category.category" class="flex items-center gap-2">
              <VCheckbox
                :id="`import-${category.category}`"
                v-model="importCategories"
                :value="category.category"
              />
              <VLabel :for="`import-${category.category}`">{{ category.label }}</VLabel>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <VCheckbox id="overwrite-existing" v-model="overwriteExisting" />
          <VLabel for="overwrite-existing">Overwrite existing settings</VLabel>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2">
          <VButton variant="outline" @click="showImportDialog = false">
            Cancel
          </VButton>
          <VButton
            @click="handleImport"
            :disabled="!selectedFile || importCategories.length === 0 || settingsStore.state.isSaving"
          >
            <Upload class="mr-2 h-4 w-4" />
            Import Settings
          </VButton>
        </div>
      </template>
    </VModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { 
  VButton, VCard, VBadge, VAlert, VModal, VLabel, VCheckbox
} from '@/components/ui'
import { 
  Settings, ChevronRight, Building2, Cog, Bell, Shield, HardDrive,
  Save, X, Download, Upload, RotateCcw, AlertTriangle, Lock
} from 'lucide-vue-next'
import { cn } from '@/utils/cn'
import { useSettingsStore } from '../stores/settings.store'
import type { SettingCategory } from '../types/settings.types'
import CompanySettings from './CompanySettings.vue'
import SystemSettings from './SystemSettings.vue'
import NotificationSettings from './NotificationSettings.vue'
import SecuritySettings from './SecuritySettings.vue'
import BackupSettings from './BackupSettings.vue'

interface NavigationItem {
  category: SettingCategory
  label: string
  icon: any
  requiredRole?: string
}

const settingsNavigation: NavigationItem[] = [
  {
    category: 'company',
    label: 'Company',
    icon: Building2,
    requiredRole: 'admin'
  },
  {
    category: 'system',
    label: 'System',
    icon: Cog,
    requiredRole: 'admin'
  },
  {
    category: 'notifications',
    label: 'Notifications',
    icon: Bell,
    requiredRole: 'manager'
  },
  {
    category: 'security',
    label: 'Security',
    icon: Shield,
    requiredRole: 'admin'
  },
  {
    category: 'backup',
    label: 'Backup',
    icon: HardDrive,
    requiredRole: 'admin'
  }
]

// Composables
const route = useRoute()
const router = useRouter()
const settingsStore = useSettingsStore()

// State
const activeCategory = ref<SettingCategory>('company')
const showImportDialog = ref(false)
const selectedFile = ref<File | null>(null)
const importCategories = ref<SettingCategory[]>([])
const overwriteExisting = ref(false)
const fileInputRef = ref<HTMLInputElement>()

// Computed
const filteredNavigation = computed(() => {
  return settingsNavigation.filter(item => {
    return settingsStore.canReadCategory(item.category)
  })
})

const currentNavItem = computed(() => {
  return settingsNavigation.find(item => item.category === activeCategory.value)
})

const currentCategorySettings = computed(() => {
  if (!settingsStore.isLoaded || !settingsStore.state.settings) return null
  return settingsStore.state.settings[activeCategory.value]
})

const canReadCurrentCategory = computed(() => {
  return settingsStore.canReadCategory(activeCategory.value)
})

const canWriteCurrentCategory = computed(() => {
  return settingsStore.canWriteCategory(activeCategory.value)
})

// Methods
function setActiveCategory(category: SettingCategory) {
  activeCategory.value = category
  // Update URL without navigation
  router.replace({ 
    name: route.name, 
    query: { ...route.query, category } 
  })
}

function hasUnsavedChanges(category: SettingCategory) {
  return settingsStore.unsavedChanges[category] && 
         Object.keys(settingsStore.unsavedChanges[category]).length > 0
}

async function saveAllChanges() {
  try {
    await settingsStore.saveAllUnsavedChanges()
  } catch (error) {
    console.error('Failed to save all changes:', error)
  }
}

function discardAllChanges() {
  settingsStore.discardUnsavedChanges()
}

async function exportSettings() {
  try {
    await settingsStore.exportSettings()
  } catch (error) {
    console.error('Failed to export settings:', error)
  }
}

function handleFileSelection(event: Event) {
  const input = event.target as HTMLInputElement
  selectedFile.value = input.files?.[0] || null
}

async function handleImport() {
  if (!selectedFile.value || importCategories.value.length === 0) return

  try {
    await settingsStore.importSettings(
      selectedFile.value,
      overwriteExisting.value,
      importCategories.value
    )
    showImportDialog.value = false
    resetImportForm()
  } catch (error) {
    console.error('Failed to import settings:', error)
  }
}

function resetImportForm() {
  selectedFile.value = null
  importCategories.value = []
  overwriteExisting.value = false
  if (fileInputRef.value) {
    fileInputRef.value.value = ''
  }
}

async function resetToDefaults() {
  if (!activeCategory.value) return

  try {
    await settingsStore.resetCategoryToDefaults(activeCategory.value)
  } catch (error) {
    console.error(`Failed to reset ${activeCategory.value} to defaults:`, error)
  }
}

function formatDate(dateString: string) {
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(dateString))
}

// Lifecycle
onMounted(async () => {
  // Load settings if not already loaded
  if (!settingsStore.isLoaded) {
    await settingsStore.loadSettings()
  }

  // Set initial category from URL
  const categoryFromUrl = route.query.category as SettingCategory
  if (categoryFromUrl && settingsNavigation.find(item => item.category === categoryFromUrl)) {
    activeCategory.value = categoryFromUrl
  } else {
    // Default to first available category
    const firstAvailable = filteredNavigation.value[0]
    if (firstAvailable) {
      activeCategory.value = firstAvailable.category
    }
  }
})

// Auto-save when settings change
watch(
  () => settingsStore.unsavedChanges,
  (changes) => {
    Object.keys(changes).forEach(category => {
      if (changes[category as SettingCategory] && Object.keys(changes[category as SettingCategory]).length > 0) {
        settingsStore.debouncedSave(category as SettingCategory)
      }
    })
  },
  { deep: true }
)
</script>