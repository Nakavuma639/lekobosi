<x-site-layout title="Register | Lekobosi" chrome="auth">
<section class="section">
    <p class="eyebrow">JOIN LEKOBOSI</p>
    <h2>Create Your Customer Account</h2>

    <form class="form-box small-box registerForm" name="registerForm" method="POST" action="{{ route('register') }}">
        @csrf

        <label>Full Name
            <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Enter your full name">
        </label>
        <x-site.field-error field="name" />

        <label>Email Address
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="Enter your email">
        </label>
        <x-site.field-error field="email" />

        <label>Phone Number
            <input type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="Enter your phone number">
        </label>
        <x-site.field-error field="phone" />

        <label>Password
            <input type="password" minlength="8" name="password" required autocomplete="new-password" placeholder="At least 8 characters">
        </label>
        <x-site.field-error field="password" />

        <label>Confirm Password
            <input type="password" minlength="8" name="confirm_password" required autocomplete="new-password" placeholder="Re-enter password">
        </label>
        <x-site.field-error field="confirm_password" />

        <button type="submit" class="button" style="margin-top: 7px;">Create Account</button>

        <p>Already registered? <a href="{{ route('login') }}">Login here</a></p>
    </form>
</section>
</x-site-layout>
