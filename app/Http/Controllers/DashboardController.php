<?php

namespace App\Http\Controllers;

use App\Models\Associate;
use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use App\Models\PlotDetail;
use App\Models\PlotRegistry;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $businessChartData = $this->getVisitorsData();

        $rootAssociateIds = Associate::whereNull('under_place_id')->pluck('associate_id');

        $data = [
            'projectCount' => Project::count(),
            'totalPlot' => PlotDetail::count(),
            'totalCustomer' => CustomerBooking::whereNotNull('customer_code')->count(),
            'totalAssociate' => Associate::count(),

            'allDirectAssociate' => Associate::whereIn('sponsor_id', $rootAssociateIds)->count(),
            'leftDirectAssociate' => Associate::whereIn('sponsor_id', $rootAssociateIds)
                ->where('direction', 'left')
                ->count(),
            'rightDirectAssociate' => Associate::whereIn('sponsor_id', $rootAssociateIds)
                ->where('direction', 'right')
                ->count(),

            'allTeamCount' => Associate::whereNotNull('under_place_id')->count(),
            'leftTeamCount' => Associate::whereNotNull('under_place_id')
                ->where('direction', 'left')
                ->count(),
            'rightTeamCount' => Associate::whereNotNull('under_place_id')
                ->where('direction', 'right')
                ->count(),

            'plotStats' => $this->getPlotStats(),
            'visitorsData' => $this->getVisitorsData(),
            'monthlyDues' => $this->getMonthlyDues(),
            'totalOutstanding' => $this->calculateOutstanding(),

            'confirmedPayment' => CustomerPayment::where('booking_status', 'booked')
                ->whereIn('payment_status', ['paid', 'cleared'])
                ->sum('paid_amount'),

            'holdPayment' => CustomerPayment::where('payment_status', 'hold')->sum('paid_amount'),
            'pendingPayment' => $this->calculateOutstanding(),
            'businessConfirmedPayment' => $businessChartData['monthlyPaidAmount'][0] ?? 0,
            'businesspendingPayment' => $businessChartData['monthlyDueAmount'][0] ?? 0,
        ];

        return view('dashboard', array_merge($data, $data['plotStats']));
    }

    private function getPlotStats()
    {
        $stats = PlotDetail::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'booked' => $stats['booked'] ?? 0,
            'hold' => $stats['hold'] ?? 0,
            'registry' => $stats['registry'] ?? PlotRegistry::count(),
            'available' => $stats['available'] ?? 0,
        ];
    }

    private function getMonthlyDues()
    {
        $monthlyDues = collect();

        $emiPlans = CustomerPayment::with([
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->where('plan_type', 'emi_plan')
            ->where('booking_status', 'booked')
            ->where('transaction_category', 'booking_fee')
            ->whereNotNull('customer_booking_id')
            ->whereNotNull('plot_sale_detail_id')
            ->whereNotNull('emi_months')
            ->where('emi_months', '>', 0)
            ->get();

        $currentMonth = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();

        foreach ($emiPlans as $emiPlan) {
            $emiStartDate = Carbon::parse($emiPlan->emi_date ?? $emiPlan->created_at)->startOfMonth();

            $monthDifference = $emiStartDate->diffInMonths($currentMonth, false);

            if ($monthDifference < 0 || $monthDifference >= $emiPlan->emi_months) {
                continue;
            }

            $monthlyEmiAmount = (float) ($emiPlan->after_booking_payable_amount ?? 0);

            if ($monthlyEmiAmount <= 0) {
                $monthlyEmiAmount = (float) ($emiPlan->due_amount / $emiPlan->emi_months);
            }

            $totalPaidTillCurrentMonth = CustomerPayment::where('customer_booking_id', $emiPlan->customer_booking_id)
                ->where('plot_sale_detail_id', $emiPlan->plot_sale_detail_id)
                ->where('transaction_category', 'emi_payment')
                ->where('booking_status', 'booked')
                ->whereIn('payment_status', ['paid', 'cleared'])
                ->whereDate('created_at', '<=', $currentMonthEnd)
                ->sum('paid_amount');

            $totalDueBeforeCurrentMonth = $monthlyEmiAmount * $monthDifference;

            $paidAdjustedForCurrentMonth = $totalPaidTillCurrentMonth - $totalDueBeforeCurrentMonth;

            $paidAdjustedForCurrentMonth = max(0, min($paidAdjustedForCurrentMonth, $monthlyEmiAmount));

            $currentMonthPending = $monthlyEmiAmount - $paidAdjustedForCurrentMonth;

            if ($currentMonthPending > 0) {
                $emiPlan->due_amount = round($currentMonthPending, 2);
                $emiPlan->emi_date = $currentMonth;

                $monthlyDues->push($emiPlan);
            }
        }

        return $monthlyDues
            ->groupBy(function ($due) {
                $bookingCode = $due->plotSaleDetail?->booking_code
                    ?: $due->customerBooking?->booking_code
                    ?: 'booking-' . $due->customer_booking_id;

                return implode('|', [
                    $due->customer_booking_id,
                    $bookingCode,
                    Carbon::parse($due->emi_date)->format('Y-m-d'),
                ]);
            })
            ->map(function ($group) {
                $first = $group->first();
                $plotSales = $group->pluck('plotSaleDetail')->filter();

                $first->group_due_amount = round((float) $group->sum('due_amount'), 2);
                $first->group_plot_count = $plotSales->count();
                $first->group_projects = $plotSales
                    ->map(fn($sale) => $sale?->project?->name)->filter()->unique()->implode(', ');
                $first->group_blocks = $plotSales
                    ->map(fn($sale) => $sale?->block?->block)->filter()->unique()->implode(', ');
                $first->group_plot_numbers = $plotSales
                    ->map(fn($sale) => $sale?->plotDetail?->plot_number)->filter()->unique()->implode(', ');
                return $first;
            })
            ->sortByDesc('id')
            ->values();
    }

    private function calculateOutstanding()
    {
        return CustomerPayment::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('customer_payments')
                ->whereNotNull('plot_sale_detail_id')
                ->groupBy('customer_booking_id', 'plot_sale_detail_id');
        })
            ->whereIn('payment_status', ['pending', 'paid', 'hold'])
            ->where('booking_status', 'booked')
            ->where('due_amount', '>', 0)
            ->sum('due_amount');
    }

    private function calculateOverdue()
    {
        return CustomerPayment::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('customer_payments')
                ->whereNotNull('plot_sale_detail_id')
                ->groupBy('customer_booking_id', 'plot_sale_detail_id');
        })
            ->where('plan_type', 'emi_plan')
            ->whereIn('payment_status', ['pending', 'paid', 'hold'])
            ->where('booking_status', 'booked')
            ->where('due_amount', '>', 0)
            ->whereNotNull('emi_date')
            ->whereDate('emi_date', '<', now())
            ->sum('due_amount');
    }

    private function getVisitorsData()
    {
        $labels = [];
        $monthlyPaidAmount = [];
        $monthlyDueAmount = [];

        $startMonth = now()->startOfMonth();

        $emiPlans = CustomerPayment::where('plan_type', 'emi_plan')
            ->where('booking_status', 'booked')
            ->where('transaction_category', 'booking_fee')
            ->whereNotNull('customer_booking_id')
            ->whereNotNull('plot_sale_detail_id')
            ->whereNotNull('emi_months')
            ->where('emi_months', '>', 0)
            ->get();

        for ($i = 0; $i < 10; $i++) {

            $month = $startMonth->copy()->addMonths($i)->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            $labels[] = $month->format('M');
            $paidAmount = 0;
            $pendingAmount = 0;
            foreach ($emiPlans as $emiPlan) {
                $emiStartDate = Carbon::parse($emiPlan->emi_date ?? $emiPlan->created_at)
                    ->startOfMonth();
                $monthDifference = $emiStartDate->diffInMonths($month, false);
                if ($monthDifference < 0 || $monthDifference >= $emiPlan->emi_months) {
                    continue;
                }
                $monthlyEmiAmount = (float) ($emiPlan->after_booking_payable_amount ?? 0);
                if ($monthlyEmiAmount <= 0) {
                    $monthlyEmiAmount = (float) ($emiPlan->due_amount / $emiPlan->emi_months);
                }
                $paidThisMonth = CustomerPayment::where('customer_booking_id', $emiPlan->customer_booking_id)
                    ->where('plot_sale_detail_id', $emiPlan->plot_sale_detail_id)
                    ->where('transaction_category', 'emi_payment')
                    ->where('booking_status', 'booked')
                    ->whereIn('payment_status', ['paid', 'cleared'])
                    ->whereBetween('created_at', [
                        $month->copy()->startOfMonth(),
                        $monthEnd,
                    ])->sum('paid_amount');
                $totalPaidTillThisMonth = CustomerPayment::where('customer_booking_id', $emiPlan->customer_booking_id)
                    ->where('plot_sale_detail_id', $emiPlan->plot_sale_detail_id)
                    ->where('transaction_category', 'emi_payment')->where('booking_status', 'booked')
                    ->whereIn('payment_status', ['paid', 'cleared'])
                    ->whereDate('created_at', '<=', $monthEnd)->sum('paid_amount');
                $totalDueBeforeThisMonth = $monthlyEmiAmount * $monthDifference;
                $paidAdjustedForThisMonth = $totalPaidTillThisMonth - $totalDueBeforeThisMonth;
                if ($paidAdjustedForThisMonth < 0) {
                    $paidAdjustedForThisMonth = 0;
                }
                if ($paidAdjustedForThisMonth > $monthlyEmiAmount) {
                    $paidAdjustedForThisMonth = $monthlyEmiAmount;
                }
                $currentMonthPending = $monthlyEmiAmount - $paidAdjustedForThisMonth;
                if ($currentMonthPending < 0) {
                    $currentMonthPending = 0;
                }
                $paidAmount += (float) $paidThisMonth;
                $pendingAmount += (float) $currentMonthPending;
            }
            $monthlyPaidAmount[] = round($paidAmount, 2);
            $monthlyDueAmount[] = round($pendingAmount, 2);
        }
        return ['labels' => $labels, 'monthlyPaidAmount' => $monthlyPaidAmount, 'monthlyDueAmount' => $monthlyDueAmount];
    }
}