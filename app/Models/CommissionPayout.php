<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionPayout extends Model
{
    protected $guarded = [];

    public function associate()
    {
        return $this->belongsTo(Associate::class);
    }

    public function sourceAssociate()
    {
        return $this->belongsTo(Associate::class, 'source_associate_id');
    }

    public function customerBooking()
    {
        return $this->belongsTo(CustomerBooking::class);
    }

    public function plotSaleDetail()
    {
        return $this->belongsTo(PlotSaleDetail::class);
    }

    public function payment()
    {
        return $this->belongsTo(CustomerPayment::class, 'customer_payment_id');
    }

    public function generation()
    {
        return $this->belongsTo(CommissionGeneration::class, 'commission_generation_id');
    }
}