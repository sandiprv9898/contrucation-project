<?php

namespace App\Http\Traits;

use App\Http\Filters\Contracts\FilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

trait FilterableTrait
{
    /**
     * Apply filters and return paginated results
     */
    protected function getFilteredResults(
        Builder $query,
        FilterInterface $filter,
        Request $request,
        bool $paginate = true,
        bool $useCache = false
    ) {
        // Apply filters
        $filteredQuery = $filter->apply($query, $request);

        // Handle caching
        if ($useCache) {
            $cacheKey = $this->generateCacheKey($request, $filteredQuery);
            $cacheMinutes = config('cache.default_ttl', 5);

            return Cache::remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($filteredQuery, $request, $paginate) {
                return $this->executeQuery($filteredQuery, $request, $paginate);
            });
        }

        return $this->executeQuery($filteredQuery, $request, $paginate);
    }

    /**
     * Execute the query with pagination or collection
     */
    protected function executeQuery(Builder $query, Request $request, bool $paginate = true)
    {
        if ($paginate) {
            $perPage = $this->getPerPage($request);
            $page = $request->get('page', 1);
            
            return $query->paginate($perPage, ['*'], 'page', $page);
        }

        return $query->get();
    }

    /**
     * Get per page limit with validation
     */
    protected function getPerPage(Request $request): int
    {
        $perPage = (int) $request->get('per_page', 15);
        $maxPerPage = config('pagination.max_per_page', 100);
        
        return min(max($perPage, 1), $maxPerPage);
    }

    /**
     * Generate cache key for filtered results
     */
    protected function generateCacheKey(Request $request, Builder $query): string
    {
        $modelClass = get_class($query->getModel());
        $params = $request->query();
        ksort($params);
        
        $key = sprintf(
            'filtered_%s_%s',
            class_basename($modelClass),
            md5(serialize($params))
        );

        return $key;
    }

    /**
     * Get statistics for filtered results
     */
    protected function getFilteredStatistics(Builder $query, FilterInterface $filter, Request $request): array
    {
        $baseQuery = clone $query;
        $filteredQuery = $filter->apply($baseQuery, $request);

        // Remove pagination and ordering for stats
        $statsQuery = $filteredQuery->getQuery();
        $statsQuery->orders = null;
        $statsQuery->limit = null;
        $statsQuery->offset = null;

        return [
            'total_filtered' => $filteredQuery->count(),
            'applied_filters' => $filter->getAppliedFilters(),
            'filter_config' => $filter->getFilterConfig()
        ];
    }

    /**
     * Build export query for filtered results
     */
    protected function buildExportQuery(Builder $query, FilterInterface $filter, Request $request): Builder
    {
        $exportQuery = $filter->apply($query, $request);
        
        // Remove pagination for export
        $exportQuery->getQuery()->limit = null;
        $exportQuery->getQuery()->offset = null;
        
        return $exportQuery;
    }

    /**
     * Clear cache for filtered results
     */
    protected function clearFilterCache(string $modelClass = null): void
    {
        if ($modelClass) {
            $pattern = "filtered_" . class_basename($modelClass) . "_*";
        } else {
            $pattern = "filtered_*";
        }

        // Implementation depends on cache driver
        if (config('cache.default') === 'redis') {
            $keys = Cache::getRedis()->keys($pattern);
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        } else {
            // For other cache drivers, you might need to track keys manually
            // or use tags if supported
        }
    }

    /**
     * Validate filter parameters
     */
    protected function validateFilterParameters(Request $request, FilterInterface $filter): array
    {
        $errors = [];
        $config = $filter->getFilterConfig();

        foreach ($config as $filterName => $filterConfig) {
            $param = $filterConfig['param'] ?? $filterName;
            
            // Handle array parameters (composite filters)
            if (is_array($param)) {
                // Skip array parameter validation here - handled by specific filter implementations
                continue;
            }
            
            $value = $request->get($param);

            if ($value !== null && isset($filterConfig['validation'])) {
                $validationErrors = $this->validateFilterValue($value, $filterConfig['validation'], $filterName);
                if (!empty($validationErrors)) {
                    $errors[$filterName] = $validationErrors;
                }
            }
        }

        return $errors;
    }

    /**
     * Validate individual filter value
     */
    protected function validateFilterValue($value, array $rules, string $filterName): array
    {
        $errors = [];

        foreach ($rules as $rule) {
            if (str_starts_with($rule, 'in:')) {
                $allowedValues = explode(',', substr($rule, 3));
                if (!in_array($value, $allowedValues)) {
                    $errors[] = "Invalid value for {$filterName}. Allowed values: " . implode(', ', $allowedValues);
                }
            } elseif ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format for {$filterName}";
            } elseif ($rule === 'numeric' && !is_numeric($value)) {
                $errors[] = "Value for {$filterName} must be numeric";
            }
        }

        return $errors;
    }

    /**
     * Get quick filters for common use cases
     */
    protected function getQuickFilters(): array
    {
        return [
            'recent' => 'created_at >= ' . now()->subDays(7)->toDateString(),
            'this_month' => 'created_at >= ' . now()->startOfMonth()->toDateString(),
            'active' => 'deleted_at IS NULL',
        ];
    }
}