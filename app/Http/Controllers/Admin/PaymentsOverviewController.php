<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BalanceStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\BookingStatistics;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentsOverviewController extends Controller
{
    /**
     * Confirmed payments total for the booking row, as SQL. A constant string;
     * the status value is passed as a binding.
     */
    private const PAID_SUM_SQL = '(SELECT COALESCE(SUM(payments.amount), 0) FROM payments WHERE payments.booking_id = bookings.id AND payments.status = ?)';

    /**
     * The "Payments and Balances" screen: approved bookings with their quote,
     * amount paid, balance and payment status.
     */
    public function __invoke(Request $request, BookingStatistics $statistics): View
    {
        Gate::authorize('viewAny', Payment::class);

        $filters = $request->validate([
            'balance' => ['nullable', Rule::enum(BalanceStatus::class)],
            'outstanding' => ['nullable', 'boolean'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $outstandingOnly = $request->boolean('outstanding');
        $balanceStatus = isset($filters['balance']) ? BalanceStatus::from($filters['balance']) : null;
        $confirmed = [PaymentStatus::Confirmed->value];
        $paid = self::PAID_SUM_SQL;

        $bookings = Booking::query()
            ->where('status', BookingStatus::Approved)
            ->withAmountPaid()
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->where('event_date', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->where('event_date', '<=', $to))
            ->when($outstandingOnly || $balanceStatus, fn (Builder $query) => $query->whereNotNull('quoted_amount'))
            ->when($outstandingOnly, fn (Builder $query) => $query->whereRaw("bookings.quoted_amount > {$paid}", $confirmed))
            ->when($balanceStatus === BalanceStatus::Unpaid, fn (Builder $query) => $query->whereRaw("{$paid} = 0", $confirmed))
            ->when($balanceStatus === BalanceStatus::Partial, fn (Builder $query) => $query
                ->whereRaw("{$paid} > 0", $confirmed)
                ->whereRaw("{$paid} < bookings.quoted_amount", $confirmed))
            ->when($balanceStatus === BalanceStatus::Paid, fn (Builder $query) => $query->whereRaw("{$paid} >= bookings.quoted_amount", $confirmed))
            ->orderBy('event_date')
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();

        $totals = $statistics->paymentTotals();
        $unquotedCount = Booking::query()->where('status', BookingStatus::Approved)->whereNull('quoted_amount')->count();
        $balanceStatuses = BalanceStatus::cases();

        return view('admin.payments.index', compact('bookings', 'filters', 'outstandingOnly', 'totals', 'unquotedCount', 'balanceStatuses'));
    }
}
