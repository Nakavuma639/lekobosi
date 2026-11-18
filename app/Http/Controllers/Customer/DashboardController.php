<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the signed-in customer their account home.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('customer.dashboard', compact('user'));
    }
}
