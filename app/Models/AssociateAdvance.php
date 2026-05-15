<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssociateAdvance extends Model
{
    protected $fillable = [
        'associate_id',
        'advance_amount',
        'advance_date',
        'remarks',
    ];

    protected $casts = [
        'advance_date' => 'date',
    ];

    public function associate()
    {
        return $this->belongsTo(Associate::class);
    }
}