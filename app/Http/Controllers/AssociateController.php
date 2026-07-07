<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssociateRequest;
use App\Models\Associate;
use App\Models\DesignationRank;
use App\Services\AssociateService;
use App\Services\ExcelExportService;
use App\Services\LocationService;
use Illuminate\Http\Request;

class AssociateController extends Controller
{
    protected $associateService;

    protected $locationService;

    public function __construct(AssociateService $associateService, LocationService $locationService)
    {
        $this->associateService = $associateService;
        $this->locationService = $locationService;
    }

    public function index(Request $request)
    {
        $data = $this->associateService->indexData($request);

        return view('associate.index', $data);
    }

    public function create()
    {
        $data = $this->associateService->createData();
        $data['states'] = $this->locationService->getStates();

        return view('associate.create', $data);
    }

    public function getSponsorRanks($associateId)
    {
        $associate = Associate::where('associate_id', $associateId)->with('rank')->firstOrFail();
        $ranks = DesignationRank::where('rank_number', '<=', $associate->rank->rank_number)->get();

        return response()->json($ranks);
    }

    public function store(AssociateRequest $request)
    {
        $this->associateService->store($request->validated());

        return redirect()->route('associate.create')->with('success', 'Associate created successfully.');
    }

    public function show($id)
    {
        $associate = Associate::with(['rank', 'bankDetail', 'sponsor', 'underPlace'])->findOrFail($id);
        $states = $this->locationService->getStates();

        return view('associate.show', compact('associate', 'states'));
    }

    public function edit($id)
    {
        
        $data = $this->associateService->editData($id);
        $data['states'] = $this->locationService->getStates();
        return view('associate.edit', $data);
    }

    public function update(AssociateRequest $request, $id)
    {
        
        $this->associateService->update($request->validated(), $id);
       
        return redirect()->route('associate.index')->with('success', 'Associate updated successfully.');
    }

    public function destroy($id)
    {
        $this->associateService->delete($id);

        return redirect()->route('associate.index')->with('success', 'Associate deleted successfully.');
    }

    public function export(Request $request, ExcelExportService $excelExportService)
    {
        $associates = $this->associateService->getExportData($request);
        $headers = [
            'SNo.',
            'Sponsor Id',
            'Associate Id',
            'Under Place Id',
            'Associate Name',
            'Direction',
            'D.O.B',
            'Address',
            'Mobile',
            'Pancard No',
            'Bank Name',
            'Account No',
            'IFSC Code',
            'Password',
            'Date',
            'Passbook',
            'IdProof',
            'Pancard',
        ];

        return $excelExportService->export($associates, 'associate-list', $headers, function ($associate) {
            return [
                $associate->id,
                $associate->sponsor_id,
                $associate->associate_id,
                $associate->under_place_id,
                $associate->associate_name,
                    $associate->direction,
                    // number_format($associate->rank?->commission, 2).' ('.$associate->rank?->designation.')',
                $associate->dob,
                $associate->address,
                $associate->mobile_number,
                $associate->pancard_number,
                $associate->bankDetail?->bank_name,
                $associate->bankDetail?->account_number,
                $associate->bankDetail?->ifsc_code,
                $associate->password ?? '',
                $associate->created_at?->format('d-M-y'),
                $associate->bankDetail?->bank_passbook ? 'Yes' : 'No',
                $associate->id_proof_photo ? 'Yes' : 'No',
                $associate->photo ? 'Yes' : 'No',
            ];
        }
        );
    }
}