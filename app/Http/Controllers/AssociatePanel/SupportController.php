<?php

namespace App\Http\Controllers\AssociatePanel;

use App\Http\Controllers\Controller;
use App\Models\Support;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function index()
    {
        $enquiries = Support::where('associate_id', Auth::guard('associate')->id())
            ->latest()
            ->get();

        return view('associate-panel.support.index', compact('enquiries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        Support::create([
            'associate_id' => Auth::guard('associate')->id(),
            'query' => $request->input('query'),
            'description' => $request->input('description'),
            'status' => 'Pending',
        ]);

        return redirect()->route('associate-panel.support.index')
            ->with('success', 'Support ticket submitted successfully.');
    }

    public function supportList()
    {
        $supports = Support::with(['associate', 'customerBooking.primaryDetail'])
            ->latest()
            ->paginate(15);

        return view('support.index', compact('supports'));
    }

    public function supportDetail(Support $support)
    {
        return view('support.detail', compact('support'));
    }

    public function supportReply(Request $request, Support $support)
    {
        $request->validate([
            'reply' => 'required|string',
            'status' => 'required|in:Pending,In-Progress,Resolved',
        ]);

        $support->update([
            'reply' => $request->reply,
            'status' => $request->status,
        ]);

        return redirect()->route('support.detail', $support->id)
            ->with('success', 'Reply submitted successfully.');
    }
}
