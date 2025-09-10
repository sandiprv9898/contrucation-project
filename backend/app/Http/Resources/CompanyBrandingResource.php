<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyBrandingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'asset_type' => $this->asset_type,
            'asset_type_display' => $this->asset_type_display,
            'asset_variant' => $this->asset_variant,
            'asset_variant_display' => $this->asset_variant_display,
            'file_path' => $this->file_path,
            'file_url' => $this->file_url,
            'file_size' => $this->file_size,
            'file_size_human' => $this->file_size_human,
            'mime_type' => $this->mime_type,
            'dimensions' => $this->dimensions,
            'dimensions_string' => $this->dimensions_string,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}