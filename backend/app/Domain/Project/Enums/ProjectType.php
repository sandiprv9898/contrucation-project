<?php

namespace App\Domain\Project\Enums;

enum ProjectType: string
{
    case CONSTRUCTION = 'construction';
    case RENOVATION = 'renovation';
    case MAINTENANCE = 'maintenance';
    case INSPECTION = 'inspection';
    case CONSULTING = 'consulting';

    public function label(): string
    {
        return match($this) {
            self::CONSTRUCTION => 'Construction',
            self::RENOVATION => 'Renovation',
            self::MAINTENANCE => 'Maintenance',
            self::INSPECTION => 'Inspection',
            self::CONSULTING => 'Consulting',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::CONSTRUCTION => 'New construction projects',
            self::RENOVATION => 'Building renovation and remodeling',
            self::MAINTENANCE => 'Regular maintenance and repairs',
            self::INSPECTION => 'Safety and quality inspections',
            self::CONSULTING => 'Construction consulting services',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->map(fn($case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'description' => $case->description(),
            ])
            ->toArray();
    }
}