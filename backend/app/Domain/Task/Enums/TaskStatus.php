<?php

namespace App\Domain\Task\Enums;

enum TaskStatus: string
{
    case NOT_STARTED = 'not_started';
    case IN_PROGRESS = 'in_progress';
    case REVIEW = 'review';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case ON_HOLD = 'on_hold';

    public function label(): string
    {
        return match($this) {
            self::NOT_STARTED => 'Not Started',
            self::IN_PROGRESS => 'In Progress',
            self::REVIEW => 'Review',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::ON_HOLD => 'On Hold',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::NOT_STARTED => 'gray',
            self::IN_PROGRESS => 'blue',
            self::REVIEW => 'purple',
            self::COMPLETED => 'green',
            self::CANCELLED => 'red',
            self::ON_HOLD => 'yellow',
        };
    }

    public function canTransitionTo(TaskStatus $newStatus): bool
    {
        return match($this) {
            self::NOT_STARTED => in_array($newStatus, [self::IN_PROGRESS, self::CANCELLED, self::ON_HOLD]),
            self::IN_PROGRESS => in_array($newStatus, [self::REVIEW, self::COMPLETED, self::CANCELLED, self::ON_HOLD]),
            self::REVIEW => in_array($newStatus, [self::IN_PROGRESS, self::COMPLETED, self::CANCELLED]),
            self::ON_HOLD => in_array($newStatus, [self::NOT_STARTED, self::IN_PROGRESS, self::CANCELLED]),
            self::COMPLETED => in_array($newStatus, [self::IN_PROGRESS]), // Allow reopening
            self::CANCELLED => in_array($newStatus, [self::NOT_STARTED, self::IN_PROGRESS]),
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->map(fn($case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'color' => $case->color(),
            ])
            ->toArray();
    }

    public function isActive(): bool
    {
        return in_array($this, [self::IN_PROGRESS, self::REVIEW]);
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }
}