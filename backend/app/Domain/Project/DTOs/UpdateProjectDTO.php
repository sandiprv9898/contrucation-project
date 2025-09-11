<?php

namespace App\Domain\Project\DTOs;

use App\Domain\Project\Enums\ProjectPriority;
use App\Domain\Project\Enums\ProjectStatus;
use App\Domain\Project\Enums\ProjectType;
use Carbon\Carbon;

class UpdateProjectDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $clientCompanyId = null,
        public readonly ?string $projectManagerId = null,
        public readonly ?ProjectType $projectType = null,
        public readonly ?ProjectPriority $priority = null,
        public readonly ?ProjectStatus $status = null,
        public readonly ?Carbon $startDate = null,
        public readonly ?Carbon $endDate = null,
        public readonly ?float $plannedBudget = null,
        public readonly ?float $actualBudget = null,
        public readonly ?int $progressPercentage = null,
        public readonly ?string $address = null,
        public readonly ?array $coordinates = null,
        public readonly ?array $metadata = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            clientCompanyId: $data['client_company_id'] ?? null,
            projectManagerId: $data['project_manager_id'] ?? null,
            projectType: isset($data['project_type']) ? ProjectType::from($data['project_type']) : null,
            priority: isset($data['priority']) ? ProjectPriority::from($data['priority']) : null,
            status: isset($data['status']) ? ProjectStatus::from($data['status']) : null,
            startDate: isset($data['start_date']) ? Carbon::parse($data['start_date']) : null,
            endDate: isset($data['end_date']) ? Carbon::parse($data['end_date']) : null,
            plannedBudget: isset($data['planned_budget']) ? (float) $data['planned_budget'] : null,
            actualBudget: isset($data['actual_budget']) ? (float) $data['actual_budget'] : null,
            progressPercentage: isset($data['progress_percentage']) ? (int) $data['progress_percentage'] : null,
            address: $data['address'] ?? null,
            coordinates: $data['coordinates'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) $data['name'] = $this->name;
        if ($this->description !== null) $data['description'] = $this->description;
        if ($this->clientCompanyId !== null) $data['client_company_id'] = $this->clientCompanyId;
        if ($this->projectManagerId !== null) $data['project_manager_id'] = $this->projectManagerId;
        if ($this->projectType !== null) $data['project_type'] = $this->projectType->value;
        if ($this->priority !== null) $data['priority'] = $this->priority->value;
        if ($this->status !== null) $data['status'] = $this->status->value;
        if ($this->startDate !== null) $data['start_date'] = $this->startDate->format('Y-m-d');
        if ($this->endDate !== null) $data['end_date'] = $this->endDate->format('Y-m-d');
        if ($this->plannedBudget !== null) $data['planned_budget'] = $this->plannedBudget;
        if ($this->actualBudget !== null) $data['actual_budget'] = $this->actualBudget;
        if ($this->progressPercentage !== null) $data['progress_percentage'] = $this->progressPercentage;
        if ($this->address !== null) $data['address'] = $this->address;
        if ($this->coordinates !== null) $data['coordinates'] = $this->coordinates;
        if ($this->metadata !== null) $data['metadata'] = $this->metadata;

        return $data;
    }
}