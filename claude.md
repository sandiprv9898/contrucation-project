# Construction Management Platform - Project Policy & Rule Book

## Project Overview
Construction Management Platform is a Laravel 11.x (backend) and Vue.js 3/TypeScript (frontend) based comprehensive construction project management system. This project follows strict architectural patterns, coding standards, and development workflows to ensure scalability, maintainability, and performance.

## 🚨 CRITICAL DEVELOPMENT RULES

### Frontend (Vue.js) Rules
1. **ALWAYS use components from `/components/ui/`** - NO EXCEPTIONS
2. **NEVER use inline styles** - Use Tailwind utilities only
3. **ALWAYS use TypeScript** - No plain JavaScript files
4. **FOLLOW Composition API** - No Options API
5. **USE Pinia for state management** - No Vuex
6. **CRUD modules MUST use data tables** - NO CARD GRIDS for listings
7. **Keep UI COMPACT** - Maximize data density
8. **Filters must be INLINE** - No large filter sections
9. **Table Sorting is MANDATORY** - All columns sortable
10. **Use Lucide Vue icons ONLY** - No other icon libraries

### Backend (Laravel) Rules
1. **ALWAYS use Repository Pattern** - No direct Eloquent in controllers
2. **FOLLOW Domain-Driven Design** - Organize by domain, not type
3. **USE Service Layer** - Business logic in services, not controllers
4. **API Resources MANDATORY** - Never return raw models
5. **USE Form Requests** - All validation in request classes
6. **IMPLEMENT DTOs** - Use Spatie Laravel-Data package
7. **QUEUE long operations** - Use Laravel Horizon
8. **CACHE aggressively** - Redis for all caching
9. **USE database transactions** - For data integrity
10. **FOLLOW PSR-12** - PHP coding standards

## Core Development Principles

### 1. Task Management
- **Task Division**: Break work into 2-4 hour tasks maximum
- **Task Numbering**: Use format `CONST-001`, `CONST-002`, etc.
- **Documentation**: Store in `/docs/tasks/` folder
- **Tracking**: Use project management tool integration
- **Branch Naming**: `feature/CONST-XXX-description`

### 2. Development Workflow (Mandatory for Each Task)

#### Phase 1: Analysis & Planning
```markdown
## Task Analysis Checklist
- [ ] Requirements documented
- [ ] API endpoints identified
- [ ] Database changes planned
- [ ] UI components identified
- [ ] Test scenarios defined
- [ ] Performance impact assessed
```

#### Phase 2: Backend Implementation (Laravel)
```php
// ALWAYS follow this structure for new features

// 1. Migration
Schema::create('table_name', function (Blueprint $table) {
    $table->uuid('id')->primary();
    // ... fields
    $table->timestamps();
    $table->softDeletes();
});

// 2. Model (app/Domain/[Feature]/Models/)
class Project extends Model
{
    use HasUuid, SoftDeletes, HasFactory;
    
    protected $fillable = [...];
    protected $casts = [...];
    
    // Relationships
    // Scopes
    // Accessors/Mutators
}

// 3. Repository (app/Domain/[Feature]/Repositories/)
interface ProjectRepositoryInterface
{
    public function findByUser(User $user): Collection;
}

class ProjectRepository implements ProjectRepositoryInterface
{
    // Implementation
}

// 4. Service (app/Domain/[Feature]/Services/)
class ProjectService
{
    public function __construct(
        private ProjectRepositoryInterface $repository
    ) {}
    
    // Business logic here
}

// 5. Controller (app/Http/Controllers/Api/)
class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $service
    ) {}
    
    public function index(ProjectIndexRequest $request): JsonResource
    {
        return ProjectResource::collection(
            $this->service->getProjects($request->validated())
        );
    }
}

// 6. Form Request (app/Http/Requests/)
class ProjectIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => 'nullable|in:active,completed,on_hold',
            'search' => 'nullable|string|max:255',
        ];
    }
}

// 7. API Resource (app/Http/Resources/)
class ProjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // ... other fields
        ];
    }
}
```

#### Phase 3: Frontend Implementation (Vue.js)
```typescript
// ALWAYS follow this component structure

// 1. Types (types/models/project.ts)
export interface Project {
  id: string;
  name: string;
  status: ProjectStatus;
  // ... other fields
}

export interface CreateProjectDTO {
  name: string;
  type: ProjectType;
  // ... other fields
}

// 2. API Service (services/api/projectApi.ts)
import { api } from '@/services/api/client';

export const projectApi = {
  async getProjects(params?: ProjectFilters): Promise<PaginatedResponse<Project>> {
    const { data } = await api.get('/api/v1/projects', { params });
    return data;
  },
  
  async createProject(project: CreateProjectDTO): Promise<Project> {
    const { data } = await api.post('/api/v1/projects', project);
    return data;
  }
};

// 3. Store (stores/project.ts)
import { defineStore } from 'pinia';

export const useProjectStore = defineStore('project', () => {
  const projects = ref<Project[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  
  const fetchProjects = async (filters?: ProjectFilters) => {
    loading.value = true;
    try {
      const response = await projectApi.getProjects(filters);
      projects.value = response.data;
    } catch (e) {
      error.value = e.message;
    } finally {
      loading.value = false;
    }
  };
  
  return {
    projects: readonly(projects),
    loading: readonly(loading),
    error: readonly(error),
    fetchProjects
  };
});

// 4. Composable (composables/useProject.ts)
export function useProject() {
  const store = useProjectStore();
  const route = useRoute();
  
  const currentProject = computed(() => 
    store.projects.find(p => p.id === route.params.id)
  );
  
  onMounted(() => {
    if (!store.projects.length) {
      store.fetchProjects();
    }
  });
  
  return {
    currentProject,
    loading: store.loading,
    error: store.error
  };
}

// 5. Component (components/projects/ProjectList.vue)
<template>
  <div class="space-y-4">
    <!-- Header with inline stats -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-6">
        <h1 class="text-2xl font-bold">Projects</h1>
        <div class="flex items-center gap-4 text-sm text-muted-foreground">
          <span>Total: <strong>{{ stats.total }}</strong></span>
          <span class="text-green-600">Active: <strong>{{ stats.active }}</strong></span>
          <span class="text-blue-600">This Month: <strong>{{ stats.thisMonth }}</strong></span>
        </div>
      </div>
      <Button @click="openCreateDialog" size="sm">
        <Plus class="mr-2 h-4 w-4" />
        Add Project
      </Button>
    </div>
    
    <!-- Inline Filters -->
    <div class="flex items-center gap-2">
      <Input
        v-model="filters.search"
        placeholder="Search projects..."
        class="max-w-xs h-8"
      />
      <Select v-model="filters.status" class="w-32 h-8">
        <SelectTrigger>
          <SelectValue placeholder="Status" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="all">All Status</SelectItem>
          <SelectItem value="active">Active</SelectItem>
          <SelectItem value="completed">Completed</SelectItem>
        </SelectContent>
      </Select>
      <Button variant="ghost" size="icon" class="h-8 w-8">
        <Settings2 class="h-4 w-4" />
      </Button>
    </div>
    
    <!-- Data Table -->
    <DataTable
      :columns="columns"
      :data="projects"
      :loading="loading"
      :pagination="pagination"
      @sort="handleSort"
      @page-change="handlePageChange"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useProjectStore } from '@/stores/project';
import { DataTable } from '@/components/ui/data-table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Plus, Settings2 } from 'lucide-vue-next';

const store = useProjectStore();
const filters = ref<ProjectFilters>({
  search: '',
  status: 'all'
});

const columns = [
  {
    key: 'name',
    label: 'Project Name',
    sortable: true
  },
  {
    key: 'status',
    label: 'Status',
    sortable: true,
    cell: ({ row }) => (
      <Badge variant={getStatusVariant(row.status)}>
        {row.status}
      </Badge>
    )
  },
  // ... more columns
];

onMounted(() => {
  store.fetchProjects(filters.value);
});
</script>
```

#### Phase 4: Testing

**Laravel Testing:**
```php
// tests/Feature/ProjectTest.php
class ProjectTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function user_can_create_project()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->postJson('/api/v1/projects', [
                'name' => 'Test Project',
                'type' => 'commercial',
                'budget' => 100000
            ]);
            
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'type', 'budget']
            ]);
            
        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project'
        ]);
    }
}
```

**Vue.js Testing:**
```typescript
// tests/unit/components/ProjectList.spec.ts
import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import ProjectList from '@/components/projects/ProjectList.vue';
import { createTestingPinia } from '@pinia/testing';

describe('ProjectList', () => {
  it('displays projects correctly', async () => {
    const wrapper = mount(ProjectList, {
      global: {
        plugins: [
          createTestingPinia({
            initialState: {
              project: {
                projects: [
                  { id: '1', name: 'Test Project', status: 'active' }
                ]
              }
            }
          })
        ]
      }
    });
    
    expect(wrapper.text()).toContain('Test Project');
    expect(wrapper.find('[data-testid="status-badge"]').text()).toBe('active');
  });
});
```

### 3. Code Quality Standards

#### Laravel Standards
```php
// ✅ GOOD - Repository Pattern
class ProjectRepository implements ProjectRepositoryInterface
{
    public function findActive(): Collection
    {
        return Project::active()
            ->with(['tasks', 'team'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}

// ❌ BAD - Direct Eloquent in Controller
class ProjectController extends Controller
{
    public function index()
    {
        // NEVER do this!
        return Project::where('status', 'active')->get();
    }
}

// ✅ GOOD - Service Layer
class ProjectService
{
    public function createProject(ProjectData $data): Project
    {
        return DB::transaction(function () use ($data) {
            $project = $this->repository->create($data->toArray());
            
            event(new ProjectCreated($project));
            
            ProjectCreatedJob::dispatch($project);
            
            return $project;
        });
    }
}

// ✅ GOOD - API Resource
class ProjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'progress' => $this->progress,
            'team' => UserResource::collection($this->whenLoaded('team')),
            'tasks_count' => $this->when($this->tasks_count !== null, $this->tasks_count),
            'links' => [
                'self' => route('projects.show', $this->id),
                'tasks' => route('projects.tasks.index', $this->id)
            ]
        ];
    }
}
```

#### Vue.js Standards
```typescript
// ✅ GOOD - Composition API with TypeScript
<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import type { Project } from '@/types/models/project';

interface Props {
  project: Project;
  editable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  editable: false
});

const emit = defineEmits<{
  update: [project: Project];
  delete: [id: string];
}>();

const isEditing = ref(false);
const formData = ref<Partial<Project>>({...props.project});

const canSave = computed(() => {
  return formData.value.name && formData.value.name.length > 0;
});

const handleSave = async () => {
  if (!canSave.value) return;
  
  try {
    const updated = await projectApi.updateProject(props.project.id, formData.value);
    emit('update', updated);
    isEditing.value = false;
  } catch (error) {
    console.error('Failed to update project:', error);
  }
};
</script>

// ❌ BAD - Options API, no TypeScript
export default {
  props: ['project'],
  data() {
    return {
      isEditing: false,
      formData: {}
    }
  },
  methods: {
    handleSave() {
      // No type safety!
      this.$emit('update', this.formData);
    }
  }
}
```

### 4. UI/UX Standards (CRITICAL)

#### Component Usage Rules
```vue
<!-- ✅ GOOD - Using centralized components -->
<template>
  <Card>
    <CardHeader>
      <CardTitle>Project Details</CardTitle>
    </CardHeader>
    <CardContent>
      <DataTable :columns="columns" :data="projects" />
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DataTable } from '@/components/ui/data-table';
</script>

<!-- ❌ BAD - Custom styling and inline styles -->
<template>
  <div style="padding: 20px; border: 1px solid #ccc;">
    <h2 class="custom-title">Project Details</h2>
    <table class="custom-table">
      <!-- Never do this! -->
    </table>
  </div>
</template>

<style>
.custom-title { /* NEVER create custom CSS! */ }
</style>
```

#### Data Table Requirements
```typescript
// ✅ GOOD - Proper data table configuration
const pagination = ref({
  page: 1,
  perPage: 50, // Default 50 rows
  total: 0,
  perPageOptions: [10, 25, 50, 100, 250] // Required options
});

const columns: ColumnDef[] = [
  {
    key: 'name',
    label: 'Project Name',
    sortable: true, // ALL columns must be sortable
    width: '250px'
  },
  {
    key: 'status',
    label: 'Status',
    sortable: true,
    cell: ({ row }) => (
      <Badge variant={getStatusVariant(row.status)}>
        {row.status}
      </Badge>
    )
  },
  {
    key: 'actions',
    label: '',
    width: '100px',
    cell: ({ row }) => (
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <Button variant="ghost" size="icon">
            <MoreHorizontal class="h-4 w-4" />
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
          <DropdownMenuItem @click="editProject(row)">
            <Edit class="mr-2 h-4 w-4" />
            Edit
          </DropdownMenuItem>
          <DropdownMenuItem @click="deleteProject(row.id)">
            <Trash class="mr-2 h-4 w-4" />
            Delete
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    )
  }
];
```

### 5. Project Structure

```
construction-platform/
├── backend/                      # Laravel Backend
│   ├── app/
│   │   ├── Domain/              # Domain-Driven Design
│   │   │   ├── Project/         # Project Domain
│   │   │   │   ├── Models/
│   │   │   │   ├── Services/
│   │   │   │   ├── Repositories/
│   │   │   │   ├── DTOs/
│   │   │   │   ├── Events/
│   │   │   │   └── Jobs/
│   │   │   ├── User/            # User Domain
│   │   │   ├── Task/            # Task Domain
│   │   │   └── Finance/         # Finance Domain
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── Api/         # API Controllers only
│   │   │   ├── Middleware/
│   │   │   ├── Requests/        # Form Requests
│   │   │   └── Resources/       # API Resources
│   │   └── Support/             # Helpers & Utilities
│   ├── config/
│   ├── database/
│   │   ├── migrations/
│   │   ├── factories/
│   │   └── seeders/
│   ├── routes/
│   │   └── api.php              # API routes only
│   └── tests/
│       ├── Unit/
│       ├── Feature/
│       └── Integration/
├── frontend/                     # Vue.js Frontend
│   ├── src/
│   │   ├── components/
│   │   │   ├── ui/              # CENTRALIZED UI COMPONENTS
│   │   │   ├── layout/          # Layout components
│   │   │   └── features/        # Feature components
│   │   ├── composables/         # Vue composables
│   │   ├── pages/               # Page components
│   │   ├── stores/              # Pinia stores
│   │   ├── services/            # API services
│   │   │   └── api/
│   │   ├── types/               # TypeScript types
│   │   ├── utils/               # Utilities
│   │   ├── router/              # Vue Router
│   │   └── assets/              # Static assets
│   └── tests/
│       ├── unit/
│       └── e2e/
├── docs/                         # Documentation
│   ├── API.md                   # API documentation
│   ├── DEPLOYMENT.md            # Deployment guide
│   ├── tasks/                   # Task documentation
│   └── architecture/            # Architecture diagrams
└── scripts/                      # Utility scripts
```

### 6. API Design Standards

#### RESTful Endpoints
```php
// routes/api.php
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Projects
    Route::get('/projects', [ProjectController::class, 'index']);        // GET all
    Route::post('/projects', [ProjectController::class, 'store']);       // CREATE
    Route::get('/projects/{project}', [ProjectController::class, 'show']); // GET one
    Route::put('/projects/{project}', [ProjectController::class, 'update']); // UPDATE
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']); // DELETE
    
    // Nested Resources
    Route::get('/projects/{project}/tasks', [TaskController::class, 'index']);
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
    
    // Actions
    Route::post('/projects/{project}/archive', [ProjectController::class, 'archive']);
    Route::post('/projects/{project}/restore', [ProjectController::class, 'restore']);
});
```

#### Response Format
```json
{
  "data": {
    "id": "uuid",
    "type": "project",
    "attributes": {
      "name": "Downtown Complex",
      "status": "active"
    },
    "relationships": {
      "tasks": {
        "data": [
          {"id": "task-1", "type": "task"}
        ]
      }
    }
  },
  "meta": {
    "total": 100,
    "per_page": 50,
    "current_page": 1
  }
}
```

### 7. Database Standards

#### Migration Naming
```bash
# Format: yyyy_mm_dd_hhmmss_action_table_name.php
2024_01_01_000001_create_projects_table.php
2024_01_02_000001_add_budget_to_projects_table.php
2024_01_03_000001_create_project_user_pivot_table.php
```

#### Model Conventions
```php
class Project extends Model
{
    // ALWAYS use UUIDs
    use HasUuid;
    
    // ALWAYS use soft deletes for important data
    use SoftDeletes;
    
    // ALWAYS define fillable or guarded
    protected $fillable = ['name', 'type', 'budget'];
    
    // ALWAYS cast dates and JSON
    protected $casts = [
        'budget' => 'decimal:2',
        'start_date' => 'date',
        'metadata' => 'array',
        'is_active' => 'boolean'
    ];
    
    // ALWAYS define relationships
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
    
    // Use scopes for common queries
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
```

### 8. Security Guidelines

#### Laravel Security
```php
// ✅ GOOD - Use Form Requests for validation
class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Project::class);
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240'
        ];
    }
}

// ✅ GOOD - Use policies for authorization
class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id;
    }
}

// ✅ GOOD - Sanitize user input
$data = $request->validated();
$data['description'] = strip_tags($data['description']);
```

#### Vue.js Security
```typescript
// ✅ GOOD - Validate and sanitize
import DOMPurify from 'dompurify';

const sanitizedHtml = DOMPurify.sanitize(userInput);

// ✅ GOOD - Use v-text instead of v-html when possible
<span v-text="userContent"></span>

// ❌ BAD - Direct v-html with user content
<div v-html="userContent"></div>

// ✅ GOOD - Validate file uploads
const validateFile = (file: File): boolean => {
  const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
  const maxSize = 10 * 1024 * 1024; // 10MB
  
  return allowedTypes.includes(file.type) && file.size <= maxSize;
};
```

### 9. Performance Standards

#### Laravel Performance
```php
// ✅ GOOD - Eager loading
$projects = Project::with(['tasks', 'team', 'client'])->get();

// ❌ BAD - N+1 queries
$projects = Project::all();
foreach ($projects as $project) {
    echo $project->tasks->count(); // N+1 problem!
}

// ✅ GOOD - Use caching
$projects = Cache::remember('user.projects.' . $userId, 3600, function () use ($userId) {
    return Project::where('user_id', $userId)->get();
});

// ✅ GOOD - Queue heavy operations
ProcessProjectReportJob::dispatch($project)->onQueue('reports');

// ✅ GOOD - Database indexing
Schema::table('projects', function (Blueprint $table) {
    $table->index(['company_id', 'status']);
    $table->index('created_at');
});
```

#### Vue.js Performance
```typescript
// ✅ GOOD - Lazy loading routes
const routes = [
  {
    path: '/projects',
    component: () => import('@/pages/projects/Index.vue')
  }
];

// ✅ GOOD - Use computed for derived state
const activeProjects = computed(() => 
  projects.value.filter(p => p.status === 'active')
);

// ✅ GOOD - Debounce search inputs
import { debounce } from 'lodash-es';

const searchProjects = debounce(async (query: string) => {
  await store.searchProjects(query);
}, 300);

// ✅ GOOD - Virtual scrolling for large lists
<VirtualList
  :items="projects"
  :item-height="80"
  :overscan="5"
>
  <template #default="{ item }">
    <ProjectCard :project="item" />
  </template>
</VirtualList>
```

### 10. Git Workflow

#### Branch Strategy
```bash
main                 # Production-ready code
├── develop         # Integration branch
    ├── feature/CONST-001-project-crud
    ├── feature/CONST-002-task-management
    ├── bugfix/CONST-003-fix-validation
    └── hotfix/CONST-004-critical-fix
```

#### Commit Message Format
```bash
# Format: [Type][Scope] Brief description

[feat][projects] Add project budget tracking
[fix][tasks] Resolve subtask dependency calculation
[refactor][api] Optimize project query performance
[test][auth] Add unit tests for login flow
[docs][api] Update project endpoints documentation
[style][ui] Update button styles to match design
[chore][deps] Update Laravel to 11.x
```

#### Pre-Push Checklist
```bash
# Backend checks
php artisan test
php artisan migrate:fresh --seed
./vendor/bin/phpstan analyse
./vendor/bin/pint --test

# Frontend checks
npm run type-check
npm run lint
npm run test:unit
npm run build

# Both
git status # Ensure no unwanted files
```

## Port Configuration

**IMPORTANT**: This project uses odd port numbers between 3070-3080 to avoid conflicts with other projects:

- **Laravel Backend**: Port **3071** (API server)
- **Vue.js Frontend**: Port **3073** (Vite dev server)  
- **Database (PostgreSQL)**: Port **3075**
- **Redis**: Port **3077**
- **Horizon Dashboard**: Port **3079**

### Environment Configuration
```bash
# .env (Laravel)
APP_URL=http://localhost:3071
DB_PORT=3075
REDIS_PORT=3077
HORIZON_PORT=3079

# .env.local (Vue.js)
VITE_API_URL=http://localhost:3071/api
VITE_DEV_PORT=3073

# docker-compose.yml ports mapping
laravel:
  ports:
    - "3071:80"
postgres:
  ports:
    - "3075:5432"
redis:
  ports:
    - "3077:6379"
```

## Quick Reference Commands

### Laravel Commands
```bash
# Development
sail up -d                          # Start Docker containers
sail artisan serve --port=3071      # Start development server on port 3071
sail artisan migrate:fresh --seed   # Reset database with seeds
sail artisan queue:work             # Start queue worker
sail artisan horizon               # Start Horizon dashboard (port 3079)

# Testing
sail artisan test                   # Run all tests
sail artisan test --filter=ProjectTest  # Run specific test
sail artisan test --coverage       # Run with coverage

# Code Quality
./vendor/bin/pint                  # Format code (Laravel Pint)
./vendor/bin/phpstan analyse       # Static analysis
./vendor/bin/pest                  # Run Pest tests

# Debugging
sail artisan tinker                # Interactive console
sail artisan telescope             # Open Telescope dashboard
sail artisan route:list           # List all routes
```

### Vue.js Commands
```bash
# Development
npm run dev -- --port 3073         # Start Vite dev server on port 3073
npm run build                      # Build for production
npm run preview -- --port 3073     # Preview production build on port 3073

# Testing
npm run test:unit                  # Run unit tests
npm run test:e2e                   # Run E2E tests
npm run coverage                   # Generate coverage report

# Code Quality
npm run lint                       # Run ESLint
npm run lint:fix                   # Fix linting issues
npm run type-check                 # TypeScript checking
npm run format                     # Format with Prettier
```

## Important Reminders

### Before Starting Any Task
- [ ] Read this entire policy document
- [ ] **Verify port configuration** (Laravel: 3071, Vue: 3073, PostgreSQL: 3075, Redis: 3077, Horizon: 3079)
- [ ] Check if UI components exist in `/components/ui/`
- [ ] Review the domain structure
- [ ] Plan API endpoints and database changes
- [ ] Create task documentation

### During Development
- [ ] Follow the repository pattern (Laravel)
- [ ] Use Composition API (Vue.js)
- [ ] Write tests as you code
- [ ] Keep commits atomic and well-described
- [ ] Update API documentation

### Before Submitting PR
- [ ] All tests passing
- [ ] No console.log or dd() statements
- [ ] Code follows standards
- [ ] Documentation updated
- [ ] Performance impact assessed
- [ ] Security vulnerabilities checked

## Common Mistakes to Avoid

### Laravel
❌ Direct Eloquent queries in controllers
❌ Business logic in controllers
❌ Missing database transactions
❌ Not using API Resources
❌ Forgetting to queue heavy operations
❌ Not caching expensive queries

### Vue.js
❌ Using Options API
❌ Not typing props and emits
❌ Inline styles or custom CSS
❌ Not using centralized UI components
❌ Forgetting loading and error states
❌ Direct API calls in components (use stores)

### General
❌ Committing sensitive data
❌ Not writing tests
❌ Poor commit messages
❌ Not documenting decisions
❌ Ignoring performance
❌ Skipping code reviews

---
**Last Updated:** 2024  
**Version:** 1.0  
**Maintained by:** Development Team

> **Remember:** Quality over speed. A well-structured, tested, and documented feature is worth more than a quickly hacked solution.