<?php

namespace App\Http\Filters\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

interface FilterInterface
{
    /**
     * Apply filters to the query builder
     */
    public function apply(Builder $query, Request $request): Builder;

    /**
     * Get filter configuration
     */
    public function getFilterConfig(): array;

    /**
     * Get applied filters for debugging/logging
     */
    public function getAppliedFilters(): array;
}