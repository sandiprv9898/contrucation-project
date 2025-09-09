<template>
  <div class="space-y-4">
    <!-- Simple debugging table -->
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
      <!-- Debug Info -->
      <div class="p-4 bg-blue-50">
        <p>🔍 VDataTableSimple mounted and working!</p>
        <p>📊 Data received: {{ data?.length || 0 }} items</p>
        <p>📋 Columns: {{ columns?.length || 0 }}</p>
      </div>
      
      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="flex items-center space-x-3">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-orange-500"></div>
          <span class="text-gray-600">Loading...</span>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!data?.length" class="text-center py-12">
        <div class="text-gray-500">
          <p class="text-lg font-medium mb-2">No data available</p>
          <p class="text-sm">There are no records to display.</p>
        </div>
      </div>

      <!-- Simple Data Table -->
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <!-- Table Header -->
          <thead class="bg-gray-50">
            <tr>
              <th
                v-for="column in columns"
                :key="column.key"
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                {{ column.label }}
              </th>
            </tr>
          </thead>

          <!-- Table Body -->
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="(row, index) in data"
              :key="row.id || index"
              class="hover:bg-gray-50"
            >
              <td
                v-for="column in columns"
                :key="column.key"
                class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"
              >
                {{ getNestedValue(row, column.key) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';

interface Column {
  key: string;
  label: string;
}

interface Props {
  data: Record<string, any>[];
  columns: Column[];
  loading?: boolean;
  title?: string;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false
});

// Lifecycle
onMounted(() => {
  console.log('🔍 [VDataTableSimple] Component mounted successfully!');
  console.log('🔍 [VDataTableSimple] Props data:', props.data);
  console.log('🔍 [VDataTableSimple] Props columns:', props.columns);
  console.log('🔍 [VDataTableSimple] Data length:', props.data?.length);
});

// Utility function to get nested values
const getNestedValue = (obj: any, path: string): any => {
  return path.split('.').reduce((value, key) => value?.[key], obj) ?? '';
};
</script>