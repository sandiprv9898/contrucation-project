<template>
  <div class="space-y-6">
    <!-- Portfolio Items Management -->
    <VCard>
      <template #header>
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold">Company Portfolio</h3>
            <p class="text-sm text-muted-foreground">Showcase your projects, achievements, and company highlights</p>
          </div>
          <VButton
            @click="openAddModal"
            :disabled="!canWrite"
          >
            <Plus class="mr-2 h-4 w-4" />
            Add Item
          </VButton>
        </div>
      </template>
      
      <template #content>
        <!-- Category Filter -->
        <div class="mb-6">
          <VLabel>Filter by Category</VLabel>
          <div class="flex flex-wrap gap-2 mt-2">
            <VButton
              v-for="category in portfolioCategories"
              :key="category.value"
              @click="selectedCategory = category.value"
              :variant="selectedCategory === category.value ? 'default' : 'outline'"
              size="sm"
            >
              {{ category.label }}
            </VButton>
            <VButton
              @click="selectedCategory = 'all'"
              :variant="selectedCategory === 'all' ? 'default' : 'outline'"
              size="sm"
            >
              All
            </VButton>
          </div>
        </div>

        <!-- Portfolio Grid -->
        <div v-if="filteredPortfolioItems.length > 0" class="space-y-4">
          <div
            v-for="item in filteredPortfolioItems"
            :key="item.id"
            class="p-4 border border-border rounded-lg"
          >
            <div class="flex items-start gap-4">
              <!-- Item Image -->
              <div class="h-20 w-20 rounded-lg border border-border flex items-center justify-center overflow-hidden bg-muted flex-shrink-0">
                <img
                  v-if="item.image_url"
                  :src="item.image_url"
                  :alt="item.title"
                  class="h-full w-full object-cover"
                />
                <Briefcase v-else class="h-8 w-8 text-muted-foreground" />
              </div>

              <!-- Item Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between">
                  <div>
                    <h4 class="font-medium">{{ item.title }}</h4>
                    <p class="text-sm text-muted-foreground">{{ item.description }}</p>
                    <div class="flex items-center gap-2 mt-2">
                      <VBadge variant="secondary" class="text-xs">
                        {{ getCategoryLabel(item.category) }}
                      </VBadge>
                      <VBadge v-if="item.is_featured" variant="default" class="text-xs">
                        Featured
                      </VBadge>
                    </div>
                  </div>
                  
                  <!-- Actions -->
                  <div class="flex items-center gap-2">
                    <VButton
                      @click="editItem(item)"
                      variant="ghost"
                      size="sm"
                      :disabled="!canWrite"
                    >
                      <Edit class="h-4 w-4" />
                    </VButton>
                    <VButton
                      @click="deleteItem(item)"
                      variant="ghost"
                      size="sm"
                      :disabled="!canWrite"
                    >
                      <Trash2 class="h-4 w-4" />
                    </VButton>
                    <VButton
                      @click="toggleFeatured(item)"
                      variant="ghost"
                      size="sm"
                      :disabled="!canWrite"
                    >
                      <Star :class="item.is_featured ? 'h-4 w-4 fill-current' : 'h-4 w-4'" />
                    </VButton>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <Briefcase class="mx-auto h-12 w-12 text-muted-foreground" />
          <h3 class="mt-4 text-lg font-medium">No portfolio items</h3>
          <p class="mt-2 text-sm text-muted-foreground">
            Get started by adding your first portfolio item to showcase your work.
          </p>
          <VButton
            @click="openAddModal"
            class="mt-4"
            :disabled="!canWrite"
          >
            <Plus class="mr-2 h-4 w-4" />
            Add Portfolio Item
          </VButton>
        </div>
      </template>
    </VCard>

    <!-- Add/Edit Modal -->
    <VModal
      v-model:open="showModal"
      :title="editingItem ? 'Edit Portfolio Item' : 'Add Portfolio Item'"
    >
      <div class="space-y-4">
        <!-- Title -->
        <div>
          <VLabel for="item-title" required>Title</VLabel>
          <VInput
            id="item-title"
            v-model="formData.title"
            placeholder="Enter item title"
            class="mt-1"
          />
        </div>

        <!-- Description -->
        <div>
          <VLabel for="item-description">Description</VLabel>
          <VTextarea
            id="item-description"
            v-model="formData.description"
            placeholder="Describe this portfolio item..."
            rows="3"
            class="mt-1"
          />
        </div>

        <!-- Category -->
        <div>
          <VLabel for="item-category" required>Category</VLabel>
          <VSelect
            id="item-category"
            v-model="formData.category"
            :options="portfolioCategories"
            placeholder="Select category"
            class="mt-1"
          />
        </div>

        <!-- External URL -->
        <div>
          <VLabel for="item-url">External URL</VLabel>
          <VInput
            id="item-url"
            v-model="formData.external_url"
            placeholder="https://example.com"
            type="url"
            class="mt-1"
          />
        </div>

        <!-- Image Upload -->
        <div>
          <VLabel>Image</VLabel>
          <div class="mt-2">
            <div
              v-if="formData.image_preview"
              class="h-40 w-40 rounded-lg border border-border overflow-hidden mb-3"
            >
              <img
                :src="formData.image_preview"
                alt="Preview"
                class="h-full w-full object-cover"
              />
            </div>
            <VButton
              @click="triggerImageUpload"
              variant="outline"
              size="sm"
            >
              <Upload class="mr-2 h-4 w-4" />
              {{ formData.image_preview ? 'Change Image' : 'Upload Image' }}
            </VButton>
            <input
              ref="imageInputRef"
              type="file"
              accept="image/*"
              @change="handleImageUpload"
              class="hidden"
            />
            <p class="text-xs text-muted-foreground mt-1">
              PNG, JPG up to 10MB. Recommended: 800x600px
            </p>
          </div>
        </div>

        <!-- Featured Toggle -->
        <div class="flex items-center space-x-2">
          <VCheckbox
            id="item-featured"
            v-model="formData.is_featured"
          />
          <VLabel for="item-featured">Featured Item</VLabel>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2">
          <VButton
            @click="showModal = false"
            variant="outline"
          >
            Cancel
          </VButton>
          <VButton
            @click="saveItem"
            :loading="saving"
          >
            {{ editingItem ? 'Update' : 'Add' }} Item
          </VButton>
        </div>
      </template>
    </VModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, reactive, nextTick } from 'vue'
import { VButton, VCard, VLabel, VInput, VSelect, VTextarea, VBadge, VModal, VCheckbox } from '@/components/ui'
import { Plus, Edit, Trash2, Star, Briefcase, Upload } from 'lucide-vue-next'
import type { CompanySettings } from '../../types/settings.types'

// Props
interface Props {
  settings: CompanySettings | null
  canWrite: boolean
}

const props = withDefaults(defineProps<Props>(), {
  canWrite: false
})

// State
const portfolioItems = ref<any[]>([])
const selectedCategory = ref('all')
const showModal = ref(false)
const editingItem = ref<any>(null)
const saving = ref(false)
const imageInputRef = ref<HTMLInputElement>()

const formData = reactive({
  title: '',
  description: '',
  category: '',
  external_url: '',
  is_featured: false,
  image_file: null as File | null,
  image_preview: null as string | null
})

// Portfolio Categories
const portfolioCategories = [
  { value: 'project', label: 'Project' },
  { value: 'certification', label: 'Certification' },
  { value: 'award', label: 'Award' },
  { value: 'team', label: 'Team Member' },
  { value: 'testimonial', label: 'Testimonial' },
  { value: 'case_study', label: 'Case Study' },
  { value: 'gallery', label: 'Photo Gallery' }
]

// Computed
const filteredPortfolioItems = computed(() => {
  if (selectedCategory.value === 'all') {
    return portfolioItems.value
  }
  return portfolioItems.value.filter(item => item.category === selectedCategory.value)
})

// Methods
function getCategoryLabel(category: string) {
  const cat = portfolioCategories.find(c => c.value === category)
  return cat?.label || category
}

function openAddModal() {
  editingItem.value = null
  resetForm()
  showModal.value = true
}

function editItem(item: any) {
  editingItem.value = item
  formData.title = item.title
  formData.description = item.description
  formData.category = item.category
  formData.external_url = item.external_url
  formData.is_featured = item.is_featured
  formData.image_preview = item.image_url
  showModal.value = true
}

function resetForm() {
  formData.title = ''
  formData.description = ''
  formData.category = ''
  formData.external_url = ''
  formData.is_featured = false
  formData.image_file = null
  formData.image_preview = null
}

function triggerImageUpload() {
  imageInputRef.value?.click()
}

function handleImageUpload(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  
  if (file) {
    formData.image_file = file
    
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      formData.image_preview = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

async function saveItem() {
  if (!formData.title || !formData.category) return
  
  saving.value = true
  
  try {
    // Mock API call - replace with actual implementation
    console.log('Saving portfolio item:', formData)
    
    // Simulate API delay
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    if (editingItem.value) {
      // Update existing item
      const index = portfolioItems.value.findIndex(item => item.id === editingItem.value.id)
      if (index !== -1) {
        portfolioItems.value[index] = {
          ...editingItem.value,
          ...formData,
          image_url: formData.image_preview
        }
      }
    } else {
      // Add new item
      portfolioItems.value.push({
        id: Date.now(),
        ...formData,
        image_url: formData.image_preview,
        created_at: new Date().toISOString()
      })
    }
    
    showModal.value = false
    resetForm()
    
  } catch (error) {
    console.error('Failed to save portfolio item:', error)
  } finally {
    saving.value = false
  }
}

function deleteItem(item: any) {
  if (confirm('Are you sure you want to delete this portfolio item?')) {
    const index = portfolioItems.value.findIndex(i => i.id === item.id)
    if (index !== -1) {
      portfolioItems.value.splice(index, 1)
    }
  }
}

function toggleFeatured(item: any) {
  const index = portfolioItems.value.findIndex(i => i.id === item.id)
  if (index !== -1) {
    portfolioItems.value[index].is_featured = !portfolioItems.value[index].is_featured
  }
}

// Initialize with mock data for demonstration
portfolioItems.value = [
  {
    id: 1,
    title: 'Downtown Office Complex',
    description: 'Modern 50-story office building in the heart of downtown',
    category: 'project',
    image_url: null,
    external_url: '',
    is_featured: true,
    created_at: '2024-01-15'
  },
  {
    id: 2,
    title: 'LEED Platinum Certification',
    description: 'Green building certification for sustainable construction practices',
    category: 'certification',
    image_url: null,
    external_url: '',
    is_featured: false,
    created_at: '2024-02-20'
  }
]
</script>