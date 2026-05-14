<?php

namespace App\Services;

use App\Models\PlotRegistry;

class PlotRegistryService
{
    public function create(array $data): PlotRegistry
    {
        return PlotRegistry::create([

            'project_id' => $data['project_id'],
            'block_id' => $data['block_id'],
            'plot_detail_id' => $data['plot_detail_id'],
            'customer_booking_id' => $data['customer_booking_id'],

            'gata_number' => $data['gata_number'],
            'seller_name' => $data['seller_name'],
            'register_no' => $data['register_no'],
            'register_date' => $data['register_date'],

        ]);
    }
}
