<?php

namespace App\Http\Controllers\AssociatePanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssociateRequest; // Wahi validation request jo pehle thi
use App\Models\Associate;
use App\Models\DesignationRank;
use App\Services\Associate\AssociateRegistrationService;
use Illuminate\Http\Request;

class AssociateRegistrationController extends Controller
{
    protected $associateService;

    public function __construct(AssociateRegistrationService $associateService)
    {
        $this->associateService = $associateService;
    }

    public function create()
    {
        $data = $this->associateService->createData();

        return view('associate-panel.registration.create', $data);
    }

    public function getSponsorRanks($associateId)
    {
        $associate = Associate::where('associate_id', $associateId)->with('rank')->firstOrFail();
        $ranks = DesignationRank::where('rank_number', '<=', $associate->rank->rank_number)->orderByDesc('rank_number')->get();

        return response()->json($ranks);
    }

    public function store(AssociateRequest $request)
    {
        $this->associateService->store($request->validated());

        return redirect()->route('associate-panel.register-create')->with('success', 'Associate registered successfully.');
    }

    public function edit($id)
    {
        $data = $this->associateService->editData($id);

        return view('associate-panel.registration.create', $data);

    }

    public function update(AssociateRequest $request, $id)
    {
        $this->associateService->update($request->validated(), $id);

        return redirect()->route('associate-panel.index')->with('success', 'Associate updated successfully.');
    }
}
