<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ConfirmOrderRequest extends FormRequest
{
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
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.package' => ['required', 'string', Rule::in(['barkadahan', 'salo-salo', 'handaan'])],
            'items.*.flavor' => ['required', 'string', Rule::in(['Regular', 'Spicy'])],
            'fulfillment' => ['required', 'string', Rule::in(['Pickup', 'Delivery'])],
            'delivery_location' => ['nullable', 'required_if:fulfillment,Delivery', 'string', 'max:300'],
            'payment_method' => ['required', 'string', Rule::in(['Cash', 'GCash'])],
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'contact_number' => ['required', 'string', 'regex:/^9\d{9}$/'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_time' => ['required', 'date_format:H:i', Rule::in($this->preferredTimeOptions())],
            'notes' => ['nullable', 'string', 'max:300'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Please add at least one lechon belly to your order.',
            'items.min' => 'Please add at least one lechon belly to your order.',
            'items.max' => 'For orders above 20 bellies, please contact Toño’s Bellyssimo directly.',
            'items.*.package.required' => 'Please choose a package for every belly in your order.',
            'items.*.package.in' => 'Please choose a valid Bellyssimo package.',
            'items.*.flavor.required' => 'Please choose Regular or Spicy for every belly in your order.',
            'items.*.flavor.in' => 'Please choose Regular or Spicy for every belly in your order.',
            'delivery_location.required_if' => 'Please enter the delivery location so the fee can be confirmed.',
            'payment_method.required' => 'Please choose Cash or GCash as your payment option.',
            'payment_method.in' => 'Please choose Cash or GCash as your payment option.',
            'first_name.required' => 'Please enter your first name.',
            'last_name.required' => 'Please enter your last name.',
            'contact_number.required' => 'Please enter an active Philippine mobile number.',
            'contact_number.regex' => 'Enter a valid Philippine mobile number after +63, beginning with 9.',
            'email.required' => 'Please enter the email address that should receive order updates.',
            'email.email' => 'Please enter a valid email address.',
            'preferred_date.after_or_equal' => 'Please choose today or a future date.',
            'preferred_time.in' => 'Please choose a preferred time between 11:00 AM and 7:00 PM.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $digits = preg_replace('/\D+/', '', (string) $this->input('contact_number')) ?? '';

        if (str_starts_with($digits, '63') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0') && strlen($digits) === 11) {
            $digits = substr($digits, 1);
        }

        $this->merge([
            'first_name' => Str::squish((string) $this->input('first_name')),
            'last_name' => Str::squish((string) $this->input('last_name')),
            'contact_number' => $digits,
            'email' => Str::lower(trim((string) $this->input('email'))),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->hasAny(['preferred_date', 'preferred_time'])) {
                return;
            }

            $preferredSchedule = CarbonImmutable::createFromFormat(
                '!Y-m-d H:i',
                $this->string('preferred_date').' '.$this->string('preferred_time'),
                'Asia/Manila',
            );
            $bookingCutoff = CarbonImmutable::now('Asia/Manila')->addHours(48);

            if ($preferredSchedule->lt($bookingCutoff)) {
                $validator->errors()->add(
                    'preferred_date',
                    'Bookings must be placed at least 48 hours before the preferred pickup or delivery time.',
                );
            }
        });
    }

    /** @return list<string> */
    private function preferredTimeOptions(): array
    {
        return array_map(
            fn (int $minutes): string => sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60),
            range(11 * 60, 19 * 60, 30),
        );
    }
}
