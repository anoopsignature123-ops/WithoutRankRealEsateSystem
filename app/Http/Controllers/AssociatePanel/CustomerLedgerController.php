<?php

namespace App\Http\Controllers\AssociatePanel;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use App\Models\PlotDetail;
use App\Models\Project;
use Illuminate\Http\Request;

class CustomerLedgerController extends Controller
{
    public function customerLedger(Request $request)
    {
        $projects = Project::latest()->get();
        $blocks = [];
        $plots = [];
        $ledgerData = null;
        if ($request->filled('project_id')) {
            $blocks = Block::where('project_id', $request->project_id)->get();
        }
        if ($request->filled('block_id')) {
            $plots = PlotDetail::where('block_id', $request->block_id)->get();
        }

        if ($request->filled('booking_id')) {
            $booking = CustomerBooking::with([
                'primaryDetail',
                'associate',
                'plotSaleDetail.plotDetail.block.project',
            ])
                ->where('booking_code', $request->booking_id)
                ->first();

            if ($booking) {
                $payments = CustomerPayment::where('customer_booking_id', $booking->id)
                    ->latest()
                    ->get();

                $plotSale = $booking->plotSaleDetail;
                $plot = $plotSale?->plotDetail;
                $block = $plot?->block;
                $project = $block?->project;

                $ledgerData = (object) [
                    'booking' => $booking,
                    'customer_name' => $booking->primaryDetail?->name ?? '-',
                    'customer_id' => $booking->customer_code ?? '-',
                    'associate_name' => $booking->associate?->associate_name ?? '-',
                    'project_name' => $project?->name ?? '-',
                    'block_name' => $block?->block ?? '-',
                    'plot_no' => $plot?->plot_number ?? '-',
                    'plot_amount' => $plotSale?->plot_cost ?? 0,
                    'payments' => $payments,
                    'total_paid' => $payments->sum('net_payable_amount'),
                ];
            }
        }

        return view('associate-panel.customer-ledger.index', compact('projects', 'blocks', 'plots', 'ledgerData'));
    }
}
