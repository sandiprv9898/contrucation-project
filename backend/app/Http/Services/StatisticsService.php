<?php

namespace App\Http\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    protected int $cacheMinutes = 10;

    /**
     * Get basic count statistics
     */
    public function getBasicStats(Builder $query, array $config = []): array
    {
        $cacheKey = $this->generateStatsKey($query, 'basic', $config);

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheMinutes), function () use ($query, $config) {
            $stats = [];
            
            // Total count
            $stats['total'] = $query->count();

            // Custom counts based on config
            foreach ($config as $statName => $conditions) {
                $statQuery = clone $query;
                
                if (is_array($conditions)) {
                    foreach ($conditions as $field => $value) {
                        if (is_null($value)) {
                            $statQuery->whereNull($field);
                        } elseif ($value === 'NOT_NULL') {
                            $statQuery->whereNotNull($field);
                        } else {
                            $statQuery->where($field, $value);
                        }
                    }
                } elseif (is_callable($conditions)) {
                    $statQuery->where($conditions);
                }
                
                $stats[$statName] = $statQuery->count();
            }

            return $stats;
        });
    }

    /**
     * Get time-based statistics
     */
    public function getTimeBasedStats(Builder $query, string $dateField = 'created_at', array $periods = []): array
    {
        $cacheKey = $this->generateStatsKey($query, 'time_based', [$dateField, $periods]);

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheMinutes), function () use ($query, $dateField, $periods) {
            $stats = [];
            $now = now();

            $defaultPeriods = [
                'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
                'yesterday' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
                'this_week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
                'last_week' => [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()],
                'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
                'last_month' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
                'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
                'last_30_days' => [$now->copy()->subDays(30), $now],
                'last_90_days' => [$now->copy()->subDays(90), $now],
            ];

            $periodsToCalculate = empty($periods) ? $defaultPeriods : array_intersect_key($defaultPeriods, array_flip($periods));

            foreach ($periodsToCalculate as $period => [$start, $end]) {
                $periodQuery = clone $query;
                $stats[$period] = $periodQuery->whereBetween($dateField, [$start, $end])->count();
            }

            return $stats;
        });
    }

    /**
     * Get distribution statistics
     */
    public function getDistributionStats(Builder $query, string $field, int $limit = 10): array
    {
        $cacheKey = $this->generateStatsKey($query, 'distribution', [$field, $limit]);

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheMinutes), function () use ($query, $field, $limit) {
            return $query->select($field, DB::raw('COUNT(*) as count'))
                        ->groupBy($field)
                        ->orderByDesc('count')
                        ->limit($limit)
                        ->pluck('count', $field)
                        ->toArray();
        });
    }

    /**
     * Get growth statistics
     */
    public function getGrowthStats(Builder $query, string $dateField = 'created_at', string $period = 'monthly', int $periods = 12): array
    {
        $cacheKey = $this->generateStatsKey($query, 'growth', [$dateField, $period, $periods]);

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheMinutes), function () use ($query, $dateField, $period, $periods) {
            $dateFormat = match ($period) {
                'daily' => '%Y-%m-%d',
                'weekly' => '%Y-%u',
                'monthly' => '%Y-%m',
                'yearly' => '%Y',
                default => '%Y-%m'
            };

            $startDate = match ($period) {
                'daily' => now()->subDays($periods),
                'weekly' => now()->subWeeks($periods),
                'monthly' => now()->subMonths($periods),
                'yearly' => now()->subYears($periods),
                default => now()->subMonths($periods)
            };

            return $query->select(
                    DB::raw("DATE_FORMAT({$dateField}, '{$dateFormat}') as period"),
                    DB::raw('COUNT(*) as count')
                )
                ->where($dateField, '>=', $startDate)
                ->groupBy('period')
                ->orderBy('period')
                ->pluck('count', 'period')
                ->toArray();
        });
    }

    /**
     * Get percentile statistics
     */
    public function getPercentileStats(Builder $query, string $field, array $percentiles = [25, 50, 75, 90, 95]): array
    {
        $cacheKey = $this->generateStatsKey($query, 'percentile', [$field, $percentiles]);

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheMinutes), function () use ($query, $field, $percentiles) {
            $values = $query->pluck($field)->filter()->sort()->values();
            $count = $values->count();
            
            if ($count === 0) {
                return array_fill_keys($percentiles, 0);
            }

            $stats = [];
            foreach ($percentiles as $percentile) {
                $index = ($percentile / 100) * ($count - 1);
                $lower = floor($index);
                $upper = ceil($index);
                
                if ($lower === $upper) {
                    $stats["p{$percentile}"] = $values[$lower];
                } else {
                    $weight = $index - $lower;
                    $stats["p{$percentile}"] = $values[$lower] * (1 - $weight) + $values[$upper] * $weight;
                }
            }

            return $stats;
        });
    }

    /**
     * Get advanced statistics
     */
    public function getAdvancedStats(Builder $query, string $field): array
    {
        $cacheKey = $this->generateStatsKey($query, 'advanced', [$field]);

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheMinutes), function () use ($query, $field) {
            $result = $query->selectRaw("
                COUNT({$field}) as count,
                AVG({$field}) as average,
                MIN({$field}) as minimum,
                MAX({$field}) as maximum,
                STDDEV({$field}) as standard_deviation,
                VARIANCE({$field}) as variance
            ")->first();

            return [
                'count' => (int) $result->count,
                'average' => round((float) $result->average, 2),
                'minimum' => (float) $result->minimum,
                'maximum' => (float) $result->maximum,
                'standard_deviation' => round((float) $result->standard_deviation, 2),
                'variance' => round((float) $result->variance, 2),
            ];
        });
    }

    /**
     * Get comparison statistics
     */
    public function getComparisonStats(Builder $currentQuery, Builder $previousQuery, array $metrics = ['count']): array
    {
        $current = [];
        $previous = [];

        foreach ($metrics as $metric) {
            switch ($metric) {
                case 'count':
                    $current[$metric] = $currentQuery->count();
                    $previous[$metric] = $previousQuery->count();
                    break;
                case 'sum':
                    if (isset($metrics['sum_field'])) {
                        $current[$metric] = $currentQuery->sum($metrics['sum_field']);
                        $previous[$metric] = $previousQuery->sum($metrics['sum_field']);
                    }
                    break;
                case 'avg':
                    if (isset($metrics['avg_field'])) {
                        $current[$metric] = $currentQuery->avg($metrics['avg_field']);
                        $previous[$metric] = $previousQuery->avg($metrics['avg_field']);
                    }
                    break;
            }
        }

        $comparison = [];
        foreach ($current as $metric => $value) {
            $prevValue = $previous[$metric] ?? 0;
            $change = $value - $prevValue;
            $percentChange = $prevValue > 0 ? ($change / $prevValue) * 100 : 0;

            $comparison[$metric] = [
                'current' => $value,
                'previous' => $prevValue,
                'change' => $change,
                'percent_change' => round($percentChange, 2),
                'trend' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable')
            ];
        }

        return $comparison;
    }

    /**
     * Clear statistics cache
     */
    public function clearCache(string $pattern = null): void
    {
        if ($pattern) {
            $keys = Cache::getStore()->getRedis()->keys("stats_{$pattern}_*");
        } else {
            $keys = Cache::getStore()->getRedis()->keys("stats_*");
        }

        if (!empty($keys)) {
            Cache::getStore()->getRedis()->del($keys);
        }
    }

    /**
     * Generate cache key for statistics
     */
    protected function generateStatsKey(Builder $query, string $type, array $params = []): string
    {
        $modelClass = get_class($query->getModel());
        $paramsHash = md5(serialize($params));
        
        return "stats_" . class_basename($modelClass) . "_{$type}_{$paramsHash}";
    }

    /**
     * Set cache duration
     */
    public function setCacheMinutes(int $minutes): self
    {
        $this->cacheMinutes = $minutes;
        return $this;
    }
}