<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for validating kitchen order item updates.
 */
class KitchenUpdateItemRequest extends FormRequest
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
            'status' => ['required', 'string', 'in:accepted,preparing,ready'],
            'preparation_notes' => ['nullable', 'string', 'max:255'],
            'estimated_ready_time' => ['nullable', 'date_format:Y-m-d H:i:s'],
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
            'status.required' => 'Order item status is required.',
            'status.in' => 'The selected order item status is invalid.',
            'preparation_notes.string' => 'Preparation notes must be valid text.',
            'preparation_notes.max' => 'Preparation notes may not be greater than 255 characters.',
            'estimated_ready_time.date_format' => 'The estimated ready time must be in the format YYYY-MM-DD HH:MM:SS.',
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
            'status' => 'order item status',
            'preparation_notes' => 'preparation notes',
            'estimated_ready_time' => 'estimated ready time',
        ];
    }
}
