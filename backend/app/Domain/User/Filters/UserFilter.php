<?php

namespace App\Domain\User\Filters;

use App\Http\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;

class UserFilter extends BaseFilter
{
    /**
     * Get filter configuration
     */
    public function getFilterConfig(): array
    {
        return [
            'search' => [
                'type' => 'string',
                'param' => 'search'
            ],
            'role' => [
                'type' => 'string',
                'param' => 'role',
                'validation' => ['in:admin,project_manager,supervisor,field_worker']
            ],
            'verified' => [
                'type' => 'boolean',
                'param' => 'verified'
            ],
            'company_id' => [
                'type' => 'integer',
                'param' => 'company_id'
            ],
            'department' => [
                'type' => 'string',
                'param' => 'department'
            ],
            'active' => [
                'type' => 'boolean',
                'param' => 'active'
            ],
            'date_range' => [
                'type' => 'array',
                'param' => ['created_from', 'created_to']
            ],
            'sort' => [
                'type' => 'array',
                'param' => ['sort_by', 'sort_direction']
            ],
            'roles' => [
                'type' => 'array',
                'param' => 'roles'
            ],
            'email_domain' => [
                'type' => 'string',
                'param' => 'email_domain'
            ],
            'has_phone' => [
                'type' => 'boolean',
                'param' => 'has_phone'
            ]
        ];
    }

    /**
     * Get searchable fields
     */
    protected function getSearchableFields(): array
    {
        return [
            'name',
            'email', 
            'phone',
            'department',
            'company.name'
        ];
    }

    /**
     * Get sortable fields
     */
    protected function getSortableFields(): array
    {
        return [
            'name',
            'email',
            'role',
            'created_at',
            'updated_at',
            'email_verified_at',
            'department',
            'phone'
        ];
    }

    /**
     * Parse date range filter
     */
    protected function parseFilters(): void
    {
        parent::parseFilters();

        // Handle date range parsing
        if ($this->request->has('created_from') || $this->request->has('created_to')) {
            $this->filters['date_range'] = [
                'field' => 'created_at',
                'from' => $this->request->get('created_from'),
                'to' => $this->request->get('created_to')
            ];
        }

        // Handle sorting
        if ($this->request->has('sort_by') || $this->request->has('sort_direction')) {
            $this->filters['sort'] = [
                'by' => $this->request->get('sort_by', 'created_at'),
                'direction' => $this->request->get('sort_direction', 'desc')
            ];
        }
    }

    /**
     * Apply role filter
     */
    protected function applyRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    /**
     * Apply multiple roles filter
     */
    protected function applyRoles(Builder $query, array $roles): Builder
    {
        return $query->whereIn('role', $roles);
    }

    /**
     * Apply verified status filter
     */
    protected function applyVerified(Builder $query, bool $verified): Builder
    {
        if ($verified) {
            return $query->whereNotNull('email_verified_at');
        }
        
        return $query->whereNull('email_verified_at');
    }

    /**
     * Apply company filter
     */
    protected function applyCompanyId(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Apply department filter
     */
    protected function applyDepartment(Builder $query, string $department): Builder
    {
        return $query->where('department', 'LIKE', "%{$department}%");
    }

    /**
     * Apply email domain filter
     */
    protected function applyEmailDomain(Builder $query, string $domain): Builder
    {
        return $query->where('email', 'LIKE', "%@{$domain}");
    }

    /**
     * Apply has phone filter
     */
    protected function applyHasPhone(Builder $query, bool $hasPhone): Builder
    {
        if ($hasPhone) {
            return $query->whereNotNull('phone')->where('phone', '!=', '');
        }
        
        return $query->where(function ($q) {
            $q->whereNull('phone')->orWhere('phone', '');
        });
    }

    /**
     * Apply default sorting for users
     */
    protected function applyDefaultSorting(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc')
                    ->orderBy('name', 'asc');
    }
}