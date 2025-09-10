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
        Schema::create('settings_audit_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('setting_id')->nullable();
            $table->string('category', 50);
            $table->string('key', 100);
            $table->json('old_value')->nullable();
            $table->json('new_value');
            $table->uuid('changed_by')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign key constraints
            $table->foreign('setting_id')->references('id')->on('settings')->onDelete('set null');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
            
            // Index for performance
            $table->index(['setting_id', 'created_at']);
            $table->index(['category', 'key', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings_audit_log');
    }
};
