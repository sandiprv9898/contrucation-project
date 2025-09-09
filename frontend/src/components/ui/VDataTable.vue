<template>
  <div class="space-y-4">
    <!-- Table Header with Stats and Actions -->
    <div class="flex items-center justify-between" v-if="showHeader">
      <div class="flex items-center gap-6">
        <h2 v-if="title" class="text-xl font-bold text-gray-900">{{ title }}</h2>
        <div v-if="stats" class="flex items-center gap-4 text-sm text-gray-600">
          <span v-for="(value, key) in stats" :key="key">
            {{ key }}: <strong class="text-gray-900">{{ value }}</strong>
          </span>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <slot name="actions" />
      </div>
    </div>

    <!-- Filters and Search -->
    <div v-if="showFilters" class="flex items-center gap-2 pb-2">
      <slot name="filters">
        <!-- Search Input -->
        <VInput
          v-if="searchable"
          v-model="searchQuery"
          :placeholder="searchPlaceholder"
          class="max-w-xs"
          size="sm"
        >
          <template #icon>
            <Search class="w-4 h-4 text-gray-400" />
          </template>
        </VInput>
      </slot>
      
      <!-- Bulk Actions -->
      <div v-if="selectedRows.length > 0" class="flex items-center gap-2 ml-auto">
        <span class="text-sm text-gray-600">
          {{ selectedRows.length }} selected
        </span>
        <slot name="bulk-actions" :selected="selectedRows" />
      </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="flex items-center space-x-3">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-orange-500"></div>
          <span class="text-gray-600">{{ loadingText }}</span>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!filteredData.length" class="text-center py-12">
        <slot name="empty">
          <div class="text-gray-500">
            <component :is="emptyIcon" class="w-12 h-12 mx-auto mb-4 text-gray-300" />
            <p class="text-lg font-medium mb-2">{{ emptyTitle }}</p>
            <p class="text-sm">{{ emptyMessage }}</p>
          </div>
        </slot>
      </div>

      <!-- Data Table -->
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <!-- Table Header -->
          <thead class="bg-gray-50">
            <tr>
              <!-- Select All Checkbox -->
              <th v-if="selectable" class="w-4 px-4 py-3">
                <VCheckbox
                  :checked="isAllSelected"
                  :indeterminate="isIndeterminate"
                  @update:checked="toggleSelectAll"
                />
              </th>
              
              <!-- Column Headers -->
              <th
                v-for="column in columns"
                :key="column.key"
                :class="getHeaderClass(column)"
                :style="column.width ? { width: column.width } : {}"
              >
                <button
                  v-if="column.sortable"
                  @click="handleSort(column.key)"
                  class="group flex items-center space-x-1 text-left font-medium text-gray-900 hover:text-gray-700"
                >
                  <span>{{ column.label }}</span>
                  <div class="flex flex-col">
                    <ChevronUp 
                      class="w-3 h-3 -mb-1"
                      :class="getSortIconClass(column.key, 'asc')"
                    />
                    <ChevronDown 
                      class="w-3 h-3"
                      :class="getSortIconClass(column.key, 'desc')"
                    />
                  </div>
                </button>
                <span v-else class="font-medium text-gray-900">
                  {{ column.label }}
                </span>
              </th>
            </tr>
          </thead>

          <!-- Table Body -->
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="(row, index) in paginatedData"
              :key="getRowKey(row, index)"
              :class="getRowClass(row, index)"
              @click="handleRowClick(row, index)"
            >
              <!-- Selection Checkbox -->
              <td v-if="selectable" class="px-4 py-3">
                <VCheckbox
                  :checked="isRowSelected(row)"
                  @update:checked="toggleRowSelection(row)"
                  @click.stop
                />
              </td>
              
              <!-- Data Cells -->
              <td
                v-for="column in columns"
                :key="column.key"
                :class="getCellClass(column)"
              >
                <!-- Custom Cell Slot -->
                <slot
                  v-if="$slots[`cell-${column.key}`]"
                  :name="`cell-${column.key}`"
                  :row="row"
                  :value="getNestedValue(row, column.key)"
                  :index="index"
                />
                <!-- Default Cell Content -->
                <span v-else class="text-sm text-gray-900">
                  {{ formatCellValue(row, column) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="showPagination && totalPages > 1" class="px-4 py-3 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <!-- Results Info -->
          <div class="text-sm text-gray-700">
            Showing {{ startRecord }} to {{ endRecord }} of {{ totalRecords }} results
          </div>
          
          <!-- Pagination Controls -->
          <div class="flex items-center space-x-2">
            <!-- Per Page Selector -->
            <div class="flex items-center space-x-2">
              <span class="text-sm text-gray-700">Show:</span>
              <VSelect
                v-model="currentPerPage"
                :items="perPageOptions"
                size="sm"
                class="w-20"
                @change="handlePerPageChange"
              />
            </div>
            
            <!-- Page Navigation -->
            <div class="flex items-center space-x-1">
              <VButton
                variant="outline"
                size="sm"
                :disabled="currentPage === 1"
                @click="goToPage(1)"
              >
                <ChevronsLeft class="w-4 h-4" />
              </VButton>
              <VButton
                variant="outline"
                size="sm"
                :disabled="currentPage === 1"
                @click="goToPage(currentPage - 1)"
              >
                <ChevronLeft class="w-4 h-4" />
              </VButton>
              
              <!-- Page Numbers -->
              <VButton
                v-for="page in visiblePages"
                :key="page"
                :variant="page === currentPage ? 'default' : 'outline'"
                size="sm"
                class="w-8"
                @click="goToPage(page)"
              >
                {{ page }}
              </VButton>
              
              <VButton
                variant="outline"
                size="sm"
                :disabled="currentPage === totalPages"
                @click="goToPage(currentPage + 1)"
              >
                <ChevronRight class="w-4 h-4" />
              </VButton>
              <VButton
                variant="outline"
                size="sm"
                :disabled="currentPage === totalPages"
                @click="goToPage(totalPages)"
              >
                <ChevronsRight class="w-4 h-4" />
              </VButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { VButton, VCheckbox, VInput, VSelect } from '@/components/ui';
import { 
  Search, ChevronUp, ChevronDown, ChevronLeft, ChevronRight, 
  ChevronsLeft, ChevronsRight, Table 
} from 'lucide-vue-next';
import { cn } from '@/utils/cn';

// Types
interface Column {
  key: string;
  label: string;
  sortable?: boolean;
  width?: string;
  align?: 'left' | 'center' | 'right';
  formatter?: (value: any, row: any) => string;
}

interface SortState {
  column: string;
  direction: 'asc' | 'desc';
}

interface Props {
  // Data
  data: Record<string, any>[];
  columns: Column[];
  
  // Table Configuration
  title?: string;
  stats?: Record<string, string | number>;
  loading?: boolean;
  loadingText?: string;
  
  // Empty State
  emptyTitle?: string;
  emptyMessage?: string;
  emptyIcon?: any;
  
  // Features
  searchable?: boolean;
  searchPlaceholder?: string;
  selectable?: boolean;
  sortable?: boolean;
  
  // Pagination
  paginated?: boolean;
  perPage?: number;
  perPageOptions?: number[];
  
  // Display Options
  showHeader?: boolean;
  showFilters?: boolean;
  showPagination?: boolean;
  
  // Styling
  striped?: boolean;
  hoverable?: boolean;
  compact?: boolean;
  
  // Row Configuration
  rowKey?: string;
  clickableRows?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  loadingText: 'Loading...',
  emptyTitle: 'No data available',
  emptyMessage: 'There are no records to display.',
  emptyIcon: Table,
  searchPlaceholder: 'Search...',
  perPage: 50,
  perPageOptions: () => [10, 25, 50, 100, 250],
  showHeader: true,
  showFilters: true,
  showPagination: true,
  paginated: true,
  searchable: true,
  selectable: false,
  sortable: true,
  striped: true,
  hoverable: true,
  rowKey: 'id'
});

const emit = defineEmits<{
  'row-click': [row: any, index: number];
  'selection-change': [selectedRows: any[]];
  'sort-change': [sort: SortState];
  'page-change': [page: number];
  'per-page-change': [perPage: number];
}>();

// State
const searchQuery = ref('');
const sortState = ref<SortState>({ column: '', direction: 'asc' });
const selectedRows = ref<any[]>([]);
const currentPage = ref(1);
const currentPerPage = ref(props.perPage);

// Computed Properties
const filteredData = computed(() => {
  let filtered = [...props.data];
  
  // Apply search filter
  if (searchQuery.value && props.searchable) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(row => 
      props.columns.some(column => {
        const value = getNestedValue(row, column.key);
        return String(value).toLowerCase().includes(query);
      })
    );
  }
  
  // Apply sorting
  if (sortState.value.column && props.sortable) {
    filtered.sort((a, b) => {
      const aValue = getNestedValue(a, sortState.value.column);
      const bValue = getNestedValue(b, sortState.value.column);
      
      let comparison = 0;
      if (aValue > bValue) comparison = 1;
      if (aValue < bValue) comparison = -1;
      
      return sortState.value.direction === 'desc' ? -comparison : comparison;
    });
  }
  
  return filtered;
});

const totalRecords = computed(() => filteredData.value.length);
const totalPages = computed(() => {
  if (!props.paginated) return 1;
  return Math.ceil(totalRecords.value / currentPerPage.value);
});

const startRecord = computed(() => {
  if (!props.paginated || totalRecords.value === 0) return 0;
  return (currentPage.value - 1) * currentPerPage.value + 1;
});

const endRecord = computed(() => {
  if (!props.paginated) return totalRecords.value;
  const end = currentPage.value * currentPerPage.value;
  return Math.min(end, totalRecords.value);
});

const paginatedData = computed(() => {
  if (!props.paginated) return filteredData.value;
  
  const start = (currentPage.value - 1) * currentPerPage.value;
  const end = start + currentPerPage.value;
  return filteredData.value.slice(start, end);
});

const visiblePages = computed(() => {
  const pages = [];
  const maxVisible = 5;
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2));
  let end = Math.min(totalPages.value, start + maxVisible - 1);
  
  if (end - start < maxVisible - 1) {
    start = Math.max(1, end - maxVisible + 1);
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  
  return pages;
});

// Selection computed
const isAllSelected = computed(() => 
  paginatedData.value.length > 0 && 
  paginatedData.value.every(row => isRowSelected(row))
);

const isIndeterminate = computed(() => 
  selectedRows.value.length > 0 && !isAllSelected.value
);

// Methods
const getNestedValue = (obj: any, path: string): any => {
  return path.split('.').reduce((value, key) => value?.[key], obj) ?? '';
};

const getRowKey = (row: any, index: number): string | number => {
  return row[props.rowKey] || index;
};

const formatCellValue = (row: any, column: Column): string => {
  const value = getNestedValue(row, column.key);
  return column.formatter ? column.formatter(value, row) : String(value);
};

const isRowSelected = (row: any): boolean => {
  return selectedRows.value.some(selected => 
    getRowKey(selected, -1) === getRowKey(row, -1)
  );
};

const toggleRowSelection = (row: any): void => {
  const key = getRowKey(row, -1);
  const index = selectedRows.value.findIndex(selected => 
    getRowKey(selected, -1) === key
  );
  
  if (index > -1) {
    selectedRows.value.splice(index, 1);
  } else {
    selectedRows.value.push(row);
  }
  
  emit('selection-change', selectedRows.value);
};

const toggleSelectAll = (): void => {
  if (isAllSelected.value) {
    // Deselect all visible rows
    paginatedData.value.forEach(row => {
      const index = selectedRows.value.findIndex(selected => 
        getRowKey(selected, -1) === getRowKey(row, -1)
      );
      if (index > -1) {
        selectedRows.value.splice(index, 1);
      }
    });
  } else {
    // Select all visible rows
    paginatedData.value.forEach(row => {
      if (!isRowSelected(row)) {
        selectedRows.value.push(row);
      }
    });
  }
  
  emit('selection-change', selectedRows.value);
};

const handleSort = (column: string): void => {
  if (sortState.value.column === column) {
    sortState.value.direction = sortState.value.direction === 'asc' ? 'desc' : 'asc';
  } else {
    sortState.value.column = column;
    sortState.value.direction = 'asc';
  }
  
  emit('sort-change', { ...sortState.value });
};

const handleRowClick = (row: any, index: number): void => {
  if (props.clickableRows) {
    emit('row-click', row, index);
  }
};

const goToPage = (page: number): void => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    emit('page-change', page);
  }
};

const handlePerPageChange = (): void => {
  currentPage.value = 1;
  emit('per-page-change', currentPerPage.value);
};

// CSS Class Helpers
const getHeaderClass = (column: Column): string => {
  const base = 'px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider';
  const align = {
    left: 'text-left',
    center: 'text-center',
    right: 'text-right'
  };
  
  return cn(base, column.align ? align[column.align] : '');
};

const getCellClass = (column: Column): string => {
  const base = 'px-4 py-3 whitespace-nowrap';
  const align = {
    left: 'text-left',
    center: 'text-center',
    right: 'text-right'
  };
  
  return cn(base, column.align ? align[column.align] : '');
};

const getRowClass = (row: any, index: number): string => {
  const classes = [];
  
  if (props.striped && index % 2 === 0) classes.push('bg-gray-50');
  if (props.hoverable) classes.push('hover:bg-gray-100');
  if (props.clickableRows) classes.push('cursor-pointer');
  if (isRowSelected(row)) classes.push('bg-orange-50 border-l-4 border-orange-500');
  
  return cn(classes);
};

const getSortIconClass = (column: string, direction: 'asc' | 'desc'): string => {
  const base = 'transition-colors';
  const isActive = sortState.value.column === column && sortState.value.direction === direction;
  
  return cn(
    base,
    isActive ? 'text-orange-500' : 'text-gray-300 group-hover:text-gray-400'
  );
};

// Watchers
watch(() => props.data, () => {
  currentPage.value = 1;
  selectedRows.value = [];
});

watch(searchQuery, () => {
  currentPage.value = 1;
});
</script>