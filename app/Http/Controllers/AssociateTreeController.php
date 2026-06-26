<?php

namespace App\Http\Controllers;

use App\Models\Associate;
use Illuminate\Http\Request;

class AssociateTreeController extends Controller
{
    public function index(Request $request)
    {
        $associateId = trim($request->associate_id ?? '');

        if ($associateId) {
            $rootAssociate = Associate::with([
                'rank',
                'children.rank',
                'children.children.rank',
            ])
                ->where('associate_id', $associateId)
                ->first();
        } else {
            $rootAssociate = Associate::with([
                'rank',
                'children.rank',
                'children.children.rank',
            ])
                ->whereNull('under_place_id')
                ->first();
        }

        return view(
            'associate-tree.index',
            compact('rootAssociate')
        );
    }
}
