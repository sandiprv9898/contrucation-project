import { ref } from 'vue'
import { apiClient } from '@/modules/shared/api/client'
import type { TaskAttachment, AttachmentConfig } from '../types/task.types'

export function useTaskAttachmentsApi() {
  const loading = ref(false)
  const error = ref<string | null>(null)

  const getTaskAttachments = async (taskId: string): Promise<{
    data: TaskAttachment[]
    meta: {
      current_page: number
      last_page: number
      per_page: number
      total: number
      statistics: {
        total_count: number
        total_size: number
        formatted_total_size: string
        images_count: number
        documents_count: number
        recent_uploads: number
      }
    }
  }> => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiClient.get<{
        data: TaskAttachment[]
        meta: any
      }>(`/tasks/${taskId}/attachments`)
      
      return response
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch attachments'
      throw err
    } finally {
      loading.value = false
    }
  }

  const uploadAttachments = async (taskId: string, formData: FormData): Promise<{
    data: TaskAttachment[]
    message: string
    statistics: any
  }> => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiClient.post<{
        data: TaskAttachment[]
        message: string
        statistics: any
      }>(`/tasks/${taskId}/attachments`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      
      return response
    } catch (err: any) {
      error.value = err.message || 'Upload failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteAttachment = async (attachmentId: string): Promise<{ message: string }> => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiClient.delete<{ message: string }>(`/attachments/${attachmentId}`)
      return response
    } catch (err: any) {
      error.value = err.message || 'Delete failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const bulkDeleteAttachments = async (taskId: string, attachmentIds: string[]): Promise<{
    message: string
    deleted_count: number
    errors: string[]
    statistics: any
  }> => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiClient.delete<{
        message: string
        deleted_count: number
        errors: string[]
        statistics: any
      }>(`/tasks/${taskId}/attachments/bulk`, {
        attachment_ids: attachmentIds
      })
      
      return response
    } catch (err: any) {
      error.value = err.message || 'Bulk delete failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getAttachment = async (attachmentId: string): Promise<{ data: TaskAttachment }> => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiClient.get<{ data: TaskAttachment }>(`/attachments/${attachmentId}`)
      return response
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch attachment'
      throw err
    } finally {
      loading.value = false
    }
  }

  const downloadAttachment = async (attachmentId: string): Promise<Blob> => {
    try {
      const response = await fetch(`/api/v1/attachments/${attachmentId}/download`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        }
      })
      
      if (!response.ok) {
        throw new Error('Download failed')
      }
      
      return response.blob()
    } catch (err: any) {
      error.value = err.message || 'Download failed'
      throw err
    }
  }

  const getPreviewUrl = (attachmentId: string): string => {
    return `${window.location.origin}/api/v1/attachments/${attachmentId}/preview`
  }

  const getThumbnailUrl = (attachmentId: string): string => {
    return `${window.location.origin}/api/v1/attachments/${attachmentId}/thumbnail`
  }

  const getConfig = async (): Promise<AttachmentConfig> => {
    try {
      const response = await apiClient.get<AttachmentConfig>('/attachments/config')
      return response
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch config'
      throw err
    }
  }

  const openAttachment = async (attachment: TaskAttachment) => {
    try {
      if (attachment.is_image || attachment.mime_type === 'application/pdf') {
        // Open preview in new tab
        const url = getPreviewUrl(attachment.id)
        window.open(url, '_blank')
      } else {
        // Download the file
        const blob = await downloadAttachment(attachment.id)
        const url = URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = attachment.original_name
        document.body.appendChild(a)
        a.click()
        document.body.removeChild(a)
        URL.revokeObjectURL(url)
      }
    } catch (err: any) {
      error.value = err.message || 'Failed to open attachment'
      throw err
    }
  }

  return {
    loading,
    error,
    getTaskAttachments,
    uploadAttachments,
    deleteAttachment,
    bulkDeleteAttachments,
    getAttachment,
    downloadAttachment,
    getPreviewUrl,
    getThumbnailUrl,
    getConfig,
    openAttachment
  }
}

export function useTaskAttachments(taskId: string) {
  const attachments = ref<TaskAttachment[]>([])
  const statistics = ref<any>({})
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 20,
    total: 0
  })

  const api = useTaskAttachmentsApi()

  const loadAttachments = async () => {
    try {
      const response = await api.getTaskAttachments(taskId)
      attachments.value = response.data
      statistics.value = response.meta.statistics
      pagination.value = {
        current_page: response.meta.current_page,
        last_page: response.meta.last_page,
        per_page: response.meta.per_page,
        total: response.meta.total
      }
    } catch (error) {
      console.error('Failed to load attachments:', error)
    }
  }

  const addAttachments = (newAttachments: TaskAttachment[]) => {
    attachments.value = [...newAttachments, ...attachments.value]
    // Refresh statistics
    loadAttachments()
  }

  const removeAttachment = (attachmentId: string) => {
    attachments.value = attachments.value.filter(a => a.id !== attachmentId)
    // Refresh statistics
    loadAttachments()
  }

  const removeMultipleAttachments = (attachmentIds: string[]) => {
    attachments.value = attachments.value.filter(a => !attachmentIds.includes(a.id))
    // Refresh statistics
    loadAttachments()
  }

  return {
    attachments,
    statistics,
    pagination,
    loadAttachments,
    addAttachments,
    removeAttachment,
    removeMultipleAttachments,
    ...api
  }
}