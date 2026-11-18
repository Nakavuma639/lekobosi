<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the back-office dashboard to the owner and staff.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $customerCount = User::query()->where('role', UserRole::Customer)->count();
        $staffCount = User::query()->where('role', UserRole::Staff)->where('is_active', true)->count();

        return view('admin.dashboard', compact('user', 'customerCount', 'staffCount'));
    }
}
