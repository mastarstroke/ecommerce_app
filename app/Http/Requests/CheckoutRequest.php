<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'shipping_address' => 'required|string|min:10',
            'shipping_city' => 'required|string|max:100',
            'shipping_postal' => 'required|string|max:20',
            'payment_method' => 'required|in:credit_card,paypal,cash_on_delivery',
            'notes' => 'nullable|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_address.required' => 'Please provide your shipping address',
            'shipping_city.required' => 'Please provide your city',
            'shipping_postal.required' => 'Please provide your postal code',
            'payment_method.required' => 'Please select a payment method'
        ];
    }
}