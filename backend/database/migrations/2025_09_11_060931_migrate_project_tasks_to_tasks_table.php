<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if project_tasks table exists and has data
        if (Schema::hasTable('project_tasks')) {
            // Migrate existing project_tasks data to tasks table
            $projectTasks = DB::table('project_tasks')->get();
            
            foreach ($projectTasks as $projectTask) {
                // Map old ProjectTask fields to new Task fields
                $taskData = [
                    'id' => $projectTask->id,
                    'project_id' => $projectTask->project_id,
                    'phase_id' => $projectTask->phase_id,
                    'parent_task_id' => null, // ProjectTask didn't support hierarchy
                    'name' => $projectTask->name,
                    'description' => $projectTask->description,
                    'status' => $this->mapStatus($projectTask->status),
                    'priority' => $this->mapPriority($projectTask->priority),
                    'task_type' => 'general', // Default type for migrated tasks
                    'assigned_to_id' => $projectTask->assigned_to_id,
                    'created_by_id' => $projectTask->assigned_to_id ?? '00000000-0000-0000-0000-000000000000', // Fallback
                    'estimated_hours' => $projectTask->estimated_hours,
                    'actual_hours' => $projectTask->actual_hours,
                    'progress_percentage' => $this->calculateProgress($projectTask->status),
                    'start_date' => null, // ProjectTask didn't have start_date
                    'due_date' => $projectTask->due_date,
                    'completed_at' => $projectTask->completed_at,
                    'task_order' => 0, // Default order
                    'metadata' => json_encode(['migrated_from_project_task' => true]),
                    'created_at' => $projectTask->created_at,
                    'updated_at' => $projectTask->updated_at,
                ];
                
                // Insert into tasks table, ignore if already exists
                DB::table('tasks')->insertOrIgnore($taskData);
            }
            
            // Drop the project_tasks table after migration
            Schema::dropIfExists('project_tasks');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate project_tasks table
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->uuid('phase_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 50)->default('pending');
            $table->string('priority', 20)->default('medium');
            $table->uuid('assigned_to_id')->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('dependencies')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('phase_id')->references('id')->on('project_phases')->onDelete('set null');
            $table->foreign('assigned_to_id')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('project_id');
            $table->index('phase_id');
            $table->index('assigned_to_id');
            $table->index('status');
            $table->index('priority');
            $table->index('due_date');
        });
        
        // Migrate tasks back to project_tasks if needed (only migrated ones)
        $migratedTasks = DB::table('tasks')
            ->whereRaw("JSON_EXTRACT(metadata, '$.migrated_from_project_task') = true")
            ->get();
            
        foreach ($migratedTasks as $task) {
            $projectTaskData = [
                'id' => $task->id,
                'project_id' => $task->project_id,
                'phase_id' => $task->phase_id,
                'name' => $task->name,
                'description' => $task->description,
                'status' => $this->unmapStatus($task->status),
                'priority' => $this->unmapPriority($task->priority),
                'assigned_to_id' => $task->assigned_to_id,
                'estimated_hours' => $task->estimated_hours,
                'actual_hours' => $task->actual_hours,
                'due_date' => $task->due_date,
                'completed_at' => $task->completed_at,
                'dependencies' => json_encode([]), // Empty dependencies
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,
            ];
            
            DB::table('project_tasks')->insertOrIgnore($projectTaskData);
        }
        
        // Remove migrated tasks from tasks table
        DB::table('tasks')
            ->whereRaw("JSON_EXTRACT(metadata, '$.migrated_from_project_task') = true")
            ->delete();
    }
    
    /**
     * Map old status values to new enum values
     */
    private function mapStatus(string $oldStatus): string
    {
        return match($oldStatus) {
            'pending' => 'not_started',
            'in_progress' => 'in_progress',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            default => 'not_started',
        };
    }
    
    /**
     * Map old priority values to new enum values
     */
    private function mapPriority(string $oldPriority): string
    {
        return match($oldPriority) {
            'low' => 'low',
            'medium' => 'medium',
            'high' => 'high',
            'urgent' => 'critical',
            default => 'medium',
        };
    }
    
    /**
     * Calculate progress percentage based on status
     */
    private function calculateProgress(string $status): int
    {
        return match($status) {
            'pending' => 0,
            'in_progress' => 50,
            'completed' => 100,
            'cancelled' => 0,
            default => 0,
        };
    }
    
    /**
     * Reverse map status values for rollback
     */
    private function unmapStatus(string $newStatus): string
    {
        return match($newStatus) {
            'not_started' => 'pending',
            'in_progress' => 'in_progress',
            'review' => 'in_progress',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            'on_hold' => 'pending',
            default => 'pending',
        };
    }
    
    /**
     * Reverse map priority values for rollback
     */
    private function unmapPriority(string $newPriority): string
    {
        return match($newPriority) {
            'low' => 'low',
            'medium' => 'medium',
            'high' => 'high',
            'critical' => 'urgent',
            default => 'medium',
        };
    }
};
