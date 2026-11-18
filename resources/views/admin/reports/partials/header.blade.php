{{-- Expects $current ('bookings'|'payments'), $from and $to from the including view. --}}
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-semibold">Reports</h1>
        <p class="mt-1 text-sm text-stone-500">{{ $from->format('j M Y') }} to {{ $to->format('j M Y') }}</p>
    </div>
    <nav class="flex rounded-full border border-stone-300 bg-white p-1 text-sm" aria-label="Report type">
        @foreach (['bookings' => 'Bookings', 'payments' => 'Payments'] as $report => $label)
            <a href="{{ route("admin.reports.{$report}", ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}"
               @class(['rounded-full px-4 py-1.5 font-medium', 'bg-brand-700 text-white' => $current === $report, 'text-stone-600' => $current !== $report])
               @if ($current === $report) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>
</div>

<form method="get" action="{{ route("admin.reports.{$current}") }}" class="mt-6 flex flex-wrap items-end gap-4">
    <div>
        <label for="from" class="field-label">{{ $current === 'bookings' ? 'Events from' : 'Received from' }}</label>
        <input id="from" name="from" type="date" value="{{ $from->toDateString() }}" class="field-input">
    </div>
    <div>
        <label for="to" class="field-label">to</label>
        <input id="to" name="to" type="date" value="{{ $to->toDateString() }}" class="field-input">
        @error('to') <p class="field-error">{{ $message }}</p> @enderror
    </div>
    <button type="submit" class="btn-brand py-2.5">Run report</button>
    @can('export-reports')
        <a href="{{ route("admin.reports.{$current}", ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'format' => 'csv']) }}" class="btn-outline ml-auto py-2.5">Download CSV</a>
    @endcan
</form>
