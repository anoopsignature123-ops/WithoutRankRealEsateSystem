<?php

namespace App\Services;

use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use Exception;
use Illuminate\Support\Facades\DB;

class OneTimePaymentService
{
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $booking = CustomerBooking::with('plotSaleDetail')->findOrFail($data['customer_booking_id']);
            $plotSale = $booking->plotSaleDetail;
            if (! $plotSale) {
                throw new Exception('Plot sale details not found.');
            }
            $totalPlotCost = (float) $plotSale->total_plot_cost;
            $alreadyPaid = CustomerPayment::where('customer_booking_id', $booking->id)->sum('booking_amount');
            $payingAmount = (float) $data['booking_amount'];
            $remainingDue = $totalPlotCost - $alreadyPaid;
            if ($remainingDue <= 0) {
                throw new Exception('This booking is already fully paid.');
            }
            if ($payingAmount > $remainingDue) {
                throw new Exception('Payment amount cannot exceed due amount.');
            }
            $lastId = (CustomerPayment::max('id') ?? 0) + 1;
            $autoReceiptNumber = 'RCP-'.date('Ymd').'-'.str_pad($lastId, 4, '0', STR_PAD_LEFT);
            $finalDue = $remainingDue - $payingAmount;
            $paymentStatus = 'booked';
            if (in_array($data['payment_mode'], ['cheque', 'dd'])
            ) {
                $paymentStatus = 'hold';
            }

            return CustomerPayment::create([
                'customer_booking_id' => $booking->id,
                'plot_sale_detail_id' => $data['plot_sale_detail_id'],
                'receipt_number' => $autoReceiptNumber,
                'manual_receipt_number' => $data['manual_receipt_number'] ?? null,
                'plan_type' => 'full_payment',
                'booking_amount' => $payingAmount,
                'due_amount' => $finalDue,
                'net_payable_amount' => $totalPlotCost,
                'remark' => $data['remark'] ?? null,
                'payment_mode' => $data['payment_mode'],
                'account_number' => $data['account_number'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'branch_name' => $data['branch_name'] ?? null,
                'cheque_number' => $data['cheque_number'] ?? null,
                'cheque_date' => $data['cheque_date'] ?? null,
                'dd_number' => $data['dd_number'] ?? null,
                'transaction_number' => $data['transaction_number'] ?? null,
                'payment_status' => $paymentStatus,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        });
    }
}
