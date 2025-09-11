<template>
  <div class="task-attachment-gallery">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-900">
        Attachments
        <span v-if="statistics.total_count > 0" class="text-sm text-gray-500 font-normal">
          ({{ statistics.total_count }})
        </span>
      </h3>
      <div class="flex items-center space-x-2">
        <!-- View Toggle -->
        <div class="flex border border-gray-300 rounded-md overflow-hidden">
          <button
            @click="viewMode = 'grid'"
            class="px-3 py-1 text-sm"
            :class="{
              'bg-blue-600 text-white': viewMode === 'grid',
              'bg-white text-gray-700 hover:bg-gray-50': viewMode !== 'grid'
            }"
          >
            <Grid class="w-4 h-4" />
          </button>
          <button
            @click="viewMode = 'list'"
            class="px-3 py-1 text-sm border-l border-gray-300"
            :class="{
              'bg-blue-600 text-white': viewMode === 'list',
              'bg-white text-gray-700 hover:bg-gray-50': viewMode !== 'list'
            }"
          >
            <List class="w-4 h-4" />
          </button>
        </div>
        
        <!-- Bulk Actions -->
        <div v-if="selectedAttachments.size > 0" class="flex items-center space-x-2">
          <span class="text-sm text-gray-600">
            {{ selectedAttachments.size }} selected
          </span>
          <button
            @click="bulkDelete"
            :disabled="isDeleting"
            class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50"
          >
            <Loader2 v-if="isDeleting" class="w-4 h-4 animate-spin inline mr-1" />
            Delete Selected
          </button>
          <button
            @click="selectedAttachments.clear()"
            class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50"
          >
            Clear Selection
          </button>
        </div>
      </div>
    </div>

    <!-- Statistics Bar -->
    <div v-if="statistics.total_count > 0" class="bg-gray-50 rounded-lg p-3 mb-4">
      <div class="flex items-center justify-between text-sm text-gray-600">
        <div class="flex space-x-6">
          <span>{{ statistics.images_count }} Images</span>
          <span>{{ statistics.documents_count }} Documents</span>
          <span>{{ statistics.formatted_total_size }} Total Size</span>
        </div>
        <div v-if="statistics.recent_uploads > 0">
          {{ statistics.recent_uploads }} uploaded this week
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-8">
      <Loader2 class="w-6 h-6 animate-spin text-gray-400" />
      <span class="ml-2 text-gray-600">Loading attachments...</span>
    </div>

    <!-- Empty State -->
    <div v-else-if="attachments.length === 0" class="text-center py-8">
      <Paperclip class="w-12 h-12 mx-auto text-gray-400 mb-3" />
      <h4 class="text-lg font-medium text-gray-900 mb-1">No attachments</h4>
      <p class="text-gray-600">Upload files to get started</p>
    </div>

    <!-- Grid View -->
    <div
      v-else-if="viewMode === 'grid'"
      class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
    >
      <div
        v-for="attachment in attachments"
        :key="attachment.id"
        class="group relative border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-all duration-200"
        :class="{ 'ring-2 ring-blue-500': selectedAttachments.has(attachment.id) }"
      >
        <!-- Selection Checkbox -->
        <div class="absolute top-2 left-2 z-10">
          <input
            type="checkbox"
            :checked="selectedAttachments.has(attachment.id)"
            @change="toggleSelection(attachment.id, $event.target.checked)"
            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
          />
        </div>

        <!-- File Preview -->
        <div class="aspect-square bg-gray-100 flex items-center justify-center">
          <!-- Image Thumbnail -->
          <img
            v-if="attachment.is_image && attachment.thumbnail_url"
            :src="attachment.thumbnail_url"
            :alt="attachment.original_name"
            class="w-full h-full object-cover cursor-pointer"
            @click="openAttachment(attachment)"
            @error="onImageError"
          />
          <!-- File Icon -->
          <component
            v-else
            :is="getFileIcon(attachment)"
            class="w-12 h-12 text-gray-400 cursor-pointer"
            @click="openAttachment(attachment)"
          />
        </div>

        <!-- File Info -->
        <div class="p-3">
          <h5 class="text-sm font-medium text-gray-900 truncate" :title="attachment.original_name">
            {{ attachment.original_name }}
          </h5>
          <div class="text-xs text-gray-500 mt-1 space-y-1">
            <div>{{ attachment.formatted_file_size }}</div>
            <div>{{ formatDate(attachment.created_at) }}</div>
            <div v-if="attachment.uploaded_by">by {{ attachment.uploaded_by.name }}</div>
          </div>
        </div>

        <!-- Actions Overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200">
          <div class="absolute bottom-2 right-2 flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button
              @click="openAttachment(attachment)"
              class="p-1 bg-white bg-opacity-90 hover:bg-opacity-100 rounded text-gray-700"
              :title="attachment.is_image || attachment.mime_type === 'application/pdf' ? 'Preview' : 'Download'"
            >
              <Eye v-if="attachment.is_image || attachment.mime_type === 'application/pdf'" class="w-4 h-4" />
              <Download v-else class="w-4 h-4" />
            </button>
            <button
              v-if="attachment.can_delete"
              @click="deleteAttachment(attachment.id)"
              class="p-1 bg-white bg-opacity-90 hover:bg-opacity-100 rounded text-red-600"
              title="Delete"
            >
              <Trash2 class="w-4 h-4" />
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
        class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
        :class="{ 'bg-blue-50 border-blue-200': selectedAttachments.has(attachment.id) }"
      >
        <div class="flex items-center space-x-3 flex-1 min-w-0">
          <!-- Selection -->
          <input
            type="checkbox"
            :checked="selectedAttachments.has(attachment.id)"
            @change="toggleSelection(attachment.id, $event.target.checked)"
            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
          />
          
          <!-- File Icon -->
          <div class="flex-shrink-0">
            <component
              :is="getFileIcon(attachment)"
              class="w-6 h-6 text-gray-500"
            />
          </div>
          
          <!-- File Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center space-x-2">
              <h5 class="text-sm font-medium text-gray-900 truncate">
                {{ attachment.original_name }}
              </h5>
              <span class="text-xs text-gray-500">{{ attachment.formatted_file_size }}</span>
            </div>
            <div class="text-xs text-gray-500">
              Uploaded {{ formatDate(attachment.created_at) }}
              <span v-if="attachment.uploaded_by"> by {{ attachment.uploaded_by.name }}</span>
            </div>
          </div>
        </div>
        
        <!-- Actions -->
        <div class="flex items-center space-x-2">
          <button
            @click="openAttachment(attachment)"
            class="p-1 text-gray-500 hover:text-blue-600 transition-colors"
            :title="attachment.is_image || attachment.mime_type === 'application/pdf' ? 'Preview' : 'Download'"
          >
            <Eye v-if="attachment.is_image || attachment.mime_type === 'application/pdf'" class="w-4 h-4" />
            <Download v-else class="w-4 h-4" />
          </button>
          <button
            v-if="attachment.can_delete"
            @click="deleteAttachment(attachment.id)"
            class="p-1 text-gray-500 hover:text-red-600 transition-colors"
            title="Delete"
          >
            <Trash2 class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Load More -->
    <div v-if="pagination.current_page < pagination.last_page" class="text-center mt-6">
      <button
        @click="loadMore"
        :disabled="loading"
        class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50"
      >
        <Loader2 v-if="loading" class="w-4 h-4 animate-spin inline mr-2" />
        Load More
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import {
  Grid,
  List,
  Loader2,
  Paperclip,
  Eye,
  Download,
  Trash2,
  FileText,
  Image,
  File,
  Archive,
  AlertCircle
} from 'lucide-vue-next'
import { useTaskAttachments } from '../composables/useTaskAttachments'
import type { TaskAttachment } from '../types/task.types'

interface Props {
  taskId: string
}

interface Emits {
  (e: 'deleted', attachmentId: string): void
  (e: 'bulk-deleted', attachmentIds: string[]): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const {
  attachments,
  statistics,
  pagination,
  loading,
  error,
  loadAttachments,
  removeAttachment,
  removeMultipleAttachments,
  deleteAttachment: deleteAttachmentApi,
  bulkDeleteAttachments,
  openAttachment: openAttachmentApi
} = useTaskAttachments(props.taskId)

// State
const viewMode = ref<'grid' | 'list'>('grid')
const selectedAttachments = ref(new Set<string>())
const isDeleting = ref(false)

// Methods
const toggleSelection = (attachmentId: string, selected: boolean) => {
  if (selected) {
    selectedAttachments.value.add(attachmentId)
  } else {
    selectedAttachments.value.delete(attachmentId)
  }
}

const deleteAttachment = async (attachmentId: string) => {
  if (!confirm('Are you sure you want to delete this attachment?')) return
  
  try {
    await deleteAttachmentApi(attachmentId)
    removeAttachment(attachmentId)
    selectedAttachments.value.delete(attachmentId)
    emit('deleted', attachmentId)
  } catch (error: any) {
    alert(error.message || 'Failed to delete attachment')
  }
}

const bulkDelete = async () => {
  const count = selectedAttachments.value.size
  if (!confirm(`Are you sure you want to delete ${count} attachment${count > 1 ? 's' : ''}?`)) return
  
  isDeleting.value = true
  
  try {
    const attachmentIds = Array.from(selectedAttachments.value)
    await bulkDeleteAttachments(props.taskId, attachmentIds)
    removeMultipleAttachments(attachmentIds)
    selectedAttachments.value.clear()
    emit('bulk-deleted', attachmentIds)
  } catch (error: any) {
    alert(error.message || 'Failed to delete attachments')
  } finally {
    isDeleting.value = false
  }
}

const openAttachment = async (attachment: TaskAttachment) => {
  try {
    await openAttachmentApi(attachment)
  } catch (error: any) {
    alert(error.message || 'Failed to open attachment')
  }
}

const loadMore = async () => {
  // Implementation for pagination would go here
  // For now, this is a placeholder
}

const onImageError = (event: Event) => {
  // Replace broken image with file icon
  const img = event.target as HTMLImageElement
  img.style.display = 'none'
}

const getFileIcon = (attachment: TaskAttachment) => {
  if (attachment.is_image) {
    return Image
  } else if (attachment.is_document) {
    return FileText
  } else if (attachment.mime_type.includes('zip') || attachment.mime_type.includes('rar')) {
    return Archive
  } else {
    return File
  }
}

const formatDate = (dateString: string): string => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60))
  
  if (diffInHours < 1) {
    return 'Just now'
  } else if (diffInHours < 24) {
    return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`
  } else if (diffInHours < 24 * 7) {
    const days = Math.floor(diffInHours / 24)
    return `${days} day${days > 1 ? 's' : ''} ago`
  } else {
    return date.toLocaleDateString()
  }
}

// Initialize
onMounted(async () => {
  await loadAttachments()
})
</script>

<style scoped>
.task-attachment-gallery {
  @apply w-full;
}
</style>