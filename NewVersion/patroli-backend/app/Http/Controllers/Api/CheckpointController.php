<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tcheckpoint;
use App\Traits\FiltersByUserLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CheckpointController extends Controller
{
    use FiltersByUserLocation;

    public function index(Request $request)
    {
        $recordOwnerID = $request->user()->RecordOwnerID;

        $query = Tcheckpoint::with('lokasi:id,NamaArea')
            ->where('RecordOwnerID', $recordOwnerID);

        $allowedIds = $this->getAllowedLocationIds($request);
        if ($allowedIds !== null) {
            $query->whereIn('LocationID', $allowedIds);
        }

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
        $recordOwnerID = $request->user()->RecordOwnerID;

        $validator = Validator::make($request->all(), [
            'KodeCheckPoint' => 'required|string|max:50',
            'NamaCheckPoint' => 'required|string|max:255',
            'LocationID'     => 'required',
            'Keterangan'     => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $exists = Tcheckpoint::where('KodeCheckPoint', $request->KodeCheckPoint)
            ->where('RecordOwnerID', $recordOwnerID)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Kode Check Point sudah terdaftar.',
                'errors'  => ['KodeCheckPoint' => ['Kode sudah digunakan.']],
            ], 422);
        }

        $checkpoint = Tcheckpoint::create([
            'KodeCheckPoint' => $request->KodeCheckPoint,
            'NamaCheckPoint' => $request->NamaCheckPoint,
            'LocationID'     => $request->LocationID,
            'Keterangan'     => $request->Keterangan,
            'RecordOwnerID'  => $recordOwnerID,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Titik patroli berhasil disimpan.',
            'data'    => $checkpoint,
        ], 201);
    }

    public function show(Request $request, $kode)
    {
        $checkpoint = Tcheckpoint::with('lokasi:id,NamaArea')
            ->where('KodeCheckPoint', $kode)
            ->where('RecordOwnerID', $request->user()->RecordOwnerID)
            ->first();

        if (!$checkpoint) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $checkpoint,
        ]);
    }

    public function update(Request $request, $kode)
    {
        $checkpoint = Tcheckpoint::where('KodeCheckPoint', $kode)
            ->where('RecordOwnerID', $request->user()->RecordOwnerID)
            ->first();

        if (!$checkpoint) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'NamaCheckPoint' => 'required|string|max:255',
            'LocationID'     => 'required',
            'Keterangan'     => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $checkpoint->update([
            'NamaCheckPoint' => $request->NamaCheckPoint,
            'LocationID'     => $request->LocationID,
            'Keterangan'     => $request->Keterangan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Titik patroli berhasil diperbarui.',
            'data'    => $checkpoint,
        ]);
    }

    public function destroy(Request $request, $kode)
    {
        $checkpoint = Tcheckpoint::where('KodeCheckPoint', $kode)
            ->where('RecordOwnerID', $request->user()->RecordOwnerID)
            ->first();

        if (!$checkpoint) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $checkpoint->delete();

        return response()->json([
            'success' => true,
            'message' => 'Titik patroli berhasil dihapus.',
        ]);
    }
}