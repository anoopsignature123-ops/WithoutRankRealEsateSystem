<?php

namespace App\Services;

use App\Models\CustomerPayment;
use App\Models\PlotDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlotPaymentService
{
    public function getAll()
    {
        $payments = CustomerPayment::with([
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->whereNotIn('payment_status', ['cleared', 'paid'])
            ->latest()
            ->get();

        return $this->decoratePaymentGroups($payments);
    }

    public function findPaymentById(int $paymentId)
    {
        $payment = CustomerPayment::with([
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->whereNotIn('payment_status', ['cleared', 'paid'])
            ->findOrFail($paymentId);

        return $this->decoratePaymentGroup($payment, $this->getPaymentGroup($payment));
    }

    public function updatePayment(int $paymentId, array $data)
    {
        return DB::transaction(function () use ($paymentId, $data) {
            $payment = CustomerPayment::with('plotSaleDetail.plotDetail')
                ->whereNotIn('payment_status', ['cleared', 'paid'])
                ->findOrFail($paymentId);

            $groupPayments = $this->getPaymentGroup($payment);

            if ($groupPayments->isEmpty()) {
                abort(404, 'Payment record not found.');
            }

            if ($groupPayments->contains(fn ($item) => ! $item->plotSaleDetail)) {
                abort(404, 'Plot sale detail not found.');
            }

            $paidAmount = round((float) ($data['paid_amount'] ?? 0), 2);
            $totalPayable = round((float) $groupPayments->sum(function ($item) {
                return (float) ($item->paid_amount ?? $item->booking_amount ?? 0)
                    + (float) ($item->due_amount ?? 0);
            }), 2);

            if ($paidAmount > $totalPayable + 0.01) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Paid amount cannot exceed payable amount for this receipt.',
                ]);
            }

            $paidAmount = min($paidAmount, $totalPayable);
            $bookingStatus = in_array($data['payment_mode'], ['cheque', 'dd']) ? 'hold' : 'booked';
            $allocations = $this->allocateGroupPaidAmount($groupPayments, $paidAmount, $totalPayable);
            $emiMonths = (int) ($data['emi_months'] ?? 0);
            $updatedPayments = collect();

            foreach ($groupPayments as $groupPayment) {
                $plotSale = $groupPayment->plotSaleDetail;
                $plotPayable = round(
                    (float) ($groupPayment->paid_amount ?? $groupPayment->booking_amount ?? 0)
                    + (float) ($groupPayment->due_amount ?? 0),
                    2
                );
                $plotPaidAmount = round((float) ($allocations[$groupPayment->id] ?? 0), 2);
                $dueAmount = round(max(0, $plotPayable - $plotPaidAmount), 2);
                $paymentStatus = $bookingStatus === 'hold'
                    ? 'hold'
                    : ($dueAmount <= 0 ? 'cleared' : 'paid');

                $afterBookingPayableAmount = null;

                if (($data['plan_type'] ?? null) === 'emi_plan') {
                    $afterBookingPayableAmount = $emiMonths > 0 ? round($dueAmount / $emiMonths, 2) : 0;
                }

                $groupPayment->update([
                    'manual_receipt_number' => $data['manual_receipt_number'] ?? null,
                    'plan_type' => $data['plan_type'] ?? null,
                    'booking_amount' => $plotPaidAmount,
                    'paid_amount' => $plotPaidAmount,
                    'due_amount' => $dueAmount,
                    'net_payable_amount' => $dueAmount,
                    'emi_months' => $data['emi_months'] ?? null,
                    'after_booking_payable_amount' => $afterBookingPayableAmount,
                    'payment_mode' => $data['payment_mode'] ?? null,
                    'account_number' => $data['account_number'] ?? null,
                    'bank_name' => $data['bank_name'] ?? null,
                    'branch_name' => $data['branch_name'] ?? null,
                    'cheque_number' => $data['cheque_number'] ?? null,
                    'cheque_date' => $data['cheque_date'] ?? null,
                    'dd_number' => $data['dd_number'] ?? null,
                    'transaction_number' => $data['transaction_number'] ?? null,
                    'booking_status' => $bookingStatus,
                    'payment_status' => $paymentStatus,
                ]);

                if ($plotSale?->plot_detail_id) {
                    PlotDetail::where('id', $plotSale->plot_detail_id)->update(['status' => $bookingStatus]);
                }

                $updatedPayments->push($groupPayment->fresh());
            }

            return $updatedPayments;
        });
    }

    private function getPaymentGroup(CustomerPayment $payment): Collection
    {
        if (! $payment->receipt_number) {
            return collect([$payment]);
        }

        return CustomerPayment::with([
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->where('customer_booking_id', $payment->customer_booking_id)
            ->where('receipt_number', $payment->receipt_number)
            ->where('transaction_category', $payment->transaction_category)
            ->where('plan_type', $payment->plan_type)
            ->whereNotIn('payment_status', ['cleared', 'paid'])
            ->orderBy('id')
            ->get();
    }

    private function decoratePaymentGroups(Collection $payments): Collection
    {
        return $payments
            ->groupBy(fn ($payment) => $this->paymentGroupKey($payment))
            ->map(function ($group) {
                $payment = $group->sortByDesc('id')->first();

                return $this->decoratePaymentGroup($payment, $group->sortBy('id')->values());
            })
            ->sortByDesc('id')
            ->values();
    }

    private function decoratePaymentGroup(CustomerPayment $payment, Collection $group): CustomerPayment
    {
        $plotSales = $group->pluck('plotSaleDetail')->filter();
        $plotNumbers = $plotSales->map(fn ($sale) => $sale?->plotDetail?->plot_number)->filter()->unique()->values();
        $statuses = $group->pluck('payment_status')->filter()->unique()->values();
        $bookingStatuses = $group->pluck('booking_status')->filter()->unique()->values();

        $payment->setRelation('groupPayments', $group);
        $payment->group_payment_ids = $group->pluck('id')->implode(',');
        $payment->group_plot_count = $plotSales->count();
        $payment->group_plot_numbers = $plotNumbers->implode(', ');
        $payment->group_projects = $plotSales->map(fn ($sale) => $sale?->project?->name)->filter()->unique()->implode(', ');
        $payment->group_blocks = $plotSales->map(fn ($sale) => $sale?->block?->block)->filter()->unique()->implode(', ');
        $payment->group_total_cost = round((float) $plotSales->sum(fn ($sale) => (float) ($sale->total_plot_cost ?? 0)), 2);
        $payment->group_paid_amount = round((float) $group->sum(fn ($item) => (float) ($item->paid_amount ?? $item->booking_amount ?? 0)), 2);
        $payment->group_due_amount = round((float) $group->sum(fn ($item) => (float) ($item->due_amount ?? 0)), 2);
        $payment->group_editable_payable = round($payment->group_paid_amount + $payment->group_due_amount, 2);
        $payment->group_payment_status = $statuses->count() === 1 ? $statuses->first() : 'mixed';
        $payment->group_booking_status = $bookingStatuses->count() === 1 ? $bookingStatuses->first() : 'mixed';
        $payment->group_is_multiple = $plotSales->count() > 1;
        $payment->group_plot_breakdown = $group->map(function ($item) {
            $sale = $item->plotSaleDetail;
            $paid = round((float) ($item->paid_amount ?? $item->booking_amount ?? 0), 2);
            $total = round((float) ($sale?->total_plot_cost ?? 0), 2);
            $due = round((float) ($item->due_amount ?? 0), 2);

            return [
                'plot' => $sale?->plotDetail?->plot_number ?? '-',
                'project' => $sale?->project?->name ?? '-',
                'block' => $sale?->block?->block ?? '-',
                'area' => number_format((float) ($sale?->plot_area ?? 0), 2),
                'total_cost' => $total,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payable_amount' => round($paid + $due, 2),
                'payment_status' => $item->payment_status,
                'booking_status' => $item->booking_status,
            ];
        })->values();

        return $payment;
    }

    private function paymentGroupKey(CustomerPayment $payment): string
    {
        if (! $payment->receipt_number) {
            return 'payment-'.$payment->id;
        }

        return implode('|', [
            $payment->customer_booking_id,
            $payment->receipt_number,
            $payment->transaction_category,
            $payment->plan_type,
        ]);
    }

    private function allocateGroupPaidAmount(Collection $payments, float $paidAmount, float $totalCost): array
    {
        if ($payments->isEmpty()) {
            return [];
        }

        $allocations = [];
        $remainingPaid = $paidAmount;
        $lastIndex = $payments->count() - 1;

        foreach ($payments->values() as $index => $payment) {
            $plotPayable = round(
                (float) ($payment->paid_amount ?? $payment->booking_amount ?? 0)
                + (float) ($payment->due_amount ?? 0),
                2
            );
            $allocated = $index === $lastIndex
                ? $remainingPaid
                : round($totalCost > 0 ? ($paidAmount * $plotPayable / $totalCost) : 0, 2);
            $allocated = max(0, min($allocated, $plotPayable));
            $remainingPaid = round($remainingPaid - $allocated, 2);
            $allocations[$payment->id] = $allocated;
        }

        return $allocations;
    }
}
