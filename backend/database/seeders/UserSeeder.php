<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin Users
        User::create([
            'name' => 'John Administrator',
            'email' => 'admin@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Sarah Admin',
            'email' => 'sarah.admin@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create Project Managers
        User::create([
            'name' => 'Michael Johnson',
            'email' => 'michael.pm@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'project_manager',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Emily Davis',
            'email' => 'emily.pm@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'project_manager',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Robert Wilson',
            'email' => 'robert.pm@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'project_manager',
            'email_verified_at' => now(),
        ]);

        // Create Supervisors
        User::create([
            'name' => 'David Brown',
            'email' => 'david.supervisor@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Lisa Anderson',
            'email' => 'lisa.supervisor@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'James Miller',
            'email' => 'james.supervisor@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Patricia Martinez',
            'email' => 'patricia.supervisor@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
            'email_verified_at' => now(),
        ]);

        // Create Field Workers
        User::create([
            'name' => 'Tom Smith',
            'email' => 'tom.worker@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'field_worker',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Mary Johnson',
            'email' => 'mary.worker@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'field_worker',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Christopher Lee',
            'email' => 'chris.worker@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'field_worker',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Jennifer White',
            'email' => 'jennifer.worker@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'field_worker',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Daniel Garcia',
            'email' => 'daniel.worker@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'field_worker',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Linda Rodriguez',
            'email' => 'linda.worker@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'field_worker',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Paul Thompson',
            'email' => 'paul.worker@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'field_worker',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Nancy Harris',
            'email' => 'nancy.worker@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'field_worker',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Mark Clark',
            'email' => 'mark.worker@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'field_worker',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Susan Lewis',
            'email' => 'susan.worker@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'field_worker',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Kevin Walker',
            'email' => 'kevin.worker@construction.com',
            'password' => Hash::make('password123'),
            'role' => 'field_worker',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Created 20 users with different roles');
        $this->command->info('   - 2 Admins');
        $this->command->info('   - 3 Project Managers');
        $this->command->info('   - 4 Supervisors');
        $this->command->info('   - 11 Field Workers');
        $this->command->info('   Default password for all: password123');
    }
}
