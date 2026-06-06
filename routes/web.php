<?php

use App\Http\Controllers\AgentDetailReportController;
use App\Http\Controllers\AgentSummaryDetailsReportController;
use App\Http\Controllers\AssociateAdvanceController;
use App\Http\Controllers\AssociateAdvanceReportController;
use App\Http\Controllers\AssociateChainReportController;
use App\Http\Controllers\AssociateController;
use App\Http\Controllers\AssociateDirectReportController;
use App\Http\Controllers\AssociatePanel\AssociateAuthController;
use App\Http\Controllers\AssociatePanel\AssociateCommissionController;
use App\Http\Controllers\AssociatePanel\AssociateDashboardController;
use App\Http\Controllers\AssociatePanel\AssociateProfileController;
use App\Http\Controllers\AssociatePanel\AssociateRegistrationController;
use App\Http\Controllers\AssociatePanel\BookingDetailController;
use App\Http\Controllers\AssociatePanel\CustomerLedgerController;
use App\Http\Controllers\AssociatePanel\PlotAvilabilityController;
use App\Http\Controllers\AssociatePanel\SupportController;
use App\Http\Controllers\AssociatePanel\TeamController;
use App\Http\Controllers\AssociateTeamNewBookingDetailsReportController;
use App\Http\Controllers\AssociateTreeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\BookingLetterController;
use App\Http\Controllers\BouncedChequeDetailsReportController;
use App\Http\Controllers\BrokerController;
use App\Http\Controllers\CancelBookingController;
use App\Http\Controllers\CancelPlotBookingReportController;
use App\Http\Controllers\ChequeClearanceController;
use App\Http\Controllers\ChequeDetailsReportController;
use App\Http\Controllers\CommissionPayoutController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerBookingController;
use App\Http\Controllers\CustomerDetailReportController;
use App\Http\Controllers\CustomerLedgerReportController;
use App\Http\Controllers\CustomerListController;
use App\Http\Controllers\DailyCollectionReportController;
use App\Http\Controllers\DailyDuesReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesignationRankController;
use App\Http\Controllers\DevelopmentController;
use App\Http\Controllers\DirectAssociateController;
use App\Http\Controllers\DuesInstallmentReportController;
use App\Http\Controllers\EmiDueDateReportController;
use App\Http\Controllers\EmiDuesSummaryReportController;
use App\Http\Controllers\EmiDueStatusReportController;
use App\Http\Controllers\EmiPaymentController;
use App\Http\Controllers\EmiPaymentDetailsController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\EnquiryTypeController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\FullPaymentDetailsController;
use App\Http\Controllers\GenerateEmiController;
use App\Http\Controllers\NewBookingPaymentDetailsReportController;
use App\Http\Controllers\OneTimePaymentController;
use App\Http\Controllers\OneTimePaymentDueController;
use App\Http\Controllers\PaymentCollectionDuesSummaryReportController;
use App\Http\Controllers\PaymentTransferController;
use App\Http\Controllers\PlcRateController;
use App\Http\Controllers\PlotBookingDetailsController;
use App\Http\Controllers\PlotChangeController;
use App\Http\Controllers\PlotDetailController;
use App\Http\Controllers\PlotPaymentController;
use App\Http\Controllers\PlotRateController;
use App\Http\Controllers\PlotRegistryController;
use App\Http\Controllers\PlotTransferController;
use App\Http\Controllers\PlotTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectManipulationController;
use App\Http\Controllers\ReceiptReprintController;
use App\Http\Controllers\RegisteredPlotDetailsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\UpdateEmiDateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WithoutRegisteredPlotController;
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

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::controller(ProfileController::class)->group(function () {
        Route::get('profile', 'index')->name('profile');
        Route::post('profile/update', 'update')->name('profile.update');
        Route::get('change-password', 'changePasswordPage')->name('change-password');
        Route::post('change-password', 'changePassword')->name('change-password.update');
    });
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('company', CompanyController::class);
    Route::post('company/{id}/toggle-status', [CompanyController::class, 'toggleStatus'])->name('company.toggleStatus');
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
    Route::resource('associate-user', AssociateController::class)->names('associate')->parameters(['associate-user' => 'associate']);
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
        Route::get('/receipt-reprint/search', 'index');
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

    Route::controller(ChequeClearanceController::class)->group(function () {
        Route::get('/multiple-cheque-clearance', 'multipleChequeClearanceIndex')->name('multiple-cheque-clearance.index');
        Route::post('/multiple-cheque-clearance/store', 'storeMultipleChequeClearance')->name('multiple-cheque-clearance.store');
    });

    Route::controller(UpdateEmiDateController::class)->group(function () {
        Route::get('/update-emi-date', 'index')->name('update-emi-date.index');
        Route::post('/update-emi-date/store', 'store')->name('update-emi-date.store');
    });

    Route::controller(GenerateEmiController::class)->group(function () {
        Route::get('generate-emi', 'index')->name('generate-emi.index');
        Route::post('generate-emi/{id}', 'store')->name('generate-emi.store');
    });

    Route::controller(AssociateAdvanceController::class)->group(function () {
        Route::get('associate-advances', 'index')->name('associate-advances.index');
        Route::get('associate-advances/create', 'create')->name('associate-advances.create');
        Route::post('associate-advances', 'store')->name('associate-advances.store');
        Route::get('associate-advances/{id}/edit', 'edit')->name('associate-advances.edit');
        Route::put('associate-advances/{id}', 'update')->name('associate-advances.update');
        Route::delete('associate-advances/{id}', 'destroy')->name('associate-advances.destroy');
    });
    Route::controller(BookingLetterController::class)->prefix('booking-letter')->group(function () {
        Route::get('/', 'index')->name('booking-letter.index');
        Route::get('/allotement-pdf/{id}', 'allotementPdf')->name('booking-letter.allotement.pdf');
        Route::get('/agreement-pdf/{id}', 'agreementPdf')->name('booking-letter.agreement.pdf');
    });
    Route::controller(AgentDetailReportController::class)->group(function () {
        Route::get('agent-details-report', 'index')->name('agent-detail-report.index');
        Route::get('agent-details-report/export', 'export')->name('agent-detail-report.export');
    });
    Route::controller(CustomerDetailReportController::class)->group(function () {
        Route::get('customer-details-report', 'index')->name('customer-details-report.index');
        Route::get('customer-details-report/export', 'export')->name('customer-details-report.export');
    });

    Route::controller(EmiDueDateReportController::class)->group(function () {
        Route::get('emi-due-date-report', 'index')->name('emi-due-date-report.index');
        Route::get('emi-due-date-report/export', 'export')->name('emi-due-date-report.export');
    });

    Route::controller(EmiDueStatusReportController::class)->group(function () {
        Route::get('emi-due-status-report', 'index')->name('emi-due-status-report.index');
        Route::get('emi-due-status-report/export', 'export')->name('emi-due-status-report.export');
    });

    Route::controller(OneTimePaymentDueController::class)->group(function () {
        Route::get('one-time-payment-dues-report', 'index')->name('one-time-payment-dues-report.index');
        Route::get('one-time-payment-dues-report/export', 'export')->name('one-time-payment-dues-report.export');
    });

    Route::controller(PlotBookingDetailsController::class)->group(function () {
        Route::get('plot-booking-details-report', 'index')->name('plot-booking-details-report.index');
        Route::get('plot-booking-details-report/export', 'export')->name('plot-booking-details-report.export');
        Route::get('plot-booking-details-report/customer-details/{id}', 'getCustomerDetails')
            ->name('plot-booking-details-report.customer');
        Route::get('plot-booking-details-report/project-blocks/{id}', 'getProjectBlocks')
            ->name('plot-booking-details-report.blocks');
        Route::get('plot-booking-details-report/block-plc/{id}', 'getBlockPlcTypes')->name('plot-booking-details-report.plc');
    });

    Route::controller(EmiPaymentDetailsController::class)->group(function () {
        Route::get('emi-payment-details-report', 'index')->name('emi-payment-details-report.index');
        Route::get('emi-payment-details-report/export', 'export')->name('emi-payment-details-report.export');
        Route::get('get-customer-details/{id}', 'getCustomerDetails')->name('get-customer-details');
    });

    Route::controller(FullPaymentDetailsController::class)->group(function () {
        Route::get('full-payment-details-report', 'index')->name('full-payment-details-report.index');
        Route::get('full-payment-details-report/export', 'export')->name('full-payment-details-report.export');
        Route::get('get-customer-details/{id}', 'getCustomerDetails');
    });
    Route::controller(RegisteredPlotDetailsController::class)->group(function () {
        Route::get('registered-plot-details-report', 'index')->name('registered-plot-details-report.index');
        Route::get('registered-plot-details-report/export', 'export')->name('registered-plot-details-report.export');
        Route::get('registered-project-blocks/{id}', 'getProjectBlocks');
    });

    Route::controller(WithoutRegisteredPlotController::class)->group(function () {
        Route::get('without-registered-plot-report', 'index')->name('without-registered-plot-report.index');
        Route::get('without-registered-plot-report/export', 'export')->name('without-registered-plot-report.export');
    });

    Route::controller(AssociateDirectReportController::class)->group(function () {
        Route::get('/associate-direct-report', 'index')->name('associate-direct-report.index');
        Route::get('/associate-direct-report/export', 'export')->name('associate-direct-report.export');
    });

    Route::controller(AssociateChainReportController::class)->group(function () {
        Route::get('/associate-chain-report', 'index')->name('associate-chain-report.index');
        Route::get('/associate-chain-report/export', 'export')->name('associate-chain-report.export');
    });

    Route::controller(CancelPlotBookingReportController::class)->group(function () {
        Route::get('/cancel-plot-booking-report', 'index')->name('cancel-plot-booking-report.index');
        Route::get('/cancel-plot-booking-report/export', 'export')->name('cancel-plot-booking-report.export');
    });

    Route::controller(CustomerLedgerReportController::class)->group(function () {
        Route::get('/customer-ledger-report', 'index')->name('customer-ledger-report.index');
        Route::get('/ledger-project-blocks/{projectId}', 'getBlocks');
        Route::get('/ledger-block-customers/{projectId}/{blockId}', 'getCustomers');
        Route::get('/ledger-customer-plots/{customerId}', 'getPlots');
        Route::get('/ledger-plot-booking/{plotId}/{customerId}', 'getBooking');
        Route::get('/customer-ledger-report/export', 'export')->name('customer-ledger-report.export');
    });

    Route::controller(PaymentCollectionDuesSummaryReportController::class)->group(function () {
        Route::get('/payment-collection-dues-summary-report', 'index')->name('payment-collection-dues-summary-report.index');
        Route::get('/payment-collection-dues-summary-report/export', 'export'
        )->name('payment-collection-dues-summary-report.export');
    });

    Route::controller(DuesInstallmentReportController::class)->group(function () {
        Route::get('/dues-installment-report', 'index')->name('dues-installment-report.index');
        Route::get('/dues-installment-report/export', 'export')->name('dues-installment-report.export');
    });

    Route::controller(ChequeDetailsReportController::class)->group(function () {
        Route::get('/cheque-details-report', 'index')->name('cheque-details-report.index');
        Route::get('/cheque-details-report/export', 'export')->name('cheque-details-report.export');
    });

    Route::controller(EmiDuesSummaryReportController::class)->group(function () {
        Route::get('/emi-dues-summary-report', 'index')->name('emi-dues-summary-report.index');
        Route::get('/emi-dues-summary-report/export', 'export')->name('emi-dues-summary-report.export');
    });

    Route::controller(DailyCollectionReportController::class)->group(function () {
        Route::get('/daily-collection-report', 'index')->name('daily-collection-report.index');
        Route::get('/daily-collection-report/export', 'export')->name('daily-collection-report.export');
    });

    Route::controller(DailyDuesReportController::class)->group(function () {
        Route::get('/daily-dues-report', 'index')->name('daily-dues-report.index');
        Route::get('/daily-dues-report/export', 'export')->name('daily-dues-report.export');
    });

    Route::controller(AgentSummaryDetailsReportController::class)->group(function () {
        Route::get('/agent-summary-details-report', 'index')->name('agent-summary-details-report.index');
        Route::get('/agent-summary-details-report/export', 'export')->name('agent-summary-details-report.export');
    });

    Route::controller(NewBookingPaymentDetailsReportController::class)->group(function () {
        Route::get('/new-booking-payment-details-report', 'index')->name('new-booking-payment-details-report.index');
        Route::get('/new-booking-payment-details-report/export', 'export')->name('new-booking-payment-details-report.export');
    });

    Route::controller(AssociateTeamNewBookingDetailsReportController::class)->group(function () {
        Route::get('/associate-team-new-booking-details-report', 'index')
            ->name('associate-team-new-booking-details-report.index');
        Route::get('/associate-team-new-booking-details-report/export', 'export')
            ->name('associate-team-new-booking-details-report.export');
    });

    Route::controller(BouncedChequeDetailsReportController::class)->group(function () {
        Route::get('/bounced-cheque-details-report', 'index')->name('bounced-cheque-details-report.index');
        Route::get('/bounced-cheque-details-report/export', 'export')->name('bounced-cheque-details-report.export');
    });

    Route::controller(AssociateAdvanceReportController::class)->group(function () {
        Route::get('/associate-advance-report', 'index')->name('associate-advance-report.index');
        Route::get('/associate-advance-report/export', 'export')->name('associate-advance-report.export');
    });

    Route::controller(SourceController::class)->group(function () {
        Route::get('source', 'index')->name('source.index');
        Route::post('source/store', 'store')->name('source.store');
        Route::get('source/edit/{id}', 'edit')->name('source.edit');
        Route::put('source/update/{id}', 'update')->name('source.update');
        Route::delete('source/delete/{id}', 'destroy')->name('source.destroy');
    });
    Route::controller(EnquiryTypeController::class)->group(function () {
        Route::get('enquiry-type/', 'index')->name('enquiry-type.index');
        Route::post('enquiry-type/store', 'store')->name('enquiry-type.store');
        Route::get('enquiry-type/edit/{id}', 'edit')->name('enquiry-type.edit');
        Route::put('enquiry-type/update/{id}', 'update')->name('enquiry-type.update');
        Route::delete('enquiry-type/destroy/{id}', 'destroy')->name('enquiry-type.destroy');
    });
    Route::controller(EnquiryController::class)->group(function () {
        Route::get('enquiry', 'index')->name('enquiry.index');
        Route::post('enquiry/store', 'store')->name('enquiry.store');
        Route::get('enquiry/edit/{id}', 'edit')->name('enquiry.edit');
        Route::put('enquiry/update/{id}', 'update')->name('enquiry.update');
        Route::delete('enquiry/delete/{id}', 'destroy')->name('enquiry.destroy');
    });
    Route::controller(SupportController::class)->group(function () {
        Route::get('/support', 'supportList')->name('support.index');
        Route::get('/support/{support}', 'supportDetail')->name('support.detail');
        Route::post('/support-reply/{support}', 'supportReply')->name('support.reply');
    });
    Route::controller(PlotAvilabilityController::class)->group(function () {
        Route::get('plot-availability', 'index')->name('plot-availability.index');
    });

    Route::resource('brokers', BrokerController::class);
    Route::resource('farmers', FarmerController::class);

    Route::get('/get-cities/{stateId}', [FarmerController::class, 'getCities'])->name('get.cities');

    Route::controller(PlotTransferController::class)->group(function () {
        Route::get('plot-transfer', 'index')->name('plot-transfer.index');
        Route::post('plot-transfer', 'store')->name('plot-transfer.store');
        Route::get('plot-transfer/blocks/{project}', 'getBlocks')->name('plot-transfer.blocks');
        Route::get('plot-transfer/plots/{block}', 'getPlots')->name('plot-transfer.plots');
        Route::get('plot-transfer/booking/{plot}', 'getBookingData')->name('plot-transfer.booking');
        Route::get('plot-transfer/customers/{bookingId}', 'getTransferCustomers')->name('plot-transfer.customers');
    });

    Route::controller(PaymentTransferController::class)->group(function () {
        Route::get('payment-transfer', 'index')->name('payment-transfer.index');
        Route::post('payment-transfer', 'store')->name('payment-transfer.store');
        Route::get('payment-transfer/blocks/{project}', 'getBlocks')->name('payment-transfer.blocks');
        Route::get('payment-transfer/plots/{block}', 'getPlots')->name('payment-transfer.plots');
        Route::get('payment-transfer/payments/{plot}', 'getPayments')->name('payment-transfer.payments');
        Route::get('payment-transfer/customers', 'getCustomers')->name('payment-transfer.customers');
        Route::get('payment-transfer/customer-plots/{customerBooking}', 'getCustomerPlots')
            ->name('payment-transfer.customer-plots');
    });

    Route::controller(PlotChangeController::class)->group(function () {
        Route::get('plot-change', 'index')->name('plot-change.index');
        Route::post('plot-change', 'store')->name('plot-change.store');
        Route::get('plot-change/blocks/{project}', 'getBlocks')->name('plot-change.blocks');
        Route::get('plot-change/booked-plots/{block}', 'getBookedPlots')->name('plot-change.booked-plots');
        Route::get('plot-change/available-plots/{block}', 'getAvailablePlots')->name('plot-change.available-plots');
        Route::get('plot-change/booking/{plot}', 'getBookingData')->name('plot-change.booking');
        Route::get('plot-change/new-plot/{plot}', 'getNewPlotData')->name('plot-change.new-plot');
    });
    Route::controller(CommissionPayoutController::class)->group(function () {
        Route::get('/generate-commission', 'index')->name('generate-commission.index');
        Route::post('/generate-commission', 'store')->name('generate-commission.store');
        Route::get('/commission-ledger', 'commissionList')->name('commission-ledger.index');

        Route::get('/commission-ledger/export/excel', 'exportCommissionExcel')->name('commission-ledger.export.excel');
        Route::get('/commission-ledger/export/pdf', 'exportCommissionPdf')->name('commission-ledger.export.pdf');

        Route::get('/commission-ledger/{commission}/export/excel', 'exportSingleExcel')->name('commission-ledger.single.excel');
        Route::get('/commission-ledger/{commission}/export/pdf', 'exportSinglePdf')->name('commission-ledger.single.pdf');
    });

});

// ------------------Associate Routes-------------
Route::prefix('associate-panel')->name('associate-panel.')->group(function () {
    Route::middleware('guest:associate')->group(function () {
        Route::get('/login', [AssociateAuthController::class, 'loginForm'])->name('login');
        Route::post('/login', [AssociateAuthController::class, 'login'])->name('login.submit');
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