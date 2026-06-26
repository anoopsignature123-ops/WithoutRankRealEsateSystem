<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerPanle\CustomerAuthController;
use App\Http\Controllers\CustomerPanle\CustomerDashboardController;
use App\Http\Controllers\CustomerPanle\CustomerHistoryController;

Route::prefix('customer-panel')->name('customer-panel.')->group(function () {
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [CustomerAuthController::class, 'loginForm'])->name('login');
        Route::post('/login', [CustomerAuthController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('login.submit');
    });
    Route::middleware('auth:customer')->group(function () {
        Route::get('/dashboard', [CustomerDashboardController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

        Route::get('/profile', [CustomerHistoryController::class, 'profile'])->name('profile');
        Route::get('/manage-profile', [CustomerHistoryController::class, 'manageProfile'])->name('manage-profile');
        Route::post('/manage-profile', [CustomerHistoryController::class, 'updateManageProfile'])->name('manage-profile.update');
        Route::post('/manage-profile/password', [CustomerHistoryController::class, 'updatePassword'])
            ->name('manage-profile.password.update');
        Route::get('/booking-history', [CustomerHistoryController::class, 'bookingHistory'])->name('booking-history');
        Route::get('/payment-history', [CustomerHistoryController::class, 'paymentHistory'])->name('payment-history');
        Route::get('/payment-history/receipt/{payment}/download', [CustomerHistoryController::class, 'downloadReceipt'])
            ->name('payment-history.receipt.download');
        Route::get('/my-plot-booking', [CustomerHistoryController::class, 'myPlotBooking'])->name('my-plot-booking');
        Route::get('/support', [CustomerHistoryController::class, 'support'])->name('support');
        Route::post('/support', [CustomerHistoryController::class, 'supportStore'])->name('support.store');
    });
});
