<?php

namespace App\Domain\Project\DTOs;

use App\Domain\Project\Enums\ProjectPriority;
use App\Domain\Project\Enums\ProjectStatus;
use App\Domain\Project\Enums\ProjectType;
use Carbon\Carbon;

class CreateProjectDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $clientCompanyId,
        public readonly string $projectManagerId,
        public readonly ProjectType $projectType,
        public readonly ProjectPriority $priority,
        public readonly ProjectStatus $status,
        public readonly ?Carbon $startDate,
        public readonly ?Carbon $endDate,
        public readonly ?float $plannedBudget,
        public readonly ?string $address,
        public readonly ?array $coordinates,
        public readonly ?array $metadata = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            clientCompanyId: $data['client_company_id'],
            projectManagerId: $data['project_manager_id'],
            projectType: ProjectType::from($data['project_type'] ?? 'construction'),
            priority: ProjectPriority::from($data['priority'] ?? 'medium'),
            status: ProjectStatus::from($data['status'] ?? 'draft'),
            startDate: isset($data['start_date']) ? Carbon::parse($data['start_date']) : null,
            endDate: isset($data['end_date']) ? Carbon::parse($data['end_date']) : null,
            plannedBudget: isset($data['planned_budget']) ? (float) $data['planned_budget'] : null,
            address: $data['address'] ?? null,
            coordinates: $data['coordinates'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'client_company_id' => $this->clientCompanyId,
            'project_manager_id' => $this->projectManagerId,
            'project_type' => $this->projectType->value,
            'priority' => $this->priority->value,
            'status' => $this->status->value,
            'start_date' => $this->startDate?->format('Y-m-d'),
            'end_date' => $this->endDate?->format('Y-m-d'),
            'planned_budget' => $this->plannedBudget,
            'address' => $this->address,
            'coordinates' => $this->coordinates,
            'metadata' => $this->metadata,
        ];
    }
}