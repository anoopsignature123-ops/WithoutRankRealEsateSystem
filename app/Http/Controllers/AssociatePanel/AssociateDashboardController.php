<?php

namespace App\Http\Controllers\AssociatePanel;

use App\Http\Controllers\Controller;
use App\Services\Associate\AssociateDashboardService;

class AssociateDashboardController extends Controller
{
    public function __construct(private AssociateDashboardService $service) {}

    public function dashboard()
    {
        $associate = auth()->user();
        $data = $this->service->getDashboardStats($associate->id);
        $monthlyData = $this->service->getMonthlyBusinessData($associate->id);
        $stats = $this->service->getBusinessStats($associate->id);
         $payoutStats = $this->service->getPayoutStats($associate->id);
        return view('associate_dashboard', compact('associate', 'data', 'monthlyData', 'stats', 'payoutStats'));
    }
}