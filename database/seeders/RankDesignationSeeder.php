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
                'priority' => 1,
                'designation' => 'Business Development Executive',
                'commission' => 8,
                'target_from' => 0,
                'target_to' => 500000,
            ],

            [
                'rank_number' => 2,
                'priority' => 2,
                'designation' => 'Business Development Manager',
                'commission' => 9,
                'target_from' => 500001,
                'target_to' => 1000000,
            ],

            [
                'rank_number' => 3,
                'priority' => 3,
                'designation' => 'Sales Manager',
                'commission' => 10,
                'target_from' => 1000001,
                'target_to' => 2500000,
            ],

            [
                'rank_number' => 4,
                'priority' => 4,
                'designation' => 'Regional Manager',
                'commission' => 11,
                'target_from' => 2500001,
                'target_to' => 5000000,
            ],

            [
                'rank_number' => 5,
                'priority' => 5,
                'designation' => 'Zonal Manager',
                'commission' => 12,
                'target_from' => 5000001,
                'target_to' => 10000000,
            ],

            [
                'rank_number' => 6,
                'priority' => 6,
                'designation' => 'Asst. General Manager',
                'commission' => 13,
                'target_from' => 10000001,
                'target_to' => 20000000,
            ],

            [
                'rank_number' => 7,
                'priority' => 7,
                'designation' => 'General Manager',
                'commission' => 14,
                'target_from' => 20000001,
                'target_to' => 30000000,
            ],

            [
                'rank_number' => 8,
                'priority' => 8,
                'designation' => 'Vice President',
                'commission' => 15,
                'target_from' => 30000001,
                'target_to' => 50000000,
            ],

            [
                'rank_number' => 9,
                'priority' => 9,
                'designation' => 'Silver President',
                'commission' => 16,
                'target_from' => 50000001,
                'target_to' => 70000000,
            ],

            [
                'rank_number' => 10,
                'priority' => 10,
                'designation' => 'Gold President',
                'commission' => 17,
                'target_from' => 70000001,
                'target_to' => 150000000,
            ],

            [
                'rank_number' => 11,
                'priority' => 11,
                'designation' => 'Diamond President',
                'commission' => 18,
                'target_from' => 150000001,
                'target_to' => 300000000,
            ],

            [
                'rank_number' => 12,
                'priority' => 12,
                'designation' => 'Platinum President',
                'commission' => 19,
                'target_from' => 300000001,
                'target_to' => 500000000,
            ],

            [
                'rank_number' => 13,
                'priority' => 13,
                'designation' => 'President',
                'commission' => 20,
                'target_from' => 500000001,
                'target_to' => 999999999999,
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