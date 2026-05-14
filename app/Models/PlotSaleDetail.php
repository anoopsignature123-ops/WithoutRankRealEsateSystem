<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlotSaleDetail extends Model
{
    protected $fillable = [
        'customer_booking_id',
        'project_id',
        'block_id',
        'plot_detail_id',
        'total_development_charge',
        'development_rate',
        'plot_rate',
        'plot_area',
        'plot_cost',
        'plc_amount',
        'remark',
        'other_charges',
        'final_payable',
        'coupon_discount',
        'total_plot_cost',
        'booking_date',
    ];

    public function customerBooking()
    {
        return $this->belongsTo(CustomerBooking::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    public function plotDetail()
    {
        return $this->belongsTo(PlotDetail::class);
    }

     
}
