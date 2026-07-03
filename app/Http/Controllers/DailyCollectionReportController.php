<?php

namespace App\Http\Controllers;

use App\Models\CustomerPayment;
use App\Services\ExcelExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DailyCollectionReportController extends Controller
{
    protected $excelExportService;

    public function __construct(ExcelExportService $excelExportService)
    {
        $this->excelExportService = $excelExportService;
    }

    public function index(Request $request)
    {
        $reports = collect();

        if ($request->has('search')) {
            $reports = $this->buildQuery($request)->latest()->get();
        }

        $summary = $this->buildSummary($reports);

        return view('reports.daily-collection-report.index', compact('reports', 'summary'));
    }

    public function export(Request $request)
    {
        $reports = $this->buildQuery($request)->latest()->get();

        return $this->excelExportService->export(
            $reports,
            'daily-collection-due-report',
            [
                'Agent ID',
                'Customer ID',
                'Customer Name',
                'Booking ID',
                'Plot No',
                'Plan Type',
                'Payment Type',
                'Receipt No',
                'Total Cost',
                'Paid Amount',
                'Due Amount',
                'Paymode / Cheque / DD / Reference No',
                'Date',
            ],
            function ($report) {
                $totalCost = (float) ($report->net_payable_amount ?? 0);
                $paidAmount = (float) ($report->paid_amount ?? $report->booking_amount ?? 0);
                $dueAmount = max(0, $totalCost - $paidAmount);

                return [
                    $report->customerBooking?->associate?->associate_code ?? $report->customerBooking?->associate?->associate_id ?? 'N/A',
                    $report->customerBooking?->customer_code ?? 'N/A',
                    $report->customerBooking?->primaryDetail?->name ?? 'N/A',
                    $report->customerBooking?->booking_code ?? 'N/A',
                    $report->plotSaleDetail?->plotDetail?->plot_number ?? 'N/A',
                    ucfirst(str_replace('_', ' ', $report->plan_type ?? 'N/A')),
                    ucfirst(str_replace('_', ' ', $report->transaction_category ?? 'N/A')),
                    $report->receipt_number ?? 'N/A',
                    number_format($totalCost, 2, '.', ''),
                    number_format($paidAmount, 2, '.', ''),
                    number_format($dueAmount, 2, '.', ''),
                    $this->paymentReference($report),
                    $report->created_at ? Carbon::parse($report->created_at)->format('d-m-Y') : 'N/A',
                ];
            }
        );
    }

    private function buildQuery(Request $request)
    {
        $query = CustomerPayment::with([
            'customerBooking.primaryDetail',
            'customerBooking.associate',
            'plotSaleDetail.plotDetail',
        ])

            ->where('booking_status', 'booked');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('report_type')) {
            if ($request->report_type === 'collection') {
                $query->whereIn('payment_status', ['paid', 'cleared']);
            }

            if ($request->report_type === 'due') {
                $query->whereRaw('(COALESCE(net_payable_amount, 0) - COALESCE(paid_amount, booking_amount, 0)) > 0');
            }
        }

        return $query;
    }

    private function buildSummary($reports): array
    {
        $totalCost = $reports->sum(fn($item) => (float) ($item->net_payable_amount ?? 0));
        $totalPaid = $reports->sum(fn($item) => (float) ($item->paid_amount ?? $item->booking_amount ?? 0));
        $totalDue = $reports->sum(function ($item) {
            $totalCost = (float) ($item->net_payable_amount ?? 0);
            $paidAmount = (float) ($item->paid_amount ?? $item->booking_amount ?? 0);

            return max(0, $totalCost - $paidAmount);
        });

        return [
            'total_records' => $reports->count(),
            'total_cost' => $totalCost,
            'total_paid' => $totalPaid,
            'total_due' => $totalDue,
        ];
    }

    private function paymentReference($report): string
    {
        $mode = strtolower($report->payment_mode ?? '');

        if ($mode === 'cheque') {
            return $report->cheque_number ?? '-';
        }

        if ($mode === 'dd') {
            return $report->dd_number ?? '-';
        }

        return $report->transaction_number ?? '-';
    }
}