<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        Project::query()->delete();

        Project::create([
            'name' => 'Gomti Green',
            'location' => 'Lucknow',
            'date' => now()->toDateString(),
        ]);

        Project::create([
            'name' => 'Royal City',
            'location' => 'Lucknow',
            'date' => now()->toDateString(),
        ]);
    }
}