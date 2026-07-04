<?php

namespace App\Http\Controllers;

use App\Models\Associate;
use App\Models\PromotionHistory;
use App\Services\AssociatePromotionService;
use Illuminate\Http\Request;

class PromotedBusinessController extends Controller
{
    public function __construct(
        protected AssociatePromotionService $promotionService
    ) {}

    public function index(Request $request)
    {
        $associateList = Associate::select('id', 'associate_id', 'associate_name')
            ->orderBy('associate_name')
            ->get();

        $query = Associate::with('rank');

        if ($request->filled('associate_id')) {
            $query->where('id', $request->associate_id);
        }

        $associates = $query->latest()->get();

        $reports = $associates->map(function ($associate) {
            return $this->promotionService->getPromotionPreview($associate->id);
        });

        $histories = PromotionHistory::latest()->get();
        $summary = [
            'total_associates' => $reports->count(),
            'eligible' => $reports->where('can_promote', true)->count(),
            'not_eligible' => $reports->where('can_promote', false)->count(),
            'total_self_business' => $reports->sum('self_business'),
            'total_team_business' => $reports->sum('team_business'),
            'total_business' => $reports->sum('total_business'),
            'history_count' => $histories->count(),
        ];

        return view('promoted-business.index', compact(
            'reports',
            'histories',
            'associateList',
            'summary'
        ));
    }

    public function history(Request $request)
    {
        $query = PromotionHistory::with([
            'associate',
            'oldRank',
            'newRank',
        ]);

        if ($request->filled('associate_name')) {
            $query->whereHas('associate', function ($q) use ($request) {
                $q->where('associate_name', 'like', '%'.$request->associate_name.'%')
                    ->orWhere('associate_id', 'like', '%'.$request->associate_name.'%');
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('promotion_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('promotion_date', '<=', $request->to_date);
        }

        $histories = $query->latest()->get();

        $summary = [
            'total_promotions' => $histories->count(),
            'total_self_business' => $histories->sum('self_business'),
            'total_team_business' => $histories->sum('team_business'),
            'total_business' => $histories->sum('total_business'),
        ];

        return view('promoted-business.history', compact('histories', 'summary'));
    }

    public function check($associateId)
    {
        $result = $this->promotionService->checkPromotionResult($associateId);

        return back()->with('promotion_alert', [
            'type' => $result['type'],
            'title' => $result['title'],
            'message' => $result['message'],
        ]);
    }

    public function checkAll()
    {
        $promotedCount = 0;
        $checkedCount = 0;

        Associate::chunk(100, function ($associates) use (&$promotedCount, &$checkedCount) {
            foreach ($associates as $associate) {
                $checkedCount++;

                if ($this->promotionService->checkPromotionResult($associate->id)['promoted']) {
                    $promotedCount++;
                }
            }
        });

        return back()->with('promotion_alert', [
            'type' => $promotedCount > 0 ? 'success' : 'info',
            'title' => $promotedCount > 0 ? 'Promotion Check Completed' : 'No Promotion Available',
            'message' => $promotedCount.' of '.$checkedCount.' associate(s) promoted after checking current business targets.',
        ]);
    }
}
