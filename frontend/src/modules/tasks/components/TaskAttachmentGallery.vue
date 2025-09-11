<template>
  <div class="task-attachment-gallery">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-8">
      <Loader2 class="h-6 w-6 animate-spin text-gray-500" />
      <span class="ml-2 text-gray-600">Loading attachments...</span>
    </div>

    <!-- Empty State -->
    <div v-else-if="attachments.length === 0" class="text-center py-8">
      <Paperclip class="h-12 w-12 text-gray-400 mx-auto mb-3" />
      <p class="text-gray-600">No attachments yet</p>
      <p class="text-sm text-gray-500">Files uploaded to this task will appear here</p>
    </div>

    <!-- Attachments Grid -->
    <div v-else class="space-y-4">
      <div class="flex items-center justify-between">
        <h4 class="text-sm font-medium text-gray-900">
          Attachments ({{ attachments.length }})
        </h4>
        <div class="flex items-center gap-2">
          <button
            v-if="selectedAttachments.length > 0"
            @click="bulkDelete"
            :disabled="deleting"
            class="text-sm text-red-600 hover:text-red-800 disabled:opacity-50"
          >
            Delete Selected ({{ selectedAttachments.length }})
          </button>
          <div class="flex items-center gap-1">
            <button
              @click="viewMode = 'grid'"
              :class="[
                'p-2 rounded-md',
                viewMode === 'grid' ? 'bg-blue-100 text-blue-600' : 'text-gray-400 hover:text-gray-600'
              ]"
            >
              <Grid3X3 class="h-4 w-4" />
            </button>
            <button
              @click="viewMode = 'list'"
              :class="[
                'p-2 rounded-md',
                viewMode === 'list' ? 'bg-blue-100 text-blue-600' : 'text-gray-400 hover:text-gray-600'
              ]"
            >
              <List class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Grid View -->
      <div v-if="viewMode === 'grid'" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div
          v-for="attachment in attachments"
          :key="attachment.id"
          class="relative group bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow"
        >
          <!-- Selection Checkbox -->
          <div class="absolute top-2 left-2 z-10">
            <input
              type="checkbox"
              :value="attachment.id"
              v-model="selectedAttachments"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
          </div>

          <!-- Thumbnail/Preview -->
          <div class="aspect-square bg-gray-50 flex items-center justify-center">
            <img
              v-if="attachment.is_image && attachment.thumbnail_url"
              :src="attachment.thumbnail_url"
              :alt="attachment.original_name"
              class="max-w-full max-h-full object-cover"
              @click="openAttachment(attachment)"
              @error="handleImageError"
            />
            <div v-else class="text-center p-4">
              <component
                :is="getFileIcon(attachment)"
                class="h-8 w-8 text-gray-400 mx-auto mb-2"
              />
              <p class="text-xs text-gray-600 truncate">{{ attachment.file_extension?.toUpperCase() }}</p>
            </div>
          </div>

          <!-- File Info -->
          <div class="p-3">
            <p class="text-sm font-medium text-gray-900 truncate" :title="attachment.original_name">
              {{ attachment.original_name }}
            </p>
            <p class="text-xs text-gray-500">{{ attachment.formatted_file_size }}</p>
          </div>

          <!-- Actions Overlay -->
          <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100">
            <div class="flex gap-2">
              <button
                @click="openAttachment(attachment)"
                class="p-2 bg-white bg-opacity-90 rounded-full text-gray-700 hover:bg-opacity-100 transition-colors"
                :title="attachment.is_image ? 'Preview' : 'Download'"
              >
                <Eye v-if="attachment.is_image" class="h-4 w-4" />
                <Download v-else class="h-4 w-4" />
              </button>
              <button
                v-if="attachment.can_delete"
                @click="deleteAttachment(attachment.id)"
                class="p-2 bg-white bg-opacity-90 rounded-full text-red-600 hover:bg-opacity-100 transition-colors"
                title="Delete"
              >
                <Trash2 class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- List View -->
      <div v-else class="space-y-2">
        <div
          v-for="attachment in attachments"
          :key="attachment.id"
          class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-lg hover:shadow-sm transition-shadow"
        >
          <input
            type="checkbox"
            :value="attachment.id"
            v-model="selectedAttachments"
            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
          />
          
          <div class="flex-shrink-0">
            <component
              :is="getFileIcon(attachment)"
              class="h-6 w-6 text-gray-400"
            />
          </div>
          
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">
              {{ attachment.original_name }}
            </p>
            <div class="flex items-center gap-2 text-xs text-gray-500">
              <span>{{ attachment.formatted_file_size }}</span>
              <span>•</span>
              <span>{{ attachment.file_extension?.toUpperCase() }}</span>
              <span v-if="attachment.uploaded_by">•</span>
              <span v-if="attachment.uploaded_by">{{ attachment.uploaded_by.name }}</span>
            </div>
          </div>
          
          <div class="flex items-center gap-2">
            <button
              @click="openAttachment(attachment)"
              class="p-2 text-gray-400 hover:text-gray-600 transition-colors"
              :title="attachment.is_image ? 'Preview' : 'Download'"
            >
              <Eye v-if="attachment.is_image" class="h-4 w-4" />
              <Download v-else class="h-4 w-4" />
            </button>
            <button
              v-if="attachment.can_delete"
              @click="deleteAttachment(attachment.id)"
              class="p-2 text-red-400 hover:text-red-600 transition-colors"
              title="Delete"
            >
              <Trash2 class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import {
  Loader2,
  Paperclip,
  Grid3X3,
  List,
  Eye,
  Download,
  Trash2,
  FileText,
  Image,
  File,
  Archive,
  Music,
  Video
} from 'lucide-vue-next'
import { useTaskAttachments } from '../composables/useTaskAttachments'
import type { TaskAttachment } from '../types/task.types'

interface Props {
  taskId: string
  refreshTrigger?: number // Used to trigger refresh from parent
}

interface Emits {
  (e: 'deleted', attachmentId: string): void
  (e: 'bulkDeleted', attachmentIds: string[]): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const {
  attachments,
  loading,
  error,
  fetchAttachments,
  deleteAttachment: deleteAttachmentApi,
  bulkDeleteAttachments,
  openAttachment
} = useTaskAttachments()

// State
const viewMode = ref<'grid' | 'list'>('grid')
const selectedAttachments = ref<string[]>([])
const deleting = ref(false)

// Watch for refresh trigger changes
watch(() => props.refreshTrigger, () => {
  if (props.refreshTrigger) {
    loadAttachments()
  }
})

// Load attachments on mount
onMounted(() => {
  loadAttachments()
})

const loadAttachments = async () => {
  try {
    await fetchAttachments(props.taskId)
  } catch (err) {
    console.error('Failed to load attachments:', err)
  }
}

const deleteAttachment = async (attachmentId: string) => {
  if (!confirm('Are you sure you want to delete this attachment?')) {
    return
  }

  try {
    deleting.value = true
    await deleteAttachmentApi(attachmentId)
    emit('deleted', attachmentId)
    
    // Remove from selection if it was selected
    selectedAttachments.value = selectedAttachments.value.filter(id => id !== attachmentId)
    
    // Refresh the list
    await loadAttachments()
  } catch (err) {
    console.error('Failed to delete attachment:', err)
    alert('Failed to delete attachment. Please try again.')
  } finally {
    deleting.value = false
  }
}

const bulkDelete = async () => {
  if (selectedAttachments.value.length === 0) return
  
  const count = selectedAttachments.value.length
  if (!confirm(`Are you sure you want to delete ${count} attachment${count > 1 ? 's' : ''}?`)) {
    return
  }

  try {
    deleting.value = true
    await bulkDeleteAttachments(selectedAttachments.value)
    
    emit('bulkDeleted', [...selectedAttachments.value])
    selectedAttachments.value = []
    
    // Refresh the list
    await loadAttachments()
  } catch (err) {
    console.error('Failed to bulk delete attachments:', err)
    alert('Failed to delete attachments. Please try again.')
  } finally {
    deleting.value = false
  }
}

const getFileIcon = (attachment: TaskAttachment) => {
  const mimeType = attachment.mime_type?.toLowerCase() || ''
  
  if (attachment.is_image || mimeType.startsWith('image/')) {
    return Image
  } else if (mimeType.includes('pdf') || attachment.is_document || mimeType.includes('document') || mimeType.includes('text')) {
    return FileText
  } else if (mimeType.startsWith('audio/')) {
    return Music
  } else if (mimeType.startsWith('video/')) {
    return Video
  } else if (mimeType.includes('zip') || mimeType.includes('rar') || mimeType.includes('archive')) {
    return Archive
  } else {
    return File
  }
}

const handleImageError = (event: Event) => {
  const img = event.target as HTMLImageElement
  img.style.display = 'none'
  
  // Show file icon instead
  const container = img.parentElement
  if (container) {
    container.innerHTML = `
      <div class="text-center p-4">
        <svg class="h-8 w-8 text-gray-400 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
        </svg>
        <p class="text-xs text-gray-600">Preview unavailable</p>
      </div>
    `
  }
}
</script>

<style scoped>
.task-attachment-gallery {
  @apply w-full;
}
</style>