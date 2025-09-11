<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Domain\Task\Models\Task;
use App\Domain\Task\Enums\TaskStatus;
use App\Domain\Task\Enums\TaskPriority;
use App\Domain\Task\Enums\TaskType;
use App\Domain\User\Models\User;
use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectPhase;

class EnhancedTaskManagementSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Creating Enhanced Task Management data with proper user assignments...');
        
        // Get the seeded users by role
        $users = $this->getSeededUsers();
        $projects = $this->getExistingProjects();
        $phases = $this->getExistingPhases();
        
        if (empty($projects) || empty($phases)) {
            $this->command->error('❌ No projects or phases found! Please run ProjectSeeder first.');
            return;
        }
        
        // Create tasks with proper role assignments
        $this->createRoleBasedTasks($projects, $phases, $users);
        
        $this->command->info('✅ Enhanced Task Management data created successfully!');
        $this->showUserTaskSummary($users);
    }
    
    private function getSeededUsers(): array
    {
        $this->command->info('👥 Getting seeded users by role...');
        
        $users = [
            'admin' => User::where('role', 'admin')->first(),
            'project_managers' => User::where('role', 'project_manager')->get()->toArray(),
            'supervisors' => User::where('role', 'supervisor')->get()->toArray(),
            'field_workers' => User::where('role', 'field_worker')->get()->toArray()
        ];
        
        $this->command->info("Found: " . ($users['admin'] ? 1 : 0) . " admin, " . count($users['project_managers']) . " project managers, " . count($users['supervisors']) . " supervisors, " . count($users['field_workers']) . " field workers");
        
        return $users;
    }
    
    private function getExistingProjects(): array
    {
        return Project::all()->toArray();
    }
    
    private function getExistingPhases(): array
    {
        return ProjectPhase::all()->toArray();
    }
    
    private function createRoleBasedTasks(array $projects, array $phases, array $users): void
    {
        $this->command->info('📝 Creating tasks with role-based assignments...');
        
        // Define task templates with role-specific assignments
        $taskTemplates = [
            // Today's urgent tasks for field workers
            [
                'name' => 'Complete Foundation Concrete Pour - Section A',
                'description' => 'Complete the concrete pour for foundation section A. Weather window is good today. Ensure proper curing and finish work.',
                'type' => TaskType::FOUNDATION,
                'priority' => TaskPriority::CRITICAL,
                'status' => TaskStatus::IN_PROGRESS,
                'estimated_hours' => 8,
                'progress' => 65,
                'role' => 'field_worker',
                'due_date' => Carbon::now()->addDays(1)
            ],
            [
                'name' => 'Install Electrical Conduit - Floor 2',
                'description' => 'Install electrical conduit for second floor according to electrical plans. Coordinate with structural team for proper routing.',
                'type' => TaskType::ELECTRICAL,
                'priority' => TaskPriority::HIGH,
                'status' => TaskStatus::IN_PROGRESS,
                'estimated_hours' => 6,
                'progress' => 40,
                'role' => 'field_worker',
                'due_date' => Carbon::now()->addDays(2)
            ],
            [
                'name' => 'Framing Inspection - Building A',
                'description' => 'Conduct detailed framing inspection for Building A. Check all connections, measurements, and code compliance.',
                'type' => TaskType::INSPECTION,
                'priority' => TaskPriority::HIGH,
                'status' => TaskStatus::NOT_STARTED,
                'estimated_hours' => 4,
                'progress' => 0,
                'role' => 'supervisor',
                'due_date' => Carbon::now()->addDays(1)
            ],
            [
                'name' => 'Plumbing Rough-In - Units 101-110',
                'description' => 'Complete plumbing rough-in for residential units 101-110. Install supply lines, drainage, and prepare for pressure testing.',
                'type' => TaskType::PLUMBING,
                'priority' => TaskPriority::MEDIUM,
                'status' => TaskStatus::NOT_STARTED,
                'estimated_hours' => 12,
                'progress' => 0,
                'role' => 'field_worker',
                'due_date' => Carbon::now()->addDays(3)
            ],
            [
                'name' => 'Site Safety Audit - Weekly',
                'description' => 'Conduct comprehensive weekly safety audit of all work areas. Document findings and ensure corrective actions are implemented.',
                'type' => TaskType::INSPECTION,
                'priority' => TaskPriority::HIGH,
                'status' => TaskStatus::IN_PROGRESS,
                'estimated_hours' => 3,
                'progress' => 20,
                'role' => 'supervisor',
                'due_date' => Carbon::now()
            ],
            [
                'name' => 'Review Architectural Changes - Plan Set Rev C',
                'description' => 'Review and approve architectural changes in revision C. Assess impact on schedule and budget.',
                'type' => TaskType::PLANNING,
                'priority' => TaskPriority::HIGH,
                'status' => TaskStatus::NOT_STARTED,
                'estimated_hours' => 4,
                'progress' => 0,
                'role' => 'project_manager',
                'due_date' => Carbon::now()->addDays(2)
            ],
            [
                'name' => 'Drywall Installation - Conference Rooms',
                'description' => 'Install drywall in all conference rooms on floor 3. Ensure proper finishing and preparation for painting.',
                'type' => TaskType::DRYWALL,
                'priority' => TaskPriority::MEDIUM,
                'status' => TaskStatus::NOT_STARTED,
                'estimated_hours' => 16,
                'progress' => 0,
                'role' => 'field_worker',
                'due_date' => Carbon::now()->addDays(5)
            ],
            [
                'name' => 'HVAC System Startup - Zone 1',
                'description' => 'Startup and commission HVAC system for zone 1. Test all components and verify proper operation.',
                'type' => TaskType::HVAC,
                'priority' => TaskPriority::HIGH,
                'status' => TaskStatus::NOT_STARTED,
                'estimated_hours' => 10,
                'progress' => 0,
                'role' => 'field_worker',
                'due_date' => Carbon::now()->addDays(4)
            ],
            [
                'name' => 'Material Delivery Coordination - Steel Beams',
                'description' => 'Coordinate delivery of structural steel beams. Ensure crane availability and proper staging area preparation.',
                'type' => TaskType::PLANNING,
                'priority' => TaskPriority::CRITICAL,
                'status' => TaskStatus::IN_PROGRESS,
                'estimated_hours' => 2,
                'progress' => 80,
                'role' => 'supervisor',
                'due_date' => Carbon::now()->addDays(1)
            ],
            [
                'name' => 'Quality Control Check - Concrete Finish',
                'description' => 'Perform quality control inspection of concrete finish work. Document any deficiencies and corrective actions.',
                'type' => TaskType::INSPECTION,
                'priority' => TaskPriority::MEDIUM,
                'status' => TaskStatus::COMPLETED,
                'estimated_hours' => 3,
                'progress' => 100,
                'role' => 'supervisor',
                'due_date' => Carbon::now()->subDays(1)
            ],
            [
                'name' => 'Client Progress Meeting - Monthly Review',
                'description' => 'Monthly project progress meeting with client. Present status, discuss upcoming milestones, and address concerns.',
                'type' => TaskType::DOCUMENTATION,
                'priority' => TaskPriority::HIGH,
                'status' => TaskStatus::NOT_STARTED,
                'estimated_hours' => 2,
                'progress' => 0,
                'role' => 'project_manager',
                'due_date' => Carbon::now()->addDays(7)
            ],
            [
                'name' => 'Flooring Installation - Lobby Area',
                'description' => 'Install premium flooring in main lobby area. Ensure perfect finish as this is client-facing space.',
                'type' => TaskType::FLOORING,
                'priority' => TaskPriority::MEDIUM,
                'status' => TaskStatus::NOT_STARTED,
                'estimated_hours' => 14,
                'progress' => 0,
                'role' => 'field_worker',
                'due_date' => Carbon::now()->addDays(6)
            ]
        ];
        
        $taskCount = 0;
        foreach ($projects as $project) {
            $projectObj = (object)$project;
            $projectPhases = array_filter($phases, fn($p) => $p['project_id'] === $projectObj->id);
            
            if (empty($projectPhases)) {
                continue;
            }
            
            // Create 8-12 tasks per project
            $tasksForProject = array_slice($taskTemplates, 0, rand(8, min(12, count($taskTemplates))));
            shuffle($tasksForProject);
            
            foreach ($tasksForProject as $index => $taskTemplate) {
                $assignedUser = $this->getUserByRole($users, $taskTemplate['role']);
                $createdByUser = $this->getUserByRole($users, 'project_manager');
                $randomPhase = $projectPhases[array_rand($projectPhases)];
                
                if (!$assignedUser || !$createdByUser) {
                    continue;
                }
                
                $startDate = $this->calculateStartDate($taskTemplate['status'], $taskTemplate['due_date']);
                $actualHours = $this->calculateActualHours($taskTemplate['status'], $taskTemplate['estimated_hours'], $taskTemplate['progress']);
                
                Task::create([
                    'name' => $taskTemplate['name'],
                    'description' => $taskTemplate['description'],
                    'status' => $taskTemplate['status'],
                    'priority' => $taskTemplate['priority'],
                    'task_type' => $taskTemplate['type'],
                    'project_id' => $projectObj->id,
                    'phase_id' => $randomPhase['id'],
                    'assigned_to_id' => $assignedUser->id,
                    'created_by_id' => $createdByUser->id,
                    'estimated_hours' => $taskTemplate['estimated_hours'],
                    'actual_hours' => $actualHours,
                    'progress_percentage' => $taskTemplate['progress'],
                    'start_date' => $startDate,
                    'due_date' => $taskTemplate['due_date'],
                    'completed_at' => $taskTemplate['status'] === TaskStatus::COMPLETED ? Carbon::now()->subDays(rand(1, 5)) : null,
                    'task_order' => $index + 1,
                    'is_overdue' => $taskTemplate['due_date']->isPast() && $taskTemplate['status'] !== TaskStatus::COMPLETED,
                    'is_due_soon' => $taskTemplate['due_date']->isToday() || $taskTemplate['due_date']->isTomorrow(),
                    'metadata' => [
                        'project_name' => $projectObj->name,
                        'assigned_role' => $taskTemplate['role'],
                        'created_for_demo' => true
                    ]
                ]);
                
                $taskCount++;
            }
        }
        
        $this->command->info("✅ Created {$taskCount} role-based tasks");
    }
    
    private function getUserByRole(array $users, string $role): ?User
    {
        switch ($role) {
            case 'admin':
                return $users['admin'];
            case 'project_manager':
                return !empty($users['project_managers']) ? 
                    User::find($users['project_managers'][array_rand($users['project_managers'])]['id']) : null;
            case 'supervisor':
                return !empty($users['supervisors']) ? 
                    User::find($users['supervisors'][array_rand($users['supervisors'])]['id']) : null;
            case 'field_worker':
                return !empty($users['field_workers']) ? 
                    User::find($users['field_workers'][array_rand($users['field_workers'])]['id']) : null;
            default:
                return null;
        }
    }
    
    private function calculateStartDate(TaskStatus $status, Carbon $dueDate): Carbon
    {
        switch ($status) {
            case TaskStatus::COMPLETED:
                return $dueDate->copy()->subDays(rand(7, 14));
            case TaskStatus::IN_PROGRESS:
                return Carbon::now()->subDays(rand(1, 5));
            case TaskStatus::NOT_STARTED:
                return $dueDate->copy()->subDays(rand(2, 10));
            default:
                return Carbon::now();
        }
    }
    
    private function calculateActualHours(TaskStatus $status, int $estimatedHours, int $progress): int
    {
        switch ($status) {
            case TaskStatus::COMPLETED:
                return (int)($estimatedHours * (0.9 + (rand(0, 20) / 100))); // 90-110% of estimate
            case TaskStatus::IN_PROGRESS:
                return (int)($estimatedHours * ($progress / 100 * 1.1)); // Slightly over progress rate
            default:
                return 0;
        }
    }
    
    private function showUserTaskSummary(array $users): void
    {
        $this->command->info("\n📊 === USER TASK ASSIGNMENT SUMMARY ===");
        
        // Show task assignments by role
        if ($users['admin']) {
            $adminTasks = Task::where('assigned_to_id', $users['admin']->id)->count();
            $this->command->info("👑 Admin ({$users['admin']->name}): {$adminTasks} tasks");
        }
        
        foreach ($users['project_managers'] as $pm) {
            $pmUser = User::find($pm['id']);
            $pmTasks = Task::where('assigned_to_id', $pm['id'])->count();
            $this->command->info("🏗️ Project Manager ({$pmUser->name}): {$pmTasks} tasks");
        }
        
        foreach ($users['supervisors'] as $supervisor) {
            $supervisorUser = User::find($supervisor['id']);
            $supervisorTasks = Task::where('assigned_to_id', $supervisor['id'])->count();
            $this->command->info("👷‍♂️ Supervisor ({$supervisorUser->name}): {$supervisorTasks} tasks");
        }
        
        foreach ($users['field_workers'] as $worker) {
            $workerUser = User::find($worker['id']);
            $workerTasks = Task::where('assigned_to_id', $worker['id'])->count();
            $this->command->info("🔨 Field Worker ({$workerUser->name}): {$workerTasks} tasks");
        }
        
        $this->command->info("=====================================\n");
        
        $this->command->info("🎯 Test Login Credentials (password: password123):");
        $this->command->info("• admin@construction.com - Admin Dashboard");
        $this->command->info("• michael.pm@construction.com - Project Manager Dashboard");
        $this->command->info("• david.supervisor@construction.com - Supervisor Dashboard");
        $this->command->info("• tom.worker@construction.com - Field Worker Dashboard (Mobile UI)");
        
        $totalTasks = Task::count();
        $this->command->info("\n✅ Total Tasks Created: {$totalTasks}");
        $this->command->info("🌐 Access your role-specific interface at: http://localhost:3073");
    }
}