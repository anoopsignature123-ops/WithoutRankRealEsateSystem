<?php

namespace App\Http\Controllers;

use App\Models\Associate;
use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AssociateTreeController extends Controller
{
    public function index(Request $request)
    {
                $associateId = trim((string) $request->input('associate_id', ''));
        $direction = null;

        $rootAssociate = Associate::query()
            ->when(
                $associateId !== '',
                fn($query) => $query->where('associate_id', $associateId),
                fn($query) => $query->where(function ($subQuery) {
                    $subQuery->whereNull('under_place_id')
                        ->orWhere('under_place_id', '');
                })
            )
            ->orderBy('id')
            ->first();

        if ($rootAssociate) {
            $rootAssociate = $this->buildAssociateTree($rootAssociate);
            $this->attachTreeStatsRecursively($rootAssociate);
        }

        return view('associate-tree.index', compact('rootAssociate', 'direction'));
    }

    private function buildAssociateTree(Associate $rootAssociate): Associate
    {
        $allAssociates = Associate::query()->orderBy('id')->get();
        $treeAssociates = $this->getReachableAssociates($rootAssociate, $allAssociates);

        foreach ($treeAssociates as $associate) {
            $this->initializeTreeNode($associate);
        }

        $this->initializeTreeNode($rootAssociate);

        $associateLookup = collect([$rootAssociate])
            ->merge($treeAssociates)
            ->filter(fn($associate) => $associate instanceof Associate)
            ->keyBy(fn($associate) => trim((string) $associate->associate_id));

        $pendingAssociates = $treeAssociates
            ->reject(fn($associate) => (int) $associate->id === (int) $rootAssociate->id)
            ->sortBy('id')
            ->values();

        $maximumPasses = max(1, $pendingAssociates->count() + 1);

        for ($pass = 0; $pass < $maximumPasses; $pass++) {
            if ($pendingAssociates->isEmpty()) {
                break;
            }

            $placedIds = [];

            foreach ($pendingAssociates as $associate) {
                $parentAssociateId = trim((string) ($associate->under_place_id ?? ''));

                if ($parentAssociateId === '') {
                    continue;
                }

                $parentAssociate = $associateLookup->get($parentAssociateId);

                if (!$parentAssociate instanceof Associate) {
                    continue;
                }

                $direction = $this->normalizeDirection($associate->direction ?? null);

                if (!$direction) {
                    continue;
                }

                $this->placeAssociateInDirection(
                    parent: $parentAssociate,
                    associate: $associate,
                    direction: $direction
                );

                $placedIds[] = (int) $associate->id;
            }

            if (empty($placedIds)) {
                break;
            }

            $pendingAssociates = $pendingAssociates
                ->reject(fn($associate) => in_array((int) $associate->id, $placedIds, true))
                ->values();
        }

        return $rootAssociate;
    }

    private function getReachableAssociates(Associate $rootAssociate, Collection $allAssociates): Collection
    {
        $childrenByParent = $allAssociates
            ->filter(fn($associate) => trim((string) ($associate->under_place_id ?? '')) !== '')
            ->groupBy(fn($associate) => trim((string) $associate->under_place_id));

        $result = collect();
        $queue = collect([trim((string) $rootAssociate->associate_id)]);
        $processedParentIds = [];

        while ($queue->isNotEmpty()) {
            $parentAssociateId = $queue->shift();

            if ($parentAssociateId === '' || in_array($parentAssociateId, $processedParentIds, true)) {
                continue;
            }

            $processedParentIds[] = $parentAssociateId;

            $children = collect($childrenByParent->get($parentAssociateId, collect()))
                ->sortBy('id')
                ->values();

            foreach ($children as $child) {
                if (!$child instanceof Associate) {
                    continue;
                }

                if (!$result->contains('id', $child->id)) {
                    $result->push($child);
                }

                $childAssociateId = trim((string) $child->associate_id);
                if ($childAssociateId !== '') {
                    $queue->push($childAssociateId);
                }
            }
        }

        return $result->sortBy('id')->values();
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
        $currentParent = $parent;
        $visitedIds = [];

        while (true) {
            $currentParentId = (int) $currentParent->id;

            if (in_array($currentParentId, $visitedIds, true)) {
                return;
            }

            $visitedIds[] = $currentParentId;

            $existingChild = $this->getTreeChildren($currentParent, $direction)->first();

            if (!$existingChild instanceof Associate) {
                $this->setTreeChild(parent: $currentParent, direction: $direction, child: $associate);
                return;
            }

            if ((int) $existingChild->id === (int) $associate->id) {
                return;
            }

            $currentParent = $existingChild;
        }
    }

    private function setTreeChild(Associate $parent, string $direction, Associate $child): void
    {
        $treeChildren = $parent->tree_children ?? ['left' => collect(), 'right' => collect()];

        $treeChildren['left'] = collect($treeChildren['left'] ?? []);
        $treeChildren['right'] = collect($treeChildren['right'] ?? []);
        $treeChildren[$direction] = collect([$child]);

        $parent->setAttribute('tree_children', $treeChildren);
    }

    private function getTreeChildren(Associate $associate, string $direction): Collection
    {
        $treeChildren = $associate->tree_children ?? ['left' => collect(), 'right' => collect()];

        return collect($treeChildren[$direction] ?? []);
    }

    private function attachTreeStatsRecursively(Associate $associate, array &$visitedIds = []): void
    {
        if (in_array((int) $associate->id, $visitedIds, true)) {
            return;
        }

        $visitedIds[] = (int) $associate->id;
        $associate->setAttribute('tree_stats', $this->buildStatsFor($associate));

        foreach (['left', 'right'] as $dir) {
            $child = $this->getTreeChildren($associate, $dir)->first();
            if ($child instanceof Associate) {
                $this->attachTreeStatsRecursively($child, $visitedIds);
            }
        }
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
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        $teamStats = $this->businessStatsForAssociateIds($downlineIds);
        $directCount = Associate::query()->where('under_place_id', $associate->associate_id)->count();
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
            'direct_count' => (int) ($associate->direct_count ?? $directCount),
            'downline_count' => (int) ($associate->downline_count ?? $downlineIds->count()),
            'left_team_business' => $leftStats['business'],
            'right_team_business' => $rightStats['business'],

            'left_team_area' => $leftStats['area'],
            'right_team_area' => $rightStats['area'],
        ];
    }

    private function businessStatsForAssociateIds(Collection $associateIds): array
    {
        $associateIds = $associateIds->filter()->map(fn($id) => (int) $id)->unique()->values();

        if ($associateIds->isEmpty()) {
            return $this->emptyBusinessStats();
        }

        $bookingIds = CustomerBooking::query()
            ->whereIn('associate_id', $associateIds)
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        if ($bookingIds->isEmpty()) {
            return $this->emptyBusinessStats();
        }

        $payments = CustomerPayment::query()
            ->with(['plotSaleDetail.plotDetail'])
            ->whereIn('customer_booking_id', $bookingIds)
            ->where('booking_status', 'booked')
            ->whereIn('payment_status', ['paid', 'cleared'])
            ->whereHas('plotSaleDetail', fn($query) => $query->where('status', 'active'))
            ->get();

        if ($payments->isEmpty()) {
            return $this->emptyBusinessStats();
        }

        $business = (float) $payments->sum(fn($payment) => (float) ($payment->paid_amount ?? 0));

        $area = (float) $payments->pluck('plotSaleDetail')
            ->filter()
            ->unique('id')
            ->sum(fn($plotSale) => (float) ($plotSale->plot_area ?? $plotSale->plotDetail?->plot_area ?? 0));

        return ['business' => $business, 'area' => $area];
    }

    private function emptyBusinessStats(): array
    {
        return ['business' => 0.0, 'area' => 0.0];
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