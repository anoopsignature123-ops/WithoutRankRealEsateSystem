<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerBooking extends Model
{
    use SoftDeletes;

    protected $fillable = ['associate_id', 'customer_id', 'booking_code', 'customer_type', 'customer_code', 'customer_name', 'associate_code', 'associate_name', 'current_step', 'status'];

    public function parentCustomer()
    {
        return $this->belongsTo(
            CustomerBooking::class,
            'customer_id'
        );
    }

    public function associate()
    {
        return $this->belongsTo(Associate::class);
    }

    public function primaryDetail()
    {
        return $this->hasOne(PrimaryDetail::class);
    }

    public function secondaryDetail()
    {
        return $this->hasOne(SecondaryDetail::class);
    }

    public function nomineeDetail()
    {
        return $this->hasOne(NomineeDetail::class);
    }

    public function plotSaleDetails()
    {
        return $this->hasMany(PlotSaleDetail::class);
    }

    public function plotSaleDetail()
    {
        return $this->hasOne(PlotSaleDetail::class)->latestOfMany();
    }

    public function payments()
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function payment()
    {
        return $this->hasOne(CustomerPayment::class);
    }

    public function primaryDocument()
    {
        return $this->hasOneThrough(
            CustomerDocument::class,
            PrimaryDetail::class,
            'customer_booking_id',
            'primary_detail_id',
            'id',
            'id'
        );
    }

    public function secondaryDocument()
    {
        return $this->hasOneThrough(
            CustomerDocument::class,
            SecondaryDetail::class,
            'customer_booking_id',
            'secondary_detail_id',
            'id',
            'id'
        );
    }

    public function plotRegistry()
    {
        return $this->hasOne(
            PlotRegistry::class,
            'customer_booking_id'
        );
    }

    public function cancelBooking()
    {
        return $this->hasOne(
            CancelBooking::class
        );
    }


    // CustomerBooking.php mein
public function latestPayment()
{
    return $this->hasOne(CustomerPayment::class)->latestOfMany();
}
}
