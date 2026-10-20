<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderPaymentRequest extends FormRequest
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
        $order = $this->route('order');
        $maximum = $order instanceof Order ? $order->order_total : PHP_INT_MAX;
        $rules = ['required', 'integer', 'min:0', 'max:'.$maximum];

        if ($order instanceof Order && $order->status === 'Completed') {
            $rules[] = Rule::in([$maximum]);
        }

        return [
            'amount_paid' => $rules,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount_paid.max' => 'The amount received cannot be more than the order total.',
            'amount_paid.in' => 'Completed orders must remain fully paid.',
        ];
    }
}
