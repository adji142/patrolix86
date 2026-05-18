<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TukarJadwal;
use App\Models\TukarJadwalFoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TukarJadwalController extends Controller
{
    private function saveImage(string $base64): string
    {
        $data = $base64;
        if (str_contains($data, ',')) {
            $data = substr($data, strpos($data, ',') + 1);
        }

        $fileName = 'tukar_' . uniqid() . '.jpg';
        $dir = public_path('tukar-jadwal');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . '/' . $fileName, base64_decode($data));

        return $fileName;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = TukarJadwal::with(['fotos', 'jadwalAwal.shift', 'jadwalBaru.shift', 'targetShift'])
            ->where('KodeKaryawan', $user->username)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->orderBy('id', 'desc');

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'TanggalTukar'  => 'required|date_format:Y-m-d',
            'Keterangan'    => 'required|string|max:1000',
            'idJadwalAwal'  => 'required|exists:jadwalkerja,id',
            'idJadwalBaru'  => 'nullable|exists:jadwalkerja,id',
            'TargetShiftID' => 'nullable|exists:tshift,id',
            'IsToOff'       => 'nullable|boolean',
            'Fotos'         => 'nullable|array|max:5',
            'Fotos.*'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = $request->user();

        return DB::transaction(function () use ($request, $user) {
            $tukar = TukarJadwal::create([
                'RecordOwnerID' => $user->RecordOwnerID,
                'KodeKaryawan'  => $user->username,
                'TanggalTukar'  => $request->TanggalTukar,
                'TglPencatatan' => Carbon::now()->toDateString(),
                'Keterangan'    => $request->Keterangan,
                'Approval'      => 0,
                'idJadwalAwal'  => $request->idJadwalAwal,
                'idJadwalBaru'  => $request->idJadwalBaru,
                'TargetShiftID' => $request->TargetShiftID,
                'IsToOff'       => $request->IsToOff ?? false,
            ]);

            if ($request->filled('Fotos')) {
                foreach ($request->Fotos as $base64) {
                    if (empty($base64)) continue;
                    $fileName = $this->saveImage($base64);
                    TukarJadwalFoto::create([
                        'tukar_jadwal_id' => $tukar->id,
                        'FileName'        => $fileName,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan tukar jadwal berhasil dikirim.',
                'data'    => $tukar->load('fotos'),
            ], 201);
        });
    }
}
