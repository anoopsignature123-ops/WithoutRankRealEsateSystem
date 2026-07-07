<?php

namespace App\Http\Controllers;

use App\Models\Associate;
use App\Services\ExcelExportService;
use Illuminate\Http\Request;

class AgentDetailReportController extends Controller
{
    protected $excelExportService;

    public function __construct(ExcelExportService $excelExportService)
    {
        $this->excelExportService = $excelExportService;
    }

    public function index(Request $request)
    {
        $agents = $this->buildQuery($request)->latest()->get();

        $associateList = Associate::select('id', 'associate_id', 'associate_name')->get();

        $summary = [
            'total_records' => $agents->count(),
            'left_associates' => $agents->where('direction', 'left')->count(),
            'right_associates' => $agents->where('direction', 'right')->count(),
            'active_agents' => $agents->where('status', 'active')->count(),
        ];

        return view(
            'reports.agent_detail_report.index',
            compact('agents', 'associateList', 'summary')
        );
    }

    public function export(Request $request)
    {
        $agents = $this->buildQuery($request)->get();

        return $this->excelExportService->export(
            $agents,
            'associate-report',
            [
                'Sponsor ID',
                'Associate ID',
                'Name',
                'Mobile',
                'Direction',
                'Status',
                'Joining Date',
            ],
            function ($agent) {
                return [
                    $agent->sponsor_id ?? 'Self',
                    $agent->associate_id ?? 'N/A',
                    $agent->associate_name ?? 'N/A',
                    $agent->mobile_number ?? 'N/A',
                    ucfirst($agent->direction ?? 'N/A'),
                    ucfirst($agent->status ?? 'N/A'),
                    $agent->created_at ? $agent->created_at->format('d-m-Y') : 'N/A',
                ];
            }
        );
    }

    private function buildQuery(Request $request)
    {
        $query = Associate::query();

        if ($request->filled('associate_id')) {
            $query->where('id', $request->associate_id);
        }

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        if ($request->filled('name')) {
            $query->where('associate_name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('mobile')) {
            $query->where('mobile_number', 'like', '%' . $request->mobile . '%');
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        return $query;
    }
}