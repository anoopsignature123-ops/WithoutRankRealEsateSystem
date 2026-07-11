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
        $allowedIds = collect($this->getPlacementDownlineIds($user))->push($user->id)->unique()->values()->all();
        $rootAssociate = Associate::whereIn('id', $allowedIds)->where('associate_id', $associateId)->first();
        if ($rootAssociate) {
            $rootAssociate = $this->buildPlacementTree($rootAssociate, $direction);
            $this->attachTreeStats($rootAssociate);
        }
        return view('associate-panel.team.my_tree', compact('rootAssociate', 'direction'));
    }

    private function attachTreeStats(Associate $associate, ?string $direction = null): void
    {
        $associate->setAttribute('tree_stats', $this->buildTreeStatsFor($associate));
        foreach ($associate->tree_children as $groupedChildren) {
            foreach ($groupedChildren as $child) {
                $this->attachTreeStats($child, $direction);
            }
        }
    }

    private function buildPlacementTree(Associate $rootAssociate, ?string $selectedDirection = null): Associate
    {
        $allAssociates = Associate::orderBy('id')->get();
        $treeAssociates = $this->getReachableAssociates($rootAssociate, $allAssociates);

        $this->initializeTreeNode($rootAssociate);
        foreach ($treeAssociates as $associate) {
            $this->initializeTreeNode($associate);
        }

        $lookup = collect([$rootAssociate])->merge($treeAssociates)->keyBy(fn($associate) => trim((string) $associate->associate_id));

        foreach ($treeAssociates->reject(fn($associate) => (int) $associate->id === (int) $rootAssociate->id)->sortBy('id') as $associate) {
            $parentCode = trim((string) ($associate->under_place_id ?? ''));
            $parent = $lookup->get($parentCode);
            $direction = $this->normalizeDirection($associate->direction ?? null);

            if ($parent && $direction) {
                $this->placeAssociateInDirection($parent, $associate, $direction);
            }
        }

        if ($selectedDirection === 'left') {
            $rootAssociate->setAttribute('tree_children', [
                'left' => $this->getTreeChildren($rootAssociate, 'left'),
                'right' => collect(),
            ]);
        }

        if ($selectedDirection === 'right') {
            $rootAssociate->setAttribute('tree_children', [
                'left' => collect(),
                'right' => $this->getTreeChildren($rootAssociate, 'right'),
            ]);
        }

        return $rootAssociate;
    }

    private function getReachableAssociates(Associate $rootAssociate, $allAssociates)
    {
        $childrenByParent = $allAssociates
            ->filter(fn($associate) => trim((string) ($associate->under_place_id ?? '')) !== '')
            ->groupBy(fn($associate) => trim((string) $associate->under_place_id));

        $result = collect();
        $queue = collect([trim((string) $rootAssociate->associate_id)]);
        $processed = [];

        while ($queue->isNotEmpty()) {
            $parentCode = $queue->shift();
            if ($parentCode === '' || in_array($parentCode, $processed, true)) {
                continue;
            }

            $processed[] = $parentCode;
            foreach (collect($childrenByParent->get($parentCode, collect()))->sortBy('id') as $child) {
                if (!$result->contains('id', $child->id)) {
                    $result->push($child);
                }

                $queue->push(trim((string) $child->associate_id));
            }
        }

        return $result->values();
    }

    private function initializeTreeNode(Associate $associate): void
    {
        $associate->setAttribute('tree_children', [
            'left' => collect(),
            'right' => collect(),
        ]);
    }

    private function placeAssociateInDirection(Associate $parent, Associate $associate, string $direction): void
    {
        $current = $parent;
        $visited = [];

        while (true) {
            if (in_array((int) $current->id, $visited, true)) {
                return;
            }

            $visited[] = (int) $current->id;
            $existing = $this->getTreeChildren($current, $direction)->first();

            if (!$existing instanceof Associate) {
                $children = $current->tree_children ?? ['left' => collect(), 'right' => collect()];
                $children[$direction] = collect([$associate]);
                $current->setAttribute('tree_children', $children);
                return;
            }

            if ((int) $existing->id === (int) $associate->id) {
                return;
            }

            $current = $existing;
        }
    }

    private function getTreeChildren(Associate $associate, string $direction)
    {
        $treeChildren = $associate->tree_children ?? ['left' => collect(), 'right' => collect()];
        return collect($treeChildren[$direction] ?? []);
    }

    private function getPlacementDownlineIds(Associate $associate): array
    {
        $ids = [];
        $children = Associate::where('under_place_id', $associate->associate_id)->pluck('id')->toArray();

        foreach ($children as $childId) {
            $ids[] = $childId;
            $child = Associate::find($childId);
            if ($child) {
                $ids = array_merge($ids, $this->getPlacementDownlineIds($child));
            }
        }

        return array_unique($ids);
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
        $downlineIds = collect($this->getPlacementDownlineIds($associate))->filter()->unique()->values();
        $teamStats = $this->businessStatsForAssociateIds($downlineIds);

        $leftStats = $this->calculateBranchBusiness(
            $this->getTreeChildren($associate, 'left')->first()
        );

        $rightStats = $this->calculateBranchBusiness(
            $this->getTreeChildren($associate, 'right')->first()
        );
        return [
            'self_business' => $selfStats['business'],
            'team_business' => $teamStats['business'],
            'total_business' => $selfStats['business'] + $teamStats['business'],
            'plot_area' => $selfStats['area'],
            'team_area' => $teamStats['area'],
            'total_area' => $selfStats['area'] + $teamStats['area'],
            'direct_count' => (int) Associate::where('under_place_id', $associate->associate_id)->count(),
            'downline_count' => (int) ($associate->downline_count ?? $downlineIds->count()),

            'left_team_business' => $leftStats['business'],
            'right_team_business' => $rightStats['business'],

            'left_team_area' => $leftStats['area'],
            'right_team_area' => $rightStats['area'],
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

        $query = Associate::with(['sponsor', 'stateName', 'cityName'])
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
        $query = Associate::with(['sponsor', 'stateName', 'cityName'])
            ->where('under_place_id', $user->associate_id);
        $query = $this->applyFilters($query, $request);
        $associates = $query->latest()->get();
        return view('associate-panel.team.my_downline', compact('associates', 'pageTitle', 'direction'));
    }

    private function calculateBranchBusiness(?Associate $associate): array
    {
        if (!$associate) {
            return [
                'business' => 0,
                'area' => 0,
            ];
        }

        $associateIds = collect([$associate->id])
            ->merge($associate->getDownlineIds())
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        return $this->businessStatsForAssociateIds($associateIds);
    }
}
