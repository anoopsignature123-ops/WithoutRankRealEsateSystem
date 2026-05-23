<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    protected $fillable = [

        'associate_id',
        'bank_name',
        'account_holder_name',
        'account_number',
        'ifsc_code',
        'nominee_name',
        'nominee_relation',
        'nominee_age',
        'joining_date',
        'bank_passbook',
    ];

    protected $casts = [
        'joining_date' => 'date',
    ];

    public function associate()
    {
        return $this->belongsTo(Associate::class);
    }
}
