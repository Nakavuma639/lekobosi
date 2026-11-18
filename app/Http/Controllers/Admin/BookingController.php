<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingAvailability;
use App\Support\PhoneNumber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Sortable columns and their default direction.
     *
     * @var array<string, string>
     */
    private const SORTS = [
        'event_date' => 'asc',
        'created_at' => 'desc',
    ];

    /**
     * List bookings with filters for status, search text, date range and source.
     */
    public function index(Request $request, BookingAvailability $availability): View
    {
        Gate::authorize('viewAny', Booking::class);

        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(BookingStatus::class)],
            'source' => ['nullable', Rule::enum(BookingSource::class)],
            'q' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'sort' => ['nullable', Rule::in(array_keys(self::SORTS))],
        ]);

        $sort = $filters['sort'] ?? 'created_at';

        $bookings = Booking::query()
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['source'] ?? null, fn (Builder $query, string $source) => $query->where('source', $source))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->where('event_date', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->where('event_date', '<=', $to))
            ->when($filters['q'] ?? null, fn (Builder $query, string $search) => $this->applySearch($query, $search))
            ->orderBy($sort, self::SORTS[$sort])
            ->orderBy('id', self::SORTS[$sort])
            ->paginate(20)
            ->withQueryString();

        $conflictDates = $availability->fullDatesAmong(
            $bookings->getCollection()->where('status', BookingStatus::Pending)->pluck('event_date')
        );

        $statuses = BookingStatus::cases();
        $sources = BookingSource::cases();

        return view('admin.bookings.index', compact('bookings', 'filters', 'conflictDates', 'statuses', 'sources'));
    }

    /**
     * Show a booking with its full history and any date conflict.
     */
    public function show(Request $request, Booking $booking, BookingAvailability $availability): View
    {
        Gate::authorize('view', $booking);

        $booking->load(['statusHistories.changer', 'decider', 'creator', 'user']);

        $hasDateConflict = $availability->hasDateConflict($booking);
        $otherBookingsOnDate = Booking::query()
            ->where('event_date', $booking->event_date->toDateString())
            ->whereKeyNot($booking->id)
            ->whereIn('status', [BookingStatus::Pending, BookingStatus::Approved])
            ->orderBy('status')
            ->get(['id', 'reference', 'customer_name', 'status', 'guest_count']);
        $canDecide = $request->user()->can('decide', $booking);

        return view('admin.bookings.show', compact('booking', 'hasDateConflict', 'otherBookingsOnDate', 'canDecide'));
    }

    /**
     * Match the search text against reference, customer name or phone.
     *
     * @param  Builder<Booking>  $query
     */
    private function applySearch(Builder $query, string $search): void
    {
        $search = trim($search);
        $phone = PhoneNumber::normalize($search);
        $digits = preg_replace('/\D/', '', $search);

        $query->where(function (Builder $query) use ($search, $phone, $digits): void {
            $query->where('reference', 'like', '%'.mb_strtoupper($search).'%')
                ->orWhere('customer_name', 'like', "%{$search}%");

            if ($phone !== null) {
                $query->orWhere('customer_phone', $phone)->orWhere('whatsapp_number', $phone);
            } elseif (strlen($digits) >= 4 && strlen($digits) === strlen(preg_replace('/[\s\-+()]/', '', $search))) {
                // A partial number such as "123456": match the end of stored phones.
                $query->orWhere('customer_phone', 'like', "%{$digits}%");
            }
        });
    }
}
