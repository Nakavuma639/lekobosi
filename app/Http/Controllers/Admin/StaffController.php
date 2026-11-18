<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Staff\CreateStaffAccount;
use App\Actions\Staff\SetStaffAccountActive;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class StaffController extends Controller
{
    /**
     * The owner's list of staff and admin accounts.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', User::class);

        $accounts = User::query()
            ->whereIn('role', [UserRole::Admin, UserRole::Staff])
            ->orderByDesc('is_active')
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return view('admin.staff.index', compact('accounts'));
    }

    /**
     * Form for adding a staff member.
     */
    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('admin.staff.create');
    }

    /**
     * Create a staff account and email them a link to set their password.
     */
    public function store(StoreStaffRequest $request, CreateStaffAccount $createStaffAccount): RedirectResponse
    {
        ['user' => $staff, 'linkSent' => $linkSent] = $createStaffAccount->handle(
            name: $request->validated('name'),
            email: $request->validated('email'),
            phone: $request->validated('phone'),
        );

        return redirect()
            ->route('admin.staff.index')
            ->with('status', $linkSent
                ? "Account created. {$staff->name} has been emailed a link to set their password."
                : 'Account created, but the password email could not be sent. Use "Resend password link" once email is working.');
    }

    /**
     * Enable or disable a staff account.
     */
    public function update(Request $request, User $user, SetStaffAccountActive $setStaffAccountActive): RedirectResponse
    {
        Gate::authorize('update', $user);

        $validated = $request->validate(['is_active' => ['required', 'boolean']]);
        $staff = $setStaffAccountActive->handle($user, (bool) $validated['is_active']);

        return redirect()
            ->route('admin.staff.index')
            ->with('status', $staff->is_active
                ? "{$staff->name} can sign in again."
                : "{$staff->name}'s account is disabled and they have been signed out.");
    }

    /**
     * Email a staff member a fresh "set your password" link.
     */
    public function sendPasswordLink(User $user, CreateStaffAccount $createStaffAccount): RedirectResponse
    {
        Gate::authorize('update', $user);

        $status = match ($createStaffAccount->sendPasswordLinkWithStatus($user)) {
            Password::RESET_LINK_SENT => "A password link has been emailed to {$user->name}.",
            Password::RESET_THROTTLED => "A link was sent to {$user->name} less than a minute ago. Ask them to check their inbox and spam folder, or try again shortly.",
            default => 'The email could not be sent. Check the mail settings and try again in a few minutes.',
        };

        return redirect()->route('admin.staff.index')->with('status', $status);
    }
}
