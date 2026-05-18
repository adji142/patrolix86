<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengajuanIzin;
use App\Models\PengajuanIzinFoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PengajuanIzinController extends Controller
{
    private function saveImage(string $base64): string
    {
        $data = $base64;
        if (str_contains($data, ',')) {
            $data = substr($data, strpos($data, ',') + 1);
        }

        $fileName = 'izin_' . uniqid() . '.jpg';
        $dir = public_path('pengajuan-izin');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . '/' . $fileName, base64_decode($data));

        return $fileName;
    }

    // GET /api/pengajuan-izin — riwayat izin milik user yang login
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = PengajuanIzin::with('fotos')
            ->where('KodeKaryawan', $user->username)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->orderBy('id', 'desc');

        if ($request->filled('tahun') && $request->filled('bulan')) {
            $query->whereYear('TglIzinAwal', $request->tahun)
                  ->whereMonth('TglIzinAwal', $request->bulan);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
        ]);
    }

    // POST /api/pengajuan-izin — submit pengajuan baru
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'TglIzinAwal'    => 'required|date_format:Y-m-d',
            'TglIzinAkhir'   => 'required|date_format:Y-m-d|after_or_equal:TglIzinAwal',
            'KeteranganIzin' => 'required|string|max:1000',
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
            $izin = PengajuanIzin::create([
                'RecordOwnerID'  => $user->RecordOwnerID,
                'KodeKaryawan'   => $user->username,
                'TglIzinAwal'    => $request->TglIzinAwal,
                'TglIzinAkhir'   => $request->TglIzinAkhir,
                'TglPencatatan'  => now()->toDateString(),
                'KeteranganIzin' => $request->KeteranganIzin,
                'Approval'       => 0,
                'CreatedOn'      => $now,
            ]);

            if ($request->filled('Fotos')) {
                foreach ($request->Fotos as $base64) {
                    if (empty($base64)) continue;
                    $fileName = $this->saveImage($base64);
                    PengajuanIzinFoto::create([
                        'pengajuan_izin_id' => $izin->id,
                        'FileName'          => $fileName,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan izin berhasil dikirim.',
                'data'    => $izin->load('fotos'),
            ], 201);
        });
    }

    // GET /api/pengajuan-izin/{id}
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $izin = PengajuanIzin::with('fotos')
            ->where('id', $id)
            ->where('KodeKaryawan', $user->username)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->first();

        if (!$izin) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $izin,
        ]);
    }
}