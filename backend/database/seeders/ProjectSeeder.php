<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectPhase;
use App\Domain\Project\Models\ProjectMilestone;
use App\Domain\Project\Enums\ProjectStatus;
use App\Domain\Project\Enums\ProjectPriority;
use App\Domain\Project\Enums\ProjectType;
use App\Domain\User\Models\Company;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Creating Project Management demo data...');
        
        // Get existing users and companies or create basic ones
        $users = $this->ensureUsers();
        $companies = $this->ensureCompanies();
        
        // Create projects with realistic construction scenarios
        $projects = $this->createProjects($companies, $users);
        $this->createProjectPhases($projects);
        $this->createProjectMilestones($projects);
        
        $this->command->info('✅ Project Management demo data created successfully!');
        $this->showSummary();
    }
    
    private function ensureUsers(): array
    {
        // Get existing users or create basic ones
        $users = User::all();
        
        if ($users->count() < 3) {
            $this->command->info('👥 Creating basic users for projects...');
            
            $basicUsers = [
                [
                    'name' => 'Project Manager',
                    'email' => 'pm@construction.com',
                    'role' => 'project_manager'
                ],
                [
                    'name' => 'Site Supervisor', 
                    'email' => 'supervisor@construction.com',
                    'role' => 'supervisor'
                ],
                [
                    'name' => 'Client Representative',
                    'email' => 'client@construction.com',
                    'role' => 'client'
                ]
            ];
            
            foreach ($basicUsers as $userData) {
                User::firstOrCreate(
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
            }
            
            $users = User::all();
        }
        
        return $users->toArray();
    }
    
    private function ensureCompanies(): array
    {
        // Get existing companies or create basic ones
        $companies = Company::all();
        
        if ($companies->count() < 2) {
            $this->command->info('🏢 Creating basic companies for projects...');
            
            $basicCompanies = [
                [
                    'name' => 'Premier Construction Ltd',
                    'type' => 'general_contractor',
                    'description' => 'Full-service construction company'
                ],
                [
                    'name' => 'Metro Property Group',
                    'type' => 'client',
                    'description' => 'Real estate development company'
                ]
            ];
            
            foreach ($basicCompanies as $companyData) {
                Company::firstOrCreate(
                    ['name' => $companyData['name']],
                    [
                        'id' => Str::uuid(),
                        'name' => $companyData['name'],
                        'type' => $companyData['type'],
                        'description' => $companyData['description'],
                        'address' => '123 Business Ave, Construction City',
                        'phone' => '+1-555-0100',
                        'email' => strtolower(str_replace(' ', '', $companyData['name'])) . '@example.com',
                        'website' => 'https://' . strtolower(str_replace(' ', '', $companyData['name'])) . '.com',
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
            
            $companies = Company::all();
        }
        
        return $companies->toArray();
    }
    
    private function createProjects(array $companies, array $users): array
    {
        $this->command->info('🏗️ Creating construction projects...');
        
        $projectsData = [
            [
                'name' => 'Downtown Office Complex',
                'description' => 'Modern 15-story office building with underground parking, retail space on ground floor, and rooftop garden. LEED Gold certification target.',
                'type' => ProjectType::COMMERCIAL,
                'status' => ProjectStatus::ACTIVE,
                'priority' => ProjectPriority::HIGH,
                'client_name' => 'Metro Business Solutions Inc.',
                'client_contact' => 'Sarah Johnson, Development Director',
                'planned_budget' => 15000000.00,
                'actual_budget' => 8500000.00,
                'planned_start_date' => Carbon::now()->subMonths(6),
                'planned_end_date' => Carbon::now()->addMonths(18),
                'actual_start_date' => Carbon::now()->subMonths(6),
                'progress_percentage' => 35,
                'location' => '450 Downtown Plaza, Metro City, MC 12345',
                'square_footage' => 450000
            ],
            [
                'name' => 'Riverside Residential Complex',
                'description' => '120-unit luxury apartment complex with amenities including fitness center, pool, and community gardens. Energy-efficient design with solar panels.',
                'type' => ProjectType::RESIDENTIAL,
                'status' => ProjectStatus::ACTIVE,
                'priority' => ProjectPriority::MEDIUM,
                'client_name' => 'Riverside Properties LLC',
                'client_contact' => 'Michael Chen, Project Director',
                'planned_budget' => 8500000.00,
                'actual_budget' => 4200000.00,
                'planned_start_date' => Carbon::now()->subMonths(3),
                'planned_end_date' => Carbon::now()->addMonths(12),
                'actual_start_date' => Carbon::now()->subMonths(3),
                'progress_percentage' => 55,
                'location' => '789 Riverside Drive, Metro City, MC 12346',
                'square_footage' => 180000
            ],
            [
                'name' => 'Shopping Center Renovation',
                'description' => 'Complete renovation of existing 50-year-old shopping center including modernization of HVAC, electrical, and structural upgrades.',
                'type' => ProjectType::RENOVATION,
                'status' => ProjectStatus::PLANNING,
                'priority' => ProjectPriority::MEDIUM,
                'client_name' => 'Retail Spaces Development Corp',
                'client_contact' => 'Lisa Rodriguez, Facilities Manager',
                'planned_budget' => 3200000.00,
                'actual_budget' => 250000.00,
                'planned_start_date' => Carbon::now()->addMonth(),
                'planned_end_date' => Carbon::now()->addMonths(8),
                'progress_percentage' => 10,
                'location' => '123 Shopping Boulevard, Metro City, MC 12347',
                'square_footage' => 85000
            ],
            [
                'name' => 'Industrial Warehouse Facility',
                'description' => 'Large-scale warehouse and distribution center with automated sorting systems, dock doors, and office space. Green building features.',
                'type' => ProjectType::INDUSTRIAL,
                'status' => ProjectStatus::COMPLETED,
                'priority' => ProjectPriority::LOW,
                'client_name' => 'Logistics Solutions International',
                'client_contact' => 'Robert Thompson, Operations VP',
                'planned_budget' => 6800000.00,
                'actual_budget' => 6950000.00,
                'planned_start_date' => Carbon::now()->subYear(),
                'planned_end_date' => Carbon::now()->subMonths(2),
                'actual_start_date' => Carbon::now()->subYear(),
                'actual_end_date' => Carbon::now()->subMonth(),
                'progress_percentage' => 100,
                'location' => '500 Industrial Way, Metro City, MC 12348',
                'square_footage' => 250000
            ],
            [
                'name' => 'Medical Center Expansion',
                'description' => '3-story addition to existing medical facility with specialized surgical suites, imaging center, and patient recovery areas.',
                'type' => ProjectType::HEALTHCARE,
                'status' => ProjectStatus::ACTIVE,
                'priority' => ProjectPriority::CRITICAL,
                'client_name' => 'Metro General Hospital',
                'client_contact' => 'Dr. Amanda Wilson, Chief Administrator',
                'planned_budget' => 12000000.00,
                'actual_budget' => 2800000.00,
                'planned_start_date' => Carbon::now()->subMonths(2),
                'planned_end_date' => Carbon::now()->addMonths(16),
                'actual_start_date' => Carbon::now()->subMonths(2),
                'progress_percentage' => 20,
                'location' => '200 Health Drive, Metro City, MC 12349',
                'square_footage' => 75000
            ],
            [
                'name' => 'University Student Housing',
                'description' => 'Modern 6-building student housing complex with 400 beds, study lounges, dining facilities, and recreational areas.',
                'type' => ProjectType::INSTITUTIONAL,
                'status' => ProjectStatus::PLANNING,
                'priority' => ProjectPriority::HIGH,
                'client_name' => 'Metro State University',
                'client_contact' => 'Prof. James Martinez, Facilities Planning',
                'planned_budget' => 18000000.00,
                'actual_budget' => 500000.00,
                'planned_start_date' => Carbon::now()->addMonths(2),
                'planned_end_date' => Carbon::now()->addMonths(20),
                'progress_percentage' => 5,
                'location' => '100 University Circle, Metro City, MC 12350',
                'square_footage' => 320000
            ]
        ];
        
        $projects = [];
        foreach ($projectsData as $index => $projectData) {
            $clientCompany = $companies[array_rand($companies)];
            $contractorCompany = $companies[0]; // First company as main contractor
            $projectManager = collect($users)->firstWhere('role', 'project_manager') ?? $users[0];
            
            $project = Project::create([
                'name' => $projectData['name'],
                'description' => $projectData['description'],
                'type' => $projectData['type'],
                'status' => $projectData['status'],
                'priority' => $projectData['priority'],
                'client_company_id' => $clientCompany['id'],
                'contractor_company_id' => $contractorCompany['id'],
                'project_manager_id' => $projectManager['id'],
                'client_name' => $projectData['client_name'],
                'client_email' => strtolower(str_replace([' ', '.'], ['', ''], explode(',', $projectData['client_contact'])[0])) . '@client.com',
                'client_phone' => '+1-555-0' . (200 + $index),
                'planned_budget' => $projectData['planned_budget'],
                'actual_budget' => $projectData['actual_budget'],
                'planned_start_date' => $projectData['planned_start_date'],
                'planned_end_date' => $projectData['planned_end_date'],
                'actual_start_date' => $projectData['actual_start_date'],
                'actual_end_date' => $projectData['actual_end_date'] ?? null,
                'progress_percentage' => $projectData['progress_percentage'],
                'address' => $projectData['location'],
                'coordinates' => null,
                'metadata' => [
                    'square_footage' => $projectData['square_footage'],
                    'client_contact' => $projectData['client_contact'],
                    'construction_type' => $this->getConstructionType($projectData['type']),
                    'permits_required' => $this->getRequiredPermits($projectData['type']),
                    'sustainability_target' => $this->getSustainabilityTarget($projectData['type']),
                ],
                'created_at' => Carbon::now()->subMonths(7 - $index),
                'updated_at' => now()
            ]);
            
            $projects[] = $project;
        }
        
        return $projects;
    }
    
    private function createProjectPhases(array $projects): void
    {
        $this->command->info('📋 Creating project phases...');
        
        $phaseTemplates = [
            [
                'name' => 'Pre-Construction',
                'description' => 'Planning, permits, and preparation',
                'order' => 1,
                'estimated_duration_days' => 45
            ],
            [
                'name' => 'Foundation & Earthwork',
                'description' => 'Site preparation, excavation, and foundation work',
                'order' => 2,
                'estimated_duration_days' => 60
            ],
            [
                'name' => 'Structure & Framing',
                'description' => 'Structural elements, framing, and roofing',
                'order' => 3,
                'estimated_duration_days' => 90
            ],
            [
                'name' => 'MEP Systems',
                'description' => 'Mechanical, Electrical, and Plumbing systems',
                'order' => 4,
                'estimated_duration_days' => 75
            ],
            [
                'name' => 'Interior Finishing',
                'description' => 'Drywall, flooring, painting, and fixtures',
                'order' => 5,
                'estimated_duration_days' => 80
            ],
            [
                'name' => 'Final Inspection & Handover',
                'description' => 'Final inspections, testing, and project handover',
                'order' => 6,
                'estimated_duration_days' => 30
            ]
        ];
        
        foreach ($projects as $project) {
            foreach ($phaseTemplates as $phase) {
                $startDate = $project->planned_start_date
                    ? Carbon::parse($project->planned_start_date)->addDays(($phase['order'] - 1) * 30)
                    : Carbon::now()->addDays(($phase['order'] - 1) * 30);
                
                $endDate = $startDate->copy()->addDays($phase['estimated_duration_days']);
                
                ProjectPhase::create([
                    'project_id' => $project->id,
                    'name' => $phase['name'],
                    'description' => $phase['description'],
                    'phase_order' => $phase['order'],
                    'status' => $this->getPhaseStatus($phase['order'], $project->status),
                    'planned_start_date' => $startDate,
                    'planned_end_date' => $endDate,
                    'actual_start_date' => $this->shouldHaveStarted($phase['order'], $project->status) ? $startDate : null,
                    'actual_end_date' => $this->shouldHaveCompleted($phase['order'], $project->status) ? $endDate : null,
                    'estimated_budget' => $this->calculatePhaseEstimatedBudget($project->planned_budget, $phase['order']),
                    'actual_budget' => $this->calculatePhaseActualBudget($project->actual_budget, $phase['order'], $project->status),
                    'progress_percentage' => $this->calculatePhaseProgress($phase['order'], $project->status, $project->progress_percentage),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
    
    private function createProjectMilestones(array $projects): void
    {
        $this->command->info('🎯 Creating project milestones...');
        
        foreach ($projects as $project) {
            $milestones = $this->getMilestonesForProject($project);
            
            foreach ($milestones as $index => $milestoneData) {
                ProjectMilestone::create([
                    'project_id' => $project->id,
                    'name' => $milestoneData['name'],
                    'description' => $milestoneData['description'],
                    'milestone_type' => $milestoneData['type'],
                    'target_date' => $milestoneData['target_date'],
                    'actual_date' => $milestoneData['actual_date'] ?? null,
                    'status' => $milestoneData['status'],
                    'milestone_order' => $index + 1,
                    'is_critical' => $milestoneData['is_critical'],
                    'metadata' => $milestoneData['metadata'] ?? [],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
    
    private function getConstructionType(ProjectType $type): string
    {
        return match($type) {
            ProjectType::COMMERCIAL => 'Type II Commercial Construction',
            ProjectType::RESIDENTIAL => 'Type V Residential Construction',
            ProjectType::INDUSTRIAL => 'Type I Industrial Construction',
            ProjectType::HEALTHCARE => 'Type I Healthcare Construction',
            ProjectType::INSTITUTIONAL => 'Type II Institutional Construction',
            default => 'General Construction'
        };
    }
    
    private function getRequiredPermits(ProjectType $type): array
    {
        $basePermits = ['Building Permit', 'Electrical Permit', 'Plumbing Permit'];
        
        return match($type) {
            ProjectType::COMMERCIAL => array_merge($basePermits, ['Fire Department Approval', 'ADA Compliance']),
            ProjectType::RESIDENTIAL => array_merge($basePermits, ['Zoning Approval', 'Environmental Impact']),
            ProjectType::INDUSTRIAL => array_merge($basePermits, ['Environmental Permit', 'Industrial Safety Approval']),
            ProjectType::HEALTHCARE => array_merge($basePermits, ['Health Department Approval', 'Medical Gas Permit']),
            ProjectType::INSTITUTIONAL => array_merge($basePermits, ['Fire Safety Approval', 'Accessibility Compliance']),
            default => $basePermits
        };
    }
    
    private function getSustainabilityTarget(ProjectType $type): string
    {
        return match($type) {
            ProjectType::COMMERCIAL => 'LEED Gold Certification',
            ProjectType::RESIDENTIAL => 'Energy Star Certification',
            ProjectType::INDUSTRIAL => 'Green Manufacturing Certification',
            ProjectType::HEALTHCARE => 'Green Hospital Certification',
            ProjectType::INSTITUTIONAL => 'LEED Silver Certification',
            default => 'Basic Energy Efficiency'
        };
    }
    
    private function getPhaseStatus(int $order, ProjectStatus $projectStatus): string
    {
        if ($projectStatus === ProjectStatus::COMPLETED) return 'completed';
        if ($projectStatus === ProjectStatus::PLANNING) return 'planned';
        if ($projectStatus === ProjectStatus::ON_HOLD) return 'on_hold';
        if ($projectStatus === ProjectStatus::CANCELLED) return 'cancelled';
        
        // Active project phase progression
        return match(true) {
            $order <= 2 => 'completed',
            $order <= 4 => 'in_progress',
            default => 'planned'
        };
    }
    
    private function shouldHaveStarted(int $order, ProjectStatus $projectStatus): bool
    {
        if ($projectStatus === ProjectStatus::PLANNING) return false;
        return $order <= 3; // First 3 phases should have started in active projects
    }
    
    private function shouldHaveCompleted(int $order, ProjectStatus $projectStatus): bool
    {
        if ($projectStatus === ProjectStatus::COMPLETED) return true;
        return $order <= 1; // Only first phase completed in active projects
    }
    
    private function calculatePhaseEstimatedBudget(float $totalBudget, int $order): float
    {
        $percentages = [1 => 0.15, 2 => 0.25, 3 => 0.30, 4 => 0.20, 5 => 0.08, 6 => 0.02];
        return $totalBudget * ($percentages[$order] ?? 0.1);
    }
    
    private function calculatePhaseActualBudget(float $totalActual, int $order, ProjectStatus $status): float
    {
        if ($status === ProjectStatus::PLANNING) return 0;
        
        $spentPercentages = [1 => 0.20, 2 => 0.35, 3 => 0.25, 4 => 0.15, 5 => 0.05, 6 => 0.00];
        return $totalActual * ($spentPercentages[$order] ?? 0.05);
    }
    
    private function calculatePhaseProgress(int $order, ProjectStatus $status, int $projectProgress): int
    {
        if ($status === ProjectStatus::COMPLETED) return 100;
        if ($status === ProjectStatus::PLANNING) return 0;
        
        return match(true) {
            $order <= 2 => 100,
            $order === 3 => min(85, $projectProgress + 10),
            $order === 4 => max(0, $projectProgress - 20),
            default => 0
        };
    }
    
    private function getMilestonesForProject(Project $project): array
    {
        $baseDate = Carbon::parse($project->planned_start_date ?? now());
        $endDate = Carbon::parse($project->planned_end_date ?? now()->addYear());
        $isCompleted = $project->status === ProjectStatus::COMPLETED;
        $isActive = $project->status === ProjectStatus::ACTIVE;
        
        return [
            [
                'name' => 'Permits Approved',
                'description' => 'All required construction permits obtained and approved',
                'type' => 'regulatory',
                'target_date' => $baseDate->copy()->addDays(30),
                'actual_date' => $isActive || $isCompleted ? $baseDate->copy()->addDays(25) : null,
                'status' => $isActive || $isCompleted ? 'completed' : 'pending',
                'is_critical' => true,
                'metadata' => ['permit_count' => count($this->getRequiredPermits($project->type))]
            ],
            [
                'name' => 'Foundation Complete',
                'description' => 'Foundation and earthwork phase completion',
                'type' => 'construction',
                'target_date' => $baseDate->copy()->addDays(90),
                'actual_date' => $isCompleted ? $baseDate->copy()->addDays(95) : null,
                'status' => $isCompleted ? 'completed' : ($isActive ? 'in_progress' : 'pending'),
                'is_critical' => true
            ],
            [
                'name' => 'Structure Topped Out',
                'description' => 'Main structural elements completed',
                'type' => 'construction',
                'target_date' => $baseDate->copy()->addDays(180),
                'actual_date' => $isCompleted ? $baseDate->copy()->addDays(175) : null,
                'status' => $isCompleted ? 'completed' : 'pending',
                'is_critical' => true
            ],
            [
                'name' => 'MEP Rough-In Complete',
                'description' => 'Mechanical, Electrical, and Plumbing rough-in finished',
                'type' => 'systems',
                'target_date' => $baseDate->copy()->addDays(240),
                'actual_date' => $isCompleted ? $baseDate->copy()->addDays(245) : null,
                'status' => $isCompleted ? 'completed' : 'pending',
                'is_critical' => false
            ],
            [
                'name' => 'Certificate of Occupancy',
                'description' => 'Final inspections passed and CO issued',
                'type' => 'regulatory',
                'target_date' => $endDate->copy()->subDays(15),
                'actual_date' => $isCompleted ? $endDate->copy()->subDays(10) : null,
                'status' => $isCompleted ? 'completed' : 'pending',
                'is_critical' => true
            ],
            [
                'name' => 'Project Handover',
                'description' => 'Final project delivery to client',
                'type' => 'delivery',
                'target_date' => $endDate,
                'actual_date' => $isCompleted ? $endDate->copy()->addDays(3) : null,
                'status' => $isCompleted ? 'completed' : 'pending',
                'is_critical' => true
            ]
        ];
    }
    
    private function showSummary(): void
    {
        $projectCount = Project::count();
        $phaseCount = ProjectPhase::count();
        $milestoneCount = ProjectMilestone::count();
        $userCount = User::count();
        $companyCount = Company::count();
        
        $this->command->info("\n📊 === PROJECT MANAGEMENT SEEDER SUMMARY ===");
        $this->command->info("👥 Users: {$userCount}");
        $this->command->info("🏢 Companies: {$companyCount}");
        $this->command->info("🏗️ Projects: {$projectCount}");
        $this->command->info("📋 Project Phases: {$phaseCount}");
        $this->command->info("🎯 Milestones: {$milestoneCount}");
        $this->command->info("==========================================\n");
        
        $this->command->info("🌐 You can now access the Project Management interface at:");
        $this->command->info("Frontend: http://localhost:3074 (Projects Page)");
        $this->command->info("Backend API: http://localhost:3071/api/projects");
        
        $this->command->info("\n📋 Sample Projects Created:");
        Project::take(3)->get()->each(function ($project) {
            $this->command->info("• {$project->name} - {$project->status->value} - {$project->progress_percentage}%");
        });
    }
}