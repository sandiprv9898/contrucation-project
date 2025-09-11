<?php

namespace App\Domain\Task\DTOs;

use App\Domain\Task\Enums\TaskPriority;
use App\Domain\Task\Enums\TaskStatus;
use App\Domain\Task\Enums\TaskType;
use Carbon\Carbon;

class UpdateTaskDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $phaseId = null,
        public readonly ?string $parentTaskId = null,
        public readonly ?TaskType $taskType = null,
        public readonly ?TaskPriority $priority = null,
        public readonly ?TaskStatus $status = null,
        public readonly ?string $assignedToId = null,
        public readonly ?float $estimatedHours = null,
        public readonly ?float $actualHours = null,
        public readonly ?int $progressPercentage = null,
        public readonly ?Carbon $startDate = null,
        public readonly ?Carbon $dueDate = null,
        public readonly ?Carbon $completedAt = null,
        public readonly ?int $taskOrder = null,
        public readonly ?array $metadata = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            description: array_key_exists('description', $data) ? $data['description'] : null,
            phaseId: array_key_exists('phase_id', $data) ? $data['phase_id'] : null,
            parentTaskId: array_key_exists('parent_task_id', $data) ? $data['parent_task_id'] : null,
            taskType: isset($data['task_type']) ? TaskType::from($data['task_type']) : null,
            priority: isset($data['priority']) ? TaskPriority::from($data['priority']) : null,
            status: isset($data['status']) ? TaskStatus::from($data['status']) : null,
            assignedToId: array_key_exists('assigned_to_id', $data) ? $data['assigned_to_id'] : null,
            estimatedHours: isset($data['estimated_hours']) ? (float) $data['estimated_hours'] : null,
            actualHours: isset($data['actual_hours']) ? (float) $data['actual_hours'] : null,
            progressPercentage: isset($data['progress_percentage']) ? (int) $data['progress_percentage'] : null,
            startDate: isset($data['start_date']) ? Carbon::parse($data['start_date']) : null,
            dueDate: isset($data['due_date']) ? Carbon::parse($data['due_date']) : null,
            completedAt: isset($data['completed_at']) ? Carbon::parse($data['completed_at']) : null,
            taskOrder: isset($data['task_order']) ? (int) $data['task_order'] : null,
            metadata: array_key_exists('metadata', $data) ? $data['metadata'] : null,
        );
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        if ($this->phaseId !== null) {
            $data['phase_id'] = $this->phaseId;
        }

        if ($this->parentTaskId !== null) {
            $data['parent_task_id'] = $this->parentTaskId;
        }

        if ($this->taskType !== null) {
            $data['task_type'] = $this->taskType->value;
        }

        if ($this->priority !== null) {
            $data['priority'] = $this->priority->value;
        }

        if ($this->status !== null) {
            $data['status'] = $this->status->value;
        }

        if ($this->assignedToId !== null) {
            $data['assigned_to_id'] = $this->assignedToId;
        }

        if ($this->estimatedHours !== null) {
            $data['estimated_hours'] = $this->estimatedHours;
        }

        if ($this->actualHours !== null) {
            $data['actual_hours'] = $this->actualHours;
        }

        if ($this->progressPercentage !== null) {
            $data['progress_percentage'] = $this->progressPercentage;
        }

        if ($this->startDate !== null) {
            $data['start_date'] = $this->startDate->format('Y-m-d');
        }

        if ($this->dueDate !== null) {
            $data['due_date'] = $this->dueDate->format('Y-m-d');
        }

        if ($this->completedAt !== null) {
            $data['completed_at'] = $this->completedAt;
        }

        if ($this->taskOrder !== null) {
            $data['task_order'] = $this->taskOrder;
        }

        if ($this->metadata !== null) {
            $data['metadata'] = $this->metadata;
        }

        return $data;
    }
}