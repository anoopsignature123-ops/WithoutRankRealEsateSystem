<?php

namespace App\Http\Controllers\AssociatePanel;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Associate\BookingDetailService;
use Illuminate\Http\Request;

class BookingDetailController extends Controller
{
    protected $service;

    public function __construct(BookingDetailService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $bookings = $this->service->getFilteredBookings($request);
        $projects = Project::latest()->get();

        return view('associate-panel.booking-details.index', compact('bookings', 'projects'));
    }

    public function getBlocks($projectId)
    {
        return response()->json($this->service->getBlocksByProject($projectId));
    }

    public function getPlots($blockId)
    {
        return response()->json($this->service->getPlotsByBlock($blockId));
    }

    public function getBookingByPlot($plotId)
    {
        return response()->json($this->service->getBookingDataByPlot($plotId));
    }

    public function teamBusinessReport()
    {
        $reports = $this->service->getTeamBusinessData();

        return view('associate-panel.booking-details.team-business', compact('reports'));
    }

    public function dueEmiAmount()
    {
        $dueEmi = $this->service->getDueEmiAmountData();

        return view(
            'associate-panel.booking-details.due-emi',
            compact('dueEmi')
        );
    }
}
