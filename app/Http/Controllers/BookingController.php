<?php

namespace App\Http\Controllers;

use App\Actions\Bookings\SubmitBooking;
use App\Enums\EventType;
use App\Enums\UserRole;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Services\BookingAvailability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Show the public booking form.
     */
    public function create(Request $request, BookingAvailability $availability): View
    {
        $eventTypes = EventType::cases();
        $earliestDate = $availability->earliestBookableDate()->toDateString();
        $latestDate = $availability->latestBookableDate()->toDateString();
        $maxGuests = (int) config('lekobosi.bookings.max_guests');
        $customer = $request->user()?->hasRole(UserRole::Customer) ? $request->user() : null;

        return view('site.booking', compact('eventTypes', 'earliestDate', 'latestDate', 'maxGuests', 'customer'));
    }

    /**
     * Store a booking request and show the customer their reference.
     */
    public function store(StoreBookingRequest $request, SubmitBooking $submitBooking): RedirectResponse
    {
        $customer = $request->user()?->hasRole(UserRole::Customer) ? $request->user() : null;

        $booking = $submitBooking->handle($request->bookingDetails(), $customer);

        return redirect()
            ->route('bookings.confirmation')
            ->with('submitted_booking_reference', $booking->reference);
    }

    /**
     * Confirm a just-submitted booking. The reference comes from the flashed
     * session, so the page is only reachable straight after submitting and can't
     * be used to look bookings up.
     */
    public function confirmation(Request $request): View|RedirectResponse
    {
        $reference = $request->session()->get('submitted_booking_reference');

        if ($reference === null) {
            return redirect()->route('bookings.create');
        }

        $booking = Booking::query()->where('reference', $reference)->firstOrFail();

        return view('site.booking-confirmation', compact('booking'));
    }
}
