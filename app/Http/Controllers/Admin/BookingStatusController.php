<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Bookings\ChangeBookingStatus;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBookingStatusRequest;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class BookingStatusController extends Controller
{
    /**
     * Approve, reject or cancel a booking (owner only).
     */
    public function update(UpdateBookingStatusRequest $request, Booking $booking, ChangeBookingStatus $changeBookingStatus): RedirectResponse|JsonResponse
    {
        $newStatus = BookingStatus::from($request->validated('status'));
        $quotedAmount = $request->validated('quoted_amount');

        $booking = $changeBookingStatus->handle(
            booking: $booking,
            newStatus: $newStatus,
            decidedBy: $request->user(),
            note: $request->validated('note'),
            quotedAmount: $quotedAmount === null ? null : (int) $quotedAmount,
        );

        if ($request->expectsJson()) {
            return response()->json([
                'reference' => $booking->reference,
                'status' => $booking->status->value,
                'status_changed_at' => $booking->status_changed_at?->toIso8601String(),
            ]);
        }

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', "Booking {$booking->reference} is now {$booking->status->label()}.");
    }
}
