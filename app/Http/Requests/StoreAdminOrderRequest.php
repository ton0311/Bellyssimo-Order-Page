<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminOrderRequest extends FormRequest
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
            'customer' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^9\d{9}$/'],
            'email' => ['nullable', 'email:rfc', 'max:254'],
            'schedule' => ['required', 'date'],
            'package' => ['required', 'string', Rule::in(['barkadahan', 'salo-salo', 'handaan'])],
            'flavor' => ['required', 'string', Rule::in(['Regular', 'Spicy'])],
            'fulfillment' => ['required', 'string', Rule::in(['Pickup', 'Delivery'])],
            'delivery_location' => ['nullable', 'required_if:fulfillment,Delivery', 'string', 'max:300'],
            'payment' => ['required', 'string', Rule::in(['Unpaid', 'Deposit received', 'Paid'])],
            'payment_method' => ['required', 'string', Rule::in(['Cash', 'GCash'])],
            'note' => ['nullable', 'string', 'max:300'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid Philippine mobile number after +63, beginning with 9.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $digits = preg_replace('/\D+/', '', (string) $this->input('phone')) ?? '';

        if (str_starts_with($digits, '63') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0') && strlen($digits) === 11) {
            $digits = substr($digits, 1);
        }

        $this->merge([
            'phone' => $digits,
            'email' => filled($this->input('email'))
                ? mb_strtolower(trim((string) $this->input('email')))
                : null,
        ]);
    }
}
