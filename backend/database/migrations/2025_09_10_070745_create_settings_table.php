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
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('category', 50)->index();
            $table->string('key', 100);
            $table->json('value');
            $table->uuid('company_id')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            
            // Foreign key constraints (temporarily disabled for testing)
            // $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            // $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Unique constraint for category + key + company
            $table->unique(['category', 'key', 'company_id'], 'settings_unique');
            
            // Indexes
            $table->index(['category', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
