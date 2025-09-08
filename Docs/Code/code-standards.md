# Construction Platform - Code Standards & Best Practices Guide

**Version:** 1.0  
**Last Updated:** January 2025  
**Stack:** Laravel 11.x + Vue.js 3.4 + TypeScript  
**Status:** Enforced

## 📋 Table of Contents

1. [General Principles](#general-principles)
2. [Laravel Backend Standards](#laravel-backend-standards)
3. [Vue.js Frontend Standards](#vuejs-frontend-standards)
4. [Code Reusability Patterns](#code-reusability-patterns)
5. [Testing Standards](#testing-standards)
6. [Performance Guidelines](#performance-guidelines)
7. [Security Standards](#security-standards)
8. [Documentation Standards](#documentation-standards)

---

## 🎯 General Principles

### Core Philosophy
1. **DRY (Don't Repeat Yourself)** - Extract common logic into reusable components
2. **SOLID Principles** - Single responsibility, Open/closed, Liskov substitution, Interface segregation, Dependency inversion
3. **KISS (Keep It Simple, Stupid)** - Prefer simple, readable solutions
4. **YAGNI (You Aren't Gonna Need It)** - Don't add functionality until necessary
5. **Boy Scout Rule** - Leave code better than you found it

### Naming Conventions

#### Universal Rules
```php
// PHP/Laravel
$camelCase          // Variables
CONSTANT_VALUE      // Constants
PascalCase          // Classes
camelCase()         // Methods
snake_case          // Database columns
kebab-case          // URLs/Routes

// TypeScript/Vue
camelCase           // Variables, functions
PascalCase          // Components, Types, Interfaces
CONSTANT_VALUE      // Constants
kebab-case          // HTML attributes, CSS classes
```

---

## 🔧 Laravel Backend Standards

### 1. File Organization & Architecture

#### Domain-Driven Structure
```
app/
├── Domain/                    # Business Logic
│   ├── Project/
│   │   ├── Actions/          # Single-purpose actions
│   │   │   ├── CreateProjectAction.php
│   │   │   ├── UpdateProjectStatusAction.php
│   │   │   └── CalculateProjectProgressAction.php
│   │   ├── DTOs/             # Data Transfer Objects
│   │   │   ├── ProjectData.php
│   │   │   └── ProjectFilterData.php
│   │   ├── Models/           # Eloquent Models
│   │   │   ├── Project.php
│   │   │   └── ProjectMilestone.php
│   │   ├── QueryBuilders/    # Custom Query Builders
│   │   │   └── ProjectQueryBuilder.php
│   │   ├── Repositories/     # Data Access Layer
│   │   │   ├── ProjectRepositoryInterface.php
│   │   │   └── ProjectRepository.php
│   │   ├── Services/         # Business Logic
│   │   │   ├── ProjectService.php
│   │   │   ├── ProjectSchedulingService.php
│   │   │   └── ProjectBudgetService.php
│   │   ├── Events/           # Domain Events
│   │   ├── Listeners/        # Event Listeners
│   │   ├── Observers/        # Model Observers
│   │   └── Policies/         # Authorization Policies
│   ├── Task/
│   ├── User/
│   └── Shared/               # Shared domain logic
│       ├── Traits/
│       ├── Interfaces/
│       └── ValueObjects/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── V1/           # Versioned API
│   ├── Middleware/
│   ├── Requests/             # Form Requests
│   └── Resources/            # API Resources
└── Support/                   # Helpers & Utilities
```

### 2. Model Standards

#### Model Template
```php
<?php

namespace App\Domain\Project\Models;

use App\Domain\Shared\Traits\HasUuid;
use App\Domain\Shared\Traits\HasCreator;
use App\Domain\Shared\Traits\Searchable;
use App\Domain\Project\QueryBuilders\ProjectQueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory, HasUuid, SoftDeletes, HasCreator, Searchable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'status',
        'budget',
        'start_date',
        'end_date',
        'location',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'budget' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'location' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'progress_percentage',
        'is_overdue',
        'days_remaining',
    ];

    /**
     * The searchable fields for the model.
     */
    protected $searchable = [
        'name',
        'code',
        'description',
    ];

    /**
     * Get the custom query builder for the model.
     */
    public function newEloquentBuilder($query): ProjectQueryBuilder
    {
        return new ProjectQueryBuilder($query);
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Project $project) {
            $project->code = $project->code ?? Project::generateCode();
            $project->status = $project->status ?? ProjectStatus::PLANNING;
        });
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the tasks for the project.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)
            ->orderBy('priority')
            ->orderBy('due_date');
    }

    /**
     * Get the project manager.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the team members.
     */
    public function team(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_users')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    // ==================== SCOPES ====================

    /**
     * Scope a query to only include active projects.
     */
    public function scopeActive($query)
    {
        return $query->where('status', ProjectStatus::ACTIVE);
    }

    /**
     * Scope a query to only include projects within budget.
     */
    public function scopeWithinBudget($query)
    {
        return $query->whereRaw('spent <= budget');
    }

    // ==================== ACCESSORS ====================

    /**
     * Get the project's progress percentage.
     */
    public function getProgressPercentageAttribute(): int
    {
        if ($this->tasks->isEmpty()) {
            return 0;
        }

        return round(
            $this->tasks->where('status', TaskStatus::COMPLETED)->count() / 
            $this->tasks->count() * 100
        );
    }

    /**
     * Check if the project is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->end_date < now() && 
               $this->status !== ProjectStatus::COMPLETED;
    }

    // ==================== METHODS ====================

    /**
     * Generate a unique project code.
     */
    public static function generateCode(): string
    {
        $prefix = 'PRJ';
        $year = now()->format('Y');
        $lastProject = static::whereYear('created_at', $year)
            ->orderBy('code', 'desc')
            ->first();

        $number = $lastProject 
            ? intval(substr($lastProject->code, -4)) + 1 
            : 1;

        return sprintf('%s-%s-%04d', $prefix, $year, $number);
    }

    /**
     * Calculate the budget variance.
     */
    public function getBudgetVariance(): float
    {
        return $this->budget - $this->spent;
    }

    /**
     * Check if user can access this project.
     */
    public function canBeAccessedBy(User $user): bool
    {
        return $this->manager_id === $user->id ||
               $this->team->contains($user);
    }
}
```

### 3. Repository Pattern

#### Repository Interface
```php
<?php

namespace App\Domain\Project\Repositories;

use App\Domain\Project\Models\Project;
use App\Domain\Project\DTOs\ProjectFilterData;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProjectRepositoryInterface
{
    public function find(string $id): ?Project;
    
    public function findOrFail(string $id): Project;
    
    public function getAll(ProjectFilterData $filters): Collection;
    
    public function paginate(ProjectFilterData $filters, int $perPage = 50): LengthAwarePaginator;
    
    public function create(array $data): Project;
    
    public function update(Project $project, array $data): Project;
    
    public function delete(Project $project): bool;
    
    public function getByUser(User $user, ProjectFilterData $filters): Collection;
    
    public function getActiveProjects(): Collection;
    
    public function getOverdueProjects(): Collection;
}
```

#### Repository Implementation
```php
<?php

namespace App\Domain\Project\Repositories;

use App\Domain\Project\Models\Project;
use App\Domain\Project\DTOs\ProjectFilterData;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function __construct(
        private Project $model
    ) {}

    public function find(string $id): ?Project
    {
        return Cache::tags(['projects'])->remember(
            "project.{$id}",
            3600,
            fn() => $this->model->with(['tasks', 'team'])->find($id)
        );
    }

    public function paginate(ProjectFilterData $filters, int $perPage = 50): LengthAwarePaginator
    {
        return $this->model->query()
            ->when($filters->search, fn($q, $search) => 
                $q->search($search)
            )
            ->when($filters->status, fn($q, $status) => 
                $q->where('status', $status)
            )
            ->when($filters->type, fn($q, $type) => 
                $q->where('type', $type)
            )
            ->when($filters->dateFrom, fn($q, $date) => 
                $q->where('start_date', '>=', $date)
            )
            ->when($filters->dateTo, fn($q, $date) => 
                $q->where('end_date', '<=', $date)
            )
            ->with(['manager', 'tasks' => fn($q) => $q->pending()])
            ->orderBy($filters->sortBy ?? 'created_at', $filters->sortDirection ?? 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Project
    {
        $project = $this->model->create($data);
        
        Cache::tags(['projects'])->flush();
        
        return $project->fresh(['team', 'tasks']);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);
        
        Cache::tags(['projects'])->forget("project.{$project->id}");
        
        return $project->fresh();
    }

    public function getActiveProjects(): Collection
    {
        return Cache::tags(['projects'])->remember(
            'projects.active',
            1800,
            fn() => $this->model->active()
                ->with(['manager', 'currentTasks'])
                ->get()
        );
    }
}
```

### 4. Service Layer

```php
<?php

namespace App\Domain\Project\Services;

use App\Domain\Project\Models\Project;
use App\Domain\Project\DTOs\ProjectData;
use App\Domain\Project\Actions\CreateProjectAction;
use App\Domain\Project\Actions\UpdateProjectStatusAction;
use App\Domain\Project\Actions\AssignTeamMembersAction;
use App\Domain\Project\Repositories\ProjectRepositoryInterface;
use App\Domain\Task\Services\TaskService;
use App\Domain\Notification\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectService
{
    public function __construct(
        private ProjectRepositoryInterface $repository,
        private CreateProjectAction $createAction,
        private UpdateProjectStatusAction $statusAction,
        private AssignTeamMembersAction $teamAction,
        private TaskService $taskService,
        private NotificationService $notificationService
    ) {}

    /**
     * Create a new project with initial setup.
     */
    public function createProject(ProjectData $data, User $creator): Project
    {
        return DB::transaction(function () use ($data, $creator) {
            // Create the project
            $project = $this->createAction->execute($data, $creator);
            
            // Assign team members
            if ($data->teamMemberIds) {
                $this->teamAction->execute($project, $data->teamMemberIds);
            }
            
            // Create default tasks based on project type
            $this->taskService->createDefaultTasks($project);
            
            // Send notifications
            $this->notificationService->notifyProjectCreated($project);
            
            // Log activity
            activity()
                ->performedOn($project)
                ->causedBy($creator)
                ->withProperties(['data' => $data->toArray()])
                ->log('Project created');
            
            return $project->fresh(['team', 'tasks']);
        });
    }

    /**
     * Update project status with validations.
     */
    public function updateProjectStatus(Project $project, string $newStatus, User $user): Project
    {
        // Validate status transition
        if (!$this->canTransitionTo($project->status, $newStatus)) {
            throw new InvalidStatusTransitionException(
                "Cannot transition from {$project->status} to {$newStatus}"
            );
        }
        
        return DB::transaction(function () use ($project, $newStatus, $user) {
            $oldStatus = $project->status;
            
            // Update status
            $project = $this->statusAction->execute($project, $newStatus);
            
            // Handle status-specific logic
            $this->handleStatusChange($project, $oldStatus, $newStatus);
            
            // Log activity
            activity()
                ->performedOn($project)
                ->causedBy($user)
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ])
                ->log('Project status updated');
            
            return $project;
        });
    }

    /**
     * Calculate project health score.
     */
    public function calculateHealthScore(Project $project): ProjectHealthScore
    {
        $score = 100;
        $issues = [];
        
        // Check if overdue
        if ($project->is_overdue) {
            $score -= 30;
            $issues[] = 'Project is overdue';
        }
        
        // Check budget
        $budgetUsage = ($project->spent / $project->budget) * 100;
        if ($budgetUsage > 90) {
            $score -= 20;
            $issues[] = 'Budget nearly exhausted';
        }
        
        // Check task completion rate
        if ($project->progress_percentage < 50 && $project->getDaysRemaining() < 30) {
            $score -= 25;
            $issues[] = 'Behind schedule';
        }
        
        // Check team allocation
        if ($project->team->count() < 3) {
            $score -= 10;
            $issues[] = 'Understaffed';
        }
        
        return new ProjectHealthScore(
            score: max(0, $score),
            status: $this->getHealthStatus($score),
            issues: $issues,
            recommendations: $this->getRecommendations($issues)
        );
    }

    /**
     * Validate status transition.
     */
    private function canTransitionTo(string $currentStatus, string $newStatus): bool
    {
        $transitions = [
            ProjectStatus::PLANNING => [ProjectStatus::ACTIVE, ProjectStatus::CANCELLED],
            ProjectStatus::ACTIVE => [ProjectStatus::ON_HOLD, ProjectStatus::COMPLETED, ProjectStatus::CANCELLED],
            ProjectStatus::ON_HOLD => [ProjectStatus::ACTIVE, ProjectStatus::CANCELLED],
            ProjectStatus::COMPLETED => [],
            ProjectStatus::CANCELLED => [],
        ];
        
        return in_array($newStatus, $transitions[$currentStatus] ?? []);
    }
}
```

### 5. API Controller Standards

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Requests\Project\IndexProjectRequest;
use App\Http\Resources\Project\ProjectResource;
use App\Http\Resources\Project\ProjectCollection;
use App\Domain\Project\Services\ProjectService;
use App\Domain\Project\DTOs\ProjectData;
use App\Domain\Project\DTOs\ProjectFilterData;
use App\Domain\Project\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $projectService
    ) {}

    /**
     * Display a listing of projects.
     */
    public function index(IndexProjectRequest $request): JsonResource
    {
        $filters = ProjectFilterData::from($request->validated());
        
        $projects = $this->projectService->getPaginatedProjects(
            user: $request->user(),
            filters: $filters,
            perPage: $request->get('per_page', 50)
        );
        
        return new ProjectCollection($projects);
    }

    /**
     * Store a newly created project.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $data = ProjectData::from($request->validated());
        
        $project = $this->projectService->createProject(
            data: $data,
            creator: $request->user()
        );
        
        return response()->json([
            'message' => 'Project created successfully',
            'data' => new ProjectResource($project)
        ], 201);
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project): JsonResource
    {
        $this->authorize('view', $project);
        
        $project->load([
            'tasks' => fn($q) => $q->pending(),
            'team.profile',
            'documents',
            'milestones'
        ]);
        
        return new ProjectResource($project);
    }

    /**
     * Update the specified project.
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        
        $data = ProjectData::from($request->validated());
        
        $project = $this->projectService->updateProject(
            project: $project,
            data: $data,
            user: $request->user()
        );
        
        return response()->json([
            'message' => 'Project updated successfully',
            'data' => new ProjectResource($project)
        ]);
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        
        $this->projectService->deleteProject($project);
        
        return response()->json([
            'message' => 'Project deleted successfully'
        ], 204);
    }

    /**
     * Update project status.
     */
    public function updateStatus(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        
        $request->validate([
            'status' => ['required', 'string', Rule::in(ProjectStatus::values())]
        ]);
        
        $project = $this->projectService->updateProjectStatus(
            project: $project,
            newStatus: $request->status,
            user: $request->user()
        );
        
        return response()->json([
            'message' => 'Project status updated successfully',
            'data' => new ProjectResource($project)
        ]);
    }
}
```

### 6. Form Request Validation

```php
<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Domain\Project\Enums\ProjectType;
use App\Domain\Project\Enums\ProjectStatus;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Project::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:projects,code'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', Rule::enum(ProjectType::class)],
            'budget' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'manager_id' => ['required', 'uuid', 'exists:users,id'],
            'team_member_ids' => ['nullable', 'array'],
            'team_member_ids.*' => ['uuid', 'exists:users,id'],
            'location' => ['required', 'array'],
            'location.address' => ['required', 'string', 'max:255'],
            'location.city' => ['required', 'string', 'max:100'],
            'location.state' => ['required', 'string', 'max:100'],
            'location.zip' => ['required', 'string', 'max:20'],
            'location.coordinates' => ['nullable', 'array'],
            'location.coordinates.lat' => ['required_with:location.coordinates', 'numeric', 'between:-90,90'],
            'location.coordinates.lng' => ['required_with:location.coordinates', 'numeric', 'between:-180,180'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Project name is required',
            'budget.required' => 'Project budget is required',
            'budget.max' => 'Budget cannot exceed $999,999,999',
            'start_date.after_or_equal' => 'Start date cannot be in the past',
            'end_date.after' => 'End date must be after start date',
            'manager_id.exists' => 'Selected project manager does not exist',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'budget' => $this->budget ? str_replace(',', '', $this->budget) : null,
        ]);
    }
}
```

---

## 🎨 Vue.js Frontend Standards

### 1. Component Structure

#### Component Template
```vue
<template>
  <div class="project-manager">
    <!-- Component content -->
  </div>
</template>

<script setup lang="ts">
// ==================== IMPORTS ====================
// Vue Core
import { ref, computed, watch, onMounted, defineProps, defineEmits } from 'vue';
import { useRouter, useRoute } from 'vue-router';

// Stores
import { useProjectStore } from '@/stores/project';
import { useAuthStore } from '@/stores/auth';

// Composables
import { useProject } from '@/composables/useProject';
import { usePermissions } from '@/composables/usePermissions';

// Components
import { VButton, VCard, VTable } from '@/components/ui';
import ProjectForm from '@/components/projects/ProjectForm.vue';

// Types
import type { Project, ProjectFilters } from '@/types/models/project';

// Utils
import { formatDate, formatCurrency } from '@/utils/formatters';
import { validateProject } from '@/utils/validators';

// Icons
import { Plus, Edit, Trash } from 'lucide-vue-next';

// ==================== PROPS & EMITS ====================
interface Props {
  projectId?: string;
  editable?: boolean;
  showActions?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  editable: true,
  showActions: true
});

const emit = defineEmits<{
  'update': [project: Project];
  'delete': [id: string];
  'select': [project: Project];
}>();

// ==================== STATE ====================
const projectStore = useProjectStore();
const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();

// Local State
const loading = ref(false);
const error = ref<string | null>(null);
const selectedProject = ref<Project | null>(null);
const filters = ref<ProjectFilters>({
  search: '',
  status: 'all',
  type: null,
  dateRange: null
});

// ==================== COMPUTED ====================
const currentUser = computed(() => authStore.user);
const projects = computed(() => projectStore.filteredProjects(filters.value));
const canEdit = computed(() => props.editable && hasPermission('project.edit'));
const canDelete = computed(() => props.showActions && hasPermission('project.delete'));

const statistics = computed(() => ({
  total: projects.value.length,
  active: projects.value.filter(p => p.status === 'active').length,
  completed: projects.value.filter(p => p.status === 'completed').length,
  thisMonth: projects.value.filter(p => isThisMonth(p.createdAt)).length
}));

// ==================== WATCHERS ====================
watch(filters, (newFilters) => {
  projectStore.fetchProjects(newFilters);
}, { deep: true });

watch(
  () => props.projectId,
  (newId) => {
    if (newId) {
      loadProject(newId);
    }
  }
);

// ==================== LIFECYCLE ====================
onMounted(async () => {
  await initializeComponent();
});

// ==================== METHODS ====================
/**
 * Initialize component data
 */
async function initializeComponent(): Promise<void> {
  loading.value = true;
  try {
    await projectStore.fetchProjects(filters.value);
    
    if (props.projectId) {
      await loadProject(props.projectId);
    }
  } catch (err) {
    error.value = err.message;
    console.error('Failed to initialize:', err);
  } finally {
    loading.value = false;
  }
}

/**
 * Load a specific project
 */
async function loadProject(id: string): Promise<void> {
  try {
    const project = await projectStore.fetchProject(id);
    selectedProject.value = project;
    emit('select', project);
  } catch (err) {
    error.value = `Failed to load project: ${err.message}`;
  }
}

/**
 * Handle project update
 */
async function handleUpdate(project: Project): Promise<void> {
  loading.value = true;
  try {
    const updated = await projectStore.updateProject(project.id, project);
    emit('update', updated);
    showSuccessToast('Project updated successfully');
  } catch (err) {
    showErrorToast('Failed to update project');
  } finally {
    loading.value = false;
  }
}

/**
 * Handle project deletion
 */
async function handleDelete(id: string): Promise<void> {
  if (!confirm('Are you sure you want to delete this project?')) {
    return;
  }
  
  loading.value = true;
  try {
    await projectStore.deleteProject(id);
    emit('delete', id);
    showSuccessToast('Project deleted successfully');
  } catch (err) {
    showErrorToast('Failed to delete project');
  } finally {
    loading.value = false;
  }
}

// ==================== COMPOSABLES ====================
const { hasPermission } = usePermissions();
const { showSuccessToast, showErrorToast } = useToast();
</script>

<style scoped>
/* Component-specific styles if needed */
.project-manager {
  @apply space-y-4;
}
</style>
```

### 2. Composables for Reusability

```typescript
// composables/useProject.ts
import { ref, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useProjectStore } from '@/stores/project';
import { projectApi } from '@/services/api/projectApi';
import type { Project } from '@/types/models/project';

export function useProject(projectId?: string) {
  // State
  const store = useProjectStore();
  const route = useRoute();
  const loading = ref(false);
  const error = ref<Error | null>(null);
  
  // Get project ID from route if not provided
  const id = projectId || route.params.id as string;
  
  // Computed
  const project = computed(() => 
    store.projects.find(p => p.id === id)
  );
  
  const isOwner = computed(() => 
    project.value?.managerId === store.currentUserId
  );
  
  const canEdit = computed(() => 
    isOwner.value || store.hasPermission('project.edit')
  );
  
  // Methods
  async function loadProject(forceRefresh = false) {
    if (project.value && !forceRefresh) {
      return project.value;
    }
    
    loading.value = true;
    error.value = null;
    
    try {
      const data = await projectApi.getProject(id);
      store.setProject(data);
      return data;
    } catch (err) {
      error.value = err as Error;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  async function updateProject(updates: Partial<Project>) {
    loading.value = true;
    error.value = null;
    
    try {
      const updated = await projectApi.updateProject(id, updates);
      store.updateProject(id, updated);
      return updated;
    } catch (err) {
      error.value = err as Error;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  async function deleteProject() {
    loading.value = true;
    error.value = null;
    
    try {
      await projectApi.deleteProject(id);
      store.removeProject(id);
    } catch (err) {
      error.value = err as Error;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  // Auto-load on mount
  if (id) {
    loadProject();
  }
  
  return {
    // State
    project: readonly(project),
    loading: readonly(loading),
    error: readonly(error),
    
    // Computed
    isOwner: readonly(isOwner),
    canEdit: readonly(canEdit),
    
    // Methods
    loadProject,
    updateProject,
    deleteProject,
    refresh: () => loadProject(true)
  };
}
```

### 3. Store Patterns (Pinia)

```typescript
// stores/project.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { projectApi } from '@/services/api/projectApi';
import type { Project, ProjectFilters, ProjectStatistics } from '@/types/models/project';

export const useProjectStore = defineStore('project', () => {
  // ==================== STATE ====================
  const projects = ref<Project[]>([]);
  const currentProject = ref<Project | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const filters = ref<ProjectFilters>({
    search: '',
    status: 'all',
    type: null,
    sortBy: 'createdAt',
    sortOrder: 'desc'
  });
  const pagination = ref({
    page: 1,
    perPage: 50,
    total: 0,
    totalPages: 0
  });

  // ==================== GETTERS ====================
  const activeProjects = computed(() => 
    projects.value.filter(p => p.status === 'active')
  );
  
  const projectsByStatus = computed(() => {
    const grouped = new Map<string, Project[]>();
    
    projects.value.forEach(project => {
      const status = project.status;
      if (!grouped.has(status)) {
        grouped.set(status, []);
      }
      grouped.get(status)!.push(project);
    });
    
    return grouped;
  });
  
  const statistics = computed((): ProjectStatistics => ({
    total: projects.value.length,
    active: activeProjects.value.length,
    completed: projects.value.filter(p => p.status === 'completed').length,
    onHold: projects.value.filter(p => p.status === 'on_hold').length,
    totalBudget: projects.value.reduce((sum, p) => sum + p.budget, 0),
    totalSpent: projects.value.reduce((sum, p) => sum + p.spent, 0)
  }));
  
  const filteredProjects = computed(() => {
    let result = [...projects.value];
    
    // Apply search filter
    if (filters.value.search) {
      const search = filters.value.search.toLowerCase();
      result = result.filter(p => 
        p.name.toLowerCase().includes(search) ||
        p.code.toLowerCase().includes(search)
      );
    }
    
    // Apply status filter
    if (filters.value.status && filters.value.status !== 'all') {
      result = result.filter(p => p.status === filters.value.status);
    }
    
    // Apply type filter
    if (filters.value.type) {
      result = result.filter(p => p.type === filters.value.type);
    }
    
    // Apply sorting
    result.sort((a, b) => {
      const field = filters.value.sortBy || 'createdAt';
      const order = filters.value.sortOrder === 'asc' ? 1 : -1;
      
      if (a[field] < b[field]) return -order;
      if (a[field] > b[field]) return order;
      return 0;
    });
    
    return result;
  });

  // ==================== ACTIONS ====================
  /**
   * Fetch projects from API
   */
  async function fetchProjects(customFilters?: ProjectFilters): Promise<void> {
    loading.value = true;
    error.value = null;
    
    try {
      const appliedFilters = { ...filters.value, ...customFilters };
      const response = await projectApi.getProjects({
        ...appliedFilters,
        page: pagination.value.page,
        perPage: pagination.value.perPage
      });
      
      projects.value = response.data;
      pagination.value = {
        page: response.meta.currentPage,
        perPage: response.meta.perPage,
        total: response.meta.total,
        totalPages: response.meta.totalPages
      };
      
      if (customFilters) {
        filters.value = appliedFilters;
      }
    } catch (err) {
      error.value = err.message;
      console.error('Failed to fetch projects:', err);
    } finally {
      loading.value = false;
    }
  }
  
  /**
   * Fetch single project
   */
  async function fetchProject(id: string): Promise<Project> {
    loading.value = true;
    
    try {
      const project = await projectApi.getProject(id);
      
      // Update in list if exists
      const index = projects.value.findIndex(p => p.id === id);
      if (index !== -1) {
        projects.value[index] = project;
      } else {
        projects.value.push(project);
      }
      
      currentProject.value = project;
      return project;
    } catch (err) {
      error.value = err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  /**
   * Create new project
   */
  async function createProject(data: Partial<Project>): Promise<Project> {
    loading.value = true;
    
    try {
      const project = await projectApi.createProject(data);
      projects.value.unshift(project);
      return project;
    } catch (err) {
      error.value = err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  /**
   * Update existing project
   */
  async function updateProject(id: string, data: Partial<Project>): Promise<Project> {
    loading.value = true;
    
    try {
      const updated = await projectApi.updateProject(id, data);
      
      const index = projects.value.findIndex(p => p.id === id);
      if (index !== -1) {
        projects.value[index] = updated;
      }
      
      if (currentProject.value?.id === id) {
        currentProject.value = updated;
      }
      
      return updated;
    } catch (err) {
      error.value = err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  /**
   * Delete project
   */
  async function deleteProject(id: string): Promise<void> {
    loading.value = true;
    
    try {
      await projectApi.deleteProject(id);
      projects.value = projects.value.filter(p => p.id !== id);
      
      if (currentProject.value?.id === id) {
        currentProject.value = null;
      }
    } catch (err) {
      error.value = err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  /**
   * Set current project
   */
  function setCurrentProject(project: Project | null): void {
    currentProject.value = project;
  }
  
  /**
   * Update filters
   */
  function updateFilters(newFilters: Partial<ProjectFilters>): void {
    filters.value = { ...filters.value, ...newFilters };
  }
  
  /**
   * Change page
   */
  function changePage(page: number): void {
    pagination.value.page = page;
    fetchProjects();
  }
  
  /**
   * Reset store
   */
  function $reset(): void {
    projects.value = [];
    currentProject.value = null;
    loading.value = false;
    error.value = null;
    filters.value = {
      search: '',
      status: 'all',
      type: null,
      sortBy: 'createdAt',
      sortOrder: 'desc'
    };
    pagination.value = {
      page: 1,
      perPage: 50,
      total: 0,
      totalPages: 0
    };
  }

  return {
    // State
    projects: readonly(projects),
    currentProject: readonly(currentProject),
    loading: readonly(loading),
    error: readonly(error),
    filters: readonly(filters),
    pagination: readonly(pagination),
    
    // Getters
    activeProjects,
    projectsByStatus,
    statistics,
    filteredProjects,
    
    // Actions
    fetchProjects,
    fetchProject,
    createProject,
    updateProject,
    deleteProject,
    setCurrentProject,
    updateFilters,
    changePage,
    $reset
  };
});
```

---

## ♻️ Code Reusability Patterns

### 1. Shared Traits (Laravel)

```php
// app/Domain/Shared/Traits/HasUuid.php
<?php

namespace App\Domain\Shared\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}
```

```php
// app/Domain/Shared/Traits/Searchable.php
<?php

namespace App\Domain\Shared\Traits;

trait Searchable
{
    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }

        $searchable = $this->searchable ?? [];
        
        return $query->where(function ($q) use ($search, $searchable) {
            foreach ($searchable as $field) {
                $q->orWhere($field, 'LIKE', "%{$search}%");
            }
        });
    }
}
```

### 2. Base Classes

```php
// app/Domain/Shared/Actions/Action.php
<?php

namespace App\Domain\Shared\Actions;

abstract class Action
{
    abstract public function execute(...$args);
    
    protected function authorize($ability, $arguments = []): void
    {
        if (!auth()->user()->can($ability, $arguments)) {
            throw new UnauthorizedException("Unauthorized action: {$ability}");
        }
    }
    
    protected function validate(array $data, array $rules): array
    {
        return validator($data, $rules)->validate();
    }
}
```

### 3. Reusable Vue Components

```vue
<!-- components/shared/DataTable.vue -->
<template>
  <div class="data-table">
    <!-- Table Header -->
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-4">
        <slot name="title">
          <h2 class="text-xl font-semibold">{{ title }}</h2>
        </slot>
        <slot name="stats" :stats="statistics" />
      </div>
      <slot name="actions">
        <VButton v-if="showAddButton" @click="$emit('add')">
          <Plus class="mr-2 h-4 w-4" />
          Add {{ entityName }}
        </VButton>
      </slot>
    </div>
    
    <!-- Filters -->
    <div class="flex items-center gap-2 mb-4">
      <slot name="filters" :filters="localFilters">
        <VInput
          v-model="localFilters.search"
          placeholder="Search..."
          class="max-w-xs"
        />
      </slot>
    </div>
    
    <!-- Table -->
    <VTable
      :columns="columns"
      :data="paginatedData"
      :loading="loading"
      @sort="handleSort"
    >
      <template v-for="(_, slot) in $slots" v-slot:[slot]="scope">
        <slot :name="slot" v-bind="scope" />
      </template>
    </VTable>
    
    <!-- Pagination -->
    <div class="flex items-center justify-between mt-4">
      <div class="text-sm text-muted-foreground">
        Showing {{ startIndex }}-{{ endIndex }} of {{ total }}
      </div>
      <VPagination
        v-model="currentPage"
        :total-pages="totalPages"
        @change="handlePageChange"
      />
    </div>
  </div>
</template>

<script setup lang="ts" generic="T">
import { ref, computed, watch } from 'vue';
import { VButton, VInput, VTable, VPagination } from '@/components/ui';
import { Plus } from 'lucide-vue-next';

interface Props {
  title?: string;
  entityName?: string;
  data: T[];
  columns: Column[];
  loading?: boolean;
  showAddButton?: boolean;
  perPage?: number;
  filters?: Record<string, any>;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  showAddButton: true,
  perPage: 50
});

const emit = defineEmits<{
  'add': [];
  'filter': [filters: Record<string, any>];
  'page-change': [page: number];
  'sort': [field: string, direction: string];
}>();

// Local state
const currentPage = ref(1);
const localFilters = ref({ ...props.filters });
const sortField = ref<string | null>(null);
const sortDirection = ref<'asc' | 'desc'>('asc');

// Computed
const filteredData = computed(() => {
  let result = [...props.data];
  
  if (localFilters.value.search) {
    // Implement search logic
  }
  
  if (sortField.value) {
    // Implement sort logic
  }
  
  return result;
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * props.perPage;
  const end = start + props.perPage;
  return filteredData.value.slice(start, end);
});

const totalPages = computed(() => 
  Math.ceil(filteredData.value.length / props.perPage)
);

const startIndex = computed(() => 
  (currentPage.value - 1) * props.perPage + 1
);

const endIndex = computed(() => 
  Math.min(currentPage.value * props.perPage, filteredData.value.length)
);

const total = computed(() => filteredData.value.length);

const statistics = computed(() => ({
  total: props.data.length,
  filtered: filteredData.value.length,
  page: currentPage.value,
  pages: totalPages.value
}));

// Methods
function handleSort(field: string) {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }
  emit('sort', field, sortDirection.value);
}

function handlePageChange(page: number) {
  currentPage.value = page;
  emit('page-change', page);
}

// Watchers
watch(localFilters, (newFilters) => {
  emit('filter', newFilters);
  currentPage.value = 1;
}, { deep: true });
</script>
```

---

## 🧪 Testing Standards

### Laravel Testing

```php
// tests/Feature/ProjectTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Domain\Project\Models\Project;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->manager = User::factory()->manager()->create();
    }

    /** @test */
    public function user_can_create_project_with_valid_data(): void
    {
        $projectData = [
            'name' => 'Test Project',
            'type' => 'commercial',
            'budget' => 100000,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
            'manager_id' => $this->manager->id,
            'location' => [
                'address' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'zip' => '10001'
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/projects', $projectData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'code',
                    'type',
                    'budget',
                    'status'
                ]
            ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project',
            'type' => 'commercial',
            'budget' => 100000
        ]);
    }

    /** @test */
    public function user_cannot_create_project_with_invalid_budget(): void
    {
        $projectData = [
            'name' => 'Test Project',
            'budget' => -1000, // Invalid negative budget
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/projects', $projectData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['budget']);
    }

    /** @test */
    public function manager_can_update_their_project(): void
    {
        $project = Project::factory()->create([
            'manager_id' => $this->manager->id
        ]);

        $response = $this->actingAs($this->manager)
            ->putJson("/api/v1/projects/{$project->id}", [
                'name' => 'Updated Project Name'
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project Name'
        ]);
    }
}
```

### Vue.js Testing

```typescript
// tests/unit/components/ProjectCard.spec.ts
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { createTestingPinia } from '@pinia/testing';
import ProjectCard from '@/components/projects/ProjectCard.vue';
import type { Project } from '@/types/models/project';

describe('ProjectCard', () => {
  let wrapper;
  
  const mockProject: Project = {
    id: '123',
    name: 'Test Project',
    code: 'PRJ-2024-001',
    status: 'active',
    type: 'commercial',
    budget: 100000,
    spent: 45000,
    progress: 45,
    startDate: '2024-01-01',
    endDate: '2024-12-31',
    managerId: 'user-1',
    createdAt: '2024-01-01'
  };

  beforeEach(() => {
    wrapper = mount(ProjectCard, {
      props: {
        project: mockProject,
        showActions: true
      },
      global: {
        plugins: [
          createTestingPinia({
            createSpy: vi.fn
          })
        ],
        stubs: {
          VCard: true,
          VButton: true,
          VBadge: true
        }
      }
    });
  });

  it('displays project information correctly', () => {
    expect(wrapper.text()).toContain('Test Project');
    expect(wrapper.text()).toContain('PRJ-2024-001');
  });

  it('shows correct status badge', () => {
    const badge = wrapper.find('[data-testid="status-badge"]');
    expect(badge.text()).toBe('active');
    expect(badge.classes()).toContain('badge-success');
  });

  it('displays progress correctly', () => {
    const progress = wrapper.find('[data-testid="progress-bar"]');
    expect(progress.attributes('style')).toContain('width: 45%');
  });

  it('emits edit event when edit button clicked', async () => {
    const editBtn = wrapper.find('[data-testid="edit-btn"]');
    await editBtn.trigger('click');
    
    expect(wrapper.emitted('edit')).toBeTruthy();
    expect(wrapper.emitted('edit')[0]).toEqual([mockProject]);
  });

  it('shows confirmation dialog before deletion', async () => {
    const deleteSpy = vi.spyOn(window, 'confirm').mockReturnValue(true);
    
    const deleteBtn = wrapper.find('[data-testid="delete-btn"]');
    await deleteBtn.trigger('click');
    
    expect(deleteSpy).toHaveBeenCalled();
    expect(wrapper.emitted('delete')).toBeTruthy();
  });

  it('calculates budget usage percentage correctly', () => {
    const budgetUsage = wrapper.vm.budgetUsagePercentage;
    expect(budgetUsage).toBe(45); // 45000/100000 * 100
  });

  it('identifies overdue projects', () => {
    const overdueProject = {
      ...mockProject,
      endDate: '2023-01-01',
      status: 'active'
    };
    
    const wrapper = mount(ProjectCard, {
      props: { project: overdueProject }
    });
    
    expect(wrapper.vm.isOverdue).toBe(true);
    expect(wrapper.classes()).toContain('border-red-500');
  });
});
```

---

## 🚀 Performance Guidelines

### Laravel Performance

```php
// 1. Eager Loading
// ❌ BAD - N+1 Problem
$projects = Project::all();
foreach ($projects as $project) {
    echo $project->tasks->count(); // Executes query for each project
}

// ✅ GOOD - Eager Loading
$projects = Project::with('tasks')->get();
foreach ($projects as $project) {
    echo $project->tasks->count(); // No additional queries
}

// 2. Query Optimization
// ✅ Use select to limit columns
Project::select(['id', 'name', 'status'])
    ->where('status', 'active')
    ->get();

// 3. Caching
Cache::remember('active-projects', 3600, function () {
    return Project::active()->with('tasks')->get();
});

// 4. Database Indexing
Schema::table('projects', function (Blueprint $table) {
    $table->index(['company_id', 'status']); // Composite index
    $table->index('created_at'); // Single column index
});

// 5. Chunking Large Datasets
Project::chunk(100, function ($projects) {
    foreach ($projects as $project) {
        // Process project
    }
});
```

### Vue.js Performance

```typescript
// 1. Lazy Loading Components
const ProjectGantt = defineAsyncComponent(() => 
  import('@/components/projects/ProjectGantt.vue')
);

// 2. Virtual Scrolling for Large Lists
<VirtualList
  :items="projects"
  :item-height="80"
  v-slot="{ item }"
>
  <ProjectCard :project="item" />
</VirtualList>

// 3. Debouncing Input
import { debounce } from 'lodash-es';

const search = debounce((query: string) => {
  store.searchProjects(query);
}, 300);

// 4. Memoization
const expensiveComputation = computed(() => {
  return useMemo(() => {
    // Expensive calculation
    return projects.value.reduce((acc, p) => {
      // Complex logic
    }, 0);
  }, [projects.value]);
});

// 5. Image Optimization
<img 
  :src="optimizedImageUrl" 
  loading="lazy"
  :srcset="`${image.small} 300w, ${image.medium} 768w, ${image.large} 1200w`"
/>
```

---

## 🔒 Security Standards

### Laravel Security

```php
// 1. Input Validation
$validated = $request->validate([
    'email' => 'required|email:rfc,dns',
    'file' => 'required|file|mimes:pdf,doc|max:10240'
]);

// 2. SQL Injection Prevention
// ❌ BAD
$projects = DB::select("SELECT * FROM projects WHERE name = '{$request->name}'");

// ✅ GOOD
$projects = DB::select('SELECT * FROM projects WHERE name = ?', [$request->name]);
// Or use Eloquent
$projects = Project::where('name', $request->name)->get();

// 3. Authorization
Gate::define('update-project', function (User $user, Project $project) {
    return $user->id === $project->manager_id;
});

// In controller
$this->authorize('update', $project);

// 4. Rate Limiting
Route::middleware(['throttle:api'])->group(function () {
    Route::apiResource('projects', ProjectController::class);
});

// 5. XSS Prevention
$clean = strip_tags($request->description);
$escaped = e($userInput); // Laravel's escape helper
```

### Vue.js Security

```typescript
// 1. Never use v-html with user content
// ❌ BAD
<div v-html="userContent"></div>

// ✅ GOOD
<div v-text="userContent"></div>
// Or use DOMPurify for sanitization
import DOMPurify from 'dompurify';
const sanitized = DOMPurify.sanitize(userContent);

// 2. Validate file uploads
function validateFile(file: File): boolean {
  const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
  const maxSize = 10 * 1024 * 1024; // 10MB
  
  if (!allowedTypes.includes(file.type)) {
    throw new Error('Invalid file type');
  }
  
  if (file.size > maxSize) {
    throw new Error('File too large');
  }
  
  return true;
}

// 3. Secure API calls
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest'
  }
});

// Add CSRF token
api.interceptors.request.use(config => {
  config.headers['X-CSRF-TOKEN'] = getCsrfToken();
  return config;
});
```

---

## 📝 Documentation Standards

### Code Documentation

```php
/**
 * Calculate project completion percentage based on task status.
 *
 * This method calculates the overall project progress by analyzing
 * the completion status of all associated tasks. Tasks with higher
 * priority have greater weight in the calculation.
 *
 * @param bool $includeOptional Include optional tasks in calculation
 * @return float The completion percentage (0-100)
 * 
 * @throws \InvalidArgumentException If project has no tasks
 * 
 * @example
 * $project = Project::find(1);
 * $progress = $project->calculateProgress(); // Returns 67.5
 */
public function calculateProgress(bool $includeOptional = true): float
{
    if ($this->tasks->isEmpty()) {
        throw new \InvalidArgumentException('Project has no tasks');
    }
    
    // Implementation...
}
```

### API Documentation

```yaml
openapi: 3.0.0
info:
  title: Construction Management API
  version: 1.0.0
  
paths:
  /api/v1/projects:
    get:
      summary: List all projects
      parameters:
        - name: status
          in: query
          schema:
            type: string
            enum: [planning, active, on_hold, completed]
        - name: page
          in: query
          schema:
            type: integer
            default: 1
      responses:
        200:
          description: Successful response
          content:
            application/json:
              schema:
                type: object
                properties:
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/Project'
```

---

## ✅ Code Review Checklist

### Before Submitting PR

#### Laravel
- [ ] All tests passing (`php artisan test`)
- [ ] Code follows PSR-12 standards (`./vendor/bin/pint`)
- [ ] No N+1 queries (check with Debugbar/Telescope)
- [ ] Proper validation in Form Requests
- [ ] Authorization implemented
- [ ] Database migrations are reversible
- [ ] API Resources used for responses
- [ ] Caching implemented where appropriate
- [ ] Queue jobs for heavy operations
- [ ] Proper error handling

#### Vue.js
- [ ] TypeScript types defined
- [ ] Component props validated
- [ ] Composition API used
- [ ] No inline styles
- [ ] UI components from `/components/ui/`
- [ ] Loading states implemented
- [ ] Error handling in place
- [ ] Responsive design tested
- [ ] Accessibility checked
- [ ] Tests written and passing

#### General
- [ ] Documentation updated
- [ ] No console.log or dd() statements
- [ ] No commented-out code
- [ ] Meaningful commit messages
- [ ] Branch up to date with main
- [ ] No merge conflicts
- [ ] Security vulnerabilities checked
- [ ] Performance impact assessed

---

**Remember:** Clean, maintainable, and well-tested code is more valuable than quick fixes. Always prioritize code quality and follow these standards consistently.