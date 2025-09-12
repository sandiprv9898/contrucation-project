<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with perfect, simple structure.
     * No duplicates, clean dependencies, proper ordering.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting optimized database seeding...');
        
        // 1. System Data (Required)
        $this->call([
            LanguageSeeder::class,           // Languages for internationalization
            TranslationSeeder::class,        // Base translations 
            NavigationTranslationSeeder::class, // Navigation translations
        ]);
        
        // 2. Demo Data (Clean dependencies)
        $this->call([
            UserSeeder::class,               // Create all users first (no duplicates)
            ProjectSeeder::class,            // Create projects + companies (uses existing users)
            TaskManagementSeeder::class,     // Create tasks (uses existing users/projects)
        ]);
        
        $this->command->info('✅ Perfect seeding completed! No duplicates, clean structure.');
        $this->command->info('🌐 Application: http://localhost:3073');
        $this->command->info('🔑 Login: admin@construction.com / password123');
    }
}
