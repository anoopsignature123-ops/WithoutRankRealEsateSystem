<?php

namespace App\Services;

use App\Models\CustomerPayment;
use App\Models\PlotDetail;

class PlotPaymentService
{
    public function getAll()
    {
        return CustomerPayment::with([
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->whereNotIn('payment_status', ['cleared', 'paid'])
            ->latest()
            ->get();
    }

    public function findPaymentById(int $paymentId)
    {
        return CustomerPayment::with([
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->whereNotIn('payment_status', ['cleared', 'paid'])
            ->findOrFail($paymentId);
    }

    public function updatePayment(int $paymentId, array $data)
    {
        $payment = $this->findPaymentById($paymentId);

        $plotSale = $payment->plotSaleDetail;

        if (! $plotSale) {
            abort(404, 'Plot sale detail not found.');
        }

        $paidAmount = round((float) ($data['paid_amount'] ?? 0), 2);
        $totalPlotCost = round((float) ($plotSale->total_plot_cost ?? 0), 2);
        $dueAmount = round(max(0, $totalPlotCost - $paidAmount), 2);

        $bookingStatus = in_array($data['payment_mode'], ['cheque', 'dd'])
            ? 'hold'
            : 'booked';

        $paymentStatus = $dueAmount <= 0 ? 'cleared' : 'pending';

        $afterBookingPayableAmount = null;

        if (($data['plan_type'] ?? null) === 'emi_plan') {
            $emiMonths = (int) ($data['emi_months'] ?? 0);

            $afterBookingPayableAmount = $emiMonths > 0
                ? round($dueAmount / $emiMonths, 2)
                : 0;
        }

        $payment->update([
            'manual_receipt_number' => $data['manual_receipt_number'] ?? null,
            'plan_type' => $data['plan_type'] ?? null,

            'booking_amount' => $paidAmount,
            'paid_amount' => $paidAmount,
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

        if ($plotSale->plot_detail_id) {
            PlotDetail::where('id', $plotSale->plot_detail_id)
                ->update([
                    'status' => $bookingStatus,
                ]);
        }

        return $payment;
    }
}
