<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tshift;
use Illuminate\Http\Request;

class TShiftController extends Controller
{
    public function index(Request $request)
    {
        $query = Tshift::where('RecordOwnerID', $request->user()->RecordOwnerID)
            ->select('id', 'NamaShift', 'MulaiBekerja', 'SelesaiBekerja');

        if ($request->filled('LocationID')) {
            $query->where('LocationID', $request->LocationID);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
        ]);
    }
}