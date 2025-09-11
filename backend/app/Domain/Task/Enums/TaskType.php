<?php

namespace App\Domain\Task\Enums;

enum TaskType: string
{
    case GENERAL = 'general';
    case PLANNING = 'planning';
    case FOUNDATION = 'foundation';
    case FRAMING = 'framing';
    case ELECTRICAL = 'electrical';
    case PLUMBING = 'plumbing';
    case HVAC = 'hvac';
    case ROOFING = 'roofing';
    case INSULATION = 'insulation';
    case DRYWALL = 'drywall';
    case FLOORING = 'flooring';
    case PAINTING = 'painting';
    case FINISHING = 'finishing';
    case INSPECTION = 'inspection';
    case CLEANUP = 'cleanup';

    public function label(): string
    {
        return match($this) {
            self::GENERAL => 'General',
            self::PLANNING => 'Planning',
            self::FOUNDATION => 'Foundation',
            self::FRAMING => 'Framing',
            self::ELECTRICAL => 'Electrical',
            self::PLUMBING => 'Plumbing',
            self::HVAC => 'HVAC',
            self::ROOFING => 'Roofing',
            self::INSULATION => 'Insulation',
            self::DRYWALL => 'Drywall',
            self::FLOORING => 'Flooring',
            self::PAINTING => 'Painting',
            self::FINISHING => 'Finishing',
            self::INSPECTION => 'Inspection',
            self::CLEANUP => 'Cleanup',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::GENERAL => 'gray',
            self::PLANNING => 'purple',
            self::FOUNDATION => 'orange',
            self::FRAMING => 'yellow',
            self::ELECTRICAL => 'blue',
            self::PLUMBING => 'cyan',
            self::HVAC => 'indigo',
            self::ROOFING => 'red',
            self::INSULATION => 'pink',
            self::DRYWALL => 'green',
            self::FLOORING => 'teal',
            self::PAINTING => 'lime',
            self::FINISHING => 'emerald',
            self::INSPECTION => 'amber',
            self::CLEANUP => 'slate',
        };
    }

    public function category(): string
    {
        return match($this) {
            self::GENERAL, self::PLANNING => 'Management',
            self::FOUNDATION, self::FRAMING => 'Structure',
            self::ELECTRICAL, self::PLUMBING, self::HVAC => 'Systems',
            self::ROOFING, self::INSULATION => 'Envelope',
            self::DRYWALL, self::FLOORING, self::PAINTING, self::FINISHING => 'Interior',
            self::INSPECTION, self::CLEANUP => 'Quality',
        };
    }

    public function requiresSkills(): array
    {
        return match($this) {
            self::ELECTRICAL => ['electrical', 'safety', 'wiring'],
            self::PLUMBING => ['plumbing', 'pipefitting', 'safety'],
            self::HVAC => ['hvac', 'ductwork', 'refrigeration'],
            self::FRAMING => ['carpentry', 'measuring', 'safety'],
            self::ROOFING => ['roofing', 'safety', 'heights'],
            self::FOUNDATION => ['concrete', 'excavation', 'safety'],
            self::PAINTING => ['painting', 'surface_prep', 'color_matching'],
            self::FLOORING => ['flooring', 'measuring', 'installation'],
            self::DRYWALL => ['drywall', 'taping', 'mudding'],
            default => ['general', 'safety'],
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->map(fn($case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'color' => $case->color(),
                'category' => $case->category(),
                'required_skills' => $case->requiresSkills(),
            ])
            ->toArray();
    }

    public static function getByCategory(): array
    {
        return collect(self::cases())
            ->groupBy(fn($case) => $case->category())
            ->map(fn($cases) => $cases->map(fn($case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'color' => $case->color(),
            ]))
            ->toArray();
    }
}