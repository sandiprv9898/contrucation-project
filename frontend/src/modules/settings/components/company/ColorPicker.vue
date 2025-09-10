<template>
  <div class="flex items-center gap-3">
    <!-- Color Swatch -->
    <button
      :style="{ backgroundColor: modelValue }"
      :disabled="disabled"
      @click="showPicker = !showPicker"
      class="h-10 w-16 rounded border border-border cursor-pointer disabled:cursor-not-allowed relative"
      :class="{
        'ring-2 ring-primary ring-offset-2': showPicker
      }"
    >
      <!-- Transparency Pattern Background for transparent colors -->
      <div
        v-if="isTransparent"
        class="absolute inset-0 opacity-50"
        style="background-image: linear-gradient(45deg, #ccc 25%, transparent 25%), linear-gradient(-45deg, #ccc 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #ccc 75%), linear-gradient(-45deg, transparent 75%, #ccc 75%); background-size: 8px 8px; background-position: 0 0, 0 4px, 4px -4px, -4px 0px;"
      ></div>
    </button>

    <!-- Text Input -->
    <VInput
      :model-value="displayValue"
      @update:model-value="handleInputChange"
      :placeholder="placeholder"
      :disabled="disabled"
      class="flex-1"
      maxlength="9"
    />

    <!-- Color Picker Popover -->
    <div
      v-if="showPicker && !disabled"
      class="absolute z-50 mt-2 p-4 bg-background border border-border rounded-lg shadow-lg"
      style="top: 100%; left: 0;"
      @click.stop
    >
      <!-- Color Palette -->
      <div class="space-y-3">
        <!-- Predefined Colors -->
        <div>
          <VLabel class="text-xs font-medium mb-2 block">Quick Colors</VLabel>
          <div class="grid grid-cols-8 gap-1">
            <button
              v-for="color in predefinedColors"
              :key="color"
              :style="{ backgroundColor: color }"
              @click="selectColor(color)"
              class="w-6 h-6 rounded border border-border hover:ring-2 hover:ring-primary/50"
              :class="{
                'ring-2 ring-primary': modelValue === color
              }"
            ></button>
          </div>
        </div>

        <!-- Color Input Controls -->
        <div class="space-y-2">
          <!-- Native Color Picker -->
          <div>
            <VLabel class="text-xs font-medium mb-1 block">Color Picker</VLabel>
            <input
              :value="hexValue"
              @input="handleColorInput"
              type="color"
              class="w-full h-8 rounded border border-border cursor-pointer"
            />
          </div>

          <!-- Hex Input -->
          <div>
            <VLabel class="text-xs font-medium mb-1 block">Hex Value</VLabel>
            <VInput
              :model-value="hexValue"
              @update:model-value="handleHexInput"
              placeholder="#ffffff"
              class="text-xs font-mono"
              maxlength="7"
            />
          </div>

          <!-- RGB Inputs -->
          <div>
            <VLabel class="text-xs font-medium mb-1 block">RGB Values</VLabel>
            <div class="grid grid-cols-3 gap-1">
              <VInput
                :model-value="rgbValues.r"
                @update:model-value="(val) => updateRGB('r', val)"
                type="number"
                min="0"
                max="255"
                class="text-xs"
                placeholder="R"
              />
              <VInput
                :model-value="rgbValues.g"
                @update:model-value="(val) => updateRGB('g', val)"
                type="number"
                min="0"
                max="255"
                class="text-xs"
                placeholder="G"
              />
              <VInput
                :model-value="rgbValues.b"
                @update:model-value="(val) => updateRGB('b', val)"
                type="number"
                min="0"
                max="255"
                class="text-xs"
                placeholder="B"
              />
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between pt-2 border-t border-border">
          <VButton
            @click="clearColor"
            variant="outline"
            size="sm"
          >
            Clear
          </VButton>
          <VButton
            @click="showPicker = false"
            size="sm"
          >
            Done
          </VButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { VInput, VLabel, VButton } from '@/components/ui'

// Props
interface Props {
  modelValue: string
  placeholder?: string
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: '#ffffff',
  disabled: false
})

// Emits
const emit = defineEmits<{
  'update:modelValue': [value: string]
  update: [colorKey: string, value: string]
}>()

// State
const showPicker = ref(false)

// Predefined color palette
const predefinedColors = [
  '#000000', '#333333', '#666666', '#999999',
  '#cccccc', '#ffffff', '#f3f4f6', '#e5e7eb',
  '#ef4444', '#f97316', '#f59e0b', '#eab308',
  '#84cc16', '#22c55e', '#10b981', '#14b8a6',
  '#06b6d4', '#0ea5e9', '#3b82f6', '#6366f1',
  '#8b5cf6', '#a855f7', '#d946ef', '#ec4899',
  '#f43f5e', '#dc2626', '#ea580c', '#d97706',
  '#ca8a04', '#65a30d', '#16a34a', '#059669'
]

// Computed
const displayValue = computed(() => props.modelValue || '')

const hexValue = computed(() => {
  if (!props.modelValue) return '#ffffff'
  return props.modelValue.startsWith('#') ? props.modelValue : `#${props.modelValue}`
})

const isTransparent = computed(() => {
  return !props.modelValue || props.modelValue === 'transparent'
})

const rgbValues = computed(() => {
  const hex = hexValue.value
  if (!hex || hex.length !== 7) {
    return { r: 255, g: 255, b: 255 }
  }
  
  const r = parseInt(hex.slice(1, 3), 16)
  const g = parseInt(hex.slice(3, 5), 16)
  const b = parseInt(hex.slice(5, 7), 16)
  
  return { r, g, b }
})

// Methods
function selectColor(color: string) {
  emit('update:modelValue', color)
  showPicker.value = false
}

function handleInputChange(value: string) {
  emit('update:modelValue', value)
}

function handleColorInput(event: Event) {
  const target = event.target as HTMLInputElement
  emit('update:modelValue', target.value)
}

function handleHexInput(value: string) {
  let hex = value
  if (!hex.startsWith('#')) {
    hex = `#${hex}`
  }
  
  // Validate hex format
  if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
    emit('update:modelValue', hex)
  }
}

function updateRGB(component: 'r' | 'g' | 'b', value: string) {
  const numValue = parseInt(value) || 0
  const clampedValue = Math.max(0, Math.min(255, numValue))
  
  const newRgb = { ...rgbValues.value }
  newRgb[component] = clampedValue
  
  // Convert RGB to hex
  const hex = `#${[newRgb.r, newRgb.g, newRgb.b]
    .map(x => x.toString(16).padStart(2, '0'))
    .join('')}`
  
  emit('update:modelValue', hex)
}

function clearColor() {
  emit('update:modelValue', '')
  showPicker.value = false
}

// Close picker when clicking outside
function handleClickOutside(event: Event) {
  const target = event.target as HTMLElement
  if (showPicker.value && !target.closest('.absolute')) {
    showPicker.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>