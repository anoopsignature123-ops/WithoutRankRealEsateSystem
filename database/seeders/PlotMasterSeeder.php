<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\PlotDetail;
use App\Models\PlotType;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlotMasterSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        PlotDetail::truncate();
        Block::truncate();
        PlotType::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $plotTypes = [
            'normal' => PlotType::create([
                'plot_type_name' => 'Normal',
                'date' => now()->toDateString(),
            ]),
            'corner' => PlotType::create([
                'plot_type_name' => 'Corner',
                'date' => now()->toDateString(),
            ]),
            'road' => PlotType::create([
                'plot_type_name' => '40 FT Road',
                'date' => now()->toDateString(),
            ]),
        ];

        $projects = Project::query()->get();

        foreach ($projects as $project) {
            foreach (['A', 'B', 'C'] as $blockName) {
                $block = Block::create([
                    'project_id' => $project->id,
                    'block' => $blockName,
                ]);

                for ($i = 1; $i <= 10; $i++) {
                    $plotType = match (true) {
                        $i % 5 === 0 => $plotTypes['corner'],
                        $i % 3 === 0 => $plotTypes['road'],
                        default => $plotTypes['normal'],
                    };

                    PlotDetail::create([
                        'project_id' => $project->id,
                        'block_id' => $block->id,
                        'plot_type_id' => $plotType->id,
                        'location' => $project->location,
                        'number_of_plots' => 10,
                        'plot_number' => $blockName . '-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                        'plot_no_from' => null,
                        'plot_no_to' => null,
                        'plot_rate' => 500,
                        'plc_rate' => $plotType->plot_type_name === 'Normal' ? 0 : 50,
                        'plot_area' => 1000,
                        'plot_width' => 25,
                        'plot_length' => 40,
                        'status' => 'available',
                    ]);
                }
            }
        }
    }
}