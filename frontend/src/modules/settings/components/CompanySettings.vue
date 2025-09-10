<template>
  <div class="space-y-6">
    <!-- Enhanced Company Settings with Tabs -->
    <div class="bg-white border border-gray-200 rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Manage your company profile, branding, and showcase</p>
          </div>
          <div v-if="hasUnsavedChanges" class="bg-amber-100 text-amber-800 text-xs px-3 py-1 rounded-full">
            Unsaved Changes
          </div>
        </div>
      </div>
      
      <div class="p-0">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 px-6">
          <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center',
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              <component :is="tab.icon" class="mr-2 h-4 w-4" />
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          <!-- Basic Information Tab -->
          <div v-if="activeTab === 'basic'" class="space-y-8">
            <!-- Company Information -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Company Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Company Name *</label>
                  <input 
                    v-model="formData.name"
                    type="text" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="Enter company name"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Legal Name</label>
                  <input 
                    v-model="formData.legal_name"
                    type="text" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="Enter legal company name"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Industry Type</label>
                  <select 
                    v-model="formData.industry_type"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="">Select industry</option>
                    <option value="construction">General Construction</option>
                    <option value="residential">Residential Construction</option>
                    <option value="commercial">Commercial Construction</option>
                    <option value="industrial">Industrial Construction</option>
                    <option value="infrastructure">Infrastructure</option>
                    <option value="renovation">Renovation & Remodeling</option>
                    <option value="electrical">Electrical Contracting</option>
                    <option value="plumbing">Plumbing</option>
                    <option value="hvac">HVAC</option>
                    <option value="roofing">Roofing</option>
                    <option value="architecture">Architecture</option>
                    <option value="engineering">Engineering</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Company Size</label>
                  <select 
                    v-model="formData.company_size"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="">Select company size</option>
                    <option value="startup">Startup (1-10 employees)</option>
                    <option value="small">Small (11-50 employees)</option>
                    <option value="medium">Medium (51-200 employees)</option>
                    <option value="large">Large (201-1000 employees)</option>
                    <option value="enterprise">Enterprise (1000+ employees)</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Founded Date</label>
                  <input 
                    v-model="formData.founded_date"
                    type="date" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                  <input 
                    v-model="formData.website"
                    type="url" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="https://company.com"
                  >
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                  <textarea 
                    v-model="formData.description"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="Describe your company, its mission, and services..."
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- Tax & Legal Information -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Tax & Legal Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Business Registration Number</label>
                  <input 
                    v-model="formData.business_registration"
                    type="text" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="Enter registration number"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Tax Identification Number</label>
                  <input 
                    v-model="formData.tax_identification"
                    type="text" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="12-3456789"
                  >
                </div>
              </div>
            </div>
          </div>

          <!-- Branding Tab -->
          <div v-if="activeTab === 'branding'" class="space-y-8">
            <!-- Logo Management -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Company Logo</h3>
              <div class="flex items-start gap-6">
                <div class="flex-shrink-0">
                  <div class="w-24 h-24 border-2 border-gray-300 border-dashed rounded-lg flex items-center justify-center bg-gray-50">
                    <img 
                      v-if="formData.logo_url" 
                      :src="formData.logo_url" 
                      alt="Company Logo" 
                      class="w-full h-full object-contain rounded-lg"
                    >
                    <div v-else class="text-center">
                      <svg class="w-8 h-8 text-gray-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                      </svg>
                      <span class="text-xs text-gray-500">No Logo</span>
                    </div>
                  </div>
                </div>
                <div class="flex-1">
                  <input 
                    type="file" 
                    accept="image/*" 
                    @change="handleLogoUpload"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                  <p class="text-xs text-gray-500 mt-1">PNG, JPG, or SVG up to 5MB. Recommended: 200x200px</p>
                </div>
              </div>
            </div>

            <!-- Brand Colors -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Brand Colors</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Primary Color</label>
                  <div class="flex items-center gap-3">
                    <input 
                      v-model="formData.primary_color"
                      type="color" 
                      class="w-12 h-10 border border-gray-300 rounded cursor-pointer"
                    >
                    <input 
                      v-model="formData.primary_color"
                      type="text" 
                      class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="#f97316"
                    >
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Secondary Color</label>
                  <div class="flex items-center gap-3">
                    <input 
                      v-model="formData.secondary_color"
                      type="color" 
                      class="w-12 h-10 border border-gray-300 rounded cursor-pointer"
                    >
                    <input 
                      v-model="formData.secondary_color"
                      type="text" 
                      class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="#475569"
                    >
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Accent Color</label>
                  <div class="flex items-center gap-3">
                    <input 
                      v-model="formData.accent_color"
                      type="color" 
                      class="w-12 h-10 border border-gray-300 rounded cursor-pointer"
                    >
                    <input 
                      v-model="formData.accent_color"
                      type="text" 
                      class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="#22c55e"
                    >
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Contact Tab -->
          <div v-if="activeTab === 'contact'" class="space-y-8">
            <!-- Primary Contact -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Primary Contact Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Company Email *</label>
                  <input 
                    v-model="formData.email"
                    type="email" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="contact@company.com"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                  <input 
                    v-model="formData.phone"
                    type="tel" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="+1 (555) 123-4567"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Fax Number</label>
                  <input 
                    v-model="formData.fax"
                    type="tel" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="+1 (555) 123-4568"
                  >
                </div>
              </div>
            </div>

            <!-- Business Address -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Business Address</h3>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Street Address *</label>
                  <input 
                    v-model="formData.address_line_1"
                    type="text" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="123 Main Street"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Address Line 2</label>
                  <input 
                    v-model="formData.address_line_2"
                    type="text" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="Suite 100"
                  >
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                    <input 
                      v-model="formData.city"
                      type="text" 
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                      placeholder="New York"
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">State/Province *</label>
                    <input 
                      v-model="formData.state_province"
                      type="text" 
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                      placeholder="NY"
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code *</label>
                    <input 
                      v-model="formData.postal_code"
                      type="text" 
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                      placeholder="10001"
                    >
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Portfolio Tab -->
          <div v-if="activeTab === 'portfolio'" class="space-y-6">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Portfolio & Showcase</h3>
              <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Add Portfolio Item
              </button>
            </div>
            <div class="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg">
              <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
              </svg>
              <h4 class="text-lg font-medium text-gray-900 mb-2">No portfolio items yet</h4>
              <p class="text-gray-500">Showcase your company's best projects, certifications, and achievements.</p>
            </div>
          </div>

          <!-- Legal & Compliance Tab -->
          <div v-if="activeTab === 'legal'" class="space-y-8">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Legal & Compliance</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Privacy Policy URL</label>
                  <input 
                    v-model="formData.privacy_policy_url"
                    type="url" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="https://company.com/privacy"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Terms of Service URL</label>
                  <input 
                    v-model="formData.terms_of_service_url"
                    type="url" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="https://company.com/terms"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Data Retention Period (days)</label>
                  <input 
                    v-model="formData.data_retention_days"
                    type="number" 
                    min="1"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="2555"
                  >
                  <p class="text-xs text-gray-500 mt-1">How long to keep deleted records (default: 7 years)</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, reactive } from 'vue'
import { User, Palette, Phone, Briefcase, Scale } from 'lucide-vue-next'
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
const activeTab = ref('basic')
const formData = reactive<Partial<CompanySettings>>({
  name: '',
  legal_name: '',
  industry_type: '',
  company_size: '',
  founded_date: '',
  website: '',
  description: '',
  business_registration: '',
  tax_identification: '',
  logo_url: '',
  primary_color: '#f97316',
  secondary_color: '#475569',
  accent_color: '#22c55e',
  email: '',
  phone: '',
  fax: '',
  address_line_1: '',
  address_line_2: '',
  city: '',
  state_province: '',
  postal_code: '',
  privacy_policy_url: '',
  terms_of_service_url: '',
  data_retention_days: 2555
})

// Tab Configuration
const tabs = [
  { id: 'basic', name: 'Basic Info', icon: User },
  { id: 'branding', name: 'Branding', icon: Palette },
  { id: 'contact', name: 'Contact', icon: Phone },
  { id: 'portfolio', name: 'Portfolio', icon: Briefcase },
  { id: 'legal', name: 'Legal', icon: Scale }
]

// Computed
const hasUnsavedChanges = computed(() => {
  return Object.keys(settingsStore.unsavedChanges?.company || {}).length > 0
})

// Methods
function handleLogoUpload(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  
  if (!file) return

  // Create preview URL
  const reader = new FileReader()
  reader.onload = (e) => {
    formData.logo_url = e.target?.result as string
  }
  reader.readAsDataURL(file)

  // TODO: Upload to server
  console.log('Logo file selected:', file)
}

function handleSettingUpdate(key: string, value: any) {
  settingsStore.updateLocalSetting('company', key, value)
}

// Initialize form data when props change
watch(
  () => props.settings,
  (newSettings) => {
    if (newSettings) {
      Object.assign(formData, newSettings)
    }
  },
  { immediate: true, deep: true }
)

// Update store when form data changes
watch(
  formData,
  (newData) => {
    // Only update if we have write permissions and data is different
    if (props.canWrite) {
      Object.entries(newData).forEach(([key, value]) => {
        const currentValue = props.settings?.[key as keyof CompanySettings]
        if (value !== currentValue && value !== '') {
          console.log(`🔄 Updating ${key} from "${currentValue}" to "${value}"`)
          handleSettingUpdate(key, value)
        }
      })
    }
  },
  { deep: true }
)
</script>