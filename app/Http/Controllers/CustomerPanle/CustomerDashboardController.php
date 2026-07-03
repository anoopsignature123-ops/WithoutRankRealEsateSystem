<?php

namespace App\Http\Controllers\CustomerPanle;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CustomerDashboardController extends Controller
{
    public function dashboard()
    {
        $customer = auth()->guard('customer')->user();

        $customer->load([
            'primaryDetail.correspondenceDetail',
            'primaryDocument',
            'plotSaleDetails' => fn ($query) => $query->whereHas('payments', function ($paymentQuery) {
                $paymentQuery->where('booking_status', 'booked');
            }),
            'plotSaleDetails.project',
            'plotSaleDetails.block',
            'plotSaleDetails.plotDetail',
            'payments' => fn ($query) => $query->where('booking_status', 'booked'),
            'payments.plotSaleDetail.plotDetail',
        ]);

        $plots = $customer->plotSaleDetails;
        $payments = $customer->payments;

        $totalBooking = $plots->whereNotNull('booking_code')->count();
        $totalPlotCost = $plots->sum(fn ($plot) => $plot->total_plot_cost ?? $plot->final_payable ?? 0);
        $totalPaid = $payments
            ->whereIn('payment_status', ['paid', 'cleared'])
            ->sum(fn ($payment) => $payment->paid_amount ?? $payment->booking_amount ?? 0);
        $dueAmount = max($totalPlotCost - $totalPaid, 0);
        $pendingPayments = $payments
            ->whereIn('payment_status', ['pending', 'hold', 'paid'])
            ->where('due_amount', '>', 0)
            ->count();
        $paidPercent = $totalPlotCost > 0 ? min(round(($totalPaid / $totalPlotCost) * 100), 100) : 0;
        $bookingGroups = $plots
            ->whereNotNull('booking_code')
            ->groupBy(fn ($plot) => $plot->booking_code ?: 'plot-'.$plot->id)
            ->map(function (Collection $group) use ($payments) {
                $first = $group->first();
                $groupPayments = $payments->whereIn('plot_sale_detail_id', $group->pluck('id'));
                $groupCost = $group->sum(fn ($plot) => (float) ($plot->total_plot_cost ?? $plot->final_payable ?? 0));
                $groupPaid = $groupPayments
                    ->whereIn('payment_status', ['paid', 'cleared'])
                    ->sum(fn ($payment) => (float) ($payment->paid_amount ?? $payment->booking_amount ?? 0));
                $planTypes = $groupPayments->pluck('plan_type')->filter()->unique()->values();

                return [
                    'booking_code' => $first?->booking_code ?? '-',
                    'project' => $group->pluck('project.name')->filter()->unique()->implode(', ') ?: 'N/A',
                    'block' => $group->pluck('block.block')->filter()->unique()->implode(', ') ?: 'N/A',
                    'plots' => $group->pluck('plotDetail.plot_number')->filter()->implode(', ') ?: 'N/A',
                    'plot_count' => $group->count(),
                    'total_area' => $group->sum(fn ($plot) => (float) ($plot->plot_area ?? 0)),
                    'total_cost' => $groupCost,
                    'paid' => $groupPaid,
                    'due' => max($groupCost - $groupPaid, 0),
                    'plan_type' => $planTypes->count() > 1 ? 'mixed' : ($planTypes->first() ?? 'full_payment'),
                    'created_at' => $group->max('created_at'),
                    'booking_date' => $first?->booking_date,
                ];
            })
            ->sortByDesc('created_at')
            ->values();
        $latestBookings = $bookingGroups->take(4);
        $latestPayments = $payments->sortByDesc('created_at')->take(4);

        return view('customer_dashboard', compact(
            'customer',
            'plots',
            'payments',
            'totalBooking',
            'totalPlotCost',
            'totalPaid',
            'dueAmount',
            'pendingPayments',
            'paidPercent',
            'bookingGroups',
            'latestBookings',
            'latestPayments'
        ));
    }

}
