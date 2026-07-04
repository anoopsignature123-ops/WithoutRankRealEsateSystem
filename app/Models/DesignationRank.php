<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignationRank extends Model
{
    protected $fillable = ['designation', 'rank_number', 'commission', 'target_from', 'target_to', 'priority'];
}