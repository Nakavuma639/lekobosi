@props(['status'])

@php
    $classes = match ($status) {
        \App\Enums\BookingStatus::Pending => 'bg-saffron-100 text-saffron-700',
        \App\Enums\BookingStatus::Approved => 'bg-emerald-100 text-emerald-800',
        \App\Enums\BookingStatus::Rejected => 'bg-brand-100 text-brand-800',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-3 py-1 text-sm font-semibold {$classes}"]) }}>{{ $status->label() }}</span>
