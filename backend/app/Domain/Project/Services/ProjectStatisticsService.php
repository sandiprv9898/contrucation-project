<?php

namespace App\Domain\Project\Services;

use App\Domain\Project\Repositories\Contracts\ProjectRepositoryInterface;
use Carbon\Carbon;

class ProjectStatisticsService
{
    public function __construct(
        private ProjectRepositoryInterface $projectRepository
    ) {}

    public function getOverallStatistics(): array
    {
        $baseStats = $this->projectRepository->getStatistics();
        
        return array_merge($baseStats, [
            'monthly_completion_trend' => $this->getMonthlyCompletionTrend(),
            'budget_performance_trend' => $this->getBudgetPerformanceTrend(),
            'priority_distribution' => $this->getPriorityDistribution(),
            'type_distribution' => $this->getTypeDistribution(),
        ]);
    }

    public function getStatisticsByCompany(string $companyId): array
    {
        $projects = $this->projectRepository->getByCompanyId($companyId);
        
        return $this->calculateStatisticsForCollection($projects);
    }

    public function getStatisticsByManager(string $managerId): array
    {
        $projects = $this->projectRepository->getByManagerId($managerId);
        
        return $this->calculateStatisticsForCollection($projects);
    }

    public function getDashboardMetrics(): array
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();
        
        return [
            'active_projects' => $this->projectRepository->getByStatus('active')->count(),
            'overdue_projects' => $this->projectRepository->getOverdueProjects()->count(),
            'completed_this_month' => $this->getProjectsCompletedInPeriod($lastMonth, $now)->count(),
            'upcoming_milestones' => $this->getUpcomingMilestones(7),
            'budget_alerts' => $this->getBudgetAlerts(),
            'recent_activities' => $this->getRecentActivities(10),
        ];
    }

    public function getPerformanceMetrics(): array
    {
        return [
            'average_project_duration' => $this->getAverageProjectDuration(),
            'on_time_completion_rate' => $this->getOnTimeCompletionRate(),
            'budget_accuracy_rate' => $this->getBudgetAccuracyRate(),
            'resource_utilization' => $this->getResourceUtilization(),
        ];
    }

    private function getMonthlyCompletionTrend(): array
    {
        $months = [];
        $now = Carbon::now();
        
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();
            
            $completed = $this->getProjectsCompletedInPeriod($startOfMonth, $endOfMonth)->count();
            
            $months[] = [
                'month' => $month->format('M Y'),
                'completed' => $completed
            ];
        }
        
        return $months;
    }

    private function getBudgetPerformanceTrend(): array
    {
        $months = [];
        $now = Carbon::now();
        
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();
            
            $projects = $this->getProjectsCompletedInPeriod($startOfMonth, $endOfMonth);
            
            $planned = $projects->sum('planned_budget');
            $actual = $projects->sum('actual_budget');
            $variance = $planned > 0 ? (($actual - $planned) / $planned) * 100 : 0;
            
            $months[] = [
                'month' => $month->format('M Y'),
                'planned_budget' => $planned,
                'actual_budget' => $actual,
                'variance_percentage' => round($variance, 2)
            ];
        }
        
        return $months;
    }

    private function getPriorityDistribution(): array
    {
        return [
            'low' => $this->projectRepository->getByStatus('active')->where('priority', 'low')->count(),
            'medium' => $this->projectRepository->getByStatus('active')->where('priority', 'medium')->count(),
            'high' => $this->projectRepository->getByStatus('active')->where('priority', 'high')->count(),
            'urgent' => $this->projectRepository->getByStatus('active')->where('priority', 'urgent')->count(),
        ];
    }

    private function getTypeDistribution(): array
    {
        $allProjects = collect($this->projectRepository->getByStatus('active'));
        
        return [
            'construction' => $allProjects->where('project_type', 'construction')->count(),
            'renovation' => $allProjects->where('project_type', 'renovation')->count(),
            'maintenance' => $allProjects->where('project_type', 'maintenance')->count(),
            'inspection' => $allProjects->where('project_type', 'inspection')->count(),
            'consulting' => $allProjects->where('project_type', 'consulting')->count(),
        ];
    }

    private function calculateStatisticsForCollection($projects): array
    {
        $total = $projects->count();
        $completed = $projects->where('status', 'completed')->count();
        $active = $projects->where('status', 'active')->count();
        $overdue = $projects->filter(fn($p) => $p->isOverdue())->count();
        
        $totalBudget = $projects->sum('planned_budget');
        $spentBudget = $projects->sum('actual_budget');
        $averageProgress = $projects->avg('progress_percentage');
        
        return [
            'total_projects' => $total,
            'active_projects' => $active,
            'completed_projects' => $completed,
            'overdue_projects' => $overdue,
            'total_budget' => $totalBudget,
            'spent_budget' => $spentBudget,
            'average_progress' => round($averageProgress, 2),
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'budget_utilization' => $totalBudget > 0 ? round(($spentBudget / $totalBudget) * 100, 2) : 0,
        ];
    }

    private function getProjectsCompletedInPeriod(Carbon $start, Carbon $end)
    {
        return collect($this->projectRepository->getByStatus('completed'))
            ->filter(function ($project) use ($start, $end) {
                return $project->updated_at >= $start && $project->updated_at <= $end;
            });
    }

    private function getUpcomingMilestones(int $days): array
    {
        // This would need to be implemented with milestone repository
        // For now, return empty array
        return [];
    }

    private function getBudgetAlerts(): array
    {
        $projects = $this->projectRepository->getByStatus('active');
        $alerts = [];
        
        foreach ($projects as $project) {
            if ($project->isOverBudget()) {
                $alerts[] = [
                    'project_id' => $project->id,
                    'project_name' => $project->name,
                    'type' => 'over_budget',
                    'message' => "Project is over budget by " . number_format($project->getBudgetVariance(), 2),
                    'severity' => 'high'
                ];
            }
            
            if ($project->isOverdue()) {
                $alerts[] = [
                    'project_id' => $project->id,
                    'project_name' => $project->name,
                    'type' => 'overdue',
                    'message' => "Project is overdue",
                    'severity' => 'high'
                ];
            }
        }
        
        return $alerts;
    }

    private function getRecentActivities(int $limit): array
    {
        // This would need to be implemented with activity logging
        // For now, return empty array
        return [];
    }

    private function getAverageProjectDuration(): float
    {
        $completedProjects = $this->projectRepository->getByStatus('completed');
        $durations = [];
        
        foreach ($completedProjects as $project) {
            $duration = $project->getDurationInDays();
            if ($duration !== null) {
                $durations[] = $duration;
            }
        }
        
        return count($durations) > 0 ? array_sum($durations) / count($durations) : 0;
    }

    private function getOnTimeCompletionRate(): float
    {
        $completedProjects = $this->projectRepository->getByStatus('completed');
        $total = $completedProjects->count();
        
        if ($total === 0) {
            return 0;
        }
        
        $onTime = $completedProjects->filter(function ($project) {
            return $project->end_date && $project->updated_at <= $project->end_date;
        })->count();
        
        return ($onTime / $total) * 100;
    }

    private function getBudgetAccuracyRate(): float
    {
        $completedProjects = $this->projectRepository->getByStatus('completed');
        $total = $completedProjects->count();
        
        if ($total === 0) {
            return 0;
        }
        
        $withinBudget = $completedProjects->filter(function ($project) {
            return !$project->isOverBudget();
        })->count();
        
        return ($withinBudget / $total) * 100;
    }

    private function getResourceUtilization(): array
    {
        // This would need to be implemented with resource tracking
        // For now, return basic data
        return [
            'total_hours_planned' => 0,
            'total_hours_actual' => 0,
            'utilization_rate' => 0
        ];
    }
}