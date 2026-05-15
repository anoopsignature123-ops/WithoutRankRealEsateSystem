<?php

namespace App\Services;

use App\Models\CustomerBooking;
use App\Models\PlotDetail;

class PlotPaymentService
{
    public function getAll()
    {
        return CustomerBooking::with([
            'primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.plotDetail',
            'payment',
        ])->latest()->get();
    }

    public function findById(int $id)
    {
        return CustomerBooking::with(['primaryDetail', 'plotSaleDetail.project',
            'plotSaleDetail.plotDetail', 'payment', ])->findOrFail($id);
    }

    public function updatePayment(int $bookingId, array $data)
    {
        $booking = $this->findById($bookingId);
        $payment = $booking->payment;
        if (! $payment) {
            abort(404, 'Payment record not found');
        }
        $paymentStatus = 'hold';
        if ($data['plan_type'] === 'emi_plan') {
            $paymentStatus = 'emi';
        }
        if (in_array($data['payment_mode'], ['cash', 'card'])) {
            $paymentStatus = 'booked';
        }
        $payment->update([
            'manual_receipt_number' => $data['manual_receipt_number'],
            'plan_type' => $data['plan_type'],
            'booking_amount' => $data['booking_amount'],
            'due_amount' => $data['due_amount'],
            'emi_months' => $data['emi_months'],
            'after_booking_payable_amount' => $data['after_booking_payable_amount'],
            'payment_mode' => $data['payment_mode'],
            'account_number' => $data['account_number'],
            'bank_name' => $data['bank_name'],
            'branch_name' => $data['branch_name'],
            'cheque_number' => $data['cheque_number'],
            'dd_number' => $data['dd_number'],
            'payment_status' => $paymentStatus,
        ]);

        if ($booking->plotSaleDetail?->plot_detail_id) {
            PlotDetail::where('id', $booking->plotSaleDetail->plot_detail_id)->update([
                'status' => 'booked',
            ]);
        }
        return $payment;
    }
}
