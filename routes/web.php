<?php

use App\Http\Controllers\OrganizerEventController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserManagementController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [RegistrationController::class, 'index'])->name('dashboard');
    Route::post('/registrations/discount-preview', [RegistrationController::class, 'previewDiscount'])->name('registrations.discount-preview');
    Route::post('/registrations', [RegistrationController::class, 'store'])->name('registrations.store');
    Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show');

    Route::prefix('organizer')->name('organizer.')->group(function () {
        Route::get('/events', [OrganizerEventController::class, 'index'])->name('events.index');
        Route::post('/events', [OrganizerEventController::class, 'store'])->name('events.store');
        Route::patch('/events/{event}', [OrganizerEventController::class, 'update'])->name('events.update');
        Route::post('/events/{event}/tickets', [OrganizerEventController::class, 'storeTicket'])->name('events.tickets.store');
        Route::patch('/events/{event}/tickets/{ticket}', [OrganizerEventController::class, 'updateTicket'])->name('events.tickets.update');
        Route::post('/events/{event}/add-ons', [OrganizerEventController::class, 'storeAddOn'])->name('events.add-ons.store');
        Route::patch('/events/{event}/add-ons/{addOn}', [OrganizerEventController::class, 'updateAddOn'])->name('events.add-ons.update');
        Route::post('/events/{event}/discounts', [OrganizerEventController::class, 'storeDiscount'])->name('events.discounts.store');
        Route::patch('/events/{event}/discounts/{discountCode}', [OrganizerEventController::class, 'updateDiscount'])->name('events.discounts.update');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// User Management Routes
Route::get('user/index', [UserManagementController::class, 'index'])->name('user.index');
    //
    Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
    Route::post('/users/store', [UserManagementController::class, 'store'])->name('users.store');

require __DIR__.'/auth.php';
