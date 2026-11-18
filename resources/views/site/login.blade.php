<x-site-layout title="Login | Lekobosi" chrome="auth">
<section class="section">
    <p class="eyebrow">WELCOME BACK</p>
    <h2>Login to Your Account</h2>

    <form class="form-box small-box login-form" method="POST" action="{{ route('login') }}">
        @csrf

        @if (session('status'))
            <p class="form-success">{{ session('status') }}</p>
        @endif

        <label>Email Address
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="Enter your email">
        </label>
        <x-site.field-error field="email" />

        <label>Password
            <input type="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
        </label>
        <x-site.field-error field="password" />

        <button type="submit" class="button" style="margin-top: 6px;">Login</button>

        <p>New customer? <a href="{{ route('register') }}">Create an account</a></p>
        <p>Staff member? Use your authorised staff account.</p>
    </form>
</section>
</x-site-layout>
