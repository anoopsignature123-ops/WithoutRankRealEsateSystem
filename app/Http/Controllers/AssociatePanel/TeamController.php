<?php

namespace App\Http\Controllers\AssociatePanel;

use App\Http\Controllers\Controller;
use App\Models\Associate;
use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function myTree(Request $request)
    {
        $user = Auth::guard('associate')->user();
        $associateId = trim($request->associate_id ?? $user->associate_id);
        $allowedIds = collect($user->getDownlineIds())
            ->push($user->id)
            ->unique()
            ->values()
            ->all();

        $rootAssociate = Associate::with([
            'rank',
            'children.rank',
            'children.children.rank',
        ])
            ->whereIn('id', $allowedIds)
            ->where('associate_id', $associateId)
            ->first();

        if ($rootAssociate) {
            $this->attachTreeStats($rootAssociate);
        }

        return view('associate-panel.team.my_tree', compact('rootAssociate'));
    }

    private function attachTreeStats(Associate $associate): void
    {
        $associate->setAttribute('tree_stats', $this->buildTreeStatsFor($associate));

        $associate->children->each(function (Associate $child) {
            $this->attachTreeStats($child);
        });
    }

    private function buildTreeStatsFor(Associate $associate): array
    {
        $selfStats = $this->businessStatsForAssociateIds(collect([$associate->id]));
        $downlineIds = collect($associate->getDownlineIds())->filter()->values();
        $teamStats = $this->businessStatsForAssociateIds($downlineIds);

        return [
            'self_business' => $selfStats['business'],
            'team_business' => $teamStats['business'],
            'total_business' => $selfStats['business'] + $teamStats['business'],
            'plot_area' => $selfStats['area'],
            'team_area' => $teamStats['area'],
            'total_area' => $selfStats['area'] + $teamStats['area'],
            'direct_count' => (int) ($associate->direct_count ?? 0),
            'downline_count' => (int) ($associate->downline_count ?? 0),
        ];
    }

    private function businessStatsForAssociateIds($associateIds): array
    {
        $associateIds = collect($associateIds)->filter()->unique()->values();

        if ($associateIds->isEmpty()) {
            return ['business' => 0, 'area' => 0];
        }

        $bookingIds = CustomerBooking::whereIn('associate_id', $associateIds)->pluck('id');

        if ($bookingIds->isEmpty()) {
            return ['business' => 0, 'area' => 0];
        }

        $payments = CustomerPayment::with('plotSaleDetail.plotDetail')
            ->whereIn('customer_booking_id', $bookingIds)
            ->where('booking_status', 'booked')
            ->whereIn('payment_status', ['paid', 'cleared'])
            ->whereHas('plotSaleDetail', function ($query) {
                $query->where('status', 'active');
            })
            ->get();

        $plotSales = $payments->pluck('plotSaleDetail')->filter()->unique('id');

        return [
            'business' => (float) $payments->sum('paid_amount'),
            'area' => (float) $plotSales->sum(fn ($plotSale) => $plotSale->plot_area ?? $plotSale->plotDetail?->plot_area ?? 0),
        ];
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('associate_id')) {
            $query->where('associate_id', 'like', '%'.trim($request->associate_id).'%');
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        return $query;
    }

    // My Direct
    public function myDirect(Request $request)
    {
        $user = Auth::guard('associate')->user();

        $query = Associate::with(['sponsor', 'rank'])
            ->where('sponsor_id', $user->associate_id);

        $query = $this->applyFilters($query, $request);

        $associates = $query->latest()->get();

        return view('associate-panel.team.my_direct', compact('associates'));
    }

    // My Downline
    public function myDownline(Request $request)
    {
        $user = Auth::guard('associate')->user();

        $query = Associate::with(['sponsor', 'rank'])
            ->where('under_place_id', $user->associate_id);

        $query = $this->applyFilters($query, $request);

        $associates = $query->latest()->get();

        return view('associate-panel.team.my_downline', compact('associates'));
    }
}
