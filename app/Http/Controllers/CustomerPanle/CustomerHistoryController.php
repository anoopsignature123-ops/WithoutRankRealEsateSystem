<?php

namespace App\Http\Controllers\CustomerPanle;

use App\Http\Controllers\Controller;
use App\Models\Support;
use Illuminate\Http\Request;

class CustomerHistoryController extends Controller
{
    public function profile(Request $request)
    {
        $customer = auth()->guard('customer')->user();

        $customer->load([
            'primaryDetail.correspondenceDetail',
            'plotSaleDetails.project',
            'plotSaleDetails.block',
            'plotSaleDetails.plotDetail',
            'payments',
        ]);

        $plots = $customer->plotSaleDetails;
        $payments = $customer->payments;

        $totalBooking = $plots->count();

        $totalPlotCost = $plots->sum(function ($plot) {
            return $plot->total_plot_cost ?? $plot->total_amount ?? 0;
        });

        $totalPaid = $payments->sum(function ($payment) {
            return $payment->booking_amount ?? $payment->paid_amount ?? 0;
        });

        $dueAmount = max($totalPlotCost - $totalPaid, 0);
        $paidPercent = $totalPlotCost > 0 ? min(round(($totalPaid / $totalPlotCost) * 100), 100) : 0;
        $latestPlot = $plots->sortByDesc('created_at')->first();
        $latestPayment = $payments->sortByDesc('created_at')->first();

        return view('customer-panel.profile.index', compact(
            'customer',
            'plots',
            'payments',
            'totalBooking',
            'totalPlotCost',
            'totalPaid',
            'dueAmount',
            'paidPercent',
            'latestPlot',
            'latestPayment'
        ));
    }

    public function bookingHistory(Request $request)
    {
        $customer = auth()->guard('customer')->user();

        $customer->load([
            'primaryDetail.correspondenceDetail',
            'plotSaleDetails.project',
            'plotSaleDetails.block',
            'plotSaleDetails.plotDetail',
            'plotSaleDetails.payments',
            'payments',
        ]);

        $bookings = $customer->plotSaleDetails()
            ->with(['project', 'block', 'plotDetail'])
            ->whereNotNull('booking_code')
            ->latest()
            ->get();

        return view('customer-panel.booking-history.index', compact('customer', 'bookings'));
    }

    public function paymentHistory(Request $request)
    {
        $customer = auth()->guard('customer')->user();

        $payments = $customer->payments()
            ->with([
                'plotSaleDetail.project',
                'plotSaleDetail.block',
                'plotSaleDetail.plotDetail',
            ])
            ->latest()
            ->get();

        return view(
            'customer-panel.payment-history.index',
            compact('payments')
        );
    }


    

    public function myPlotBooking(Request $request)
{
    $customer = auth()->guard('customer')->user();

    $plots = $customer->plotSaleDetails()
        ->with([
            'project',
            'block',
            'plotDetail',
        ])
        ->whereNotNull('booking_code')
        ->latest()
        ->get();

    return view(
        'customer-panel.plot-histroy.index',
        compact('plots')
    );
}
    public function support(Request $request)
    {
        $enquiries = Support::where('customer_booking_id', auth()->guard('customer')->id())
            ->latest()
            ->get();

        return view('customer-panel.support.index', compact('enquiries'));
    }

    public function supportStore(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        Support::create([
            'customer_booking_id' => auth()->guard('customer')->id(),
            'query' => $request->input('query'),
            'description' => $request->input('description'),
            'status' => 'Pending',
        ]);

        return redirect()->route('customer-panel.support')
            ->with('success', 'Support ticket submitted successfully!');
    }
}
