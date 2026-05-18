<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalKerja;
use App\Models\TukarJadwal;
use App\Models\Tsecurity;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReviewTukarJadwalController extends Controller
{
    private function formatDates(mixed $item, array $secMap): array
    {
        $arr = $item->toArray();

        $arr['NamaSecurity']  = $secMap[$item->KodeKaryawan] ?? '-';
        $arr['TanggalTukar']  = $item->TanggalTukar  ? Carbon::parse($item->TanggalTukar)->format('d-m-Y') : null;
        $arr['TglPencatatan'] = $item->TglPencatatan ? Carbon::parse($item->TglPencatatan)->format('d-m-Y') : null;
        $arr['ApprovedOn']    = $item->ApprovedOn    ? Carbon::parse($item->ApprovedOn)->format('d-m-Y H:i') : null;

        return $arr;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = TukarJadwal::with(['fotos', 'jadwalAwal.shift', 'jadwalBaru.shift', 'targetShift'])
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->orderBy('id', 'desc');

        if ($request->filled('tgl_awal')) {
            $query->whereDate('TglPencatatan', '>=', $request->tgl_awal);
        }
        if ($request->filled('tgl_akhir')) {
            $query->whereDate('TglPencatatan', '<=', $request->tgl_akhir);
        }
        if ($request->filled('approval')) {
            $query->where('Approval', $request->approval);
        }

        $items = $query->get();

        $secMap = Tsecurity::whereIn('NIK', $items->pluck('KodeKaryawan')->unique())
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->pluck('NamaSecurity', 'NIK');

        $data = $items->map(fn($item) => $this->formatDates($item, $secMap->toArray()));

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    public function approve(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $tukar = TukarJadwal::where('id', $id)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->first();

        if (!$tukar) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'Approval' => 'required|integer|in:1,2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $label = $request->Approval == 1 ? 'disetujui' : 'ditolak';

        DB::transaction(function () use ($request, $tukar, $user) {
            $tukar->update([
                'Approval'   => $request->Approval,
                'ApprovedBy' => $user->username,
                'ApprovedOn' => now()->toDateTimeString(),
            ]);

            // Jika disetujui, update jadwal kerja
            if ($request->Approval == 1) {
                // Update Jadwal Kerja Awal
                $updateData = [
                    'StatusKehadiran'           => 'Tukar',
                    'KeteranganStatusKehadiran' => $tukar->Keterangan,
                    'BaseEntry'                 => $tukar->id,
                    'BaseType'                  => 'TukarJadwal',
                ];

                if ($tukar->IsToOff) {
                    $updateData['shiftid'] = -1;
                    $updateData['StatusKehadiran'] = 'OFF';
                } elseif ($tukar->TargetShiftID) {
                    $updateData['shiftid'] = $tukar->TargetShiftID;
                    $updateData['StatusKehadiran'] = 'Tukar'; // Or keep null as per system default
                }

                JadwalKerja::where('id', $tukar->idJadwalAwal)->update($updateData);

                // Jika ada Jadwal Baru (swap manual dengan orang lain)
                if ($tukar->idJadwalBaru) {
                    // Logic untuk orang kedua jika ini benar-benar swap 2 arah
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Pengajuan tukar jadwal berhasil {$label}.",
        ]);
    }
}
