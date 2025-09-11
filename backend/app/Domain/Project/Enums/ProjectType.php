<?php

namespace App\Domain\Project\Enums;

enum ProjectType: string
{
    case COMMERCIAL = 'commercial';
    case RESIDENTIAL = 'residential';
    case INDUSTRIAL = 'industrial';
    case HEALTHCARE = 'healthcare';
    case INSTITUTIONAL = 'institutional';
    case CONSTRUCTION = 'construction';
    case RENOVATION = 'renovation';
    case MAINTENANCE = 'maintenance';
    case INSPECTION = 'inspection';
    case CONSULTING = 'consulting';

    public function label(): string
    {
        return match($this) {
            self::COMMERCIAL => 'Commercial',
            self::RESIDENTIAL => 'Residential',
            self::INDUSTRIAL => 'Industrial',
            self::HEALTHCARE => 'Healthcare',
            self::INSTITUTIONAL => 'Institutional',
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
            self::COMMERCIAL => 'Commercial buildings and office complexes',
            self::RESIDENTIAL => 'Residential homes and apartment complexes',
            self::INDUSTRIAL => 'Factories, warehouses, and industrial facilities',
            self::HEALTHCARE => 'Hospitals, clinics, and medical facilities',
            self::INSTITUTIONAL => 'Schools, universities, and government buildings',
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