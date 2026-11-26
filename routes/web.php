<?php

use App\Http\Controllers\AdminOperationsController;
use App\Http\Controllers\EventManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [RegistrationController::class, 'index'])->name('dashboard');
    Route::get('/events', [RegistrationController::class, 'events'])->name('events.index');
    Route::post('/registrations/discount-preview', [RegistrationController::class, 'previewDiscount'])->name('registrations.discount-preview');
    Route::post('/registrations', [RegistrationController::class, 'store'])->name('registrations.store');
    Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminOperationsController::class, 'dashboard'])->name('dashboard');
        Route::get('/events', [EventManagementController::class, 'index'])->name('events.index');
        Route::post('/events', [EventManagementController::class, 'store'])->name('events.store');
        Route::patch('/events/{event}', [EventManagementController::class, 'update'])->name('events.update');
        Route::post('/events/{event}/tickets', [EventManagementController::class, 'storeTicket'])->name('events.tickets.store');
        Route::patch('/events/{event}/tickets/{ticket}', [EventManagementController::class, 'updateTicket'])->name('events.tickets.update');
        Route::post('/events/{event}/add-ons', [EventManagementController::class, 'storeAddOn'])->name('events.add-ons.store');
        Route::patch('/events/{event}/add-ons/{addOn}', [EventManagementController::class, 'updateAddOn'])->name('events.add-ons.update');
        Route::post('/events/{event}/discounts', [EventManagementController::class, 'storeDiscount'])->name('events.discounts.store');
        Route::patch('/events/{event}/discounts/{discountCode}', [EventManagementController::class, 'updateDiscount'])->name('events.discounts.update');
        Route::get('/ticketing', [AdminOperationsController::class, 'ticketing'])->name('ticketing.index');
        Route::get('/attendance', [AdminOperationsController::class, 'attendance'])->name('attendance.index');
        Route::post('/attendance/{ticket}/check-in', [AdminOperationsController::class, 'checkIn'])->name('attendance.check-in');
    });

    Route::middleware('admin')->group(function () {
        Route::get('user/index', [UserManagementController::class, 'index'])->name('user.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users/store', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
