<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    {{ $head ?? '' }}
</head>
<body>
@if ($chrome === 'public')
    @include('layouts.partials.site-header')
@elseif ($chrome === 'admin')
    @include('layouts.partials.admin-header')
@endif

{{ $slot }}

@if ($chrome === 'public')
    @include('layouts.partials.site-footer')
@elseif ($chrome === 'auth')
    <footer class="footer">
        <img src="{{ asset('images/logo.jpeg') }}" alt="Lekobosi Logo" width="85">
        <p>Kyankwanzi, Uganda</p>
    </footer>
@else
    <footer class="footer">
        <div class="footer-bottom">
            <p>
                &copy; 2026 Lekobosi Restaurant and Outside Catering Services.
                All rights reserved.
            </p>
        </div>
    </footer>
@endif

<script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
