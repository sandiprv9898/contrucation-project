<template>
  <!-- Native Select Mode -->
  <select
    v-if="mode === 'native'"
    :id="fieldId"
    :value="modelValue"
    :disabled="disabled"
    :required="required"
    :class="computedSelectClass"
    v-bind="$attrs"
    @change="handleChange"
  >
    <option v-if="placeholder" value="" disabled>
      {{ placeholder }}
    </option>
    
    <!-- Default slot for custom options -->
    <slot>
      <!-- Auto-generated options from items prop -->
      <template v-for="item in processedItems" :key="getItemValue(item)">
        <!-- Group Header -->
        <optgroup v-if="item.isGroup" :label="item.label">
          <option
            v-for="child in item.children"
            :key="getItemValue(child)"
            :value="getItemValue(child)"
            :disabled="getItemDisabled(child)"
          >
            {{ getItemLabel(child) }}
          </option>
        </optgroup>
        
        <!-- Regular Option -->
        <option
          v-else
          :value="getItemValue(item)"
          :disabled="getItemDisabled(item)"
        >
          {{ getItemLabel(item) }}
        </option>
      </template>
    </slot>
  </select>
  
  <!-- Custom Dropdown Mode -->
  <div v-else class="relative" v-click-outside="closeDropdown">
    <!-- Trigger Button -->
    <button
      :id="fieldId"
      type="button"
      :disabled="disabled"
      :required="required"
      :class="computedSelectClass"
      @click="toggleDropdown"
      @keydown="handleKeydown"
    >
      <div class="flex items-center justify-between w-full">
        <div class="flex items-center space-x-2 flex-1 min-w-0">
          <!-- Selected Item Content -->
          <slot name="selected" :item="selectedItem" :value="modelValue">
            <div v-if="selectedItem" class="flex items-center space-x-2 min-w-0">
              <!-- Custom selected item rendering -->
              <component
                v-if="selectedItem.avatar"
                :is="'img'"
                :src="selectedItem.avatar"
                class="w-6 h-6 rounded-full flex-shrink-0"
                :alt="getItemLabel(selectedItem)"
              />
              <div v-if="selectedItem.icon" class="flex-shrink-0">
                <component :is="selectedItem.icon" class="w-5 h-5" />
              </div>
              <span class="truncate">{{ getItemLabel(selectedItem) }}</span>
              <VBadge v-if="selectedItem.badge" size="sm" :variant="selectedItem.badgeVariant">
                {{ selectedItem.badge }}
              </VBadge>
            </div>
            <span v-else class="text-gray-500">{{ placeholder || 'Select option...' }}</span>
          </slot>
        </div>
        
        <!-- Arrow Icon -->
        <ChevronDown 
          :class="[
            'w-4 h-4 transition-transform',
            isOpen ? 'rotate-180' : ''
          ]"
        />
      </div>
    </button>
    
    <!-- Dropdown Panel -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div
        v-if="isOpen"
        :class="dropdownClass"
      >
        <!-- Search Input -->
        <div v-if="searchable" class="p-2 border-b border-gray-200">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              ref="searchInput"
              v-model="searchQuery"
              type="text"
              :placeholder="searchPlaceholder"
              class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
              @keydown="handleSearchKeydown"
            />
            <button
              v-if="searchQuery"
              @click="clearSearch"
              class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <X class="w-4 h-4" />
            </button>
          </div>
        </div>
        
        <!-- Options Container -->
        <div :class="optionsContainerClass">
          <!-- No Results -->
          <div v-if="filteredItems.length === 0" class="px-3 py-2 text-sm text-gray-500 text-center">
            {{ searchQuery ? 'No results found' : 'No options available' }}
          </div>
          
          <!-- Options List -->
          <div v-else>
            <template v-for="(item, index) in filteredItems" :key="getItemValue(item) || `group-${index}`">
              <!-- Group Header -->
              <div v-if="item.isGroup" class="px-3 py-2 text-xs font-semibold text-gray-700 bg-gray-50 border-b border-gray-100">
                {{ item.label }}
              </div>
              
              <!-- Group Items -->
              <template v-else-if="item.isGroupChild">
                <button
                  type="button"
                  :class="getOptionClass(item, index)"
                  :disabled="getItemDisabled(item)"
                  @click="selectItem(item)"
                  @mouseenter="highlightedIndex = index"
                >
                  <slot name="option" :item="item" :selected="isSelected(item)">
                    <div class="flex items-center justify-between w-full">
                      <div class="flex items-center space-x-2 min-w-0">
                        <!-- Avatar -->
                        <img
                          v-if="item.avatar"
                          :src="item.avatar"
                          class="w-6 h-6 rounded-full flex-shrink-0"
                          :alt="getItemLabel(item)"
                        />
                        <!-- Icon -->
                        <div v-if="item.icon" class="flex-shrink-0">
                          <component :is="item.icon" class="w-5 h-5" />
                        </div>
                        <!-- Label and Description -->
                        <div class="min-w-0">
                          <div class="truncate font-medium">{{ getItemLabel(item) }}</div>
                          <div v-if="item.description" class="text-xs text-gray-500 truncate">
                            {{ item.description }}
                          </div>
                        </div>
                      </div>
                      
                      <!-- Right Content -->
                      <div class="flex items-center space-x-2 flex-shrink-0">
                        <!-- Badge -->
                        <VBadge v-if="item.badge" size="sm" :variant="item.badgeVariant">
                          {{ item.badge }}
                        </VBadge>
                        <!-- Selected Indicator -->
                        <Check v-if="isSelected(item)" class="w-4 h-4 text-orange-500" />
                      </div>
                    </div>
                  </slot>
                </button>
              </template>
              
              <!-- Regular Option -->
              <button
                v-else
                type="button"
                :class="getOptionClass(item, index)"
                :disabled="getItemDisabled(item)"
                @click="selectItem(item)"
                @mouseenter="highlightedIndex = index"
              >
                <slot name="option" :item="item" :selected="isSelected(item)">
                  <div class="flex items-center justify-between w-full">
                    <div class="flex items-center space-x-2 min-w-0">
                      <!-- Avatar -->
                      <img
                        v-if="item.avatar"
                        :src="item.avatar"
                        class="w-6 h-6 rounded-full flex-shrink-0"
                        :alt="getItemLabel(item)"
                      />
                      <!-- Icon -->
                      <div v-if="item.icon" class="flex-shrink-0">
                        <component :is="item.icon" class="w-5 h-5" />
                      </div>
                      <!-- Label and Description -->
                      <div class="min-w-0">
                        <div class="truncate font-medium">{{ getItemLabel(item) }}</div>
                        <div v-if="item.description" class="text-xs text-gray-500 truncate">
                          {{ item.description }}
                        </div>
                      </div>
                    </div>
                    
                    <!-- Right Content -->
                    <div class="flex items-center space-x-2 flex-shrink-0">
                      <!-- Badge -->
                      <VBadge v-if="item.badge" size="sm" :variant="item.badgeVariant">
                        {{ item.badge }}
                      </VBadge>
                      <!-- Selected Indicator -->
                      <Check v-if="isSelected(item)" class="w-4 h-4 text-orange-500" />
                    </div>
                  </div>
                </slot>
              </button>
            </template>
          </div>
        </div>
        
        <!-- Footer Slot -->
        <div v-if="$slots.footer" class="border-t border-gray-200 p-2">
          <slot name="footer" />
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { computed, useId, ref, nextTick, watch } from 'vue';
import { VBadge } from '@/components/ui';
import { ChevronDown, Search, X, Check } from 'lucide-vue-next';
import { cn } from '@/utils/cn';

// Define types for select items
interface SelectItem {
  label: string;
  value: string | number;
  disabled?: boolean;
  description?: string;
  icon?: any;
  avatar?: string;
  badge?: string;
  badgeVariant?: 'default' | 'success' | 'warning' | 'danger' | 'info';
  group?: string;
  isGroup?: boolean;
  isGroupChild?: boolean;
  children?: SelectItem[];
}

interface SelectGroup {
  label: string;
  children: SelectItem[];
  isGroup: true;
}

interface Props {
  modelValue?: string | number | null;
  items?: SelectItem[] | string[] | number[] | SelectGroup[];
  placeholder?: string;
  disabled?: boolean;
  required?: boolean;
  size?: 'sm' | 'md' | 'lg';
  variant?: 'default' | 'compact';
  hasError?: boolean;
  
  // UI Mode
  mode?: 'native' | 'custom';
  
  // Search functionality
  searchable?: boolean;
  searchPlaceholder?: string;
  
  // Grouping
  groupBy?: string;
  
  // Styling
  selectClass?: string;
  dropdownClass?: string;
  optionsContainerClass?: string;
  
  // For complex objects
  itemLabel?: string;
  itemValue?: string;
  itemDisabled?: string;
  itemGroup?: string;
}

const props = withDefaults(defineProps<Props>(), {
  items: () => [],
  size: 'md',
  variant: 'default',
  mode: 'native',
  searchable: false,
  searchPlaceholder: 'Search options...',
  itemLabel: 'label',
  itemValue: 'value',
  itemDisabled: 'disabled',
  itemGroup: 'group',
  dropdownClass: 'absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-hidden',
  optionsContainerClass: 'overflow-y-auto max-h-48'
});

const emit = defineEmits<{
  'update:modelValue': [value: string | number | null];
  change: [value: string | number | null];
}>();

// State
const isOpen = ref(false);
const searchQuery = ref('');
const highlightedIndex = ref(-1);
const searchInput = ref<HTMLInputElement>();

// Generate unique field ID
const fieldId = useId();

// Helper functions for different item types
const getItemLabel = (item: any): string => {
  if (typeof item === 'string' || typeof item === 'number') {
    return String(item);
  }
  return item[props.itemLabel] || item.label || String(item);
};

const getItemValue = (item: any): string | number => {
  if (typeof item === 'string' || typeof item === 'number') {
    return item;
  }
  return item[props.itemValue] || item.value || item;
};

const getItemDisabled = (item: any): boolean => {
  if (typeof item === 'string' || typeof item === 'number') {
    return false;
  }
  return item[props.itemDisabled] || item.disabled || false;
};

// Computed select classes
const computedSelectClass = computed(() => {
  if (props.mode === 'native') {
    const baseClasses = 'block w-full border rounded-md bg-white text-sm transition-all duration-200 focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none bg-no-repeat bg-right bg-select-arrow';
    
    const sizeClasses = {
      sm: 'h-8 px-2 pr-8 text-xs',
      md: 'h-10 px-3 pr-10 text-sm',
      lg: 'h-12 px-4 pr-12 text-sm'
    };
    
    const variantClasses = {
      default: 'border-gray-200 hover:border-gray-300 focus:border-orange-500 focus:ring-orange-500/20',
      compact: 'border-gray-200 hover:border-gray-300 focus:border-orange-500 focus:ring-orange-500/10'
    };
    
    const errorClasses = props.hasError
      ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
      : variantClasses[props.variant];

    return cn(
      baseClasses,
      sizeClasses[props.size],
      errorClasses,
      props.selectClass
    );
  } else {
    // Custom dropdown mode
    const baseClasses = 'block w-full border rounded-md bg-white text-sm transition-all duration-200 focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:opacity-50 text-left';
    
    const sizeClasses = {
      sm: 'h-8 px-2 text-xs',
      md: 'h-10 px-3 text-sm',
      lg: 'h-12 px-4 text-sm'
    };
    
    const variantClasses = {
      default: 'border-gray-200 hover:border-gray-300 focus:border-orange-500 focus:ring-orange-500/20',
      compact: 'border-gray-200 hover:border-gray-300 focus:border-orange-500 focus:ring-orange-500/10'
    };
    
    const errorClasses = props.hasError
      ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
      : variantClasses[props.variant];
    
    const openClasses = isOpen.value 
      ? 'border-orange-500 ring-2 ring-orange-500/20'
      : '';

    return cn(
      baseClasses,
      sizeClasses[props.size],
      errorClasses,
      openClasses,
      props.selectClass
    );
  }
});

// Computed properties
const selectedItem = computed(() => {
  if (!props.modelValue) return null;
  return processedItems.value.find(item => 
    !item.isGroup && getItemValue(item) === props.modelValue
  );
});

const processedItems = computed(() => {
  if (props.groupBy) {
    return groupItems(props.items);
  }
  
  // Handle groups passed directly
  const result: SelectItem[] = [];
  
  props.items.forEach((item: any) => {
    if (item.isGroup && item.children) {
      // Add group header
      result.push({ ...item, isGroup: true });
      // Add group children
      item.children.forEach((child: SelectItem) => {
        result.push({ ...child, isGroupChild: true });
      });
    } else {
      result.push(item);
    }
  });
  
  return result;
});

const filteredItems = computed(() => {
  if (!props.searchable || !searchQuery.value) {
    return processedItems.value;
  }
  
  const query = searchQuery.value.toLowerCase();
  return processedItems.value.filter(item => {
    if (item.isGroup) return true; // Always show group headers
    return getItemLabel(item).toLowerCase().includes(query);
  });
});

// Helper methods
const groupItems = (items: any[]) => {
  if (!props.groupBy) return items;
  
  const groups: Record<string, SelectItem[]> = {};
  const ungrouped: SelectItem[] = [];
  
  items.forEach(item => {
    const groupKey = item[props.groupBy!];
    if (groupKey) {
      if (!groups[groupKey]) groups[groupKey] = [];
      groups[groupKey].push({ ...item, isGroupChild: true });
    } else {
      ungrouped.push(item);
    }
  });
  
  const result: SelectItem[] = [];
  
  // Add ungrouped items first
  result.push(...ungrouped);
  
  // Add grouped items
  Object.entries(groups).forEach(([groupName, groupItems]) => {
    result.push({ label: groupName, value: '', isGroup: true } as SelectItem);
    result.push(...groupItems);
  });
  
  return result;
};

const isSelected = (item: SelectItem): boolean => {
  return getItemValue(item) === props.modelValue;
};

const getOptionClass = (item: SelectItem, index: number): string => {
  const base = 'w-full px-3 py-2 text-left text-sm hover:bg-gray-50 focus:outline-none focus:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed';
  
  const selectedClasses = isSelected(item) 
    ? 'bg-orange-50 text-orange-900'
    : 'text-gray-900';
    
  const highlightedClasses = highlightedIndex.value === index
    ? 'bg-gray-100'
    : '';
  
  const groupChildClasses = item.isGroupChild ? 'pl-6' : '';
  
  return cn(base, selectedClasses, highlightedClasses, groupChildClasses);
};

// Event handlers
const handleChange = (event: Event) => {
  const target = event.target as HTMLSelectElement;
  const value = target.value || null;
  emit('update:modelValue', value);
  emit('change', value);
};

const toggleDropdown = () => {
  if (props.disabled) return;
  isOpen.value = !isOpen.value;
  
  if (isOpen.value && props.searchable) {
    nextTick(() => {
      searchInput.value?.focus();
    });
  }
};

const closeDropdown = () => {
  isOpen.value = false;
  searchQuery.value = '';
  highlightedIndex.value = -1;
};

const selectItem = (item: SelectItem) => {
  if (getItemDisabled(item)) return;
  
  const value = getItemValue(item);
  emit('update:modelValue', value);
  emit('change', value);
  closeDropdown();
};

const clearSearch = () => {
  searchQuery.value = '';
  searchInput.value?.focus();
};

const handleKeydown = (event: KeyboardEvent) => {
  if (props.disabled) return;
  
  switch (event.key) {
    case 'Enter':
    case ' ':
      event.preventDefault();
      toggleDropdown();
      break;
    case 'Escape':
      closeDropdown();
      break;
    case 'ArrowDown':
      event.preventDefault();
      if (!isOpen.value) {
        toggleDropdown();
      } else {
        navigateOptions(1);
      }
      break;
    case 'ArrowUp':
      event.preventDefault();
      if (isOpen.value) {
        navigateOptions(-1);
      }
      break;
  }
};

const handleSearchKeydown = (event: KeyboardEvent) => {
  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault();
      navigateOptions(1);
      break;
    case 'ArrowUp':
      event.preventDefault();
      navigateOptions(-1);
      break;
    case 'Enter':
      event.preventDefault();
      if (highlightedIndex.value >= 0) {
        const item = filteredItems.value[highlightedIndex.value];
        if (item && !item.isGroup) {
          selectItem(item);
        }
      }
      break;
    case 'Escape':
      closeDropdown();
      break;
  }
};

const navigateOptions = (direction: number) => {
  const selectableItems = filteredItems.value.filter(item => !item.isGroup && !getItemDisabled(item));
  if (selectableItems.length === 0) return;
  
  let newIndex = highlightedIndex.value + direction;
  
  // Find the next selectable item
  while (newIndex >= 0 && newIndex < filteredItems.value.length) {
    const item = filteredItems.value[newIndex];
    if (!item.isGroup && !getItemDisabled(item)) {
      highlightedIndex.value = newIndex;
      return;
    }
    newIndex += direction;
  }
  
  // Wrap around
  if (direction > 0) {
    highlightedIndex.value = filteredItems.value.findIndex(item => !item.isGroup && !getItemDisabled(item));
  } else {
    const selectableIndices = filteredItems.value
      .map((item, index) => ({ item, index }))
      .filter(({ item }) => !item.isGroup && !getItemDisabled(item))
      .map(({ index }) => index);
    highlightedIndex.value = selectableIndices[selectableIndices.length - 1] || -1;
  }
};

// Watch for search query changes
watch(searchQuery, () => {
  highlightedIndex.value = -1;
});

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

// Expose methods and properties for external use
defineExpose({ 
  fieldId, 
  focus: () => {
    if (props.mode === 'custom') {
      toggleDropdown();
    }
  },
  blur: () => {
    if (props.mode === 'custom') {
      closeDropdown();
    }
  }
});
</script>

<style scoped>
/* Custom select arrow */
select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-size: 1.25em 1.25em;
}

select:focus {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23f97316' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
}
</style>