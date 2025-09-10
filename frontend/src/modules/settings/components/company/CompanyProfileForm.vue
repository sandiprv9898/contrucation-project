<template>
  <div class="space-y-6">
    <!-- Company Information Section -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Company Information</h3>
      </template>
      
      <template #content>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

          <!-- Business Registration Number -->
          <div>
            <VLabel for="business-registration">Business Registration Number</VLabel>
            <VInput
              id="business-registration"
              v-model="localSettings.business_registration"
              placeholder="Enter registration number"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Tax ID -->
          <div>
            <VLabel for="tax-id">Tax Identification Number</VLabel>
            <VInput
              id="tax-id"
              v-model="localSettings.tax_identification"
              placeholder="12-3456789"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Industry Type -->
          <div>
            <VLabel for="industry-type">Industry</VLabel>
            <VSelect
              id="industry-type"
              v-model="localSettings.industry_type"
              :options="industryOptions"
              placeholder="Select industry"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Company Size -->
          <div>
            <VLabel for="company-size">Company Size</VLabel>
            <VSelect
              id="company-size"
              v-model="localSettings.company_size"
              :options="companySizeOptions"
              placeholder="Select company size"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Founded Date -->
          <div>
            <VLabel for="founded-date">Founded Date</VLabel>
            <VInput
              id="founded-date"
              v-model="localSettings.founded_date"
              type="date"
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
              placeholder="https://company.com"
              type="url"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Company Description -->
          <div class="md:col-span-2">
            <VLabel for="description">Company Description</VLabel>
            <VTextarea
              id="description"
              v-model="localSettings.description"
              placeholder="Describe your company, its mission, and services..."
              rows="4"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>
        </div>
      </template>
    </VCard>

    <!-- Social Media Section -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Social Media & Professional Profiles</h3>
      </template>
      
      <template #content>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- LinkedIn -->
          <div>
            <VLabel for="linkedin">LinkedIn Profile</VLabel>
            <VInput
              id="linkedin"
              v-model="socialMedia.linkedin"
              placeholder="https://linkedin.com/company/yourcompany"
              type="url"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Twitter -->
          <div>
            <VLabel for="twitter">Twitter/X Profile</VLabel>
            <VInput
              id="twitter"
              v-model="socialMedia.twitter"
              placeholder="https://twitter.com/yourcompany"
              type="url"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Facebook -->
          <div>
            <VLabel for="facebook">Facebook Page</VLabel>
            <VInput
              id="facebook"
              v-model="socialMedia.facebook"
              placeholder="https://facebook.com/yourcompany"
              type="url"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Instagram -->
          <div>
            <VLabel for="instagram">Instagram Profile</VLabel>
            <VInput
              id="instagram"
              v-model="socialMedia.instagram"
              placeholder="https://instagram.com/yourcompany"
              type="url"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>
        </div>
      </template>
    </VCard>

    <!-- Certifications Section -->
    <VCard>
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">Certifications & Licenses</h3>
          <VButton
            @click="addCertification"
            variant="outline"
            size="sm"
            :disabled="!canWrite"
          >
            <Plus class="mr-2 h-4 w-4" />
            Add Certification
          </VButton>
        </div>
      </template>
      
      <template #content>
        <div class="space-y-4">
          <div
            v-for="(cert, index) in certifications"
            :key="index"
            class="p-4 border border-border rounded-lg"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <VLabel>Certification Name</VLabel>
                <VInput
                  v-model="cert.name"
                  placeholder="e.g., OSHA 30-Hour Construction"
                  :disabled="!canWrite"
                  class="mt-1"
                />
              </div>
              <div>
                <VLabel>Issuing Authority</VLabel>
                <VInput
                  v-model="cert.authority"
                  placeholder="e.g., OSHA"
                  :disabled="!canWrite"
                  class="mt-1"
                />
              </div>
              <div>
                <VLabel>Issue Date</VLabel>
                <VInput
                  v-model="cert.issued_date"
                  type="date"
                  :disabled="!canWrite"
                  class="mt-1"
                />
              </div>
              <div>
                <VLabel>Expiry Date</VLabel>
                <VInput
                  v-model="cert.expiry_date"
                  type="date"
                  :disabled="!canWrite"
                  class="mt-1"
                />
              </div>
            </div>
            <div class="flex justify-end mt-4">
              <VButton
                @click="removeCertification(index)"
                variant="outline"
                size="sm"
                :disabled="!canWrite"
              >
                <Trash2 class="mr-2 h-4 w-4" />
                Remove
              </VButton>
            </div>
          </div>
          
          <div v-if="certifications.length === 0" class="text-center py-8 text-muted-foreground">
            No certifications added yet. Click "Add Certification" to get started.
          </div>
        </div>
      </template>
    </VCard>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, reactive } from 'vue'
import { VButton, VCard, VLabel, VInput, VSelect, VTextarea } from '@/components/ui'
import { Plus, Trash2 } from 'lucide-vue-next'
import type { CompanySettings } from '../../types/settings.types'

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
const localSettings = reactive<Partial<CompanySettings>>({})
const socialMedia = reactive<Record<string, string>>({
  linkedin: '',
  twitter: '',
  facebook: '',
  instagram: ''
})
const certifications = ref<Array<{
  name: string
  authority: string
  issued_date: string
  expiry_date: string
}>>([])

// Options
const industryOptions = [
  { value: 'construction', label: 'Construction' },
  { value: 'architecture', label: 'Architecture' },
  { value: 'engineering', label: 'Engineering' },
  { value: 'real_estate', label: 'Real Estate Development' },
  { value: 'infrastructure', label: 'Infrastructure' },
  { value: 'residential', label: 'Residential Construction' },
  { value: 'commercial', label: 'Commercial Construction' },
  { value: 'industrial', label: 'Industrial Construction' },
  { value: 'civil', label: 'Civil Engineering' },
  { value: 'mechanical', label: 'Mechanical Engineering' },
  { value: 'electrical', label: 'Electrical Engineering' },
  { value: 'plumbing', label: 'Plumbing & HVAC' },
  { value: 'landscaping', label: 'Landscaping' },
  { value: 'interior_design', label: 'Interior Design' },
  { value: 'project_management', label: 'Project Management' },
  { value: 'consulting', label: 'Construction Consulting' },
  { value: 'other', label: 'Other' }
]

const companySizeOptions = [
  { value: 'startup', label: 'Startup (1-10 employees)' },
  { value: 'small', label: 'Small (11-50 employees)' },
  { value: 'medium', label: 'Medium (51-200 employees)' },
  { value: 'large', label: 'Large (201-1000 employees)' },
  { value: 'enterprise', label: 'Enterprise (1000+ employees)' }
]

// Methods
function addCertification() {
  certifications.value.push({
    name: '',
    authority: '',
    issued_date: '',
    expiry_date: ''
  })
}

function removeCertification(index: number) {
  certifications.value.splice(index, 1)
  updateCertifications()
}

function updateCertifications() {
  emit('update', 'certifications', certifications.value)
}

function updateSocialMedia() {
  emit('update', 'social_media', { ...socialMedia })
}

// Initialize settings when props change
watch(
  () => props.settings,
  (newSettings) => {
    if (newSettings) {
      Object.assign(localSettings, newSettings)
      
      // Initialize social media
      if (newSettings.social_media) {
        Object.assign(socialMedia, newSettings.social_media)
      }
      
      // Initialize certifications
      if (newSettings.certifications) {
        certifications.value = [...newSettings.certifications]
      }
    }
  },
  { immediate: true, deep: true }
)

// Emit changes when local settings change
watch(
  localSettings,
  (newSettings) => {
    Object.entries(newSettings).forEach(([key, value]) => {
      if (value !== props.settings?.[key as keyof CompanySettings]) {
        emit('update', key, value)
      }
    })
  },
  { deep: true }
)

// Watch social media changes
watch(socialMedia, updateSocialMedia, { deep: true })

// Watch certifications changes
watch(certifications, updateCertifications, { deep: true })
</script>