<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for validating customer order creation.
 */
class StoreOrderRequest extends FormRequest
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
            'table_session_id' => ['required', 'exists:table_sessions,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
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
            'table_session_id.required' => 'A table session is required to place an order.',
            'table_session_id.exists' => 'The selected table session is invalid.',
            'items.required' => 'At least one menu item is required to place an order.',
            'items.array' => 'Order items must be provided as a list.',
            'items.min' => 'At least one menu item is required to place an order.',
            'items.*.menu_item_id.required' => 'Each order item must reference a menu item.',
            'items.*.menu_item_id.exists' => 'One or more selected menu items are invalid.',
            'items.*.quantity.required' => 'Quantity is required for each menu item.',
            'items.*.quantity.integer' => 'Quantity must be a whole number.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
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
            'table_session_id' => 'table session',
            'items' => 'order items',
            'items.*.menu_item_id' => 'menu item',
            'items.*.quantity' => 'quantity',
        ];
    }
}
