<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Associate extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'associate_id',
        'sponsor_id',
        'under_place_id',
        'rank_id',
        'associate_name',
        'gender',
        'title',
        'address',
        'father_name',
        'dob',
        'city',
        'state',
        'mobile_number',
        'pancard_number',
        'email',
        'password',
        'plain_password',
        'aadhar_number',
        'photo',
        'id_proof_photo',
        'pancard_photo',
        'direction',
    ];

    protected $hidden = [
        'password',
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function sponsor()
    {
        return $this->belongsTo(Associate::class, 'sponsor_id', 'associate_id');
    }

    public function underPlace()
    {
        return $this->belongsTo(Associate::class, 'under_place_id', 'associate_id');
    }

    public function rank()
    {
        return $this->belongsTo(DesignationRank::class, 'rank_id');
    }

    public function bankDetail()
    {
        return $this->hasOne(BankDetail::class, 'associate_id', 'id');
    }

    public function stateName(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state', 'id_state');
    }

    public function cityName(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city', 'city');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'sponsor_id', 'associate_id')->with('children');
    }

    public function getDirectCountAttribute()
    {
        return $this->children()->count();
    }

    public function getDownlineCountAttribute()
    {
        return $this->getAllChildrenCount($this);
    }

    private function getAllChildrenCount($associate)
    {
        $count = $associate->children->count();
        foreach ($associate->children as $child) {
            $count += $this->getAllChildrenCount($child);
        }

        return $count;
    }

    public function getLevelAttribute()
    {
        $level = 1;
        $parent = $this;
        while ($parent->under_place_id) {
            $parent = Associate::where('associate_id', $parent->under_place_id)->first();
            if ($parent) {
                $level++;
            } else {
                break;
            }
        }

        return $level;
    }

    public function getDownlineIds()
    {
        $downlineIds = [];

        $children = Associate::where('sponsor_id', $this->associate_id)->pluck('id')->toArray();

        foreach ($children as $childId) {
            $downlineIds[] = $childId;
            $childAssociate = Associate::find($childId);

            if ($childAssociate) {
                $downlineIds = array_merge($downlineIds, $childAssociate->getDownlineIds());

            }
        }

        return array_unique($downlineIds);
    }

    public function commissionPayouts()
    {
        return $this->hasMany(CommissionPayout::class);
    }
}
