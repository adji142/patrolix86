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
            ->select('id', 'NamaShift', 'MulaiBekerja', 'SelesaiBekerja', 'GantiHari', 'MulaiAbsen', 'MaxAbsen');

        if ($request->filled('LocationID')) {
            $query->where('LocationID', $request->LocationID);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'LocationID'     => 'required|integer',
            'NamaShift'      => 'required|string|max:50',
            'MulaiBekerja'   => 'required|date_format:H:i',
            'SelesaiBekerja' => 'required|date_format:H:i',
            'GantiHari'      => 'required|boolean',
            'MulaiAbsen'     => 'required|integer',
            'MaxAbsen'       => 'required|integer',
        ]);

        $shift = Tshift::create([
            'RecordOwnerID'  => $request->user()->RecordOwnerID,
            'LocationID'     => $request->LocationID,
            'NamaShift'      => $request->NamaShift,
            'MulaiBekerja'   => $request->MulaiBekerja,
            'SelesaiBekerja' => $request->SelesaiBekerja,
            'GantiHari'      => $request->GantiHari,
            'MulaiAbsen'     => $request->MulaiAbsen,
            'MaxAbsen'       => $request->MaxAbsen,
            'IntervalPatroli'=> 0,
            'IntervalType'   => 'MINUTE',
            'Toleransi'      => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shift berhasil ditambahkan',
            'data'    => $shift,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'NamaShift'      => 'required|string|max:50',
            'MulaiBekerja'   => 'required|date_format:H:i:s,H:i',
            'SelesaiBekerja' => 'required|date_format:H:i:s,H:i',
            'GantiHari'      => 'required|boolean',
            'MulaiAbsen'     => 'required|integer',
            'MaxAbsen'       => 'required|integer',
        ]);

        $shift = Tshift::where('id', $id)
            ->where('RecordOwnerID', $request->user()->RecordOwnerID)
            ->firstOrFail();
            
        // Handle format H:i or H:i:s
        $mulai = strlen($request->MulaiBekerja) == 5 ? $request->MulaiBekerja . ':00' : $request->MulaiBekerja;
        $selesai = strlen($request->SelesaiBekerja) == 5 ? $request->SelesaiBekerja . ':00' : $request->SelesaiBekerja;

        $shift->update([
            'NamaShift'      => $request->NamaShift,
            'MulaiBekerja'   => $mulai,
            'SelesaiBekerja' => $selesai,
            'GantiHari'      => $request->GantiHari,
            'MulaiAbsen'     => $request->MulaiAbsen,
            'MaxAbsen'       => $request->MaxAbsen,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shift berhasil diperbarui',
            'data'    => $shift,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $shift = Tshift::where('id', $id)
            ->where('RecordOwnerID', $request->user()->RecordOwnerID)
            ->firstOrFail();

        $shift->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shift berhasil dihapus',
        ]);
    }
}