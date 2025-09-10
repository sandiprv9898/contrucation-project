<template>
  <div class="space-y-6">
    <!-- Company Profile Section -->
    <VCard>
      <template #header>
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold">Company Profile</h3>
            <p class="text-sm text-muted-foreground">Basic company information and branding</p>
          </div>
          <VBadge v-if="hasUnsavedChanges" variant="secondary" class="text-xs">
            Unsaved Changes
          </VBadge>
        </div>
      </template>
      
      <template #content>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Company Logo -->
          <div class="md:col-span-2">
            <VLabel for="company-logo">Company Logo</VLabel>
            <div class="flex items-center gap-4 mt-2">
              <div class="h-16 w-16 rounded-lg border border-border flex items-center justify-center overflow-hidden bg-muted">
                <img
                  v-if="settings?.logo_url"
                  :src="settings.logo_url"
                  :alt="settings.name || 'Company Logo'"
                  class="h-full w-full object-cover"
                />
                <Building2 v-else class="h-8 w-8 text-muted-foreground" />
              </div>
              <div>
                <VButton
                  @click="triggerFileUpload"
                  variant="outline"
                  size="sm"
                  class="h-8"
                  :disabled="!canWrite"
                >
                  <Upload class="mr-2 h-4 w-4" />
                  Upload Logo
                </VButton>
                <p class="text-xs text-muted-foreground mt-1">
                  PNG, JPG up to 2MB. Recommended: 200x200px
                </p>
              </div>
            </div>
            <input
              ref="fileInputRef"
              type="file"
              accept="image/*"
              @change="handleLogoUpload"
              class="hidden"
            />
          </div>

          <!-- Company Name -->
          <div>
            <VLabel for="company-name" required>Company Name</VLabel>
            <VInput
              id="company-name"
              v-model="localSettings.name"
              placeholder="Enter company name"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Legal Name -->
          <div>
            <VLabel for="legal-name">Legal Name</VLabel>
            <VInput
              id="legal-name"
              v-model="localSettings.legal_name"
              placeholder="Enter legal company name"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Industry -->
          <div>
            <VLabel for="industry">Industry</VLabel>
            <VSelect
              id="industry"
              v-model="localSettings.industry"
              :options="industryOptions"
              placeholder="Select industry"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Website -->
          <div>
            <VLabel for="website">Website</VLabel>
            <VInput
              id="website"
              v-model="localSettings.website"
              placeholder="https://example.com"
              type="url"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>
        </div>
      </template>
    </VCard>

    <!-- Contact Information Section -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Contact Information</h3>
      </template>
      
      <template #content>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Email -->
          <div>
            <VLabel for="company-email" required>Company Email</VLabel>
            <VInput
              id="company-email"
              v-model="localSettings.email"
              placeholder="contact@company.com"
              type="email"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Phone -->
          <div>
            <VLabel for="company-phone">Phone Number</VLabel>
            <VInput
              id="company-phone"
              v-model="localSettings.phone"
              placeholder="+1 (555) 123-4567"
              type="tel"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Fax -->
          <div>
            <VLabel for="company-fax">Fax Number</VLabel>
            <VInput
              id="company-fax"
              v-model="localSettings.fax"
              placeholder="+1 (555) 123-4568"
              type="tel"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>
        </div>
      </template>
    </VCard>

    <!-- Address Section -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Business Address</h3>
      </template>
      
      <template #content>
        <div class="space-y-4">
          <!-- Address Line 1 -->
          <div>
            <VLabel for="address-line-1" required>Street Address</VLabel>
            <VInput
              id="address-line-1"
              v-model="localSettings.address_line_1"
              placeholder="123 Main Street"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Address Line 2 -->
          <div>
            <VLabel for="address-line-2">Address Line 2</VLabel>
            <VInput
              id="address-line-2"
              v-model="localSettings.address_line_2"
              placeholder="Suite 100"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- City -->
            <div>
              <VLabel for="city" required>City</VLabel>
              <VInput
                id="city"
                v-model="localSettings.city"
                placeholder="New York"
                :disabled="!canWrite"
                class="mt-1"
              />
            </div>

            <!-- State/Province -->
            <div>
              <VLabel for="state-province" required>State/Province</VLabel>
              <VInput
                id="state-province"
                v-model="localSettings.state_province"
                placeholder="NY"
                :disabled="!canWrite"
                class="mt-1"
              />
            </div>

            <!-- Postal Code -->
            <div>
              <VLabel for="postal-code" required>Postal Code</VLabel>
              <VInput
                id="postal-code"
                v-model="localSettings.postal_code"
                placeholder="10001"
                :disabled="!canWrite"
                class="mt-1"
              />
            </div>
          </div>

          <!-- Country -->
          <div>
            <VLabel for="country" required>Country</VLabel>
            <VSelect
              id="country"
              v-model="localSettings.country"
              :options="countryOptions"
              placeholder="Select country"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>
        </div>
      </template>
    </VCard>

    <!-- Financial Settings Section -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Financial Settings</h3>
      </template>
      
      <template #content>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Tax ID -->
          <div>
            <VLabel for="tax-id">Tax ID</VLabel>
            <VInput
              id="tax-id"
              v-model="localSettings.tax_id"
              placeholder="12-3456789"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Currency -->
          <div>
            <VLabel for="currency" required>Default Currency</VLabel>
            <VSelect
              id="currency"
              v-model="localSettings.currency"
              :options="currencyOptions"
              placeholder="Select currency"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Fiscal Year Start -->
          <div class="md:col-span-2">
            <VLabel for="fiscal-year">Fiscal Year Start</VLabel>
            <VSelect
              id="fiscal-year"
              v-model="localSettings.fiscal_year_start"
              :options="fiscalYearOptions"
              placeholder="Select fiscal year start"
              :disabled="!canWrite"
              class="mt-1 max-w-xs"
            />
          </div>
        </div>
      </template>
    </VCard>

    <!-- Branding Section -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Branding & Theme</h3>
      </template>
      
      <template #content>
        <div class="space-y-6">
          <!-- Color Palette -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Primary Color -->
            <div>
              <VLabel for="primary-color">Primary Color</VLabel>
              <div class="flex items-center gap-3 mt-2">
                <input
                  id="primary-color"
                  v-model="localSettings.primary_color"
                  type="color"
                  :disabled="!canWrite"
                  class="h-10 w-16 rounded border border-border cursor-pointer disabled:cursor-not-allowed"
                />
                <VInput
                  v-model="localSettings.primary_color"
                  placeholder="#f97316"
                  :disabled="!canWrite"
                  class="flex-1"
                />
              </div>
            </div>

            <!-- Secondary Color -->
            <div>
              <VLabel for="secondary-color">Secondary Color</VLabel>
              <div class="flex items-center gap-3 mt-2">
                <input
                  id="secondary-color"
                  v-model="localSettings.secondary_color"
                  type="color"
                  :disabled="!canWrite"
                  class="h-10 w-16 rounded border border-border cursor-pointer disabled:cursor-not-allowed"
                />
                <VInput
                  v-model="localSettings.secondary_color"
                  placeholder="#475569"
                  :disabled="!canWrite"
                  class="flex-1"
                />
              </div>
            </div>

            <!-- Accent Color -->
            <div>
              <VLabel for="accent-color">Accent Color</VLabel>
              <div class="flex items-center gap-3 mt-2">
                <input
                  id="accent-color"
                  v-model="localSettings.accent_color"
                  type="color"
                  :disabled="!canWrite"
                  class="h-10 w-16 rounded border border-border cursor-pointer disabled:cursor-not-allowed"
                />
                <VInput
                  v-model="localSettings.accent_color"
                  placeholder="#22c55e"
                  :disabled="!canWrite"
                  class="flex-1"
                />
              </div>
            </div>
          </div>

          <!-- Color Preview -->
          <div class="p-4 border border-border rounded-lg">
            <h4 class="font-medium mb-3">Color Preview</h4>
            <div class="flex items-center gap-3">
              <div
                class="h-8 w-20 rounded"
                :style="{ backgroundColor: localSettings.primary_color }"
              ></div>
              <div
                class="h-8 w-20 rounded"
                :style="{ backgroundColor: localSettings.secondary_color }"
              ></div>
              <div
                class="h-8 w-20 rounded"
                :style="{ backgroundColor: localSettings.accent_color }"
              ></div>
            </div>
          </div>
        </div>
      </template>
    </VCard>

    <!-- Legal & Compliance Section -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Legal & Compliance</h3>
      </template>
      
      <template #content>
        <div class="space-y-4">
          <!-- Privacy Policy URL -->
          <div>
            <VLabel for="privacy-policy">Privacy Policy URL</VLabel>
            <VInput
              id="privacy-policy"
              v-model="localSettings.privacy_policy_url"
              placeholder="https://company.com/privacy"
              type="url"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Terms of Service URL -->
          <div>
            <VLabel for="terms-of-service">Terms of Service URL</VLabel>
            <VInput
              id="terms-of-service"
              v-model="localSettings.terms_of_service_url"
              placeholder="https://company.com/terms"
              type="url"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Data Retention -->
          <div>
            <VLabel for="data-retention">Data Retention Period (days)</VLabel>
            <VInput
              id="data-retention"
              v-model="localSettings.data_retention_days"
              placeholder="2555"
              type="number"
              min="1"
              :disabled="!canWrite"
              class="mt-1 max-w-xs"
            />
            <p class="text-xs text-muted-foreground mt-1">
              How long to keep deleted records (default: 7 years)
            </p>
          </div>
        </div>
      </template>
    </VCard>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, reactive } from 'vue'
import { VButton, VCard, VLabel, VInput, VSelect, VBadge } from '@/components/ui'
import { Building2, Upload } from 'lucide-vue-next'
import { useSettingsStore } from '../stores/settings.store'
import type { CompanySettings } from '../types/settings.types'

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

// State
const fileInputRef = ref<HTMLInputElement>()
const localSettings = reactive<Partial<CompanySettings>>({})

// Computed
const hasUnsavedChanges = computed(() => {
  return Object.keys(settingsStore.unsavedChanges.company || {}).length > 0
})

// Options
const industryOptions = [
  { value: 'construction', label: 'Construction' },
  { value: 'architecture', label: 'Architecture' },
  { value: 'engineering', label: 'Engineering' },
  { value: 'real_estate', label: 'Real Estate' },
  { value: 'manufacturing', label: 'Manufacturing' },
  { value: 'technology', label: 'Technology' },
  { value: 'other', label: 'Other' }
]

const currencyOptions = [
  { value: 'USD', label: 'US Dollar ($)' },
  { value: 'EUR', label: 'Euro (€)' },
  { value: 'GBP', label: 'British Pound (£)' },
  { value: 'CAD', label: 'Canadian Dollar (C$)' },
  { value: 'AUD', label: 'Australian Dollar (A$)' },
  { value: 'JPY', label: 'Japanese Yen (¥)' }
]

const countryOptions = [
  { value: 'US', label: 'United States' },
  { value: 'CA', label: 'Canada' },
  { value: 'GB', label: 'United Kingdom' },
  { value: 'AU', label: 'Australia' },
  { value: 'FR', label: 'France' },
  { value: 'DE', label: 'Germany' },
  { value: 'ES', label: 'Spain' },
  { value: 'IT', label: 'Italy' }
]

const fiscalYearOptions = [
  { value: '01-01', label: 'January 1st' },
  { value: '04-01', label: 'April 1st' },
  { value: '07-01', label: 'July 1st' },
  { value: '10-01', label: 'October 1st' }
]

// Methods
function triggerFileUpload() {
  fileInputRef.value?.click()
}

async function handleLogoUpload(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  
  if (!file) return

  try {
    const logoUrl = await settingsStore.uploadCompanyLogo(file)
    localSettings.logo_url = logoUrl
  } catch (error) {
    console.error('Failed to upload logo:', error)
  }
}

// Initialize local settings when props change
watch(
  () => props.settings,
  (newSettings) => {
    if (newSettings) {
      Object.assign(localSettings, newSettings)
    }
  },
  { immediate: true, deep: true }
)

// Update store when local settings change
watch(
  localSettings,
  (newSettings) => {
    Object.entries(newSettings).forEach(([key, value]) => {
      if (value !== props.settings?.[key as keyof CompanySettings]) {
        settingsStore.updateLocalSetting('company', key, value)
      }
    })
  },
  { deep: true }
)
</script>