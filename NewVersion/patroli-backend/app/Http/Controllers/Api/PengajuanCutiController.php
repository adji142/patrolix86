<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengajuanCuti;
use App\Models\PengajuanCutiFoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PengajuanCutiController extends Controller
{
    private const KATEGORI_LIST = [
        'Cuti Tahunan',
        'Cuti Hamil',
        'Cuti Sakit',
    ];

    private function saveImage(string $base64): string
    {
        $data = $base64;
        if (str_contains($data, ',')) {
            $data = substr($data, strpos($data, ',') + 1);
        }

        $fileName = 'cuti_' . uniqid() . '.jpg';
        $dir = public_path('pengajuan-cuti');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . '/' . $fileName, base64_decode($data));

        return $fileName;
    }

    // GET /api/pengajuan-cuti/kategori — daftar kategori cuti
    public function kategori(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => self::KATEGORI_LIST,
        ]);
    }

    // GET /api/pengajuan-cuti — riwayat cuti milik user yang login
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = PengajuanCuti::with('fotos')
            ->where('KodeKaryawan', $user->username)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->orderBy('id', 'desc');

        if ($request->filled('tahun') && $request->filled('bulan')) {
            $query->whereYear('TglCutiAwal', $request->tahun)
                  ->whereMonth('TglCutiAwal', $request->bulan);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
        ]);
    }

    // POST /api/pengajuan-cuti — submit pengajuan baru
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'TglCutiAwal'    => 'required|date_format:Y-m-d',
            'TglCutiAkhir'   => 'required|date_format:Y-m-d|after_or_equal:TglCutiAwal',
            'KeteranganCuti' => 'required|string|max:1000',
            'KategoriCuti'   => 'required|string|in:' . implode(',', self::KATEGORI_LIST),
            'Fotos'          => 'nullable|array|max:5',
            'Fotos.*'        => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $now  = now()->toDateTimeString();

        return DB::transaction(function () use ($request, $user, $now) {
            $cuti = PengajuanCuti::create([
                'RecordOwnerID'  => $user->RecordOwnerID,
                'KodeKaryawan'   => $user->username,
                'TglCutiAwal'    => $request->TglCutiAwal,
                'TglCutiAkhir'   => $request->TglCutiAkhir,
                'TglPencatatan'  => now()->toDateString(),
                'KeteranganCuti' => $request->KeteranganCuti,
                'KategoriCuti'   => $request->KategoriCuti,
                'Approval'       => 0,
                'CreatedOn'      => $now,
            ]);

            if ($request->filled('Fotos')) {
                foreach ($request->Fotos as $base64) {
                    if (empty($base64)) continue;
                    $fileName = $this->saveImage($base64);
                    PengajuanCutiFoto::create([
                        'pengajuan_cuti_id' => $cuti->id,
                        'FileName'          => $fileName,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan cuti berhasil dikirim.',
                'data'    => $cuti->load('fotos'),
            ], 201);
        });
    }

    // GET /api/pengajuan-cuti/{id}
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $cuti = PengajuanCuti::with('fotos')
            ->where('id', $id)
            ->where('KodeKaryawan', $user->username)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->first();

        if (!$cuti) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $cuti,
        ]);
    }
}