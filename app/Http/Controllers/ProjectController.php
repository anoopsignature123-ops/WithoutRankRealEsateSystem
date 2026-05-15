<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Services\ProjectService;

class ProjectController extends Controller
{
    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index()
    {
        $projects = $this->projectService->getAll();

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(ProjectRequest $request)
    {
        $this->projectService->create($request->validated());

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project created successfully');
    }

    public function edit($id)
    {
        $project = $this->projectService->find($id);

        return view('projects.edit', compact('project'));
    }

    public function update(ProjectRequest $request, $id)
    {
        $this->projectService->update($id, $request->validated());

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project updated successfully');
    }

    public function destroy($id)
    {
        $this->projectService->delete($id);

        return redirect()->back()
            ->with('success', 'Project deleted successfully');
    }
}
