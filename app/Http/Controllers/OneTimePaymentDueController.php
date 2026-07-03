<?php

namespace App\Http\Controllers;

use App\Models\CustomerPayment;
use App\Services\ExcelExportService;
use Illuminate\Http\Request;

class OneTimePaymentDueController extends Controller
{
    protected $excelExportService;

    public function __construct(ExcelExportService $excelExportService)
    {
        $this->excelExportService = $excelExportService;
    }

    public function index(Request $request)
    {
        $payments = $this->buildQuery($request)->latest()->get();

        $paymentGroups = $this->buildPaymentGroups($payments);

        $summary = [
            'total_records' => $paymentGroups->count(),
            'total_payable' => $paymentGroups->sum('payable'),
            'total_paid' => $paymentGroups->sum('paid'),
            'total_due' => $paymentGroups->sum('due'),
        ];

        $customerIds = CustomerPayment::where('plan_type', 'full_payment')
            ->where('booking_status', 'booked')
            ->where('transaction_category', 'booking_fee')
            ->with('customerBooking.primaryDetail')
            ->get()
            ->unique(fn($item) => $item->customerBooking?->customer_id)
            ->values();

        return view(
            'reports.one-time-payment-due.index',
            compact('paymentGroups', 'customerIds', 'summary')
        );
    }

    public function export(Request $request)
    {
        $payments = $this->buildQuery($request)->latest()->get();

        $paymentGroups = $this->buildPaymentGroups($payments);

        return $this->excelExportService->export(
            $paymentGroups,
            'one-time-payment-due-report',
            [
                'Booking ID',
                'Customer ID',
                'Customer Name',
                'Project Name',
                'Block',
                'Plot No',
                'Plot Details',
                'Total Plots',
                'Payable Amount',
                'Paid Amount',
                'Due Amount',
                'Status',
            ],
            function ($payment) {
                return [
                    $payment['booking_code'],
                    $payment['customer_code'],
                    $payment['customer_name'],
                    $payment['project'],
                    $payment['block'],
                    $payment['plots'],
                    collect($payment['plot_details'])->map(function ($plot) {
                        return 'Plot ' . $plot['plot_no']
                            . ' | Block ' . $plot['block']
                            . (!empty($plot['area']) ? ' | Area ' . $plot['area'] : '')
                            . (!empty($plot['rate']) ? ' | Rate ₹' . number_format($plot['rate'], 2) : '')
                            . ' | Amount ₹' . number_format($plot['amount'], 2);
                    })->implode("\n"),
                    $payment['plot_count'],
                    number_format($payment['payable'], 2, '.', ''),
                    number_format($payment['paid'], 2, '.', ''),
                    number_format($payment['due'], 2, '.', ''),
                    ucfirst($payment['status']),
                ];
            }
        );
    }

    private function buildQuery(Request $request)
    {
        $query = CustomerPayment::with([
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->where('plan_type', 'full_payment')
            ->where('booking_status', 'booked')
            ->where('transaction_category', 'booking_fee')
            ->whereNotNull('plot_sale_detail_id');

        if ($request->filled('customer_id')) {
            $query->whereHas('customerBooking', function ($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            });
        }

        if ($request->filled('customer_name')) {
            $query->whereHas('customerBooking.primaryDetail', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer_name . '%');
            });
        }

        return $query;
    }

    private function buildPaymentGroups($payments)
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

                $payable = $group->sum(fn($payment) => (float) ($payment->net_payable_amount ?? 0));
                $paid = $group->sum(fn($payment) => (float) ($payment->booking_amount ?? 0));
                $due = max(0, $payable - $paid);

                return [
                    'booking_code' => $first->plotSaleDetail?->booking_code
                        ?? $first->customerBooking?->booking_code
                        ?? 'N/A',

                    'customer_code' => $first->customerBooking?->customer_code ?? 'N/A',

                    'customer_name' => $first->customerBooking?->primaryDetail?->name ?? 'N/A',

                    'project' => $group
                        ->map(fn($item) => $item->plotSaleDetail?->project?->name)
                        ->filter()
                        ->unique()
                        ->implode(', ') ?: 'N/A',

                    'block' => $group
                        ->map(fn($item) => $item->plotSaleDetail?->block?->block)
                        ->filter()
                        ->unique()
                        ->implode(', ') ?: 'N/A',

                    'plots' => $group
                        ->map(fn($item) => $item->plotSaleDetail?->plotDetail?->plot_number)
                        ->filter()
                        ->unique()
                        ->implode(', ') ?: 'N/A',

                    'plot_count' => $group
                        ->pluck('plot_sale_detail_id')
                        ->filter()
                        ->unique()
                        ->count(),

                    'payable' => $payable,
                    'paid' => $paid,
                    'due' => $due,
                    'status' => $due <= 0 ? 'completed' : 'due',

                    'plot_details' => $group
                        ->map(function ($item) {
                            $sale = $item->plotSaleDetail;
                            $plot = $sale?->plotDetail;

                            $plotNo = $plot?->plot_number ?? 'N/A';
                            $block = $sale?->block?->block ?? 'N/A';
                            $area = $plot?->plot_area ?? $plot?->area ?? $plot?->size ?? null;
                            $rate = $plot?->plot_rate ?? $plot?->rate ?? $sale?->rate ?? null;

                            $amount = $item->net_payable_amount ?? $sale?->total_plot_cost ?? $sale?->net_payable_amount ?? 0;

                            return [
                                'plot_no' => $plotNo,
                                'block' => $block,
                                'area' => $area,
                                'rate' => $rate,
                                'amount' => (float) $amount,
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();
    }
}