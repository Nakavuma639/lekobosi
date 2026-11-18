<?php

namespace App\Http\Controllers;

use App\Services\BookingAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    /**
     * List the fully booked dates in a month so the booking form can warn
     * customers. Exposes dates only, never customer data.
     */
    public function __invoke(Request $request, BookingAvailability $availability): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $month = CarbonImmutable::createFromFormat('!Y-m', $validated['month']);

        return response()->json([
            'month' => $month->format('Y-m'),
            'full_dates' => $availability->fullDatesInMonth($month),
        ]);
    }
}
