<?php

namespace App\Http\Controllers;

use App\Models\Associate;
use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use Illuminate\Http\Request;

class AssociateTreeController extends Controller
{
    public function index(Request $request)
    {
        $associateId = trim($request->associate_id ?? '');
        $direction = $this->normalizeDirection($request->direction ?? null);

        $rootAssociate = Associate::with([
            'children.children.children.children.children',
        ])
            ->when($associateId, function ($query) use ($associateId) {
                $query->where('associate_id', $associateId);
            }, function ($query) {
                $query->whereNull('under_place_id');
            })
            ->first();

        if ($rootAssociate) {
            $this->attachTreeStats($rootAssociate, $direction);
        }

        return view('associate-tree.index', compact('rootAssociate', 'direction'));
    }

    private function attachTreeStats(Associate $associate, ?string $direction = null): void
    {
        $associate->setAttribute('tree_stats', $this->buildStatsFor($associate));

        $children = $associate->children ?? collect();

        $associate->setAttribute(
            'tree_children',
            $this->groupChildrenByDirection($children, $direction)
        );

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
            return [
                'left' => $leftChildren,
                'right' => collect(),
            ];
        }

        if ($direction === 'right') {
            return [
                'left' => collect(),
                'right' => $rightChildren,
            ];
        }

        return [
            'left' => $leftChildren,
            'right' => $rightChildren,
        ];
    }

    private function normalizeDirection(?string $direction): ?string
    {
        $direction = strtolower(trim((string) $direction));

        return in_array($direction, ['left', 'right'], true) ? $direction : null;
    }

    private function buildStatsFor(Associate $associate): array
    {
        $selfStats = $this->businessStatsForAssociateIds(collect([$associate->id]));

        $downlineIds = collect($associate->getDownlineIds())
            ->filter()
            ->unique()
            ->values();

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
            'area' => (float) $plotSales->sum(function ($plotSale) {
                return $plotSale->plot_area ?? $plotSale->plotDetail?->plot_area ?? 0;
            }),
        ];
    }
}