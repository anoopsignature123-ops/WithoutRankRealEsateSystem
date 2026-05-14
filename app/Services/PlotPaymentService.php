<?php

namespace App\Services;

use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use App\Models\PlotDetail;
use Illuminate\Support\Str;

class PlotPaymentService
{
    public function getAll()
    {
        return CustomerBooking::with([
            'primaryDetail.customerDocument',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
            'payment',
        ])->orderByDesc('id')->get();
    }

    public function findById($id)
    {
        return CustomerBooking::with([
            'primaryDetail.customerDocument',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
            'payment',
        ])->findOrFail($id);
    }

    public function savePayment($bookingId, array $data)
    {
        $booking = CustomerBooking::findOrFail($bookingId);
        $plotSaleId = $data['plot_sale_detail_id'] ?? $booking->plotSaleDetail?->id;
        $paymentMode = $data['payment_mode'] ?? null;
        $planType = $data['plan_type'] ?? null;

        $transactionNumber = $data['transaction_number'] ??
            strtoupper($paymentMode ?: 'PAY').'-'.time();

        $receiptNumber = $data['receipt_number'] ??
            'REC-'.Str::upper(Str::random(8));

        $paymentStatus = 'hold';

        if ($planType === 'emi_plan') {
            $paymentStatus = 'emi';
        } elseif (in_array($paymentMode, ['cash', 'card'], true)) {
            $paymentStatus = 'booked';
        }

        $payment = CustomerPayment::updateOrCreate(
            [
                'customer_booking_id' => $bookingId,
                'plot_sale_detail_id' => $plotSaleId,
            ],
            [
                'plan_type' => $planType,
                'booking_amount' => $data['booking_amount'] ?? 0,
                'due_amount' => $data['due_amount'] ?? 0,
                'net_payable_amount' => $data['net_payable_amount'] ?? 0,
                'emi_months' => $data['emi_months'] ?? null,
                'after_booking_payable_amount' => $data['after_booking_payable_amount'] ?? null,
                'remark' => $data['remark'] ?? null,
                'payment_mode' => $paymentMode,
                'account_number' => $data['account_number'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'branch_name' => $data['branch_name'] ?? null,
                'cheque_number' => $data['cheque_number'] ?? null,
                'cheque_date' => $data['cheque_date'] ?? null,
                'dd_number' => $data['dd_number'] ?? null,
                'transaction_number' => $transactionNumber,
                'receipt_number' => $receiptNumber,
                'payment_status' => $paymentStatus,
            ]
        );

        if ($plotSaleId) {
            $plotSale = $booking->plotSaleDetails()->find($plotSaleId);
            if ($plotSale && $plotSale->plot_detail_id) {
                PlotDetail::where('id', $plotSale->plot_detail_id)
                    ->update(['status' => 'booked']);
            }
        }

        $booking->update([
            'current_step' => 6,
            'status' => $paymentStatus === 'booked' ? 'completed' : 'pending',
        ]);

        return $payment;
    }
}
