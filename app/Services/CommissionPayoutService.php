<?php

namespace App\Services;

use App\Models\Associate;
use App\Models\CommissionGeneration;
use App\Models\CommissionPayout;
use App\Models\CustomerPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommissionPayoutService
{
    public function getLastGeneratedToDate(): ?string
    {
        $lastGeneration = CommissionGeneration::latest('to_date')->first();

        return $lastGeneration?->to_date;
    }

    public function getNextGlobalFromDate(): string
    {
        $lastGeneratedDate = $this->getLastGeneratedToDate();

        if ($lastGeneratedDate) {
            return Carbon::parse($lastGeneratedDate)->addDay()->format('Y-m-d');
        }

        return now()->startOfMonth()->format('Y-m-d');
    }

    public function previewAllCommission(string $fromDate, string $toDate): array
    {
        $associates = Associate::with('rank')
            ->whereNotNull('rank_id')
            ->get();

        $rows = [];

        $summary = [
            'self_business' => 0,
            'team_business' => 0,
            'self_commission' => 0,
            'team_commission' => 0,
            'total_commission' => 0,
            'total_records' => 0,
        ];

        foreach ($associates as $associate) {
            $calculation = $this->calculateCommission(
                associate: $associate,
                fromDate: $fromDate,
                toDate: $toDate,
                skipExisting: false
            );

            if ($calculation['total_commission'] <= 0) {
                continue;
            }

            $rows[] = [
                'associate' => $associate,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'calculation' => $calculation,
            ];

            $summary['self_business'] += $calculation['self_business'];
            $summary['team_business'] += $calculation['team_business'];
            $summary['self_commission'] += $calculation['self_commission'];
            $summary['team_commission'] += $calculation['team_commission'];
            $summary['total_commission'] += $calculation['total_commission'];
            $summary['total_records'] += count($calculation['rows']);
        }

        return [
            'rows' => $rows,
            'summary' => array_map(fn ($value) => round($value, 2), $summary),
        ];
    }

    public function generateAllCommission(string $fromDate, string $toDate): array
    {
        return DB::transaction(function () use ($fromDate, $toDate) {
            if (Carbon::parse($fromDate)->gt(Carbon::parse($toDate))) {
                throw new \Exception('Invalid date range.');
            }

            $preview = $this->previewAllCommission($fromDate, $toDate);

            $generatedCount = 0;
            $payoutCount = 0;

            foreach ($preview['rows'] as $item) {
                $associate = $item['associate'];
                $calculation = $item['calculation'];

                $generation = CommissionGeneration::create([
                    'associate_id' => $associate->id,
                    'from_date' => $fromDate,
                    'to_date' => $toDate,
                    'self_business' => $calculation['self_business'],
                    'team_business' => $calculation['team_business'],
                    'self_commission' => $calculation['self_commission'],
                    'team_commission' => $calculation['team_commission'],
                    'total_commission' => $calculation['total_commission'],
                    'status' => 'generated',
                ]);

                foreach ($calculation['rows'] as $row) {
                    $alreadyGenerated = CommissionPayout::where('associate_id', $row['associate_id'])
                        ->where('customer_payment_id', $row['customer_payment_id'])
                        ->where('commission_type', $row['commission_type'])
                        ->exists();

                    if ($alreadyGenerated) {
                        continue;
                    }

                    $row['commission_generation_id'] = $generation->id;

                    CommissionPayout::create($row);

                    $payoutCount++;
                }

                if ($payoutCount > 0) {
                    $generatedCount++;
                }
            }

            return [
                'message' => $generatedCount . ' associates commission generated successfully. Total records: ' . $payoutCount,
            ];
        });
    }

    private function calculateCommission(
        Associate $associate,
        string $fromDate,
        string $toDate,
        bool $skipExisting
    ): array {
        $selfBusiness = 0;
        $teamBusiness = 0;
        $selfCommission = 0;
        $teamCommission = 0;
        $rows = [];

        $associateRankPercent = (float) ($associate->rank?->commission ?? 0);

        if ($associateRankPercent <= 0) {
            return $this->emptyCalculation();
        }

        $payments = CustomerPayment::with([
            'customerBooking.associate.rank',
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->whereBetween('created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ])
            ->where('booking_status', 'booked')
            ->whereNotNull('customer_booking_id')
            ->whereNotNull('plot_sale_detail_id')
            ->where(function ($query) {
                $query->whereIn('payment_status', ['paid', 'cleared'])
                    ->orWhere('transaction_category', 'booking_fee');
            })
            ->latest('id')
            ->get();

        foreach ($payments as $payment) {
            if ($skipExisting) {
                $alreadyGenerated = CommissionPayout::where('associate_id', $associate->id)
                    ->where('customer_payment_id', $payment->id)
                    ->exists();

                if ($alreadyGenerated) {
                    continue;
                }
            }

            $sourceAssociate = $payment->customerBooking?->associate;

            if (! $sourceAssociate) {
                continue;
            }

            $sourceAssociate->loadMissing('rank');

            $paymentAmount = (float) ($payment->paid_amount ?: $payment->booking_amount ?: 0);

            if ($paymentAmount <= 0) {
                continue;
            }

            $sourceRankPercent = (float) ($sourceAssociate->rank?->commission ?? 0);

            if ((int) $sourceAssociate->id === (int) $associate->id) {
                $commissionPercent = $associateRankPercent;
                $commissionAmount = ($paymentAmount * $commissionPercent) / 100;

                $selfBusiness += $paymentAmount;
                $selfCommission += $commissionAmount;

                $rows[] = $this->makeCommissionRow(
                    associate: $associate,
                    sourceAssociate: $sourceAssociate,
                    payment: $payment,
                    commissionType: 'self',
                    paymentAmount: $paymentAmount,
                    commissionPercent: $commissionPercent,
                    commissionAmount: $commissionAmount
                );

                continue;
            }

            if (! $this->isSeniorOf($associate, $sourceAssociate)) {
                continue;
            }

            $commissionPercent = $associateRankPercent - $sourceRankPercent;

            if ($commissionPercent <= 0) {
                continue;
            }

            $commissionAmount = ($paymentAmount * $commissionPercent) / 100;

            $teamBusiness += $paymentAmount;
            $teamCommission += $commissionAmount;

            $rows[] = $this->makeCommissionRow(
                associate: $associate,
                sourceAssociate: $sourceAssociate,
                payment: $payment,
                commissionType: 'team',
                paymentAmount: $paymentAmount,
                commissionPercent: $commissionPercent,
                commissionAmount: $commissionAmount
            );
        }

        return [
            'self_business' => round($selfBusiness, 2),
            'team_business' => round($teamBusiness, 2),
            'self_commission' => round($selfCommission, 2),
            'team_commission' => round($teamCommission, 2),
            'total_commission' => round($selfCommission + $teamCommission, 2),
            'rows' => $rows,
        ];
    }

    private function emptyCalculation(): array
    {
        return [
            'self_business' => 0,
            'team_business' => 0,
            'self_commission' => 0,
            'team_commission' => 0,
            'total_commission' => 0,
            'rows' => [],
        ];
    }

    private function isSeniorOf(Associate $senior, Associate $junior): bool
    {
        $currentUplineId = $junior->under_place_id ?: $junior->sponsor_id;

        while ($currentUplineId) {
            $upline = Associate::where('associate_id', $currentUplineId)->first();

            if (! $upline) {
                return false;
            }

            if ((int) $upline->id === (int) $senior->id) {
                return true;
            }

            $currentUplineId = $upline->under_place_id ?: $upline->sponsor_id;
        }

        return false;
    }

    private function makeCommissionRow(
        Associate $associate,
        Associate $sourceAssociate,
        CustomerPayment $payment,
        string $commissionType,
        float $paymentAmount,
        float $commissionPercent,
        float $commissionAmount
    ): array {
        return [
            'associate_id' => $associate->id,
            'source_associate_id' => $sourceAssociate->id,
            'customer_booking_id' => $payment->customer_booking_id,
            'plot_sale_detail_id' => $payment->plot_sale_detail_id,
            'customer_payment_id' => $payment->id,
            'commission_type' => $commissionType,
            'payment_amount' => round($paymentAmount, 2),
            'associate_rank_id' => $associate->rank_id ?? null,
            'source_rank_id' => $sourceAssociate->rank_id ?? null,
            'commission_percent' => round($commissionPercent, 2),
            'commission_amount' => round($commissionAmount, 2),
            'status' => 'pending',
            'generated_date' => now()->toDateString(),
        ];
    }

    public function getCommissionList($request)
    {
        $query = CommissionPayout::with([
            'associate',
            'sourceAssociate',
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
            'payment',
            'generation',
        ])->latest();

        if ($request->associate_id) {
            $query->whereHas('associate', function ($q) use ($request) {
                $q->where('associate_id', 'like', '%' . $request->associate_id . '%')
                    ->orWhere('associate_name', 'like', '%' . $request->associate_id . '%');
            });
        }

        if ($request->commission_type) {
            $query->where('commission_type', $request->commission_type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->from_date && $request->to_date) {
            $query->whereBetween('generated_date', [
                Carbon::parse($request->from_date)->startOfDay(),
                Carbon::parse($request->to_date)->endOfDay(),
            ]);
        } elseif ($request->from_date) {
            $query->whereDate('generated_date', '>=', $request->from_date);
        } elseif ($request->to_date) {
            $query->whereDate('generated_date', '<=', $request->to_date);
        }

        return $query->get();
    }
}