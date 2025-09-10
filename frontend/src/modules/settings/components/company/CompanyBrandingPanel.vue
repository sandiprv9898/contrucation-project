<template>
  <div class="space-y-6">
    <!-- Logo Management Section -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Logo & Brand Assets</h3>
      </template>
      
      <template #content>
        <div class="space-y-6">
          <!-- Primary Logo -->
          <div>
            <VLabel>Primary Logo</VLabel>
            <LogoUploadWidget
              v-model="brandingAssets.logo"
              asset-type="logo"
              variant="primary"
              :can-write="canWrite"
              :max-size="5"
              recommended-size="200x200"
              @upload="handleAssetUpload"
              @remove="handleAssetRemove"
            />
          </div>

          <!-- Logo Variants -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Light Version -->
            <div>
              <VLabel>Light Version (for dark backgrounds)</VLabel>
              <LogoUploadWidget
                v-model="brandingAssets.logoLight"
                asset-type="logo"
                variant="light"
                :can-write="canWrite"
                :max-size="5"
                recommended-size="200x200"
                @upload="handleAssetUpload"
                @remove="handleAssetRemove"
              />
            </div>

            <!-- Dark Version -->
            <div>
              <VLabel>Dark Version (for light backgrounds)</VLabel>
              <LogoUploadWidget
                v-model="brandingAssets.logoDark"
                asset-type="logo"
                variant="dark"
                :can-write="canWrite"
                :max-size="5"
                recommended-size="200x200"
                @upload="handleAssetUpload"
                @remove="handleAssetRemove"
              />
            </div>
          </div>

          <!-- Favicon -->
          <div>
            <div class="flex items-center gap-4 mb-3">
              <VLabel>Favicon</VLabel>
              <VButton
                v-if="brandingAssets.logo"
                @click="generateFavicon"
                variant="outline"
                size="sm"
                :disabled="!canWrite || generatingFavicon"
                :loading="generatingFavicon"
              >
                <Zap class="mr-2 h-4 w-4" />
                Generate from Logo
              </VButton>
            </div>
            <LogoUploadWidget
              v-model="brandingAssets.favicon"
              asset-type="favicon"
              variant="standard"
              :can-write="canWrite"
              :max-size="1"
              recommended-size="32x32"
              @upload="handleAssetUpload"
              @remove="handleAssetRemove"
            />
          </div>
        </div>
      </template>
    </VCard>

    <!-- Color Palette Section -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Brand Colors</h3>
      </template>
      
      <template #content>
        <div class="space-y-6">
          <!-- Primary Colors -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Primary Color -->
            <div>
              <VLabel for="primary-color">Primary Color</VLabel>
              <ColorPicker
                id="primary-color"
                v-model="colorPalette.primary"
                :disabled="!canWrite"
                @update="updateColor"
              />
            </div>

            <!-- Secondary Color -->
            <div>
              <VLabel for="secondary-color">Secondary Color</VLabel>
              <ColorPicker
                id="secondary-color"
                v-model="colorPalette.secondary"
                :disabled="!canWrite"
                @update="updateColor"
              />
            </div>

            <!-- Accent Color -->
            <div>
              <VLabel for="accent-color">Accent Color</VLabel>
              <ColorPicker
                id="accent-color"
                v-model="colorPalette.accent"
                :disabled="!canWrite"
                @update="updateColor"
              />
            </div>
          </div>

          <!-- Additional Colors -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Success Color -->
            <div>
              <VLabel for="success-color">Success Color</VLabel>
              <ColorPicker
                id="success-color"
                v-model="colorPalette.success"
                :disabled="!canWrite"
                @update="updateColor"
              />
            </div>

            <!-- Warning Color -->
            <div>
              <VLabel for="warning-color">Warning Color</VLabel>
              <ColorPicker
                id="warning-color"
                v-model="colorPalette.warning"
                :disabled="!canWrite"
                @update="updateColor"
              />
            </div>

            <!-- Error Color -->
            <div>
              <VLabel for="error-color">Error Color</VLabel>
              <ColorPicker
                id="error-color"
                v-model="colorPalette.error"
                :disabled="!canWrite"
                @update="updateColor"
              />
            </div>

            <!-- Info Color -->
            <div>
              <VLabel for="info-color">Info Color</VLabel>
              <ColorPicker
                id="info-color"
                v-model="colorPalette.info"
                :disabled="!canWrite"
                @update="updateColor"
              />
            </div>
          </div>

          <!-- Color Palette Preview -->
          <BrandingPreview
            :color-palette="colorPalette"
            :logo-assets="brandingAssets"
            class="mt-6"
          />
        </div>
      </template>
    </VCard>

    <!-- Typography Section -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Typography</h3>
      </template>
      
      <template #content>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Primary Font -->
          <div>
            <VLabel for="primary-font">Primary Font</VLabel>
            <VSelect
              id="primary-font"
              v-model="typography.primary"
              :options="fontOptions"
              placeholder="Select primary font"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Secondary Font -->
          <div>
            <VLabel for="secondary-font">Secondary Font</VLabel>
            <VSelect
              id="secondary-font"
              v-model="typography.secondary"
              :options="fontOptions"
              placeholder="Select secondary font"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>
        </div>
      </template>
    </VCard>

    <!-- Brand Guidelines Section -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Brand Guidelines</h3>
      </template>
      
      <template #content>
        <div class="space-y-4">
          <!-- Brand Statement -->
          <div>
            <VLabel for="brand-statement">Brand Statement</VLabel>
            <VTextarea
              id="brand-statement"
              v-model="brandGuidelines.statement"
              placeholder="A brief statement that captures your brand essence..."
              rows="3"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Brand Values -->
          <div>
            <VLabel for="brand-values">Brand Values (one per line)</VLabel>
            <VTextarea
              id="brand-values"
              v-model="brandGuidelines.values"
              placeholder="Excellence&#10;Innovation&#10;Reliability&#10;Customer Focus"
              rows="4"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Logo Usage Guidelines -->
          <div>
            <VLabel for="logo-usage">Logo Usage Guidelines</VLabel>
            <VTextarea
              id="logo-usage"
              v-model="brandGuidelines.logoUsage"
              placeholder="Guidelines for how the logo should and shouldn't be used..."
              rows="3"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>
        </div>
      </template>
    </VCard>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { VButton, VCard, VLabel, VSelect, VTextarea } from '@/components/ui'
import { Zap } from 'lucide-vue-next'
import type { CompanySettings } from '../../types/settings.types'

// Sub-components
import LogoUploadWidget from './LogoUploadWidget.vue'
import ColorPicker from './ColorPicker.vue'
import BrandingPreview from './BrandingPreview.vue'

// Props
interface Props {
  settings: CompanySettings | null
  canWrite: boolean
}

const props = withDefaults(defineProps<Props>(), {
  canWrite: false
})

// Emits
const emit = defineEmits<{
  update: [key: string, value: any]
}>()

// State
const generatingFavicon = ref(false)

const brandingAssets = reactive({
  logo: null as string | null,
  logoLight: null as string | null,
  logoDark: null as string | null,
  favicon: null as string | null,
})

const colorPalette = reactive({
  primary: '#f97316',
  secondary: '#475569',
  accent: '#22c55e',
  success: '#10b981',
  warning: '#f59e0b',
  error: '#ef4444',
  info: '#3b82f6'
})

const typography = reactive({
  primary: 'Inter',
  secondary: 'Inter'
})

const brandGuidelines = reactive({
  statement: '',
  values: '',
  logoUsage: ''
})

// Options
const fontOptions = [
  { value: 'Inter', label: 'Inter' },
  { value: 'Roboto', label: 'Roboto' },
  { value: 'Open Sans', label: 'Open Sans' },
  { value: 'Poppins', label: 'Poppins' },
  { value: 'Lato', label: 'Lato' },
  { value: 'Montserrat', label: 'Montserrat' },
  { value: 'Source Sans Pro', label: 'Source Sans Pro' },
  { value: 'Nunito', label: 'Nunito' }
]

// Methods
async function handleAssetUpload(assetType: string, variant: string, file: File) {
  // Implementation would upload to backend and update branding assets
  console.log('Uploading asset:', { assetType, variant, file })
}

function handleAssetRemove(assetType: string, variant: string) {
  // Implementation would remove from backend and update local state
  console.log('Removing asset:', { assetType, variant })
}

async function generateFavicon() {
  if (!brandingAssets.logo) return
  
  generatingFavicon.value = true
  try {
    // Implementation would call backend to generate favicon from logo
    console.log('Generating favicon from logo')
    
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 2000))
    
    // Update favicon asset
    brandingAssets.favicon = '/path/to/generated-favicon.png'
    
  } catch (error) {
    console.error('Failed to generate favicon:', error)
  } finally {
    generatingFavicon.value = false
  }
}

function updateColor(colorKey: string, value: string) {
  emit('update', `color_${colorKey}`, value)
}

// Initialize settings when props change
watch(
  () => props.settings,
  (newSettings) => {
    if (newSettings) {
      // Initialize branding assets
      brandingAssets.logo = newSettings.logo_url || null
      brandingAssets.logoLight = newSettings.logo_light_url || null
      brandingAssets.logoDark = newSettings.logo_dark_url || null
      brandingAssets.favicon = newSettings.favicon_url || null
      
      // Initialize color palette
      if (newSettings.primary_color) colorPalette.primary = newSettings.primary_color
      if (newSettings.secondary_color) colorPalette.secondary = newSettings.secondary_color
      if (newSettings.accent_color) colorPalette.accent = newSettings.accent_color
      
      // Initialize typography
      if (newSettings.primary_font) typography.primary = newSettings.primary_font
      if (newSettings.secondary_font) typography.secondary = newSettings.secondary_font
      
      // Initialize brand guidelines
      if (newSettings.brand_statement) brandGuidelines.statement = newSettings.brand_statement
      if (newSettings.brand_values) brandGuidelines.values = newSettings.brand_values
      if (newSettings.logo_usage) brandGuidelines.logoUsage = newSettings.logo_usage
    }
  },
  { immediate: true, deep: true }
)

// Emit changes
watch(colorPalette, (newPalette) => {
  Object.entries(newPalette).forEach(([key, value]) => {
    emit('update', `${key}_color`, value)
  })
}, { deep: true })

watch(typography, (newTypography) => {
  Object.entries(newTypography).forEach(([key, value]) => {
    emit('update', `${key}_font`, value)
  })
}, { deep: true })

watch(brandGuidelines, (newGuidelines) => {
  Object.entries(newGuidelines).forEach(([key, value]) => {
    const mappedKey = key === 'statement' ? 'brand_statement' : 
                      key === 'values' ? 'brand_values' : 
                      key === 'logoUsage' ? 'logo_usage' : key
    emit('update', mappedKey, value)
  })
}, { deep: true })
</script>