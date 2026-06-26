<?php

namespace Database\Seeders;

use App\Models\Associate;
use App\Models\BankDetail;
use App\Models\DesignationRank;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\Hash;

class AssociateSeeder extends Seeder
{
    public function run(): void
    {
        $defaultRank = DesignationRank::orderByDesc(
            'rank_number'
        )->first();
        // Crypt::decryptString($associate->password);
        $associate = Associate::create([
            'associate_id' => '12137644',
            'sponsor_id' => '6326623632',
            'under_place_id' => null,
            'rank_id' => $defaultRank->id,
            'associate_name' => 'Realestate',
            'gender' => 'male',
            'title' => 's/o',
            'father_name' => 'Demo Father',
            'dob' => '2000-01-01',
            'address' => 'Lucknow, Uttar Pradesh',
            'city' => 'Lucknow',
            'state' => 'UP',
            'mobile_number' => '9876543210',
            'email' => 'demo@example.com',
            'password' => Hash::make('Lucknow@123'),
            'plain_password' => 'Lucknow@123',
            'pancard_number' => 'ABCDE1234F',
            'aadhar_number' => '123456789012',
            'photo' => null,
            'id_proof_photo' => null,
        ]);

        BankDetail::create([
            'associate_id' => $associate->id,
            'bank_name' => 'State Bank Of India',
            'account_holder_name' => 'Realestate',
            'account_number' => '12345678901',
            'ifsc_code' => 'SBIN0001234',
            'nominee_name' => 'Demo Nominee',
            'nominee_relation' => 'Brother',
            'nominee_age' => 25,
            'joining_date' => now(),
            'bank_passbook' => null,
        ]);
    }
}