<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionHistory extends Model
{
    protected $fillable = [
        'associate_id',
        'old_rank_id',
        'new_rank_id',
        'self_business',
        'team_business',
        'total_business',
        'promotion_date',
        'remarks',
    ];

    public function associate()
    {
        return $this->belongsTo(Associate::class);
    }

    public function oldRank()
    {
        return $this->belongsTo(DesignationRank::class, 'old_rank_id');
    }

    public function newRank()
    {
        return $this->belongsTo(DesignationRank::class, 'new_rank_id');
    }
}