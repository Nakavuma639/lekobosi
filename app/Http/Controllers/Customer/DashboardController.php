<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the signed-in customer their own bookings.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $bookings = $user->bookings()
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->paginate(10);

        return view('customer.dashboard', compact('user', 'bookings'));
    }
}
