<?php

namespace App\Http\Controllers;

use App\Models\Associate;
use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use App\Models\PlotDetail;
use App\Models\PlotRegistry;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'projectCount' => Project::count(),
            'totalPlot' => PlotDetail::count(),
            'totalCustomer' => CustomerBooking::whereNotNull('customer_code')->count(),
            'totalAssociate' => Associate::count(),
            'plotStats' => $this->getPlotStats(),
            'visitorsData' => $this->getVisitorsData(),
            'monthlyDues' => $this->getMonthlyDues(),
            'totalOutstanding' => $this->calculateOutstanding(),
            'totalOverdue' => $this->calculateOverdue(),

            'confirmedPayment' => CustomerPayment::sum('booking_amount'),
            'pendingPayment' => CustomerPayment::sum('due_amount'),
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
        return CustomerPayment::with([
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->where('plan_type', 'emi_plan')
            ->where('payment_status', 'pending')
            ->where('due_amount', '>', 0)
            ->whereMonth('emi_date', now()->month)
            ->whereYear('emi_date', now()->year)
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('customer_payments')
                    ->where('plan_type', 'emi_plan')
                    ->whereNotNull('plot_sale_detail_id')
                    ->groupBy('customer_booking_id', 'plot_sale_detail_id');
            })
            ->latest()
            ->get();
    }

    private function calculateOutstanding()
    {
        return CustomerPayment::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('customer_payments')
                ->whereNotNull('plot_sale_detail_id')
                ->groupBy('customer_booking_id', 'plot_sale_detail_id');
        })
            ->where('payment_status', 'pending')
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
            ->where('payment_status', 'pending')
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

        for ($i = 0; $i < 10; $i++) {
            $month = $startMonth->copy()->addMonths($i);

            $labels[] = $month->format('M');

            $baseQuery = CustomerPayment::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year);

            $monthlyPaidAmount[] = (float) (clone $baseQuery)
                ->where('booking_status', 'booked')
                ->sum('paid_amount');

            $monthlyDueAmount[] = (float) CustomerPayment::whereIn('id', function ($query) use ($month) {
                $query->selectRaw('MAX(id)')
                    ->from('customer_payments')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->whereNotNull('plot_sale_detail_id')
                    ->groupBy('customer_booking_id', 'plot_sale_detail_id');
            })->sum('due_amount');
        }

        return [
            'labels' => $labels,
            'monthlyPaidAmount' => $monthlyPaidAmount,
            'monthlyDueAmount' => $monthlyDueAmount,
        ];
    }
}