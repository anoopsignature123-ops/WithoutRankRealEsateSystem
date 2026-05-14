<?php

namespace App\Http\Controllers;

use App\Models\CustomerBooking;
use App\Models\PlotDetail;
use App\Services\ReceiptReprintService;
use Illuminate\Http\Request;

class ReceiptReprintController extends Controller
{
    protected $service;

    public function __construct(
        ReceiptReprintService $service
    ) {
        $this->service = $service;
    }

    public function index()
    {
        $plots = PlotDetail::all();

        return view(
            'payment.receipt-reprint.index',
            compact('plots')
        );
    }

    public function search(Request $request)
    {
        $request->validate([

            'plot_id' => 'required',
            'customer_id' => 'required',

        ]);

        $plots = PlotDetail::all();

        $receipts = $this->service->search(
            $request->plot_id,
            $request->customer_id
        );

        return view(
            'payment.receipt-reprint.index',
            compact(
                'plots',
                'receipts'
            )
        );
    }

    public function download($paymentId)
    {
        return $this->service->downloadPdf(
            $paymentId
        );
    }

    public function getCustomersByPlot($plotId)
    {
        $customers = CustomerBooking::with(
            'primaryDetail'
        )
            ->whereHas(
                'plotSaleDetail',
                function ($query) use ($plotId) {

                    $query->where(
                        'plot_detail_id',
                        $plotId
                    );
                }
            )
            ->get()
            ->map(function ($booking) {

                return [

                    'id' => $booking->customer_code,

                    'text' => $booking->customer_code.
                        ' / '.
                        ($booking->primaryDetail?->name ?? ''),

                ];
            });

        return response()->json(
            $customers
        );
    }
}
