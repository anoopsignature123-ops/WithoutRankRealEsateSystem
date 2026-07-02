<?php

namespace App\Http\Controllers\AssociatePanel;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\PlotDetail;
use App\Models\PlotRegistry;
use App\Models\Project;
use Illuminate\Http\Request;

class PlotAvilabilityController extends Controller
{
    public function plotAvilable(Request $request)
    {
        $query = PlotDetail::with([
            'project',
            'block',
            'plotSaleDetail' => function ($q) {
                $q->where('status', 'active');
            },
        ]);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('block_id')) {
            $query->where('block_id', $request->block_id);
        }

        if ($request->filled('plot_number')) {
            $query->where('plot_number', 'like', '%' . $request->plot_number . '%');
        }

        $plots = $query->get()->each(function ($plot) {
            $plot->current_status = 'Available';

            if ($plot->status == 'hold') {
                $plot->current_status = 'Hold Plot';
            }

            if ($plot->status == 'booked' && $plot->plotSaleDetail) {
                $plot->current_status = ($plot->plotSaleDetail->booking_status == 'alloted')
                    ? 'Alloted Plot'
                    : 'Booked Plot';
            }

            if (PlotRegistry::where('plot_detail_id', $plot->id)->exists()) {
                $plot->current_status = 'Registry Plot';
            }
        });

        $projects = Project::all();
        $blocks = Block::all();

        return view('associate-panel.plot-avilable.index', compact('plots', 'projects', 'blocks'));
    }

    public function index(Request $request)
    {
        $query = PlotDetail::with([
            'project',
            'block',
            'plotSaleDetail' => function ($q) {
                $q->where('status', 'active');
            },
        ]);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('block_id')) {
            $query->where('block_id', $request->block_id);
        }

        if ($request->filled('plot_number')) {
            $query->where('plot_number', 'like', '%' . $request->plot_number . '%');
        }

        $plots = $query->get()->each(function ($plot) {
            $plot->current_status = 'Available';

            if ($plot->status == 'hold') {
                $plot->current_status = 'Hold Plot';
            }

            if ($plot->status == 'booked' && $plot->plotSaleDetail) {
                $plot->current_status = ($plot->plotSaleDetail->booking_status == 'alloted')
                    ? 'Alloted Plot'
                    : 'Booked Plot';
            }

            if (PlotRegistry::where('plot_detail_id', $plot->id)->exists()) {
                $plot->current_status = 'Registry Plot';
            }
        });

        $projects = Project::all();
        $blocks = Block::all();

        return view('plot-avilable.index', compact('plots', 'projects', 'blocks'));
    }
}