<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LanguageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'native_name' => $this->native_name,
            'flag_emoji' => $this->flag_emoji,
            'direction' => $this->direction,
            'is_active' => $this->is_active,
            'is_default' => $this->is_default,
            'sort_order' => $this->sort_order,
            'date_format' => $this->date_format,
            'js_date_format' => $this->getJsDateFormat(),
            'number_format' => $this->getNumberFormat(),
            'currency_format' => $this->getCurrencyFormat(),
            'completion_percentage' => $this->when(
                $request->routeIs('api.v1.localization.languages'),
                fn() => $this->getCompletionPercentage()
            ),
            'has_construction_terms' => $this->when(
                $request->get('include_construction_stats'),
                fn() => $this->hasConstructionTerms()
            ),
        ];
    }
}