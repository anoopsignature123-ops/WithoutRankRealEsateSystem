<?php

namespace App\Services;

use App\Models\Associate;
use App\Models\CustomerPayment;
use App\Models\DesignationRank;
use App\Models\PromotionHistory;
use Carbon\Carbon;
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
                    'message' => 'This associate does not have a current rank.',
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
                        ? 'Remaining target: Rs. ' . number_format($preview['remaining_target'], 2)
                        : 'No higher rank is available.',
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
            'remaining_target' => $nextRank ? max(0, $targetFrom - $totalBusiness) : 0,
            'next_target_from' => $targetFrom,
            'next_target_to' => $targetTo,
            'progress_percent' => $progressPercent,
            'can_promote' => ! is_null($eligibleRank),
        ];
    }

    public function getSelfBusiness(int $associateId, Carbon|string|null $asOfDate = null): float
    {
        return $this->paidBookedBusinessForAssociateIds([$associateId], $asOfDate);
    }

    public function getTeamBusiness(Associate $associate, Carbon|string|null $asOfDate = null): float
    {
        $teamIds = collect(
            method_exists($associate, 'getDownlineIds')
                ? $associate->getDownlineIds()
                : []
        )
            ->filter()
            ->map(fn($id) => (int) $id)
            ->reject(fn($id) => $id === (int) $associate->id)
            ->unique()
            ->values();

        if ($teamIds->isEmpty()) {
            return 0;
        }

        return $this->paidBookedBusinessForAssociateIds($teamIds->all(), $asOfDate);
    }

    public function getBusinessSnapshot(Associate $associate, Carbon|string|null $asOfDate = null): array
    {
        $selfBusiness = $this->getSelfBusiness($associate->id, $asOfDate);
        $teamBusiness = $this->getTeamBusiness($associate, $asOfDate);

        return [
            'self_business' => $selfBusiness,
            'team_business' => $teamBusiness,
            'total_business' => $selfBusiness + $teamBusiness,
        ];
    }

    private function paidBookedBusinessForAssociateIds(array $associateIds, Carbon|string|null $asOfDate = null): float
    {
        $associateIds = collect($associateIds)
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($associateIds)) {
            return 0;
        }

        $query = CustomerPayment::query()
            ->where('booking_status', 'booked')
            ->whereIn('payment_status', ['paid', 'cleared'])
            ->where('paid_amount', '>', 0)
            ->whereHas('customerBooking', function ($q) use ($associateIds) {
                $q->whereIn('associate_id', $associateIds);
            })
            ->whereHas('plotSaleDetail', function ($q) {
                $q->where('status', 'active');
            });

        if ($asOfDate) {
            $asOfDate = Carbon::parse($asOfDate)->endOfDay();

            $query->where(function ($dateQuery) use ($asOfDate) {
                $dateQuery->where(function ($clearedQuery) use ($asOfDate) {
                    $clearedQuery->whereNotNull('cheque_clearance_date')
                        ->where('cheque_clearance_date', '<=', $asOfDate);
                })->orWhere(function ($paidQuery) use ($asOfDate) {
                    $paidQuery->whereNull('cheque_clearance_date')
                        ->where('created_at', '<=', $asOfDate);
                });
            });
        }

        return (float) $query->sum('paid_amount');
    }

    private function rankOrder($rank): int
    {
        $priority = (int) ($rank->priority ?? 0);

        return $priority > 0
            ? $priority
            : (int) ($rank->rank_number ?? 0);
    }
}