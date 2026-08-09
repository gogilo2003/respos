<?php

namespace App\Http\Requests\MenuItem;

use App\Models\MenuItem;
use Illuminate\Foundation\Http\FormRequest;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', MenuItem::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:menu_categories,id'],
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:200'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'tax_inclusive' => ['required', 'boolean'],
            'prep_time_min' => ['required', 'integer', 'min:1', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'image_url' => ['nullable', 'string'],
            'modifier_groups' => ['nullable', 'array'],
            'is_available' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
