<template>
  <div class="space-y-8">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold text-gray-900">
          {{ $t('common.construction.labels.construction_dictionary', 'Construction Dictionary') }}
        </h1>
        <div class="flex items-center gap-4">
          <span class="text-sm text-gray-500">Current Language:</span>
          <LanguageSwitcher />
        </div>
      </div>
      <p class="text-gray-600">
        {{ $t('common.app.description', 'Professional construction project management solution') }}
      </p>
    </div>

    <!-- Construction Terms Demo -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Construction Terminology Examples</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Materials Section -->
        <div class="space-y-3">
          <h3 class="font-medium text-gray-900 border-b pb-2">
            {{ $t('common.construction.categories.materials', 'Materials') }}
          </h3>
          <div class="space-y-2">
            <div class="flex items-center gap-2">
              <span class="text-2xl">🧱</span>
              <ConstructionTermTooltip
                term-key="concrete"
                category="materials"
                description="Portland cement mixed with water, sand, and aggregate to form a hard, durable building material."
                usage-context="Used in foundations, slabs, walls, and structural elements."
              >
                {{ $t('construction.materials.concrete') }}
              </ConstructionTermTooltip>
            </div>
            
            <div class="flex items-center gap-2">
              <span class="text-2xl">🔩</span>
              <ConstructionTermTooltip
                term-key="steel"
                category="materials"
                description="Strong metal alloy used for structural framing and reinforcement."
                usage-context="Primary structural material for beams, columns, and reinforcement bars."
              >
                {{ $t('construction.materials.steel') }}
              </ConstructionTermTooltip>
            </div>
            
            <div class="flex items-center gap-2">
              <span class="text-2xl">🪵</span>
              <ConstructionTermTooltip
                term-key="lumber"
                category="materials" 
                description="Processed wood used for construction framing and finishing."
                usage-context="Used for framing, flooring, roofing, and interior finishing."
              >
                {{ $t('construction.materials.lumber') }}
              </ConstructionTermTooltip>
            </div>
          </div>
        </div>

        <!-- Safety Equipment Section -->
        <div class="space-y-3">
          <h3 class="font-medium text-gray-900 border-b pb-2">
            {{ $t('common.construction.categories.safety', 'Safety Equipment') }}
          </h3>
          <div class="space-y-2">
            <div class="flex items-center gap-2">
              <span class="text-2xl">🦺</span>
              <ConstructionTermTooltip
                term-key="hard_hat"
                category="safety"
                description="Protective helmet worn to prevent head injuries from falling objects."
                usage-context="Required PPE on all construction sites. Must meet OSHA/ANSI standards."
              >
                {{ $t('construction.safety.hard_hat') }}
              </ConstructionTermTooltip>
            </div>
            
            <div class="flex items-center gap-2">
              <span class="text-2xl">🦺</span>
              <ConstructionTermTooltip
                term-key="safety_vest"
                category="safety"
                description="High-visibility vest worn to increase worker visibility."
                usage-context="Required in areas with vehicle traffic or low visibility conditions."
              >
                {{ $t('construction.safety.safety_vest') }}
              </ConstructionTermTooltip>
            </div>
            
            <div class="flex items-center gap-2">
              <span class="text-2xl">👢</span>
              <ConstructionTermTooltip
                term-key="work_boots"
                category="safety"
                description="Protective footwear with steel toes and slip-resistant soles."
                usage-context="Essential PPE to protect feet from crushing, puncture, and slip hazards."
              >
                {{ $t('construction.safety.work_boots') }}
              </ConstructionTermTooltip>
            </div>
          </div>
        </div>

        <!-- Equipment Section -->
        <div class="space-y-3">
          <h3 class="font-medium text-gray-900 border-b pb-2">
            {{ $t('common.construction.categories.equipment', 'Heavy Equipment') }}
          </h3>
          <div class="space-y-2">
            <div class="flex items-center gap-2">
              <span class="text-2xl">🚜</span>
              <ConstructionTermTooltip
                term-key="excavator"
                category="equipment"
                description="Heavy machinery used for digging, demolition, and material handling."
                usage-context="Essential for site preparation, foundation work, and utility installation."
              >
                {{ $t('construction.equipment.excavator') }}
              </ConstructionTermTooltip>
            </div>
            
            <div class="flex items-center gap-2">
              <span class="text-2xl">🏗️</span>
              <ConstructionTermTooltip
                term-key="crane"
                category="equipment"
                description="Large machine used to lift and move heavy materials and equipment."
                usage-context="Critical for high-rise construction and placing heavy structural elements."
              >
                {{ $t('construction.equipment.crane') }}
              </ConstructionTermTooltip>
            </div>
            
            <div class="flex items-center gap-2">
              <span class="text-2xl">🚛</span>
              <ConstructionTermTooltip
                term-key="concrete_mixer"
                category="equipment"
                description="Vehicle or machine used to mix and transport concrete to job sites."
                usage-context="Essential for delivering fresh concrete for foundations, slabs, and structures."
              >
                {{ $t('construction.equipment.concrete_mixer') }}
              </ConstructionTermTooltip>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Project Phases Demo -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">
        {{ $t('common.construction.categories.phases', 'Project Phases') }}
      </h2>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div v-for="(phase, index) in projectPhases" :key="phase.key" class="relative">
          <div class="bg-blue-50 rounded-lg p-4 border-2 border-blue-200 hover:border-blue-400 transition-colors">
            <div class="text-sm font-medium text-blue-600 mb-1">Phase {{ index + 1 }}</div>
            <ConstructionTermTooltip
              :term-key="phase.key"
              category="phases"
              :description="getPhaseDescription(phase.key)"
              class="text-gray-900 font-medium"
            >
              {{ phase.label }}
            </ConstructionTermTooltip>
          </div>
          
          <!-- Arrow to next phase -->
          <div v-if="index < projectPhases.length - 1" class="hidden md:block absolute top-1/2 -right-2 transform -translate-y-1/2 text-gray-400">
            →
          </div>
        </div>
      </div>
    </div>

    <!-- Search Demo -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Search Construction Terms</h2>
      
      <div class="max-w-md mb-4">
        <VInput
          v-model="searchQuery"
          :placeholder="$t('common.construction.labels.search_terms', 'Search construction terms...')"
          class="w-full"
        >
          <template #prefix>
            <Search class="h-4 w-4 text-gray-400" />
          </template>
        </VInput>
      </div>
      
      <div v-if="searchResults.length > 0" class="space-y-2">
        <div class="text-sm font-medium text-gray-600">
          Found {{ searchResults.length }} terms:
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
          <div 
            v-for="result in searchResults.slice(0, 12)" 
            :key="`${result.category}-${result.key}`"
            class="bg-gray-50 rounded px-3 py-2 text-sm"
          >
            <ConstructionTermTooltip
              :term-key="result.key"
              :category="result.category"
              class="text-blue-600 hover:text-blue-800"
            >
              {{ result.translation }}
            </ConstructionTermTooltip>
            <span class="text-gray-500 text-xs ml-2">
              ({{ $t(`common.construction.categories.${result.category}`, result.category) }})
            </span>
          </div>
        </div>
      </div>
      
      <div v-else-if="searchQuery" class="text-gray-500 text-center py-4">
        {{ $t('common.construction.labels.no_results', 'No construction terms found') }}
      </div>
    </div>

    <!-- Language Statistics -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Localization Statistics</h2>
      
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center">
          <div class="text-2xl font-bold text-blue-600">{{ enabledLocales.length }}</div>
          <div class="text-sm text-gray-600">Supported Languages</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-green-600">{{ allTerms.length }}</div>
          <div class="text-sm text-gray-600">Construction Terms</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-purple-600">10</div>
          <div class="text-sm text-gray-600">Term Categories</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-orange-600">{{ currentLocale.flag }}</div>
          <div class="text-sm text-gray-600">Current Language</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Search } from 'lucide-vue-next'
import { useConstructionTerms } from '@/composables/useConstructionTerms'
import { enabledLocales, getLocaleConfig } from '@/locales/config/supported-locales'
import LanguageSwitcher from '@/components/ui/LanguageSwitcher.vue'
import ConstructionTermTooltip from '@/components/ui/ConstructionTermTooltip.vue'
import VInput from '@/components/ui/VInput.vue'

// Composables
const { t, locale } = useI18n()
const { projectPhases, allTerms, searchTerms } = useConstructionTerms()

// State
const searchQuery = ref('')

// Computed
const currentLocale = computed(() => {
  return getLocaleConfig(locale.value) || enabledLocales[0]
})

const searchResults = computed(() => {
  if (!searchQuery.value || searchQuery.value.length < 2) {
    return []
  }
  return searchTerms(searchQuery.value)
})

// Methods
const getPhaseDescription = (phaseKey: string): string => {
  const descriptions: Record<string, string> = {
    planning: "Initial project planning, design requirements, and feasibility studies.",
    design: "Architectural and engineering design development and documentation.",
    permits: "Obtaining necessary building permits and regulatory approvals.", 
    site_preparation: "Site clearing, grading, and utility connections.",
    excavation: "Earthwork, foundation excavation, and site utilities.",
    foundation: "Concrete foundation work, footings, and below-grade construction.",
    structural: "Structural framing, steel work, and load-bearing elements.",
    framing: "Wood or steel framing for walls, floors, and roof structure.",
    roofing: "Roof installation, waterproofing, and exterior weatherproofing.",
    interior_finish: "Interior walls, flooring, fixtures, and final finishes.",
    inspection: "Final building inspection and code compliance verification.",
    closeout: "Project completion, documentation, and client handover."
  }
  return descriptions[phaseKey] || "Construction project phase."
}
</script>

<style scoped>
/* Custom styles for the demo page */
.construction-term {
  @apply text-blue-600 hover:text-blue-800 cursor-help underline decoration-dotted;
}

/* Responsive arrow positioning */
@media (max-width: 768px) {
  .absolute.top-1\/2.-right-2 {
    display: none;
  }
}

/* Enhanced hover effects */
.bg-blue-50:hover {
  @apply bg-blue-100;
}

.bg-gray-50:hover {
  @apply bg-gray-100;
}
</style>