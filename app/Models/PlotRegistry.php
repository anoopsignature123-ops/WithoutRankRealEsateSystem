<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlotRegistry extends Model
{
    protected $fillable = [
        'project_id',
        'block_id',
        'plot_detail_id',
        'customer_booking_id',
        'gata_number',
        'seller_name',
        'register_no',
        'register_date',
    ];

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

    public function customerBooking()
    {
        return $this->belongsTo(CustomerBooking::class);
    }
}
