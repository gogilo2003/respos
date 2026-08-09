<?php

declare(strict_types=1);

namespace App\Http\Requests\Bill;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for validating bill split requests.
 */
class SplitBillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'split_type' => ['required', 'in:equally,by_item,custom'],
            'number_of_splits' => ['integer', 'min:1'],
            'item_groups' => ['array'],
            'custom_amounts' => ['array'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'split_type.required' => 'Split type is required.',
            'split_type.in' => 'The selected split type is invalid.',
            'number_of_splits.integer' => 'Number of splits must be a whole number.',
            'number_of_splits.min' => 'Number of splits must be at least 1.',
            'item_groups.array' => 'Item groups must be provided as a list.',
            'custom_amounts.array' => 'Custom amounts must be provided as a list.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'split_type' => 'split type',
            'number_of_splits' => 'number of splits',
            'item_groups' => 'item groups',
            'custom_amounts' => 'custom amounts',
        ];
    }
}
