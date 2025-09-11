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
        Schema::create('task_time_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id');
            $table->uuid('user_id');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('duration_minutes');
            $table->text('description')->nullable();
            $table->boolean('billable')->default(true);
            $table->decimal('hourly_rate', 8, 2)->nullable();
            
            // Geolocation fields
            $table->decimal('clock_in_location_lat', 10, 8)->nullable();
            $table->decimal('clock_in_location_lng', 11, 8)->nullable();
            $table->string('clock_in_address')->nullable();
            $table->decimal('clock_out_location_lat', 10, 8)->nullable();
            $table->decimal('clock_out_location_lng', 11, 8)->nullable();
            $table->string('clock_out_address')->nullable();
            
            // Media fields (JSON arrays of file paths)
            $table->json('clock_in_photos')->nullable();
            $table->json('clock_out_photos')->nullable();
            
            // Activity tracking
            $table->string('activity_type')->default('work');
            $table->boolean('is_active')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraints
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['task_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['start_time', 'end_time']);
            $table->index(['billable', 'hourly_rate']);
            $table->index(['activity_type', 'created_at']);
            $table->index(['is_active', 'user_id']);
            $table->index(['clock_in_location_lat', 'clock_in_location_lng']);
            $table->index(['clock_out_location_lat', 'clock_out_location_lng']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_time_logs');
    }
};