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

        $firstPaymentDate = $this->getFirstEligiblePaymentDate();

        return $firstPaymentDate
            ? Carbon::parse($firstPaymentDate)->format('Y-m-d')
            : now()->startOfMonth()->format('Y-m-d');
    }

    public function resolveCommissionPeriod(string $month): array
    {
        $fromDate = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();
        $today = now()->startOfDay();

        if ($fromDate->gt($today->copy()->startOfMonth())) {
            throw new \InvalidArgumentException('Future month is not allowed.');
        }

        $toDate = $fromDate->copy()->endOfMonth();

        if ($toDate->gt($today)) {
            $toDate = $today;
        }

        return [
            'value' => $fromDate->format('Y-m'),
            'label' => $fromDate->format('F Y'),
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'range_label' => $fromDate->format('d M Y') . ' to ' . $toDate->format('d M Y'),
            'is_current' => $fromDate->isSameMonth($today),
        ];
    }

    public function getCommissionPeriodOptions()
    {
        $firstPaymentDate = $this->getFirstEligiblePaymentDate();
        $startMonth = $firstPaymentDate
            ? Carbon::parse($firstPaymentDate)->startOfMonth()
            : now()->startOfMonth();
        $currentMonth = now()->startOfMonth();
        $generatedPeriods = $this->getGeneratedPeriodOptions()->keyBy('range_key');
        $periods = collect();

        while ($startMonth->lte($currentMonth)) {
            $period = $this->resolveCommissionPeriod($startMonth->format('Y-m'));
            $rangeKey = $period['value'];
            $generatedPeriod = $generatedPeriods->get($rangeKey);

            $periods->push(array_merge($period, [
                'range_key' => $rangeKey,
                'is_generated' => (bool) $generatedPeriod,
                'generation_count' => (int) ($generatedPeriod['generation_count'] ?? 0),
                'generated_commission' => (float) ($generatedPeriod['total_commission'] ?? 0),
            ]));

            $startMonth->addMonth();
        }

        return $periods;
    }

    public function getGeneratedPeriodOptions()
    {
        return CommissionGeneration::selectRaw("
                DATE_FORMAT(from_date, '%Y-%m') as period_month,
                MIN(from_date) as from_date,
                MAX(to_date) as to_date,
                COUNT(*) as generation_count,
                SUM(total_commission) as total_commission
            ")
            ->groupBy('period_month')
            ->orderByDesc('period_month')
            ->get()
            ->map(function ($period) {
                $fromDate = Carbon::parse($period->from_date);
                $toDate = Carbon::parse($period->to_date);

                return [
                    'value' => $period->period_month,
                    'label' => $fromDate->format('F Y'),
                    'from_date' => $fromDate->toDateString(),
                    'to_date' => $toDate->toDateString(),
                    'range_key' => $period->period_month,
                    'range_label' => $fromDate->format('d M Y') . ' to ' . $toDate->format('d M Y'),
                    'generation_count' => (int) $period->generation_count,
                    'total_commission' => (float) $period->total_commission,
                ];
            });
    }

    public function isPeriodGenerated(string $fromDate, string $toDate): bool
    {
        return CommissionGeneration::where(function ($query) use ($fromDate, $toDate) {
            $query->whereBetween('from_date', [$fromDate, $toDate])
                ->orWhereBetween('to_date', [$fromDate, $toDate])
                ->orWhere(function ($q) use ($fromDate, $toDate) {
                    $q->whereDate('from_date', '<=', $fromDate)
                        ->whereDate('to_date', '>=', $toDate);
                });
        })->exists();
    }

    private function getFirstEligiblePaymentDate(): ?string
    {
        return CustomerPayment::where('booking_status', 'booked')
            ->whereNotNull('customer_booking_id')
            ->whereNotNull('plot_sale_detail_id')
            ->where(function ($query) {
                $query->whereIn('payment_status', ['paid', 'cleared'])
                    ->orWhere('transaction_category', 'booking_fee');
            })
            ->min('created_at');
    }

    public function previewAllCommission(string $fromDate, string $toDate, bool $skipExisting = true): array
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
            'self_records' => 0,
            'team_records' => 0,
        ];

        foreach ($associates as $associate) {
            $calculation = $this->calculateCommission(
                associate: $associate,
                fromDate: $fromDate,
                toDate: $toDate,
                skipExisting: $skipExisting
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
            $summary['self_records'] += collect($calculation['rows'])->where('commission_type', 'self')->count();
            $summary['team_records'] += collect($calculation['rows'])->where('commission_type', 'team')->count();
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

            if ($this->isPeriodGenerated($fromDate, $toDate)) {
                throw new \Exception('Commission for selected month has already been generated.');
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
                    $row['commission_generation_id'] = $generation->id;

                    CommissionPayout::create($this->payoutPayload($row));

                    $payoutCount++;
                }

                $generatedCount++;
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
            'self_records' => collect($rows)->where('commission_type', 'self')->count(),
            'team_records' => collect($rows)->where('commission_type', 'team')->count(),
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
            'self_records' => 0,
            'team_records' => 0,
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
        $associateRankPercent = (float) ($associate->rank?->commission ?? 0);
        $sourceRankPercent = (float) ($sourceAssociate->rank?->commission ?? 0);
        $plotSale = $payment->plotSaleDetail;
        $plotLabel = trim(
            ($plotSale?->block?->block ? 'Block '.$plotSale->block->block.' / ' : '').
            'Plot '.($plotSale?->plotDetail?->plot_number ?? '-')
        );

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
            'associate_label' => trim(($associate->associate_id ?? '-').' / '.($associate->associate_name ?? '-')),
            'source_associate_label' => trim(($sourceAssociate->associate_id ?? '-').' / '.($sourceAssociate->associate_name ?? '-')),
            'customer_label' => $payment->customerBooking?->primaryDetail?->name
                ?? $payment->customerBooking?->customer_name
                ?? '-',
            'booking_label' => $plotSale?->booking_code
                ?? $payment->customerBooking?->booking_code
                ?? '-',
            'plot_label' => $plotLabel,
            'project_label' => $plotSale?->project?->name ?? '-',
            'receipt_number' => $payment->receipt_number ?? '-',
            'payment_mode_label' => strtoupper(str_replace('_', ' / ', $payment->payment_mode ?? '-')),
            'associate_rank_percent' => round($associateRankPercent, 2),
            'source_rank_percent' => round($sourceRankPercent, 2),
            'calculation_label' => $commissionType === 'self'
                ? number_format($paymentAmount, 2).' x '.number_format($commissionPercent, 2).'%'
                : number_format($paymentAmount, 2).' x ('.number_format($associateRankPercent, 2).'% - '.number_format($sourceRankPercent, 2).'%)',
        ];
    }

    private function payoutPayload(array $row): array
    {
        return collect($row)->only([
            'commission_generation_id',
            'associate_id',
            'source_associate_id',
            'customer_booking_id',
            'plot_sale_detail_id',
            'customer_payment_id',
            'commission_type',
            'payment_amount',
            'associate_rank_id',
            'source_rank_id',
            'commission_percent',
            'commission_amount',
            'status',
            'generated_date',
        ])->all();
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
            'associate.rank',
            'sourceAssociate.rank',
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

        if ($request->period_month) {
            try {
                $period = $this->resolveCommissionPeriod($request->period_month);

                $query->whereHas('generation', function ($q) use ($period) {
                    $fromDate = Carbon::parse($period['from_date']);

                    $q->whereYear('from_date', $fromDate->year)
                        ->whereMonth('from_date', $fromDate->month);
                });
            } catch (\Throwable) {
                // Ignore invalid filter values and keep the ledger query usable.
            }
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



    public function resolveCommissionDatePeriod(string $commissionDate): array
    {
        $toDate = Carbon::parse($commissionDate)->startOfDay();
        $today = now()->startOfDay();

        if ($toDate->gt($today)) {
            throw new \Exception('Future commission date is not allowed.');
        }

        $lastGeneratedDate = $this->getLastGeneratedToDate();

        if ($lastGeneratedDate) {
            $lastDate = Carbon::parse($lastGeneratedDate)->startOfDay();

            if ($toDate->lte($lastDate)) {
                throw new \Exception(
                    'Commission date must be after last generated date: ' . $lastDate->format('d M Y')
                );
            }

            $fromDate = $lastDate->copy()->addDay();
        } else {
            $firstPaymentDate = $this->getFirstEligiblePaymentDate();

            $fromDate = $firstPaymentDate
                ? Carbon::parse($firstPaymentDate)->startOfDay()
                : $toDate->copy()->startOfMonth();
        }

        if ($fromDate->gt($toDate)) {
            throw new \Exception('Invalid commission date range.');
        }

        return [
            'value' => $toDate->format('Y-m-d'),
            'label' => $toDate->format('d M Y'),
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'range_label' => $fromDate->format('d M Y') . ' to ' . $toDate->format('d M Y'),
        ];
    }
}
