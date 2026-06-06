<?php

namespace App\Http\Controllers;

use App\Models\PlotDetail;
use App\Models\Project;
use App\Services\ExcelExportService;
use App\Services\PdfExportService;
use App\Services\ProjectManipulationService;
use Illuminate\Http\Request;

class ProjectManipulationController extends Controller
{
    public function __construct(
        private ProjectManipulationService $projectManipulationService,
        private ExcelExportService $excelExportService,
        private PdfExportService $pdfExportService
    ) {}

    public function index(Request $request)
    {
        $projects = Project::all();
        $plots = $this->projectManipulationService->getAll($request);

        return view('project-manipulation.index', compact('projects', 'plots'));
    }

    // public function updateStatus(Request $request)
    // {
    //     $this->projectManipulationService->updateStatus($request->all());

    //     return back()->with('success', 'Status updated successfully.');
    // }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'plot_id' => ['required', 'exists:plot_details,id'],
            'status' => ['required', 'in:available,booked,hold,registry'],
        ]);

        $plot = PlotDetail::findOrFail($request->plot_id);

        if ($plot->status === 'booked') {
            return back()->with('error', 'Booked plot status cannot be changed.');
        }

        $plot->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Plot status updated successfully.');
    }

    public function getPlotsByProject($projectId)
    {
        return $this->projectManipulationService->getPlotsByProject($projectId);
    }

    public function export(Request $request)
    {
        $plots = $this->projectManipulationService->getAll($request);
        $headers = ['Project', 'Block', 'Plot No', 'Status', 'Updated Date'];
        $callback = function ($plot) {
            return [
                $plot->project?->name,
                $plot->block?->block,
                $plot->plot_number,
                ucfirst($plot->status),
                $plot->updated_at?->format('d-m-Y'),
            ];
        };
        if ($request->type == 'excel') {
            return $this->excelExportService->export($plots, 'project-manipulation', $headers, $callback);
        }

        if ($request->type == 'pdf') {
            return $this->pdfExportService->export($plots, 'project-manipulation', $headers, $callback);
        }
    }
}