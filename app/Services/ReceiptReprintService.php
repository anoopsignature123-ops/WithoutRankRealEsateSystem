<?php

namespace App\Services;

use App\Models\CustomerPayment;

class ReceiptReprintService
{
    public function __construct(private ReceiptPdfService $receiptPdfService) {}

    public function search($customerBookingId, $receiptGroup = null)
    {
        $payments = CustomerPayment::with([
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->where('customer_booking_id', $customerBookingId)
            ->latest()
            ->get();

        return $this->groupReceipts($payments)
            ->when($receiptGroup, fn ($receipts) => $receipts->where('receipt_group_key', $receiptGroup))
            ->values();
    }

    public function receiptGroupsByCustomer($customerBookingId)
    {
        $payments = CustomerPayment::with([
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->where('customer_booking_id', $customerBookingId)
            ->latest()
            ->get();

        return $this->groupReceipts($payments)->map(function ($receipt) {
            return [
                'id' => $receipt->receipt_group_key,
                'text' => trim(($receipt->receipt_number ?? 'Receipt N/A') . ' | ' . $receipt->group_booking_label . ' | ' . $receipt->group_plot_label),
            ];
        })->values();
    }

    private function groupReceipts($payments)
    {
        return $payments
            ->groupBy(fn ($payment) => $payment->receipt_number ?: 'payment-'.$payment->id)
            ->map(function ($group, $key) {
                $receipt = $group->sortByDesc('id')->first();
                $plotSales = $group->pluck('plotSaleDetail')
                    ->filter();
                $plotLabels = $plotSales->map(function ($plotSale) {
                    return trim(($plotSale?->block?->block ?? '-') . '-' . ($plotSale?->plotDetail?->plot_number ?? '-'));
                })->unique()->values();
                $bookingLabels = $plotSales->pluck('booking_code')->filter()->unique()->values();

                $receipt->receipt_group_key = $key;
                $receipt->group_amount = (float) $group->sum(fn ($payment) => $payment->paid_amount ?? $payment->booking_amount ?? 0);
                $receipt->group_due_amount = (float) $group->sum('due_amount');
                $receipt->group_plot_count = $plotLabels->count();
                $receipt->group_plot_label = $plotLabels->isNotEmpty() ? $plotLabels->implode(', ') : '-';
                $receipt->group_booking_label = $bookingLabels->isNotEmpty()
                    ? $bookingLabels->implode(', ')
                    : ($receipt->customerBooking?->booking_code ?? 'N/A');

                return $receipt;
            })
            ->sortByDesc('created_at')
            ->values();
    }

    public function downloadPdf($paymentId)
    {
        $payment = CustomerPayment::with([
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])->findOrFail($paymentId);

        return $this->receiptPdfService->download($payment);
    }
}