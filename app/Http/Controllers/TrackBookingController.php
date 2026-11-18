<?php

namespace App\Http\Controllers;

use App\Actions\Bookings\TrackBooking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrackBookingController extends Controller
{
    /**
     * The same message for every unknown reference, so the page never hints
     * at whether another customer's booking exists.
     */
    public const NOT_FOUND_MESSAGE = "We couldn't find a booking with that reference. Please check and try again.";

    /**
     * Show the track page, and the booking's status when a reference is given.
     */
    public function __invoke(Request $request, TrackBooking $trackBooking): Response
    {
        $reference = trim((string) $request->query('reference', ''));
        $trackedBooking = null;
        $notFoundMessage = null;

        if ($reference !== '') {
            $trackedBooking = $trackBooking->handle($reference);
            $notFoundMessage = $trackedBooking === null ? self::NOT_FOUND_MESSAGE : null;
        }

        $status = $notFoundMessage === null ? Response::HTTP_OK : Response::HTTP_NOT_FOUND;

        return response()->view('site.track-booking', compact('reference', 'trackedBooking', 'notFoundMessage'), $status);
    }
}
