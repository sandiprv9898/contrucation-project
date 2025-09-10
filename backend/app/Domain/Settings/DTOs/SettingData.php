<?php

namespace App\Domain\Settings\DTOs;

use Spatie\LaravelData\Data;

class SettingData extends Data
{
    public function __construct(
        public string $category,
        public string $key,
        public mixed $value,
        public ?string $company_id = null,
        public ?string $updated_by = null,
    ) {}

    public static function fromRequest(array $request): self
    {
        return new self(
            category: $request['category'],
            key: $request['key'],
            value: $request['value'],
            company_id: $request['company_id'] ?? auth()->user()?->company_id,
            updated_by: auth()->id(),
        );
    }

    public function toArray(): array
    {
        return [
            'category' => $this->category,
            'key' => $this->key,
            'value' => $this->value,
            'company_id' => $this->company_id,
            'updated_by' => $this->updated_by,
        ];
    }
}