<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Domain\Task\Models\Task;
use App\Domain\Task\Enums\TaskStatus;
use App\Domain\Task\Enums\TaskPriority;
use App\Domain\Task\Enums\TaskType;
use App\Models\User;
use App\Domain\User\Models\Company;
use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectPhase;

class TaskManagementSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Creating Task Management demo data...');
        
        // Get existing data or create basic requirements
        $users = $this->ensureUsers();
        $companies = $this->ensureCompanies();
        $projects = $this->getExistingProjects();
        $phases = $this->getExistingPhases();
        
        // Create tasks with realistic construction scenarios
        $this->createConstructionTasks($projects, $phases, $users);
        
        $this->command->info('✅ Task Management demo data created successfully!');
        $this->showSummary();
    }
    
    private function ensureUsers(): array
    {
        $this->command->info('👥 Creating users...');
        
        $usersData = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@construction.com',
                'role' => 'field_worker',
                'specialization' => 'General Construction'
            ],
            [
                'name' => 'Jane Manager',
                'email' => 'jane.manager@construction.com',
                'role' => 'project_manager',
                'specialization' => 'Project Management'
            ],
            [
                'name' => 'Mike Rodriguez',
                'email' => 'mike.rodriguez@construction.com',
                'role' => 'field_worker',
                'specialization' => 'Steel & Concrete'
            ],
            [
                'name' => 'Sarah Inspector',
                'email' => 'sarah.inspector@construction.com',
                'role' => 'supervisor',
                'specialization' => 'Quality Control'
            ],
            [
                'name' => 'David Electrician',
                'email' => 'david.electrician@construction.com',
                'role' => 'specialist',
                'specialization' => 'Electrical Systems'
            ],
            [
                'name' => 'Lisa Plumber',
                'email' => 'lisa.plumber@construction.com',
                'role' => 'specialist',
                'specialization' => 'Plumbing & HVAC'
            ],
            [
                'name' => 'Robert Foreman',
                'email' => 'robert.foreman@construction.com',
                'role' => 'supervisor',
                'specialization' => 'Site Supervision'
            ],
            [
                'name' => 'Maria Architect',
                'email' => 'maria.architect@construction.com',
                'role' => 'architect',
                'specialization' => 'Design & Planning'
            ]
        ];
        
        $users = [];
        foreach ($usersData as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'id' => Str::uuid(),
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'email_verified_at' => now(),
                    'password' => bcrypt('password'),
                    'role' => $userData['role'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
            $users[] = $user;
        }
        
        return $users;
    }
    
    private function ensureCompanies(): array
    {
        $this->command->info('🏢 Creating companies...');
        
        $companiesData = [
            [
                'name' => 'Metropolitan Construction Corp',
                'type' => 'general_contractor',
                'description' => 'Leading construction company specializing in commercial and residential projects'
            ],
            [
                'name' => 'Residential Builders Ltd',
                'type' => 'residential_builder', 
                'description' => 'Specialized residential construction and renovation company'
            ],
            [
                'name' => 'Urban Development Group',
                'type' => 'developer',
                'description' => 'Real estate development and urban planning specialists'
            ]
        ];
        
        $companies = [];
        foreach ($companiesData as $companyData) {
            $company = Company::firstOrCreate(
                ['name' => $companyData['name']],
                [
                    'id' => Str::uuid(),
                    'name' => $companyData['name'],
                    'type' => $companyData['type'],
                    'description' => $companyData['description'],
                    'address' => '123 Construction Ave, Builder City, BC 12345',
                    'phone' => '+1-555-0100',
                    'email' => strtolower(str_replace(' ', '', $companyData['name'])) . '@example.com',
                    'website' => 'https://' . strtolower(str_replace(' ', '', $companyData['name'])) . '.com',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
            $companies[] = $company;
        }
        
        return $companies;
    }
    
    private function getExistingProjects(): array
    {
        $this->command->info('📊 Using existing projects for tasks...');
        
        $projects = Project::all();
        
        if ($projects->isEmpty()) {
            $this->command->warn('⚠️  No projects found! Please run ProjectSeeder first.');
            return [];
        }
        
        return $projects->toArray();
    }
    
    private function getExistingPhases(): array
    {
        $this->command->info('📊 Using existing project phases for tasks...');
        
        $phases = ProjectPhase::all();
        
        if ($phases->isEmpty()) {
            $this->command->warn('⚠️  No project phases found!');
            return [];
        }
        
        return $phases->toArray();
    }
    
    private function createProjects(array $companies, array $users): array
    {
        $this->command->info('🏗️ Creating projects...');
        
        $projectsData = [
            [
                'name' => 'Downtown Office Complex',
                'description' => 'Modern 15-story office building with underground parking and retail space',
                'type' => 'commercial',
                'status' => 'active',
                'priority' => 'high',
                'client_name' => 'Metro Business Solutions',
                'planned_budget' => 15000000.00,
                'actual_budget' => 8500000.00,
                'planned_start_date' => Carbon::now()->subMonths(6),
                'planned_end_date' => Carbon::now()->addMonths(18),
                'actual_start_date' => Carbon::now()->subMonths(6),
                'progress_percentage' => 35
            ],
            [
                'name' => 'Riverside Residential Complex', 
                'description' => '120-unit luxury apartment complex with amenities',
                'type' => 'residential',
                'status' => 'active',
                'priority' => 'medium',
                'client_name' => 'Riverside Properties Inc',
                'planned_budget' => 8500000.00,
                'actual_budget' => 4200000.00,
                'planned_start_date' => Carbon::now()->subMonths(3),
                'planned_end_date' => Carbon::now()->addMonths(12),
                'actual_start_date' => Carbon::now()->subMonths(3),
                'progress_percentage' => 55
            ],
            [
                'name' => 'Shopping Center Renovation',
                'description' => 'Complete renovation of existing shopping center including modernization',
                'type' => 'renovation',
                'status' => 'planning',
                'priority' => 'medium',
                'client_name' => 'Retail Spaces LLC',
                'planned_budget' => 3200000.00,
                'actual_budget' => 150000.00,
                'planned_start_date' => Carbon::now()->addMonth(),
                'planned_end_date' => Carbon::now()->addMonths(8),
                'progress_percentage' => 5
            ],
            [
                'name' => 'Industrial Warehouse Facility',
                'description' => 'Large-scale warehouse and distribution center',
                'type' => 'industrial',
                'status' => 'completed',
                'priority' => 'low',
                'client_name' => 'Logistics Solutions Corp',
                'planned_budget' => 6800000.00,
                'actual_budget' => 6950000.00,
                'planned_start_date' => Carbon::now()->subYear(),
                'planned_end_date' => Carbon::now()->subMonths(2),
                'actual_start_date' => Carbon::now()->subYear(),
                'actual_end_date' => Carbon::now()->subMonth(),
                'progress_percentage' => 100
            ]
        ];
        
        $projects = [];
        foreach ($projectsData as $index => $projectData) {
            DB::table('projects')->insert([
                'id' => Str::uuid(),
                'name' => $projectData['name'],
                'description' => $projectData['description'],
                'type' => $projectData['type'],
                'status' => $projectData['status'],
                'priority' => $projectData['priority'],
                'client_company_id' => $companies[array_rand($companies)]->id,
                'contractor_company_id' => $companies[0]->id, // Main company
                'project_manager_id' => $users[1]->id, // Jane Manager
                'client_name' => $projectData['client_name'],
                'client_email' => strtolower(str_replace(' ', '.', $projectData['client_name'])) . '@client.com',
                'client_phone' => '+1-555-0' . (200 + $index),
                'planned_budget' => $projectData['planned_budget'],
                'actual_budget' => $projectData['actual_budget'],
                'planned_start_date' => $projectData['planned_start_date'],
                'planned_end_date' => $projectData['planned_end_date'],
                'actual_start_date' => $projectData['actual_start_date'],
                'actual_end_date' => $projectData['actual_end_date'] ?? null,
                'progress_percentage' => $projectData['progress_percentage'],
                'address' => 'Project Site ' . ($index + 1) . ', Construction District, City',
                'coordinates' => null,
                'created_at' => Carbon::now()->subMonths(7 - $index),
                'updated_at' => now()
            ]);
            
            $projects[] = (object)[
                'id' => DB::getPdo()->lastInsertId(),
                'name' => $projectData['name'],
                'status' => $projectData['status']
            ];
        }
        
        return $projects;
    }
    
    private function createProjectPhases(array $projects): array
    {
        $this->command->info('📋 Creating project phases...');
        
        $phases = [];
        $phaseTemplates = [
            ['name' => 'Pre-Construction', 'order' => 1],
            ['name' => 'Foundation', 'order' => 2],
            ['name' => 'Structure', 'order' => 3],
            ['name' => 'MEP Systems', 'order' => 4],
            ['name' => 'Finishing', 'order' => 5],
            ['name' => 'Final Inspection', 'order' => 6]
        ];
        
        foreach ($projects as $project) {
            foreach ($phaseTemplates as $phase) {
                DB::table('project_phases')->insert([
                    'id' => Str::uuid(),
                    'project_id' => $project->id,
                    'name' => $phase['name'],
                    'description' => "Phase {$phase['order']}: {$phase['name']} work for {$project->name}",
                    'phase_order' => $phase['order'],
                    'status' => $this->getPhaseStatus($phase['order'], $project->status),
                    'planned_start_date' => Carbon::now()->subMonths(6)->addWeeks($phase['order'] * 4),
                    'planned_end_date' => Carbon::now()->subMonths(6)->addWeeks(($phase['order'] + 1) * 4),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $phases[] = (object)[
                    'id' => DB::getPdo()->lastInsertId(),
                    'project_id' => $project->id,
                    'name' => $phase['name'],
                    'order' => $phase['order']
                ];
            }
        }
        
        return $phases;
    }
    
    private function getPhaseStatus(int $order, string $projectStatus): string
    {
        if ($projectStatus === 'completed') return 'completed';
        if ($projectStatus === 'planning') return 'planned';
        
        // Simulate realistic phase progression
        if ($order <= 2) return 'completed';
        if ($order <= 4) return 'in_progress';
        return 'planned';
    }
    
    private function createConstructionTasks(array $projects, array $phases, array $users): void
    {
        $this->command->info('📝 Creating construction tasks...');
        
        $taskTemplates = [
            // Pre-Construction Phase
            [
                'phase' => 'Pre-Construction',
                'tasks' => [
                    ['name' => 'Site Survey and Soil Testing', 'type' => 'planning', 'priority' => 'high', 'hours' => 16, 'status' => 'completed'],
                    ['name' => 'Permit Applications and Approvals', 'type' => 'documentation', 'priority' => 'critical', 'hours' => 24, 'status' => 'completed'],
                    ['name' => 'Final Design Review and Client Approval', 'type' => 'planning', 'priority' => 'high', 'hours' => 12, 'status' => 'completed'],
                    ['name' => 'Subcontractor Selection and Contracts', 'type' => 'documentation', 'priority' => 'high', 'hours' => 20, 'status' => 'completed']
                ]
            ],
            // Foundation Phase
            [
                'phase' => 'Foundation',
                'tasks' => [
                    ['name' => 'Site Preparation and Clearing', 'type' => 'general', 'priority' => 'high', 'hours' => 32, 'status' => 'completed'],
                    ['name' => 'Excavation and Grading', 'type' => 'foundation', 'priority' => 'high', 'hours' => 48, 'status' => 'completed'],
                    ['name' => 'Foundation Formwork Installation', 'type' => 'foundation', 'priority' => 'high', 'hours' => 40, 'status' => 'completed'],
                    ['name' => 'Rebar Installation and Inspection', 'type' => 'foundation', 'priority' => 'critical', 'hours' => 36, 'status' => 'completed'],
                    ['name' => 'Concrete Pour - Foundation Slab', 'type' => 'foundation', 'priority' => 'critical', 'hours' => 24, 'status' => 'in_progress']
                ]
            ],
            // Structure Phase
            [
                'phase' => 'Structure',
                'tasks' => [
                    ['name' => 'Steel Frame Delivery and Staging', 'type' => 'framing', 'priority' => 'medium', 'hours' => 16, 'status' => 'in_progress'],
                    ['name' => 'Steel Column Installation - Floors 1-5', 'type' => 'framing', 'priority' => 'high', 'hours' => 80, 'status' => 'in_progress'],
                    ['name' => 'Beam Installation and Welding', 'type' => 'framing', 'priority' => 'high', 'hours' => 120, 'status' => 'not_started'],
                    ['name' => 'Concrete Deck Pour - Level 1', 'type' => 'foundation', 'priority' => 'high', 'hours' => 32, 'status' => 'not_started'],
                    ['name' => 'Structure Safety Inspection', 'type' => 'inspection', 'priority' => 'critical', 'hours' => 8, 'status' => 'not_started']
                ]
            ],
            // MEP Systems Phase
            [
                'phase' => 'MEP Systems',
                'tasks' => [
                    ['name' => 'Electrical Rough-In Installation', 'type' => 'electrical', 'priority' => 'medium', 'hours' => 60, 'status' => 'not_started'],
                    ['name' => 'Plumbing Rough-In Installation', 'type' => 'plumbing', 'priority' => 'medium', 'hours' => 48, 'status' => 'not_started'],
                    ['name' => 'HVAC Ductwork Installation', 'type' => 'hvac', 'priority' => 'medium', 'hours' => 72, 'status' => 'not_started'],
                    ['name' => 'Fire Safety System Installation', 'type' => 'electrical', 'priority' => 'critical', 'hours' => 40, 'status' => 'not_started'],
                    ['name' => 'MEP Systems Integration Testing', 'type' => 'inspection', 'priority' => 'high', 'hours' => 24, 'status' => 'not_started']
                ]
            ],
            // Finishing Phase  
            [
                'phase' => 'Finishing',
                'tasks' => [
                    ['name' => 'Drywall Installation and Finishing', 'type' => 'drywall', 'priority' => 'medium', 'hours' => 80, 'status' => 'not_started'],
                    ['name' => 'Flooring Installation - Common Areas', 'type' => 'flooring', 'priority' => 'medium', 'hours' => 48, 'status' => 'not_started'],
                    ['name' => 'Interior Painting and Touch-ups', 'type' => 'painting', 'priority' => 'low', 'hours' => 60, 'status' => 'not_started'],
                    ['name' => 'Fixture and Hardware Installation', 'type' => 'finishing', 'priority' => 'medium', 'hours' => 32, 'status' => 'not_started'],
                    ['name' => 'Exterior Landscaping and Hardscaping', 'type' => 'finishing', 'priority' => 'low', 'hours' => 40, 'status' => 'not_started']
                ]
            ],
            // Final Inspection Phase
            [
                'phase' => 'Final Inspection',
                'tasks' => [
                    ['name' => 'Building Code Compliance Inspection', 'type' => 'inspection', 'priority' => 'critical', 'hours' => 12, 'status' => 'not_started'],
                    ['name' => 'Fire Safety and Emergency Systems Test', 'type' => 'inspection', 'priority' => 'critical', 'hours' => 8, 'status' => 'not_started'],
                    ['name' => 'Client Walkthrough and Punch List', 'type' => 'inspection', 'priority' => 'high', 'hours' => 16, 'status' => 'not_started'],
                    ['name' => 'Final Documentation and Handover', 'type' => 'documentation', 'priority' => 'high', 'hours' => 20, 'status' => 'not_started']
                ]
            ]
        ];
        
        foreach (array_slice($projects, 0, 3) as $project) { // Limit to first 3 projects for demo
            $projectObj = (object)$project;
            foreach ($taskTemplates as $phaseTemplate) {
                $phase = null;
                foreach ($phases as $p) {
                    $phaseObj = (object)$p;
                    if ($phaseObj->project_id == $projectObj->id && 
                        (stripos($phaseObj->name, $phaseTemplate['phase']) !== false || 
                         stripos($phaseTemplate['phase'], $phaseObj->name) !== false)) {
                        $phase = $phaseObj;
                        break;
                    }
                }
                
                if (!$phase) {
                    // Try to find any phase from this project
                    foreach ($phases as $p) {
                        $phaseObj = (object)$p;
                        if ($phaseObj->project_id == $projectObj->id) {
                            $phase = $phaseObj;
                            break;
                        }
                    }
                    if (!$phase) continue;
                }
                
                foreach ($phaseTemplate['tasks'] as $index => $taskData) {
                    $assignedUser = $this->getAssignedUser($users, $taskData['type']);
                    $status = TaskStatus::from($taskData['status']);
                    $priority = TaskPriority::from($taskData['priority']);
                    $type = TaskType::from($taskData['type']);
                    
                    $dueDate = $this->calculateDueDate($taskData['status']);
                    $startDate = $this->calculateStartDate($taskData['status']);
                    
                    Task::create([
                        'name' => $taskData['name'],
                        'description' => $this->generateTaskDescription($taskData['name'], $projectObj->name),
                        'status' => $status,
                        'priority' => $priority,
                        'task_type' => $type,
                        'project_id' => $projectObj->id,
                        'phase_id' => $phase->id,
                        'assigned_to_id' => $assignedUser['id'],
                        'created_by_id' => $users[1]['id'], // Jane Manager or first available
                        'estimated_hours' => $taskData['hours'],
                        'actual_hours' => $this->calculateActualHours($taskData['status'], $taskData['hours']),
                        'progress_percentage' => $this->calculateProgress($taskData['status']),
                        'start_date' => $startDate,
                        'due_date' => $dueDate,
                        'completed_at' => $taskData['status'] === 'completed' ? Carbon::now()->subDays(rand(1, 30)) : null,
                        'task_order' => $index + 1,
                        'metadata' => [
                            'phase_name' => $phaseTemplate['phase'],
                            'project_type' => $projectObj->name,
                            'estimated_duration_days' => ceil($taskData['hours'] / 8)
                        ]
                    ]);
                }
            }
        }
        
        // Create some task dependencies to show relationships
        $this->createTaskDependencies();
        
        // Create some task comments
        $this->createTaskComments($users);
    }
    
    private function getAssignedUser(array $users, string $taskType): object
    {
        $assignments = [
            'general' => [0, 2, 6], // Field workers and foreman
            'foundation' => [0, 2, 6], // Field workers and foreman
            'framing' => [0, 2, 6], // Field workers and foreman
            'electrical' => [4], // Electrical specialist
            'plumbing' => [5], // Plumbing specialist
            'hvac' => [5], // HVAC specialist
            'drywall' => [0, 2], // Field workers
            'flooring' => [0, 2], // Field workers
            'painting' => [0, 2], // Field workers
            'finishing' => [0, 2], // Field workers
            'inspection' => [3, 6], // Supervisors
            'documentation' => [1, 7], // Project manager and architect
            'planning' => [1, 7], // Project manager and architect
            'cleanup' => [0, 2] // Field workers
        ];
        
        $possibleUsers = $assignments[$taskType] ?? [0, 1, 2];
        $userIndex = $possibleUsers[array_rand($possibleUsers)];
        
        return $users[$userIndex];
    }
    
    private function calculateDueDate(string $status): Carbon
    {
        $baseDate = Carbon::now();
        
        switch ($status) {
            case 'completed':
                return $baseDate->subDays(rand(5, 30));
            case 'in_progress':
                return $baseDate->addDays(rand(3, 14));
            case 'not_started':
                return $baseDate->addDays(rand(7, 45));
            default:
                return $baseDate->addDays(rand(1, 60));
        }
    }
    
    private function calculateStartDate(string $status): Carbon
    {
        $baseDate = Carbon::now();
        
        switch ($status) {
            case 'completed':
                return $baseDate->subDays(rand(30, 60));
            case 'in_progress':
                return $baseDate->subDays(rand(1, 10));
            case 'not_started':
                return $baseDate->addDays(rand(1, 30));
            default:
                return $baseDate->addDays(rand(1, 15));
        }
    }
    
    private function calculateActualHours(string $status, int $estimatedHours): int
    {
        switch ($status) {
            case 'completed':
                // Add some variance to actual hours (85-115% of estimated)
                return (int) ($estimatedHours * (0.85 + (rand(0, 30) / 100)));
            case 'in_progress':
                // Some progress made
                return (int) ($estimatedHours * (0.2 + (rand(0, 40) / 100)));
            default:
                return 0;
        }
    }
    
    private function calculateProgress(string $status): int
    {
        switch ($status) {
            case 'completed':
                return 100;
            case 'in_progress':
                return rand(25, 85);
            case 'review':
                return rand(90, 98);
            case 'on_hold':
                return rand(10, 60);
            default:
                return 0;
        }
    }
    
    private function generateTaskDescription(string $taskName, string $projectName): string
    {
        $descriptions = [
            'Site Survey' => "Comprehensive site survey including topographical mapping, utilities location, and environmental assessment for {$projectName}. Coordinate with city planning department and obtain necessary access permits.",
            'Excavation' => "Mass excavation work including site preparation, soil removal, and grading. Ensure proper safety protocols and coordinate with utility companies for any relocations required.",
            'Foundation' => "Critical foundation work requiring precise measurements and quality concrete pour. Weather conditions must be monitored and concrete curing time must be respected.",
            'Steel' => "Structural steel installation requiring certified welders and crane operations. All connections must be inspected and documented according to structural engineering specifications.",
            'Electrical' => "Electrical system installation including power distribution, lighting circuits, and safety systems. All work must comply with local electrical codes and be inspected by certified electricians.",
            'Plumbing' => "Complete plumbing system installation including water supply, drainage, and fixture connections. Pressure testing required before final approval.",
            'HVAC' => "Heating, ventilation, and air conditioning system installation. Energy efficiency requirements must be met and systems tested for proper operation.",
            'Inspection' => "Mandatory safety and code compliance inspection. All previous work must be completed and documented before inspection can proceed.",
            'Finishing' => "Interior finishing work including flooring, painting, and fixture installation. Quality standards must be maintained throughout the process.",
            'Documentation' => "Complete project documentation including as-built drawings, warranty information, and maintenance schedules. Client training on building systems may be required."
        ];
        
        // Find the best matching description
        foreach ($descriptions as $key => $description) {
            if (stripos($taskName, $key) !== false) {
                return $description;
            }
        }
        
        return "Task: {$taskName} for {$projectName}. This task requires careful coordination with other trades and adherence to project timeline and quality standards.";
    }
    
    private function createTaskDependencies(): void
    {
        $this->command->info('🔗 Creating task dependencies...');
        
        // Get some tasks to create realistic dependencies
        $tasks = Task::with('project', 'phase')->get();
        
        $dependencyCount = 0;
        foreach ($tasks as $task) {
            // Create dependencies for tasks that logically depend on others
            if (stripos($task->name, 'Steel') !== false) {
                $foundationTask = $tasks->where('project_id', $task->project_id)
                                      ->where('name', 'like', '%Foundation%')
                                      ->first();
                if ($foundationTask) {
                    DB::table('task_dependencies')->insert([
                        'id' => Str::uuid(),
                        'task_id' => $task->id,
                        'depends_on_task_id' => $foundationTask->id,
                        'dependency_type' => 'finish_to_start',
                        'lag_days' => 3,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $dependencyCount++;
                }
            }
            
            if (stripos($task->name, 'Inspection') !== false) {
                $constructionTask = $tasks->where('project_id', $task->project_id)
                                         ->where('task_type', 'general')
                                         ->where('phase_id', $task->phase_id)
                                         ->first();
                if ($constructionTask) {
                    DB::table('task_dependencies')->insert([
                        'id' => Str::uuid(),
                        'task_id' => $task->id,
                        'depends_on_task_id' => $constructionTask->id,
                        'dependency_type' => 'finish_to_start',
                        'lag_days' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $dependencyCount++;
                }
            }
        }
        
        $this->command->info("✅ Created {$dependencyCount} task dependencies");
    }
    
    private function createTaskComments(array $users): void
    {
        $this->command->info('💬 Creating task comments...');
        
        $tasks = Task::limit(15)->get(); // Add comments to first 15 tasks
        
        $commentTemplates = [
            "Work progressing well. Weather conditions are favorable for construction.",
            "Minor delay due to material delivery. Expected to resume tomorrow morning.",
            "Quality inspection passed. Moving to next phase as scheduled.",
            "Coordinating with electrical team for utility connections.",
            "Safety protocols reviewed with all team members before starting.",
            "Client requested minor modifications to original specifications.",
            "Waiting for building permit approval before proceeding.",
            "Equipment malfunction caused 2-hour delay. Issue resolved.",
            "Excellent work quality demonstrated by the construction team.",
            "Need to schedule follow-up inspection for final approval."
        ];
        
        $commentCount = 0;
        foreach ($tasks as $task) {
            $numComments = rand(1, 4);
            
            for ($i = 0; $i < $numComments; $i++) {
                $user = $users[array_rand($users)];
                $comment = $commentTemplates[array_rand($commentTemplates)];
                
                DB::table('task_comments')->insert([
                    'id' => Str::uuid(),
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'comment' => $comment,
                    'is_internal' => rand(0, 1) === 1,
                    'created_at' => Carbon::now()->subDays(rand(1, 10)),
                    'updated_at' => now()
                ]);
                $commentCount++;
            }
        }
        
        $this->command->info("✅ Created {$commentCount} task comments");
    }
    
    private function showSummary(): void
    {
        $userCount = User::count();
        $projectCount = DB::table('projects')->count();
        $phaseCount = DB::table('project_phases')->count();
        $taskCount = Task::count();
        $dependencyCount = DB::table('task_dependencies')->count();
        $commentCount = DB::table('task_comments')->count();
        
        $this->command->info("\n📊 === TASK MANAGEMENT SEEDER SUMMARY ===");
        $this->command->info("👥 Users: {$userCount}");
        $this->command->info("🏗️ Projects: {$projectCount}");
        $this->command->info("📋 Project Phases: {$phaseCount}");
        $this->command->info("📝 Tasks: {$taskCount}");
        $this->command->info("🔗 Dependencies: {$dependencyCount}");
        $this->command->info("💬 Comments: {$commentCount}");
        $this->command->info("=====================================\n");
        
        $this->command->info("🌐 You can now access the Task Management interface at:");
        $this->command->info("Frontend: http://localhost:3074 (Tasks Page)");
        $this->command->info("Backend API: http://localhost:3071/api/tasks");
        
        $this->command->info("\n🔑 Demo Users (password: 'password'):");
        $this->command->info("• jane.manager@construction.com - Project Manager");
        $this->command->info("• john.smith@construction.com - Field Worker");
        $this->command->info("• sarah.inspector@construction.com - Supervisor");
    }
}