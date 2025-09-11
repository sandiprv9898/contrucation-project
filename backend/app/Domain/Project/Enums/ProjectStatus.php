<?php

namespace App\Domain\Project\Enums;

enum ProjectStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case ON_HOLD = 'on_hold';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::ACTIVE => 'Active',
            self::ON_HOLD => 'On Hold',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::ACTIVE => 'blue',
            self::ON_HOLD => 'yellow',
            self::COMPLETED => 'green',
            self::CANCELLED => 'red',
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
}