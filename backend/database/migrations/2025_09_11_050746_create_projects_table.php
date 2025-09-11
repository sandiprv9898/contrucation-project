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
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 50)->default('draft');
            $table->string('priority', 20)->default('medium');
            $table->string('project_type', 50)->default('construction');
            $table->uuid('client_company_id');
            $table->uuid('project_manager_id');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('planned_budget', 15, 2)->nullable();
            $table->decimal('actual_budget', 15, 2)->default(0);
            $table->integer('progress_percentage')->default(0)->check('progress_percentage >= 0 AND progress_percentage <= 100');
            $table->text('address')->nullable();
            $table->json('coordinates')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('client_company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('project_manager_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes for performance
            $table->index('status');
            $table->index('client_company_id');
            $table->index('project_manager_id');
            $table->index(['start_date', 'end_date']);
            $table->index('priority');
            $table->index('project_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
