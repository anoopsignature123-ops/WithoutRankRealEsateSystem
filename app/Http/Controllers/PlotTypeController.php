<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlotTypeRequest;
use App\Services\PlotTypeService;

class PlotTypeController extends Controller
{
    protected $plotTypeService;
    public function __construct(PlotTypeService $plotTypeService) {
        $this->plotTypeService = $plotTypeService;
    }
    public function index()
    {
        $plotTypes = $this->plotTypeService->getAll();
        return view('plot-types.index',compact('plotTypes'));
    }

    public function create()
    {
        return view('plot-types.create');
    }

    public function store(PlotTypeRequest $request) {
        $this->plotTypeService->create($request->validated());
        return redirect()->route('admin.plot-types.index')
            ->with('success','Plot type created successfully');
    }

    public function edit($id)
    {
        $plotType = $this ->plotTypeService->find($id);
        return view( 'plot-types.edit', compact('plotType'));
    }

    public function update(PlotTypeRequest $request,$id) {
        $this->plotTypeService->update($id,$request->validated());
        return redirect()->route('admin.plot-types.index')
        ->with('success','Plot type updated successfully');
    }

    public function destroy($id)
    {
        $this->plotTypeService->delete($id);
        return redirect()->route('admin.plot-types.index')
            ->with('success','Plot type deleted successfully');
    }
}
