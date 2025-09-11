<template>
  <div class="bg-white rounded-lg border border-gray-200 p-6">
    <!-- Work Status Header -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center space-x-3">
        <div :class="[
          'w-4 h-4 rounded-full',
          isActivelyWorking ? 'bg-green-500 animate-pulse' : 'bg-gray-300'
        ]"></div>
        <h3 class="text-lg font-medium text-gray-900">
          {{ isActivelyWorking ? 'Work in Progress' : 'Ready to Start Work' }}
        </h3>
      </div>
      
      <!-- Timer Display -->
      <div v-if="isActivelyWorking" class="text-right">
        <div class="text-2xl font-mono font-bold text-blue-600">{{ currentDuration }}</div>
        <div class="text-sm text-gray-500">Active work session</div>
      </div>
    </div>

    <!-- Start Work Section -->
    <div v-if="!isActivelyWorking" class="space-y-4">
      <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <h4 class="font-medium text-green-900 mb-3 flex items-center">
          <Play class="w-5 h-5 mr-2" />
          Start Work Session
        </h4>
        
        <!-- Activity Type -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Work Activity</label>
          <select 
            v-model="startWorkData.activity_type" 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Select activity type</option>
            <option value="work">General Work</option>
            <option value="inspection">Inspection</option>
            <option value="planning">Planning</option>
            <option value="documentation">Documentation</option>
            <option value="meeting">Meeting</option>
            <option value="travel">Travel</option>
            <option value="break">Break</option>
            <option value="other">Other</option>
          </select>
        </div>

        <!-- Start Comment -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Start Work Comment</label>
          <textarea 
            v-model="startWorkData.description"
            placeholder="Describe what you're starting to work on..."
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          ></textarea>
        </div>

        <!-- Photo Capture -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Start Work Photos</label>
          <div class="space-y-3">
            <!-- Camera Capture -->
            <div class="flex items-center space-x-3">
              <button
                @click="captureStartPhoto"
                :disabled="isCapturingPhoto"
                class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
              >
                <Camera class="w-4 h-4 mr-2" />
                {{ isCapturingPhoto ? 'Capturing...' : 'Take Photo' }}
              </button>
              
              <!-- File Upload -->
              <input
                ref="startPhotoInput"
                type="file"
                accept="image/*"
                multiple
                @change="handleStartPhotoUpload"
                class="hidden"
              />
              <button
                @click="($refs.startPhotoInput as HTMLInputElement | undefined)?.click()"
                class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
              >
                <Upload class="w-4 h-4 mr-2" />
                Upload Photos
              </button>
            </div>

            <!-- Photo Preview -->
            <div v-if="startWorkData.photos.length > 0" class="grid grid-cols-3 gap-2 mt-3">
              <div
                v-for="(photo, index) in startWorkData.photos"
                :key="index"
                class="relative group"
              >
                <img
                  :src="photo.preview"
                  :alt="`Start photo ${index + 1}`"
                  class="w-full h-20 object-cover rounded-md"
                />
                <button
                  @click="removeStartPhoto(index)"
                  class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <X class="w-3 h-3" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Location Info -->
        <div v-if="currentLocation" class="mb-4 p-3 bg-blue-50 rounded-md">
          <div class="flex items-center text-sm text-blue-800">
            <MapPin class="w-4 h-4 mr-2" />
            <span>Location: {{ currentLocation.address || `${currentLocation.lat.toFixed(6)}, ${currentLocation.lng.toFixed(6)}` }}</span>
          </div>
        </div>

        <!-- Start Work Button -->
        <button
          @click="startWork"
          :disabled="isStartingWork || !startWorkData.activity_type"
          class="w-full flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <Play class="w-5 h-5 mr-2" />
          {{ isStartingWork ? 'Starting Work...' : 'Start Work' }}
        </button>
      </div>
    </div>

    <!-- Stop Work Section -->
    <div v-if="isActivelyWorking" class="space-y-4">
      <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <h4 class="font-medium text-red-900 mb-3 flex items-center">
          <Square class="w-5 h-5 mr-2" />
          Stop Work Session
        </h4>

        <!-- Current Session Info -->
        <div class="mb-4 p-3 bg-white rounded-md border">
          <div class="text-sm text-gray-600 space-y-1">
            <div><strong>Activity:</strong> {{ activeSession?.activity_type }}</div>
            <div><strong>Started:</strong> {{ formatTime(activeSession?.start_time) }}</div>
            <div><strong>Duration:</strong> {{ currentDuration }}</div>
          </div>
        </div>

        <!-- Stop Comment -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Work Completion Comment</label>
          <textarea 
            v-model="stopWorkData.description"
            placeholder="Describe what you accomplished during this work session..."
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          ></textarea>
        </div>

        <!-- Stop Photos -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">End Work Photos</label>
          <div class="space-y-3">
            <div class="flex items-center space-x-3">
              <button
                @click="captureStopPhoto"
                :disabled="isCapturingPhoto"
                class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
              >
                <Camera class="w-4 h-4 mr-2" />
                {{ isCapturingPhoto ? 'Capturing...' : 'Take Photo' }}
              </button>
              
              <input
                ref="stopPhotoInput"
                type="file"
                accept="image/*"
                multiple
                @change="handleStopPhotoUpload"
                class="hidden"
              />
              <button
                @click="($refs.stopPhotoInput as HTMLInputElement)?.click()"
                class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
              >
                <Upload class="w-4 h-4 mr-2" />
                Upload Photos
              </button>
            </div>

            <div v-if="stopWorkData.photos.length > 0" class="grid grid-cols-3 gap-2 mt-3">
              <div
                v-for="(photo, index) in stopWorkData.photos"
                :key="index"
                class="relative group"
              >
                <img
                  :src="photo.preview"
                  :alt="`Stop photo ${index + 1}`"
                  class="w-full h-20 object-cover rounded-md"
                />
                <button
                  @click="removeStopPhoto(index)"
                  class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <X class="w-3 h-3" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Billable Work -->
        <div class="mb-4">
          <label class="flex items-center">
            <input
              v-model="stopWorkData.billable"
              type="checkbox"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span class="ml-2 text-sm text-gray-700">Mark this work as billable</span>
          </label>
          
          <div v-if="stopWorkData.billable" class="mt-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Hourly Rate</label>
            <input
              v-model.number="stopWorkData.hourly_rate"
              type="number"
              min="0"
              step="0.01"
              placeholder="0.00"
              class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <!-- Stop Work Button -->
        <button
          @click="stopWork"
          :disabled="isStoppingWork"
          class="w-full flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <Square class="w-5 h-5 mr-2" />
          {{ isStoppingWork ? 'Stopping Work...' : 'Stop Work' }}
        </button>
      </div>
    </div>

    <!-- Error Display -->
    <div v-if="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
      <div class="text-sm text-red-800">{{ error }}</div>
    </div>

    <!-- Success Message -->
    <div v-if="successMessage" class="mt-4 p-3 bg-green-50 border border-green-200 rounded-md">
      <div class="text-sm text-green-800">{{ successMessage }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Play, Square, Camera, Upload, X, MapPin } from 'lucide-vue-next'
import { useTimeTracking } from '../composables/useTimeTracking'

interface Props {
  taskId: string
}

interface PhotoFile {
  file: File
  preview: string
}

interface Location {
  lat: number
  lng: number
  address?: string
}

const props = defineProps<Props>()
const emit = defineEmits<{
  workStarted: [session: any]
  workStopped: [session: any]
}>()

// Time tracking composable
const {
  activeTimeLog,
  isActivelyTracking,
  currentDuration,
  clockIn,
  clockOut,
  getActiveTimeLog
} = useTimeTracking()

// Local state
const error = ref<string | null>(null)
const successMessage = ref<string | null>(null)
const isStartingWork = ref(false)
const isStoppingWork = ref(false)
const isCapturingPhoto = ref(false)
const currentLocation = ref<Location | null>(null)

// Work session data
const startWorkData = ref({
  activity_type: '',
  description: '',
  photos: [] as PhotoFile[]
})

const stopWorkData = ref({
  description: '',
  photos: [] as PhotoFile[],
  billable: false,
  hourly_rate: 0
})

// Computed
const isActivelyWorking = computed(() => isActivelyTracking.value)
const activeSession = computed(() => activeTimeLog.value)

// Location tracking
const watchId = ref<number | null>(null)

const getCurrentLocation = (): Promise<Location> => {
  return new Promise((resolve, reject) => {
    if (!navigator.geolocation) {
      reject(new Error('Geolocation is not supported'))
      return
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        const location = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        }
        
        // Try to get address
        if ((window as any).geocoder) {
          // If geocoding service is available
          (window as any).geocoder.reverse(location.lat, location.lng)
            .then((address: string) => {
              resolve({ ...location, address })
            })
            .catch(() => resolve(location))
        } else {
          resolve(location)
        }
      },
      (error) => reject(error),
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 300000 // 5 minutes
      }
    )
  })
}

// Photo handling
const captureStartPhoto = async () => {
  try {
    isCapturingPhoto.value = true
    const stream = await navigator.mediaDevices.getUserMedia({ 
      video: { facingMode: 'environment' } 
    })
    
    // Create video element and canvas for photo capture
    const video = document.createElement('video')
    const canvas = document.createElement('canvas')
    const context = canvas.getContext('2d')
    
    video.srcObject = stream
    await video.play()
    
    // Set canvas dimensions
    canvas.width = video.videoWidth
    canvas.height = video.videoHeight
    
    // Capture frame
    context?.drawImage(video, 0, 0)
    
    // Stop video stream
    stream.getTracks().forEach(track => track.stop())
    
    // Convert to blob and create file
    canvas.toBlob((blob) => {
      if (blob) {
        const file = new File([blob], `start-work-${Date.now()}.jpg`, { type: 'image/jpeg' })
        const preview = URL.createObjectURL(blob)
        startWorkData.value.photos.push({ file, preview })
      }
    }, 'image/jpeg', 0.8)
    
  } catch (err) {
    console.error('Error capturing photo:', err)
    error.value = 'Failed to capture photo. Please try uploading an image instead.'
  } finally {
    isCapturingPhoto.value = false
  }
}

const captureStopPhoto = async () => {
  try {
    isCapturingPhoto.value = true
    const stream = await navigator.mediaDevices.getUserMedia({ 
      video: { facingMode: 'environment' } 
    })
    
    const video = document.createElement('video')
    const canvas = document.createElement('canvas')
    const context = canvas.getContext('2d')
    
    video.srcObject = stream
    await video.play()
    
    canvas.width = video.videoWidth
    canvas.height = video.videoHeight
    context?.drawImage(video, 0, 0)
    
    stream.getTracks().forEach(track => track.stop())
    
    canvas.toBlob((blob) => {
      if (blob) {
        const file = new File([blob], `stop-work-${Date.now()}.jpg`, { type: 'image/jpeg' })
        const preview = URL.createObjectURL(blob)
        stopWorkData.value.photos.push({ file, preview })
      }
    }, 'image/jpeg', 0.8)
    
  } catch (err) {
    console.error('Error capturing photo:', err)
    error.value = 'Failed to capture photo. Please try uploading an image instead.'
  } finally {
    isCapturingPhoto.value = false
  }
}

const handleStartPhotoUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  const files = target.files
  
  if (files) {
    Array.from(files).forEach(file => {
      if (file.type.startsWith('image/')) {
        const preview = URL.createObjectURL(file)
        startWorkData.value.photos.push({ file, preview })
      }
    })
  }
}

const handleStopPhotoUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  const files = target.files
  
  if (files) {
    Array.from(files).forEach(file => {
      if (file.type.startsWith('image/')) {
        const preview = URL.createObjectURL(file)
        stopWorkData.value.photos.push({ file, preview })
      }
    })
  }
}

const removeStartPhoto = (index: number) => {
  const photo = startWorkData.value.photos[index]
  URL.revokeObjectURL(photo.preview)
  startWorkData.value.photos.splice(index, 1)
}

const removeStopPhoto = (index: number) => {
  const photo = stopWorkData.value.photos[index]
  URL.revokeObjectURL(photo.preview)
  stopWorkData.value.photos.splice(index, 1)
}

// Work session methods
const startWork = async () => {
  try {
    isStartingWork.value = true
    error.value = null
    
    // Get current location
    const location = await getCurrentLocation()
    
    // Prepare clock in data
    const clockInData = {
      latitude: location.lat,
      longitude: location.lng,
      ...(location.address && { address: location.address }),
      activity_type: startWorkData.value.activity_type,
      description: startWorkData.value.description,
      photos: startWorkData.value.photos.map(p => p.file)
    }
    
    // Clock in
    const session = await clockIn(props.taskId, clockInData)
    
    successMessage.value = 'Work session started successfully!'
    emit('workStarted', session)
    
    // Reset form
    startWorkData.value = {
      activity_type: '',
      description: '',
      photos: []
    }
    
    // Clear success message after 3 seconds
    setTimeout(() => {
      successMessage.value = null
    }, 3000)
    
  } catch (err: any) {
    error.value = err.message || 'Failed to start work session'
  } finally {
    isStartingWork.value = false
  }
}

const stopWork = async () => {
  try {
    isStoppingWork.value = true
    error.value = null
    
    // Get current location
    const location = await getCurrentLocation()
    
    // Prepare clock out data
    const clockOutData = {
      latitude: location.lat,
      longitude: location.lng,
      ...(location.address && { address: location.address }),
      description: stopWorkData.value.description,
      billable: stopWorkData.value.billable,
      ...(stopWorkData.value.billable && stopWorkData.value.hourly_rate && { hourly_rate: stopWorkData.value.hourly_rate }),
      photos: stopWorkData.value.photos.map(p => p.file)
    }
    
    // Clock out
    const session = await clockOut(clockOutData)
    
    successMessage.value = 'Work session completed successfully!'
    emit('workStopped', session)
    
    // Reset form
    stopWorkData.value = {
      description: '',
      photos: [],
      billable: false,
      hourly_rate: 0
    }
    
    // Clear success message after 3 seconds
    setTimeout(() => {
      successMessage.value = null
    }, 3000)
    
  } catch (err: any) {
    error.value = err.message || 'Failed to stop work session'
  } finally {
    isStoppingWork.value = false
  }
}

// Utility methods
const formatTime = (timestamp: string | undefined) => {
  if (!timestamp) return 'Unknown'
  return new Date(timestamp).toLocaleString()
}

// Lifecycle
onMounted(async () => {
  try {
    currentLocation.value = await getCurrentLocation()
    await getActiveTimeLog()
  } catch (err) {
    console.warn('Could not get location:', err)
  }
})

onUnmounted(() => {
  // Clean up photo URLs
  startWorkData.value.photos.forEach(photo => {
    URL.revokeObjectURL(photo.preview)
  })
  stopWorkData.value.photos.forEach(photo => {
    URL.revokeObjectURL(photo.preview)
  })
  
  // Stop location tracking
  if (watchId.value !== null) {
    navigator.geolocation.clearWatch(watchId.value)
  }
})
</script>