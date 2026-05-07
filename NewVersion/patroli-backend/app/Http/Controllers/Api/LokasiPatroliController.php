<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tcheckpoint;
use App\Models\Tlokasipatroli;
use App\Models\Tsecurity;
use App\Traits\FiltersByUserLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LokasiPatroliController extends Controller
{
    use FiltersByUserLocation;

    public function index(Request $request)
    {
        $recordOwnerID = $request->user()->RecordOwnerID;

        $query = Tlokasipatroli::where('RecordOwnerID', $recordOwnerID);

        $allowedIds = $this->getAllowedLocationIds($request);
        if ($allowedIds !== null) {
            $query->whereIn('id', $allowedIds);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'NamaArea'   => 'required|string|max:255',
            'AlamatArea' => 'required|string|max:500',
            'Keterangan' => 'nullable|string|max:500',
            'Latitude'   => 'nullable|numeric',
            'Longitude'  => 'nullable|numeric',
            'Radius'     => 'nullable|integer|min:50|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $lokasi = Tlokasipatroli::create([
            'NamaArea'      => $request->NamaArea,
            'AlamatArea'    => $request->AlamatArea,
            'Keterangan'    => $request->Keterangan,
            'Latitude'      => $request->Latitude,
            'Longitude'     => $request->Longitude,
            'Radius'        => $request->Radius ?? 100,
            'RecordOwnerID' => $request->user()->RecordOwnerID,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi patroli berhasil disimpan.',
            'data'    => $lokasi,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $lokasi = Tlokasipatroli::where('id', $id)
            ->where('RecordOwnerID', $request->user()->RecordOwnerID)
            ->first();

        if (!$lokasi) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $lokasi,
        ]);
    }

    public function update(Request $request, $id)
    {
        $lokasi = Tlokasipatroli::where('id', $id)
            ->where('RecordOwnerID', $request->user()->RecordOwnerID)
            ->first();

        if (!$lokasi) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'NamaArea'   => 'required|string|max:255',
            'AlamatArea' => 'required|string|max:500',
            'Keterangan' => 'nullable|string|max:500',
            'Latitude'   => 'nullable|numeric',
            'Longitude'  => 'nullable|numeric',
            'Radius'     => 'nullable|integer|min:50|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $lokasi->update([
            'NamaArea'   => $request->NamaArea,
            'AlamatArea' => $request->AlamatArea,
            'Keterangan' => $request->Keterangan,
            'Latitude'   => $request->Latitude,
            'Longitude'  => $request->Longitude,
            'Radius'     => $request->Radius ?? 100,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi patroli berhasil diperbarui.',
            'data'    => $lokasi,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $lokasi = Tlokasipatroli::where('id', $id)
            ->where('RecordOwnerID', $request->user()->RecordOwnerID)
            ->first();

        if (!$lokasi) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $usedByCheckpoint = Tcheckpoint::where('LocationID', $id)
            ->where('RecordOwnerID', $request->user()->RecordOwnerID)
            ->exists();

        $usedBySecurity = Tsecurity::where('LocationID', $id)
            ->where('RecordOwnerID', $request->user()->RecordOwnerID)
            ->exists();

        if ($usedByCheckpoint || $usedBySecurity) {
            $sources = [];
            if ($usedByCheckpoint) $sources[] = 'data Checkpoint';
            if ($usedBySecurity)   $sources[] = 'data Master Security';

            return response()->json([
                'success' => false,
                'message' => 'Lokasi tidak dapat dihapus karena sudah digunakan di ' . implode(' dan ', $sources) . '.',
            ], 422);
        }

        $lokasi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lokasi patroli berhasil dihapus.',
        ]);
    }
}