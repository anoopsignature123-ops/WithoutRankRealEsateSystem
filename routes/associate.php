<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssociatePanel\AssociateAuthController;
use App\Http\Controllers\AssociatePanel\AssociateDashboardController;
use App\Http\Controllers\AssociatePanel\AssociateProfileController;
use App\Http\Controllers\AssociatePanel\AssociateRegistrationController;
use App\Http\Controllers\AssociatePanel\BookingDetailController;
use App\Http\Controllers\AssociatePanel\CustomerLedgerController;
use App\Http\Controllers\AssociatePanel\PlotAvilabilityController;
use App\Http\Controllers\AssociatePanel\SupportController;
use App\Http\Controllers\AssociatePanel\TeamController;
use App\Http\Controllers\AssociatePanel\AssociateCommissionController;

// ------------------Associate Routes-------------
Route::prefix('associate-panel')->name('associate-panel.')->group(function () {
    Route::middleware('guest:associate')->group(function () {
        Route::get('/login', [AssociateAuthController::class, 'loginForm'])->name('login');
        Route::post('/login', [AssociateAuthController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('login.submit');
    });
    Route::middleware('auth:associate')->group(function () {
        Route::get('dashboard', [AssociateDashboardController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [AssociateAuthController::class, 'logout'])->name('logout');

        Route::controller(AssociateProfileController::class)->group(function () {
            Route::get('view-profile', 'viewProfile')->name('view-profile');
            Route::get('edit-profile', 'editProfile')->name('edit-profile');
            Route::post('update-profile', 'updateProfile')->name('update-profile');
            Route::get('change-password', 'changePassword')->name('change-password');
            Route::post('update-password', 'updatePassword')->name('update-password');
            Route::get('/welcome-letter', 'downloadPdf')->name('welcome-letter');
        });

        Route::controller(AssociateRegistrationController::class)->group(function () {
            Route::get('register', 'create')->name('register-create');
            Route::post('register', 'store')->name('register-store');
            Route::get('associate/{id}/edit', 'edit')->name('associate-edit');
            Route::put('associate/{id}/update', 'update')->name('associate-update');
            Route::delete('associate-delete/{id}', 'associateDelete')->name('associate-delete');
            Route::get('associate-detail', 'associateDatail')->name('associate-detail');
            Route::get('export-associate', 'associateExport')->name('export-associate');
            Route::get('associate/{id}/download-pdf', 'downloadPdf')->name('associat-download-pdf');
        });
        Route::controller(BookingDetailController::class)->group(function () {
            Route::get('booking-detail', 'index')->name('booking-detail');
            Route::get('get-blocks/{projectId}', 'getBlocks')->name('booking.blocks');
            Route::get('get-plots/{blockId}', 'getPlots')->name('booking.plots');
            Route::get('get-booking-by-plot/{plotId}', 'getBookingByPlot')->name('booking.by.plot');

            Route::get('team-business-report', 'teamBusinessReport')->name('team-business-report');
            Route::get('due-emi-amount', 'dueEmiAmount')->name('due-emi-amount');
        });
        Route::controller(CustomerLedgerController::class)->group(function () {
            Route::get('customer-ledger', 'customerLedger')->name('customer-ledger');
        });
        Route::controller(TeamController::class)->group(function () {
            Route::get('my-tree', 'myTree')->name('my-tree');
            Route::get('my-downline', 'myDownline')->name('my-downline');
            Route::get('my-direct', 'myDirect')->name('my-direct');
        });
        Route::controller(PlotAvilabilityController::class)->group(function () {
            Route::get('plot-avilable', 'plotAvilable')->name('plot-avilable');
        });
        Route::controller(SupportController::class)->group(function () {
            Route::get('/support', 'index')->name('support.index');
            Route::post('/support', 'store')->name('support.store');
        });

        Route::controller(AssociateCommissionController::class)->group(function () {
            Route::get('payout-details', 'index')->name('payout-details');

            Route::get('payout-details/export/excel', 'exportExcel')->name('payout-details.export.excel');
            Route::get('payout-details/export/pdf', 'exportPdf')->name('payout-details.export.pdf');

            Route::get('payout-details/{commission}/export/excel', 'exportSingleExcel')->name('payout-details.single.excel');
            Route::get('payout-details/{commission}/export/pdf', 'exportSinglePdf')->name('payout-details.single.pdf');
        });
    });
});
