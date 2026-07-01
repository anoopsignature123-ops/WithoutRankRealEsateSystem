<?php

namespace App\Http\Controllers\AssociatePanel;

use App\Http\Controllers\Controller;
use App\Models\Associate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function myTree(Request $request)
    {
        $user = Auth::guard('associate')->user();
        $associateId = trim($request->associate_id ?? $user->associate_id);
        $allowedIds = collect($user->getDownlineIds())
            ->push($user->id)
            ->unique()
            ->values()
            ->all();

        $rootAssociate = Associate::with([
            'rank',
            'children.rank',
            'children.children.rank',
        ])
            ->whereIn('id', $allowedIds)
            ->where('associate_id', $associateId)
            ->first();

        return view('associate-panel.team.my_tree', compact('rootAssociate'));
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('associate_id')) {
            $query->where('associate_id', 'like', '%'.trim($request->associate_id).'%');
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        return $query;
    }

    // My Direct
    public function myDirect(Request $request)
    {
        $user = Auth::guard('associate')->user();

        $query = Associate::with(['sponsor', 'rank'])
            ->where('sponsor_id', $user->associate_id);

        $query = $this->applyFilters($query, $request);

        $associates = $query->latest()->get();

        return view('associate-panel.team.my_direct', compact('associates'));
    }

    // My Downline
    public function myDownline(Request $request)
    {
        $user = Auth::guard('associate')->user();

        $query = Associate::with(['sponsor', 'rank'])
            ->where('under_place_id', $user->associate_id);

        $query = $this->applyFilters($query, $request);

        $associates = $query->latest()->get();

        return view('associate-panel.team.my_downline', compact('associates'));
    }
}
