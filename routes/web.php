<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Customer;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'site.home')->name('home');
Route::view('/about', 'site.about')->name('about');
Route::view('/services', 'site.services')->name('services');
Route::view('/booking', 'site.booking')->name('bookings.create');
Route::view('/track-booking', 'site.track-booking')->name('bookings.track');
Route::view('/contact', 'site.contact')->name('contact.create');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'role:customer'])
    ->prefix('account')
    ->name('customer.')
    ->group(function () {
        Route::get('/', Customer\DashboardController::class)->name('dashboard');
    });

Route::middleware(['auth', 'verified', 'role:admin,staff'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
