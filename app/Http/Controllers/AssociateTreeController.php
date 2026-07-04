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

        if ($associateId) {
            $rootAssociate = Associate::with([
                'rank',
                'children.rank',
                'children.children.rank',
            ])
                ->where('associate_id', $associateId)
                ->first();
        } else {
            $rootAssociate = Associate::with([
                'rank',
                'children.rank',
                'children.children.rank',
            ])
                ->whereNull('under_place_id')
                ->first();
        }

        if ($rootAssociate) {
            $this->attachTreeStats($rootAssociate);
        }

        return view(
            'associate-tree.index',
            compact('rootAssociate')
        );
    }

    private function attachTreeStats(Associate $associate): void
    {
        $associate->setAttribute('tree_stats', $this->buildStatsFor($associate));

        $associate->children->each(function (Associate $child) {
            $this->attachTreeStats($child);
        });
    }

    private function buildStatsFor(Associate $associate): array
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
            return [
                'business' => 0,
                'area' => 0,
            ];
        }

        $bookingIds = CustomerBooking::whereIn('associate_id', $associateIds)->pluck('id');

        if ($bookingIds->isEmpty()) {
            return [
                'business' => 0,
                'area' => 0,
            ];
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
}
