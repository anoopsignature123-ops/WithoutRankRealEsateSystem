<?php
namespace App\Http\Controllers;

use App\Models\{Project, PlotDetail, CustomerBooking, CustomerPayment, Associate};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'projectCount'   => Project::count(),
            'totalPlot'      => PlotDetail::count(),
            'totalCustomer'  => CustomerBooking::count(),
            'totalAssociate' => Associate::count(),
            'plotStats'      => $this->getPlotStats(),
            'visitorsData'   => $this->getVisitorsData(),
            'monthlyDues'    => $this->getMonthlyDues(),
            'totalOutstanding' => $this->calculateOutstanding(),
            'totalOverdue'   => $this->calculateOverdue(),
        ];

        return view('dashboard', array_merge($data, $data['plotStats']));
    }

    private function getPlotStats()
    {
        $stats = PlotDetail::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'booked'    => $stats['booked'] ?? 0,
            'hold'      => $stats['hold'] ?? 0,
            'registry'  => $stats['registry'] ?? 0,
            'available' => $stats['available'] ?? 0,
        ];
    }

    private function getMonthlyDues()
    {
        return CustomerPayment::with(['customerBooking', 'plotSaleDetail'])
            ->whereMonth('emi_date', now()->month)
            ->whereYear('emi_date', now()->year)
            ->where('payment_status', 'emi')
            ->get();
    }

    private function calculateOutstanding()
    {
        return CustomerBooking::whereHas('latestPayment', fn($q) => $q->where('payment_status', 'booked'))
            ->withSum(['latestPayment as total' => fn($q) => $q->select('due_amount')], 'due_amount')
            ->get()->sum('total');
    }

    private function calculateOverdue()
    {
        return CustomerBooking::whereHas('latestPayment', function ($q) {
            $q->where('payment_status', 'booked')->where('emi_date', '<', now());
        })
        ->withSum(['latestPayment as total' => fn($q) => $q->select('due_amount')], 'due_amount')
        ->get()->sum('total');
    }

    private function getVisitorsData()
    {
        return [
            'labels'     => ['Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'registered' => [3500, 2500, 5000, 3000, 2800, 3200, 2600, 4534, 2700, 3100],
            'guests'     => [4800, 4200, 7000, 6200, 5800, 6500, 5000, 7675, 6000, 5500],
        ];
    }
}