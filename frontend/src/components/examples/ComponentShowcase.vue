<template>
  <div class="space-y-8 p-6 max-w-6xl mx-auto">
    <div class="text-center">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Component Reusability Showcase</h1>
      <p class="text-gray-600">Demonstrating the new reusable UI components for forms, tables, and more</p>
    </div>

    <!-- Form Components Section -->
    <section class="space-y-6">
      <h2 class="text-2xl font-semibold text-gray-900 border-b pb-2">Form Components</h2>
      
      <VCard class="p-6">
        <h3 class="text-lg font-medium mb-4">VFormField with Various Input Types</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Text Input with FormField -->
          <VFormField
            label="Full Name"
            :error-message="formErrors.name"
            help-text="Enter your complete name as shown on official documents"
            required
          >
            <template #default="{ fieldId, inputClass }">
              <VInput
                :id="fieldId"
                v-model="formData.name"
                type="text"
                :class="inputClass"
                placeholder="Enter your full name"
              />
            </template>
          </VFormField>

          <!-- Email with validation -->
          <VFormField
            label="Email Address"
            :error-message="formErrors.email"
            required
          >
            <template #default="{ fieldId, inputClass }">
              <VInput
                :id="fieldId"
                v-model="formData.email"
                type="email"
                :class="inputClass"
                placeholder="name@company.com"
              />
            </template>
          </VFormField>

          <!-- Select Dropdown -->
          <VFormField
            label="Department"
            :error-message="formErrors.department"
            required
          >
            <template #default="{ fieldId, hasError }">
              <VSelect
                :id="fieldId"
                v-model="formData.department"
                :items="departmentOptions"
                placeholder="Select department"
                :has-error="hasError"
              />
            </template>
          </VFormField>

          <!-- Multi-value select -->
          <VFormField
            label="Skills"
            :error-message="formErrors.skills"
          >
            <template #default="{ fieldId, hasError }">
              <VSelect
                :id="fieldId"
                v-model="formData.skills"
                :items="skillOptions"
                placeholder="Select skills"
                :has-error="hasError"
              />
            </template>
          </VFormField>

          <!-- Textarea -->
          <div class="md:col-span-2">
            <VFormField
              label="Project Description"
              :error-message="formErrors.description"
              help-text="Describe your project goals and requirements"
            >
              <template #default="{ fieldId, hasError }">
                <VTextarea
                  :id="fieldId"
                  v-model="formData.description"
                  :rows="4"
                  :has-error="hasError"
                  placeholder="Enter project description..."
                  maxlength="500"
                />
              </template>
            </VFormField>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
          <VButton variant="outline" @click="resetForm">
            Reset Form
          </VButton>
          <VButton @click="validateAndSubmit">
            Submit Form
          </VButton>
        </div>
      </VCard>
    </section>

    <!-- Data Table Section -->
    <section class="space-y-6">
      <h2 class="text-2xl font-semibold text-gray-900 border-b pb-2">Data Table Component</h2>
      
      <VDataTable
        title="Project Management"
        :data="tableData"
        :columns="tableColumns"
        :stats="tableStats"
        :loading="tableLoading"
        searchable
        selectable
        @row-click="handleRowClick"
        @selection-change="handleSelectionChange"
      >
        <!-- Custom actions slot -->
        <template #actions>
          <VButton size="sm" @click="openCreateModal">
            <Plus class="w-4 h-4 mr-2" />
            New Project
          </VButton>
        </template>

        <!-- Custom cell for status -->
        <template #cell-status="{ value }">
          <VBadge :variant="getStatusVariant(value)">
            {{ value }}
          </VBadge>
        </template>

        <!-- Custom cell for actions -->
        <template #cell-actions="{ row }">
          <div class="flex items-center space-x-2">
            <VButton variant="ghost" size="sm" @click="editRow(row)">
              <Edit class="w-4 h-4" />
            </VButton>
            <VButton variant="ghost" size="sm" @click="deleteRow(row)">
              <Trash class="w-4 h-4" />
            </VButton>
          </div>
        </template>

        <!-- Bulk actions -->
        <template #bulk-actions="{ selected }">
          <VButton variant="outline" size="sm" @click="bulkArchive(selected)">
            Archive Selected
          </VButton>
          <VButton variant="danger" size="sm" @click="bulkDelete(selected)">
            Delete Selected
          </VButton>
        </template>
      </VDataTable>
    </section>

    <!-- Filter Bar Section -->
    <section class="space-y-6">
      <h2 class="text-2xl font-semibold text-gray-900 border-b pb-2">Filter Bar Component</h2>
      
      <VFilterBar
        title="Advanced Filters"
        :filters="filterOptions"
        show-active-filters
        @filter-change="handleFilterChange"
        @search-change="handleSearchChange"
      >
        <!-- Custom filter actions -->
        <template #actions>
          <VButton variant="outline" size="sm" @click="saveFilters">
            <Bookmark class="w-4 h-4 mr-1" />
            Save
          </VButton>
          <VButton variant="ghost" size="sm" @click="exportData">
            <Download class="w-4 h-4 mr-1" />
            Export
          </VButton>
        </template>
      </VFilterBar>
    </section>

    <!-- Modal Section -->
    <section class="space-y-6">
      <h2 class="text-2xl font-semibold text-gray-900 border-b pb-2">Modal Component</h2>
      
      <div class="flex space-x-4">
        <VButton @click="showDefaultModal = true">
          Default Modal
        </VButton>
        <VButton variant="danger" @click="showDangerModal = true">
          Danger Modal
        </VButton>
        <VButton variant="outline" @click="showLargeModal = true">
          Large Modal
        </VButton>
      </div>

      <!-- Default Modal -->
      <VModal
        v-model="showDefaultModal"
        title="Create New Project"
        subtitle="Fill in the details to create a new construction project"
        @confirm="handleCreateProject"
        @cancel="showDefaultModal = false"
      >
        <div class="space-y-4">
          <VFormField label="Project Name" required>
            <template #default="{ fieldId, inputClass }">
              <VInput
                :id="fieldId"
                v-model="newProject.name"
                :class="inputClass"
                placeholder="Enter project name"
              />
            </template>
          </VFormField>
          
          <VFormField label="Project Type">
            <template #default="{ fieldId, hasError }">
              <VSelect
                :id="fieldId"
                v-model="newProject.type"
                :items="projectTypes"
                placeholder="Select project type"
                :has-error="hasError"
              />
            </template>
          </VFormField>
        </div>
      </VModal>

      <!-- Danger Modal -->
      <VModal
        v-model="showDangerModal"
        title="Delete Project"
        subtitle="This action cannot be undone"
        variant="danger"
        confirm-text="Delete"
        confirm-variant="danger"
        @confirm="handleDeleteProject"
        @cancel="showDangerModal = false"
      >
        <p class="text-gray-600">
          Are you sure you want to delete this project? All associated data will be permanently removed.
        </p>
      </VModal>

      <!-- Large Modal -->
      <VModal
        v-model="showLargeModal"
        title="Project Details"
        size="lg"
        :show-confirm-button="false"
        @close="showLargeModal = false"
      >
        <div class="space-y-6">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <h4 class="font-medium text-gray-900">Project Information</h4>
              <dl class="mt-2 space-y-1">
                <div>
                  <dt class="text-sm text-gray-600">Name:</dt>
                  <dd class="text-sm font-medium">Downtown Office Complex</dd>
                </div>
                <div>
                  <dt class="text-sm text-gray-600">Status:</dt>
                  <dd><VBadge variant="success">Active</VBadge></dd>
                </div>
              </dl>
            </div>
            <div>
              <h4 class="font-medium text-gray-900">Timeline</h4>
              <dl class="mt-2 space-y-1">
                <div>
                  <dt class="text-sm text-gray-600">Start Date:</dt>
                  <dd class="text-sm font-medium">March 1, 2024</dd>
                </div>
                <div>
                  <dt class="text-sm text-gray-600">Expected Completion:</dt>
                  <dd class="text-sm font-medium">December 15, 2024</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      </VModal>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { 
  VButton, VCard, VFormField, VInput, VSelect, VTextarea, 
  VDataTable, VFilterBar, VModal, VBadge 
} from '@/components/ui';
import { Plus, Edit, Trash, Bookmark, Download } from 'lucide-vue-next';

// Form data and validation
const formData = reactive({
  name: '',
  email: '',
  department: '',
  skills: '',
  description: ''
});

const formErrors = reactive({
  name: '',
  email: '',
  department: '',
  skills: '',
  description: ''
});

// Form options
const departmentOptions = [
  { label: 'Engineering', value: 'engineering' },
  { label: 'Construction', value: 'construction' },
  { label: 'Project Management', value: 'project_management' },
  { label: 'Safety', value: 'safety' }
];

const skillOptions = [
  { label: 'Project Planning', value: 'planning' },
  { label: 'Budget Management', value: 'budget' },
  { label: 'Team Leadership', value: 'leadership' },
  { label: 'Quality Assurance', value: 'qa' }
];

// Table data
const tableLoading = ref(false);
const tableData = ref([
  {
    id: 1,
    name: 'Downtown Office Complex',
    status: 'active',
    progress: 65,
    budget: '$2,500,000',
    deadline: '2024-12-15'
  },
  {
    id: 2,
    name: 'Residential Tower Phase 2',
    status: 'planning',
    progress: 25,
    budget: '$8,900,000',
    deadline: '2025-06-30'
  },
  {
    id: 3,
    name: 'Shopping Mall Renovation',
    status: 'completed',
    progress: 100,
    budget: '$1,200,000',
    deadline: '2024-03-01'
  }
]);

const tableColumns = [
  { key: 'name', label: 'Project Name', sortable: true },
  { key: 'status', label: 'Status', sortable: true, width: '120px' },
  { key: 'progress', label: 'Progress', sortable: true, width: '100px' },
  { key: 'budget', label: 'Budget', sortable: true, width: '120px' },
  { key: 'deadline', label: 'Deadline', sortable: true, width: '120px' },
  { key: 'actions', label: 'Actions', width: '100px' }
];

const tableStats = {
  'Total': 3,
  'Active': 1,
  'Completed': 1
};

// Filter options
const filterOptions = [
  {
    key: 'status',
    label: 'Status',
    type: 'select' as const,
    placeholder: 'All Status',
    options: [
      { label: 'Active', value: 'active' },
      { label: 'Planning', value: 'planning' },
      { label: 'Completed', value: 'completed' },
      { label: 'On Hold', value: 'on_hold' }
    ]
  },
  {
    key: 'departments',
    label: 'Departments',
    type: 'multi-select' as const,
    placeholder: 'Select departments',
    options: departmentOptions
  },
  {
    key: 'budget_range',
    label: 'Budget Range',
    type: 'number-range' as const,
    minPlaceholder: 'Min budget',
    maxPlaceholder: 'Max budget'
  },
  {
    key: 'date_range',
    label: 'Project Timeline',
    type: 'date-range' as const
  }
];

// Modal states
const showDefaultModal = ref(false);
const showDangerModal = ref(false);
const showLargeModal = ref(false);

// New project data for modal
const newProject = reactive({
  name: '',
  type: ''
});

const projectTypes = [
  { label: 'Residential', value: 'residential' },
  { label: 'Commercial', value: 'commercial' },
  { label: 'Industrial', value: 'industrial' },
  { label: 'Infrastructure', value: 'infrastructure' }
];

// Methods
const resetForm = () => {
  Object.keys(formData).forEach(key => {
    formData[key as keyof typeof formData] = '';
  });
  Object.keys(formErrors).forEach(key => {
    formErrors[key as keyof typeof formErrors] = '';
  });
};

const validateAndSubmit = () => {
  // Reset errors
  Object.keys(formErrors).forEach(key => {
    formErrors[key as keyof typeof formErrors] = '';
  });
  
  // Simple validation
  if (!formData.name) formErrors.name = 'Name is required';
  if (!formData.email) formErrors.email = 'Email is required';
  if (!formData.department) formErrors.department = 'Department is required';
  
  const hasErrors = Object.values(formErrors).some(error => error !== '');
  
  if (!hasErrors) {
    alert('Form submitted successfully!');
    resetForm();
  }
};

const getStatusVariant = (status: string) => {
  const variants: Record<string, any> = {
    active: 'success',
    planning: 'warning',
    completed: 'default',
    on_hold: 'destructive'
  };
  return variants[status] || 'default';
};

const handleRowClick = (row: any, index: number) => {
  console.log('Row clicked:', row, index);
};

const handleSelectionChange = (selectedRows: any[]) => {
  console.log('Selection changed:', selectedRows);
};

const editRow = (row: any) => {
  console.log('Edit row:', row);
};

const deleteRow = (row: any) => {
  console.log('Delete row:', row);
};

const bulkArchive = (selected: any[]) => {
  console.log('Bulk archive:', selected);
};

const bulkDelete = (selected: any[]) => {
  console.log('Bulk delete:', selected);
};

const handleFilterChange = (filters: Record<string, any>) => {
  console.log('Filters changed:', filters);
};

const handleSearchChange = (query: string) => {
  console.log('Search changed:', query);
};

const saveFilters = () => {
  console.log('Save filters');
};

const exportData = () => {
  console.log('Export data');
};

const openCreateModal = () => {
  showDefaultModal.value = true;
};

const handleCreateProject = () => {
  console.log('Create project:', newProject);
  showDefaultModal.value = false;
  // Reset form
  newProject.name = '';
  newProject.type = '';
};

const handleDeleteProject = () => {
  console.log('Delete project confirmed');
  showDangerModal.value = false;
};
</script>