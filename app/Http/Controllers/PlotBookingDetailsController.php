<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\CustomerBooking;
use App\Models\PlotDetail;
use App\Models\Project;
use App\Services\ExcelExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PlotBookingDetailsController extends Controller
{
    protected $excelExportService;

    public function __construct(ExcelExportService $excelExportService)
    {
        $this->excelExportService = $excelExportService;
    }

    public function getCustomerDetails($id)
    {
        $customer = CustomerBooking::with('primaryDetail')->find($id);

        return response()->json([
            'name' => $customer?->primaryDetail?->name,
        ]);
    }

    public function getProjectBlocks($projectId)
    {
        $blocks = Block::where('project_id', $projectId)->get();

        return response()->json($blocks);
    }

    public function getBlockPlcTypes($blockId)
    {
        $types = PlotDetail::with('plotType')
            ->where('block_id', $blockId)
            ->get()
            ->pluck('plotType')
            ->filter()
            ->unique('id')
            ->values();

        return response()->json($types);
    }

    public function index(Request $request)
    {
        $bookings = $this->buildQuery($request)->latest()->get();

        $bookingGroups = $this->buildBookingGroups($bookings);

        $summary = [
            'total_records' => $bookingGroups->count(),
            'total_plots' => $bookingGroups->sum('plot_count'),
            'total_final_amount' => $bookingGroups->sum('final_amount'),
            'total_paid_amount' => $bookingGroups->sum('paid_amount'),
            'total_due_amount' => $bookingGroups->sum('due_amount'),
        ];

        $customerIds = CustomerBooking::with('primaryDetail')->get();
        $projects = Project::get();

        $selectedBlockId = $request->block_id;
        $selectedPlotTypeId = $request->plot_type_id;

        return view(
            'reports.plot-booking-details.index',
            compact(
                'bookingGroups',
                'customerIds',
                'projects',
                'summary',
                'selectedBlockId',
                'selectedPlotTypeId'
            )
        );
    }

    public function export(Request $request)
    {
        $bookings = $this->buildQuery($request)->latest()->get();

        $bookingGroups = $this->buildBookingGroups($bookings);

        return $this->excelExportService->export(
            $bookingGroups,
            'plot-booking-details-report',
            [
                'Booking ID',
                'Agent ID / Name',
                'Customer ID',
                'Customer Name',
                'Project Name',
                'Block',
                'Plot Details',
                'Total Plots',
                'Plot Cost',
                'Other Charges',
                'Discount',
                'Final Amount',
                'Paid Amount',
                'Due Amount',
                'Installment Amount',
                'Booking Date',
                'Plan Type',
            ],
            function ($booking) {
                return [
                    $booking['booking_code'],
                    $booking['agent'],
                    $booking['customer_code'],
                    $booking['customer_name'],
                    $booking['project'],
                    $booking['block'],
                    collect($booking['plot_details'])->map(function ($plot) {
                        return 'Plot ' . $plot['plot_no']
                            . ' | Block ' . $plot['block']
                            . ' | Area ' . $plot['area']
                            . ' | Rate ₹' . number_format($plot['rate'], 2)
                            . ' | Amount ₹' . number_format($plot['plot_cost'], 2);
                    })->implode("\n"),
                    $booking['plot_count'],
                    number_format($booking['plot_cost'], 2, '.', ''),
                    number_format($booking['other_charges'], 2, '.', ''),
                    number_format($booking['discount'], 2, '.', ''),
                    number_format($booking['final_amount'], 2, '.', ''),
                    number_format($booking['paid_amount'], 2, '.', ''),
                    number_format($booking['due_amount'], 2, '.', ''),
                    number_format($booking['installment_amount'], 2, '.', ''),
                    $booking['booking_date'],
                    $booking['plan_type_label'],
                ];
            }
        );
    }

    private function buildQuery(Request $request)
    {
        $query = CustomerBooking::with([
            'primaryDetail',
            'associate',
            'plotSaleDetails.project',
            'plotSaleDetails.block',
            'plotSaleDetails.plotDetail.plotType',
            'payments',
        ])
            ->whereHas('plotSaleDetails');

        if ($request->filled('customer_id')) {
            $query->where('id', $request->customer_id);
        }

        if ($request->filled('customer_name')) {
            $query->whereHas('primaryDetail', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer_name . '%');
            });
        }

        if ($request->filled('project_id')) {
            $query->whereHas('plotSaleDetails', function ($q) use ($request) {
                $q->where('project_id', $request->project_id)->where('status', 'active');
            });
        }

        if ($request->filled('block_id')) {
            $query->whereHas('plotSaleDetails', function ($q) use ($request) {
                $q->where('block_id', $request->block_id);
            });
        }

        if ($request->filled('plot_type_id')) {
            $query->whereHas('plotSaleDetails.plotDetail', function ($q) use ($request) {
                $q->where('plot_type_id', $request->plot_type_id);
            });
        }

        if ($request->filled('plan_type')) {
            $query->whereHas('payments', function ($q) use ($request) {
                $q->where('plan_type', $request->plan_type);
            });
        }

        if ($request->filled('payment_mode')) {
            $query->whereHas('payments', function ($q) use ($request) {
                $q->where('payment_mode', $request->payment_mode);
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        return $query;
    }

    private function buildBookingGroups(Collection $bookings): Collection
    {
        return $bookings
            ->flatMap(function ($booking) {
                return $booking->plotSaleDetails
                    ->groupBy(fn($sale) => $sale->booking_code ?: $booking->booking_code)
                    ->map(function ($plotSales, $bookingCode) use ($booking) {
                        $plotSaleIds = $plotSales->pluck('id')->filter()->values();

                        $payments = $booking->payments
                            ->whereIn('plot_sale_detail_id', $plotSaleIds);

                        if ($payments->isEmpty()) {
                            $payments = $booking->payments;
                        }

                        $payment = $payments
                            ->where('transaction_category', 'booking_fee')
                            ->where('booking_status', 'booked')
                            ->sortBy('id')
                            ->first() ?? $payments->sortBy('id')->first();

                        $paidAmount = $payments
                            ->whereIn('payment_status', ['paid', 'cleared'])
                            ->sum('paid_amount');

                        $plotCost = $plotSales->sum(fn($sale) => (float) ($sale->plot_cost ?? 0));
                        $otherCharges = $plotSales->sum(fn($sale) => (float) ($sale->other_charges ?? 0));
                        $discount = $plotSales->sum(fn($sale) => (float) ($sale->coupon_discount ?? 0));
                        $finalAmount = $plotSales->sum(fn($sale) => (float) ($sale->total_plot_cost ?? 0));
                        $dueAmount = max(0, $finalAmount - $paidAmount);

                        $installmentAmount = 0;

                        if (($payment?->plan_type ?? '') === 'emi_plan' && (int) ($payment?->emi_months ?? 0) > 0) {
                            $installmentAmount = ((float) ($payment->net_payable_amount ?? $finalAmount)) / (int) $payment->emi_months;
                        }

                        $firstSale = $plotSales->first();

                        return [
                            'booking_code' => $bookingCode ?: 'N/A',
                            'customer_code' => $booking->customer_code ?? 'N/A',
                            'customer_name' => $booking->primaryDetail?->name ?? 'N/A',
                            'agent' => ($booking->associate_code ?? 'N/A') . ' / ' . ($booking->associate_name ?? 'N/A'),
                            'project' => $plotSales->map(fn($sale) => $sale->project?->name)->filter()->unique()->implode(', ') ?: 'N/A',
                            'block' => $plotSales->map(fn($sale) => $sale->block?->block)->filter()->unique()->implode(', ') ?: 'N/A',
                            'plots' => $plotSales->map(fn($sale) => $sale->plotDetail?->plot_number)->filter()->unique()->implode(', ') ?: 'N/A',
                            'plot_count' => $plotSales->count(),
                            'plot_cost' => $plotCost,
                            'other_charges' => $otherCharges,
                            'discount' => $discount,
                            'final_amount' => $finalAmount,
                            'paid_amount' => $paidAmount,
                            'due_amount' => $dueAmount,
                            'installment_amount' => $installmentAmount,
                            'booking_date' => $firstSale?->booking_date
                                ? \Carbon\Carbon::parse($firstSale->booking_date)->format('d-m-Y')
                                : ($booking->created_at ? $booking->created_at->format('d-m-Y') : 'N/A'),
                            'plan_type' => $payment?->plan_type ?? 'N/A',
                            'plan_type_label' => ucfirst(str_replace('_', ' ', $payment?->plan_type ?? 'N/A')),
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
}