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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // en, es, fr, de, ar, etc.
            $table->string('name', 100); // English, Spanish, French
            $table->string('native_name', 100); // English, Español, Français
            $table->string('flag_emoji', 10)->nullable(); // 🇺🇸, 🇪🇸, 🇫🇷
            $table->enum('direction', ['ltr', 'rtl'])->default('ltr');
            $table->string('date_format', 50)->default('MM/DD/YYYY');
            $table->enum('time_format', ['12h', '24h'])->default('12h');
            $table->string('currency_code', 10)->default('USD');
            $table->enum('currency_position', ['before', 'after'])->default('before');
            $table->string('thousand_separator', 5)->default(',');
            $table->string('decimal_separator', 5)->default('.');
            $table->integer('decimal_places')->default(2);
            $table->json('phone_format')->nullable(); // Format pattern for phone numbers
            $table->json('address_format')->nullable(); // Field order for addresses
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['is_active', 'sort_order']);
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};