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
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('target_date');
            $table->date('completed_date')->nullable();
            $table->string('status', 50)->default('pending');
            $table->string('milestone_type', 50)->default('delivery');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            // Indexes
            $table->index('project_id');
            $table->index('target_date');
            $table->index('status');
            $table->index('milestone_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_milestones');
    }
};
