<template>
  <div class="space-y-6">
    <!-- Legal Information -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Legal Information</h3>
      </template>
      
      <template #content>
        <div class="space-y-4">
          <!-- Privacy Policy URL -->
          <div>
            <VLabel for="privacy-policy">Privacy Policy URL</VLabel>
            <VInput
              id="privacy-policy"
              v-model="legalInfo.privacyPolicyUrl"
              placeholder="https://company.com/privacy"
              type="url"
              :disabled="!canWrite"
              class="mt-1"
            />
            <p class="text-xs text-muted-foreground mt-1">
              Link to your company's privacy policy document
            </p>
          </div>

          <!-- Terms of Service URL -->
          <div>
            <VLabel for="terms-of-service">Terms of Service URL</VLabel>
            <VInput
              id="terms-of-service"
              v-model="legalInfo.termsOfServiceUrl"
              placeholder="https://company.com/terms"
              type="url"
              :disabled="!canWrite"
              class="mt-1"
            />
            <p class="text-xs text-muted-foreground mt-1">
              Link to your terms of service or terms and conditions
            </p>
          </div>

          <!-- Cookie Policy URL -->
          <div>
            <VLabel for="cookie-policy">Cookie Policy URL</VLabel>
            <VInput
              id="cookie-policy"
              v-model="legalInfo.cookiePolicyUrl"
              placeholder="https://company.com/cookies"
              type="url"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>
        </div>
      </template>
    </VCard>

    <!-- Data & Compliance -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Data & Compliance</h3>
      </template>
      
      <template #content>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Data Retention -->
          <div>
            <VLabel for="data-retention">Data Retention Period (days)</VLabel>
            <VInput
              id="data-retention"
              v-model.number="compliance.dataRetentionDays"
              placeholder="2555"
              type="number"
              min="1"
              :disabled="!canWrite"
              class="mt-1"
            />
            <p class="text-xs text-muted-foreground mt-1">
              How long to keep deleted records (default: 7 years = 2555 days)
            </p>
          </div>

          <!-- GDPR Compliance -->
          <div>
            <VLabel>GDPR Compliance</VLabel>
            <div class="mt-2">
              <div class="flex items-center space-x-2">
                <VCheckbox
                  id="gdpr-compliant"
                  v-model="compliance.gdprCompliant"
                  :disabled="!canWrite"
                />
                <VLabel for="gdpr-compliant" class="text-sm">
                  GDPR Compliant
                </VLabel>
              </div>
              <p class="text-xs text-muted-foreground mt-1">
                Indicates if your company follows GDPR regulations
              </p>
            </div>
          </div>

          <!-- CCPA Compliance -->
          <div>
            <VLabel>CCPA Compliance</VLabel>
            <div class="mt-2">
              <div class="flex items-center space-x-2">
                <VCheckbox
                  id="ccpa-compliant"
                  v-model="compliance.ccpaCompliant"
                  :disabled="!canWrite"
                />
                <VLabel for="ccpa-compliant" class="text-sm">
                  CCPA Compliant
                </VLabel>
              </div>
              <p class="text-xs text-muted-foreground mt-1">
                California Consumer Privacy Act compliance
              </p>
            </div>
          </div>

          <!-- Data Processing Location -->
          <div>
            <VLabel for="data-location">Data Processing Location</VLabel>
            <VSelect
              id="data-location"
              v-model="compliance.dataProcessingLocation"
              :options="dataLocationOptions"
              placeholder="Select location"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>
        </div>
      </template>
    </VCard>

    <!-- Business Licenses -->
    <VCard>
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">Business Licenses</h3>
          <VButton
            @click="addLicense"
            variant="outline"
            size="sm"
            :disabled="!canWrite"
          >
            <Plus class="mr-2 h-4 w-4" />
            Add License
          </VButton>
        </div>
      </template>
      
      <template #content>
        <div class="space-y-4">
          <div
            v-for="(license, index) in businessLicenses"
            :key="index"
            class="p-4 border border-border rounded-lg"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <VLabel>License Name</VLabel>
                <VInput
                  v-model="license.name"
                  placeholder="e.g., General Contractor License"
                  :disabled="!canWrite"
                  class="mt-1"
                />
              </div>
              <div>
                <VLabel>License Number</VLabel>
                <VInput
                  v-model="license.number"
                  placeholder="e.g., GC-123456"
                  :disabled="!canWrite"
                  class="mt-1"
                />
              </div>
              <div>
                <VLabel>Issuing Authority</VLabel>
                <VInput
                  v-model="license.authority"
                  placeholder="e.g., State Licensing Board"
                  :disabled="!canWrite"
                  class="mt-1"
                />
              </div>
              <div>
                <VLabel>Issue Date</VLabel>
                <VInput
                  v-model="license.issueDate"
                  type="date"
                  :disabled="!canWrite"
                  class="mt-1"
                />
              </div>
              <div>
                <VLabel>Expiry Date</VLabel>
                <VInput
                  v-model="license.expiryDate"
                  type="date"
                  :disabled="!canWrite"
                  class="mt-1"
                />
              </div>
              <div class="flex items-center">
                <VCheckbox
                  v-model="license.isActive"
                  :disabled="!canWrite"
                />
                <VLabel class="ml-2 text-sm">Active</VLabel>
              </div>
            </div>
            <div class="flex justify-end mt-4">
              <VButton
                @click="removeLicense(index)"
                variant="outline"
                size="sm"
                :disabled="!canWrite"
              >
                <Trash2 class="mr-2 h-4 w-4" />
                Remove
              </VButton>
            </div>
          </div>
          
          <div v-if="businessLicenses.length === 0" class="text-center py-8 text-muted-foreground">
            No business licenses added yet. Click "Add License" to get started.
          </div>
        </div>
      </template>
    </VCard>

    <!-- Insurance Information -->
    <VCard>
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">Insurance Information</h3>
          <VButton
            @click="addInsurance"
            variant="outline"
            size="sm"
            :disabled="!canWrite"
          >
            <Plus class="mr-2 h-4 w-4" />
            Add Insurance
          </VButton>
        </div>
      </template>
      
      <template #content>
        <div class="space-y-4">
          <div
            v-for="(insurance, index) in insurancePolicies"
            :key="index"
            class="p-4 border border-border rounded-lg"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <VLabel>Insurance Type</VLabel>
                <VSelect
                  v-model="insurance.type"
                  :options="insuranceTypeOptions"
                  placeholder="Select insurance type"
                  :disabled="!canWrite"
                  class="mt-1"
                />
              </div>
              <div>
                <VLabel>Policy Number</VLabel>
                <VInput
                  v-model="insurance.policyNumber"
                  placeholder="e.g., POL-123456789"
                  :disabled="!canWrite"
                  class="mt-1"
                />
              </div>
              <div>
                <VLabel>Insurance Provider</VLabel>
                <VInput
                  v-model="insurance.provider"
                  placeholder="e.g., ABC Insurance Company"
                  :disabled="!canWrite"
                  class="mt-1"
                />
              </div>
              <div>
                <VLabel>Coverage Amount</VLabel>
                <VInput
                  v-model="insurance.coverageAmount"
                  placeholder="e.g., $1,000,000"
                  :disabled="!canWrite"
                  class="mt-1"
                />
              </div>
              <div>
                <VLabel>Effective Date</VLabel>
                <VInput
                  v-model="insurance.effectiveDate"
                  type="date"
                  :disabled="!canWrite"
                  class="mt-1"
                />
              </div>
              <div>
                <VLabel>Expiry Date</VLabel>
                <VInput
                  v-model="insurance.expiryDate"
                  type="date"
                  :disabled="!canWrite"
                  class="mt-1"
                />
              </div>
            </div>
            <div class="flex justify-end mt-4">
              <VButton
                @click="removeInsurance(index)"
                variant="outline"
                size="sm"
                :disabled="!canWrite"
              >
                <Trash2 class="mr-2 h-4 w-4" />
                Remove
              </VButton>
            </div>
          </div>
          
          <div v-if="insurancePolicies.length === 0" class="text-center py-8 text-muted-foreground">
            No insurance policies added yet. Click "Add Insurance" to get started.
          </div>
        </div>
      </template>
    </VCard>

    <!-- Legal Disclaimers -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Legal Disclaimers</h3>
      </template>
      
      <template #content>
        <div class="space-y-4">
          <!-- General Disclaimer -->
          <div>
            <VLabel for="general-disclaimer">General Disclaimer</VLabel>
            <VTextarea
              id="general-disclaimer"
              v-model="disclaimers.general"
              placeholder="Enter your general legal disclaimer..."
              rows="4"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Liability Disclaimer -->
          <div>
            <VLabel for="liability-disclaimer">Liability Disclaimer</VLabel>
            <VTextarea
              id="liability-disclaimer"
              v-model="disclaimers.liability"
              placeholder="Enter your liability disclaimer..."
              rows="4"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Professional Disclaimer -->
          <div>
            <VLabel for="professional-disclaimer">Professional Services Disclaimer</VLabel>
            <VTextarea
              id="professional-disclaimer"
              v-model="disclaimers.professional"
              placeholder="Enter disclaimer for professional services..."
              rows="4"
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
import { reactive, watch, ref } from 'vue'
import { VButton, VCard, VLabel, VInput, VSelect, VTextarea, VCheckbox } from '@/components/ui'
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
const legalInfo = reactive({
  privacyPolicyUrl: '',
  termsOfServiceUrl: '',
  cookiePolicyUrl: ''
})

const compliance = reactive({
  dataRetentionDays: 2555,
  gdprCompliant: false,
  ccpaCompliant: false,
  dataProcessingLocation: 'US'
})

const businessLicenses = ref<Array<{
  name: string
  number: string
  authority: string
  issueDate: string
  expiryDate: string
  isActive: boolean
}>>([])

const insurancePolicies = ref<Array<{
  type: string
  policyNumber: string
  provider: string
  coverageAmount: string
  effectiveDate: string
  expiryDate: string
}>>([])

const disclaimers = reactive({
  general: '',
  liability: '',
  professional: ''
})

// Options
const dataLocationOptions = [
  { value: 'US', label: 'United States' },
  { value: 'EU', label: 'European Union' },
  { value: 'CA', label: 'Canada' },
  { value: 'UK', label: 'United Kingdom' },
  { value: 'AU', label: 'Australia' },
  { value: 'APAC', label: 'Asia Pacific' },
  { value: 'GLOBAL', label: 'Global' }
]

const insuranceTypeOptions = [
  { value: 'general_liability', label: 'General Liability' },
  { value: 'professional_liability', label: 'Professional Liability' },
  { value: 'workers_compensation', label: 'Workers Compensation' },
  { value: 'commercial_property', label: 'Commercial Property' },
  { value: 'cyber_liability', label: 'Cyber Liability' },
  { value: 'errors_omissions', label: 'Errors & Omissions' },
  { value: 'directors_officers', label: 'Directors & Officers' },
  { value: 'commercial_auto', label: 'Commercial Auto' }
]

// Methods
function addLicense() {
  businessLicenses.value.push({
    name: '',
    number: '',
    authority: '',
    issueDate: '',
    expiryDate: '',
    isActive: true
  })
}

function removeLicense(index: number) {
  businessLicenses.value.splice(index, 1)
  updateLicenses()
}

function addInsurance() {
  insurancePolicies.value.push({
    type: '',
    policyNumber: '',
    provider: '',
    coverageAmount: '',
    effectiveDate: '',
    expiryDate: ''
  })
}

function removeInsurance(index: number) {
  insurancePolicies.value.splice(index, 1)
  updateInsurance()
}

function updateLicenses() {
  emit('update', 'business_licenses', businessLicenses.value)
}

function updateInsurance() {
  emit('update', 'insurance_policies', insurancePolicies.value)
}

// Initialize settings when props change
watch(
  () => props.settings,
  (newSettings) => {
    if (newSettings) {
      // Initialize legal info
      legalInfo.privacyPolicyUrl = newSettings.privacy_policy_url || ''
      legalInfo.termsOfServiceUrl = newSettings.terms_of_service_url || ''
      legalInfo.cookiePolicyUrl = newSettings.cookie_policy_url || ''
      
      // Initialize compliance
      compliance.dataRetentionDays = newSettings.data_retention_days || 2555
      compliance.gdprCompliant = newSettings.gdpr_compliant || false
      compliance.ccpaCompliant = newSettings.ccpa_compliant || false
      compliance.dataProcessingLocation = newSettings.data_processing_location || 'US'
      
      // Initialize licenses
      if (newSettings.business_licenses) {
        businessLicenses.value = [...newSettings.business_licenses]
      }
      
      // Initialize insurance
      if (newSettings.insurance_policies) {
        insurancePolicies.value = [...newSettings.insurance_policies]
      }
      
      // Initialize disclaimers
      disclaimers.general = newSettings.general_disclaimer || ''
      disclaimers.liability = newSettings.liability_disclaimer || ''
      disclaimers.professional = newSettings.professional_disclaimer || ''
    }
  },
  { immediate: true, deep: true }
)

// Emit changes
watch(legalInfo, (newLegalInfo) => {
  Object.entries(newLegalInfo).forEach(([key, value]) => {
    const mappedKey = key === 'privacyPolicyUrl' ? 'privacy_policy_url' :
                      key === 'termsOfServiceUrl' ? 'terms_of_service_url' :
                      key === 'cookiePolicyUrl' ? 'cookie_policy_url' : key
    emit('update', mappedKey, value)
  })
}, { deep: true })

watch(compliance, (newCompliance) => {
  Object.entries(newCompliance).forEach(([key, value]) => {
    const mappedKey = key === 'dataRetentionDays' ? 'data_retention_days' :
                      key === 'gdprCompliant' ? 'gdpr_compliant' :
                      key === 'ccpaCompliant' ? 'ccpa_compliant' :
                      key === 'dataProcessingLocation' ? 'data_processing_location' : key
    emit('update', mappedKey, value)
  })
}, { deep: true })

watch(businessLicenses, updateLicenses, { deep: true })
watch(insurancePolicies, updateInsurance, { deep: true })

watch(disclaimers, (newDisclaimers) => {
  Object.entries(newDisclaimers).forEach(([key, value]) => {
    const mappedKey = key === 'general' ? 'general_disclaimer' :
                      key === 'liability' ? 'liability_disclaimer' :
                      key === 'professional' ? 'professional_disclaimer' : key
    emit('update', mappedKey, value)
  })
}, { deep: true })
</script>