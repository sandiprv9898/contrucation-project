<?php

namespace App\Domain\Project\Enums;

enum ProjectPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
            self::URGENT => 'Urgent',
            self::CRITICAL => 'Critical',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::LOW => 'gray',
            self::MEDIUM => 'blue',
            self::HIGH => 'yellow',
            self::URGENT => 'red',
            self::CRITICAL => 'purple',
        };
    }

    public function weight(): int
    {
        return match($this) {
            self::LOW => 1,
            self::MEDIUM => 2,
            self::HIGH => 3,
            self::URGENT => 4,
            self::CRITICAL => 5,
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->map(fn($case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'color' => $case->color(),
                'weight' => $case->weight(),
            ])
            ->toArray();
    }
}