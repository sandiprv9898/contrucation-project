<template>
  <div class="space-y-6">
    <!-- Primary Contact Information -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Primary Contact Information</h3>
      </template>
      
      <template #content>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Email -->
          <div>
            <VLabel for="company-email" required>Company Email</VLabel>
            <VInput
              id="company-email"
              v-model="contactInfo.email"
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
              v-model="contactInfo.phone"
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
              v-model="contactInfo.fax"
              placeholder="+1 (555) 123-4568"
              type="tel"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Mobile -->
          <div>
            <VLabel for="company-mobile">Mobile Number</VLabel>
            <VInput
              id="company-mobile"
              v-model="contactInfo.mobile"
              placeholder="+1 (555) 123-4569"
              type="tel"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>
        </div>
      </template>
    </VCard>

    <!-- Business Address -->
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
              v-model="address.line1"
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
              v-model="address.line2"
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
                v-model="address.city"
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
                v-model="address.state"
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
                v-model="address.postalCode"
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
              v-model="address.country"
              :options="countryOptions"
              placeholder="Select country"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>
        </div>
      </template>
    </VCard>

    <!-- Operating Hours -->
    <VCard>
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">Operating Hours</h3>
          <VButton
            @click="copyToAllDays"
            variant="outline"
            size="sm"
            :disabled="!canWrite"
          >
            <Copy class="mr-2 h-4 w-4" />
            Copy to All Days
          </VButton>
        </div>
      </template>
      
      <template #content>
        <div class="space-y-4">
          <div
            v-for="day in daysOfWeek"
            :key="day.key"
            class="flex items-center gap-4 p-3 border border-border rounded-lg"
          >
            <div class="w-20">
              <VLabel class="text-sm font-medium">{{ day.name }}</VLabel>
            </div>
            
            <div class="flex items-center gap-2">
              <VCheckbox
                v-model="operatingHours[day.key].isOpen"
                :disabled="!canWrite"
              />
              <span class="text-sm">Open</span>
            </div>
            
            <div v-if="operatingHours[day.key].isOpen" class="flex items-center gap-2">
              <VInput
                v-model="operatingHours[day.key].openTime"
                type="time"
                :disabled="!canWrite"
                class="w-24"
              />
              <span class="text-sm text-muted-foreground">to</span>
              <VInput
                v-model="operatingHours[day.key].closeTime"
                type="time"
                :disabled="!canWrite"
                class="w-24"
              />
            </div>
            
            <div v-else class="text-sm text-muted-foreground">
              Closed
            </div>
          </div>
        </div>
      </template>
    </VCard>

    <!-- Emergency Contact -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Emergency Contact</h3>
      </template>
      
      <template #content>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Emergency Contact Name -->
          <div>
            <VLabel for="emergency-contact-name">Contact Name</VLabel>
            <VInput
              id="emergency-contact-name"
              v-model="emergencyContact.name"
              placeholder="John Doe"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Emergency Contact Phone -->
          <div>
            <VLabel for="emergency-contact-phone">Contact Phone</VLabel>
            <VInput
              id="emergency-contact-phone"
              v-model="emergencyContact.phone"
              placeholder="+1 (555) 911-0000"
              type="tel"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Emergency Contact Email -->
          <div>
            <VLabel for="emergency-contact-email">Contact Email</VLabel>
            <VInput
              id="emergency-contact-email"
              v-model="emergencyContact.email"
              placeholder="emergency@company.com"
              type="email"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Emergency Contact Relationship -->
          <div>
            <VLabel for="emergency-contact-relationship">Relationship</VLabel>
            <VSelect
              id="emergency-contact-relationship"
              v-model="emergencyContact.relationship"
              :options="relationshipOptions"
              placeholder="Select relationship"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>
        </div>
      </template>
    </VCard>

    <!-- Time Zone & Preferences -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Time Zone & Preferences</h3>
      </template>
      
      <template #content>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Time Zone -->
          <div>
            <VLabel for="timezone">Time Zone</VLabel>
            <VSelect
              id="timezone"
              v-model="preferences.timezone"
              :options="timezoneOptions"
              placeholder="Select time zone"
              :disabled="!canWrite"
              class="mt-1"
            />
          </div>

          <!-- Preferred Communication Method -->
          <div>
            <VLabel for="preferred-communication">Preferred Communication</VLabel>
            <VSelect
              id="preferred-communication"
              v-model="preferences.preferredCommunication"
              :options="communicationOptions"
              placeholder="Select preference"
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
import { reactive, watch } from 'vue'
import { VButton, VCard, VLabel, VInput, VSelect, VCheckbox } from '@/components/ui'
import { Copy } from 'lucide-vue-next'
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
const contactInfo = reactive({
  email: '',
  phone: '',
  fax: '',
  mobile: ''
})

const address = reactive({
  line1: '',
  line2: '',
  city: '',
  state: '',
  postalCode: '',
  country: ''
})

const operatingHours = reactive({
  monday: { isOpen: true, openTime: '09:00', closeTime: '17:00' },
  tuesday: { isOpen: true, openTime: '09:00', closeTime: '17:00' },
  wednesday: { isOpen: true, openTime: '09:00', closeTime: '17:00' },
  thursday: { isOpen: true, openTime: '09:00', closeTime: '17:00' },
  friday: { isOpen: true, openTime: '09:00', closeTime: '17:00' },
  saturday: { isOpen: false, openTime: '09:00', closeTime: '17:00' },
  sunday: { isOpen: false, openTime: '09:00', closeTime: '17:00' }
})

const emergencyContact = reactive({
  name: '',
  phone: '',
  email: '',
  relationship: ''
})

const preferences = reactive({
  timezone: 'UTC',
  preferredCommunication: 'email'
})

// Options
const daysOfWeek = [
  { key: 'monday', name: 'Monday' },
  { key: 'tuesday', name: 'Tuesday' },
  { key: 'wednesday', name: 'Wednesday' },
  { key: 'thursday', name: 'Thursday' },
  { key: 'friday', name: 'Friday' },
  { key: 'saturday', name: 'Saturday' },
  { key: 'sunday', name: 'Sunday' }
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

const relationshipOptions = [
  { value: 'owner', label: 'Owner' },
  { value: 'manager', label: 'Manager' },
  { value: 'supervisor', label: 'Supervisor' },
  { value: 'partner', label: 'Business Partner' },
  { value: 'emergency_services', label: 'Emergency Services' }
]

const timezoneOptions = [
  { value: 'UTC', label: 'UTC' },
  { value: 'America/New_York', label: 'Eastern Time (ET)' },
  { value: 'America/Chicago', label: 'Central Time (CT)' },
  { value: 'America/Denver', label: 'Mountain Time (MT)' },
  { value: 'America/Los_Angeles', label: 'Pacific Time (PT)' },
  { value: 'America/Toronto', label: 'Eastern Time - Toronto' },
  { value: 'Europe/London', label: 'Greenwich Mean Time' },
  { value: 'Europe/Paris', label: 'Central European Time' },
  { value: 'Asia/Tokyo', label: 'Japan Standard Time' }
]

const communicationOptions = [
  { value: 'email', label: 'Email' },
  { value: 'phone', label: 'Phone' },
  { value: 'sms', label: 'SMS/Text' },
  { value: 'whatsapp', label: 'WhatsApp' },
  { value: 'slack', label: 'Slack' }
]

// Methods
function copyToAllDays() {
  const mondayHours = { ...operatingHours.monday }
  Object.keys(operatingHours).forEach(day => {
    if (day !== 'monday') {
      operatingHours[day as keyof typeof operatingHours] = { ...mondayHours }
    }
  })
}

// Initialize settings when props change
watch(
  () => props.settings,
  (newSettings) => {
    if (newSettings) {
      // Initialize contact info
      contactInfo.email = newSettings.email || ''
      contactInfo.phone = newSettings.phone || ''
      contactInfo.fax = newSettings.fax || ''
      contactInfo.mobile = newSettings.mobile || ''
      
      // Initialize address
      address.line1 = newSettings.address_line_1 || ''
      address.line2 = newSettings.address_line_2 || ''
      address.city = newSettings.city || ''
      address.state = newSettings.state_province || ''
      address.postalCode = newSettings.postal_code || ''
      address.country = newSettings.country || ''
      
      // Initialize operating hours if available
      if (newSettings.operating_hours) {
        Object.assign(operatingHours, newSettings.operating_hours)
      }
      
      // Initialize emergency contact
      if (newSettings.emergency_contact) {
        Object.assign(emergencyContact, newSettings.emergency_contact)
      }
      
      // Initialize preferences
      preferences.timezone = newSettings.timezone || 'UTC'
      preferences.preferredCommunication = newSettings.preferred_communication || 'email'
    }
  },
  { immediate: true, deep: true }
)

// Emit changes
watch(contactInfo, (newContactInfo) => {
  Object.entries(newContactInfo).forEach(([key, value]) => {
    emit('update', key, value)
  })
}, { deep: true })

watch(address, (newAddress) => {
  const addressMapping = {
    line1: 'address_line_1',
    line2: 'address_line_2',
    city: 'city',
    state: 'state_province',
    postalCode: 'postal_code',
    country: 'country'
  }
  
  Object.entries(newAddress).forEach(([key, value]) => {
    const mappedKey = addressMapping[key as keyof typeof addressMapping]
    emit('update', mappedKey, value)
  })
}, { deep: true })

watch(operatingHours, (newHours) => {
  emit('update', 'operating_hours', { ...newHours })
}, { deep: true })

watch(emergencyContact, (newContact) => {
  emit('update', 'emergency_contact', { ...newContact })
}, { deep: true })

watch(preferences, (newPreferences) => {
  Object.entries(newPreferences).forEach(([key, value]) => {
    const mappedKey = key === 'preferredCommunication' ? 'preferred_communication' : key
    emit('update', mappedKey, value)
  })
}, { deep: true })
</script>