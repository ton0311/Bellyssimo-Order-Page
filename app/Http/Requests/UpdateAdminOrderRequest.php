<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['sometimes', 'required', 'array', 'min:1', 'max:20'],
            'items.*' => ['required', 'array:package,flavor'],
            'items.*.package' => ['required', 'string', Rule::in(['barkadahan', 'salo-salo', 'handaan'])],
            'items.*.flavor' => ['required', 'string', Rule::in(['Regular', 'Spicy'])],
            'customer' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^9\d{9}$/'],
            'email' => ['nullable', 'email:rfc', 'max:254'],
            'schedule' => ['required', 'date'],
            'fulfillment' => ['required', 'string', Rule::in(['Pickup', 'Delivery'])],
            'delivery_location' => ['nullable', 'required_if:fulfillment,Delivery', 'string', 'max:300'],
            'payment_method' => ['required', 'string', Rule::in(['Cash', 'GCash'])],
            'note' => ['nullable', 'string', 'max:300'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'items.required' => 'Keep at least one belly in the order.',
            'items.min' => 'Keep at least one belly in the order.',
            'items.max' => 'An order can contain up to 20 bellies.',
            'items.*.package.in' => 'Choose a valid Bellyssimo package for each belly.',
            'items.*.flavor.in' => 'Choose Regular or Spicy for each belly.',
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
            'delivery_location' => $this->input('fulfillment') === 'Delivery'
                ? trim((string) $this->input('delivery_location'))
                : null,
            'note' => filled($this->input('note')) ? trim((string) $this->input('note')) : null,
        ]);
    }
}
