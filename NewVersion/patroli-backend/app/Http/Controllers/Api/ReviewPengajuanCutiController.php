<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalKerja;
use App\Models\PengajuanCuti;
use App\Models\Tsecurity;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReviewPengajuanCutiController extends Controller
{
    private function formatDates(mixed $item, array $secMap): array
    {
        $arr = $item->toArray();

        $arr['NamaSecurity']  = $secMap[$item->KodeKaryawan] ?? '-';
        $arr['TglCutiAwal']   = $item->TglCutiAwal   ? Carbon::parse($item->TglCutiAwal)->format('d-m-Y')   : null;
        $arr['TglCutiAkhir']  = $item->TglCutiAkhir  ? Carbon::parse($item->TglCutiAkhir)->format('d-m-Y')  : null;
        $arr['TglPencatatan'] = $item->TglPencatatan  ? Carbon::parse($item->TglPencatatan)->format('d-m-Y') : null;
        $arr['ApprovedOn']    = $item->ApprovedOn     ? Carbon::parse($item->ApprovedOn)->format('d-m-Y H:i') : null;

        return $arr;
    }

    // GET /api/review-pengajuan-cuti
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = PengajuanCuti::with('fotos')
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
        if ($request->filled('kategori')) {
            $query->where('KategoriCuti', $request->kategori);
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

    // GET /api/review-pengajuan-cuti/{id}
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $cuti = PengajuanCuti::with('fotos')
            ->where('id', $id)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->first();

        if (!$cuti) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $secMap = Tsecurity::where('NIK', $cuti->KodeKaryawan)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->pluck('NamaSecurity', 'NIK')
            ->toArray();

        return response()->json([
            'success' => true,
            'data'    => $this->formatDates($cuti, $secMap),
        ]);
    }

    // PATCH /api/review-pengajuan-cuti/{id}/approve
    public function approve(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $cuti = PengajuanCuti::where('id', $id)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->first();

        if (!$cuti) {
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

        DB::transaction(function () use ($request, $cuti, $user) {
            $cuti->update([
                'Approval'        => $request->Approval,
                'ApprovedBy'      => $user->username,
                'ApprovedOn'      => now()->toDateTimeString(),
                'CatatanApproval' => $request->CatatanApproval ?? null,
                'UpdatedOn'       => now()->toDateTimeString(),
            ]);

            if ($request->Approval == 1) {
                JadwalKerja::where('KodeKaryawan', $cuti->KodeKaryawan)
                    ->where('RecordOwnerID', $cuti->RecordOwnerID)
                    ->whereBetween('Tanggal', [$cuti->TglCutiAwal, $cuti->TglCutiAkhir])
                    ->update([
                        'StatusKehadiran'           => 'CUTI',
                        'KeteranganStatusKehadiran' => $cuti->KeteranganCuti,
                        'BaseEntry'                 => $cuti->id,
                        'BaseType'                  => 'Cuti',
                    ]);
            }
        });

        $secMap = Tsecurity::where('NIK', $cuti->KodeKaryawan)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->pluck('NamaSecurity', 'NIK')
            ->toArray();

        return response()->json([
            'success' => true,
            'message' => "Pengajuan cuti berhasil {$label}.",
            'data'    => $this->formatDates($cuti->fresh('fotos'), $secMap),
        ]);
    }
}