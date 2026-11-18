<?php

namespace App\Http\Requests\Admin;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Support\Money;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingStatusRequest extends FormRequest
{
    /**
     * Only the owner may decide on a booking.
     */
    public function authorize(): bool
    {
        /** @var Booking $booking */
        $booking = $this->route('booking');

        return $this->user()->can('decide', $booking);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(BookingStatus::class)->only([BookingStatus::Approved, BookingStatus::Rejected])],
            'note' => ['nullable', 'string', 'max:500', 'required_if:status,'.BookingStatus::Rejected->value],
            'quoted_amount' => ['nullable', 'integer', 'min:0', 'max:9999999999'],
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
            'note.required_if' => 'Give the customer a reason for rejecting the booking.',
            'quoted_amount.integer' => 'Enter the quote in whole Uganda shillings, without commas.',
        ];
    }

    /**
     * Accept quotes typed with commas or spaces, e.g. "4,500,000".
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('quoted_amount')) {
            $this->merge(['quoted_amount' => Money::cleanInput($this->input('quoted_amount'))]);
        }
    }
}
