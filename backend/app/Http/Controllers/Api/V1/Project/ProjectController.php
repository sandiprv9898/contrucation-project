<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Domain\Project\DTOs\CreateProjectDTO;
use App\Domain\Project\DTOs\UpdateProjectDTO;
use App\Domain\Project\Enums\ProjectStatus;
use App\Domain\Project\Services\ProjectService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Resources\Project\ProjectListResource;
use App\Http\Resources\Project\ProjectResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $projectService
    ) {}

    /**
     * Display a listing of projects with filtering and pagination.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min($request->integer('per_page', 50), 100);
        $sortBy = $request->string('sort_by', 'created_at');
        $sortDirection = $request->string('sort_direction', 'desc');
        
        $filters = $request->only([
            'status',
            'priority', 
            'project_type',
            'client_company_id',
            'project_manager_id',
            'start_date_from',
            'start_date_to',
            'end_date_from', 
            'end_date_to',
            'budget_min',
            'budget_max',
            'progress_min',
            'progress_max',
            'search',
            'overdue'
        ]);

        // Use a temporary repository access through the service
        $projectRepository = app(\App\Domain\Project\Repositories\Contracts\ProjectRepositoryInterface::class);
        $projects = $projectRepository->paginate(
            perPage: $perPage,
            filters: $filters,
            sortBy: $sortBy,
            sortDirection: $sortDirection,
            with: ['client', 'manager']
        );

        return ProjectListResource::collection($projects);
    }

    /**
     * Store a newly created project.
     */
    public function store(CreateProjectRequest $request): JsonResponse
    {
        try {
            $projectDTO = CreateProjectDTO::fromArray($request->validated());
            $project = $this->projectService->createProject($projectDTO);

            return response()->json([
                'message' => 'Project created successfully',
                'data' => new ProjectResource($project)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create project',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified project.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $project = $this->projectService->getProject($id);
            
            return response()->json([
                'data' => new ProjectResource($project)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Project not found'
            ], 404);
        }
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
                'client_company_id' => ['sometimes', 'uuid', 'exists:companies,id'],
                'project_manager_id' => ['sometimes', 'uuid', 'exists:users,id'],
                'project_type' => ['sometimes', 'string'],
                'priority' => ['sometimes', 'string'],
                'start_date' => ['sometimes', 'nullable', 'date'],
                'end_date' => ['sometimes', 'nullable', 'date', 'after:start_date'],
                'planned_budget' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                'actual_budget' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                'progress_percentage' => ['sometimes', 'integer', 'between:0,100'],
                'address' => ['sometimes', 'nullable', 'string', 'max:1000'],
                'coordinates' => ['sometimes', 'nullable', 'array'],
                'metadata' => ['sometimes', 'nullable', 'array'],
            ]);
            
            $updateDTO = UpdateProjectDTO::fromArray($validatedData);
            $project = $this->projectService->updateProject($id, $updateDTO);

            return response()->json([
                'message' => 'Project updated successfully',
                'data' => new ProjectResource($project)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Project not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update project',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified project.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->projectService->deleteProject($id);

            return response()->json([
                'message' => 'Project deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Project not found'
            ], 404);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete project',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update project status.
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:draft,active,on_hold,completed,cancelled']
        ]);

        try {
            $status = ProjectStatus::from($request->status);
            $project = $this->projectService->updateProjectStatus($id, $status);

            return response()->json([
                'message' => 'Project status updated successfully',
                'data' => new ProjectResource($project)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Project not found'
            ], 404);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update project status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get projects by company.
     */
    public function byCompany(string $companyId): AnonymousResourceCollection
    {
        $projects = $this->projectService->getProjectsByCompany($companyId);
        return ProjectListResource::collection($projects);
    }

    /**
     * Get projects by manager.
     */
    public function byManager(string $managerId): AnonymousResourceCollection
    {
        $projects = $this->projectService->getProjectsByManager($managerId);
        return ProjectListResource::collection($projects);
    }

    /**
     * Search projects.
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'query' => ['required', 'string', 'min:2']
        ]);

        $projects = $this->projectService->searchProjects($request->input('query'));
        return ProjectListResource::collection($projects);
    }

    /**
     * Get overdue projects.
     */
    public function overdue(): AnonymousResourceCollection
    {
        $projects = $this->projectService->getOverdueProjects();
        return ProjectListResource::collection($projects);
    }

    /**
     * Get project statistics.
     */
    public function statistics(): JsonResponse
    {
        $statistics = $this->projectService->getProjectStatistics();
        
        return response()->json([
            'data' => $statistics
        ]);
    }

    /**
     * Update project progress.
     */
    public function updateProgress(string $id): JsonResponse
    {
        try {
            $project = $this->projectService->updateProjectProgress($id);
            
            return response()->json([
                'message' => 'Project progress updated successfully',
                'data' => [
                    'progress_percentage' => $project->progress_percentage
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Project not found'
            ], 404);
        }
    }
}