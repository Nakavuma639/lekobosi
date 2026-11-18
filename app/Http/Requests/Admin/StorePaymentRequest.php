<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentMethod;
use App\Models\Booking;
use App\Support\Money;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    /**
     * Owner and staff record payments.
     */
    public function authorize(): bool
    {
        /** @var Booking $booking */
        $booking = $this->route('booking');

        return $this->user()->can('recordPayment', $booking);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1', 'max:9999999999'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference' => [
                'nullable', 'string', 'max:100',
                Rule::requiredIf(fn () => in_array($this->input('method'), [PaymentMethod::MtnMomo->value, PaymentMethod::AirtelMoney->value, PaymentMethod::BankTransfer->value], true)),
            ],
            'received_at' => ['required', 'date', 'before_or_equal:now'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Enter the amount received.',
            'amount.integer' => 'Enter the amount in whole Uganda shillings, e.g. 500,000.',
            'amount.min' => 'The amount must be more than zero.',
            'reference.required' => 'Enter the transaction ID or bank reference for this payment method.',
            'received_at.before_or_equal' => "The payment date can't be in the future.",
        ];
    }

    /**
     * Accept amounts typed with commas, and tidy free text.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount' => Money::cleanInput($this->input('amount')),
            'reference' => trim(strip_tags((string) $this->input('reference'))) ?: null,
            'note' => trim(strip_tags((string) $this->input('note'))) ?: null,
        ]);
    }
}
