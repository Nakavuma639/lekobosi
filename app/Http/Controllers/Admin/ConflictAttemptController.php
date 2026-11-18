<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingConflictAttempt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ConflictAttemptController extends Controller
{
    /**
     * Requests turned away because the date was full: lost demand the owner
     * may want to call back about.
     */
    public function __invoke(Request $request): View
    {
        Gate::authorize('viewAny', BookingConflictAttempt::class);

        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $attempts = BookingConflictAttempt::query()
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->where('requested_date', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->where('requested_date', '<=', $to))
            ->latest()
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.conflict-attempts', compact('attempts', 'filters'));
    }
}
