<template>
  <div class="space-y-6">
    <!-- Branding Preview Header -->
    <div class="flex items-center justify-between">
      <h4 class="font-medium">Brand Preview</h4>
      <VButton
        @click="showFullPreview = !showFullPreview"
        variant="outline"
        size="sm"
      >
        <Eye class="mr-2 h-4 w-4" />
        {{ showFullPreview ? 'Hide' : 'Show' }} Full Preview
      </VButton>
    </div>

    <!-- Quick Color Preview -->
    <div class="p-4 border border-border rounded-lg">
      <h5 class="font-medium mb-3 text-sm">Color Palette</h5>
      <div class="flex items-center gap-3">
        <div class="flex flex-col items-center gap-1">
          <div
            class="h-12 w-12 rounded border border-border"
            :style="{ backgroundColor: colorPalette.primary }"
          ></div>
          <span class="text-xs text-muted-foreground">Primary</span>
        </div>
        <div class="flex flex-col items-center gap-1">
          <div
            class="h-12 w-12 rounded border border-border"
            :style="{ backgroundColor: colorPalette.secondary }"
          ></div>
          <span class="text-xs text-muted-foreground">Secondary</span>
        </div>
        <div class="flex flex-col items-center gap-1">
          <div
            class="h-12 w-12 rounded border border-border"
            :style="{ backgroundColor: colorPalette.accent }"
          ></div>
          <span class="text-xs text-muted-foreground">Accent</span>
        </div>
        <div class="flex flex-col items-center gap-1">
          <div
            class="h-12 w-12 rounded border border-border"
            :style="{ backgroundColor: colorPalette.success }"
          ></div>
          <span class="text-xs text-muted-foreground">Success</span>
        </div>
        <div class="flex flex-col items-center gap-1">
          <div
            class="h-12 w-12 rounded border border-border"
            :style="{ backgroundColor: colorPalette.warning }"
          ></div>
          <span class="text-xs text-muted-foreground">Warning</span>
        </div>
        <div class="flex flex-col items-center gap-1">
          <div
            class="h-12 w-12 rounded border border-border"
            :style="{ backgroundColor: colorPalette.error }"
          ></div>
          <span class="text-xs text-muted-foreground">Error</span>
        </div>
      </div>
    </div>

    <!-- Logo Preview -->
    <div v-if="logoAssets.logo" class="p-4 border border-border rounded-lg">
      <h5 class="font-medium mb-3 text-sm">Logo Variants</h5>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Primary Logo -->
        <div v-if="logoAssets.logo" class="text-center">
          <div class="h-20 w-20 mx-auto mb-2 rounded border border-border flex items-center justify-center overflow-hidden bg-white">
            <img :src="logoAssets.logo" alt="Primary Logo" class="h-full w-full object-contain" />
          </div>
          <span class="text-xs text-muted-foreground">Primary</span>
        </div>
        
        <!-- Light Logo -->
        <div v-if="logoAssets.logoLight" class="text-center">
          <div class="h-20 w-20 mx-auto mb-2 rounded border border-border flex items-center justify-center overflow-hidden bg-gray-800">
            <img :src="logoAssets.logoLight" alt="Light Logo" class="h-full w-full object-contain" />
          </div>
          <span class="text-xs text-muted-foreground">Light (Dark BG)</span>
        </div>
        
        <!-- Dark Logo -->
        <div v-if="logoAssets.logoDark" class="text-center">
          <div class="h-20 w-20 mx-auto mb-2 rounded border border-border flex items-center justify-center overflow-hidden bg-gray-100">
            <img :src="logoAssets.logoDark" alt="Dark Logo" class="h-full w-full object-contain" />
          </div>
          <span class="text-xs text-muted-foreground">Dark (Light BG)</span>
        </div>
      </div>
    </div>

    <!-- Full Preview Modal -->
    <VModal
      v-model:open="showFullPreview"
      title="Brand Preview"
      size="xl"
    >
      <div class="space-y-6">
        <!-- Header Preview -->
        <div
          class="p-6 rounded-lg"
          :style="{ backgroundColor: colorPalette.primary, color: 'white' }"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div v-if="logoAssets.logoLight" class="h-8 w-8 rounded overflow-hidden">
                <img :src="logoAssets.logoLight" alt="Logo" class="h-full w-full object-contain" />
              </div>
              <h2 class="text-xl font-semibold">Company Name</h2>
            </div>
            <nav class="flex items-center gap-4">
              <a href="#" class="hover:opacity-80">Home</a>
              <a href="#" class="hover:opacity-80">About</a>
              <a href="#" class="hover:opacity-80">Contact</a>
            </nav>
          </div>
        </div>

        <!-- Content Preview -->
        <div class="space-y-4">
          <!-- Button Examples -->
          <div>
            <h5 class="font-medium mb-3">Button Styles</h5>
            <div class="flex items-center gap-3">
              <button
                class="px-4 py-2 rounded text-white font-medium"
                :style="{ backgroundColor: colorPalette.primary }"
              >
                Primary Button
              </button>
              <button
                class="px-4 py-2 rounded border font-medium"
                :style="{ borderColor: colorPalette.primary, color: colorPalette.primary }"
              >
                Secondary Button
              </button>
              <button
                class="px-4 py-2 rounded text-white font-medium"
                :style="{ backgroundColor: colorPalette.accent }"
              >
                Accent Button
              </button>
            </div>
          </div>

          <!-- Alert Examples -->
          <div>
            <h5 class="font-medium mb-3">Alert Styles</h5>
            <div class="space-y-2">
              <div
                class="p-3 rounded border-l-4"
                :style="{ 
                  backgroundColor: `${colorPalette.success}15`, 
                  borderLeftColor: colorPalette.success,
                  color: colorPalette.success
                }"
              >
                <strong>Success:</strong> Operation completed successfully!
              </div>
              <div
                class="p-3 rounded border-l-4"
                :style="{ 
                  backgroundColor: `${colorPalette.warning}15`, 
                  borderLeftColor: colorPalette.warning,
                  color: colorPalette.warning
                }"
              >
                <strong>Warning:</strong> Please review your settings.
              </div>
              <div
                class="p-3 rounded border-l-4"
                :style="{ 
                  backgroundColor: `${colorPalette.error}15`, 
                  borderLeftColor: colorPalette.error,
                  color: colorPalette.error
                }"
              >
                <strong>Error:</strong> Something went wrong.
              </div>
            </div>
          </div>

          <!-- Card Example -->
          <div>
            <h5 class="font-medium mb-3">Card Style</h5>
            <div class="border border-border rounded-lg overflow-hidden">
              <div
                class="h-2"
                :style="{ backgroundColor: colorPalette.primary }"
              ></div>
              <div class="p-4">
                <h6 class="font-medium mb-2">Sample Card</h6>
                <p class="text-muted-foreground text-sm">
                  This is how your cards will look with the current branding colors.
                </p>
                <button
                  class="mt-3 px-3 py-1 rounded text-sm text-white"
                  :style="{ backgroundColor: colorPalette.primary }"
                >
                  Action
                </button>
              </div>
            </div>
          </div>

          <!-- Footer Preview -->
          <div
            class="p-6 rounded-lg"
            :style="{ backgroundColor: colorPalette.secondary, color: 'white' }"
          >
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <h6 class="font-medium mb-2">Company</h6>
                <ul class="space-y-1 text-sm opacity-80">
                  <li><a href="#" class="hover:opacity-100">About Us</a></li>
                  <li><a href="#" class="hover:opacity-100">Services</a></li>
                  <li><a href="#" class="hover:opacity-100">Portfolio</a></li>
                </ul>
              </div>
              <div>
                <h6 class="font-medium mb-2">Contact</h6>
                <ul class="space-y-1 text-sm opacity-80">
                  <li>contact@company.com</li>
                  <li>+1 (555) 123-4567</li>
                  <li>123 Main St, City</li>
                </ul>
              </div>
              <div>
                <h6 class="font-medium mb-2">Follow Us</h6>
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 bg-white/20 rounded flex items-center justify-center">
                    <span class="text-xs">FB</span>
                  </div>
                  <div class="w-8 h-8 bg-white/20 rounded flex items-center justify-center">
                    <span class="text-xs">TW</span>
                  </div>
                  <div class="w-8 h-8 bg-white/20 rounded flex items-center justify-center">
                    <span class="text-xs">LI</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end">
          <VButton @click="showFullPreview = false">
            Close Preview
          </VButton>
        </div>
      </template>
    </VModal>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { VButton, VModal } from '@/components/ui'
import { Eye } from 'lucide-vue-next'

// Props
interface Props {
  colorPalette: {
    primary: string
    secondary: string
    accent: string
    success: string
    warning: string
    error: string
    info?: string
  }
  logoAssets: {
    logo: string | null
    logoLight: string | null
    logoDark: string | null
    favicon?: string | null
  }
}

const props = withDefaults(defineProps<Props>(), {
  colorPalette: () => ({
    primary: '#f97316',
    secondary: '#475569',
    accent: '#22c55e',
    success: '#10b981',
    warning: '#f59e0b',
    error: '#ef4444',
    info: '#3b82f6'
  }),
  logoAssets: () => ({
    logo: null,
    logoLight: null,
    logoDark: null,
    favicon: null
  })
})

// State
const showFullPreview = ref(false)
</script>