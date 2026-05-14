<?php

use App\Http\Controllers\AssociateController;
use App\Http\Controllers\AssociateTreeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\CancelBookingController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerBookingController;
use App\Http\Controllers\CustomerListController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesignationRankController;
use App\Http\Controllers\DevelopmentController;
use App\Http\Controllers\DirectAssociateController;
use App\Http\Controllers\EmiPaymentController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\OneTimePaymentController;
use App\Http\Controllers\PlcRateController;
use App\Http\Controllers\PlotDetailController;
use App\Http\Controllers\PlotPaymentController;
use App\Http\Controllers\PlotRateController;
use App\Http\Controllers\PlotRegistryController;
use App\Http\Controllers\PlotTypeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectManipulationController;
use App\Http\Controllers\ReceiptReprintController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])
    ->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
    ->name('password.email');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])
    ->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])
    ->name('password.update');

Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('company', CompanyController::class);
    Route::resource('projects', ProjectController::class);
    Route::resource('blocks', BlockController::class);
    Route::resource('plot-types', PlotTypeController::class);
    Route::resource('plot-details', PlotDetailController::class);
    Route::controller(PlotDetailController::class)->group(function () {
        Route::get('get-project-data/{id}', 'getProjectData')->name('get.project.data');
        Route::get('plot-details-export', 'export')->name('plot-details.export');
        Route::get('get-project-plots/{project}', 'getProjectPlots');
    });
    Route::resource('plot-rates', PlotRateController::class);
    Route::get('get-project-blocks/{projectId}', [PlotRateController::class, 'getProjectBlocks'])
        ->name('get.project.blocks');
    Route::resource('plc-rates', PlcRateController::class);
    Route::resource('developments', DevelopmentController::class);

    Route::controller(ProjectManipulationController::class)->group(function () {
        Route::get('project-manipulation', 'index')->name('project.manipulation.index');
        Route::post('project-manipulation/update-status', 'updateStatus')->name('project.manipulation.update.status');
        Route::get('get-project-plots-data/{projectId}', 'getPlotsByProject');
        Route::get('project-manipulation-export', 'export')->name('project.manipulation.export');
    });

    Route::resource('designations', DesignationRankController::class);
    Route::resource('associate', AssociateController::class);
    Route::get('get-sponsor-ranks/{associateId}', [AssociateController::class, 'getSponsorRanks'])
        ->name('get.sponsor.ranks');
    Route::get('associate-export', [AssociateController::class, 'export'])->name('associate.export');
    Route::get('direct-associate', [DirectAssociateController::class, 'index'])->name('direct-associate');
    Route::get('direct-associate-export', [DirectAssociateController::class, 'export'])->name('direct-associate.export');
    Route::get('associate-downline', [DirectAssociateController::class, 'associateDownline'])->name('associate-downline');
    Route::get('associate-downline/export', [DirectAssociateController::class, 'exportDownline'])
        ->name('associate-downline.export');
    Route::get('associate-tree', [AssociateTreeController::class, 'index'])->name('associate-tree');
    Route::resource('customer-booking', CustomerBookingController::class);
    Route::resource('cancel-booking', CancelBookingController::class)->only(['index', 'store']);
    Route::get('customer-list', [CustomerListController::class, 'index'])->name('customer-list.index');
    Route::get('edit-plot-booking', [CustomerListController::class, 'editPlotBooking'])->name('edit-plot-booking.index');
    Route::get('/get-blocks/{projectId}', [CustomerBookingController::class, 'getBlocks']);
    Route::get('/get-plots/{blockId}/{customerId?}', [CustomerBookingController::class, 'getPlots']);

    Route::controller(PlotPaymentController::class)->group(function () {
        Route::get('edit-payment-details', 'index')->name('edit-payment-details.index');
        Route::get('edit-payment-details/{id}/edit', 'edit')->name('edit-payment-details.edit');
        Route::put('edit-payment-details/{id}', 'update')->name('edit-payment-details.update');
    });
    Route::controller(PlotRegistryController::class)->group(function () {
        Route::get('/plot-registry', 'index')->name('plot-registry.index');
        Route::post('/plot-registry', 'store')->name('plot-registry.store');
        Route::get('/plot-registry/blocks/{project}', 'getBlocks')->name('plot-registry.blocks');
        Route::get('/plot-registry/plots/{block}', 'getPlots')->name('plot-registry.plots');
        Route::get('/plot-registry/booking/{plot}', 'getBookingData')->name('plot-registry.booking');
    });

    Route::controller(ReceiptReprintController::class)->group(function () {
        Route::get('/receipt-reprint', 'index')->name('receipt-reprint.index');
        Route::post('/receipt-reprint/search', 'search')->name('receipt-reprint.search');
        Route::get('/receipt-reprint/download/{payment}', 'download')->name('receipt-reprint.download');
        Route::get('/receipt-reprint/customers/{plot}', 'getCustomersByPlot')->name('receipt-reprint.customers');
    });
    Route::controller(OneTimePaymentController::class)->group(function () {
        Route::get('/one-time-payment', 'index')->name('one-time-payment.index');
        Route::post('/one-time-payment', 'store')->name('one-time-payment.store');
        Route::get('/one-time-payment/blocks/{project}', 'getBlocks')->name('one-time-payment.blocks');
        Route::get('/one-time-payment/plots/{block}', 'getPlots')->name('one-time-payment.plots');
        Route::get('/one-time-payment/details/{plot}', 'getBookingDetails')->name('one-time-payment.details');
    });

    Route::controller(EmiPaymentController::class)->group(function () {
        Route::get('/emi-payment', 'index')->name('emi-payment.index');
        Route::get('/emi-payment/blocks/{id}', 'getBlocks')->name('emi-payment.blocks');
        Route::get('/emi-payment/plots/{id}', 'getPlots')->name('emi-payment.plots');
        Route::get('/emi-payment/details/{id}', 'getBookingDetails')->name('emi-payment.details');
        Route::post('/emi-payment/store', 'store')->name('emi-payment.store');
    });
});
