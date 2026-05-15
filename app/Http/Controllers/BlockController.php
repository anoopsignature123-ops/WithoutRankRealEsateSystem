<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlockRequest;
use App\Models\Project;
use App\Services\BlockService;

class BlockController extends Controller
{
    protected $blockService;

    public function __construct(BlockService $blockService) {
        $this->blockService = $blockService;
    }

    public function index()
    {
        $blocks = $this->blockService->getAll();
        return view('blocks.index',compact('blocks'));
    }

    public function create()
    {
        $projects = Project::all();
        return view('blocks.create',compact('projects'));
    }

    public function store(BlockRequest $request) {
        $this->blockService->create($request->validated());
        return redirect()->route('admin.blocks.index')
        ->with('success','Block created successfully');
    }

    public function edit($id)
    {
        $block = $this->blockService->find($id);
        $projects = Project::all();
        return view('blocks.edit',compact('block','projects'));
    }

    public function update(BlockRequest $request,$id) {
        $this->blockService->update($id,$request->validated());
        return redirect()->route('admin.blocks.index')
        ->with('success','Block updated successfully');
    }

    public function destroy($id)
    {
        $this->blockService->delete($id);
        return back()->with('success','Block deleted successfully');
    }
}
