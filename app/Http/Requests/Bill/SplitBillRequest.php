<?php

namespace App\Http\Requests\Bill;

use Illuminate\Foundation\Http\FormRequest;

class SplitBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'split_type' => ['required', 'in:equally,by_item,custom'],
            'number_of_splits' => ['integer', 'min:1'],
            'item_groups' => ['array'],
            'custom_amounts' => ['array'],
        ];
    }
}