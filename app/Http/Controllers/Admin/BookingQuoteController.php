<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Bookings\UpdateBookingQuote;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBookingQuoteRequest;
use App\Models\Booking;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;

class BookingQuoteController extends Controller
{
    /**
     * Set or change the agreed price for a booking.
     */
    public function update(UpdateBookingQuoteRequest $request, Booking $booking, UpdateBookingQuote $updateBookingQuote): RedirectResponse
    {
        $booking = $updateBookingQuote->handle($booking, (int) $request->validated('quoted_amount'));

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', 'Quote set to '.Money::format($booking->quoted_amount).'.');
    }
}
