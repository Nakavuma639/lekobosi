<?php

namespace App\Http\Requests\Admin;

use App\Models\Booking;
use App\Support\Money;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingQuoteRequest extends FormRequest
{
    /**
     * Owner and staff set the quote.
     */
    public function authorize(): bool
    {
        /** @var Booking $booking */
        $booking = $this->route('booking');

        return $this->user()->can('updateQuote', $booking);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quoted_amount' => ['required', 'integer', 'min:0', 'max:9999999999'],
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
            'quoted_amount.required' => 'Enter the agreed price.',
            'quoted_amount.integer' => 'Enter the quote in whole Uganda shillings, e.g. 4,500,000.',
        ];
    }

    /**
     * Accept amounts typed with commas.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['quoted_amount' => Money::cleanInput($this->input('quoted_amount'))]);
    }
}
