<?php

namespace App\Http\Controllers;

use App\Http\Requests\OneTimePaymentRequest;
use App\Models\Block;
use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use App\Models\PlotSaleDetail;
use App\Models\Project;
use App\Services\OneTimePaymentService;
use Exception;

class OneTimePaymentController extends Controller
{
    public function __construct(protected OneTimePaymentService $service)
    {
    }

    public function index()
    {
        $projects = Project::orderBy('name')->get();

        return view('payment.one-time-payment.index', compact('projects'));
    }

    public function getBlocks($projectId)
    {
        $blocks = Block::where('project_id', $projectId)->orderBy('block')->get();

        return response()->json($blocks);
    }

    public function getPlots($blockId)
    {
        $plotSales = PlotSaleDetail::with([
            'plotDetail',
            'customerBooking.primaryDetail',
            'customerBooking.plotSaleDetails.plotDetail',
            'customerBooking.plotSaleDetails.payments',
        ])
            ->where('block_id', $blockId)
            ->whereHas('payments', function ($query) {
                $query->where('plan_type', 'full_payment')->where('booking_status', 'booked');
            })
            ->get()
            ->groupBy(function ($sale) {
                return $sale->customer_booking_id.'|'.($sale->booking_code ?: 'plot-'.$sale->id);
            });

        $plots = $plotSales->map(function ($sales) {
            $representativeSale = $sales->first();
            $booking = $representativeSale->customerBooking;
            $bookingPlots = $representativeSale->booking_code && $booking
                ? $booking->plotSaleDetails->where('booking_code', $representativeSale->booking_code)->values()
                : $sales->values();
            $bookingPlots = $bookingPlots
                ->filter(fn ($sale) => $sale->payments->contains('plan_type', 'full_payment'))
                ->values();
            $plotNumbers = $bookingPlots
                ->map(fn ($sale) => $sale->plotDetail?->plot_number)
                ->filter()
                ->unique()
                ->values();
            $plotLabel = $plotNumbers->implode(', ');

            return [
                'id' => $representativeSale->id,
                'plot_number' => ($plotLabel ?: 'Plot #'.$representativeSale->plot_detail_id)
                    .' ('.$plotNumbers->count().' '.($plotNumbers->count() === 1 ? 'Plot' : 'Plots').')',
                'booking_code' => $representativeSale->booking_code,
                'customer_name' => $booking?->primaryDetail?->name,
                'is_multiple' => $plotNumbers->count() > 1,
                'plots' => $plotNumbers->all(),
            ];
        })
            ->sortBy('plot_number')
            ->values();

        return response()->json($plots);
    }

    public function getBookingDetails($plotSaleDetailId)
    {
        $plotSale = PlotSaleDetail::with([
            'payments',
            'customerBooking.primaryDetail',
            'customerBooking.plotSaleDetails.project',
            'customerBooking.plotSaleDetails.block',
            'customerBooking.plotSaleDetails.plotDetail',
            'customerBooking.plotSaleDetails.payments',
            'customerBooking.payments',
        ])
            ->where('id', $plotSaleDetailId)
            ->whereHas('payments', function ($query) {
                $query->where('plan_type', 'full_payment');
            })
            ->first();

        if (!$plotSale || !$plotSale->customerBooking) {
            return response()->json([
                'status' => false,
                'message' => 'Booking not found',
            ]);
        }

        $booking = $plotSale->customerBooking;

        if (!$plotSale->payments->contains('plan_type', 'full_payment')) {
            return response()->json([
                'status' => false,
                'message' => 'Full payment booking not found for this plot.',
            ]);
        }

        $groupPlotSales = $plotSale->booking_code
            ? $booking->plotSaleDetails->where('booking_code', $plotSale->booking_code)->values()
            : collect([$plotSale]);
        $groupPlotSales = $groupPlotSales
            ->filter(fn ($sale) => $sale->payments->contains('plan_type', 'full_payment'))
            ->values();

        if ($groupPlotSales->isEmpty()) {
            $groupPlotSales = collect([$plotSale]);
        }

        $plotSaleIds = $groupPlotSales->pluck('id');

        $payments = CustomerPayment::with('plotSaleDetail.plotDetail')
            ->where('customer_booking_id', $booking->id)
            ->whereIn('plot_sale_detail_id', $plotSaleIds)
            ->where('plan_type', 'full_payment')
            ->get();

        $totalCost = round((float) $groupPlotSales->sum(function ($sale) {
            return (float) ($sale->total_plot_cost ?? 0);
        }), 2);
        $confirmedPaid = round((float) $payments
            ->whereIn('payment_status', ['paid', 'cleared'])
            ->sum('paid_amount'), 2);
        $holdAmount = round((float) $payments
            ->where('payment_status', 'hold')
            ->sum('paid_amount'), 2);
        $dueAmount = round(max(0, $totalCost - $confirmedPaid), 2);

        $paymentHistory = $payments
            ->sortByDesc('id')
            ->groupBy(function ($payment) {
                return $payment->receipt_number ?: 'payment-'.$payment->id;
            })
            ->map(function ($receiptPayments) {
                $payment = $receiptPayments->first();
                $plotNumbers = $receiptPayments
                    ->map(fn ($item) => $item->plotSaleDetail?->plotDetail?->plot_number)
                    ->filter()
                    ->unique()
                    ->implode(', ');
                $bookingStatuses = $receiptPayments
                    ->pluck('booking_status')
                    ->filter()
                    ->unique()
                    ->map(fn ($status) => ucfirst($status))
                    ->implode(', ');
                $paymentStatuses = $receiptPayments
                    ->pluck('payment_status')
                    ->filter()
                    ->unique()
                    ->map(fn ($status) => ucfirst($status))
                    ->implode(', ');

                return [
                    'receipt_no' => $payment->receipt_number,
                    'date' => $payment->created_at
                        ? $payment->created_at->format('d-M-Y')
                        : '-',
                    'paid_amount' => number_format((float) $receiptPayments->sum('paid_amount'), 2),
                    'payment_mode' => strtoupper(str_replace('_', '/', $payment->payment_mode)),
                    'booking_status' => $bookingStatuses ?: 'N/A',
                    'payment_status' => $paymentStatuses ?: 'N/A',
                    'plot_no' => $plotNumbers ?: '-',
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'booking_db_id' => $booking->id,
            'plot_sale_id' => $plotSale->id,
            'plot_sale_ids' => $plotSaleIds->values(),
            'is_multiple' => $groupPlotSales->count() > 1,

            // ab booking code plot_sale_details se aayega
            'booking_code' => $plotSale->booking_code ?? 'N/A',

            'customer_code' => $booking->customer_code,
            'customer_name' => $booking->primaryDetail?->name,

            'payment_type' => 'Full Payment',
            'total_cost' => number_format($totalCost, 2, '.', ''),
            'total_paid' => number_format($confirmedPaid, 2, '.', ''),
            'hold_amount' => number_format($holdAmount, 2, '.', ''),
            'due_amount' => number_format($dueAmount, 2, '.', ''),
            'plots' => $groupPlotSales->map(function ($sale) {
                return [
                    'plot_sale_id' => $sale->id,
                    'project' => $sale->project?->name ?? '-',
                    'block' => $sale->block?->block ?? '-',
                    'plot_no' => $sale->plotDetail?->plot_number ?? '-',
                    'area' => number_format((float) ($sale->plot_area ?? 0), 2),
                    'rate' => number_format((float) ($sale->plot_rate ?? 0), 2),
                    'plc' => number_format((float) ($sale->plc_amount ?? 0), 2),
                    'total_cost' => number_format((float) ($sale->total_plot_cost ?? 0), 2),
                ];
            })->values(),
            'payment_history' => $paymentHistory,
        ]);
    }
    public function store(OneTimePaymentRequest $request)
    {
        try {
            $this->service->store($request->validated());
        } catch (Exception $exception) {
            return back()
                ->withErrors(['booking_amount' => $exception->getMessage()])
                ->withInput();
        }

        return redirect()->back()->with('success', 'Payment added successfully');
    }
}