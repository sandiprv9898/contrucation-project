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
        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id'); // Dependent task
            $table->uuid('depends_on_task_id'); // Prerequisite task
            $table->string('dependency_type', 50)->default('finish_to_start');
            $table->integer('lag_days')->default(0);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('depends_on_task_id')->references('id')->on('tasks')->onDelete('cascade');

            // Ensure unique dependency relationships
            $table->unique(['task_id', 'depends_on_task_id']);

            // Indexes for performance
            $table->index('task_id');
            $table->index('depends_on_task_id');
            $table->index('dependency_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_dependencies');
    }
};
