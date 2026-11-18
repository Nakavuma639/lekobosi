<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('site.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request, RegisterCustomer $registerCustomer): RedirectResponse
    {
        $user = $registerCustomer->handle(
            name: $request->validated('name'),
            email: $request->validated('email'),
            phone: $request->validated('phone'),
            password: $request->validated('password'),
        );

        Auth::login($user);

        $request->session()->regenerate();

        return redirect(route('dashboard', absolute: false))
            ->with('status', 'Welcome to Lekobosi! Your account has been created.');
    }
}
