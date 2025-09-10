<template>
  <div class="bulk-actions" v-if="selectedUsers.length > 0">
    <VCard class="bg-blue-50 border-blue-200">
      <div class="p-4">
        <div class="flex items-center justify-between">
          <!-- Selection Info -->
          <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2">
              <CheckSquareIcon class="w-5 h-5 text-blue-600" />
              <span class="text-sm font-medium text-blue-900">
                {{ selectedUsers.length }} user{{ selectedUsers.length === 1 ? '' : 's' }} selected
              </span>
            </div>
            
            <VButton
              variant="ghost"
              size="sm"
              @click="clearSelection"
              class="text-blue-600 hover:text-blue-700"
            >
              Clear Selection
            </VButton>
          </div>

          <!-- Bulk Actions -->
          <div class="flex items-center space-x-2">
            <!-- Verify Users -->
            <VButton
              variant="outline"
              size="sm"
              @click="handleBulkAction('verify')"
              :disabled="isProcessing || !canVerifyUsers"
              class="border-green-300 text-green-700 hover:bg-green-50"
            >
              <ShieldCheckIcon class="w-4 h-4 mr-2" />
              Verify Selected
            </VButton>

            <!-- Delete Users -->
            <VButton
              variant="outline"
              size="sm"
              @click="showDeleteConfirmation = true"
              :disabled="isProcessing || !canDeleteUsers"
              class="border-red-300 text-red-700 hover:bg-red-50"
            >
              <TrashIcon class="w-4 h-4 mr-2" />
              Delete Selected
            </VButton>

            <!-- Export Selected -->
            <div class="relative">
              <VButton
                variant="outline"
                size="sm"
                @click="showExportMenu = !showExportMenu"
                :disabled="isProcessing"
                class="border-blue-300 text-blue-700 hover:bg-blue-50"
              >
                <DownloadIcon class="w-4 h-4 mr-2" />
                Export
                <ChevronDownIcon class="w-4 h-4 ml-1" />
              </VButton>

              <!-- Export Menu -->
              <div 
                v-if="showExportMenu"
                v-click-outside="() => showExportMenu = false"
                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-10"
              >
                <div class="py-1">
                  <button
                    @click="handleExport('csv')"
                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    Export as CSV
                  </button>
                  <button
                    @click="handleExport('xlsx')"
                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    Export as Excel
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Processing Indicator -->
        <div v-if="isProcessing" class="mt-3 flex items-center space-x-2">
          <div class="animate-spin rounded-full h-4 w-4 border-2 border-blue-600 border-t-transparent"></div>
          <span class="text-sm text-blue-700">{{ processingMessage }}</span>
        </div>
      </div>
    </VCard>

    <!-- Delete Confirmation Modal -->
    <VModal
      v-model="showDeleteConfirmation"
      title="Confirm Deletion"
      :closable="!isProcessing"
    >
      <div class="space-y-4">
        <div class="flex items-start space-x-3">
          <AlertTriangleIcon class="w-6 h-6 text-red-500 flex-shrink-0 mt-0.5" />
          <div>
            <p class="text-sm text-gray-900">
              Are you sure you want to delete {{ selectedUsers.length }} user{{ selectedUsers.length === 1 ? '' : 's' }}?
            </p>
            <p class="text-sm text-gray-600 mt-1">
              This action cannot be undone. The selected users will be permanently removed from the system.
            </p>
          </div>
        </div>

        <!-- Selected Users List -->
        <div class="max-h-32 overflow-y-auto bg-gray-50 rounded-lg p-3">
          <div class="text-xs font-medium text-gray-700 mb-2">Users to be deleted:</div>
          <div class="space-y-1">
            <div
              v-for="user in selectedUsersData.slice(0, 5)"
              :key="user.id"
              class="text-sm text-gray-600 flex items-center justify-between"
            >
              <span>{{ user.name }}</span>
              <span class="text-xs text-gray-500">{{ user.email }}</span>
            </div>
            <div v-if="selectedUsers.length > 5" class="text-xs text-gray-500 italic">
              ... and {{ selectedUsers.length - 5 }} more
            </div>
          </div>
        </div>

        <!-- Type confirmation -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">
            Type "DELETE" to confirm:
          </label>
          <input
            v-model="deleteConfirmationText"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
            placeholder="Type DELETE"
            :disabled="isProcessing"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end space-x-2">
          <VButton
            variant="outline"
            @click="showDeleteConfirmation = false"
            :disabled="isProcessing"
          >
            Cancel
          </VButton>
          <VButton
            variant="danger"
            @click="handleBulkAction('delete')"
            :disabled="isProcessing || deleteConfirmationText !== 'DELETE'"
          >
            <TrashIcon class="w-4 h-4 mr-2" />
            {{ isProcessing ? 'Deleting...' : 'Delete Users' }}
          </VButton>
        </div>
      </template>
    </VModal>

    <!-- Success/Error Messages -->
    <VAlert
      v-if="lastActionResult"
      :type="lastActionResult.type"
      :title="lastActionResult.title"
      :message="lastActionResult.message"
      :dismissible="true"
      @dismiss="lastActionResult = null"
      class="mt-4"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { VCard, VButton, VModal, VAlert } from '@/components/ui'
import {
  CheckSquareIcon,
  ShieldCheckIcon,
  TrashIcon,
  DownloadIcon,
  ChevronDownIcon,
  AlertTriangleIcon
} from 'lucide-vue-next'
import { UsersApi } from '@/modules/users/api/users.api'
import type { 
  UserListItem, 
  BulkActionRequest, 
  BulkActionResponse, 
  ExportResponse,
  UserFilters 
} from '@/modules/users/types/users.types'

const props = defineProps<{
  selectedUsers: string[]
  selectedUsersData: UserListItem[]
  canVerifyUsers?: boolean
  canDeleteUsers?: boolean
  filters?: UserFilters
}>()

const emit = defineEmits<{
  'clear-selection': []
  'bulk-action-completed': [result: BulkActionResponse]
  'export-completed': [result: ExportResponse]
}>()

// Component state
const isProcessing = ref(false)
const processingMessage = ref('')
const showDeleteConfirmation = ref(false)
const showExportMenu = ref(false)
const deleteConfirmationText = ref('')
const lastActionResult = ref<{
  type: 'success' | 'error'
  title: string
  message: string
} | null>(null)

// Computed properties
const canVerifyUsers = computed(() => props.canVerifyUsers ?? true)
const canDeleteUsers = computed(() => props.canDeleteUsers ?? true)

// Methods
const clearSelection = () => {
  emit('clear-selection')
}

const handleBulkAction = async (action: 'delete' | 'verify') => {
  if (props.selectedUsers.length === 0) return
  
  try {
    isProcessing.value = true
    
    if (action === 'delete') {
      processingMessage.value = 'Deleting users...'
    } else {
      processingMessage.value = 'Verifying users...'
    }

    const request: BulkActionRequest = {
      action,
      user_ids: props.selectedUsers
    }

    const response = await UsersApi.bulkAction(request)
    
    // Show success message
    lastActionResult.value = {
      type: 'success',
      title: 'Bulk Action Completed',
      message: response.message || `Successfully ${action === 'delete' ? 'deleted' : 'verified'} ${response.affected_count} user(s)`
    }

    // Emit result
    emit('bulk-action-completed', response)

    // Clear selection
    emit('clear-selection')

    // Close delete confirmation if open
    if (showDeleteConfirmation.value) {
      showDeleteConfirmation.value = false
      deleteConfirmationText.value = ''
    }

  } catch (error) {
    console.error('Bulk action failed:', error)
    
    lastActionResult.value = {
      type: 'error',
      title: 'Bulk Action Failed',
      message: error instanceof Error ? error.message : `Failed to ${action} users. Please try again.`
    }
  } finally {
    isProcessing.value = false
    processingMessage.value = ''
  }
}

const handleExport = async (format: 'csv' | 'xlsx') => {
  try {
    isProcessing.value = true
    processingMessage.value = `Exporting ${format.toUpperCase()}...`
    showExportMenu.value = false

    // Create filters for selected users only
    const exportFilters: UserFilters = {
      ...props.filters,
      // Note: In a real implementation, you might want to add a specific filter for selected user IDs
      // For now, we'll export with current filters and note the selection in the response
    }

    const response = await UsersApi.exportUsers(format, exportFilters)
    
    // Handle download
    if (response.download_url) {
      if (response.download_url.startsWith('data:')) {
        // Handle data URL
        const link = document.createElement('a')
        link.href = response.download_url
        link.download = `users_export.${format}`
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
      } else {
        // Handle regular URL
        window.open(response.download_url, '_blank')
      }
    }

    // Show success message
    lastActionResult.value = {
      type: 'success',
      title: 'Export Completed',
      message: `Successfully exported ${response.total_exported} user(s) as ${format.toUpperCase()}`
    }

    // Emit result
    emit('export-completed', response)

  } catch (error) {
    console.error('Export failed:', error)
    
    lastActionResult.value = {
      type: 'error',
      title: 'Export Failed',
      message: error instanceof Error ? error.message : 'Failed to export users. Please try again.'
    }
  } finally {
    isProcessing.value = false
    processingMessage.value = ''
  }
}

// Click outside directive (simplified version)
const vClickOutside = {
  mounted(el: HTMLElement, binding: any) {
    el._clickOutside = (event: Event) => {
      if (!(el === event.target || el.contains(event.target as Node))) {
        binding.value()
      }
    }
    document.addEventListener('click', el._clickOutside)
  },
  unmounted(el: HTMLElement) {
    document.removeEventListener('click', el._clickOutside)
    delete el._clickOutside
  }
}

// Watchers
watch(() => props.selectedUsers.length, (newLength) => {
  if (newLength === 0) {
    showDeleteConfirmation.value = false
    showExportMenu.value = false
    deleteConfirmationText.value = ''
    lastActionResult.value = null
  }
})
</script>

<style scoped>
.bulk-actions {
  @apply transition-all duration-200 ease-in-out;
}
</style>