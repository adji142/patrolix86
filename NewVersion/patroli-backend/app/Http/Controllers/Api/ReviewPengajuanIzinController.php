<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalKerja;
use App\Models\PengajuanIzin;
use App\Models\Tsecurity;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReviewPengajuanIzinController extends Controller
{
    private function formatDates(mixed $item, array $secMap): array
    {
        $arr = $item->toArray();

        $arr['NamaSecurity']  = $secMap[$item->KodeKaryawan] ?? '-';
        $arr['TglIzinAwal']   = $item->TglIzinAwal   ? Carbon::parse($item->TglIzinAwal)->format('d-m-Y')   : null;
        $arr['TglIzinAkhir']  = $item->TglIzinAkhir  ? Carbon::parse($item->TglIzinAkhir)->format('d-m-Y')  : null;
        $arr['TglPencatatan'] = $item->TglPencatatan  ? Carbon::parse($item->TglPencatatan)->format('d-m-Y') : null;
        $arr['ApprovedOn']    = $item->ApprovedOn     ? Carbon::parse($item->ApprovedOn)->format('d-m-Y H:i') : null;

        return $arr;
    }

    // GET /api/review-pengajuan-izin
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = PengajuanIzin::with('fotos')
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->orderBy('id', 'desc');

        if ($request->filled('tgl_awal')) {
            $query->where('TglPencatatan', '>=', $request->tgl_awal);
        }
        if ($request->filled('tgl_akhir')) {
            $query->where('TglPencatatan', '<=', $request->tgl_akhir);
        }
        if ($request->filled('approval')) {
            $query->where('Approval', $request->approval);
        }
        if ($request->filled('kode_karyawan')) {
            $query->where('KodeKaryawan', $request->kode_karyawan);
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

    // GET /api/review-pengajuan-izin/{id}
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $izin = PengajuanIzin::with('fotos')
            ->where('id', $id)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->first();

        if (!$izin) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $secMap = Tsecurity::where('NIK', $izin->KodeKaryawan)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->pluck('NamaSecurity', 'NIK')
            ->toArray();

        return response()->json([
            'success' => true,
            'data'    => $this->formatDates($izin, $secMap),
        ]);
    }

    // PATCH /api/review-pengajuan-izin/{id}/approve
    public function approve(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $izin = PengajuanIzin::where('id', $id)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->first();

        if (!$izin) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'Approval'        => 'required|integer|in:1,2',
            'CatatanApproval' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $label = $request->Approval == 1 ? 'disetujui' : 'ditolak';

        DB::transaction(function () use ($request, $izin, $user) {
            $izin->update([
                'Approval'        => $request->Approval,
                'ApprovedBy'      => $user->username,
                'ApprovedOn'      => now()->toDateTimeString(),
                'CatatanApproval' => $request->CatatanApproval ?? null,
                'UpdatedOn'       => now()->toDateTimeString(),
            ]);

            // Jika disetujui, update jadwal kerja di periode izin
            if ($request->Approval == 1) {
                JadwalKerja::where('KodeKaryawan', $izin->KodeKaryawan)
                    ->where('RecordOwnerID', $izin->RecordOwnerID)
                    ->whereBetween('Tanggal', [$izin->TglIzinAwal, $izin->TglIzinAkhir])
                    ->update([
                        'StatusKehadiran'           => 'IZIN',
                        'KeteranganStatusKehadiran' => $izin->KeteranganIzin,
                        'BaseEntry'                 => $izin->id,
                        'BaseType'                  => 'Izin',
                    ]);
            }
        });

        $secMap = Tsecurity::where('NIK', $izin->KodeKaryawan)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->pluck('NamaSecurity', 'NIK')
            ->toArray();

        return response()->json([
            'success' => true,
            'message' => "Pengajuan izin berhasil {$label}.",
            'data'    => $this->formatDates($izin->fresh('fotos'), $secMap),
        ]);
    }
}