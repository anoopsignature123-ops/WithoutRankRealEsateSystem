<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmiPaymentRequest;
use App\Models\Block;
use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use App\Models\PlotSaleDetail;
use App\Models\Project;
use App\Services\EmiPaymentService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;

class EmiPaymentController extends Controller
{
    protected EmiPaymentService $service;

    public function __construct(EmiPaymentService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $projects = Project::latest()->get();

        return view('payment.emi-payment.index', compact('projects'));
    }

    public function getBlocks(int $projectId): JsonResponse
    {
        $blocks = Block::where('project_id', $projectId)->orderBy('block')->get(['id', 'block']);

        return response()->json(['status' => true, 'data' => $blocks]);
    }

    public function getPlots(int $blockId): JsonResponse
    {
        $plotSales = PlotSaleDetail::with([
            'plotDetail',
            'customerBooking.primaryDetail',
            'customerBooking.plotSaleDetails.plotDetail',
            'customerBooking.plotSaleDetails.payments',
        ])
            ->where('block_id', $blockId)
            ->whereHas('payments', function ($query) {
                $query->where('plan_type', 'emi_plan')
                    ->where('booking_status', 'booked')
                    ->where('due_amount', '>', 0);
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
                ->filter(fn ($sale) => $sale->payments->contains('plan_type', 'emi_plan'))
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
            ];
        })
            ->sortBy('plot_number')
            ->values();

        if ($plots->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No pending EMI booking found in this block.',
            ]);
        }

        return response()->json(['status' => true, 'data' => $plots]);
    }

    public function getBookingDetails(int $plotSaleDetailId): JsonResponse
    {
        $saleDetail = PlotSaleDetail::with([
            'payments',
            'customerBooking.primaryDetail',
            'customerBooking.plotSaleDetails.project',
            'customerBooking.plotSaleDetails.block',
            'customerBooking.plotSaleDetails.plotDetail',
            'customerBooking.plotSaleDetails.payments',
        ])
            ->where('id', $plotSaleDetailId)
            ->whereHas('payments', function ($query) {
                $query->where('plan_type', 'emi_plan');
            })
            ->first();

        if (! $saleDetail || ! $saleDetail->customerBooking) {
            return response()->json(['status' => false, 'message' => 'EMI booking not found.']);
        }

        $booking = $saleDetail->customerBooking;

        if (! $saleDetail->payments->contains('plan_type', 'emi_plan')) {
            return response()->json(['status' => false, 'message' => 'EMI booking not found for this plot.']);
        }

        $groupPlotSales = $saleDetail->booking_code
            ? $booking->plotSaleDetails->where('booking_code', $saleDetail->booking_code)->values()
            : collect([$saleDetail]);
        $groupPlotSales = $groupPlotSales
            ->filter(fn ($sale) => $sale->payments->contains('plan_type', 'emi_plan'))
            ->values();

        if ($groupPlotSales->isEmpty()) {
            $groupPlotSales = collect([$saleDetail]);
        }

        $plotSaleIds = $groupPlotSales->pluck('id');

        $payments = CustomerPayment::with('plotSaleDetail.plotDetail')
            ->where('customer_booking_id', $booking->id)
            ->whereIn('plot_sale_detail_id', $plotSaleIds)
            ->where('plan_type', 'emi_plan')
            ->orderBy('id')
            ->get();

        $dueDetails = $this->service->calculateDues($booking->id, $groupPlotSales);
        $firstPayment = $payments->sortBy('id')->first();

        $totalCost = round((float) $groupPlotSales->sum(fn ($sale) => (float) ($sale->total_plot_cost ?? 0)), 2);
        $confirmedPayments = $payments->whereIn('payment_status', ['paid', 'cleared']);
        $holdPayments = $payments->where('payment_status', 'hold');

        $totalPaid = round((float) $confirmedPayments->sum('paid_amount'), 2);
        $holdAmount = round((float) $holdPayments->sum('paid_amount'), 2);
        $dueAmount = round((float) $dueDetails->sum('due'), 2);
        $bookingAmount = round((float) $payments->where('transaction_category', 'booking_fee')->sum('booking_amount'), 2);
        if ($bookingAmount <= 0) {
            $bookingAmount = round((float) ($firstPayment->booking_amount ?? 0), 2);
        }
        $emiMonths = (int) $dueDetails->max('emi_months');
        $monthlyEmi = round((float) $dueDetails->sum('monthly_emi'), 2);
        $emiStartDate = $firstPayment?->created_at
            ? Carbon::parse($firstPayment->created_at)->format('d-M-Y')
            : '-';
        $monthsPassed = $payments
            ->where('transaction_category', 'emi_payment')
            ->whereIn('payment_status', ['paid', 'cleared'])
            ->groupBy(fn ($payment) => $payment->receipt_number ?: 'payment-'.$payment->id)
            ->count();
        $emiReceiptGroups = $payments
            ->where('transaction_category', 'emi_payment')
            ->groupBy(fn ($payment) => $payment->receipt_number ?: 'payment-'.$payment->id);
        $groupPaidInstallments = $emiReceiptGroups
            ->filter(fn ($receiptPayments) => $receiptPayments->whereIn('payment_status', ['paid', 'cleared'])->isNotEmpty())
            ->count();
        $groupHoldInstallments = $emiReceiptGroups
            ->filter(function ($receiptPayments) {
                return $receiptPayments->whereIn('payment_status', ['paid', 'cleared'])->isEmpty()
                    && $receiptPayments->where('payment_status', 'hold')->isNotEmpty();
            })
            ->count();
        $groupRemainingInstallments = $this->service->calculateRemainingEmiMonths($dueAmount, $monthlyEmi);
        $groupTotalInstallments = $groupPaidInstallments + $groupHoldInstallments + $groupRemainingInstallments;
        $groupProgressPercent = $groupTotalInstallments > 0
            ? min(100, round(($groupPaidInstallments / $groupTotalInstallments) * 100))
            : 0;

        $history = $payments->sortByDesc('id')->groupBy(fn ($payment) => $payment->receipt_number ?: 'payment-'.$payment->id)->map(function ($receiptPayments) {
            $payment = $receiptPayments->first();
            $plotNumbers = $receiptPayments
                ->map(fn ($item) => $item->plotSaleDetail?->plotDetail?->plot_number)
                ->filter()
                ->unique()
                ->implode(', ');
            $statuses = $receiptPayments->pluck('payment_status')->filter()->unique();
            $status = $statuses->count() === 1 ? $statuses->first() : $statuses->implode(', ');

            return [
                'receipt_no' => $payment->receipt_number,
                'date' => $payment->created_at ? $payment->created_at->format('d-M-Y') : '-',
                'amount' => number_format((float) $receiptPayments->sum('paid_amount'), 2),
                'mode' => strtoupper(str_replace('_', '/', $payment->payment_mode)),
                'booking_status' => $payment->booking_status,
                'payment_status' => $status,
                'status' => ucfirst($status ?: 'N/A'),
                'plot_no' => $plotNumbers ?: '-',
            ];
        })->values();

        return response()->json([
            'status' => true,
            'booking_db_id' => $booking->id,
            'plot_sale_id' => $saleDetail->id,
            'plot_sale_ids' => $plotSaleIds->values(),
            'is_multiple' => $groupPlotSales->count() > 1,
            'booking_code' => $saleDetail->booking_code ?? 'N/A',
            'customer_code' => $booking->customer_code,
            'customer_name' => $booking->primaryDetail?->name,
            'total_cost' => number_format($totalCost, 2, '.', ''),
            'booking_amount' => number_format($bookingAmount, 2, '.', ''),
            'total_paid' => number_format($totalPaid, 2, '.', ''),
            'hold_amount' => number_format($holdAmount, 2, '.', ''),
            'due_amount' => number_format($dueAmount, 2, '.', ''),
            'emi_months' => $emiMonths,
            'months_passed' => $monthsPassed,
            'monthly_emi' => number_format($monthlyEmi, 2, '.', ''),
            'emi_start_date' => $emiStartDate,
            'emi_overview' => [
                'total_installments' => $groupTotalInstallments,
                'paid_installments' => $groupPaidInstallments,
                'hold_installments' => $groupHoldInstallments,
                'remaining_installments' => $groupRemainingInstallments,
                'progress_percent' => $groupProgressPercent,
            ],
            'plots' => $groupPlotSales->map(function ($sale) use ($dueDetails, $payments) {
                $dueInfo = $dueDetails->firstWhere('plot_sale_id', $sale->id);
                $plotEmiReceiptGroups = $payments
                    ->where('plot_sale_detail_id', $sale->id)
                    ->where('transaction_category', 'emi_payment')
                    ->groupBy(fn ($payment) => $payment->receipt_number ?: 'payment-'.$payment->id);
                $paidInstallments = $plotEmiReceiptGroups
                    ->filter(fn ($receiptPayments) => $receiptPayments->whereIn('payment_status', ['paid', 'cleared'])->isNotEmpty())
                    ->count();
                $holdInstallments = $plotEmiReceiptGroups
                    ->filter(function ($receiptPayments) {
                        return $receiptPayments->whereIn('payment_status', ['paid', 'cleared'])->isEmpty()
                            && $receiptPayments->where('payment_status', 'hold')->isNotEmpty();
                    })
                    ->count();
                $remainingInstallments = (int) ($dueInfo['emi_months'] ?? 0);
                $totalInstallments = $paidInstallments + $holdInstallments + $remainingInstallments;
                $progressPercent = $totalInstallments > 0
                    ? min(100, round(($paidInstallments / $totalInstallments) * 100))
                    : 0;

                return [
                    'plot_sale_id' => $sale->id,
                    'project' => $sale->project?->name ?? '-',
                    'block' => $sale->block?->block ?? '-',
                    'plot_no' => $sale->plotDetail?->plot_number ?? '-',
                    'area' => number_format((float) ($sale->plot_area ?? 0), 2),
                    'total_cost' => number_format((float) ($sale->total_plot_cost ?? 0), 2),
                    'monthly_emi' => number_format((float) ($dueInfo['monthly_emi'] ?? 0), 2),
                    'due_amount' => number_format((float) ($dueInfo['due'] ?? 0), 2),
                    'total_installments' => $totalInstallments,
                    'paid_installments' => $paidInstallments,
                    'hold_installments' => $holdInstallments,
                    'remaining_installments' => $remainingInstallments,
                    'progress_percent' => $progressPercent,
                ];
            })->values(),
            'payment_history' => $history,
        ]);
    }

    public function store(EmiPaymentRequest $request)
    {
        $data = $request->validated();

        $plotSaleIds = collect($data['plot_sale_detail_ids'] ?? [])
            ->push($data['plot_sale_detail_id'] ?? null)
            ->filter()
            ->unique()
            ->values();
        $plotSales = PlotSaleDetail::whereIn('id', $plotSaleIds)
            ->where('customer_booking_id', $data['customer_booking_id'])
            ->get();

        if ($plotSales->isEmpty() || $plotSales->count() !== $plotSaleIds->count()) {
            return back()->withErrors(['booking_amount' => 'Booking record not found.'])->withInput();
        }

        $dueDetails = $this->service->calculateDues((int) $data['customer_booking_id'], $plotSales);
        $dueAmount = round((float) $dueDetails->sum('due'), 2);
        $paidAmount = round((float) $data['booking_amount'], 2);
        $monthlyEmi = round((float) $dueDetails->sum('monthly_emi'), 2);

        if (($paidAmount - $dueAmount) > 0.01) {
            return back()->withErrors(['booking_amount' => 'EMI amount cannot be greater than due amount.'])->withInput();
        }

        if ($paidAmount > $dueAmount) {
            $data['booking_amount'] = $dueAmount;
            $paidAmount = $dueAmount;
        }

        if ($paidAmount < $monthlyEmi && $paidAmount < $dueAmount) {
            return back()
                ->withErrors(['booking_amount' => 'Minimum EMI amount is Rs. ' . number_format($monthlyEmi, 2)])
                ->withInput();
        }

        try {
            $this->service->store($data);
        } catch (Exception $exception) {
            return back()->withErrors(['booking_amount' => $exception->getMessage()])->withInput();
        }

        return back()->with('success', 'EMI payment added successfully.');
    }
}