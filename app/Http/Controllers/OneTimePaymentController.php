<?php

namespace App\Http\Controllers;

use App\Http\Requests\OneTimePaymentRequest;
use App\Models\Block;
use App\Models\CustomerBooking;
use App\Models\PlotDetail;
use App\Models\Project;
use App\Services\OneTimePaymentService;

class OneTimePaymentController extends Controller
{
    public function __construct(
        protected OneTimePaymentService $service
    ) {}

    public function index()
    {
        $projects = Project::orderBy('name')
            ->get();

        return view(
            'payment.one-time-payment.index',
            compact('projects')
        );
    }

    public function getBlocks($projectId)
    {
        $blocks = Block::where(
            'project_id',
            $projectId
        )
            ->orderBy('block')
            ->get();

        return response()->json($blocks);
    }

    public function getPlots($blockId)
    {
        $plots = PlotDetail::where(
            'block_id',
            $blockId
        )
            ->orderBy('plot_number')
            ->get();

        return response()->json($plots);
    }

    public function getBookingDetails($plotId)
    {
        $booking = CustomerBooking::with([
            'primaryDetail',
            'plotSaleDetail',
            'payments',
        ])
            ->whereHas(
                'plotSaleDetail',
                function ($query) use ($plotId) {

                    $query->where(
                        'plot_detail_id',
                        $plotId
                    );
                }
            )
            ->whereHas(
                'payments',
                function ($query) {

                    $query->where(
                        'plan_type',
                        'full_payment'
                    );
                }
            )
            ->first();

        if (! $booking) {

            return response()->json([
                'status' => false,
                'message' => 'Booking not found',
            ]);
        }

        $plotSale = $booking->plotSaleDetail;

        if (! $plotSale) {

            return response()->json([
                'status' => false,
                'message' => 'Plot sale details not found',
            ]);
        }

        $totalCost = (float) $plotSale->total_plot_cost;

        $totalPaid = (float) $booking
            ->payments()
            ->sum('booking_amount');

        $dueAmount = $totalCost - $totalPaid;

        if ($dueAmount < 0) {
            $dueAmount = 0;
        }

        $paymentHistory = $booking
            ->payments()
            ->latest()
            ->get()
            ->map(function ($payment) {

                return [
                    'receipt_no' => $payment->receipt_number,
                    'date' => $payment->created_at->format('d-M-Y'),
                    'paid_amount' => $payment->booking_amount,
                    'payment_mode' => strtoupper(
                        str_replace(
                            '_',
                            '/',
                            $payment->payment_mode
                        )
                    ),
                ];
            });

        return response()->json([

            'status' => true,

            'booking_db_id' => $booking->id,

            'plot_sale_id' => $plotSale->id,

            'booking_code' => $booking->booking_code,

            'customer_code' => $booking->customer_code,

            'customer_name' => $booking->primaryDetail?->name,

            'payment_type' => 'Full Payment',

            'total_cost' => number_format(
                $totalCost,
                2,
                '.',
                ''
            ),

            'total_paid' => number_format(
                $totalPaid,
                2,
                '.',
                ''
            ),

            'due_amount' => number_format(
                $dueAmount,
                2,
                '.',
                ''
            ),

            'payment_history' => $paymentHistory,

        ]);
    }

    public function store(OneTimePaymentRequest $request) {
        $this->service->store(
            $request->validated()
        );
        return redirect()->back()->with('success','Payment added successfully');
    }
}
