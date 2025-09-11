<?php

namespace App\Domain\Task\DTOs;

use App\Domain\Task\Enums\TaskPriority;
use App\Domain\Task\Enums\TaskStatus;
use App\Domain\Task\Enums\TaskType;
use Carbon\Carbon;

class CreateTaskDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $projectId,
        public readonly ?string $phaseId,
        public readonly ?string $parentTaskId,
        public readonly TaskType $taskType,
        public readonly TaskPriority $priority,
        public readonly TaskStatus $status,
        public readonly ?string $assignedToId,
        public readonly string $createdById,
        public readonly ?float $estimatedHours,
        public readonly ?Carbon $startDate,
        public readonly ?Carbon $dueDate,
        public readonly int $taskOrder,
        public readonly ?array $metadata = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            projectId: $data['project_id'],
            phaseId: $data['phase_id'] ?? null,
            parentTaskId: $data['parent_task_id'] ?? null,
            taskType: TaskType::from($data['task_type'] ?? 'general'),
            priority: TaskPriority::from($data['priority'] ?? 'medium'),
            status: TaskStatus::from($data['status'] ?? 'not_started'),
            assignedToId: $data['assigned_to_id'] ?? null,
            createdById: $data['created_by_id'],
            estimatedHours: isset($data['estimated_hours']) ? (float) $data['estimated_hours'] : null,
            startDate: isset($data['start_date']) ? Carbon::parse($data['start_date']) : null,
            dueDate: isset($data['due_date']) ? Carbon::parse($data['due_date']) : null,
            taskOrder: (int) ($data['task_order'] ?? 0),
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'project_id' => $this->projectId,
            'phase_id' => $this->phaseId,
            'parent_task_id' => $this->parentTaskId,
            'task_type' => $this->taskType->value,
            'priority' => $this->priority->value,
            'status' => $this->status->value,
            'assigned_to_id' => $this->assignedToId,
            'created_by_id' => $this->createdById,
            'estimated_hours' => $this->estimatedHours,
            'start_date' => $this->startDate?->format('Y-m-d'),
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'task_order' => $this->taskOrder,
            'metadata' => $this->metadata,
        ];
    }
}