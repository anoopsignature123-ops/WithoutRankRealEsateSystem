<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionGeneration extends Model
{
    protected $guarded = [];

    public function associate()
    {
        return $this->belongsTo(Associate::class);
    }

    public function commissions()
    {
        return $this->hasMany(CommissionPayout::class);
    }
}