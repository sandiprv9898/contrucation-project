<template>
  <VCard class="advanced-filter">
    <div class="p-6">
      <!-- Filter Header -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
          <h3 class="text-lg font-medium text-gray-900">Advanced Filters</h3>
          <VBadge v-if="activeFiltersCount > 0" :variant="'primary'">
            {{ activeFiltersCount }} active
          </VBadge>
        </div>
        <div class="flex items-center space-x-2">
          <VButton
            variant="outline"
            size="sm"
            @click="clearAllFilters"
            :disabled="activeFiltersCount === 0"
          >
            Clear All
          </VButton>
          <VButton
            variant="outline"
            size="sm"
            @click="toggleCollapsed"
          >
            {{ isCollapsed ? 'Expand' : 'Collapse' }}
          </VButton>
        </div>
      </div>

      <!-- Quick Filters -->
      <div class="mb-6">
        <h4 class="text-sm font-medium text-gray-700 mb-3">Quick Filters</h4>
        <div class="flex flex-wrap gap-2">
          <VButton
            v-for="quickFilter in quickFilters"
            :key="quickFilter.key"
            variant="outline"
            size="sm"
            @click="applyQuickFilter(quickFilter)"
            :class="{
              'bg-blue-50 border-blue-200 text-blue-700': isQuickFilterActive(quickFilter.key)
            }"
          >
            {{ quickFilter.label }}
          </VButton>
        </div>
      </div>

      <!-- Advanced Filter Form -->
      <div v-show="!isCollapsed" class="space-y-6">
        <!-- Search and Basic Filters -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Global Search -->
          <VValidatedField
            v-model="filters.search"
            name="search"
            type="text"
            label="Search"
            placeholder="Search users..."
            @update:model-value="debouncedSearch"
          />

          <!-- Role Filter -->
          <VValidatedField
            v-model="filters.role"
            name="role"
            type="select"
            label="Role"
            placeholder="Select role"
            :items="roleOptions"
            @update:model-value="onFilterChange"
          />

          <!-- Status Filter -->
          <VValidatedField
            v-model="filters.verified"
            name="verified"
            type="select"
            label="Verification Status"
            placeholder="All statuses"
            :items="verificationOptions"
            @update:model-value="onFilterChange"
          />
        </div>

        <!-- Multiple Role Selection -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Multiple Roles
            </label>
            <div class="flex flex-wrap gap-2">
              <VButton
                v-for="role in roleOptions"
                :key="role.value"
                variant="outline"
                size="sm"
                @click="toggleMultipleRole(role.value)"
                :class="{
                  'bg-blue-50 border-blue-200 text-blue-700': filters.roles.includes(role.value)
                }"
              >
                {{ role.label }}
              </VButton>
            </div>
          </div>

          <!-- Department Filter -->
          <VValidatedField
            v-model="filters.department"
            name="department"
            type="text"
            label="Department"
            placeholder="Enter department"
            @update:model-value="debouncedSearch"
          />
        </div>

        <!-- Date Range Filters -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <VValidatedField
            v-model="filters.created_from"
            name="created_from"
            type="date"
            label="Created From"
            @update:model-value="onFilterChange"
          />

          <VValidatedField
            v-model="filters.created_to"
            name="created_to"
            type="date"
            label="Created To"
            @update:model-value="onFilterChange"
          />
        </div>

        <!-- Advanced Options -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Email Domain Filter -->
          <VValidatedField
            v-model="filters.email_domain"
            name="email_domain"
            type="text"
            label="Email Domain"
            placeholder="e.g., company.com"
            @update:model-value="debouncedSearch"
          />

          <!-- Boolean Filters -->
          <div class="space-y-3">
            <VValidatedField
              v-model="filters.has_phone"
              name="has_phone"
              type="checkbox"
              label="Has Phone Number"
              @update:model-value="onFilterChange"
            />

            <VValidatedField
              v-model="filters.active"
              name="active"
              type="checkbox"
              label="Active Users Only"
              @update:model-value="onFilterChange"
            />
          </div>

          <!-- Sorting Options -->
          <div class="space-y-3">
            <VValidatedField
              v-model="filters.sort_by"
              name="sort_by"
              type="select"
              label="Sort By"
              :items="sortOptions"
              @update:model-value="onFilterChange"
            />

            <VValidatedField
              v-model="filters.sort_direction"
              name="sort_direction"
              type="select"
              label="Sort Direction"
              :items="sortDirectionOptions"
              @update:model-value="onFilterChange"
            />
          </div>
        </div>

        <!-- Pagination Options -->
        <div class="border-t pt-4">
          <h4 class="text-sm font-medium text-gray-700 mb-3">Pagination Options</h4>
          <div class="flex items-center space-x-4">
            <VValidatedField
              v-model="filters.per_page"
              name="per_page"
              type="select"
              label="Items per Page"
              :items="perPageOptions"
              @update:model-value="onFilterChange"
            />
          </div>
        </div>
      </div>

      <!-- Active Filters Display -->
      <div v-if="activeFiltersCount > 0" class="mt-6 pt-4 border-t">
        <h4 class="text-sm font-medium text-gray-700 mb-3">Active Filters</h4>
        <div class="flex flex-wrap gap-2">
          <VBadge
            v-for="(filter, key) in activeFiltersDisplay"
            :key="key"
            variant="secondary"
            class="cursor-pointer hover:bg-red-100"
            @click="removeFilter(key)"
          >
            {{ filter.label }}: {{ filter.value }}
            <XMarkIcon class="w-3 h-3 ml-1" />
          </VBadge>
        </div>
      </div>
    </div>
  </VCard>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { debounce } from 'lodash-es'
import { VCard, VButton, VBadge, VValidatedField } from '@/components/ui'
import { XMarkIcon } from 'lucide-vue-next'

interface FilterValues {
  search: string
  role: string
  roles: string[]
  verified: string
  department: string
  created_from: string
  created_to: string
  email_domain: string
  has_phone: boolean
  active: boolean
  sort_by: string
  sort_direction: string
  per_page: number
}

const props = defineProps<{
  initialFilters?: Partial<FilterValues>
}>()

const emit = defineEmits<{
  'filter-change': [filters: FilterValues]
  'quick-filter': [filterKey: string]
}>()

// Component state
const isCollapsed = ref(false)

// Filter values
const filters = ref<FilterValues>({
  search: '',
  role: '',
  roles: [],
  verified: '',
  department: '',
  created_from: '',
  created_to: '',
  email_domain: '',
  has_phone: false,
  active: true,
  sort_by: 'created_at',
  sort_direction: 'desc',
  per_page: 25,
  ...props.initialFilters
})

// Filter options
const roleOptions = [
  { value: '', label: 'All Roles' },
  { value: 'admin', label: 'Administrator' },
  { value: 'project_manager', label: 'Project Manager' },
  { value: 'supervisor', label: 'Supervisor' },
  { value: 'field_worker', label: 'Field Worker' }
]

const verificationOptions = [
  { value: '', label: 'All Statuses' },
  { value: 'true', label: 'Verified' },
  { value: 'false', label: 'Unverified' }
]

const sortOptions = [
  { value: 'created_at', label: 'Created Date' },
  { value: 'name', label: 'Name' },
  { value: 'email', label: 'Email' },
  { value: 'role', label: 'Role' },
  { value: 'updated_at', label: 'Last Updated' },
  { value: 'email_verified_at', label: 'Verification Date' }
]

const sortDirectionOptions = [
  { value: 'desc', label: 'Descending' },
  { value: 'asc', label: 'Ascending' }
]

const perPageOptions = [
  { value: 10, label: '10 per page' },
  { value: 25, label: '25 per page' },
  { value: 50, label: '50 per page' },
  { value: 100, label: '100 per page' }
]

const quickFilters = [
  { key: 'recent', label: 'Recent Users', filters: { created_from: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0] } },
  { key: 'admins', label: 'Admins Only', filters: { role: 'admin' } },
  { key: 'unverified', label: 'Unverified', filters: { verified: 'false' } },
  { key: 'this_month', label: 'This Month', filters: { created_from: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0] } },
  { key: 'has_phone', label: 'With Phone', filters: { has_phone: true } }
]

// Computed properties
const activeFiltersCount = computed(() => {
  let count = 0
  if (filters.value.search) count++
  if (filters.value.role) count++
  if (filters.value.roles.length > 0) count++
  if (filters.value.verified) count++
  if (filters.value.department) count++
  if (filters.value.created_from) count++
  if (filters.value.created_to) count++
  if (filters.value.email_domain) count++
  if (filters.value.has_phone) count++
  if (!filters.value.active) count++
  if (filters.value.sort_by !== 'created_at') count++
  if (filters.value.sort_direction !== 'desc') count++
  return count
})

const activeFiltersDisplay = computed(() => {
  const display: Record<string, { label: string; value: string }> = {}
  
  if (filters.value.search) {
    display.search = { label: 'Search', value: filters.value.search }
  }
  if (filters.value.role) {
    const role = roleOptions.find(r => r.value === filters.value.role)
    display.role = { label: 'Role', value: role?.label || filters.value.role }
  }
  if (filters.value.roles.length > 0) {
    display.roles = { label: 'Roles', value: `${filters.value.roles.length} selected` }
  }
  if (filters.value.verified) {
    const status = verificationOptions.find(s => s.value === filters.value.verified)
    display.verified = { label: 'Status', value: status?.label || filters.value.verified }
  }
  if (filters.value.department) {
    display.department = { label: 'Department', value: filters.value.department }
  }
  if (filters.value.created_from) {
    display.created_from = { label: 'From', value: filters.value.created_from }
  }
  if (filters.value.created_to) {
    display.created_to = { label: 'To', value: filters.value.created_to }
  }
  if (filters.value.email_domain) {
    display.email_domain = { label: 'Domain', value: filters.value.email_domain }
  }
  if (filters.value.has_phone) {
    display.has_phone = { label: 'Has Phone', value: 'Yes' }
  }
  if (!filters.value.active) {
    display.active = { label: 'Include Inactive', value: 'Yes' }
  }
  
  return display
})

// Methods
const debouncedSearch = debounce(() => {
  onFilterChange()
}, 500)

const onFilterChange = () => {
  emit('filter-change', { ...filters.value })
}

const clearAllFilters = () => {
  filters.value = {
    search: '',
    role: '',
    roles: [],
    verified: '',
    department: '',
    created_from: '',
    created_to: '',
    email_domain: '',
    has_phone: false,
    active: true,
    sort_by: 'created_at',
    sort_direction: 'desc',
    per_page: 25
  }
  onFilterChange()
}

const removeFilter = (filterKey: string) => {
  switch (filterKey) {
    case 'search':
      filters.value.search = ''
      break
    case 'role':
      filters.value.role = ''
      break
    case 'roles':
      filters.value.roles = []
      break
    case 'verified':
      filters.value.verified = ''
      break
    case 'department':
      filters.value.department = ''
      break
    case 'created_from':
      filters.value.created_from = ''
      break
    case 'created_to':
      filters.value.created_to = ''
      break
    case 'email_domain':
      filters.value.email_domain = ''
      break
    case 'has_phone':
      filters.value.has_phone = false
      break
    case 'active':
      filters.value.active = true
      break
  }
  onFilterChange()
}

const toggleMultipleRole = (role: string) => {
  const index = filters.value.roles.indexOf(role)
  if (index > -1) {
    filters.value.roles.splice(index, 1)
  } else {
    filters.value.roles.push(role)
  }
  onFilterChange()
}

const applyQuickFilter = (quickFilter: typeof quickFilters[0]) => {
  Object.assign(filters.value, quickFilter.filters)
  onFilterChange()
  emit('quick-filter', quickFilter.key)
}

const isQuickFilterActive = (key: string) => {
  const quickFilter = quickFilters.find(f => f.key === key)
  if (!quickFilter) return false
  
  return Object.entries(quickFilter.filters).every(([filterKey, filterValue]) => {
    return filters.value[filterKey as keyof FilterValues] === filterValue
  })
}

const toggleCollapsed = () => {
  isCollapsed.value = !isCollapsed.value
}

// Initialize
watch(() => props.initialFilters, (newFilters) => {
  if (newFilters) {
    Object.assign(filters.value, newFilters)
  }
}, { deep: true, immediate: true })
</script>

<style scoped>
.advanced-filter {
  @apply bg-white shadow-sm border border-gray-200 rounded-lg;
}
</style>