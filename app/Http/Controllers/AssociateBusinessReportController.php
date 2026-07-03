<?php

namespace App\Http\Controllers;

use App\Models\Associate;
use App\Models\CustomerBooking;
use App\Services\ExcelExportService;
use Illuminate\Http\Request;

class AssociateBusinessReportController extends Controller
{
    protected $excelExportService;

    public function __construct(ExcelExportService $excelExportService)
    {
        $this->excelExportService = $excelExportService;
    }

    public function index(Request $request)
    {
        $associates = Associate::select('id', 'associate_id', 'associate_name')->get();

        $reports = $this->buildReports($request);

        $summary = [
            'total_records' => $reports->count(),
            'total_plots' => $reports->sum('plot_count'),
            'total_business' => $reports->sum('total_business'),
            'total_paid' => $reports->sum('paid_amount'),
            'total_due' => $reports->sum('due_amount'),
        ];

        return view(
            'reports.associate-business-report.index',
            compact('associates', 'reports', 'summary')
        );
    }

    public function export(Request $request)
    {
        $reports = $this->buildReports($request);

        return $this->excelExportService->export(
            $reports,
            'associate-business-report',
            [
                'Associate ID',
                'Associate Name',
                'Customer ID',
                'Customer Name',
                'Booking ID',
                'Project',
                'Block',
                'Plot No',
                'Total Plots',
                'Total Business',
                'Paid Amount',
                'Due Amount',
                'Booking Date',
            ],
            function ($report) {
                return [
                    $report['associate_id'],
                    $report['associate_name'],
                    $report['customer_code'],
                    $report['customer_name'],
                    $report['booking_code'],
                    $report['project'],
                    $report['block'],
                    $report['plots'],
                    $report['plot_count'],
                    number_format($report['total_business'], 2, '.', ''),
                    number_format($report['paid_amount'], 2, '.', ''),
                    number_format($report['due_amount'], 2, '.', ''),
                    $report['booking_date'],
                ];
            }
        );
    }

    private function buildReports(Request $request)
    {
        $query = CustomerBooking::with([
            'associate',
            'primaryDetail',
            'plotSaleDetails.project',
            'plotSaleDetails.block',
            'plotSaleDetails.plotDetail',
            'payments',
        ])->whereHas('plotSaleDetails');

        if ($request->filled('associate_id')) {
            $query->where('associate_id', $request->associate_id);
        }

        if ($request->filled('from_date')) {
            $query->whereHas('plotSaleDetails', function ($q) use ($request) {
                $q->whereDate('booking_date', '>=', $request->from_date);
            });
        }

        if ($request->filled('to_date')) {
            $query->whereHas('plotSaleDetails', function ($q) use ($request) {
                $q->whereDate('booking_date', '<=', $request->to_date);
            });
        }

        return $query->latest()->get()
            ->flatMap(function ($booking) {
                return $booking->plotSaleDetails
                    ->groupBy(fn ($sale) => $sale->booking_code ?: $booking->booking_code)
                    ->map(function ($plotSales, $bookingCode) use ($booking) {
                        $plotSaleIds = $plotSales->pluck('id')->filter()->values();

                        $payments = $booking->payments->whereIn('plot_sale_detail_id', $plotSaleIds)
                            ->where('booking_status', 'booked');

                        if ($payments->isEmpty()) {
                            $payments = $booking->payments;
                        }

                        $totalBusiness = $plotSales->sum(fn ($sale) => (float) ($sale->total_plot_cost ?? 0));

                        $paidAmount = $payments
                            ->whereIn('payment_status', ['paid', 'cleared'])->where('booking_status', 'booked')
                            ->sum('paid_amount');

                        $dueAmount = max(0, $totalBusiness - $paidAmount);

                        $firstSale = $plotSales->first();

                        return [
                            'associate_id' => $booking->associate?->associate_id ?? 'N/A',
                            'associate_name' => $booking->associate?->associate_name ?? 'N/A',
                            'customer_code' => $booking->customer_code ?? 'N/A',
                            'customer_name' => $booking->primaryDetail?->name ?? 'N/A',
                            'booking_code' => $bookingCode ?: $booking->booking_code ?? 'N/A',
                            'project' => $plotSales->map(fn ($sale) => $sale->project?->name)->filter()->unique()->implode(', ') ?: 'N/A',
                            'block' => $plotSales->map(fn ($sale) => $sale->block?->block)->filter()->unique()->implode(', ') ?: 'N/A',
                            'plots' => $plotSales->map(fn ($sale) => $sale->plotDetail?->plot_number)->filter()->unique()->implode(', ') ?: 'N/A',
                            'plot_count' => $plotSales->count(),
                            'total_business' => $totalBusiness,
                            'paid_amount' => $paidAmount,
                            'due_amount' => $dueAmount,
                            'booking_date' => $firstSale?->booking_date
                                ? \Carbon\Carbon::parse($firstSale->booking_date)->format('d-m-Y')
                                : 'N/A',
                        ];
                    });
            })
            ->values();
    }
}