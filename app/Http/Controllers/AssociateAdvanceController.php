<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssociateAdvanceRequest;
use App\Services\AssociateAdvanceService;

class AssociateAdvanceController extends Controller
{
    public function __construct(
        private AssociateAdvanceService $service
    ) {}

    public function index()
    {
        $advances = $this->service->getAll();
        return view('payment.associate-advance.index', compact('advances'));
    }

    public function create()
    {
        $associates = $this->service->getAssociates();
        return view('payment.associate-advance.create', compact('associates'));
    }

    public function store(AssociateAdvanceRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.associate-advances.index')
            ->with('success', 'Advance created successfully');
    }

    public function edit($id)
    {
        $advance = $this->service->findById($id);
        $associates = $this->service->getAssociates();
        return view('payment.associate-advance.edit', compact('advance', 'associates'));
    }

    public function update(AssociateAdvanceRequest $request, $id)
    {
        $this->service->update($id, $request->validated());
        return redirect()->route('admin.associate-advances.index')
            ->with('success', 'Advance updated successfully');
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        return back()->with('success', 'Advance deleted successfully');
    }
}