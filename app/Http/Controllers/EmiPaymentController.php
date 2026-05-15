<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmiPaymentRequest;
use App\Models\Block;
use App\Models\CustomerBooking;
use App\Models\PlotDetail;
use App\Models\Project;
use App\Services\EmiPaymentService;
use Carbon\Carbon;
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
        $plots = PlotDetail::where('block_id', $blockId)->whereHas('plotSaleDetail.customerBooking.payments',
            function ($query) {
                $query->where('plan_type', 'emi_plan');
            }
        )
            ->orderBy('plot_number')->get(['id', 'plot_number']);

        return response()->json($plots);
    }

    public function getBookingDetails(int $plotId): JsonResponse
    {
        $booking = CustomerBooking::with(['primaryDetail', 'plotSaleDetail', 'payments'])
            ->whereHas('plotSaleDetail', function ($query) use ($plotId) {
                $query->where('plot_detail_id', $plotId);
            }
            )
            ->whereHas('payments', function ($query) {
                $query->where('plan_type', 'emi_plan');
            })->first();
        if (! $booking) {
            return response()->json(['status' => false]);
        }
        $saleDetail = $booking->plotSaleDetail;
        $payments = $booking->payments->where('plan_type', 'emi_plan');
        $firstPayment = $payments->first();
        $totalCost = $saleDetail->total_plot_cost ?? 0;
        $totalPaid = $payments->sum('booking_amount');
        $dueAmount = $totalCost - $totalPaid;
        $emiMonths = $firstPayment?->emi_months ?? 1;
        $monthlyEmi = round($totalCost / $emiMonths, 2);
        $emiStartDate = '-';
        $monthsPassed = 1;
        if ($firstPayment?->created_at) {
            $startDate = Carbon::parse($firstPayment->created_at);
            $emiStartDate = $startDate->format('d-M-Y');
            $monthsPassed = (int) $startDate->diffInMonths(now()) + 1;
            if ($monthsPassed > $emiMonths) {
                $monthsPassed = $emiMonths;
            }
        }
        $history = $payments->map(function ($payment) {
            return [
                'receipt_no' => $payment->receipt_number,
                'date' => $payment->created_at ? $payment->created_at->format('d-M-Y') : '-',
                'amount' => $payment->booking_amount,
                'mode' => strtoupper($payment->payment_mode),
            ];
        }
        );

        return response()->json([
            'status' => true,
            'booking_db_id' => $booking->id,
            'plot_sale_id' => $saleDetail->id,
            'booking_code' => $booking->booking_code,
            'customer_code' => $booking->customer_code,
            'customer_name' => $booking->primaryDetail?->name,
            'total_cost' => $totalCost,
            'booking_amount' => $firstPayment?->booking_amount ?? 0,
            'total_paid' => $totalPaid,
            'due_amount' => $dueAmount,
            'emi_months' => $emiMonths,
            'emi_start_date' => $emiStartDate,
            'months_passed' => $monthsPassed,
            'monthly_emi' => $monthlyEmi,
            'payment_history' => $history,
        ]);
    }

    public function store(EmiPaymentRequest $request)
    {
        $this->service->store($request->validated());

        return back()->with('success', 'EMI Payment Added Successfully');
    }
}
