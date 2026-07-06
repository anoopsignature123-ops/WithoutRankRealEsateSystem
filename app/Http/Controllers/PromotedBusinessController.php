<?php

namespace App\Http\Controllers;

use App\Models\Associate;
use App\Models\CustomerPayment;
use App\Models\PromotionHistory;
use App\Services\AssociatePromotionService;
use Carbon\Carbon;
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

        $globalBusiness = $this->getGlobalConfirmedBusiness();

        $summary = [
            'total_associates' => $reports->count(),
            'eligible' => $reports->where('can_promote', true)->count(),
            'not_eligible' => $reports->where('can_promote', false)->count(),

            'total_self_business' => $reports->sum('self_business'),
            'total_team_business' => $reports->sum('team_business'),

            // Dashboard se match karne ke liye unique/global paid booked business.
            'total_business' => $globalBusiness,

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

        $businessSnapshots = [];

        $histories->each(function (PromotionHistory $history) use (&$businessSnapshots) {
            if (!$history->associate) {
                $history->setAttribute('display_self_business', (float) $history->self_business);
                $history->setAttribute('display_team_business', (float) $history->team_business);
                $history->setAttribute('display_total_business', (float) $history->total_business);

                return;
            }

            $associateId = (int) $history->associate->id;

            $asOfDate = $history->promotion_date
                ? Carbon::parse($history->promotion_date)
                : $history->created_at;

            $snapshotKey = $associateId . '|' . ($asOfDate ? Carbon::parse($asOfDate)->format('Y-m-d') : 'current');

            if (!isset($businessSnapshots[$snapshotKey])) {
                $businessSnapshots[$snapshotKey] = $this->promotionService
                    ->getBusinessSnapshot($history->associate, $asOfDate);
            }

            $history->setAttribute('display_self_business', $businessSnapshots[$snapshotKey]['self_business']);
            $history->setAttribute('display_team_business', $businessSnapshots[$snapshotKey]['team_business']);
            $history->setAttribute('display_total_business', $businessSnapshots[$snapshotKey]['total_business']);
        });

        $summary = [
            'total_promotions' => $histories->count(),
            'total_self_business' => $histories->sum('display_self_business'),
            'total_team_business' => $histories->sum('display_team_business'),
            'total_business' => $histories->sum('display_total_business'),
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

    private function getGlobalConfirmedBusiness(): float
    {
        return (float) CustomerPayment::query()
            ->where('booking_status', 'booked')
            ->whereIn('payment_status', ['paid', 'cleared'])
            ->where('paid_amount', '>', 0)
            ->whereHas('plotSaleDetail', function ($q) {
                $q->where('status', 'active');
            })
            ->sum('paid_amount');
    }
}