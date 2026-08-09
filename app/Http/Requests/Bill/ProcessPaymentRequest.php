<?php

declare(strict_types=1);

namespace App\Http\Requests\Bill;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for validating payment processing.
 */
class ProcessPaymentRequest extends FormRequest
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
            'amount_received' => ['required', 'numeric', 'min:0'],
            'cashier_id' => ['required', 'exists:users,id'],
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
            'amount_received.required' => 'Amount received is required.',
            'amount_received.numeric' => 'Amount received must be a number.',
            'amount_received.min' => 'Amount received cannot be negative.',
            'cashier_id.required' => 'Cashier is required.',
            'cashier_id.exists' => 'The selected cashier is invalid.',
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
            'amount_received' => 'amount received',
            'cashier_id' => 'cashier',
        ];
    }
}
