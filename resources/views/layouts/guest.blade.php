<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Lekobosi') }}</title>

        @include('layouts.partials.fonts')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-stone-800 antialiased">
        <div class="grid min-h-screen lg:grid-cols-2">
            <div class="relative hidden overflow-hidden bg-stone-900 lg:block">
                <img src="{{ asset('images/chef-platter.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-70">
                <div class="absolute inset-0 bg-gradient-to-t from-brand-900/90 via-brand-900/30 to-transparent"></div>
                <div class="absolute bottom-0 p-12">
                    <p class="max-w-md font-display text-3xl font-semibold leading-tight text-white">
                        Book, track and pay for your event in one place.
                    </p>
                    <p class="mt-4 text-brand-100">Lekobosi Restaurant &amp; Outside Catering, Kyankwanzi</p>
                </div>
            </div>

            <div class="flex flex-col px-6 py-10 sm:px-12">
                <a href="{{ route('home') }}" class="flex items-center gap-3 self-start">
                    <x-application-logo class="h-14 w-14" />
                    <span class="font-display text-lg font-semibold text-brand-700">Lekobosi</span>
                </a>

                <div class="flex flex-1 items-center">
                    <div class="mx-auto w-full max-w-md py-12">
                        {{ $slot }}
                    </div>
                </div>

                <a href="{{ route('home') }}" class="self-start text-sm text-stone-500 hover:text-brand-700">Back to the website</a>
            </div>
        </div>
    </body>
</html>
