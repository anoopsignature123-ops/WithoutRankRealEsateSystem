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
        $direction = $this->normalizeDirection($request->direction ?? null);
        $allowedIds = collect($user->getDownlineIds())->push($user->id)->unique()->values()->all();
        $rootAssociate = Associate::with(['children.children.children.children'])
            ->whereIn('id', $allowedIds)->where('associate_id', $associateId)->first();
        if ($rootAssociate) {
            $this->attachTreeStats($rootAssociate, $direction);
        }
        return view('associate-panel.team.my_tree', compact('rootAssociate', 'direction'));
    }

    private function attachTreeStats(Associate $associate, ?string $direction = null): void
    {
        $associate->setAttribute('tree_stats', $this->buildTreeStatsFor($associate));
        $children = $associate->children ?? collect();
        $associate->setAttribute('tree_children', $this->groupChildrenByDirection($children, $direction));
        foreach ($associate->tree_children as $groupedChildren) {
            foreach ($groupedChildren as $child) {
                $this->attachTreeStats($child, $direction);
            }
        }
    }

    private function groupChildrenByDirection($children, ?string $direction = null): array
    {
        $children = collect($children);
        $leftChildren = $children->filter(function ($child) {
            return strtolower((string) ($child->direction ?? '')) === 'left';
        })->values();

        $rightChildren = $children->filter(function ($child) {
            return strtolower((string) ($child->direction ?? '')) === 'right';
        })->values();

        if ($direction === 'left') {
            return ['left' => $leftChildren, 'right' => collect()];
        }
        if ($direction === 'right') {
            return ['left' => collect(), 'right' => $rightChildren,];
        }
        return ['left' => $leftChildren, 'right' => $rightChildren];
    }

    private function normalizeDirection(?string $direction): ?string
    {
        $direction = strtolower(trim((string) $direction));
        return in_array($direction, ['left', 'right'], true) ? $direction : null;
    }

    private function buildTreeStatsFor(Associate $associate): array
    {
        $selfStats = $this->businessStatsForAssociateIds(collect([$associate->id]));
        $downlineIds = collect($associate->getDownlineIds())->filter()->unique()->values();
        $teamStats = $this->businessStatsForAssociateIds($downlineIds);
        return [
            'self_business' => $selfStats['business'],
            'team_business' => $teamStats['business'],
            'total_business' => $selfStats['business'] + $teamStats['business'],
            'plot_area' => $selfStats['area'],
            'team_area' => $teamStats['area'],
            'total_area' => $selfStats['area'] + $teamStats['area'],
            'direct_count' => (int) ($associate->direct_count ?? $associate->children?->count() ?? 0),
            'downline_count' => (int) ($associate->downline_count ?? $downlineIds->count()),
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
            ->whereIn('customer_booking_id', $bookingIds)->where('booking_status', 'booked')
            ->whereIn('payment_status', ['paid', 'cleared'])
            ->whereHas('plotSaleDetail', function ($query) {
                $query->where('status', 'active');
            })
            ->get();

        $plotSales = $payments->pluck('plotSaleDetail')->filter()->unique('id');

        return [
            'business' => (float) $payments->sum('paid_amount'),
            'area' => (float) $plotSales->sum(fn($plotSale) => $plotSale->plot_area ?? $plotSale->plotDetail?->plot_area ?? 0),
        ];
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('associate_id')) {
            $query->where('associate_id', 'like', '%' . trim($request->associate_id) . '%');
        }

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        return $query;
    }

    public function myDirect(Request $request)
    {
        $user = Auth::guard('associate')->user();

        $direction = $this->normalizeDirection($request->direction ?? null);

        $pageTitle = match ($direction) {
            'left' => 'My Left Direct Associates',
            'right' => 'My Right Direct Associates',
            default => 'My All Direct Associates',
        };

        $query = Associate::with(['sponsor'])
            ->where('sponsor_id', $user->associate_id);

        $query = $this->applyFilters($query, $request);

        $associates = $query->latest()->get();
        return view('associate-panel.team.my_direct', compact('associates', 'pageTitle', 'direction'));
    }

    public function myDownline(Request $request)
    {
        $user = Auth::guard('associate')->user();
        $direction = $this->normalizeDirection($request->direction ?? null);
        $pageTitle = match ($direction) {
            'left' => 'My Left Team',
            'right' => 'My Right Team',
            default => 'My All Team',
        };
        $query = Associate::with(['sponsor'])
            ->where('under_place_id', $user->associate_id);
        $query = $this->applyFilters($query, $request);
        $associates = $query->latest()->get();
        return view('associate-panel.team.my_downline', compact('associates', 'pageTitle', 'direction'));
    }
}