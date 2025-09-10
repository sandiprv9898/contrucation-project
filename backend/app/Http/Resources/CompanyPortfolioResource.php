<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyPortfolioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'title' => $this->title,
            'description' => $this->description,
            'excerpt' => $this->excerpt,
            'category' => $this->category,
            'category_display' => $this->category_display,
            'image_path' => $this->image_path,
            'image_url' => $this->image_url,
            'external_url' => $this->external_url,
            'display_order' => $this->display_order,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}