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
        <!-- Export Button -->
        <VButton
          v-if="exportable"
          variant="outline"
          size="sm"
          @click="exportData"
          :loading="isExporting"
        >
          <Download class="w-4 h-4 mr-1" />
          Export
        </VButton>
        
        <!-- Column Visibility Toggle -->
        <VButton
          v-if="showColumnToggle"
          variant="outline"
          size="sm"
          @click="showColumnModal = true"
        >
          <Settings class="w-4 h-4 mr-1" />
          Columns
        </VButton>
        
        <!-- Density Toggle -->
        <VButton
          v-if="showDensityToggle"
          variant="outline"
          size="sm"
          @click="cycleDensity"
        >
          <component :is="densityIcons[density]" class="w-4 h-4 mr-1" />
          {{ densityLabels[density] }}
        </VButton>
        
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

    <!-- Advanced Filter Bar -->
    <VFilterBar
      v-if="showAdvancedFilters && advancedFilters.length > 0"
      :filters="advancedFilters"
      :searchable="false"
      layout="grid"
      size="sm"
      @filter-change="handleAdvancedFilterChange"
      @clear-filters="clearAdvancedFilters"
    />
    
    <!-- Table Container -->
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden relative">
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
      <div v-else class="overflow-x-auto" :class="tableContainerClass">
        <table class="min-w-full divide-y divide-gray-200" :class="tableClass">
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
                v-for="(column, columnIndex) in visibleColumns"
                :key="column.key"
                :class="getHeaderClass(column)"
                :style="getColumnStyle(column)"
                ref="headerRefs"
              >
                <div class="flex items-center justify-between relative">
                  <div class="flex items-center min-w-0 flex-1">
                    <button
                      v-if="column.sortable"
                      @click="handleSort(column.key)"
                      class="group flex items-center space-x-1 text-left font-medium text-gray-900 hover:text-gray-700 min-w-0 flex-1"
                    >
                      <span class="truncate">{{ column.label }}</span>
                      <div class="flex flex-col flex-shrink-0">
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
                    <span v-else class="font-medium text-gray-900 truncate">
                      {{ column.label }}
                    </span>
                    
                    <!-- Column Filter -->
                    <button
                      v-if="column.filterable"
                      @click="toggleColumnFilter(column.key)"
                      :class="[
                        'ml-2 p-1 rounded text-gray-400 hover:text-gray-600',
                        hasColumnFilter(column.key) ? 'text-orange-500' : ''
                      ]"
                    >
                      <Filter class="w-3 h-3" />
                    </button>
                  </div>
                  
                  <!-- Column Resize Handle -->
                  <div
                    v-if="resizable && columnIndex < visibleColumns.length - 1"
                    class="absolute right-0 top-0 h-full w-1 cursor-col-resize hover:bg-orange-300 bg-transparent group"
                    @mousedown="startResize(column.key, $event)"
                  >
                    <div class="absolute right-0 top-1/2 transform -translate-y-1/2 w-0.5 h-4 bg-gray-300 group-hover:bg-orange-400"></div>
                  </div>
                </div>
                
                <!-- Column Filter Dropdown -->
                <div
                  v-if="columnFilters[column.key]?.show"
                  v-click-outside="() => closeColumnFilter(column.key)"
                  class="absolute top-full left-0 mt-1 bg-white border border-gray-200 rounded-md shadow-lg p-2 z-20 min-w-48"
                >
                  <VInput
                    v-model="columnFilters[column.key].value"
                    :placeholder="`Filter ${column.label}...`"
                    size="sm"
                    @input="applyColumnFilter(column.key)"
                  >
                    <template #suffix>
                      <button
                        v-if="columnFilters[column.key].value"
                        @click="clearColumnFilter(column.key)"
                        class="text-gray-400 hover:text-gray-600"
                      >
                        <X class="w-3 h-3" />
                      </button>
                    </template>
                  </VInput>
                </div>
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
                v-for="column in visibleColumns"
                :key="column.key"
                :class="getCellClass(column)"
                :style="getColumnStyle(column)"
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
    
    <!-- Column Visibility Modal -->
    <VModal
      v-model="showColumnModal"
      title="Column Visibility"
      size="sm"
      :show-footer="true"
    >
      <div class="space-y-2">
        <div
          v-for="column in columns"
          :key="column.key"
          class="flex items-center justify-between p-2 hover:bg-gray-50 rounded"
        >
          <span class="text-sm font-medium">{{ column.label }}</span>
          <VCheckbox
            :checked="!hiddenColumns.includes(column.key)"
            @update:checked="toggleColumnVisibility(column.key)"
          />
        </div>
      </div>
      
      <template #footer>
        <VButton variant="outline" @click="showAllColumns">
          Show All
        </VButton>
        <VButton variant="outline" @click="hideAllColumns">
          Hide All
        </VButton>
        <VButton @click="showColumnModal = false">
          Done
        </VButton>
      </template>
    </VModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, reactive, nextTick } from 'vue';
import { VButton, VCheckbox, VInput, VSelect, VModal, VFilterBar } from '@/components/ui';
import { 
  Search, ChevronUp, ChevronDown, ChevronLeft, ChevronRight, 
  ChevronsLeft, ChevronsRight, Table, Download, Settings, Filter,
  X, Menu, Grid, List
} from 'lucide-vue-next';
import { cn } from '@/utils/cn';

// Types
interface Column {
  key: string;
  label: string;
  sortable?: boolean;
  filterable?: boolean;
  resizable?: boolean;
  width?: string | number;
  minWidth?: string | number;
  maxWidth?: string | number;
  align?: 'left' | 'center' | 'right';
  formatter?: (value: any, row: any) => string;
  exportable?: boolean;
}

interface AdvancedFilter {
  key: string;
  label: string;
  type: 'select' | 'multi-select' | 'date-range' | 'number-range';
  options?: any[];
  placeholder?: string;
}

interface ExportOptions {
  format: 'csv' | 'excel' | 'json';
  filename?: string;
  includeHeaders?: boolean;
  selectedOnly?: boolean;
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
  resizable?: boolean;
  exportable?: boolean;
  
  // Advanced Features
  showAdvancedFilters?: boolean;
  advancedFilters?: AdvancedFilter[];
  showColumnToggle?: boolean;
  showDensityToggle?: boolean;
  
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
  density?: 'compact' | 'standard' | 'comfortable';
  
  // Row Configuration
  rowKey?: string;
  clickableRows?: boolean;
  
  // Export Options
  exportOptions?: ExportOptions;
}

const props = withDefaults(defineProps<Props>(), {
  loadingText: 'Loading...',
  emptyTitle: 'No data available',
  emptyMessage: 'There are no records to display.',
  emptyIcon: Table,
  searchPlaceholder: 'Search...',
  perPage: 50,
  perPageOptions: () => [10, 25, 50, 100, 250],
  advancedFilters: () => [],
  showHeader: true,
  showFilters: true,
  showPagination: true,
  showAdvancedFilters: false,
  showColumnToggle: true,
  showDensityToggle: true,
  paginated: true,
  searchable: true,
  selectable: false,
  sortable: true,
  resizable: true,
  exportable: true,
  striped: true,
  hoverable: true,
  density: 'standard',
  rowKey: 'id',
  exportOptions: () => ({
    format: 'csv',
    filename: 'table-export',
    includeHeaders: true,
    selectedOnly: false
  })
});

const emit = defineEmits<{
  'row-click': [row: any, index: number];
  'selection-change': [selectedRows: any[]];
  'sort-change': [sort: SortState];
  'page-change': [page: number];
  'per-page-change': [perPage: number];
  'export-start': [options: ExportOptions];
  'export-complete': [data: any[], options: ExportOptions];
  'column-resize': [columnKey: string, width: number];
  'advanced-filter-change': [filters: Record<string, any>];
}>();

// State
const searchQuery = ref('');
const sortState = ref<SortState>({ column: '', direction: 'asc' });
const selectedRows = ref<any[]>([]);
const currentPage = ref(1);
const currentPerPage = ref(props.perPage);

// Advanced Features State
const hiddenColumns = ref<string[]>([]);
const columnWidths = reactive<Record<string, number>>({});
const columnFilters = reactive<Record<string, { show: boolean; value: string }>>({});
const advancedFilterValues = reactive<Record<string, any>>({});
const showColumnModal = ref(false);
const isExporting = ref(false);
const density = ref(props.density);

// Resize state
const isResizing = ref(false);
const resizeColumn = ref('');
const startX = ref(0);
const startWidth = ref(0);

// Refs
const headerRefs = ref<HTMLElement[]>([]);

// Computed Properties
const visibleColumns = computed(() => {
  return props.columns.filter(column => !hiddenColumns.value.includes(column.key));
});

const tableContainerClass = computed(() => {
  return cn('overflow-x-auto', {
    'min-h-0': density.value === 'compact'
  });
});

const tableClass = computed(() => {
  return cn('min-w-full divide-y divide-gray-200', {
    'table-fixed': props.resizable
  });
});

const densityIcons = {
  compact: List,
  standard: Menu,
  comfortable: Grid
};

const densityLabels = {
  compact: 'Compact',
  standard: 'Standard', 
  comfortable: 'Comfortable'
};

const filteredData = computed(() => {
  let filtered = [...props.data];
  
  // Apply search filter
  if (searchQuery.value && props.searchable) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(row => 
      visibleColumns.value.some(column => {
        const value = getNestedValue(row, column.key);
        return String(value).toLowerCase().includes(query);
      })
    );
  }
  
  // Apply column filters
  Object.entries(columnFilters).forEach(([columnKey, filter]) => {
    if (filter.value) {
      const query = filter.value.toLowerCase();
      filtered = filtered.filter(row => {
        const value = getNestedValue(row, columnKey);
        return String(value).toLowerCase().includes(query);
      });
    }
  });
  
  // Apply advanced filters
  Object.entries(advancedFilterValues).forEach(([key, value]) => {
    if (value && value !== '') {
      const filter = props.advancedFilters.find(f => f.key === key);
      if (filter) {
        filtered = applyAdvancedFilter(filtered, filter, value);
      }
    }
  });
  
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

const getColumnStyle = (column: Column) => {
  const styles: Record<string, any> = {};
  
  if (column.width) {
    styles.width = typeof column.width === 'number' ? `${column.width}px` : column.width;
  } else if (columnWidths[column.key]) {
    styles.width = `${columnWidths[column.key]}px`;
  }
  
  if (column.minWidth) {
    styles.minWidth = typeof column.minWidth === 'number' ? `${column.minWidth}px` : column.minWidth;
  }
  
  if (column.maxWidth) {
    styles.maxWidth = typeof column.maxWidth === 'number' ? `${column.maxWidth}px` : column.maxWidth;
  }
  
  return styles;
};

const applyAdvancedFilter = (data: any[], filter: AdvancedFilter, value: any): any[] => {
  switch (filter.type) {
    case 'select':
      return data.filter(row => getNestedValue(row, filter.key) === value);
    case 'multi-select':
      return Array.isArray(value) && value.length > 0
        ? data.filter(row => value.includes(getNestedValue(row, filter.key)))
        : data;
    case 'date-range':
      return data.filter(row => {
        const rowDate = new Date(getNestedValue(row, filter.key));
        const fromDate = value.from ? new Date(value.from) : null;
        const toDate = value.to ? new Date(value.to) : null;
        
        if (fromDate && rowDate < fromDate) return false;
        if (toDate && rowDate > toDate) return false;
        return true;
      });
    case 'number-range':
      return data.filter(row => {
        const rowValue = parseFloat(getNestedValue(row, filter.key));
        const minValue = value.min !== null ? parseFloat(value.min) : null;
        const maxValue = value.max !== null ? parseFloat(value.max) : null;
        
        if (minValue !== null && rowValue < minValue) return false;
        if (maxValue !== null && rowValue > maxValue) return false;
        return true;
      });
    default:
      return data;
  }
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

// Advanced Features Methods
const toggleColumnVisibility = (columnKey: string): void => {
  const index = hiddenColumns.value.indexOf(columnKey);
  if (index > -1) {
    hiddenColumns.value.splice(index, 1);
  } else {
    hiddenColumns.value.push(columnKey);
  }
};

const showAllColumns = (): void => {
  hiddenColumns.value = [];
};

const hideAllColumns = (): void => {
  hiddenColumns.value = props.columns.map(col => col.key);
};

const cycleDensity = (): void => {
  const densities: Array<'compact' | 'standard' | 'comfortable'> = ['compact', 'standard', 'comfortable'];
  const currentIndex = densities.indexOf(density.value);
  density.value = densities[(currentIndex + 1) % densities.length];
};

const exportData = async (): Promise<void> => {
  isExporting.value = true;
  emit('export-start', props.exportOptions);
  
  try {
    const dataToExport = props.exportOptions.selectedOnly && selectedRows.value.length > 0
      ? selectedRows.value
      : filteredData.value;
    
    const exportData = dataToExport.map(row => {
      const exportRow: Record<string, any> = {};
      visibleColumns.value.forEach(column => {
        if (column.exportable !== false) {
          exportRow[column.label] = formatCellValue(row, column);
        }
      });
      return exportRow;
    });
    
    if (props.exportOptions.format === 'csv') {
      downloadCSV(exportData, props.exportOptions.filename || 'export');
    } else if (props.exportOptions.format === 'json') {
      downloadJSON(exportData, props.exportOptions.filename || 'export');
    }
    
    emit('export-complete', exportData, props.exportOptions);
  } finally {
    isExporting.value = false;
  }
};

const downloadCSV = (data: any[], filename: string): void => {
  if (data.length === 0) return;
  
  const headers = Object.keys(data[0]);
  const csvContent = [
    props.exportOptions.includeHeaders ? headers.join(',') : '',
    ...data.map(row => headers.map(header => 
      `"${String(row[header]).replace(/"/g, '""')}"`
    ).join(','))
  ].filter(row => row).join('\n');
  
  downloadFile(csvContent, `${filename}.csv`, 'text/csv');
};

const downloadJSON = (data: any[], filename: string): void => {
  const jsonContent = JSON.stringify(data, null, 2);
  downloadFile(jsonContent, `${filename}.json`, 'application/json');
};

const downloadFile = (content: string, filename: string, mimeType: string): void => {
  const blob = new Blob([content], { type: mimeType });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
};

// Column Filter Methods
const toggleColumnFilter = (columnKey: string): void => {
  if (!columnFilters[columnKey]) {
    columnFilters[columnKey] = { show: false, value: '' };
  }
  columnFilters[columnKey].show = !columnFilters[columnKey].show;
};

const closeColumnFilter = (columnKey: string): void => {
  if (columnFilters[columnKey]) {
    columnFilters[columnKey].show = false;
  }
};

const hasColumnFilter = (columnKey: string): boolean => {
  return columnFilters[columnKey]?.value ? columnFilters[columnKey].value.length > 0 : false;
};

const applyColumnFilter = (columnKey: string): void => {
  currentPage.value = 1;
};

const clearColumnFilter = (columnKey: string): void => {
  if (columnFilters[columnKey]) {
    columnFilters[columnKey].value = '';
    currentPage.value = 1;
  }
};

// Advanced Filter Methods
const handleAdvancedFilterChange = (filters: Record<string, any>): void => {
  Object.assign(advancedFilterValues, filters);
  currentPage.value = 1;
  emit('advanced-filter-change', filters);
};

const clearAdvancedFilters = (): void => {
  Object.keys(advancedFilterValues).forEach(key => {
    delete advancedFilterValues[key];
  });
  currentPage.value = 1;
};

// Column Resize Methods
const startResize = (columnKey: string, event: MouseEvent): void => {
  event.preventDefault();
  event.stopPropagation();
  
  isResizing.value = true;
  resizeColumn.value = columnKey;
  startX.value = event.clientX;
  
  const headerEl = headerRefs.value.find(el => el.dataset?.columnKey === columnKey);
  if (headerEl) {
    startWidth.value = headerEl.offsetWidth;
  }
  
  document.addEventListener('mousemove', handleResize);
  document.addEventListener('mouseup', stopResize);
  document.body.style.cursor = 'col-resize';
  document.body.style.userSelect = 'none';
};

const handleResize = (event: MouseEvent): void => {
  if (!isResizing.value) return;
  
  const diff = event.clientX - startX.value;
  const newWidth = Math.max(50, startWidth.value + diff); // Minimum 50px width
  
  columnWidths[resizeColumn.value] = newWidth;
  emit('column-resize', resizeColumn.value, newWidth);
};

const stopResize = (): void => {
  isResizing.value = false;
  resizeColumn.value = '';
  
  document.removeEventListener('mousemove', handleResize);
  document.removeEventListener('mouseup', stopResize);
  document.body.style.cursor = '';
  document.body.style.userSelect = '';
};

// CSS Class Helpers
const getHeaderClass = (column: Column): string => {
  let base = 'px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider relative';
  
  // Adjust padding based on density
  if (density.value === 'compact') {
    base = base.replace('px-4', 'px-2').replace('py-3', 'py-1');
  } else if (density.value === 'comfortable') {
    base = base.replace('py-3', 'py-4');
  } else {
    base += ' py-3';
  }
  
  const align = {
    left: 'text-left',
    center: 'text-center',
    right: 'text-right'
  };
  
  return cn(base, column.align ? align[column.align] : '');
};

const getCellClass = (column: Column): string => {
  let base = 'px-4 whitespace-nowrap';
  
  // Adjust padding based on density
  if (density.value === 'compact') {
    base = base.replace('px-4', 'px-2') + ' py-1';
  } else if (density.value === 'comfortable') {
    base += ' py-4';
  } else {
    base += ' py-3';
  }
  
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