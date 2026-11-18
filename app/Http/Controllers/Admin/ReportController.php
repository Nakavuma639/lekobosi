<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\ReportBuilder;
use App\Support\CsvExport;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Bookings by event date: counts by status, type and source, plus the list.
     * Owner and staff can view; only the owner can download CSV.
     */
    public function bookings(Request $request, ReportBuilder $reports): View|StreamedResponse
    {
        Gate::authorize('view-reports');
        [$from, $to, $csv] = $this->range($request);

        $report = $reports->bookings($from, $to);

        if ($csv) {
            Gate::authorize('export-reports');

            return CsvExport::download(
                "lekobosi-bookings-{$from->toDateString()}-to-{$to->toDateString()}.csv",
                ['Reference', 'Event date', 'Event type', 'Customer', 'Phone', 'Email', 'Location', 'Guests', 'Source', 'Status', 'Quote (UGX)', 'Paid (UGX)', 'Balance (UGX)', 'Received'],
                $report['bookings']->map(fn (Booking $booking) => [
                    $booking->reference,
                    $booking->event_date->toDateString(),
                    $booking->event_type->label(),
                    $booking->customer_name,
                    $booking->customer_phone,
                    $booking->customer_email,
                    $booking->event_location,
                    $booking->guest_count,
                    $booking->source->label(),
                    $booking->status->label(),
                    $booking->quoted_amount,
                    $booking->amountPaid(),
                    $booking->balance(),
                    $booking->created_at->toDateTimeString(),
                ]),
            );
        }

        return view('admin.reports.bookings', compact('report', 'from', 'to'));
    }

    /**
     * Payments received in the range, with totals by method.
     */
    public function payments(Request $request, ReportBuilder $reports): View|StreamedResponse
    {
        Gate::authorize('view-reports');
        [$from, $to, $csv] = $this->range($request);

        $report = $reports->payments($from, $to);

        if ($csv) {
            Gate::authorize('export-reports');

            return CsvExport::download(
                "lekobosi-payments-{$from->toDateString()}-to-{$to->toDateString()}.csv",
                ['Receipt', 'Received', 'Booking', 'Customer', 'Amount (UGX)', 'Method', 'Reference', 'Recorded by', 'Status', 'Void reason'],
                $report['payments']->map(fn (Payment $payment) => [
                    $payment->receipt_number,
                    $payment->received_at->toDateTimeString(),
                    $payment->booking->reference,
                    $payment->booking->customer_name,
                    $payment->amount,
                    $payment->method->label(),
                    $payment->reference,
                    $payment->recorder?->name,
                    $payment->status->label(),
                    $payment->void_reason,
                ]),
            );
        }

        return view('admin.reports.payments', compact('report', 'from', 'to'));
    }

    /**
     * The date range from the query string; this month by default.
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable, 2: bool}
     */
    private function range(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'format' => ['nullable', 'in:csv'],
        ]);

        $from = isset($validated['from']) ? CarbonImmutable::parse($validated['from']) : CarbonImmutable::today()->startOfMonth();
        $to = isset($validated['to']) ? CarbonImmutable::parse($validated['to']) : $from->endOfMonth()->startOfDay();

        return [$from, $to, ($validated['format'] ?? null) === 'csv'];
    }
}
