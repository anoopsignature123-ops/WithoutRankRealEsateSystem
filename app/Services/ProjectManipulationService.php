<?php

namespace App\Services;

use App\Models\PlotDetail;
use Illuminate\Http\Request;

class ProjectManipulationService
{
    public function getAll(Request $request)
    {
        $query = PlotDetail::with(['project', 'block']);
        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->block_id) {
            $query->where('block_id', $request->block_id);
        }
        if ($request->plot_number) {
            $query->where('plot_number', $request->plot_number);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        return $query->get();
    }

    public function updateStatus(array $data)
    {
        $plot = PlotDetail::findOrFail($data['plot_id']);
        $plot->update(['status' => $data['status']]);

        return $plot;
    }

    

    public function getPlotsByProject(int $projectId)
    {
        return PlotDetail::where('project_id', $projectId)->get();
    }
}
