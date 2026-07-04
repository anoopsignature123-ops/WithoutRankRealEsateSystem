<?php

namespace App\Services;

use App\Models\Associate;
use App\Models\CustomerBooking;
use App\Models\DesignationRank;
use App\Models\PromotionHistory;
use Illuminate\Support\Facades\DB;

class AssociatePromotionService
{
    public function checkPromotion(int $associateId): bool
    {
        return $this->checkPromotionResult($associateId)['promoted'];
    }

    public function checkPromotionResult(int $associateId): array
    {
        return DB::transaction(function () use ($associateId) {

            $associate = Associate::with('rank')
                ->lockForUpdate()
                ->findOrFail($associateId);

            $currentRank = $associate->rank;

            if (! $currentRank) {
                return [
                    'promoted' => false,
                    'type' => 'warning',
                    'title' => 'Rank Not Assigned',
                    'message' => 'This associate does not have a current rank. Please assign a rank first.',
                    'preview' => $this->getPromotionPreview($associate->id),
                ];
            }

            $selfBusiness = $this->getSelfBusiness($associate->id);
            $teamBusiness = $this->getTeamBusiness($associate);
            $totalBusiness = $selfBusiness + $teamBusiness;

            $currentOrder = $this->rankOrder($currentRank);

            $eligibleRank = DesignationRank::query()
                ->whereRaw('CAST(COALESCE(NULLIF(priority, 0), rank_number) AS UNSIGNED) > ?', [$currentOrder])
                ->where('target_from', '<=', $totalBusiness)
                ->orderByRaw('CAST(COALESCE(NULLIF(priority, 0), rank_number) AS UNSIGNED) DESC')
                ->first();

            if (! $eligibleRank) {
                $preview = $this->getPromotionPreview($associate->id);

                return [
                    'promoted' => false,
                    'type' => $preview['next_rank'] ? 'info' : 'warning',
                    'title' => $preview['next_rank'] ? 'Promotion Target Pending' : 'Highest Rank Reached',
                    'message' => $preview['next_rank']
                        ? 'More business is required for the next rank. Remaining target: Rs. '.number_format($preview['remaining_target'], 2).'.'
                        : 'No higher rank is available after the current rank.',
                    'preview' => $preview,
                ];
            }

            $alreadyExists = PromotionHistory::where('associate_id', $associate->id)
                ->where('new_rank_id', $eligibleRank->id)
                ->exists();

            if (! $alreadyExists) {
                PromotionHistory::create([
                    'associate_id' => $associate->id,
                    'old_rank_id' => $currentRank->id,
                    'new_rank_id' => $eligibleRank->id,
                    'self_business' => $selfBusiness,
                    'team_business' => $teamBusiness,
                    'total_business' => $totalBusiness,
                    'promotion_date' => now()->toDateString(),
                    'remarks' => 'Auto promoted from '.$currentRank->designation.' to '.$eligibleRank->designation,
                ]);
            }

            $associate->update([
                'rank_id' => $eligibleRank->id,
            ]);

            return [
                'promoted' => true,
                'type' => 'success',
                'title' => 'Associate Promoted',
                'message' => $associate->associate_name.' promoted from '.$currentRank->designation.' to '.$eligibleRank->designation.'.',
                'preview' => $this->getPromotionPreview($associate->id),
            ];
        });
    }

    public function getPromotionPreview(int $associateId): array
    {
        $associate = Associate::with('rank')->findOrFail($associateId);

        $currentRank = $associate->rank;

        $selfBusiness = $this->getSelfBusiness($associate->id);
        $teamBusiness = $this->getTeamBusiness($associate);
        $totalBusiness = $selfBusiness + $teamBusiness;

        $currentOrder = $currentRank ? $this->rankOrder($currentRank) : 0;

        $nextRank = DesignationRank::query()
            ->whereRaw('CAST(COALESCE(NULLIF(priority, 0), rank_number) AS UNSIGNED) > ?', [$currentOrder])
            ->orderByRaw('CAST(COALESCE(NULLIF(priority, 0), rank_number) AS UNSIGNED) ASC')
            ->first();

        $eligibleRank = DesignationRank::query()
            ->whereRaw('CAST(COALESCE(NULLIF(priority, 0), rank_number) AS UNSIGNED) > ?', [$currentOrder])
            ->where('target_from', '<=', $totalBusiness)
            ->orderByRaw('CAST(COALESCE(NULLIF(priority, 0), rank_number) AS UNSIGNED) DESC')
            ->first();

        $targetFrom = (float) ($nextRank?->target_from ?? 0);
        $targetTo = (float) ($nextRank?->target_to ?? 0);
        $progressPercent = $nextRank && $targetFrom > 0
            ? min(100, round(($totalBusiness / $targetFrom) * 100, 2))
            : ($nextRank ? 0 : 100);

        return [
            'associate' => $associate,
            'current_rank' => $currentRank,
            'next_rank' => $nextRank,
            'eligible_rank' => $eligibleRank,
            'self_business' => $selfBusiness,
            'team_business' => $teamBusiness,
            'total_business' => $totalBusiness,
            'remaining_target' => $nextRank
                ? max(0, (float) $nextRank->target_from - $totalBusiness)
                : 0,
            'next_target_from' => $targetFrom,
            'next_target_to' => $targetTo,
            'progress_percent' => $progressPercent,
            'can_promote' => ! is_null($eligibleRank),
        ];
    }

    public function getSelfBusiness(int $associateId): float
    {
        return (float) CustomerBooking::where('associate_id', $associateId)
            ->whereHas('plotSaleDetails', function ($q) {
                $q->where('status', 'active')
                    ->whereHas('payments', function ($p) {
                        $p->where('booking_status', 'booked')
                            ->whereIn('payment_status', ['paid', 'cleared']);
                    });
            })
            ->with(['plotSaleDetails.payments'])
            ->get()
            ->sum(function ($booking) {
                return $booking->plotSaleDetails
                    ->filter(function ($plotSale) {
                        return $plotSale->status === 'active'
                            && $plotSale->payments->contains(function ($payment) {
                                return $payment->booking_status === 'booked'
                                    && in_array($payment->payment_status, ['paid', 'cleared']);
                            });
                    })
                    ->sum(function ($plotSale) {
                        return (float) ($plotSale->total_plot_cost ?? 0);
                    });
            });
    }

    public function getTeamBusiness(Associate $associate): float
    {
        $teamIds = collect(
            method_exists($associate, 'getDownlineIds')
                ? $associate->getDownlineIds()
                : []
        )
            ->filter()
            ->unique()
            ->values();

        if ($teamIds->isEmpty()) {
            return 0;
        }

        return (float) CustomerBooking::whereIn('associate_id', $teamIds)
            ->whereHas('plotSaleDetails', function ($q) {
                $q->where('status', 'active')
                    ->whereHas('payments', function ($p) {
                        $p->where('booking_status', 'booked')
                            ->whereIn('payment_status', ['paid', 'cleared']);
                    });
            })
            ->with(['plotSaleDetails.payments'])
            ->get()
            ->sum(function ($booking) {
                return $booking->plotSaleDetails
                    ->filter(function ($plotSale) {
                        return $plotSale->status === 'active'
                            && $plotSale->payments->contains(function ($payment) {
                                return $payment->booking_status === 'booked'
                                    && in_array($payment->payment_status, ['paid', 'cleared']);
                            });
                    })
                    ->sum(function ($plotSale) {
                        return (float) ($plotSale->total_plot_cost ?? 0);
                    });
            });
    }

    private function rankOrder($rank): int
    {
        $priority = (int) ($rank->priority ?? 0);

        if ($priority > 0) {
            return $priority;
        }

        return (int) ($rank->rank_number ?? 0);
    }
}
