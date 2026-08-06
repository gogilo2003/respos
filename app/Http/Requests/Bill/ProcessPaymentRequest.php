<?php

namespace App\Http\Requests\Bill;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount_received' => ['required', 'numeric', 'min:0'],
            'cashier_id' => ['required', 'exists:users,id'],
        ];
    }
}