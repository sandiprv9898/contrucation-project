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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('preferred_language_id')->nullable()->constrained('languages')->onDelete('set null')->after('email_verified_at');
            $table->string('date_format_preference', 50)->nullable()->after('preferred_language_id');
            $table->string('time_format_preference', 10)->nullable()->after('date_format_preference');
            $table->string('timezone_preference', 100)->nullable()->after('time_format_preference');
            $table->enum('measurement_system', ['metric', 'imperial'])->nullable()->after('timezone_preference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('preferred_language_id');
            $table->dropColumn([
                'date_format_preference',
                'time_format_preference', 
                'timezone_preference',
                'measurement_system'
            ]);
        });
    }
};