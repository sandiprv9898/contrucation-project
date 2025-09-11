<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Task\Models\Task;
use App\Domain\User\Models\User;
use App\Domain\Task\Enums\TaskType;

class UserTaskAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎯 Assigning existing tasks to seeded users based on roles...');
        
        // Get users by role
        $admin = User::where('email', 'admin@construction.com')->first();
        $projectManagers = User::where('role', 'project_manager')->get();
        $supervisors = User::where('role', 'supervisor')->get(); 
        $fieldWorkers = User::where('role', 'field_worker')->get();
        
        if (!$admin || $projectManagers->isEmpty() || $supervisors->isEmpty() || $fieldWorkers->isEmpty()) {
            $this->command->error('❌ Missing required user roles! Please run UserSeeder first.');
            return;
        }
        
        $this->command->info("Found: 1 admin, {$projectManagers->count()} PMs, {$supervisors->count()} supervisors, {$fieldWorkers->count()} field workers");
        
        // Get all existing tasks
        $tasks = Task::all();
        
        if ($tasks->isEmpty()) {
            $this->command->error('❌ No tasks found! Please run ProjectSeeder and TaskManagementSeeder first.');
            return;
        }
        
        $this->command->info("Assigning {$tasks->count()} tasks to users...");
        
        $assignmentCount = 0;
        foreach ($tasks as $task) {
            $assignedUser = $this->getAssignedUserByTaskType($task->task_type, $admin, $projectManagers, $supervisors, $fieldWorkers);
            
            if ($assignedUser) {
                $task->update([
                    'assigned_to_id' => $assignedUser->id,
                    // Make sure we have a creator (project manager)
                    'created_by_id' => $task->created_by_id ?: $projectManagers->random()->id
                ]);
                $assignmentCount++;
            }
        }
        
        $this->command->info("✅ Successfully assigned {$assignmentCount} tasks");
        
        // Show assignment summary
        $this->showAssignmentSummary($admin, $projectManagers, $supervisors, $fieldWorkers);
    }
    
    private function getAssignedUserByTaskType(TaskType $taskType, User $admin, $projectManagers, $supervisors, $fieldWorkers): ?User
    {
        // Define role assignments based on task types
        return match($taskType) {
            TaskType::PLANNING, TaskType::DOCUMENTATION => $projectManagers->random(),
            TaskType::INSPECTION => $supervisors->random(),
            TaskType::GENERAL, TaskType::FOUNDATION, TaskType::FRAMING, 
            TaskType::ELECTRICAL, TaskType::PLUMBING, TaskType::HVAC,
            TaskType::DRYWALL, TaskType::FLOORING, TaskType::PAINTING,
            TaskType::FINISHING => $fieldWorkers->random(),
            TaskType::CLEANUP => $fieldWorkers->random(),
            default => $fieldWorkers->random()
        };
    }
    
    private function showAssignmentSummary(User $admin, $projectManagers, $supervisors, $fieldWorkers): void
    {
        $this->command->info("\n📊 === TASK ASSIGNMENT SUMMARY ===");
        
        // Admin tasks
        $adminTasks = Task::where('assigned_to_id', $admin->id)->count();
        $this->command->info("👑 {$admin->name}: {$adminTasks} tasks");
        
        // Project Manager tasks
        foreach ($projectManagers as $pm) {
            $pmTasks = Task::where('assigned_to_id', $pm->id)->count();
            $this->command->info("🏗️ {$pm->name}: {$pmTasks} tasks");
        }
        
        // Supervisor tasks  
        foreach ($supervisors as $supervisor) {
            $supervisorTasks = Task::where('assigned_to_id', $supervisor->id)->count();
            $this->command->info("👷‍♂️ {$supervisor->name}: {$supervisorTasks} tasks");
        }
        
        // Field Worker tasks (show first few)
        foreach ($fieldWorkers->take(5) as $worker) {
            $workerTasks = Task::where('assigned_to_id', $worker->id)->count();
            $this->command->info("🔨 {$worker->name}: {$workerTasks} tasks");
        }
        
        if ($fieldWorkers->count() > 5) {
            $remainingWorkers = $fieldWorkers->skip(5)->count();
            $remainingTasks = Task::whereIn('assigned_to_id', $fieldWorkers->skip(5)->pluck('id'))->count();
            $this->command->info("... and {$remainingWorkers} more field workers with {$remainingTasks} total tasks");
        }
        
        $this->command->info("=====================================\n");
        
        $this->command->info("🎯 Quick Login Test Accounts (password: password123):");
        $this->command->info("• admin@construction.com - Admin Dashboard");
        $this->command->info("• michael.pm@construction.com - Project Manager Dashboard");
        $this->command->info("• david.supervisor@construction.com - Supervisor Dashboard");  
        $this->command->info("• tom.worker@construction.com - Field Worker Dashboard (Mobile-Optimized)");
        
        $this->command->info("\n🌐 Access the application: http://localhost:3073");
        $this->command->info("📱 Field workers will see the mobile-optimized interface at: /app/worker/tasks");
    }
}