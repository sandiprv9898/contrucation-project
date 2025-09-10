<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'business_registration' => $this->business_registration,
            'tax_identification' => $this->tax_identification,
            'industry_type' => $this->industry_type,
            'company_size' => $this->company_size,
            'company_size_display' => $this->company_size_display,
            'founded_date' => $this->founded_date?->format('Y-m-d'),
            'description' => $this->description,
            'website' => $this->website,
            'social_media' => $this->social_media,
            'certifications' => $this->certifications,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}