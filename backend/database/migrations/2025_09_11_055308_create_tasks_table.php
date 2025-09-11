<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->uuid('phase_id')->nullable();
            $table->uuid('parent_task_id')->nullable(); // For hierarchical tasks
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 50)->default('not_started');
            $table->string('priority', 20)->default('medium');
            $table->string('task_type', 50)->default('general');
            $table->uuid('assigned_to_id')->nullable();
            $table->uuid('created_by_id');
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->default(0);
            $table->integer('progress_percentage')->default(0)->check('progress_percentage >= 0 AND progress_percentage <= 100');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('task_order')->default(0);
            $table->json('metadata')->nullable(); // Construction-specific fields
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('phase_id')->references('id')->on('project_phases')->onDelete('set null');
            $table->foreign('parent_task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('assigned_to_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('restrict');

            // Indexes for performance
            $table->index('project_id');
            $table->index('phase_id');
            $table->index('parent_task_id');
            $table->index('assigned_to_id');
            $table->index('created_by_id');
            $table->index('status');
            $table->index('priority');
            $table->index('task_type');
            $table->index('due_date');
            $table->index(['project_id', 'status']);
            $table->index(['assigned_to_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
