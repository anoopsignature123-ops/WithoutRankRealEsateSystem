<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('data/cities.csv');

        if (! file_exists($file)) {
            $this->command->error('cities.csv file not found.');
            return;
        }
        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);
        $rows = [];
        $chunkSize = 1000;

        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = [
                'id_city'    => $data[0],
                'city'       => $data[1],
                'state_id'   => $data[2],
                'is_default' => $data[3] ?? 1,
                'is_active'  => $data[4] ?? 1,
                'sort_order' => $data[5] ?? null,
                'lang'       => $data[6] ?? 'en',
                'created_at' => $data[7] ?? null,
                'updated_at' => $data[8] ?? null,
            ];

            if (count($rows) >= $chunkSize) {
                DB::table('cities')->insert($rows);
                $rows = [];
            }
        }

        if (! empty($rows)) {
            DB::table('cities')->insert($rows);
        }

        fclose($handle);
    }
}