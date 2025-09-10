<template>
  <div class="space-y-3">
    <!-- Current Logo Preview -->
    <div
      v-if="currentAsset || previewUrl"
      class="relative w-fit"
    >
      <div class="h-24 w-24 rounded-lg border border-border flex items-center justify-center overflow-hidden bg-muted">
        <img
          :src="previewUrl || currentAsset"
          :alt="`${assetType} ${variant}`"
          class="h-full w-full object-contain"
        />
      </div>
      
      <!-- Remove Button -->
      <VButton
        v-if="canWrite && (currentAsset || previewUrl)"
        @click="handleRemove"
        variant="outline"
        size="sm"
        class="absolute -top-2 -right-2 h-6 w-6 p-0 rounded-full"
      >
        <X class="h-3 w-3" />
      </VButton>
    </div>

    <!-- Upload Area -->
    <div
      class="border-2 border-dashed border-border rounded-lg p-6 text-center hover:border-primary/50 transition-colors cursor-pointer"
      :class="{
        'border-primary bg-primary/5': dragOver,
        'cursor-not-allowed opacity-50': !canWrite
      }"
      @click="triggerFileUpload"
      @drop.prevent="handleDrop"
      @dragover.prevent="dragOver = true"
      @dragleave.prevent="dragOver = false"
    >
      <div class="flex flex-col items-center gap-2">
        <Upload class="h-8 w-8 text-muted-foreground" />
        <div>
          <p class="text-sm font-medium">
            {{ currentAsset || previewUrl ? `Replace ${assetType}` : `Upload ${assetType}` }}
          </p>
          <p class="text-xs text-muted-foreground">
            {{ allowedFormats }} up to {{ maxSize }}MB
          </p>
          <p class="text-xs text-muted-foreground">
            Recommended: {{ recommendedSize }}
          </p>
        </div>
      </div>
    </div>

    <!-- Hidden File Input -->
    <input
      ref="fileInputRef"
      type="file"
      :accept="acceptedMimeTypes"
      @change="handleFileSelect"
      class="hidden"
      :disabled="!canWrite"
    />

    <!-- Upload Progress -->
    <div v-if="uploading" class="space-y-2">
      <div class="flex items-center justify-between text-sm">
        <span>Uploading {{ assetType }}...</span>
        <span>{{ uploadProgress }}%</span>
      </div>
      <div class="w-full bg-muted rounded-full h-2">
        <div
          class="bg-primary h-2 rounded-full transition-all duration-300"
          :style="{ width: `${uploadProgress}%` }"
        ></div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="text-sm text-destructive">
      {{ error }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { VButton } from '@/components/ui'
import { Upload, X } from 'lucide-vue-next'

// Props
interface Props {
  modelValue?: string | null
  assetType: string
  variant: string
  canWrite: boolean
  maxSize: number // in MB
  recommendedSize: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  maxSize: 5,
  recommendedSize: '200x200'
})

// Emits
const emit = defineEmits<{
  'update:modelValue': [value: string | null]
  upload: [assetType: string, variant: string, file: File]
  remove: [assetType: string, variant: string]
}>()

// State
const fileInputRef = ref<HTMLInputElement>()
const dragOver = ref(false)
const uploading = ref(false)
const uploadProgress = ref(0)
const error = ref('')
const previewUrl = ref<string | null>(null)

// Computed
const currentAsset = computed(() => props.modelValue)

const allowedFormats = computed(() => {
  if (props.assetType === 'favicon') {
    return 'PNG, ICO'
  }
  return 'PNG, JPG, SVG'
})

const acceptedMimeTypes = computed(() => {
  if (props.assetType === 'favicon') {
    return 'image/png,image/x-icon'
  }
  return 'image/jpeg,image/png,image/svg+xml'
})

// Methods
function triggerFileUpload() {
  if (!props.canWrite) return
  fileInputRef.value?.click()
}

function handleFileSelect(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  
  if (file) {
    processFile(file)
  }
}

function handleDrop(event: DragEvent) {
  if (!props.canWrite) return
  
  dragOver.value = false
  const file = event.dataTransfer?.files[0]
  
  if (file) {
    processFile(file)
  }
}

function processFile(file: File) {
  error.value = ''
  
  // Validate file
  if (!validateFile(file)) {
    return
  }
  
  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    previewUrl.value = e.target?.result as string
  }
  reader.readAsDataURL(file)
  
  // Start upload
  uploadFile(file)
}

function validateFile(file: File): boolean {
  // Check file type
  const allowedTypes = acceptedMimeTypes.value.split(',')
  if (!allowedTypes.includes(file.type)) {
    error.value = `Invalid file type. Allowed: ${allowedFormats.value}`
    return false
  }
  
  // Check file size
  const maxSizeBytes = props.maxSize * 1024 * 1024
  if (file.size > maxSizeBytes) {
    error.value = `File too large. Maximum size: ${props.maxSize}MB`
    return false
  }
  
  // For favicon, check dimensions
  if (props.assetType === 'favicon') {
    return validateImageDimensions(file)
  }
  
  return true
}

function validateImageDimensions(file: File): Promise<boolean> {
  return new Promise((resolve) => {
    const img = new Image()
    img.onload = () => {
      if (props.assetType === 'favicon') {
        if (img.width !== 32 || img.height !== 32) {
          error.value = 'Favicon must be exactly 32x32 pixels'
          resolve(false)
          return
        }
      }
      resolve(true)
    }
    img.onerror = () => {
      error.value = 'Invalid image file'
      resolve(false)
    }
    img.src = URL.createObjectURL(file)
  })
}

async function uploadFile(file: File) {
  uploading.value = true
  uploadProgress.value = 0
  
  try {
    // Simulate upload progress
    const progressInterval = setInterval(() => {
      uploadProgress.value += 10
      if (uploadProgress.value >= 90) {
        clearInterval(progressInterval)
      }
    }, 200)
    
    // Emit upload event
    emit('upload', props.assetType, props.variant, file)
    
    // Simulate upload completion
    setTimeout(() => {
      uploadProgress.value = 100
      uploading.value = false
      emit('update:modelValue', previewUrl.value)
      clearInterval(progressInterval)
    }, 2000)
    
  } catch (err: any) {
    error.value = err.message || 'Upload failed'
    uploading.value = false
    uploadProgress.value = 0
    previewUrl.value = null
  }
}

function handleRemove() {
  emit('remove', props.assetType, props.variant)
  emit('update:modelValue', null)
  previewUrl.value = null
  
  // Clear file input
  if (fileInputRef.value) {
    fileInputRef.value.value = ''
  }
}
</script>