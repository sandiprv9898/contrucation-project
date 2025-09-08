# Construction Management Platform UI/UX Foundation & Design System

**Last Updated**: January 2025  
**Version**: 1.0  
**Status**: Enforced  
**Stack**: Vue.js 3.4 + TypeScript + Tailwind CSS

## 🎯 Core Design Philosophy

The Construction Management Platform follows a **Central Component-Based Design System** where all UI elements are standardized, reusable, and optimized for construction industry workflows. This document serves as the single source of truth for all UI/UX decisions in the project.

## 📋 Design Principles

### 1. **Component-First Architecture**
- All UI elements MUST use centralized components from `/components/ui/`
- NO custom styling in individual Vue files
- NO inline styles anywhere in the codebase
- ALL styling through Tailwind utility classes
- Use Composition API patterns for all components

### 2. **Data Table-First for All Modules** 🚨
- **DEFAULT**: Use table/listing style for ALL management modules
- **NO CARDS**: Do NOT use card layouts for projects, tasks, contractors, or any operational data
- **EXCEPTIONS**: Cards are ONLY allowed for:
  - Dashboard KPI metrics
  - Financial summaries
  - Weather widgets
  - Calendar views
- **REQUIRED**: All management modules MUST use data tables with:
  - Sortable columns with ArrowUpDown indicators
  - Advanced filtering (status, date ranges, categories)
  - Pagination (default 50 rows)
  - Bulk actions (assign, archive, export)
  - Row actions (edit, view, delete)
  - Column visibility controls

### 3. **Construction-Specific Patterns**
- Status indicators for project phases
- Progress bars for task completion
- Priority badges for urgent items
- Resource allocation visualizations
- Timeline/Gantt chart views for scheduling
- Cost tracking indicators

### 4. **Field-Friendly Design** 📱
- Large touch targets for mobile/tablet use
- High contrast for outdoor visibility
- Offline-first functionality indicators
- Quick action buttons for common tasks
- Voice input support for notes
- Photo upload optimization

### 5. **Compact & Dense Information Display** 🎯
- **Stats Display**: Inline metrics, not large cards
- **No Hero Sections**: Maximize content area
- **Inline Filters**: Horizontal filter bars, not sidebar filters
- **Minimal Padding**: Use `p-4` max for content areas
- **Dense Tables**: More rows visible without scrolling
- **Consistent Heights**: All header elements `h-8` (32px)
- **Inline Actions**: Edit/delete in table rows, not separate pages

### 6. **Performance & Responsiveness**
- Lazy load heavy components (Gantt charts, reports)
- Virtual scrolling for large task lists
- Optimistic UI updates for better perceived performance
- Progressive image loading for project photos
- Skeleton screens for all loading states

## 🏗️ Component Architecture

### Component Library Structure (Vue.js)
```
/src/components/ui/          # Core UI components (centralized)
├── VAlert.vue              # Feedback messages & notifications
├── VAvatar.vue             # User/contractor avatars
├── VBadge.vue              # Status/priority indicators
├── VButton.vue             # All button variants
├── VCard.vue               # Content containers
├── VCheckbox.vue           # Form checkboxes
├── VDatePicker.vue         # Date selection
├── VDialog.vue             # Modal dialogs
├── VDropdown.vue           # Dropdown menus
├── VInput.vue              # Form inputs
├── VLabel.vue              # Form labels
├── VProgress.vue           # Progress indicators
├── VSelect.vue             # Dropdown selects
├── VSkeleton.vue           # Loading placeholders
├── VSwitch.vue             # Toggle switches
├── VTabs.vue               # Tab navigation
├── VTable.vue              # Data tables (REQUIRED)
├── VTimeline.vue           # Project timelines
├── VGantt.vue              # Gantt charts
└── VKanban.vue             # Task boards
```

### Component Usage Rules

#### ✅ DO:
```vue
<template>
  <!-- Import from centralized UI components -->
  <VButton 
    :class="cn(
      'w-full',
      isUrgent && 'bg-red-600 hover:bg-red-700'
    )"
    @click="handleSubmit"
  >
    <Plus class="mr-2 h-4 w-4" />
    Add Task
  </VButton>

  <!-- Use Tailwind utilities for spacing/layout -->
  <div class="flex items-center gap-4 p-4">
    <VCard class="flex-1">
      <template #header>
        <h3 class="text-lg font-semibold">Project Overview</h3>
      </template>
      <template #content>
        <!-- Content -->
      </template>
    </VCard>
  </div>
</template>

<script setup lang="ts">
import { VButton, VCard } from '@/components/ui';
import { cn } from '@/lib/utils';
import { Plus } from 'lucide-vue-next';
</script>
```

#### ❌ DON'T:
```vue
<!-- Don't use inline styles -->
<div style="padding: 20px;">Bad</div>

<!-- Don't create custom CSS -->
<div class="my-custom-class">Bad</div>

<!-- Don't use arbitrary values excessively -->
<div class="p-[23px]">Use standard spacing</div>

<!-- Don't use external UI libraries -->
<el-button>Bad - Use VButton</el-button>
<v-btn>Bad - Use VButton</v-btn>
```

## 🎨 Design Tokens

### Color Palette (Construction Industry Themed)
```css
/* Base semantic colors - defined in index.css */
--background: 0 0% 100%;              /* White */
--foreground: 222.2 84% 4.9%;         /* Near black */
--primary: 25 95% 53%;                /* Construction Orange */
--secondary: 215 20% 35%;             /* Steel Blue */
--accent: 142 76% 36%;                /* Safety Green */
--destructive: 0 84.2% 60.2%;         /* Danger Red */
--warning: 38 92% 50%;                /* Warning Yellow */
--muted: 210 40% 96.1%;              /* Light Gray */
--card: 0 0% 100%;                   /* White */
--border: 214.3 31.8% 91.4%;         /* Gray-200 */

/* Status Colors */
--status-planning: 217 91% 60%;       /* Blue */
--status-active: 142 76% 36%;         /* Green */
--status-on-hold: 38 92% 50%;        /* Yellow */
--status-completed: 215 20% 35%;      /* Dark Blue */
--status-delayed: 0 84% 60%;          /* Red */

/* Priority Colors */
--priority-critical: 0 84% 60%;       /* Red */
--priority-high: 25 95% 53%;          /* Orange */
--priority-medium: 38 92% 50%;        /* Yellow */
--priority-low: 142 76% 36%;          /* Green */
```

### Typography Scale
```scss
// Headings
text-3xl  // Page titles (1.875rem)
text-2xl  // Section titles (1.5rem)
text-xl   // Card titles (1.25rem)
text-lg   // Subsection titles (1.125rem)

// Body
text-base // Normal text (1rem)
text-sm   // Table text, labels (0.875rem)
text-xs   // Badges, hints (0.75rem)

// Font weights
font-normal    // 400 - body text
font-medium    // 500 - labels
font-semibold  // 600 - headings
font-bold      // 700 - emphasis
```

### Spacing Scale (Compact)
```
Key spacing values for construction UI:
p-2  // 0.5rem - tight padding
p-3  // 0.75rem - compact padding  
p-4  // 1rem - standard padding (MAX for content)
gap-2 // 0.5rem - tight gaps
gap-4 // 1rem - standard gaps
gap-6 // 1.5rem - section gaps
```

## 🧩 Component Patterns

### 1. **Project Status Indicators**
```vue
<template>
  <VBadge 
    :variant="getStatusVariant(project.status)"
    class="font-medium"
  >
    <Circle 
      :class="cn(
        'mr-1 h-2 w-2 fill-current',
        getStatusColor(project.status)
      )" 
    />
    {{ project.status }}
  </VBadge>
</template>

<script setup lang="ts">
const statusConfig = {
  planning: { variant: 'info', color: 'text-blue-600' },
  active: { variant: 'success', color: 'text-green-600' },
  on_hold: { variant: 'warning', color: 'text-yellow-600' },
  completed: { variant: 'default', color: 'text-gray-600' },
  delayed: { variant: 'destructive', color: 'text-red-600' }
};
</script>
```

### 2. **Task Priority Badges**
```vue
<template>
  <div class="flex items-center gap-2">
    <VBadge 
      :class="cn(
        'text-xs font-medium',
        getPriorityClass(task.priority)
      )"
    >
      <AlertTriangle v-if="task.priority === 'critical'" class="mr-1 h-3 w-3" />
      {{ task.priority }}
    </VBadge>
    <span class="text-xs text-muted-foreground">
      Due {{ formatRelativeDate(task.dueDate) }}
    </span>
  </div>
</template>
```

### 3. **Progress Indicators**
```vue
<template>
  <div class="space-y-1">
    <div class="flex items-center justify-between text-sm">
      <span class="font-medium">{{ label }}</span>
      <span class="text-muted-foreground">{{ progress }}%</span>
    </div>
    <VProgress 
      :value="progress" 
      :class="cn(
        'h-2',
        progress === 100 && 'bg-green-600',
        progress < 50 && 'bg-yellow-600'
      )"
    />
  </div>
</template>
```

### 4. **Compact Stats Pattern (REQUIRED for all pages)** 📊
```vue
<template>
  <!-- Standard 4-stat inline pattern -->
  <div class="flex items-center gap-6 text-sm text-muted-foreground">
    <span>Total: <strong class="text-foreground">{{ stats.total }}</strong></span>
    <span>Active: <strong class="text-green-600">{{ stats.active }}</strong></span>
    <span>{{ relatedLabel }}: <strong class="text-foreground">{{ stats.related }}</strong></span>
    <span>This Month: <strong class="text-blue-600">{{ stats.thisMonth }}</strong></span>
  </div>
</template>

<!-- Examples for different modules: -->

<!-- Projects Page -->
<div class="flex items-center gap-6 text-sm text-muted-foreground">
  <span>Total: <strong class="text-foreground">45</strong></span>
  <span>Active: <strong class="text-green-600">12</strong></span>
  <span>On Schedule: <strong class="text-foreground">8</strong></span>
  <span>This Month: <strong class="text-blue-600">3</strong></span>
</div>

<!-- Tasks Page -->
<div class="flex items-center gap-6 text-sm text-muted-foreground">
  <span>Total: <strong class="text-foreground">324</strong></span>
  <span>In Progress: <strong class="text-green-600">67</strong></span>
  <span>Overdue: <strong class="text-red-600">5</strong></span>
  <span>Completed Today: <strong class="text-blue-600">12</strong></span>
</div>

<!-- Contractors Page -->
<div class="flex items-center gap-6 text-sm text-muted-foreground">
  <span>Total: <strong class="text-foreground">89</strong></span>
  <span>Active: <strong class="text-green-600">76</strong></span>
  <span>On Site: <strong class="text-foreground">23</strong></span>
  <span>Added This Month: <strong class="text-blue-600">8</strong></span>
</div>
```

### 5. **Data Table Pattern (MANDATORY for all modules)** 🚨
```vue
<template>
  <div class="space-y-4">
    <!-- Compact Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-8">
        <h1 class="text-2xl font-bold">{{ title }}</h1>
        <!-- Inline stats -->
        <div class="flex items-center gap-6 text-sm text-muted-foreground">
          <span>Total: <strong class="text-foreground">{{ total }}</strong></span>
          <span>Active: <strong class="text-green-600">{{ active }}</strong></span>
          <span>On Schedule: <strong class="text-foreground">{{ onSchedule }}</strong></span>
          <span>This Month: <strong class="text-blue-600">{{ thisMonth }}</strong></span>
        </div>
      </div>
      <VButton size="sm" class="h-8">
        <Plus class="mr-2 h-4 w-4" />
        Add {{ entityName }}
      </VButton>
    </div>

    <!-- Inline Filters -->
    <div class="flex items-center justify-between gap-4">
      <div class="flex items-center gap-2 flex-1">
        <!-- Search -->
        <div class="relative flex-1 max-w-sm">
          <Search class="absolute left-3 top-2 h-4 w-4 text-muted-foreground" />
          <VInput 
            v-model="filters.search"
            placeholder="Search..."
            class="pl-9 h-8"
          />
        </div>
        
        <!-- Status Filter -->
        <VSelect 
          v-model="filters.status"
          placeholder="Status"
          :options="statusOptions"
          class="w-32 h-8"
        />
        
        <!-- Date Range -->
        <VDatePicker
          v-model="filters.dateRange"
          mode="range"
          class="w-64 h-8"
        />
        
        <!-- More Filters -->
        <VButton variant="outline" size="sm" class="h-8">
          <Filter class="mr-2 h-4 w-4" />
          More Filters
        </VButton>
      </div>
      
      <!-- Column Visibility (Icon Only) -->
      <VDropdown>
        <template #trigger>
          <VButton variant="outline" size="icon" class="h-8 w-8">
            <Settings2 class="h-4 w-4" />
          </VButton>
        </template>
        <template #content>
          <div class="p-2 space-y-1">
            <label 
              v-for="col in columns" 
              :key="col.key"
              class="flex items-center gap-2 text-sm cursor-pointer hover:bg-muted p-1 rounded"
            >
              <VCheckbox v-model="col.visible" />
              {{ col.label }}
            </label>
          </div>
        </template>
      </VDropdown>
    </div>

    <!-- Data Table -->
    <VCard class="p-0">
      <VTable
        :columns="visibleColumns"
        :data="paginatedData"
        :sort="sort"
        @sort-change="handleSort"
      >
        <template #cell-name="{ row }">
          <div class="font-medium">{{ row.name }}</div>
          <div class="text-xs text-muted-foreground">{{ row.code }}</div>
        </template>
        
        <template #cell-status="{ row }">
          <VBadge :variant="getStatusVariant(row.status)">
            {{ row.status }}
          </VBadge>
        </template>
        
        <template #cell-progress="{ row }">
          <div class="flex items-center gap-2">
            <VProgress :value="row.progress" class="w-20 h-2" />
            <span class="text-xs">{{ row.progress }}%</span>
          </div>
        </template>
        
        <template #cell-actions="{ row }">
          <VDropdown align="end">
            <template #trigger>
              <VButton variant="ghost" size="icon" class="h-8 w-8">
                <MoreHorizontal class="h-4 w-4" />
              </VButton>
            </template>
            <template #content>
              <VDropdownItem @click="handleEdit(row)">
                <Edit class="mr-2 h-4 w-4" />
                Edit
              </VDropdownItem>
              <VDropdownItem @click="handleView(row)">
                <Eye class="mr-2 h-4 w-4" />
                View Details
              </VDropdownItem>
              <VDropdownItem @click="handleDelete(row)" class="text-destructive">
                <Trash class="mr-2 h-4 w-4" />
                Delete
              </VDropdownItem>
            </template>
          </VDropdown>
        </template>
      </VTable>
    </VCard>

    <!-- Pagination with Page Size -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4 text-sm text-muted-foreground">
        <span>Showing {{ startIndex }}-{{ endIndex }} of {{ total }}</span>
        <div class="flex items-center gap-2">
          <VLabel class="text-sm font-normal">Rows per page:</VLabel>
          <VSelect
            v-model="pageSize"
            :options="pageSizeOptions"
            class="w-20 h-8"
          />
        </div>
      </div>
      <VPagination
        v-model="currentPage"
        :total-pages="totalPages"
        :max-visible="5"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { 
  Plus, Search, Filter, Settings2, MoreHorizontal, 
  Edit, Eye, Trash, ArrowUpDown 
} from 'lucide-vue-next';

// Pagination defaults
const pageSize = ref(50);
const pageSizeOptions = [
  { value: 10, label: '10' },
  { value: 25, label: '25' },
  { value: 50, label: '50' },
  { value: 100, label: '100' },
  { value: 250, label: '250' }
];

// Column configuration with sorting
const columns = ref([
  { 
    key: 'name', 
    label: 'Project Name', 
    sortable: true,
    visible: true,
    width: '250px'
  },
  { 
    key: 'status', 
    label: 'Status', 
    sortable: true,
    visible: true,
    width: '120px'
  },
  { 
    key: 'progress', 
    label: 'Progress', 
    sortable: true,
    visible: true,
    width: '150px'
  },
  { 
    key: 'dueDate', 
    label: 'Due Date', 
    sortable: true,
    visible: true,
    width: '120px'
  },
  { 
    key: 'manager', 
    label: 'Project Manager', 
    sortable: true,
    visible: false,
    width: '200px'
  },
  { 
    key: 'budget', 
    label: 'Budget', 
    sortable: true,
    visible: false,
    width: '120px',
    align: 'right'
  },
  { 
    key: 'actions', 
    label: '', 
    sortable: false,
    visible: true,
    width: '80px',
    align: 'center'
  }
]);
</script>
```

## 📏 Construction-Specific UI Patterns

### 1. **Task Board (Kanban View)**
```vue
<template>
  <div class="grid grid-cols-4 gap-4">
    <div 
      v-for="column in taskColumns" 
      :key="column.id"
      class="bg-muted/50 rounded-lg p-3"
    >
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold text-sm">{{ column.title }}</h3>
        <VBadge variant="secondary">{{ column.tasks.length }}</VBadge>
      </div>
      
      <div class="space-y-2">
        <div
          v-for="task in column.tasks"
          :key="task.id"
          class="bg-card p-3 rounded border cursor-move hover:shadow-sm transition-shadow"
        >
          <div class="flex items-start justify-between mb-2">
            <span class="font-medium text-sm">{{ task.name }}</span>
            <VBadge 
              :variant="getPriorityVariant(task.priority)"
              class="text-xs"
            >
              {{ task.priority }}
            </VBadge>
          </div>
          
          <div class="space-y-1 text-xs text-muted-foreground">
            <div class="flex items-center gap-1">
              <User class="h-3 w-3" />
              {{ task.assignee }}
            </div>
            <div class="flex items-center gap-1">
              <Calendar class="h-3 w-3" />
              {{ formatDate(task.dueDate) }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
```

### 2. **Project Timeline View**
```vue
<template>
  <VCard>
    <template #header>
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold">Project Timeline</h3>
        <div class="flex items-center gap-2">
          <VButton variant="outline" size="sm" class="h-8">
            <ChevronLeft class="h-4 w-4" />
          </VButton>
          <span class="text-sm font-medium px-3">{{ currentMonth }}</span>
          <VButton variant="outline" size="sm" class="h-8">
            <ChevronRight class="h-4 w-4" />
          </VButton>
        </div>
      </div>
    </template>
    
    <template #content>
      <VGantt
        :tasks="tasks"
        :start-date="startDate"
        :end-date="endDate"
        :dependencies="dependencies"
        @task-click="handleTaskClick"
      />
    </template>
  </VCard>
</template>
```

### 3. **Resource Allocation Grid**
```vue
<template>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b">
          <th class="text-left p-2 sticky left-0 bg-background">Resource</th>
          <th 
            v-for="day in weekDays" 
            :key="day"
            class="text-center p-2 min-w-[100px]"
          >
            {{ day }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr 
          v-for="resource in resources" 
          :key="resource.id"
          class="border-b hover:bg-muted/50"
        >
          <td class="p-2 sticky left-0 bg-background">
            <div class="flex items-center gap-2">
              <VAvatar :src="resource.avatar" :name="resource.name" size="sm" />
              <div>
                <div class="font-medium">{{ resource.name }}</div>
                <div class="text-xs text-muted-foreground">{{ resource.role }}</div>
              </div>
            </div>
          </td>
          <td 
            v-for="day in weekDays" 
            :key="day"
            class="p-2 text-center"
          >
            <VBadge 
              v-if="resource.allocation[day]"
              :variant="getWorkloadVariant(resource.allocation[day].hours)"
              class="text-xs"
            >
              {{ resource.allocation[day].hours }}h
            </VBadge>
            <span v-else class="text-muted-foreground">-</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
```

### 4. **Safety Compliance Checklist**
```vue
<template>
  <VCard>
    <template #header>
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold">Safety Compliance</h3>
        <VBadge 
          :variant="allCompliant ? 'success' : 'warning'"
          class="font-medium"
        >
          {{ compliancePercentage }}% Compliant
        </VBadge>
      </div>
    </template>
    
    <template #content>
      <div class="space-y-3">
        <div 
          v-for="item in safetyChecklist" 
          :key="item.id"
          class="flex items-center justify-between p-3 border rounded-lg hover:bg-muted/50"
        >
          <div class="flex items-center gap-3">
            <VCheckbox 
              v-model="item.completed"
              @change="updateCompliance(item)"
            />
            <div>
              <div class="font-medium">{{ item.title }}</div>
              <div class="text-xs text-muted-foreground">
                {{ item.category }} • Due {{ formatDate(item.dueDate) }}
              </div>
            </div>
          </div>
          
          <div class="flex items-center gap-2">
            <VBadge 
              v-if="item.priority === 'critical'"
              variant="destructive"
              class="text-xs"
            >
              Critical
            </VBadge>
            <VButton variant="ghost" size="icon" class="h-8 w-8">
              <FileText class="h-4 w-4" />
            </VButton>
          </div>
        </div>
      </div>
    </template>
  </VCard>
</template>
```

## 📱 Mobile/Tablet Optimization

### Field Worker Interface
```vue
<template>
  <!-- Mobile-optimized task view -->
  <div class="p-3 space-y-3 max-w-md mx-auto">
    <!-- Quick Actions -->
    <div class="grid grid-cols-2 gap-2">
      <VButton class="h-12" variant="primary">
        <Clock class="mr-2 h-5 w-5" />
        Clock In
      </VButton>
      <VButton class="h-12" variant="outline">
        <Camera class="mr-2 h-5 w-5" />
        Photo
      </VButton>
    </div>
    
    <!-- Today's Tasks -->
    <VCard>
      <template #header>
        <h3 class="text-lg font-semibold">Today's Tasks</h3>
      </template>
      <template #content>
        <div class="space-y-2">
          <div 
            v-for="task in todaysTasks"
            :key="task.id"
            class="p-3 border rounded-lg touch-manipulation"
            @click="openTask(task)"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="font-medium">{{ task.name }}</div>
                <div class="text-sm text-muted-foreground mt-1">
                  {{ task.location }} • {{ task.duration }}
                </div>
              </div>
              <VBadge 
                :variant="task.completed ? 'success' : 'default'"
                class="ml-2"
              >
                {{ task.completed ? 'Done' : 'Pending' }}
              </VBadge>
            </div>
          </div>
        </div>
      </template>
    </VCard>
  </div>
</template>

<style>
/* Touch-friendly targets */
.touch-manipulation {
  touch-action: manipulation;
  -webkit-tap-highlight-color: transparent;
}
</style>
```

## 🚨 Common Violations to Avoid

1. **Using card grids for operational data**
   ```vue
   <!-- ❌ WRONG - Cards for project listing -->
   <div class="grid grid-cols-3 gap-4">
     <VCard v-for="project in projects">
       <!-- Project details -->
     </VCard>
   </div>
   
   <!-- ✅ CORRECT - Data table -->
   <VTable :columns="columns" :data="projects" />
   ```

2. **Large hero sections or stats cards**
   ```vue
   <!-- ❌ WRONG - Large stats cards -->
   <div class="grid grid-cols-4 gap-4 mb-8">
     <VCard class="p-6">
       <h3 class="text-2xl font-bold">45</h3>
       <p>Total Projects</p>
     </VCard>
   </div>
   
   <!-- ✅ CORRECT - Inline stats -->
   <div class="flex items-center gap-6 text-sm">
     <span>Total: <strong>45</strong></span>
   </div>
   ```

3. **Missing sorting indicators**
   ```vue
   <!-- ❌ WRONG - No sorting indicator -->
   <th @click="sort('name')">Name</th>
   
   <!-- ✅ CORRECT - With ArrowUpDown icon -->
   <th @click="sort('name')" class="cursor-pointer">
     Name <ArrowUpDown class="inline ml-1 h-4 w-4" />
   </th>
   ```

4. **Inconsistent button heights**
   ```vue
   <!-- ❌ WRONG - Mixed heights -->
   <VButton class="h-10">Add</VButton>
   <VInput class="h-8" />
   
   <!-- ✅ CORRECT - Consistent h-8 -->
   <VButton class="h-8">Add</VButton>
   <VInput class="h-8" />
   ```

## ✅ Design Checklist

Before committing any UI changes, verify:

### Component Standards
- [ ] All components imported from `/components/ui/`
- [ ] No inline styles used
- [ ] cn() utility used for conditional classes
- [ ] TypeScript types defined for all props
- [ ] Composition API used (no Options API)

### Data Display
- [ ] **Management modules use data tables, NOT cards**
- [ ] **Tables have sortable columns with ArrowUpDown icons**
- [ ] **Default pagination set to 50 rows**
- [ ] **Page size selector includes: 10, 25, 50, 100, 250**
- [ ] **Column visibility control available (icon button)**
- [ ] **Pagination shows range (e.g., "Showing 1-50 of 156")**

### Layout & Spacing
- [ ] **Header elements use h-8 (32px) height**
- [ ] **Inline stats follow 4-metric pattern**
- [ ] **Filters are inline, not in sidebars**
- [ ] **Content padding maximum p-4**
- [ ] **No hero sections in management pages**

### Construction-Specific
- [ ] Status indicators use industry-standard colors
- [ ] Priority badges clearly visible
- [ ] Progress bars show percentage
- [ ] Resource allocation visualized
- [ ] Safety compliance tracked
- [ ] Mobile-friendly for field use

### Accessibility & Performance
- [ ] Keyboard navigation works
- [ ] Touch targets minimum 44x44px on mobile
- [ ] Loading states implemented
- [ ] Error states handled
- [ ] Empty states designed
- [ ] Virtual scrolling for large lists
- [ ] Images lazy loaded

### Testing
- [ ] Tested on desktop (1920x1080)
- [ ] Tested on tablet (iPad)
- [ ] Tested on mobile (iPhone/Android)
- [ ] Tested in bright light (field conditions)
- [ ] Offline functionality verified

## 🔄 Adding New Patterns

If you need to add a new construction-specific pattern:

1. **Check existing components first**
2. **Discuss with team and stakeholders**
3. **Consider field worker needs**
4. **Create in `/components/ui/` if approved**
5. **Document in this guide**
6. **Add to component library**
7. **Create usage examples**
8. **Test on all devices**

## 📚 Resources

### Design Resources
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Vue.js Style Guide](https://vuejs.org/style-guide/)
- [Lucide Icons](https://lucide.dev)
- [WCAG Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

### Construction Industry Standards
- [OSHA Color Coding](https://www.osha.gov/safety-management)
- [Construction Project Phases](https://www.projectmanager.com/blog/construction-project-phases)
- [Safety Symbols](https://www.safetysign.com/ansi-safety-symbols)

### Internal Documentation
- `/docs/API.md` - API Integration Guide
- `/docs/components/` - Component Documentation
- `/docs/patterns/` - UI Pattern Examples
- `/docs/accessibility.md` - Accessibility Standards

---

**Remember**: This platform is used in the field under various conditions. Prioritize clarity, efficiency, and reliability over aesthetics. Every pixel should serve a purpose in helping construction professionals manage their projects effectively.