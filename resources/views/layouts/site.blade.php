<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description ?? 'Lekobosi Restaurant and Outside Catering Services, Kyankwanzi, Uganda. Catering for weddings, introductions, parties and corporate events.' }}">

    @include('layouts.partials.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-white font-sans text-stone-700 antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-white focus:px-4 focus:py-2">Skip to content</a>

    @include('layouts.partials.site-header')

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    @include('layouts.partials.site-footer')
</body>
</html>
