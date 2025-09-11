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
        Schema::create('project_phases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('phase_order');
            $table->string('status', 50)->default('pending');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('estimated_duration_days')->nullable();
            $table->integer('actual_duration_days')->nullable();
            $table->decimal('budget_allocation', 15, 2)->nullable();
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            // Unique constraint for project_id and phase_order
            $table->unique(['project_id', 'phase_order']);

            // Indexes
            $table->index('project_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_phases');
    }
};
