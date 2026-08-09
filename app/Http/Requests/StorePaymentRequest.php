<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for validating payment creation.
 */
class StorePaymentRequest extends FormRequest
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
            'bill_id' => ['required', 'exists:bills,id'],
            'payment_method' => ['required', 'in:cash,manual'],
            'amount_received' => ['required', 'numeric', 'min:0'],
            'manual_note' => ['nullable', 'string', 'max:100'],
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
            'bill_id.required' => 'A bill is required to process payment.',
            'bill_id.exists' => 'The selected bill is invalid.',
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'The selected payment method is invalid.',
            'amount_received.required' => 'Amount received is required.',
            'amount_received.numeric' => 'Amount received must be a number.',
            'amount_received.min' => 'Amount received cannot be negative.',
            'manual_note.string' => 'Notes must be valid text.',
            'manual_note.max' => 'Notes may not be greater than 100 characters.',
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
            'bill_id' => 'bill',
            'payment_method' => 'payment method',
            'amount_received' => 'amount received',
            'manual_note' => 'notes',
        ];
    }
}
