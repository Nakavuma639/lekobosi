<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * A customer's own booking with its quote, payments and balance.
     * Other customers' bookings return 404 (see BookingPolicy::view).
     */
    public function show(Booking $booking): View
    {
        Gate::authorize('view', $booking);

        $booking->load(['payments' => fn ($query) => $query->confirmed()]);

        return view('customer.bookings.show', compact('booking'));
    }
}
