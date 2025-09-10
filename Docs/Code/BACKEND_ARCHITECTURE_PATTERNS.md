# Backend Architecture & Coding Patterns

## Overview
This document outlines the comprehensive architecture patterns and coding standards used in the Construction Management Platform backend built with Laravel 11.x.

---

## 🏗️ Architecture Overview

### Domain-Driven Design Structure
```
app/
├── Domain/                     # Business logic and domain models
│   └── {Domain}/
│       ├── Models/            # Eloquent models
│       ├── Services/          # Business logic services
│       ├── DTOs/             # Data Transfer Objects
│       ├── Repositories/     # Repository pattern interfaces & implementations
│       ├── Events/           # Domain events
│       ├── Jobs/            # Background jobs
│       └── Filters/         # Query filters
├── Http/                     # HTTP layer (Controllers, Requests, Resources)
│   ├── Controllers/Api/     # API controllers
│   ├── Requests/           # Form request validation
│   ├── Resources/          # API resource transformers
│   └── Traits/            # Reusable traits
```

---

## 🎛️ Controller Pattern

### Base Structure
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\FilterableTrait;

class EntityController extends Controller
{
    use ApiResponseTrait, FilterableTrait;

    public function __construct(
        private EntityService $entityService,
        private StatisticsService $statisticsService
    ) {}

    // CRUD methods with consistent patterns
}
```

### Required Patterns

#### 1. **Dependency Injection in Constructor**
```php
public function __construct(
    private EntityService $entityService,
    private StatisticsService $statisticsService
) {}
```

#### 2. **Consistent Method Signatures**
```php
// Index with filtering and pagination
public function index(Request $request): JsonResponse

// Store with DTO pattern
public function store(CreateEntityRequest $request): JsonResponse

// Show with caching
public function show(Entity $entity): JsonResponse

// Update with DTO pattern
public function update(UpdateEntityRequest $request, Entity $entity): JsonResponse

// Destroy with cleanup
public function destroy(Entity $entity): JsonResponse
```

#### 3. **Error Handling Pattern**
```php
public function store(CreateEntityRequest $request): JsonResponse
{
    try {
        DB::beginTransaction();

        // Business logic here

        DB::commit();
        
        return $this->resourceResponse(
            $entity->load('relationships'),
            EntityResource::class,
            [],
            'Entity created successfully',
            201
        );
    } catch (\Exception $e) {
        DB::rollBack();
        
        return $this->errorResponse(
            'Failed to create entity: ' . $e->getMessage(),
            500
        );
    }
}
```

#### 4. **Cache Management**
```php
// Cache retrieval with key patterns
$cacheKey = "entity_profile_{$entity->id}";
$data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($entity) {
    return $entity->load(['relationships']);
});

// Cache clearing pattern
Cache::forget("entity_profile_{$entity->id}");
$this->clearEntityCaches();
```

---

## 🔧 Service Layer Pattern

### Structure
```php
<?php

namespace App\Domain\Entity\Services;

use App\Domain\Entity\DTOs\CreateEntityDTO;
use App\Domain\Entity\DTOs\UpdateEntityDTO;
use App\Domain\Entity\Models\Entity;
use App\Domain\Entity\Repositories\EntityRepositoryInterface;

class EntityService
{
    public function __construct(
        private EntityRepositoryInterface $entityRepository
    ) {}

    public function createEntity(CreateEntityDTO $dto): Entity
    {
        // Business logic
        return $this->entityRepository->create($dto->toArray());
    }

    public function updateEntity(Entity $entity, UpdateEntityDTO $dto): Entity
    {
        // Business logic with selective updates
        $attributes = $dto->getNonNullAttributes();
        return $this->entityRepository->update($entity, $attributes);
    }
}
```

---

## 📊 DTO Pattern

### Using Spatie Laravel Data
```php
<?php

namespace App\Domain\Entity\DTOs;

use Spatie\LaravelData\Data;

class CreateEntityDTO extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone = null,
        public ?string $department = null
    ) {}

    public function getNonNullAttributes(): array
    {
        return array_filter($this->toArray(), fn($value) => $value !== null);
    }
}
```

---

## 🗃️ Repository Pattern

### Interface
```php
<?php

namespace App\Domain\Entity\Repositories;

use App\Domain\Entity\Models\Entity;
use Illuminate\Database\Eloquent\Collection;

interface EntityRepositoryInterface
{
    public function getAll(): Collection;
    public function findById(string $id): ?Entity;
    public function create(array $data): Entity;
    public function update(Entity $entity, array $data): Entity;
    public function delete(Entity $entity): bool;
}
```

### Implementation
```php
<?php

namespace App\Domain\Entity\Repositories;

use App\Domain\Entity\Models\Entity;
use Illuminate\Database\Eloquent\Collection;

class EntityRepository implements EntityRepositoryInterface
{
    public function getAll(): Collection
    {
        return Entity::with(['relationships'])->get();
    }

    public function findById(string $id): ?Entity
    {
        return Entity::with(['relationships'])->find($id);
    }

    // ... other methods
}
```

---

## 📝 Request Validation Pattern

### Form Request Structure
```php
<?php

namespace App\Http\Requests\Entity;

use Illuminate\Foundation\Http\FormRequest;

class CreateEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Handle authorization logic
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:entities,email',
            'phone' => 'nullable|string|max:20',
            'metadata' => 'nullable|array',
            'metadata.*.key' => 'required_with:metadata|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Entity name is required.',
            'email.unique' => 'This email is already in use.',
        ];
    }
}
```

---

## 🎨 API Resource Pattern

### Resource Structure
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'formatted_date' => $this->created_at->format('Y-m-d H:i:s'),
            
            // Conditional loading
            'relationships' => $this->whenLoaded('relationships', function () {
                return RelationshipResource::collection($this->relationships);
            }),
            
            // Computed attributes
            'display_name' => $this->display_name,
            'status_label' => $this->status_display,
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

---

## 🔍 Filtering System

### Filter Class Pattern
```php
<?php

namespace App\Domain\Entity\Filters;

use App\Http\Filters\Contracts\FilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class EntityFilter implements FilterInterface
{
    public function apply(Builder $query, Request $request): Builder
    {
        return $query->when($request->filled('search'), function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%');
        })->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });
    }

    public function getAppliedFilters(): array
    {
        return [
            'search' => request('search'),
            'status' => request('status'),
        ];
    }

    public function getFilterConfig(): array
    {
        return [
            'search' => ['type' => 'text'],
            'status' => ['type' => 'select', 'options' => ['active', 'inactive']],
        ];
    }
}
```

---

## 🏷️ Model Patterns

### Base Model Structure
```php
<?php

namespace App\Domain\Entity\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entity extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'status',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        return ucfirst($this->name);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Boot method for auto-ordering
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (is_null($model->display_order)) {
                $model->display_order = static::max('display_order') + 1;
            }
        });
    }
}
```

---

## 🎯 API Response Patterns

### Response Trait Usage
```php
// Success responses
return $this->successResponse($data, 'Operation successful', 200);

// Resource responses (single item)
return $this->resourceResponse(
    $entity->load('relationships'),
    EntityResource::class,
    ['meta_key' => 'meta_value'],
    'Entity retrieved successfully'
);

// Collection responses
return $this->collectionResponse(
    $entities,
    EntityResource::class,
    ['total' => $entities->count()],
    'Entities retrieved successfully'
);

// Paginated responses
return $this->paginatedResponse(
    $paginatedEntities,
    EntityResource::class,
    ['applied_filters' => $appliedFilters],
    'Entities retrieved successfully'
);

// Error responses
return $this->errorResponse('Validation failed', 422, $validationErrors);
```

### Standard Response Structure
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {},
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 100
    }
}
```

---

## 📊 Statistics & Analytics Pattern

### Statistics Service Integration
```php
// In Controller
protected function getEntityStatistics($query, EntityFilter $filter, Request $request): array
{
    $cacheKey = 'entity_stats_' . md5(serialize($filter->getAppliedFilters()));
    
    return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($query) {
        // Basic statistics
        $basicStats = $this->statisticsService->getBasicStats($baseQuery, [
            'active' => ['status' => 'active'],
            'inactive' => ['status' => 'inactive'],
        ]);

        // Time-based statistics
        $timeStats = $this->statisticsService->getTimeBasedStats(
            clone $baseQuery, 
            'created_at', 
            ['today', 'this_week', 'this_month']
        );

        return [
            'stats' => array_merge($basicStats, $timeStats),
        ];
    });
}
```

---

## 🔐 Security & Authorization

### Authorization Patterns
```php
// In Controller methods
if ($entity->company_id !== $user->company->id) {
    return $this->forbiddenResponse('Unauthorized access');
}

// Rate limiting pattern
$rateLimitKey = "operation_{$user->id}";
if (Cache::has($rateLimitKey)) {
    return $this->errorResponse('Too many requests', 429);
}
Cache::put($rateLimitKey, true, now()->addMinutes(5));
```

### File Upload Security
```php
$request->validate([
    'file' => 'required|file|mimes:jpeg,png,pdf|max:10240' // 10MB
]);

// Secure file storage
$file = $request->file('file');
$path = $file->store("entities/{$entity->id}", 'public');
```

---

## 💾 Caching Strategy

### Cache Key Patterns
```php
// Entity-specific cache
"entity_profile_{$entity->id}"

// Filtered results cache  
"filtered_Entity_" . md5(serialize($filterParams))

// Statistics cache
"entity_stats_" . md5(serialize($appliedFilters))

// Role/company based cache
"entities_by_role_{$role}"
"company_entities_{$companyId}"
```

### Cache Management
```php
// Clear related caches
protected function clearEntityCaches(): void
{
    // Clear statistics cache
    Cache::forget('entity_stats_*');
    
    // Clear role-based caches
    $roles = ['admin', 'user', 'manager'];
    foreach ($roles as $role) {
        Cache::forget("entities_by_role_{$role}");
    }
    
    // Clear service-level caches
    $this->statisticsService->clearCache('Entity');
}
```

---

## 🔄 Database Transaction Pattern

### Consistent Transaction Usage
```php
public function complexOperation(Request $request): JsonResponse
{
    try {
        DB::beginTransaction();

        // Multiple database operations
        $entity = $this->entityService->create($data);
        $this->relationshipService->attach($entity, $relationships);
        $this->auditService->log($entity, 'created');

        DB::commit();
        
        // Clear caches after successful commit
        $this->clearEntityCaches();

        return $this->resourceResponse($entity, EntityResource::class);
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        return $this->errorResponse(
            'Operation failed: ' . $e->getMessage(),
            500
        );
    }
}
```

---

## 📁 File Structure Requirements

### Required Files for Each Domain
```
Domain/Entity/
├── Models/
│   └── Entity.php                    # Eloquent model
├── Services/
│   └── EntityService.php            # Business logic service
├── Repositories/
│   ├── EntityRepositoryInterface.php # Repository contract
│   └── EntityRepository.php         # Repository implementation
├── DTOs/
│   ├── CreateEntityDTO.php          # Creation data transfer object
│   └── UpdateEntityDTO.php          # Update data transfer object
├── Filters/
│   └── EntityFilter.php            # Query filtering logic
└── Events/
    └── EntityCreated.php           # Domain events

Http/
├── Controllers/Api/
│   └── EntityController.php        # API controller
├── Requests/Entity/
│   ├── CreateEntityRequest.php     # Creation validation
│   └── UpdateEntityRequest.php     # Update validation
└── Resources/
    └── EntityResource.php          # API response transformer
```

---

## 🚀 Best Practices Summary

### Code Quality
1. **Always use dependency injection** in constructors
2. **Implement repository pattern** for data access
3. **Use DTOs** for data transfer between layers
4. **Apply consistent error handling** with try-catch and transactions
5. **Implement caching strategy** with proper cache invalidation

### API Design
1. **RESTful endpoint naming** following Laravel conventions
2. **Consistent response structure** using ApiResponseTrait
3. **Proper HTTP status codes** for different scenarios
4. **Resource transformers** for consistent API responses
5. **Request validation** using Form Request classes

### Performance
1. **Eager loading** relationships to avoid N+1 queries
2. **Query caching** for expensive operations
3. **Database indexing** on frequently queried columns
4. **Pagination** for large datasets
5. **Background jobs** for time-consuming operations

### Security
1. **Input validation** on all requests
2. **Authorization checks** in controllers
3. **Rate limiting** for sensitive operations
4. **Secure file uploads** with proper validation
5. **SQL injection prevention** using Eloquent ORM

---

This architecture ensures scalability, maintainability, and consistency across the entire application while following Laravel best practices and domain-driven design principles.