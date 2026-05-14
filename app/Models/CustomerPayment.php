<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    protected $fillable = [
        'customer_booking_id',
        'plot_sale_detail_id',
        'plan_type',
        'booking_amount',
        'due_amount',
        'net_payable_amount',
        'emi_months',
        'after_booking_payable_amount',
        'remark',
        'payment_mode',
        'payment_status',
        'receipt_number',
        'account_number',
        'bank_name',
        'branch_name',
        'cheque_number',
        'cheque_date',
        'dd_number',
        'transaction_number',
        'manual_receipt_number',
    ];

    public function customerBooking()
    {
        return $this->belongsTo(CustomerBooking::class);
    }

    public function plotSaleDetail()
    {
        return $this->belongsTo(PlotSaleDetail::class);
    }
}
