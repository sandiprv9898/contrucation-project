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
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->unique();
            $table->string('business_registration', 100)->nullable();
            $table->string('tax_identification', 100)->nullable();
            $table->string('industry_type', 50)->nullable();
            $table->enum('company_size', ['startup', 'small', 'medium', 'large', 'enterprise'])->nullable();
            $table->date('founded_date')->nullable();
            $table->text('description')->nullable();
            $table->string('website', 255)->nullable();
            $table->json('social_media')->nullable();
            $table->json('certifications')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->index(['company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
