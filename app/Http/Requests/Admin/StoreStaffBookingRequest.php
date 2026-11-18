<?php

namespace App\Http\Requests\Admin;

use App\Enums\BookingSource;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * A booking taken by phone or at the counter. Same rules as the public form,
 * except email is optional (many customers have none) and the source is
 * recorded. Staff aren't bots, so there is no honeypot.
 */
class StoreStaffBookingRequest extends StoreBookingRequest
{
    /**
     * Owner and staff enter bookings on a customer's behalf.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Booking::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        unset($rules[self::HONEYPOT]);
        $rules['email'] = ['nullable', 'string', 'email', 'max:190'];
        $rules['source'] = ['required', Rule::enum(BookingSource::class)->only([BookingSource::Phone, BookingSource::WalkIn])];

        return $rules;
    }

    /**
     * How the booking reached the business.
     */
    public function source(): BookingSource
    {
        return BookingSource::from($this->validated('source'));
    }
}
