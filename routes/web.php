<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Customer;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrackBookingController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'site.home')->name('home');
Route::view('/about', 'site.about')->name('about');
Route::view('/services', 'site.services')->name('services');
Route::get('/booking', [BookingController::class, 'create'])->name('bookings.create');
Route::post('/booking', [BookingController::class, 'store'])
    ->middleware('throttle:bookings')
    ->name('bookings.store');
Route::get('/booking/submitted', [BookingController::class, 'confirmation'])->name('bookings.confirmation');
Route::get('/availability', AvailabilityController::class)
    ->middleware('throttle:availability')
    ->name('availability');
Route::get('/track-booking', TrackBookingController::class)
    ->middleware('throttle:tracking')
    ->name('bookings.track');
Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('contact.store');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'role:customer'])
    ->prefix('account')
    ->name('customer.')
    ->group(function () {
        Route::get('/', Customer\DashboardController::class)->name('dashboard');
        Route::get('/bookings/{booking}', [Customer\BookingController::class, 'show'])->name('bookings.show');
    });

Route::middleware(['auth', 'verified', 'role:admin,staff'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
        Route::get('/bookings', [Admin\BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/create', [Admin\BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [Admin\BookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{booking}', [Admin\BookingController::class, 'show'])->name('bookings.show');
        Route::get('/bookings/{booking}/edit', [Admin\BookingController::class, 'edit'])->name('bookings.edit');
        Route::patch('/bookings/{booking}', [Admin\BookingController::class, 'update'])->name('bookings.update');
        Route::patch('/bookings/{booking}/status', [Admin\BookingStatusController::class, 'update'])
            ->middleware('role:admin')
            ->name('bookings.status.update');
        Route::patch('/bookings/{booking}/quote', [Admin\BookingQuoteController::class, 'update'])->name('bookings.quote.update');
        Route::post('/bookings/{booking}/payments', [Admin\PaymentController::class, 'store'])->name('bookings.payments.store');
        Route::get('/payments', Admin\PaymentsOverviewController::class)->name('payments.index');
        Route::get('/payments/{payment}/receipt', [Admin\PaymentController::class, 'receipt'])->name('payments.receipt');
        Route::post('/payments/{payment}/void', [Admin\PaymentController::class, 'void'])
            ->middleware('role:admin')
            ->name('payments.void');
        Route::get('/calendar', Admin\CalendarController::class)->name('calendar');
        Route::get('/reports/bookings', [Admin\ReportController::class, 'bookings'])->name('reports.bookings');
        Route::get('/reports/payments', [Admin\ReportController::class, 'payments'])->name('reports.payments');

        Route::middleware('role:admin')->group(function () {
            Route::get('/conflict-attempts', Admin\ConflictAttemptController::class)->name('conflict-attempts');
            Route::get('/staff', [Admin\StaffController::class, 'index'])->name('staff.index');
            Route::get('/staff/create', [Admin\StaffController::class, 'create'])->name('staff.create');
            Route::post('/staff', [Admin\StaffController::class, 'store'])->name('staff.store');
            Route::patch('/staff/{user}', [Admin\StaffController::class, 'update'])->name('staff.update');
            Route::post('/staff/{user}/password-link', [Admin\StaffController::class, 'sendPasswordLink'])
                ->middleware('throttle:password-links')
                ->name('staff.password-link');
        });
        Route::get('/messages', [Admin\ContactMessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{contactMessage}', [Admin\ContactMessageController::class, 'show'])->name('messages.show');
        Route::patch('/messages/{contactMessage}/read', [Admin\ContactMessageController::class, 'markAsRead'])
            ->middleware('role:admin')
            ->name('messages.read');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
