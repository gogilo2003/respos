<?php

namespace App\Http\Requests\MenuCategory;

use App\Models\MenuCategory;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('category');
        if (! ($category instanceof MenuCategory)) {
            $category = MenuCategory::find($category);
        }

        return $category ? ($this->user()?->can('update', $category) ?? false) : false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:200'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
