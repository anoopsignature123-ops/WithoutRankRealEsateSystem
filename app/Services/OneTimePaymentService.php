<?php

namespace App\Services;

use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use App\Models\PlotSaleDetail;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OneTimePaymentService
{
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $booking = CustomerBooking::findOrFail($data['customer_booking_id']);

            $plotSaleIds = collect($data['plot_sale_detail_ids'] ?? [])
                ->push($data['plot_sale_detail_id'] ?? null)
                ->filter()
                ->unique()
                ->values();

            if ($plotSaleIds->isEmpty()) {
                throw new Exception('Plot sale details missing.');
            }

            $plotSales = PlotSaleDetail::whereIn('id', $plotSaleIds)
                ->where('customer_booking_id', $booking->id)
                ->orderBy('id')
                ->get();

            if ($plotSales->count() !== $plotSaleIds->count()) {
                throw new Exception('Plot sale details not found.');
            }

            $dues = $this->calculateDues($booking->id, $plotSales);
            $remainingDue = round((float) $dues->sum('due'), 2);
            $payingAmount = round((float) $data['booking_amount'], 2);

            if ($remainingDue <= 0) {
                throw new Exception('This booking is already fully paid.');
            }

            if (($payingAmount - $remainingDue) > 0.01) {
                throw new Exception('Payment amount cannot exceed due amount.');
            }

            if ($payingAmount > $remainingDue) {
                $payingAmount = $remainingDue;
            }

            $lastId = (CustomerPayment::max('id') ?? 0) + 1;
            $autoReceiptNumber = 'RCP-'.date('Ymd').'-'.str_pad($lastId, 4, '0', STR_PAD_LEFT);
            $isHoldPayment = in_array($data['payment_mode'], ['cheque', 'dd']);
            $bookingStatus = $isHoldPayment ? 'hold' : 'booked';
            $allocations = $this->allocatePaymentAmount($dues, $payingAmount, $remainingDue);
            $createdPayments = collect();

            foreach ($dues as $dueInfo) {
                $plotPaymentAmount = round((float) ($allocations[$dueInfo['plot_sale_id']] ?? 0), 2);

                if ($plotPaymentAmount <= 0) {
                    continue;
                }

                $finalDue = round($dueInfo['due'] - $plotPaymentAmount, 2);
                $paymentStatus = $isHoldPayment ? 'hold' : ($finalDue <= 0 ? 'cleared' : 'paid');

                if (!$isHoldPayment && $finalDue <= 0) {
                    CustomerPayment::where('customer_booking_id', $booking->id)
                        ->where('plot_sale_detail_id', $dueInfo['plot_sale_id'])
                        ->where('plan_type', 'full_payment')
                        ->where('booking_status', 'booked')
                        ->whereIn('payment_status', ['pending', 'paid'])
                        ->update(['payment_status' => 'cleared']);
                }

                $createdPayments->push(CustomerPayment::create([
                    'customer_booking_id' => $booking->id,
                    'plot_sale_detail_id' => $dueInfo['plot_sale_id'],
                    'receipt_number' => $autoReceiptNumber,
                    'manual_receipt_number' => $data['manual_receipt_number'] ?? null,
                    'plan_type' => 'full_payment',
                    'transaction_category' => 'one_time',
                    'booking_amount' => $plotPaymentAmount,
                    'paid_amount' => $plotPaymentAmount,
                    'due_amount' => $finalDue,
                    'net_payable_amount' => $dueInfo['total_cost'],
                    'remark' => $data['remark'] ?? null,
                    'payment_mode' => $data['payment_mode'],
                    'account_number' => $data['account_number'] ?? null,
                    'bank_name' => $data['bank_name'] ?? null,
                    'branch_name' => $data['branch_name'] ?? null,
                    'cheque_number' => $data['cheque_number'] ?? null,
                    'cheque_date' => $data['cheque_date'] ?? null,
                    'dd_number' => $data['dd_number'] ?? null,
                    'transaction_number' => $data['transaction_number'] ?? null,
                    'booking_status' => $bookingStatus,
                    'payment_status' => $paymentStatus,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }

            if ($createdPayments->contains(fn (CustomerPayment $payment) => app(AutoPromotionService::class)->isEligiblePayment($payment))) {
                app(AutoPromotionService::class)->runForBooking($booking);
            }

            return $createdPayments;
        });
    }

    private function calculateDues(int $bookingId, Collection $plotSales): Collection
    {
        $paidByPlot = CustomerPayment::where('customer_booking_id', $bookingId)
            ->whereIn('plot_sale_detail_id', $plotSales->pluck('id'))
            ->where('plan_type', 'full_payment')
            ->where('booking_status', 'booked')
            ->selectRaw('plot_sale_detail_id, SUM(paid_amount) as paid_amount')
            ->groupBy('plot_sale_detail_id')
            ->pluck('paid_amount', 'plot_sale_detail_id');

        return $plotSales->map(function (PlotSaleDetail $plotSale) use ($paidByPlot) {
            $totalCost = round((float) $plotSale->total_plot_cost, 2);
            $alreadyPaid = round((float) ($paidByPlot[$plotSale->id] ?? 0), 2);

            return [
                'plot_sale_id' => $plotSale->id,
                'total_cost' => $totalCost,
                'paid' => $alreadyPaid,
                'due' => round(max(0, $totalCost - $alreadyPaid), 2),
            ];
        });
    }

    private function allocatePaymentAmount(Collection $dues, float $payingAmount, float $remainingDue): array
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
}
