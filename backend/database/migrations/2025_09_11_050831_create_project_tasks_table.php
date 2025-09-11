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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_tasks');
    }
};
