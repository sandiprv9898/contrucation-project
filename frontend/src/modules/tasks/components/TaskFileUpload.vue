<template>
  <div class="task-file-upload">
    <!-- Upload Zone -->
    <div
      ref="dropZone"
      class="border-2 border-dashed rounded-lg p-6 text-center transition-colors"
      :class="{
        'border-blue-400 bg-blue-50': isDragging,
        'border-gray-300 hover:border-gray-400': !isDragging
      }"
      @dragover.prevent="handleDragOver"
      @dragleave.prevent="handleDragLeave"
      @drop.prevent="handleDrop"
      @click="triggerFileInput"
    >
      <input
        ref="fileInput"
        type="file"
        multiple
        accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp,.txt,.csv,.zip,.rar"
        class="hidden"
        @change="handleFileSelect"
      />
      
      <!-- Upload Icon and Text -->
      <div class="space-y-2">
        <Upload class="w-12 h-12 mx-auto text-gray-400" />
        <div class="text-lg font-medium text-gray-900">
          {{ isDragging ? 'Drop files here' : 'Drop files here or click to browse' }}
        </div>
        <div class="text-sm text-gray-600">
          Maximum {{ config.max_files_per_upload }} files, {{ config.max_file_size_formatted }} each
        </div>
        <div class="text-xs text-gray-500">
          Supported: PDF, DOC, XLS, images, text files, archives
        </div>
      </div>
    </div>

    <!-- Selected Files Preview -->
    <div v-if="selectedFiles.length > 0" class="mt-4 space-y-2">
      <h4 class="text-sm font-medium text-gray-900">Selected Files ({{ selectedFiles.length }})</h4>
      <div class="space-y-2 max-h-60 overflow-y-auto">
        <div
          v-for="(file, index) in selectedFiles"
          :key="`selected-${index}`"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
        >
          <div class="flex items-center space-x-3 flex-1 min-w-0">
            <div class="flex-shrink-0">
              <component
                :is="getFileIcon(file)"
                class="w-6 h-6 text-gray-500"
              />
            </div>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-medium text-gray-900 truncate">
                {{ file.name }}
              </div>
              <div class="text-xs text-gray-500">
                {{ formatFileSize(file.size) }}
                <span v-if="file.type" class="ml-2">{{ file.type }}</span>
              </div>
            </div>
          </div>
          <button
            @click="removeFile(index)"
            class="flex-shrink-0 p-1 text-red-500 hover:text-red-700 transition-colors"
            :title="`Remove ${file.name}`"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
      </div>
      
      <!-- Upload Actions -->
      <div class="flex items-center justify-between pt-3 border-t">
        <button
          @click="clearFiles"
          class="text-sm text-gray-600 hover:text-gray-800"
        >
          Clear All
        </button>
        <div class="flex space-x-2">
          <button
            @click="selectedFiles = []"
            class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="uploadFiles"
            :disabled="isUploading || selectedFiles.length === 0"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
          >
            <Loader2 v-if="isUploading" class="w-4 h-4 animate-spin" />
            <Upload v-else class="w-4 h-4" />
            <span>{{ isUploading ? 'Uploading...' : 'Upload Files' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Upload Progress -->
    <div v-if="isUploading" class="mt-4">
      <div class="bg-gray-200 rounded-full h-2">
        <div
          class="bg-blue-600 h-2 rounded-full transition-all duration-300"
          :style="{ width: `${uploadProgress}%` }"
        ></div>
      </div>
      <div class="text-sm text-gray-600 mt-1">
        Uploading {{ uploadingFile }} ({{ uploadProgress }}%)
      </div>
    </div>

    <!-- Upload Results -->
    <div v-if="uploadResults.length > 0" class="mt-4">
      <h4 class="text-sm font-medium text-gray-900 mb-2">Upload Results</h4>
      <div class="space-y-1">
        <div
          v-for="result in uploadResults"
          :key="result.filename"
          class="flex items-center space-x-2 text-sm"
        >
          <CheckCircle v-if="result.success" class="w-4 h-4 text-green-500" />
          <AlertCircle v-else class="w-4 h-4 text-red-500" />
          <span class="flex-1">{{ result.filename }}</span>
          <span v-if="!result.success" class="text-red-600 text-xs">{{ result.error }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { 
  Upload, 
  X, 
  Loader2, 
  CheckCircle, 
  AlertCircle,
  FileText,
  Image,
  File,
  Archive
} from 'lucide-vue-next'
import { useTaskAttachmentsApi } from '../composables/useTaskAttachments'

interface Props {
  taskId: string
}

interface Emits {
  (e: 'uploaded', attachments: any[]): void
  (e: 'error', error: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const { uploadAttachments, getConfig } = useTaskAttachmentsApi()

// State
const dropZone = ref<HTMLElement>()
const fileInput = ref<HTMLInputElement>()
const selectedFiles = ref<File[]>([])
const isDragging = ref(false)
const isUploading = ref(false)
const uploadProgress = ref(0)
const uploadingFile = ref('')
const uploadResults = ref<Array<{ filename: string; success: boolean; error?: string }>>([])
const config = ref({
  max_file_size: 10485760,
  max_file_size_formatted: '10 MB',
  max_files_per_upload: 10,
  allowed_mime_types: []
})

// Drag and Drop Handlers
const handleDragOver = (e: DragEvent) => {
  e.preventDefault()
  isDragging.value = true
}

const handleDragLeave = (e: DragEvent) => {
  e.preventDefault()
  // Only set to false if we're leaving the drop zone itself
  if (!dropZone.value?.contains(e.relatedTarget as Node)) {
    isDragging.value = false
  }
}

const handleDrop = (e: DragEvent) => {
  e.preventDefault()
  isDragging.value = false
  
  const files = Array.from(e.dataTransfer?.files || [])
  addFiles(files)
}

// File Selection Handlers
const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileSelect = (e: Event) => {
  const files = Array.from((e.target as HTMLInputElement).files || [])
  addFiles(files)
}

// File Management
const addFiles = (files: File[]) => {
  const validFiles: File[] = []
  const errors: string[] = []

  files.forEach(file => {
    // Check file size
    if (file.size > config.value.max_file_size) {
      errors.push(`${file.name}: File too large (max ${config.value.max_file_size_formatted})`)
      return
    }

    // Check file type
    const allowedTypes = config.value.allowed_mime_types
    if (allowedTypes.length > 0 && !allowedTypes.includes(file.type)) {
      errors.push(`${file.name}: File type not allowed`)
      return
    }

    // Check if already selected
    const alreadySelected = selectedFiles.value.some(f => 
      f.name === file.name && f.size === file.size && f.lastModified === file.lastModified
    )
    if (alreadySelected) {
      errors.push(`${file.name}: Already selected`)
      return
    }

    validFiles.push(file)
  })

  // Check total file count
  const totalFiles = selectedFiles.value.length + validFiles.length
  if (totalFiles > config.value.max_files_per_upload) {
    const allowedCount = config.value.max_files_per_upload - selectedFiles.value.length
    errors.push(`Too many files. Maximum ${config.value.max_files_per_upload} files allowed. You can add ${allowedCount} more.`)
    validFiles.splice(allowedCount)
  }

  selectedFiles.value = [...selectedFiles.value, ...validFiles]

  if (errors.length > 0) {
    emit('error', errors.join('\n'))
  }
}

const removeFile = (index: number) => {
  selectedFiles.value.splice(index, 1)
}

const clearFiles = () => {
  selectedFiles.value = []
  uploadResults.value = []
}

// Upload Logic
const uploadFiles = async () => {
  if (selectedFiles.value.length === 0) return

  isUploading.value = true
  uploadProgress.value = 0
  uploadResults.value = []

  try {
    const formData = new FormData()
    selectedFiles.value.forEach(file => {
      formData.append('files[]', file)
    })

    // Simulate progress updates
    const progressInterval = setInterval(() => {
      if (uploadProgress.value < 90) {
        uploadProgress.value += Math.random() * 15
      }
    }, 200)

    const response = await uploadAttachments(props.taskId, formData)
    
    clearInterval(progressInterval)
    uploadProgress.value = 100

    // Process results
    if (response.data) {
      uploadResults.value = selectedFiles.value.map(file => ({
        filename: file.name,
        success: true
      }))
      
      emit('uploaded', response.data)
      selectedFiles.value = []
    }

  } catch (error: any) {
    uploadResults.value = selectedFiles.value.map(file => ({
      filename: file.name,
      success: false,
      error: error.message || 'Upload failed'
    }))
    
    emit('error', error.message || 'Upload failed')
  } finally {
    isUploading.value = false
    setTimeout(() => {
      uploadProgress.value = 0
      uploadingFile.value = ''
    }, 2000)
  }
}

// Utility Functions
const formatFileSize = (bytes: number): string => {
  const units = ['B', 'KB', 'MB', 'GB']
  let size = bytes
  let unitIndex = 0
  
  while (size >= 1024 && unitIndex < units.length - 1) {
    size /= 1024
    unitIndex++
  }
  
  return `${Math.round(size * 100) / 100} ${units[unitIndex]}`
}

const getFileIcon = (file: File) => {
  if (file.type.startsWith('image/')) {
    return Image
  } else if (file.type === 'application/pdf' || file.type.includes('document') || file.type.includes('text')) {
    return FileText
  } else if (file.type.includes('zip') || file.type.includes('rar')) {
    return Archive
  } else {
    return File
  }
}

// Initialize
onMounted(async () => {
  try {
    const configData = await getConfig()
    config.value = configData
  } catch (error) {
    console.warn('Failed to load upload config:', error)
  }
})
</script>

<style scoped>
.task-file-upload {
  @apply w-full;
}

.border-dashed {
  border-style: dashed;
}
</style>