<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ContactMessage;
use App\Services\BookingAvailability;
use App\Services\BookingStatistics;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the back-office dashboard with live totals and requests awaiting a decision.
     */
    public function __invoke(Request $request, BookingStatistics $statistics, BookingAvailability $availability): View
    {
        $user = $request->user();
        $totals = $statistics->totals();

        $pendingBookings = Booking::query()
            ->where('status', BookingStatus::Pending)
            ->orderBy('event_date')
            ->orderBy('id')
            ->limit(10)
            ->get();
        $conflictDates = $availability->fullDatesAmong($pendingBookings->pluck('event_date'));

        $upcomingBookings = Booking::query()
            ->approved()
            ->where('event_date', '>=', today()->toDateString())
            ->orderBy('event_date')
            ->orderBy('id')
            ->limit(5)
            ->get();

        $unreadMessages = ContactMessage::query()->unread()->count();
        $outstandingBalance = $statistics->paymentTotals()['outstanding'];

        return view('admin.dashboard', compact('user', 'totals', 'pendingBookings', 'conflictDates', 'upcomingBookings', 'unreadMessages', 'outstandingBalance'));
    }
}
