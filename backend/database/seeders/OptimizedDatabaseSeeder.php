<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OptimizedDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with optimized, non-duplicate data.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting optimized database seeding...');
        
        // 1. Core System Data (Required for application functionality)
        $this->call([
            LanguageSeeder::class,           // Languages for i18n
            TranslationSeeder::class,        // Base translations
            NavigationTranslationSeeder::class, // Navigation translations
        ]);
        
        // 2. Demo Data (Users, Projects, Tasks)
        $this->call([
            UserSeeder::class,               // Create all users first
            ProjectSeeder::class,            // Create projects (may create basic companies)
            TaskManagementSeeder::class,     // Create tasks using existing users/projects
        ]);
        
        $this->command->info('✅ Optimized database seeding completed successfully!');
        $this->command->info('🌐 Access the application: http://localhost:3073');
        $this->command->info('🔑 Test login: admin@construction.com (password: password123)');
    }
}