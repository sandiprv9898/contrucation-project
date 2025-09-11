<?php

namespace App\Domain\Task\Enums;

enum TaskDependencyType: string
{
    case FINISH_TO_START = 'finish_to_start';
    case START_TO_START = 'start_to_start';
    case FINISH_TO_FINISH = 'finish_to_finish';
    case START_TO_FINISH = 'start_to_finish';

    public function label(): string
    {
        return match($this) {
            self::FINISH_TO_START => 'Finish to Start',
            self::START_TO_START => 'Start to Start',
            self::FINISH_TO_FINISH => 'Finish to Finish',
            self::START_TO_FINISH => 'Start to Finish',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::FINISH_TO_START => 'Task must finish before dependent task can start',
            self::START_TO_START => 'Tasks can start at the same time',
            self::FINISH_TO_FINISH => 'Tasks must finish at the same time',
            self::START_TO_FINISH => 'Task must start before dependent task can finish',
        };
    }

    public function isDefault(): bool
    {
        return $this === self::FINISH_TO_START;
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->map(fn($case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'description' => $case->description(),
                'is_default' => $case->isDefault(),
            ])
            ->toArray();
    }
}