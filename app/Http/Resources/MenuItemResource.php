<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
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
            'category_id' => $this->category_id,
            'name' => $this->name,
            'title' => $this->name,
            'description' => $this->description,
            'base_price' => (float) $this->base_price,
            'price' => (float) $this->base_price,
            'tax_inclusive' => (bool) $this->tax_inclusive,
            'prep_time_min' => (int) $this->prep_time_min,
            'image' => $this->image_url,
            'image_url' => $this->image_url,
            'modifier_groups' => $this->modifier_groups ?? [],
            'is_available' => (bool) $this->is_available,
            'sort_order' => (int) $this->sort_order,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'is_active' => (bool) $this->category->is_active,
            ]),
        ];
    }
}
