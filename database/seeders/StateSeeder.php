<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('data/states.csv');

        if (!file_exists($file)) {
            $this->command->error('states.csv file not found.');
            return;
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('cities')->truncate();
        DB::table('states')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $handle = fopen($file, 'r');
        fgetcsv($handle);

        $rows = [];

        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = [
                'id_state' => $data[0],
                'state' => $data[1],
                'country_id' => $data[2] ?? 101,
                'is_active' => $data[3] ?? 1,
                'sort_order' => $data[4] ?? null,
                'lang' => $data[5] ?? 'en',
            ];
        }
        fclose($handle);
        DB::table('states')->insert($rows);
    }
}