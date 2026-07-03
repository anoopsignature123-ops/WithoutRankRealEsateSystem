<?php

namespace App\Http\Controllers;

use App\Models\CustomerBooking;
use App\Services\ExcelExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PaymentCollectionDuesSummaryReportController extends Controller
{
    protected $excelExportService;

    public function __construct(ExcelExportService $excelExportService)
    {
        $this->excelExportService = $excelExportService;
    }

    public function index(Request $request)
    {
        $customerIds = CustomerBooking::with('primaryDetail')
            ->select('id', 'customer_code')
            ->get();

        $bookings = $this->buildQuery($request)->latest()->get();
        $reports = $this->buildReports($bookings);

        $summary = [
            'total_records' => $reports->count(),
            'total_plots' => $reports->sum('plot_count'),
            'total_cost' => $reports->sum('total_cost'),
            'total_paid' => $reports->sum('paid_amount'),
            'total_due' => $reports->sum('due_amount'),
        ];

        return view(
            'reports.payment-collection-dues-summary-report.index',
            compact('customerIds', 'reports', 'summary')
        );
    }

    public function export(Request $request)
    {
        $bookings = $this->buildQuery($request)->latest()->get();
        $reports = $this->buildReports($bookings);

        return $this->excelExportService->export(
            $reports,
            'payment-collection-dues-summary-report',
            [
                'Customer ID',
                'Customer Name',
                'Booking ID',
                'Project',
                'Block',
                'Plot No',
                'Total Plots',
                'Total Cost',
                'Paid Amount',
                'Due Amount',
            ],
            function ($report) {
                return [
                    $report['customer_code'],
                    $report['customer_name'],
                    $report['booking_code'],
                    $report['project'],
                    $report['block'],
                    $report['plots'],
                    $report['plot_count'],
                    number_format($report['total_cost'], 2, '.', ''),
                    number_format($report['paid_amount'], 2, '.', ''),
                    number_format($report['due_amount'], 2, '.', ''),
                ];
            }
        );
    }

    private function buildQuery(Request $request)
    {
        $query = CustomerBooking::with([
            'primaryDetail',
            'plotSaleDetails.project',
            'plotSaleDetails.block',
            'plotSaleDetails.plotDetail',
            'payment',
            'payments',
        ])
            ->whereHas('plotSaleDetails');

        if ($request->filled('date')) {
            $query->whereDate('created_at', Carbon::parse($request->date));
        }

        if ($request->filled('customer_id')) {
            $query->where('id', $request->customer_id);
        }

        return $query;
    }

    private function buildReports(Collection $bookings): Collection
    {
        return $bookings
            ->flatMap(function ($booking) {
                return $booking->plotSaleDetails
                    ->groupBy(fn($sale) => $sale->booking_code ?: $booking->booking_code)
                    ->map(function ($plotSales, $bookingCode) use ($booking) {
                        $plotSaleIds = $plotSales->pluck('id')->filter()->values();

                        $payments = $booking->payments->whereIn('plot_sale_detail_id', $plotSaleIds)
                            ->where('booking_status', 'booked');

                        if ($payments->isEmpty()) {
                            $payments = $booking->payments;
                        }

                        $totalCost = $plotSales->sum(fn($sale) => (float) ($sale->total_plot_cost ?? 0));

                        if ($totalCost <= 0) {
                            $totalCost = (float) ($payments->where('transaction_category', 'booking_fee')
                                ->where('booking_status', 'booked')->first()?->net_payable_amount ?? 0);
                        }

                        $paidAmount = $payments
                            ->whereIn('payment_status', ['paid', 'cleared'])
                            ->where('booking_status', 'booked')
                            ->sum('paid_amount');

                        $dueAmount = max(0, $totalCost - $paidAmount);

                        return [
                            'customer_code' => $booking->customer_code ?? 'N/A',
                            'customer_name' => $booking->primaryDetail?->name ?? 'N/A',
                            'booking_code' => $bookingCode ?: $booking->booking_code ?? 'N/A',
                            'project' => $plotSales->map(fn($sale) => $sale->project?->name)->filter()->unique()->implode(', ') ?: 'N/A',
                            'block' => $plotSales->map(fn($sale) => $sale->block?->block)->filter()->unique()->implode(', ') ?: 'N/A',
                            'plots' => $plotSales->map(fn($sale) => $sale->plotDetail?->plot_number)->filter()->unique()->implode(', ') ?: 'N/A',
                            'plot_count' => $plotSales->count(),
                            'total_cost' => $totalCost,
                            'paid_amount' => $paidAmount,
                            'due_amount' => $dueAmount,
                        ];
                    });
            })
            ->values();
    }
}