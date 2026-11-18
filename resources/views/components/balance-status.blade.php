@props(['status'])

@php
    $classes = match ($status) {
        \App\Enums\BalanceStatus::Unpaid => 'bg-stone-100 text-stone-700',
        \App\Enums\BalanceStatus::Partial => 'bg-saffron-100 text-saffron-700',
        \App\Enums\BalanceStatus::Paid => 'bg-emerald-100 text-emerald-800',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {$classes}"]) }}>{{ $status->label() }}</span>
