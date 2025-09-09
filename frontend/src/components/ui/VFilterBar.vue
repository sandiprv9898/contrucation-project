<template>
  <div class="space-y-4">
    <!-- Filter Bar Header -->
    <div v-if="title || showToggle" class="flex items-center justify-between">
      <h3 v-if="title" class="text-sm font-medium text-gray-700">{{ title }}</h3>
      <button
        v-if="showToggle"
        @click="isExpanded = !isExpanded"
        class="flex items-center text-sm text-gray-600 hover:text-gray-900"
      >
        {{ isExpanded ? 'Hide Filters' : 'Show Filters' }}
        <ChevronDown 
          :class="isExpanded ? 'rotate-180' : ''"
          class="ml-1 w-4 h-4 transition-transform"
        />
      </button>
    </div>

    <!-- Filter Controls -->
    <Transition
      enter-active-class="transition-all duration-200"
      enter-from-class="opacity-0 max-h-0"
      enter-to-class="opacity-100 max-h-screen"
      leave-active-class="transition-all duration-200"
      leave-from-class="opacity-100 max-h-screen"
      leave-to-class="opacity-0 max-h-0"
    >
      <div v-if="!showToggle || isExpanded" class="overflow-hidden">
        <div :class="containerClass">
          <!-- Search Input -->
          <div v-if="searchable" class="flex-1 min-w-0">
            <VInput
              v-model="searchValue"
              :placeholder="searchPlaceholder"
              :size="size"
              class="w-full"
              @input="handleSearchChange"
            >
              <template #icon>
                <Search class="w-4 h-4 text-gray-400" />
              </template>
            </VInput>
          </div>

          <!-- Filter Items Slot -->
          <slot name="filters" :size="size">
            <!-- Auto-generated filters from props -->
            <template v-for="filter in filters" :key="filter.key">
              <!-- Select Filter -->
              <VSelect
                v-if="filter.type === 'select'"
                v-model="filterValues[filter.key]"
                :items="filter.options"
                :placeholder="filter.placeholder"
                :size="size"
                :class="filter.class"
                @change="handleFilterChange(filter.key, $event)"
              />
              
              <!-- Multi-Select Filter -->
              <div v-else-if="filter.type === 'multi-select'" class="relative">
                <VButton
                  variant="outline"
                  :size="size"
                  @click="toggleMultiSelect(filter.key)"
                  class="justify-between min-w-32"
                >
                  <span>
                    {{ getMultiSelectLabel(filter) }}
                  </span>
                  <ChevronDown class="w-4 h-4 ml-2" />
                </VButton>
                
                <!-- Multi-select dropdown -->
                <div
                  v-if="openMultiSelects[filter.key]"
                  v-click-outside="() => closeMultiSelect(filter.key)"
                  class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto"
                >
                  <div class="p-2 space-y-1">
                    <label
                      v-for="option in filter.options"
                      :key="getOptionValue(option)"
                      class="flex items-center space-x-2 p-1 hover:bg-gray-50 rounded cursor-pointer"
                    >
                      <VCheckbox
                        :checked="isOptionSelected(filter.key, getOptionValue(option))"
                        @update:checked="toggleMultiSelectOption(filter.key, getOptionValue(option))"
                      />
                      <span class="text-sm">{{ getOptionLabel(option) }}</span>
                    </label>
                  </div>
                </div>
              </div>
              
              <!-- Date Range Filter -->
              <div v-else-if="filter.type === 'date-range'" class="flex items-center space-x-2">
                <VInput
                  v-model="filterValues[filter.key]?.from"
                  type="date"
                  :size="size"
                  placeholder="From date"
                  @change="handleDateRangeChange(filter.key)"
                />
                <span class="text-gray-400">to</span>
                <VInput
                  v-model="filterValues[filter.key]?.to"
                  type="date"
                  :size="size"
                  placeholder="To date"
                  @change="handleDateRangeChange(filter.key)"
                />
              </div>
              
              <!-- Number Range Filter -->
              <div v-else-if="filter.type === 'number-range'" class="flex items-center space-x-2">
                <VInput
                  v-model="filterValues[filter.key]?.min"
                  type="number"
                  :size="size"
                  :placeholder="filter.minPlaceholder || 'Min'"
                  @change="handleNumberRangeChange(filter.key)"
                />
                <span class="text-gray-400">-</span>
                <VInput
                  v-model="filterValues[filter.key]?.max"
                  type="number"
                  :size="size"
                  :placeholder="filter.maxPlaceholder || 'Max'"
                  @change="handleNumberRangeChange(filter.key)"
                />
              </div>
            </template>
          </slot>

          <!-- Action Buttons -->
          <div class="flex items-center space-x-2">
            <!-- Clear Filters -->
            <VButton
              v-if="showClearButton && hasActiveFilters"
              variant="outline"
              :size="size"
              @click="clearAllFilters"
            >
              <X class="w-4 h-4 mr-1" />
              Clear
            </VButton>

            <!-- Custom Actions Slot -->
            <slot name="actions" :size="size" />
          </div>
        </div>
      </div>
    </Transition>

    <!-- Active Filters Display -->
    <div v-if="showActiveFilters && hasActiveFilters" class="flex flex-wrap items-center gap-2">
      <span class="text-sm text-gray-600">Active filters:</span>
      
      <!-- Search Badge -->
      <VBadge
        v-if="searchValue"
        variant="outline"
        class="cursor-pointer"
        @click="clearSearch"
      >
        Search: "{{ searchValue }}"
        <X class="w-3 h-3 ml-1" />
      </VBadge>
      
      <!-- Filter Badges -->
      <VBadge
        v-for="(value, key) in activeFilterValues"
        :key="key"
        variant="outline"
        class="cursor-pointer"
        @click="clearFilter(key)"
      >
        {{ getFilterLabel(key) }}: {{ getFilterDisplayValue(key, value) }}
        <X class="w-3 h-3 ml-1" />
      </VBadge>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, reactive } from 'vue';
import { VInput, VSelect, VButton, VCheckbox, VBadge } from '@/components/ui';
import { Search, ChevronDown, X } from 'lucide-vue-next';
import { cn } from '@/utils/cn';

// Filter definition types
interface FilterOption {
  label: string;
  value: string | number;
}

interface BaseFilter {
  key: string;
  label: string;
  type: 'select' | 'multi-select' | 'date-range' | 'number-range' | 'text';
  placeholder?: string;
  class?: string;
}

interface SelectFilter extends BaseFilter {
  type: 'select' | 'multi-select';
  options: FilterOption[] | string[] | number[];
}

interface DateRangeFilter extends BaseFilter {
  type: 'date-range';
}

interface NumberRangeFilter extends BaseFilter {
  type: 'number-range';
  minPlaceholder?: string;
  maxPlaceholder?: string;
}

type Filter = SelectFilter | DateRangeFilter | NumberRangeFilter;

interface Props {
  // Configuration
  title?: string;
  searchable?: boolean;
  searchPlaceholder?: string;
  
  // Filters
  filters?: Filter[];
  
  // Layout
  layout?: 'horizontal' | 'vertical' | 'grid';
  size?: 'sm' | 'md' | 'lg';
  
  // Features
  showToggle?: boolean;
  showClearButton?: boolean;
  showActiveFilters?: boolean;
  collapsible?: boolean;
  
  // Styling
  containerClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
  searchPlaceholder: 'Search...',
  filters: () => [],
  layout: 'horizontal',
  size: 'sm',
  showClearButton: true,
  showActiveFilters: true
});

const emit = defineEmits<{
  'search-change': [value: string];
  'filter-change': [filters: Record<string, any>];
  'clear-filters': [];
}>();

// State
const searchValue = ref('');
const filterValues = reactive<Record<string, any>>({});
const openMultiSelects = reactive<Record<string, boolean>>({});
const isExpanded = ref(!props.collapsible);

// Initialize filter values
props.filters.forEach(filter => {
  switch (filter.type) {
    case 'select':
    case 'text':
      filterValues[filter.key] = '';
      break;
    case 'multi-select':
      filterValues[filter.key] = [];
      break;
    case 'date-range':
      filterValues[filter.key] = { from: '', to: '' };
      break;
    case 'number-range':
      filterValues[filter.key] = { min: null, max: null };
      break;
  }
});

// Computed
const containerClass = computed(() => {
  const base = 'flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-lg';
  
  const layoutClasses = {
    horizontal: 'flex-row flex-wrap',
    vertical: 'flex-col items-stretch',
    grid: 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3'
  };
  
  return cn(
    base,
    layoutClasses[props.layout],
    props.containerClass
  );
});

const activeFilterValues = computed(() => {
  const active: Record<string, any> = {};
  
  Object.entries(filterValues).forEach(([key, value]) => {
    if (value && value !== '' && JSON.stringify(value) !== JSON.stringify(getDefaultValue(key))) {
      active[key] = value;
    }
  });
  
  return active;
});

const hasActiveFilters = computed(() => {
  return searchValue.value !== '' || Object.keys(activeFilterValues.value).length > 0;
});

// Helper methods
const getDefaultValue = (key: string): any => {
  const filter = props.filters.find(f => f.key === key);
  if (!filter) return null;
  
  switch (filter.type) {
    case 'multi-select':
      return [];
    case 'date-range':
      return { from: '', to: '' };
    case 'number-range':
      return { min: null, max: null };
    default:
      return '';
  }
};

const getOptionLabel = (option: any): string => {
  return typeof option === 'object' ? option.label : String(option);
};

const getOptionValue = (option: any): any => {
  return typeof option === 'object' ? option.value : option;
};

const getFilterLabel = (key: string): string => {
  const filter = props.filters.find(f => f.key === key);
  return filter?.label || key;
};

const getFilterDisplayValue = (key: string, value: any): string => {
  const filter = props.filters.find(f => f.key === key);
  if (!filter) return String(value);
  
  switch (filter.type) {
    case 'multi-select':
      return Array.isArray(value) ? `${value.length} selected` : String(value);
    case 'date-range':
      return `${value.from || '...'} to ${value.to || '...'}`;
    case 'number-range':
      return `${value.min || '...'} - ${value.max || '...'}`;
    default:
      if (filter.type === 'select' && 'options' in filter) {
        const option = filter.options.find(opt => getOptionValue(opt) === value);
        return option ? getOptionLabel(option) : String(value);
      }
      return String(value);
  }
};

const getMultiSelectLabel = (filter: SelectFilter): string => {
  const selected = filterValues[filter.key] || [];
  if (selected.length === 0) return filter.placeholder || `Select ${filter.label}`;
  if (selected.length === 1) return getOptionLabel(
    filter.options.find(opt => getOptionValue(opt) === selected[0])
  );
  return `${selected.length} selected`;
};

const isOptionSelected = (filterKey: string, value: any): boolean => {
  const selected = filterValues[filterKey] || [];
  return selected.includes(value);
};

// Event handlers
const handleSearchChange = (): void => {
  emit('search-change', searchValue.value);
  emitFilterChange();
};

const handleFilterChange = (key: string, value: any): void => {
  filterValues[key] = value;
  emitFilterChange();
};

const handleDateRangeChange = (key: string): void => {
  emitFilterChange();
};

const handleNumberRangeChange = (key: string): void => {
  emitFilterChange();
};

const toggleMultiSelect = (key: string): void => {
  openMultiSelects[key] = !openMultiSelects[key];
};

const closeMultiSelect = (key: string): void => {
  openMultiSelects[key] = false;
};

const toggleMultiSelectOption = (filterKey: string, value: any): void => {
  const selected = filterValues[filterKey] || [];
  const index = selected.indexOf(value);
  
  if (index > -1) {
    selected.splice(index, 1);
  } else {
    selected.push(value);
  }
  
  emitFilterChange();
};

const clearSearch = (): void => {
  searchValue.value = '';
  emit('search-change', '');
  emitFilterChange();
};

const clearFilter = (key: string): void => {
  filterValues[key] = getDefaultValue(key);
  emitFilterChange();
};

const clearAllFilters = (): void => {
  searchValue.value = '';
  Object.keys(filterValues).forEach(key => {
    filterValues[key] = getDefaultValue(key);
  });
  
  emit('clear-filters');
  emit('search-change', '');
  emitFilterChange();
};

const emitFilterChange = (): void => {
  const allFilters = {
    search: searchValue.value,
    ...filterValues
  };
  emit('filter-change', allFilters);
};

// Click outside directive
const vClickOutside = {
  beforeMount(el: HTMLElement, binding: any) {
    el._clickOutside = (event: Event) => {
      if (!(el === event.target || el.contains(event.target as Node))) {
        binding.value();
      }
    };
    document.addEventListener('click', el._clickOutside);
  },
  unmounted(el: HTMLElement) {
    document.removeEventListener('click', el._clickOutside);
  }
};
</script>