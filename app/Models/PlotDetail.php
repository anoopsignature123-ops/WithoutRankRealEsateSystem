<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlotDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'block_id',
        'plot_type_id',
        'location',
        'number_of_plots',
        'plot_number',
        'plot_no_from',
        'plot_no_to',
        'plot_rate',
        'plc_rate',
        'plot_area',
        'plot_width',
        'plot_length',
        'status',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    public function plotType()
    {
        return $this->belongsTo(PlotType::class);
    }

    public function plotSale()
    {
        return $this->hasOne(PlotSaleDetail::class);
    }
}
