<template>
  <div class="space-y-4">
    <!-- Table Container -->
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden relative">
      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="flex items-center space-x-3">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-orange-500"></div>
          <span class="text-gray-600">Loading...</span>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!filteredData.length" class="text-center py-12">
        <div class="text-gray-500">
          <div class="w-12 h-12 mx-auto mb-4 text-gray-300 bg-gray-200 rounded"></div>
          <p class="text-lg font-medium mb-2">No data available</p>
          <p class="text-sm">There are no records to display.</p>
        </div>
      </div>

      <!-- Data Table -->
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <!-- Table Header -->
          <thead class="bg-gray-50">
            <tr>
              <!-- Column Headers -->
              <th
                v-for="column in columns"
                :key="column.key"
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                <div class="flex items-center justify-between">
                  <span class="truncate">{{ column.label }}</span>
                  <span v-if="column.sortable" class="ml-1 text-gray-300 text-xs">↕</span>
                </div>
              </th>
            </tr>
          </thead>

          <!-- Table Body -->
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="(row, index) in paginatedData"
              :key="getRowKey(row, index)"
              class="hover:bg-gray-50"
            >
              <!-- Data Cells -->
              <td
                v-for="column in columns"
                :key="column.key"
                class="px-4 py-3 whitespace-nowrap"
              >
                <span class="text-sm text-gray-900">
                  {{ formatCellValue(row, column) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Simple Pagination Info -->
      <div v-if="showPagination && totalPages > 1" class="px-4 py-3 border-t border-gray-200">
        <div class="text-sm text-gray-700 text-center">
          Showing {{ startRecord }} to {{ endRecord }} of {{ totalRecords }} results
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

// Types
interface Column {
  key: string;
  label: string;
  sortable?: boolean;
  formatter?: (value: any, row: any) => string;
}

interface Props {
  data: Record<string, any>[];
  columns: Column[];
  loading?: boolean;
  perPage?: number;
  showPagination?: boolean;
  searchable?: boolean;
  exportable?: boolean;
  showColumnToggle?: boolean;
  showDensityToggle?: boolean;
  rowKey?: string;
}

const props = withDefaults(defineProps<Props>(), {
  perPage: 50,
  showPagination: true,
  searchable: true,
  exportable: true,
  showColumnToggle: true,
  showDensityToggle: true,
  rowKey: 'id',
});

// State
const currentPage = ref(1);
const currentPerPage = ref(props.perPage);

// Computed Properties
const filteredData = computed(() => {
  return [...props.data];
});

const totalRecords = computed(() => filteredData.value.length);
const totalPages = computed(() => {
  return Math.ceil(totalRecords.value / currentPerPage.value);
});

const startRecord = computed(() => {
  if (totalRecords.value === 0) return 0;
  return (currentPage.value - 1) * currentPerPage.value + 1;
});

const endRecord = computed(() => {
  const end = currentPage.value * currentPerPage.value;
  return Math.min(end, totalRecords.value);
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * currentPerPage.value;
  const end = start + currentPerPage.value;
  return filteredData.value.slice(start, end);
});

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
</script>