<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JadwalKerjaController extends Controller
{
    public function index(Request $request)
    {
        $recordOwnerID = $request->user()->RecordOwnerID;

        $query = JadwalKerja::with('shift:id,NamaShift,MulaiBekerja,SelesaiBekerja')
            ->where('RecordOwnerID', $recordOwnerID);

        if ($request->filled('nik')) {
            $query->where('KodeKaryawan', $request->nik);
        }

        if ($request->filled('bulan')) {
            $query->where('Bulan', $request->bulan);
        }

        if ($request->filled('tahun')) {
            $query->where('Tahun', $request->tahun);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
        ]);
    }

    public function store(Request $request)
    {
        $recordOwnerID = $request->user()->RecordOwnerID;

        $isOff = $request->StatusKehadiran === 'OFF';

        $validator = Validator::make($request->all(), [
            'KodeKaryawan'              => 'required|string|max:20',
            'Tanggal'                   => 'required|date_format:Y-m-d',
            'shiftid'                   => $isOff ? 'nullable|integer' : 'required|integer',
            'StatusKehadiran'           => 'nullable|string|max:20',
            'KeteranganStatusKehadiran' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $tanggal = $request->Tanggal;
        $date    = \Carbon\Carbon::parse($tanggal);

        $jadwal = JadwalKerja::updateOrCreate(
            [
                'KodeKaryawan'  => $request->KodeKaryawan,
                'Tanggal'       => $tanggal,
                'RecordOwnerID' => $recordOwnerID,
            ],
            [
                'Tgl'                       => $date->day,
                'Bulan'                     => $date->month,
                'Tahun'                     => $date->year,
                'shiftid'                   => $isOff ? null : $request->shiftid,
                'StatusKehadiran'           => $isOff ? 'OFF' : null,
                'KeteranganStatusKehadiran' => $isOff ? null : ($request->KeteranganStatusKehadiran ?? null),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil disimpan.',
            'data'    => $jadwal->load('shift:id,NamaShift,MulaiBekerja,SelesaiBekerja'),
        ], 201);
    }

    public function destroy(Request $request, $id)
    {
        $jadwal = JadwalKerja::where('id', $id)
            ->where('RecordOwnerID', $request->user()->RecordOwnerID)
            ->first();

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $jadwal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dihapus.',
        ]);
    }
}