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
        Schema::create('task_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category', 100); // 'foundation', 'framing', 'electrical', etc.
            $table->string('task_type', 50)->default('general');
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->string('default_priority', 20)->default('medium');
            $table->json('required_skills')->nullable(); // Array of skill requirements
            $table->json('safety_requirements')->nullable();
            $table->json('materials_needed')->nullable();
            $table->json('checklist')->nullable(); // Task completion checklist
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for performance
            $table->index('category');
            $table->index('task_type');
            $table->index('is_active');
            $table->index(['category', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_templates');
    }
};
