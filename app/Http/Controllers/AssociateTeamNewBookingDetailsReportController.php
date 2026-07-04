<?php

namespace App\Http\Controllers;

use App\Models\Associate;
use App\Models\CustomerBooking;
use App\Services\ExcelExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AssociateTeamNewBookingDetailsReportController extends Controller
{
    protected $excelExportService;

    public function __construct(ExcelExportService $excelExportService)
    {
        $this->excelExportService = $excelExportService;
    }

    public function index(Request $request)
    {
        $associates = Associate::select('id', 'associate_id', 'associate_name')->get();

        $reports = collect();

        if ($request->has('search')) {
            $bookings = $this->buildQuery($request)->latest()->get();
            $reports = $this->buildReports($bookings);
        }

        $summary = [
            'total_records' => $reports->count(),
            'total_plots' => $reports->sum('plot_count'),
            'total_cost' => $reports->sum('total_cost'),
            'paid_amount' => $reports->sum('paid_amount'),
            'due_amount' => $reports->sum('due_amount'),
        ];

        return view(
            'reports.associate-team-new-booking-details-report.index',
            compact('associates', 'reports', 'summary')
        );
    }

    public function export(Request $request)
    {
        $bookings = $this->buildQuery($request)->latest()->get();
        $reports = $this->buildReports($bookings);

        return $this->excelExportService->export(
            $reports,
            'associate-team-new-booking-details-report',
            [
                'Agent ID',
                'Agent Name',
                'Position',
                'Customer ID',
                'Customer Name',
                'Booking ID',
                'Project',
                'Block',
                'Plot No',
                'Total Plots',
                'Plan Type',
                'Payment Type',
                'Total Cost',
                'Paymode',
                'Paid Amount',
                'Due Amount',
                'Date',
            ],
            function ($report) {
                return [
                    $report['agent_code'],
                    $report['agent_name'],
                    $report['position'],
                    $report['customer_code'],
                    $report['customer_name'],
                    $report['booking_code'],
                    $report['project'],
                    $report['block'],
                    $report['plots'],
                    $report['plot_count'],
                    $report['plan_type'],
                    $report['payment_type'],
                    number_format($report['total_cost'], 2, '.', ''),
                    $report['payment_mode'],
                    number_format($report['paid_amount'], 2, '.', ''),
                    number_format($report['due_amount'], 2, '.', ''),
                    $report['date'],
                ];
            }
        );
    }

    private function buildQuery(Request $request)
    {
        $associateIds = [];

        if ($request->filled('associate_id')) {
            $associate = Associate::with('children.children')->find($request->associate_id);

            if ($associate) {
                $associateIds[] = $associate->id;
                $associateIds = array_merge($associateIds, $this->getAllChildrenIds($associate));
            }
        }

        $query = CustomerBooking::with([
            'associate.rank',
            'primaryDetail',
            'plotSaleDetails.project',
            'plotSaleDetails.block',
            'plotSaleDetails.plotDetail.plotType',
            'payments',
        ])
            ->whereHas('plotSaleDetails');

        if (! empty($associateIds)) {
            $query->whereIn('associate_id', $associateIds);
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

                        $payments = $booking->payments->whereIn('plot_sale_detail_id', $plotSaleIds);

                        if ($payments->isEmpty()) {
                            $payments = $booking->payments;
                        }

                        $payment = $payments
                            ->where('transaction_category', 'booking_fee')
                            ->where('booking_status', 'booked')
                            ->sortBy('id')
                            ->first() ?? $payments->sortBy('id')->first();

                        $totalCost = $plotSales->sum(fn($sale) => (float) ($sale->total_plot_cost ?? 0));

                        $paidAmount = $payments
                            ->whereIn('payment_status', ['paid', 'cleared'])
                            ->where('booking_status', 'booked')
                            ->sum('paid_amount');

                        if ($paidAmount <= 0) {
                            $paidAmount = (float) ($payment?->booking_amount ?? 0);
                        }

                        $dueAmount = max(0, $totalCost - $paidAmount);

                        $firstSale = $plotSales->first();

                        return [
                            'agent_code' => $booking->associate?->associate_id
                                ?? $booking->associate?->associate_code
                                ?? 'N/A',
                            'agent_name' => $booking->associate?->associate_name ?? 'N/A',
                            'position' => $booking->associate?->rank?->designation ?? 'N/A',
                            'commission' => ($booking->associate?->rank?->commission ?? 'N/A') . '%',
                            'customer_code' => $booking->customer_code ?? 'N/A',
                            'customer_name' => $booking->primaryDetail?->name ?? 'N/A',
                            'booking_code' => $bookingCode ?: $booking->booking_code ?? 'N/A',
                            'project' => $plotSales->map(fn($sale) => $sale->project?->name)->filter()->unique()->implode(', ') ?: 'N/A',
                            'block' => $plotSales->map(fn($sale) => $sale->block?->block)->filter()->unique()->implode(', ') ?: 'N/A',
                            'plots' => $plotSales->map(fn($sale) => $sale->plotDetail?->plot_number)->filter()->unique()->implode(', ') ?: 'N/A',
                            'plot_count' => $plotSales->count(),
                            'plan_type' => ucfirst(str_replace('_', ' ', $payment?->plan_type ?? 'N/A')),
                            'payment_type' => ucfirst(str_replace('_', ' ', $payment?->payment_status ?? 'N/A')),
                            'payment_mode' => ucfirst($payment?->payment_mode ?? 'N/A'),
                            'total_cost' => $totalCost,
                            'paid_amount' => $paidAmount,
                            'due_amount' => $dueAmount,
                            'date' => $firstSale?->booking_date
                                ? \Carbon\Carbon::parse($firstSale->booking_date)->format('d-m-Y')
                                : ($booking->created_at?->format('d-m-Y') ?? 'N/A'),
                            'plot_details' => $plotSales->map(function ($sale) {
                                return [
                                    'plot_no' => $sale->plotDetail?->plot_number ?? 'N/A',
                                    'block' => $sale->block?->block ?? 'N/A',
                                    'plot_type' => $sale->plotDetail?->plotType?->plot_type_name ?? 'N/A',
                                    'area' => $sale->plot_area ?? $sale->plotDetail?->plot_area ?? 'N/A',
                                    'rate' => (float) ($sale->plot_rate ?? 0),
                                    'plot_cost' => (float) ($sale->plot_cost ?? 0),
                                    'other_charges' => (float) ($sale->other_charges ?? 0),
                                    'discount' => (float) ($sale->coupon_discount ?? 0),
                                    'final_amount' => (float) ($sale->total_plot_cost ?? 0),
                                ];
                            })->values(),
                        ];
                    });
            })
            ->values();
    }

    private function getAllChildrenIds($associate): array
    {
        $ids = [];

        foreach ($associate->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getAllChildrenIds($child));
        }

        return $ids;
    }
}