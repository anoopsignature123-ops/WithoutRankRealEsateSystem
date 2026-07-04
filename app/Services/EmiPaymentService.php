<?php

namespace App\Services;

use App\Models\CustomerPayment;
use App\Models\PlotSaleDetail;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EmiPaymentService
{

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $lastId = (CustomerPayment::max('id') ?? 0) + 1;
            $receiptNumber = 'RCP-'.date('Ymd').'-'.str_pad($lastId, 4, '0', STR_PAD_LEFT);
            $plotSaleIds = collect($data['plot_sale_detail_ids'] ?? [])
                ->push($data['plot_sale_detail_id'] ?? null)
                ->filter()
                ->unique()
                ->values();

            if ($plotSaleIds->isEmpty()) {
                throw new Exception('Plot details missing.');
            }

            $plotSales = PlotSaleDetail::whereIn('id', $plotSaleIds)
                ->where('customer_booking_id', $data['customer_booking_id'])
                ->orderBy('id')
                ->get();

            if ($plotSales->count() !== $plotSaleIds->count()) {
                throw new Exception('Plot sale details not found.');
            }

            $dues = $this->calculateDues((int) $data['customer_booking_id'], $plotSales);
            $totalDue = round((float) $dues->sum('due'), 2);
            $paidAmount = round((float) ($data['booking_amount'] ?? 0), 2);

            if ($totalDue <= 0) {
                throw new Exception('This EMI booking is already fully paid.');
            }

            if (($paidAmount - $totalDue) > 0.01) {
                throw new Exception('EMI amount cannot be greater than due amount.');
            }

            if ($paidAmount > $totalDue) {
                $paidAmount = $totalDue;
            }

            $isHoldPayment = in_array($data['payment_mode'], ['cheque', 'dd']);
            $allocations = $this->allocateEmiPaymentAmount($dues, $paidAmount, $totalDue);
            $fields = [
                'bank_name',
                'account_number',
                'branch_name',
                'cheque_number',
                'cheque_date',
                'dd_number',
                'transaction_number',
                'remark',
            ];
            foreach ($fields as $field) {
                $data[$field] = $data[$field] ?? null;
            }

            $createdPayments = collect();

            foreach ($dues as $dueInfo) {
                $plotPaidAmount = round((float) ($allocations[$dueInfo['plot_sale_id']] ?? 0), 2);

                if ($plotPaidAmount <= 0) {
                    continue;
                }

                $newDueAmount = round(max(0, $dueInfo['due'] - $plotPaidAmount), 2);
                $fixedMonthlyEmi = round((float) $dueInfo['monthly_emi'], 2);
                $remainingEmiMonths = $this->calculateRemainingEmiMonths($newDueAmount, $fixedMonthlyEmi);
                $paymentStatus = $isHoldPayment ? 'hold' : ($newDueAmount <= 0 ? 'cleared' : 'paid');

                if (!$isHoldPayment && $newDueAmount <= 0) {
                    CustomerPayment::where('customer_booking_id', $data['customer_booking_id'])
                        ->where('plot_sale_detail_id', $dueInfo['plot_sale_id'])
                        ->where('plan_type', 'emi_plan')
                        ->where('booking_status', 'booked')
                        ->whereIn('payment_status', ['pending', 'paid'])
                        ->update(['payment_status' => 'cleared']);
                }

                $createdPayments->push(CustomerPayment::create(array_merge($data, [
                    'plot_sale_detail_id' => $dueInfo['plot_sale_id'],
                    'receipt_number' => $receiptNumber,
                    'plan_type' => 'emi_plan',
                    'emi_months' => $remainingEmiMonths,
                    'paid_amount' => $plotPaidAmount,
                    'booking_amount' => $plotPaidAmount,
                    'due_amount' => $newDueAmount,
                    'after_booking_payable_amount' => $fixedMonthlyEmi,
                    'net_payable_amount' => $newDueAmount,
                    'transaction_category' => 'emi_payment',
                    'emi_date' => now(),
                    'booking_status' => $isHoldPayment ? 'hold' : 'booked',
                    'payment_status' => $paymentStatus,
                ])));
            }

            if ($createdPayments->contains(fn (CustomerPayment $payment) => app(AutoPromotionService::class)->isEligiblePayment($payment))) {
                app(AutoPromotionService::class)->runForBooking((int) $data['customer_booking_id']);
            }

            return $createdPayments;
        });
    }

    public function calculateDues(int $bookingId, Collection $plotSales): Collection
    {
        return $plotSales->map(function (PlotSaleDetail $plotSale) use ($bookingId) {
            $latestPayment = CustomerPayment::where('customer_booking_id', $bookingId)
                ->where('plot_sale_detail_id', $plotSale->id)
                ->where('plan_type', 'emi_plan')
                ->latest()
                ->first();

            $dueAmount = round((float) ($latestPayment->due_amount ?? 0), 2);
            $monthlyEmi = round((float) ($latestPayment->after_booking_payable_amount ?? 0), 2);

            return [
                'plot_sale_id' => $plotSale->id,
                'total_cost' => round((float) ($plotSale->total_plot_cost ?? 0), 2),
                'due' => $dueAmount,
                'monthly_emi' => $monthlyEmi,
                'booking_amount' => round((float) ($latestPayment->booking_amount ?? 0), 2),
                'emi_months' => $this->calculateRemainingEmiMonths($dueAmount, $monthlyEmi),
                'latest_payment' => $latestPayment,
            ];
        });
    }

    public function calculateRemainingEmiMonths(float $dueAmount, float $monthlyEmi): int
    {
        if ($dueAmount <= 0 || $monthlyEmi <= 0) {
            return 0;
        }

        $months = $dueAmount / $monthlyEmi;
        $roundedMonths = (int) round($months);

        if ($roundedMonths > 0 && abs(($monthlyEmi * $roundedMonths) - $dueAmount) <= 0.05) {
            return $roundedMonths;
        }

        return (int) ceil($months);
    }

    public function allocatePaymentAmount(Collection $dues, float $payingAmount, float $remainingDue): array
    {
        $payableDues = $dues->filter(fn ($dueInfo) => $dueInfo['due'] > 0)->values();

        if ($payableDues->isEmpty()) {
            return [];
        }

        if (abs($payingAmount - $remainingDue) <= 0.01) {
            return $payableDues
                ->mapWithKeys(fn ($dueInfo) => [$dueInfo['plot_sale_id'] => round($dueInfo['due'], 2)])
                ->all();
        }

        $allocations = [];
        $allocatedAmount = 0.0;
        $lastIndex = $payableDues->count() - 1;

        foreach ($payableDues as $index => $dueInfo) {
            if ($index === $lastIndex) {
                $plotAmount = round($payingAmount - $allocatedAmount, 2);
            } else {
                $plotAmount = round($payingAmount * ($dueInfo['due'] / $remainingDue), 2);
                $allocatedAmount = round($allocatedAmount + $plotAmount, 2);
            }

            $allocations[$dueInfo['plot_sale_id']] = max(0, min($plotAmount, $dueInfo['due']));
        }

        return $allocations;
    }

    public function allocateEmiPaymentAmount(Collection $dues, float $payingAmount, float $remainingDue): array
    {
        $payableDues = $dues->filter(fn ($dueInfo) => $dueInfo['due'] > 0)->values();

        if ($payableDues->isEmpty()) {
            return [];
        }

        if (abs($payingAmount - $remainingDue) <= 0.01) {
            return $payableDues
                ->mapWithKeys(fn ($dueInfo) => [$dueInfo['plot_sale_id'] => round($dueInfo['due'], 2)])
                ->all();
        }

        $monthlyWeightTotal = round((float) $payableDues->sum(function ($dueInfo) {
            $monthlyEmi = (float) ($dueInfo['monthly_emi'] ?? 0);

            return min((float) $dueInfo['due'], max(0, $monthlyEmi));
        }), 2);

        if ($monthlyWeightTotal <= 0) {
            return $this->allocatePaymentAmount($dues, $payingAmount, $remainingDue);
        }

        $allocations = [];
        $allocatedAmount = 0.0;
        $lastIndex = $payableDues->count() - 1;

        foreach ($payableDues as $index => $dueInfo) {
            if ($index === $lastIndex) {
                $plotAmount = round($payingAmount - $allocatedAmount, 2);
            } else {
                $monthlyWeight = min((float) $dueInfo['due'], max(0, (float) ($dueInfo['monthly_emi'] ?? 0)));
                $plotAmount = round($payingAmount * ($monthlyWeight / $monthlyWeightTotal), 2);
                $allocatedAmount = round($allocatedAmount + $plotAmount, 2);
            }

            $allocations[$dueInfo['plot_sale_id']] = max(0, min($plotAmount, $dueInfo['due']));
        }

        return $allocations;
    }
}
