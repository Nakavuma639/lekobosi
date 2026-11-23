@props(['count' => 0])

@if ($count > 0)
    <span {{ $attributes->merge(['class' => 'inline-flex min-w-5 items-center justify-center rounded-full bg-brand-700 px-1.5 py-0.5 text-xs font-semibold leading-none text-white']) }}>
        {{ $count > 99 ? '99+' : $count }}<span class="sr-only"> new {{ \Illuminate\Support\Str::plural('request', $count) }}</span>
    </span>
@endif
