<?php

namespace App\Http\Filters;

use App\Http\Filters\Contracts\FilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

abstract class BaseFilter implements FilterInterface
{
    protected Request $request;
    protected array $filters = [];
    protected array $appliedFilters = [];
    protected bool $useCache = false;
    protected int $cacheMinutes = 5;

    public function __construct(Request $request = null)
    {
        $this->request = $request ?? request();
        $this->parseFilters();
    }

    /**
     * Apply all filters to the query
     */
    public function apply(Builder $query, Request $request = null): Builder
    {
        if ($request) {
            $this->request = $request;
            $this->parseFilters();
        }

        $cacheKey = $this->getCacheKey($query);
        
        if ($this->useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $filteredQuery = $this->applyFilters($query);

        if ($this->useCache) {
            Cache::put($cacheKey, $filteredQuery, now()->addMinutes($this->cacheMinutes));
        }

        return $filteredQuery;
    }

    /**
     * Parse filters from request
     */
    protected function parseFilters(): void
    {
        $config = $this->getFilterConfig();
        
        foreach ($config as $filterName => $filterConfig) {
            $param = $filterConfig['param'] ?? $filterName;
            
            // Handle array parameters (composite filters)
            if (is_array($param)) {
                // Skip array parameter processing here - handled by specific parseFilters() overrides
                continue;
            }
            
            $value = $this->request->get($param);
            
            if ($this->isValidFilterValue($value, $filterConfig)) {
                $this->filters[$filterName] = $this->transformFilterValue($value, $filterConfig);
            }
        }
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters(Builder $query): Builder
    {
        foreach ($this->filters as $filterName => $value) {
            $methodName = 'apply' . Str::studly($filterName);
            
            if (method_exists($this, $methodName)) {
                $query = $this->$methodName($query, $value);
                $this->appliedFilters[$filterName] = $value;
            }
        }

        // Apply default sorting if no custom sorting applied
        if (!isset($this->appliedFilters['sort'])) {
            $query = $this->applyDefaultSorting($query);
        }

        return $query;
    }

    /**
     * Check if filter value is valid
     */
    protected function isValidFilterValue($value, array $config): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        // Type validation
        if (isset($config['type'])) {
            switch ($config['type']) {
                case 'integer':
                    return is_numeric($value);
                case 'boolean':
                    return in_array(strtolower($value), ['true', 'false', '1', '0', 'yes', 'no']);
                case 'date':
                    return strtotime($value) !== false;
                case 'array':
                    return is_array($value) || is_string($value);
            }
        }

        // Custom validation rules
        if (isset($config['validation'])) {
            return $this->validateCustomRules($value, $config['validation']);
        }

        return true;
    }

    /**
     * Transform filter value based on type
     */
    protected function transformFilterValue($value, array $config)
    {
        if (!isset($config['type'])) {
            return $value;
        }

        switch ($config['type']) {
            case 'integer':
                return (int) $value;
            case 'boolean':
                return in_array(strtolower($value), ['true', '1', 'yes']);
            case 'date':
                return date('Y-m-d', strtotime($value));
            case 'array':
                return is_array($value) ? $value : explode(',', $value);
            default:
                return $value;
        }
    }

    /**
     * Validate custom rules
     */
    protected function validateCustomRules($value, array $rules): bool
    {
        foreach ($rules as $rule) {
            switch ($rule) {
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return false;
                    }
                    break;
                case 'numeric':
                    if (!is_numeric($value)) {
                        return false;
                    }
                    break;
            }
        }
        
        return true;
    }

    /**
     * Apply search filter
     */
    protected function applySearch(Builder $query, string $search): Builder
    {
        $searchableFields = $this->getSearchableFields();
        
        if (empty($searchableFields)) {
            return $query;
        }

        return $query->where(function ($q) use ($search, $searchableFields) {
            foreach ($searchableFields as $field) {
                if (Str::contains($field, '.')) {
                    // Handle relationship fields
                    [$relation, $column] = explode('.', $field, 2);
                    $q->orWhereHas($relation, function ($relationQuery) use ($column, $search) {
                        $relationQuery->where($column, 'LIKE', "%{$search}%");
                    });
                } else {
                    $q->orWhere($field, 'LIKE', "%{$search}%");
                }
            }
        });
    }

    /**
     * Apply sorting
     */
    protected function applySort(Builder $query, array $sort): Builder
    {
        $sortBy = $sort['by'] ?? 'created_at';
        $direction = strtolower($sort['direction'] ?? 'desc');
        
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $allowedSortColumns = $this->getSortableFields();
        
        if (in_array($sortBy, $allowedSortColumns)) {
            return $query->orderBy($sortBy, $direction);
        }

        return $this->applyDefaultSorting($query);
    }

    /**
     * Apply default sorting
     */
    protected function applyDefaultSorting(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Apply date range filter
     */
    protected function applyDateRange(Builder $query, array $dateRange): Builder
    {
        $field = $dateRange['field'] ?? 'created_at';
        
        if (isset($dateRange['from'])) {
            $query->whereDate($field, '>=', $dateRange['from']);
        }
        
        if (isset($dateRange['to'])) {
            $query->whereDate($field, '<=', $dateRange['to']);
        }

        return $query;
    }

    /**
     * Apply status filter
     */
    protected function applyStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Apply active/inactive filter
     */
    protected function applyActive(Builder $query, bool $active): Builder
    {
        if ($active) {
            return $query->whereNull('deleted_at');
        }
        
        return $query->whereNotNull('deleted_at');
    }

    /**
     * Get cache key for query
     */
    protected function getCacheKey(Builder $query): string
    {
        $modelClass = get_class($query->getModel());
        $filtersHash = md5(serialize($this->filters));
        
        return "filter_{$modelClass}_{$filtersHash}";
    }

    /**
     * Get applied filters
     */
    public function getAppliedFilters(): array
    {
        return $this->appliedFilters;
    }

    /**
     * Enable caching
     */
    public function withCache(int $minutes = 5): self
    {
        $this->useCache = true;
        $this->cacheMinutes = $minutes;
        
        return $this;
    }

    /**
     * Get searchable fields - must be implemented by child classes
     */
    abstract protected function getSearchableFields(): array;

    /**
     * Get sortable fields - must be implemented by child classes
     */
    abstract protected function getSortableFields(): array;

    /**
     * Get filter configuration - must be implemented by child classes
     */
    abstract public function getFilterConfig(): array;
}