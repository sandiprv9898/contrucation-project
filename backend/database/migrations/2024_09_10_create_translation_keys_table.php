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
        Schema::create('translation_keys', function (Blueprint $table) {
            $table->id();
            $table->string('namespace', 100); // common, construction, auth, navigation, etc.
            $table->string('group', 100)->nullable(); // materials, equipment, safety, etc.
            $table->string('key', 255); // concrete, hard_hat, login.title, etc.
            $table->text('description')->nullable(); // Description for translators
            $table->json('context')->nullable(); // Additional context, usage examples
            $table->enum('type', ['text', 'html', 'plural', 'rich'])->default('text');
            $table->boolean('is_construction_term')->default(false);
            $table->boolean('requires_localization')->default(true);
            $table->timestamps();

            // Indexes
            $table->unique(['namespace', 'group', 'key']);
            $table->index(['namespace', 'group']);
            $table->index('is_construction_term');
            $table->index('requires_localization');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_keys');
    }
};