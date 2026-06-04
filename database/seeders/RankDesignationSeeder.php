<?php

namespace Database\Seeders;

use App\Models\DesignationRank;
use Illuminate\Database\Seeder;

class RankDesignationSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'rank_number' => 1,
                'designation' => 'Business Development Executive',
                'commission' => 8,
                'target_from' => 0,
                'target_to' => 300000,
            ],
            [
                'rank_number' => 2,
                'designation' => 'Business Development Manager',
                'commission' => 9,
                'target_from' => 500001,
                'target_to' => 900000,
            ],
            [
                'rank_number' => 3,
                'designation' => 'Sales Manager',
                'commission' => 10,
                'target_from' => 1000001,
                'target_to' => 2100000,
            ],
            [
                'rank_number' => 4,
                'designation' => 'Regional Manager',
                'commission' => 11,
                'target_from' => 2500001,
                'target_to' => 4500000,
            ],
            [
                'rank_number' => 5,
                'designation' => 'Zonal Manager',
                'commission' => 12,
                'target_from' => 5000001,
                'target_to' => 9300000,
            ],
            [
                'rank_number' => 6,
                'designation' => 'Asst. General Manager',
                'commission' => 13,
                'target_from' => 10000001,
                'target_to' => 18900000,
            ],
            [
                'rank_number' => 7,
                'designation' => 'General Manager',
                'commission' => 14,
                'target_from' => 20000001,
                'target_to' => 38100000,
            ],
            [
                'rank_number' => 8,
                'designation' => 'Vice President',
                'commission' => 15,
                'target_from' => 30000001,
                'target_to' => 76500000,
            ],
            [
                'rank_number' => 9,
                'designation' => 'Silver President',
                'commission' => 16,
                'target_from' => 50000001,
                'target_to' => 153300000,
            ],
            [
                'rank_number' => 10,
                'designation' => 'Gold President',
                'commission' => 17,
                'target_from' => 70000001,
                'target_to' => 306900000,
            ],
            [
                'rank_number' => 11,
                'designation' => 'Diamond President',
                'commission' => 18,
                'target_from' => 70000001,
                'target_to' => 614100000,
            ],
            [
                'rank_number' => 12,
                'designation' => 'Platinum President',
                'commission' => 19,
                'target_from' => 90000001,
                'target_to' => 1228500000,
            ],
            [
                'rank_number' => 13,
                'designation' => 'President',
                'commission' => 20,
                'target_from' => 90000001,
                'target_to' => 1228500000,
            ],
        ];

        foreach ($data as $rank) {
            DesignationRank::updateOrCreate(
                [
                    'rank_number' => $rank['rank_number'],
                ],
                $rank
            );
        }
    }
}