<template>
  <div class="space-y-6">
    <!-- Theme Selector -->
    <div class="space-y-3">
      <h3 class="text-lg font-semibold text-gray-900">Choose Theme</h3>
      <div class="grid grid-cols-2 gap-3">
        <button
          v-for="themeName in availableThemes"
          :key="themeName"
          @click="setTheme(themeName)"
          :class="[
            'relative p-4 border-2 rounded-lg transition-all duration-200 text-left',
            currentTheme === themeName
              ? 'border-orange-500 bg-orange-50'
              : 'border-gray-200 hover:border-gray-300 bg-white'
          ]"
        >
          <!-- Theme Preview -->
          <div class="flex items-center space-x-3 mb-3">
            <div class="flex space-x-1">
              <div 
                v-for="(color, index) in getThemePreviewColors(themeName)"
                :key="index"
                class="w-4 h-4 rounded-full"
                :style="{ backgroundColor: color }"
              ></div>
            </div>
            <span class="font-medium capitalize">{{ themeName.replace('-', ' ') }}</span>
          </div>
          
          <!-- Current Theme Indicator -->
          <div 
            v-if="currentTheme === themeName"
            class="absolute top-2 right-2"
          >
            <Check class="w-5 h-5 text-orange-500" />
          </div>
        </button>
      </div>
    </div>

    <!-- Dark Mode Toggle -->
    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
      <div>
        <h4 class="font-medium text-gray-900">Dark Mode</h4>
        <p class="text-sm text-gray-600">Toggle between light and dark themes</p>
      </div>
      <VButton
        variant="outline"
        size="sm"
        @click="toggleDarkMode"
      >
        <component :is="isDarkMode ? Sun : Moon" class="w-4 h-4 mr-2" />
        {{ isDarkMode ? 'Light' : 'Dark' }}
      </VButton>
    </div>

    <!-- Custom Color Editor -->
    <div v-if="showAdvanced" class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Custom Colors</h3>
        <VButton
          variant="outline"
          size="sm"
          @click="resetCustomTheme"
        >
          <RotateCcw class="w-4 h-4 mr-1" />
          Reset
        </VButton>
      </div>

      <!-- Color Categories -->
      <div class="space-y-4">
        <div 
          v-for="(colors, category) in activeTheme.colors"
          :key="category"
          class="border border-gray-200 rounded-lg p-4"
        >
          <h4 class="font-medium text-gray-900 mb-3 capitalize">{{ category }} Colors</h4>
          
          <div class="grid grid-cols-5 gap-2">
            <div
              v-for="(color, shade) in colors"
              :key="shade"
              class="space-y-1"
            >
              <label class="block text-xs font-medium text-gray-600">
                {{ shade }}
              </label>
              <div class="relative">
                <input
                  type="color"
                  :value="color"
                  @input="updateThemeColor(`${category}.${shade}`, ($event.target as HTMLInputElement).value)"
                  class="w-full h-10 border border-gray-200 rounded cursor-pointer"
                />
                <div 
                  class="absolute inset-1 rounded pointer-events-none"
                  :style="{ backgroundColor: color }"
                ></div>
              </div>
              <p class="text-xs text-gray-500 font-mono">{{ color }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Theme Properties -->
    <div v-if="showAdvanced" class="space-y-4">
      <h3 class="text-lg font-semibold text-gray-900">Theme Properties</h3>
      
      <!-- Spacing -->
      <div class="border border-gray-200 rounded-lg p-4">
        <h4 class="font-medium text-gray-900 mb-3">Spacing Scale</h4>
        <div class="grid grid-cols-2 gap-4">
          <div
            v-for="(value, size) in activeTheme.spacing"
            :key="size"
            class="space-y-1"
          >
            <label class="block text-sm font-medium text-gray-700 capitalize">
              {{ size }} ({{ value }})
            </label>
            <div 
              class="bg-orange-200 h-4 rounded"
              :style="{ width: value }"
            ></div>
          </div>
        </div>
      </div>

      <!-- Border Radius -->
      <div class="border border-gray-200 rounded-lg p-4">
        <h4 class="font-medium text-gray-900 mb-3">Border Radius</h4>
        <div class="grid grid-cols-3 gap-4">
          <div
            v-for="(value, size) in activeTheme.borderRadius"
            :key="size"
            class="space-y-1"
          >
            <label class="block text-sm font-medium text-gray-700 capitalize">
              {{ size === 'none' ? 'none' : size }} ({{ value }})
            </label>
            <div 
              class="bg-orange-100 border-2 border-orange-300 w-16 h-16 flex items-center justify-center text-xs"
              :style="{ borderRadius: value }"
            >
              {{ size }}
            </div>
          </div>
        </div>
      </div>

      <!-- Shadows -->
      <div class="border border-gray-200 rounded-lg p-4">
        <h4 class="font-medium text-gray-900 mb-3">Shadow Scale</h4>
        <div class="grid grid-cols-2 gap-4">
          <div
            v-for="(value, size) in activeTheme.shadows"
            :key="size"
            class="space-y-1"
          >
            <label class="block text-sm font-medium text-gray-700 capitalize">
              {{ size }}
            </label>
            <div 
              class="bg-white w-16 h-16 rounded-lg flex items-center justify-center text-xs border border-gray-200"
              :style="{ boxShadow: value }"
            >
              {{ size }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Export/Import Theme -->
    <div v-if="showAdvanced" class="space-y-4">
      <h3 class="text-lg font-semibold text-gray-900">Import/Export</h3>
      
      <div class="flex items-center space-x-3">
        <VButton
          variant="outline"
          size="sm"
          @click="exportTheme"
        >
          <Download class="w-4 h-4 mr-1" />
          Export Theme
        </VButton>
        
        <VButton
          variant="outline"
          size="sm"
          @click="triggerImport"
        >
          <Upload class="w-4 h-4 mr-1" />
          Import Theme
        </VButton>
        
        <input
          ref="fileInput"
          type="file"
          accept=".json"
          style="display: none"
          @change="importTheme"
        />
      </div>

      <!-- CSS Variables Output -->
      <div class="border border-gray-200 rounded-lg p-4">
        <div class="flex items-center justify-between mb-3">
          <h4 class="font-medium text-gray-900">CSS Variables</h4>
          <VButton
            variant="outline"
            size="sm"
            @click="copyCSSVariables"
          >
            <Copy class="w-4 h-4 mr-1" />
            Copy
          </VButton>
        </div>
        
        <textarea
          :value="cssVariablesOutput"
          readonly
          rows="8"
          class="w-full text-xs font-mono bg-gray-50 border border-gray-200 rounded p-2"
        ></textarea>
      </div>
    </div>

    <!-- Toggle Advanced Options -->
    <div class="border-t border-gray-200 pt-4">
      <VButton
        variant="outline"
        size="sm"
        @click="showAdvanced = !showAdvanced"
        class="w-full"
      >
        <component :is="showAdvanced ? ChevronUp : ChevronDown" class="w-4 h-4 mr-1" />
        {{ showAdvanced ? 'Hide' : 'Show' }} Advanced Options
      </VButton>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useTheme } from '@/composables/useTheme';
import { VButton } from '@/components/ui';
import { 
  Check, Sun, Moon, RotateCcw, Download, Upload, Copy,
  ChevronUp, ChevronDown
} from 'lucide-vue-next';

interface Props {
  showAdvanced?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  showAdvanced: false
});

// Theme composable
const {
  currentTheme,
  activeTheme,
  isDarkMode,
  setTheme,
  getAvailableThemes,
  toggleDarkMode,
  updateThemeColor,
  resetCustomTheme,
  generateCSSVariables
} = useTheme();

// State
const showAdvanced = ref(props.showAdvanced);
const fileInput = ref<HTMLInputElement>();

// Computed
const availableThemes = computed(() => getAvailableThemes());

const cssVariablesOutput = computed(() => {
  const variables = generateCSSVariables(activeTheme.value);
  return `:root {\n${Object.entries(variables)
    .map(([key, value]) => `  ${key}: ${value};`)
    .join('\n')}\n}`;
});

// Methods
const getThemePreviewColors = (themeName: string): string[] => {
  const themes = {
    construction: ['#f97316', '#ea580c', '#c2410c'],
    corporate: ['#3b82f6', '#2563eb', '#1d4ed8'],
    'high-contrast': ['#000000', '#404040', '#808080'],
    dark: ['#f97316', '#2a2a2a', '#4a4a4a']
  };
  
  return themes[themeName as keyof typeof themes] || themes.construction;
};

const exportTheme = () => {
  const themeData = {
    name: `custom-${Date.now()}`,
    ...activeTheme.value
  };
  
  const blob = new Blob([JSON.stringify(themeData, null, 2)], {
    type: 'application/json'
  });
  
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `${themeData.name}.json`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
};

const triggerImport = () => {
  fileInput.value?.click();
};

const importTheme = (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = (e) => {
    try {
      const themeData = JSON.parse(e.target?.result as string);
      // Apply imported theme (this would need to be implemented in useTheme)
      console.log('Imported theme:', themeData);
    } catch (error) {
      console.error('Failed to import theme:', error);
    }
  };
  reader.readAsText(file);
};

const copyCSSVariables = async () => {
  try {
    await navigator.clipboard.writeText(cssVariablesOutput.value);
    // Could show a toast notification here
  } catch (error) {
    console.error('Failed to copy CSS variables:', error);
  }
};
</script>