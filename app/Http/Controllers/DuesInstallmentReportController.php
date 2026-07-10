<?php

namespace App\Http\Controllers;

use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use App\Services\ExcelExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DuesInstallmentReportController extends Controller
{
    protected $excelExportService;

    public function __construct(ExcelExportService $excelExportService)
    {
        $this->excelExportService = $excelExportService;
    }

    public function index(Request $request)
    {
        $customerIds = CustomerBooking::with('primaryDetail')
            ->select('id', 'customer_code')->get();
        $payments = $this->buildQuery($request)->latest()->get();
        $reports = $this->buildReports($payments);
        $summary = [
            'total_records' => $reports->count(),
            'total_due_installments' => $reports->sum('due_installment'),
            'total_amount' => $reports->sum('total_amount'),
            'paid_amount' => $reports->sum('paid_amount'),
            'balance_amount' => $reports->sum('balance_amount'),
        ];
        return view('reports.dues-installment-report.index',compact('customerIds', 'reports', 'summary'));
    }

    public function export(Request $request)
    {
        $payments = $this->buildQuery($request)->latest()->get();
        $reports = $this->buildReports($payments);
        return $this->excelExportService->export($reports,'dues-installment-report',
            [
                'Agent ID',
                'Customer ID',
                'Customer Name',
                'Booking ID',
                'Booking Date',
                'Plot No',
                'Installment Amt',
                'Total Ins Amt',
                'Paid Ins Amt',
                'Balance Amt',
                'No Of Due Ins',
            ],
            function ($report) {
                return [
                    $report['agent_code'],
                    $report['customer_code'],
                    $report['customer_name'],
                    $report['booking_code'],
                    $report['booking_date'],
                    $report['plots'],
                    number_format($report['installment_amount'], 2, '.', ''),
                    number_format($report['total_amount'], 2, '.', ''),
                    number_format($report['paid_amount'], 2, '.', ''),
                    number_format($report['balance_amount'], 2, '.', ''),
                    $report['due_installment'],
                ];
            }
        );
    }

    private function buildQuery(Request $request)
    {
        $query = CustomerPayment::with([
            'customerBooking.primaryDetail',
            'customerBooking.associate',
            'customerBooking.payments',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->where('plan_type', 'emi_plan')
            ->where('booking_status', 'booked')
            ->where('transaction_category', 'booking_fee')
            ->whereNotNull('plot_sale_detail_id');
        if ($request->filled('date')) {
            $query->whereDate('created_at', Carbon::parse($request->date));
        }
        if ($request->filled('customer_id')) {
            $query->whereHas('customerBooking', function ($q) use ($request) {
                $q->where('id', $request->customer_id);
            });
        }
        return $query;
    }

    private function buildReports(Collection $payments): Collection
    {
        return $payments
            ->groupBy(function ($payment) {
                $bookingCode = $payment->plotSaleDetail?->booking_code
                    ?: $payment->customerBooking?->booking_code
                    ?: 'booking-' . $payment->customer_booking_id;
                return $payment->customer_booking_id . '|' . $bookingCode;
            })
            ->map(function ($group) {
                $first = $group->first();
                $booking = $first->customerBooking;
                $plotSaleIds = $group->pluck('plot_sale_detail_id')->filter()->unique()->values();
                $bookingPayments = $booking?->payments
                        ?->whereIn('plot_sale_detail_id', $plotSaleIds)
                    ->where('booking_status', 'booked') ?? collect();
                if ($bookingPayments->isEmpty()) {
                    $bookingPayments = $booking?->payments ?? collect();
                }
                $totalAmount = $group->sum(fn($payment) => (float) ($payment->net_payable_amount ?? 0));
                if ($totalAmount <= 0) {
                    $totalAmount = $group->sum(fn($payment) => (float) ($payment->plotSaleDetail?->total_plot_cost ?? 0));
                }

                $paidAmount = $bookingPayments
                    ->whereIn('payment_status', ['paid', 'cleared'])
                    ->where('booking_status', 'booked')
                    ->sum('paid_amount');

                $balanceAmount = max(0, $totalAmount - $paidAmount);

                $emiMonths = (int) ($group->max('emi_months') ?: 0);
                $installmentAmount = $emiMonths > 0 ? ($totalAmount / $emiMonths) : 0;
                $dueInstallment = $installmentAmount > 0 ? (int) ceil($balanceAmount / $installmentAmount) : 0;

                return [
                    'agent_code' => $booking?->associate?->associate_code ?? $booking?->associate_code ?? 'N/A',
                    'customer_code' => $booking?->customer_code ?? 'N/A',
                    'customer_name' => $booking?->primaryDetail?->name ?? 'N/A',
                    'booking_code' => $first->plotSaleDetail?->booking_code ?? $booking?->booking_code ?? 'N/A',
                    'booking_date' => $booking?->created_at?->format('d-m-Y') ?? 'N/A',
                    'project' => $group->map(fn($payment) => $payment->plotSaleDetail?->project?->name)->filter()->unique()->implode(', ') ?: 'N/A',
                    'block' => $group->map(fn($payment) => $payment->plotSaleDetail?->block?->block)->filter()->unique()->implode(', ') ?: 'N/A',
                    'plots' => $group->map(fn($payment) => $payment->plotSaleDetail?->plotDetail?->plot_number)->filter()->unique()->implode(', ') ?: 'N/A',
                    'plot_count' => $plotSaleIds->count(),
                    'installment_amount' => $installmentAmount,
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'balance_amount' => $balanceAmount,
                    'due_installment' => $dueInstallment,
                ];
            })
            ->values();
    }
}