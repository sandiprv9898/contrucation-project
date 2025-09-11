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
use App\Models\User;
use App\Domain\Project\Models\Project;

class SimpleTaskSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Creating simple task demo data...');
        
        // Get an existing user and project with explicit IDs
        $userId = '01992cd4-c0c4-700a-b1ee-97629b2d5f01'; // admin@construction.com
        $projectId = '0199373c-a08c-704f-aea6-34901ed5a91e'; // Downtown Office Complex
        
        // Create realistic construction tasks
        $this->createTasks($userId, $projectId);
        
        $this->command->info('✅ Simple task demo data created successfully!');
        $this->showSummary();
    }
    
    private function ensureUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'demo@construction.com'],
            [
                'id' => Str::uuid(),
                'name' => 'Demo User',
                'email' => 'demo@construction.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'role' => 'project_manager',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }
    
    private function createTasks(string $userId, string $projectId): void
    {
        $this->command->info('📝 Creating construction tasks...');
        
        $tasksData = [
            [
                'name' => 'Site Survey and Soil Testing',
                'description' => 'Comprehensive site survey including topographical mapping, utilities location, and environmental assessment. Coordinate with city planning department and obtain necessary access permits.',
                'type' => TaskType::PLANNING,
                'priority' => TaskPriority::HIGH,
                'status' => TaskStatus::COMPLETED,
                'estimated_hours' => 16,
                'actual_hours' => 14,
                'progress_percentage' => 100,
                'due_date' => Carbon::now()->subDays(20),
                'start_date' => Carbon::now()->subDays(25),
                'completed_at' => Carbon::now()->subDays(18)
            ],
            [
                'name' => 'Foundation Excavation and Preparation',
                'description' => 'Mass excavation work including site preparation, soil removal, and grading. Ensure proper safety protocols and coordinate with utility companies for any relocations required.',
                'type' => TaskType::FOUNDATION,
                'priority' => TaskPriority::CRITICAL,
                'status' => TaskStatus::IN_PROGRESS,
                'estimated_hours' => 48,
                'actual_hours' => 28,
                'progress_percentage' => 65,
                'due_date' => Carbon::now()->addDays(5),
                'start_date' => Carbon::now()->subDays(10)
            ],
            [
                'name' => 'Steel Frame Installation',
                'description' => 'Structural steel installation requiring certified welders and crane operations. All connections must be inspected and documented according to structural engineering specifications.',
                'type' => TaskType::FRAMING,
                'priority' => TaskPriority::HIGH,
                'status' => TaskStatus::NOT_STARTED,
                'estimated_hours' => 80,
                'actual_hours' => 0,
                'progress_percentage' => 0,
                'due_date' => Carbon::now()->addDays(25),
                'start_date' => Carbon::now()->addDays(8)
            ],
            [
                'name' => 'Electrical System Installation',
                'description' => 'Electrical system installation including power distribution, lighting circuits, and safety systems. All work must comply with local electrical codes and be inspected by certified electricians.',
                'type' => TaskType::ELECTRICAL,
                'priority' => TaskPriority::MEDIUM,
                'status' => TaskStatus::NOT_STARTED,
                'estimated_hours' => 60,
                'actual_hours' => 0,
                'progress_percentage' => 0,
                'due_date' => Carbon::now()->addDays(35),
                'start_date' => Carbon::now()->addDays(30)
            ],
            [
                'name' => 'Plumbing Rough-In Installation',
                'description' => 'Complete plumbing system installation including water supply, drainage, and fixture connections. Pressure testing required before final approval.',
                'type' => TaskType::PLUMBING,
                'priority' => TaskPriority::MEDIUM,
                'status' => TaskStatus::NOT_STARTED,
                'estimated_hours' => 45,
                'actual_hours' => 0,
                'progress_percentage' => 0,
                'due_date' => Carbon::now()->addDays(40),
                'start_date' => Carbon::now()->addDays(32)
            ],
            [
                'name' => 'HVAC System Installation',
                'description' => 'Heating, ventilation, and air conditioning system installation. Energy efficiency requirements must be met and systems tested for proper operation.',
                'type' => TaskType::HVAC,
                'priority' => TaskPriority::MEDIUM,
                'status' => TaskStatus::NOT_STARTED,
                'estimated_hours' => 55,
                'actual_hours' => 0,
                'progress_percentage' => 0,
                'due_date' => Carbon::now()->addDays(45),
                'start_date' => Carbon::now()->addDays(35)
            ],
            [
                'name' => 'Drywall Installation and Finishing',
                'description' => 'Interior finishing work including drywall installation, taping, mudding, and surface preparation for painting.',
                'type' => TaskType::DRYWALL,
                'priority' => TaskPriority::LOW,
                'status' => TaskStatus::NOT_STARTED,
                'estimated_hours' => 72,
                'actual_hours' => 0,
                'progress_percentage' => 0,
                'due_date' => Carbon::now()->addDays(55),
                'start_date' => Carbon::now()->addDays(48)
            ],
            [
                'name' => 'Interior Painting and Touch-ups',
                'description' => 'Interior finishing work including painting and touch-ups. Quality standards must be maintained throughout the process.',
                'type' => TaskType::PAINTING,
                'priority' => TaskPriority::LOW,
                'status' => TaskStatus::NOT_STARTED,
                'estimated_hours' => 40,
                'actual_hours' => 0,
                'progress_percentage' => 0,
                'due_date' => Carbon::now()->addDays(65),
                'start_date' => Carbon::now()->addDays(58)
            ],
            [
                'name' => 'Flooring Installation - Main Areas',
                'description' => 'Flooring installation for main areas including material selection, surface preparation, and professional installation.',
                'type' => TaskType::FLOORING,
                'priority' => TaskPriority::MEDIUM,
                'status' => TaskStatus::NOT_STARTED,
                'estimated_hours' => 48,
                'actual_hours' => 0,
                'progress_percentage' => 0,
                'due_date' => Carbon::now()->addDays(70),
                'start_date' => Carbon::now()->addDays(60)
            ],
            [
                'name' => 'Final Safety Inspection',
                'description' => 'Mandatory safety and code compliance inspection. All previous work must be completed and documented before inspection can proceed.',
                'type' => TaskType::INSPECTION,
                'priority' => TaskPriority::CRITICAL,
                'status' => TaskStatus::NOT_STARTED,
                'estimated_hours' => 8,
                'actual_hours' => 0,
                'progress_percentage' => 0,
                'due_date' => Carbon::now()->addDays(75),
                'start_date' => Carbon::now()->addDays(73)
            ]
        ];
        
        foreach ($tasksData as $taskData) {
            Task::create([
                'project_id' => $projectId,
                'name' => $taskData['name'],
                'description' => $taskData['description'],
                'status' => $taskData['status'],
                'priority' => $taskData['priority'],
                'task_type' => $taskData['type'],
                'assigned_to_id' => $userId,
                'created_by_id' => $userId,
                'estimated_hours' => $taskData['estimated_hours'],
                'actual_hours' => $taskData['actual_hours'],
                'progress_percentage' => $taskData['progress_percentage'],
                'start_date' => $taskData['start_date'],
                'due_date' => $taskData['due_date'],
                'completed_at' => $taskData['completed_at'] ?? null,
                'task_order' => 1,
                'metadata' => [
                    'demo_data' => true,
                    'construction_phase' => $this->getConstructionPhase($taskData['type']),
                    'estimated_duration_days' => ceil($taskData['estimated_hours'] / 8)
                ]
            ]);
        }
        
        // Create some task comments for better demo experience
        $this->createTaskComments($userId);
    }
    
    private function getConstructionPhase(TaskType $type): string
    {
        return match($type) {
            TaskType::PLANNING => 'Pre-Construction',
            TaskType::FOUNDATION => 'Foundation Phase',
            TaskType::FRAMING => 'Structure Phase', 
            TaskType::ELECTRICAL, TaskType::PLUMBING, TaskType::HVAC => 'MEP Systems',
            TaskType::DRYWALL, TaskType::PAINTING, TaskType::FLOORING => 'Interior Finishing',
            TaskType::INSPECTION => 'Quality Control',
            default => 'General Construction'
        };
    }
    
    private function createTaskComments(string $userId): void
    {
        $this->command->info('💬 Adding task comments...');
        
        $tasks = Task::limit(5)->get(); // Add comments to first 5 tasks
        
        $commentTemplates = [
            "Work progressing well. Weather conditions are favorable for construction.",
            "Quality inspection passed. Moving to next phase as scheduled.",
            "Coordinating with subcontractors for material delivery.",
            "Safety protocols reviewed with all team members before starting.",
            "Excellent progress made today. Team coordination is working well.",
            "Minor delay due to permit approval. Expected resolution tomorrow.",
            "All safety requirements met. Documentation is up to date."
        ];
        
        foreach ($tasks as $task) {
            $numComments = rand(1, 3);
            
            for ($i = 0; $i < $numComments; $i++) {
                $comment = $commentTemplates[array_rand($commentTemplates)];
                
                DB::table('task_comments')->insert([
                    'id' => Str::uuid(),
                    'task_id' => $task->id,
                    'user_id' => $userId,
                    'comment' => $comment,
                    'is_internal' => rand(0, 1) === 1,
                    'created_at' => Carbon::now()->subDays(rand(1, 5)),
                    'updated_at' => now()
                ]);
            }
        }
    }
    
    private function showSummary(): void
    {
        $taskCount = Task::count();
        $userCount = User::count();
        $commentCount = DB::table('task_comments')->count();
        
        $this->command->info("\n📊 === SIMPLE TASK SEEDER SUMMARY ===");
        $this->command->info("👥 Users: {$userCount}");
        $this->command->info("📝 Tasks: {$taskCount}");
        $this->command->info("💬 Comments: {$commentCount}");
        $this->command->info("===================================\n");
        
        $this->command->info("🌐 You can now access the Task Management interface at:");
        $this->command->info("Frontend: http://localhost:3074 (Tasks Page)");
        $this->command->info("Backend API: http://localhost:3071/api/tasks");
        
        $this->command->info("\n📋 Demo Tasks Created:");
        Task::take(5)->get()->each(function ($task) {
            $this->command->info("• {$task->name} - {$task->status->value} - {$task->progress_percentage}%");
        });
        
        $this->command->info("\n🔑 Demo User (password: 'password'):");
        $this->command->info("• demo@construction.com - Project Manager");
    }
}