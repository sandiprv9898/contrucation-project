<template>
  <div :class="gridClass">
    <label 
      :for="fieldId" 
      class="block text-sm font-medium text-gray-700 mb-2"
      :class="{ 'after:content-[\"*\"] after:text-red-500 after:ml-1': config.required }"
    >
      {{ config.label }}
    </label>

    <!-- Text Input -->
    <input
      v-if="config.type === 'text' || config.type === 'email' || config.type === 'url' || config.type === 'tel' || config.type === 'date'"
      :id="fieldId"
      :type="config.type"
      :value="modelValue"
      @input="handleInput"
      :placeholder="config.placeholder"
      :required="config.required"
      :disabled="config.disabled || !canWrite"
      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
    />

    <!-- Textarea -->
    <textarea
      v-else-if="config.type === 'textarea'"
      :id="fieldId"
      :value="modelValue"
      @input="handleInput"
      :placeholder="config.placeholder"
      :required="config.required"
      :disabled="config.disabled || !canWrite"
      rows="4"
      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
    ></textarea>

    <!-- Select -->
    <select
      v-else-if="config.type === 'select'"
      :id="fieldId"
      :value="modelValue"
      @change="handleInput"
      :required="config.required"
      :disabled="config.disabled || !canWrite"
      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
    >
      <option value="">{{ config.placeholder || `Select ${config.label.toLowerCase()}` }}</option>
      <option 
        v-for="option in config.options" 
        :key="option.value" 
        :value="option.value"
      >
        {{ option.label }}
      </option>
    </select>

    <!-- Color Input -->
    <div v-else-if="config.type === 'color'" class="flex items-center gap-3">
      <input
        :id="fieldId"
        type="color"
        :value="modelValue"
        @input="handleInput"
        :disabled="config.disabled || !canWrite"
        class="w-12 h-10 border border-gray-300 rounded cursor-pointer disabled:cursor-not-allowed"
      />
      <input
        type="text"
        :value="modelValue"
        @input="handleInput"
        :placeholder="config.placeholder"
        :disabled="config.disabled || !canWrite"
        class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
      />
    </div>

    <!-- File Input -->
    <div v-else-if="config.type === 'file'">
      <div class="flex items-start gap-6">
        <!-- File Preview -->
        <div class="flex-shrink-0">
          <div class="w-24 h-24 border-2 border-gray-300 border-dashed rounded-lg flex items-center justify-center bg-gray-50">
            <img 
              v-if="modelValue && isImageUrl(modelValue)" 
              :src="modelValue" 
              :alt="config.label" 
              class="w-full h-full object-contain rounded-lg"
            >
            <div v-else class="text-center">
              <svg class="w-8 h-8 text-gray-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              <span class="text-xs text-gray-500">No File</span>
            </div>
          </div>
        </div>
        <!-- File Input -->
        <div class="flex-1">
          <input
            :id="fieldId"
            type="file"
            :accept="getFileAccept()"
            @change="handleFileInput"
            :disabled="config.disabled || !canWrite"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
          />
        </div>
      </div>
    </div>

    <!-- Hint Text -->
    <p v-if="config.hint" class="text-xs text-gray-500 mt-1">
      {{ config.hint }}
    </p>

    <!-- Validation Error -->
    <p v-if="validationError" class="text-xs text-red-600 mt-1">
      {{ validationError }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import type { FieldConfig } from '../config/company-settings.config'

interface Props {
  config: FieldConfig
  modelValue: any
  canWrite: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:modelValue': [value: any]
  'file-upload': [file: File, fieldKey: string]
}>()

// State
const validationError = ref('')

// Computed
const fieldId = computed(() => `field-${props.config.key}`)

const gridClass = computed(() => {
  const cols = props.config.gridCols || 1
  return cols === 2 ? 'md:col-span-2' : 'md:col-span-1'
})

// Methods
function handleInput(event: Event) {
  const target = event.target as HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement
  const value = target.value
  
  // Validate input
  if (validateInput(value)) {
    emit('update:modelValue', value)
    validationError.value = ''
  }
}

function handleFileInput(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  
  if (!file) return

  // Emit file for parent to handle upload
  emit('file-upload', file, props.config.key)

  // Create preview URL for immediate display
  const reader = new FileReader()
  reader.onload = (e) => {
    emit('update:modelValue', e.target?.result as string)
  }
  reader.readAsDataURL(file)
}

function validateInput(value: string): boolean {
  // Required field validation
  if (props.config.required && (!value || value.trim() === '')) {
    validationError.value = `${props.config.label} is required`
    return false
  }

  // Custom validation
  if (value && props.config.validation) {
    if (props.config.validation instanceof RegExp) {
      if (!props.config.validation.test(value)) {
        validationError.value = `${props.config.label} format is invalid`
        return false
      }
    } else if (typeof props.config.validation === 'function') {
      if (!props.config.validation(value)) {
        validationError.value = `${props.config.label} is invalid`
        return false
      }
    }
  }

  return true
}

function isImageUrl(url: string): boolean {
  return /\.(jpg|jpeg|png|gif|svg|webp)$/i.test(url) || url.startsWith('data:image/')
}

function getFileAccept(): string {
  if (props.config.key === 'logo_url') {
    return 'image/*'
  }
  return '*/*'
}
</script>