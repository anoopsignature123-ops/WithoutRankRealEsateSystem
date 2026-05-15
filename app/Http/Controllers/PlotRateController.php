<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlotRateRequest;
use App\Models\Block;
use App\Models\Project;
use App\Services\PlotRateService;

class PlotRateController extends Controller
{
    public function __construct(private PlotRateService $plotRateService) {}

    public function index()
    {
        $plotRates = $this->plotRateService->getAll();

        return view('plot-rates.index', compact('plotRates'));
    }

    public function create()
    {
        $projects = Project::orderBy('name')->get();

        return view('plot-rates.create', compact('projects'));
    }

    public function store(PlotRateRequest $request)
    {
        $this->plotRateService->create($request->validated());

        return redirect()->route('admin.plot-rates.index')->with('success',
            'Plot rate created successfully.');
    }

    public function edit($id)
    {
        $plotRate = $this->plotRateService->find($id);
        $projects = Project::orderBy('name')->get();

        return view('plot-rates.edit', compact('plotRate', 'projects'));
    }

    public function update(PlotRateRequest $request, $id)
    {
        $this->plotRateService->update($id, $request->validated());

        return redirect()->route('admin.plot-rates.index')
            ->with('success', 'Plot rate updated successfully.');
    }

    public function destroy($id)
    {
        $this->plotRateService->delete($id);

        return redirect()->route('admin.plot-rates.index')
            ->with('success', 'Plot rate deleted successfully.');
    }

    public function getProjectBlocks($projectId)
    {
        $blocks = Block::where('project_id', $projectId)
            ->select('id', 'block')->orderBy('block')->get();

        return response()->json($blocks);
    }
}
