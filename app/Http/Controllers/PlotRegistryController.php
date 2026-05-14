<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlotRegistryRequest;
use App\Models\Block;
use App\Models\CustomerBooking;
use App\Models\PlotDetail;
use App\Models\Project;
use App\Services\PlotRegistryService;

class PlotRegistryController extends Controller
{
    protected $plotRegistryService;

    public function __construct(
        PlotRegistryService $plotRegistryService
    ) {
        $this->plotRegistryService = $plotRegistryService;
    }

    public function index()
    {
        $projects = Project::all();

        return view(
            'plot-registry.index',
            compact('projects')
        );
    }

    public function getBlocks($projectId)
    {
        return response()->json(
            Block::where(
                'project_id',
                $projectId
            )->get()
        );
    }

    public function getPlots($blockId)
    {
        return response()->json(
            PlotDetail::where(
                'block_id',
                $blockId
            )->get()
        );
    }

    public function getBookingData($plotId)
    {
        $booking = CustomerBooking::with([
            'primaryDetail',
            'payment',
            'plotSaleDetail',
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
            ->first();

        if (! $booking) {

            return response()->json([
                'status' => false,
            ]);
        }

        return response()->json([

            'status' => true,

            'booking_db_id' => $booking->id,

            'booking_id' => $booking->booking_code,

            'customer_id' => $booking->customer_code,

            'customer_name' => $booking->primaryDetail?->name,

            'payment' => [

                'amount' => $booking->payment?->booking_amount,

                'date' => optional(
                    $booking->payment?->created_at
                )->format('d/m/Y'),

                'mode' => ucfirst(
                    $booking->payment?->payment_mode
                ),

                'cheque_no' => $booking->payment?->cheque_number,

            ],

        ]);
    }

    public function store(PlotRegistryRequest $request
    ) {
        $this->plotRegistryService->create(
            $request->validated()
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Plot registry created successfully.'
            );
    }
}
