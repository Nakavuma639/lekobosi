<?php

namespace App\Exceptions;

use Carbon\CarbonInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Thrown when approving a booking would exceed the date's capacity.
 */
class BookingDateFullException extends Exception
{
    public function __construct(public readonly CarbonInterface $date)
    {
        parent::__construct("{$date->format('j F Y')} is already fully booked. Reject this request or cancel the other approved booking first.");
    }

    /**
     * JSON clients get 409 Conflict; the admin pages go back with the message.
     */
    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], Response::HTTP_CONFLICT);
        }

        return back()->withErrors(['status' => $this->getMessage()]);
    }

    /**
     * Expected business outcome, not an application error.
     */
    public function report(): bool
    {
        return true;
    }
}
