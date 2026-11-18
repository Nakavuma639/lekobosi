<?php

namespace App\Http\Requests\Admin;

use App\Models\Booking;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Correcting a pending booking's details. Same rules as staff entry; the
 * source can't change.
 */
class UpdateBookingRequest extends StoreStaffBookingRequest
{
    /**
     * Owner and staff edit pending bookings.
     */
    public function authorize(): bool
    {
        /** @var Booking $booking */
        $booking = $this->route('booking');

        return $this->user()->can('update', $booking);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['source']);

        return $rules;
    }
}
