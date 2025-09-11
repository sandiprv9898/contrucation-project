<template>
  <div class="task-time-clock bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-medium text-gray-900">Time Clock</h3>
      <div v-if="isActivelyTracking" class="flex items-center space-x-2 text-green-600">
        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
        <span class="text-sm font-medium">Tracking: {{ currentDuration }}</span>
      </div>
    </div>

    <!-- Active Time Log Display -->
    <div v-if="isActivelyTracking && activeTimeLog" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h4 class="font-medium text-green-900">{{ activeTimeLog.task?.name }}</h4>
          <p class="text-sm text-green-700">{{ activeTimeLog.formatted_activity_type || 'Work' }}</p>
          <p class="text-xs text-green-600">Started: {{ formatTime(activeTimeLog.start_time) }}</p>
          <div v-if="activeTimeLog.clock_in_address" class="text-xs text-green-600 mt-1">
            <MapPin class="w-3 h-3 inline mr-1" />
            {{ activeTimeLog.clock_in_address }}
          </div>
        </div>
        <div class="text-right">
          <div class="text-2xl font-mono font-bold text-green-900">{{ currentDuration }}</div>
          <button
            @click="handleClockOut"
            :disabled="loading"
            class="mt-2 px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 disabled:opacity-50"
          >
            <Clock4 class="w-4 h-4 inline mr-1" />
            Clock Out
          </button>
        </div>
      </div>
    </div>

    <!-- Clock In Form -->
    <div v-else class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Select Task</label>
        <select 
          v-model="selectedTaskId" 
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          required
        >
          <option value="">Choose a task to work on...</option>
          <option v-for="task in tasks" :key="task.id" :value="task.id">
            {{ task.name }} ({{ task.project?.name }})
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Activity Type</label>
        <select 
          v-model="clockInForm.activity_type" 
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="work">Work</option>
          <option value="meeting">Meeting</option>
          <option value="inspection">Inspection</option>
          <option value="travel">Travel</option>
          <option value="break">Break</option>
          <option value="planning">Planning</option>
          <option value="documentation">Documentation</option>
          <option value="other">Other</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
        <textarea 
          v-model="clockInForm.description"
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          rows="2"
          placeholder="What are you working on?"
        ></textarea>
      </div>

      <!-- Location Section -->
      <div class="border-t pt-4">
        <div class="flex items-center justify-between mb-2">
          <label class="text-sm font-medium text-gray-700">Location</label>
          <button
            @click="getCurrentLocation"
            :disabled="gettingLocation"
            class="text-sm text-blue-600 hover:text-blue-800 flex items-center"
          >
            <MapPin class="w-4 h-4 mr-1" />
            {{ gettingLocation ? 'Getting location...' : 'Get current location' }}
          </button>
        </div>
        
        <div v-if="locationError" class="text-sm text-red-600 mb-2">
          {{ locationError }}
        </div>
        
        <div v-if="clockInForm.address" class="text-sm text-gray-600 bg-gray-50 p-2 rounded">
          <MapPin class="w-4 h-4 inline mr-1" />
          {{ clockInForm.address }}
        </div>
      </div>

      <!-- Photo Section -->
      <div class="border-t pt-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Clock In Photos (Optional)</label>
        <div class="flex items-center space-x-4">
          <input
            ref="photoInput"
            type="file"
            multiple
            accept="image/*"
            @change="handlePhotoSelect"
            class="hidden"
          >
          <button
            @click="$refs.photoInput?.click()"
            class="flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
          >
            <Camera class="w-4 h-4 mr-2" />
            Add Photos
          </button>
          <span v-if="clockInForm.photos.length" class="text-sm text-gray-600">
            {{ clockInForm.photos.length }} photo(s) selected
          </span>
        </div>
        
        <!-- Photo Preview -->
        <div v-if="clockInForm.photos.length" class="mt-2 flex space-x-2">
          <div 
            v-for="(photo, index) in photoPreviewUrls" 
            :key="index"
            class="relative w-16 h-16 rounded-lg overflow-hidden border"
          >
            <img :src="photo" alt="Preview" class="w-full h-full object-cover">
            <button
              @click="removePhoto(index)"
              class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full text-xs hover:bg-red-600"
            >
              ×
            </button>
          </div>
        </div>
      </div>

      <button
        @click="handleClockIn"
        :disabled="!selectedTaskId || loading"
        class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
      >
        <Clock4 class="w-4 h-4 mr-2" />
        {{ loading ? 'Clocking In...' : 'Clock In' }}
      </button>
    </div>

    <!-- Clock Out Modal -->
    <div v-if="showClockOutModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Clock Out</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
            <textarea 
              v-model="clockOutForm.description"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              rows="3"
              placeholder="What did you accomplish?"
            ></textarea>
          </div>

          <!-- Location for Clock Out -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="text-sm font-medium text-gray-700">Clock Out Location</label>
              <button
                @click="getCurrentLocationForClockOut"
                :disabled="gettingClockOutLocation"
                class="text-sm text-blue-600 hover:text-blue-800 flex items-center"
              >
                <MapPin class="w-4 h-4 mr-1" />
                {{ gettingClockOutLocation ? 'Getting location...' : 'Get current location' }}
              </button>
            </div>
            
            <div v-if="clockOutForm.address" class="text-sm text-gray-600 bg-gray-50 p-2 rounded">
              <MapPin class="w-4 h-4 inline mr-1" />
              {{ clockOutForm.address }}
            </div>
          </div>

          <!-- Clock Out Photos -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Clock Out Photos (Optional)</label>
            <div class="flex items-center space-x-4">
              <input
                ref="clockOutPhotoInput"
                type="file"
                multiple
                accept="image/*"
                @change="handleClockOutPhotoSelect"
                class="hidden"
              >
              <button
                @click="$refs.clockOutPhotoInput?.click()"
                class="flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
              >
                <Camera class="w-4 h-4 mr-2" />
                Add Photos
              </button>
              <span v-if="clockOutForm.photos.length" class="text-sm text-gray-600">
                {{ clockOutForm.photos.length }} photo(s) selected
              </span>
            </div>
            
            <!-- Clock Out Photo Preview -->
            <div v-if="clockOutForm.photos.length" class="mt-2 flex space-x-2">
              <div 
                v-for="(photo, index) in clockOutPhotoPreviewUrls" 
                :key="index"
                class="relative w-16 h-16 rounded-lg overflow-hidden border"
              >
                <img :src="photo" alt="Preview" class="w-full h-full object-cover">
                <button
                  @click="removeClockOutPhoto(index)"
                  class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full text-xs hover:bg-red-600"
                >
                  ×
                </button>
              </div>
            </div>
          </div>

          <!-- Billable Options -->
          <div class="flex items-center space-x-4">
            <label class="flex items-center">
              <input 
                type="checkbox" 
                v-model="clockOutForm.billable"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              >
              <span class="ml-2 text-sm text-gray-700">Billable time</span>
            </label>
            
            <div v-if="clockOutForm.billable" class="flex items-center space-x-2">
              <label class="text-sm text-gray-700">Rate:</label>
              <input
                type="number"
                v-model="clockOutForm.hourly_rate"
                step="0.01"
                min="0"
                placeholder="0.00"
                class="w-20 rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
              >
              <span class="text-sm text-gray-700">/hr</span>
            </div>
          </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button
            @click="showClockOutModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="confirmClockOut"
            :disabled="loading"
            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 disabled:opacity-50"
          >
            {{ loading ? 'Clocking Out...' : 'Clock Out' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="mt-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md text-sm">
      {{ error }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { Clock4, MapPin, Camera } from 'lucide-vue-next'
import { useTimeTracking } from '../composables/useTimeTracking'

interface Task {
  id: string
  name: string
  project?: {
    id: string
    name: string
  }
}

interface Props {
  tasks: Task[]
}

interface Emits {
  (e: 'clocked-in', timeLog: any): void
  (e: 'clocked-out', timeLog: any): void
  (e: 'error', error: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const {
  loading,
  error,
  activeTimeLog,
  isActivelyTracking,
  currentDuration,
  getActiveTimeLog,
  clockIn,
  clockOut
} = useTimeTracking()

// Form states
const selectedTaskId = ref('')
const showClockOutModal = ref(false)
const gettingLocation = ref(false)
const gettingClockOutLocation = ref(false)
const locationError = ref('')

const clockInForm = ref({
  latitude: undefined as number | undefined,
  longitude: undefined as number | undefined,
  address: '',
  photos: [] as File[],
  activity_type: 'work',
  description: ''
})

const clockOutForm = ref({
  latitude: undefined as number | undefined,
  longitude: undefined as number | undefined,
  address: '',
  photos: [] as File[],
  description: '',
  billable: true,
  hourly_rate: undefined as number | undefined
})

// Photo preview URLs
const photoPreviewUrls = ref<string[]>([])
const clockOutPhotoPreviewUrls = ref<string[]>([])

// Methods
const getCurrentLocation = async () => {
  if (!navigator.geolocation) {
    locationError.value = 'Geolocation is not supported by this browser'
    return
  }

  gettingLocation.value = true
  locationError.value = ''

  try {
    const position = await new Promise<GeolocationPosition>((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 300000 // 5 minutes
      })
    })

    clockInForm.value.latitude = position.coords.latitude
    clockInForm.value.longitude = position.coords.longitude

    // Get address from coordinates (reverse geocoding)
    await getAddressFromCoords(position.coords.latitude, position.coords.longitude, 'clockIn')
  } catch (err: any) {
    locationError.value = 'Unable to get location: ' + (err.message || 'Unknown error')
  } finally {
    gettingLocation.value = false
  }
}

const getCurrentLocationForClockOut = async () => {
  if (!navigator.geolocation) {
    return
  }

  gettingClockOutLocation.value = true

  try {
    const position = await new Promise<GeolocationPosition>((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 300000
      })
    })

    clockOutForm.value.latitude = position.coords.latitude
    clockOutForm.value.longitude = position.coords.longitude

    await getAddressFromCoords(position.coords.latitude, position.coords.longitude, 'clockOut')
  } catch (err) {
    console.error('Failed to get clock out location:', err)
  } finally {
    gettingClockOutLocation.value = false
  }
}

const getAddressFromCoords = async (lat: number, lng: number, type: 'clockIn' | 'clockOut') => {
  try {
    // Using a simple reverse geocoding service (you might want to use Google Maps API)
    const response = await fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lng}&localityLanguage=en`)
    const data = await response.json()
    
    const address = [
      data.locality,
      data.principalSubdivision,
      data.countryName
    ].filter(Boolean).join(', ')

    if (type === 'clockIn') {
      clockInForm.value.address = address || `${lat}, ${lng}`
    } else {
      clockOutForm.value.address = address || `${lat}, ${lng}`
    }
  } catch (err) {
    console.error('Failed to get address:', err)
    if (type === 'clockIn') {
      clockInForm.value.address = `${lat}, ${lng}`
    } else {
      clockOutForm.value.address = `${lat}, ${lng}`
    }
  }
}

const handlePhotoSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  const files = Array.from(target.files || [])
  
  clockInForm.value.photos = files
  
  // Create preview URLs
  photoPreviewUrls.value = files.map(file => URL.createObjectURL(file))
}

const removePhoto = (index: number) => {
  clockInForm.value.photos.splice(index, 1)
  URL.revokeObjectURL(photoPreviewUrls.value[index])
  photoPreviewUrls.value.splice(index, 1)
}

const handleClockOutPhotoSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  const files = Array.from(target.files || [])
  
  clockOutForm.value.photos = files
  clockOutPhotoPreviewUrls.value = files.map(file => URL.createObjectURL(file))
}

const removeClockOutPhoto = (index: number) => {
  clockOutForm.value.photos.splice(index, 1)
  URL.revokeObjectURL(clockOutPhotoPreviewUrls.value[index])
  clockOutPhotoPreviewUrls.value.splice(index, 1)
}

const handleClockIn = async () => {
  if (!selectedTaskId.value) return

  try {
    const timeLog = await clockIn(selectedTaskId.value, {
      latitude: clockInForm.value.latitude,
      longitude: clockInForm.value.longitude,
      address: clockInForm.value.address,
      photos: clockInForm.value.photos,
      activity_type: clockInForm.value.activity_type,
      description: clockInForm.value.description
    })

    // Reset form
    selectedTaskId.value = ''
    clockInForm.value = {
      latitude: undefined,
      longitude: undefined,
      address: '',
      photos: [],
      activity_type: 'work',
      description: ''
    }
    photoPreviewUrls.value.forEach(url => URL.revokeObjectURL(url))
    photoPreviewUrls.value = []

    emit('clocked-in', timeLog)
  } catch (err: any) {
    emit('error', err.message)
  }
}

const handleClockOut = () => {
  showClockOutModal.value = true
  getCurrentLocationForClockOut() // Automatically get location when opening modal
}

const confirmClockOut = async () => {
  try {
    const timeLog = await clockOut({
      latitude: clockOutForm.value.latitude,
      longitude: clockOutForm.value.longitude,
      address: clockOutForm.value.address,
      photos: clockOutForm.value.photos,
      description: clockOutForm.value.description,
      billable: clockOutForm.value.billable,
      hourly_rate: clockOutForm.value.hourly_rate
    })

    showClockOutModal.value = false
    
    // Reset form
    clockOutForm.value = {
      latitude: undefined,
      longitude: undefined,
      address: '',
      photos: [],
      description: '',
      billable: true,
      hourly_rate: undefined
    }
    clockOutPhotoPreviewUrls.value.forEach(url => URL.revokeObjectURL(url))
    clockOutPhotoPreviewUrls.value = []

    emit('clocked-out', timeLog)
  } catch (err: any) {
    emit('error', err.message)
  }
}

const formatTime = (dateString: string): string => {
  return new Date(dateString).toLocaleTimeString([], { 
    hour: '2-digit', 
    minute: '2-digit',
    hour12: true
  })
}

// Initialize
onMounted(async () => {
  await getActiveTimeLog()
})

// Clean up object URLs on unmount
watch(() => photoPreviewUrls.value, (newUrls, oldUrls) => {
  if (oldUrls) {
    oldUrls.forEach(url => URL.revokeObjectURL(url))
  }
})

watch(() => clockOutPhotoPreviewUrls.value, (newUrls, oldUrls) => {
  if (oldUrls) {
    oldUrls.forEach(url => URL.revokeObjectURL(url))
  }
})
</script>

<style scoped>
.task-time-clock {
  min-height: 200px;
}
</style>