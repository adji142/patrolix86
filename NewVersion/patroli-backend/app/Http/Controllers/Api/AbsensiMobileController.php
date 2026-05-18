<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\PengajuanIzin;
use App\Models\PengajuanCuti;
use App\Models\JadwalKerja;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AbsensiMobileController extends Controller
{
    private function saveImage(?string $base64): ?string
    {
        if (!$base64) return null;

        $data = $base64;
        if (str_contains($data, ',')) {
            $data = substr($data, strpos($data, ',') + 1);
        }

        $fileName = 'mob_' . uniqid() . '.jpg';
        $dir      = public_path('absensi');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . '/' . $fileName, base64_decode($data));

        return $fileName;
    }

    // GET /api/absensi/today
    public function today(Request $request): JsonResponse
    {
        $user  = $request->user();
        $today = now()->toDateString();

        $record = Absensi::where('KodeKaryawan', $user->username)
                         ->where('RecordOwnerID', $user->RecordOwnerID)
                         ->where('Tanggal', $today)
                         ->first();

        return response()->json([
            'success' => true,
            'data'    => $record ? [$record] : [],
        ]);
    }

    // POST /api/absensi/checkin
    public function checkin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'LocationID'  => 'required|integer',
            'Tanggal'     => 'required|date_format:Y-m-d',
            'Checkin'     => 'required|date_format:Y-m-d H:i:s',
            'KoordinatIN' => 'nullable|string',
            'ImageIN'     => 'nullable|string',
            'Shift'       => 'nullable|string',
            'TresholdIn'  => 'nullable|string',
            'AppVersion'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user  = $request->user();

        // Validasi Face Recognition
        $partner = \App\Models\Tcompany::find($user->RecordOwnerID);
        if ($partner && $partner->AllowFaceRecognition == 1) {
            if (empty($request->ImageIN)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Absensi ditolak. Foto absensi (wajah) wajib diisi karena verifikasi wajah diaktifkan.',
                ], 422);
            }
        }

        $tanggal = $request->Tanggal;

        $existing = Absensi::where('KodeKaryawan', $user->username)
                            ->where('RecordOwnerID', $user->RecordOwnerID)
                            ->where('Tanggal', $tanggal)
                            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah check-in hari ini.',
            ], 422);
        }

        $lokasi = \App\Models\Tlokasipatroli::find($request->LocationID);
        if ($lokasi && ($lokasi->AllowJadwalKerja ?? 0) == 1) {
            $dateObj = \Carbon\Carbon::parse($request->Checkin);
            
            $activeShiftModel = null;
            $isYesterdayShift = false;
            
            $yesterday = $dateObj->copy()->subDay()->format('Y-m-d');
            $jadwalYesterday = \App\Models\JadwalKerja::with('shift')
                ->where('KodeKaryawan', $user->username)
                ->where('RecordOwnerID', $user->RecordOwnerID)
                ->where('Tanggal', $yesterday)->first();
                
            if ($jadwalYesterday && $jadwalYesterday->shift && $jadwalYesterday->shift->GantiHari == 1) {
                $selesai = strtotime($jadwalYesterday->shift->SelesaiBekerja);
                $jamNow = strtotime($dateObj->format('H:i:s'));
                if ($jamNow <= $selesai) {
                    $activeShiftModel = $jadwalYesterday->shift;
                    $isYesterdayShift = true;
                }
            }
            
            if (!$activeShiftModel) {
                $today = $dateObj->format('Y-m-d');
                $jadwalToday = \App\Models\JadwalKerja::with('shift')
                    ->where('KodeKaryawan', $user->username)
                    ->where('RecordOwnerID', $user->RecordOwnerID)
                    ->where('Tanggal', $today)->first();
                if ($jadwalToday && $jadwalToday->shiftid != -1 && $jadwalToday->shift) {
                    $activeShiftModel = $jadwalToday->shift;
                }
            }
            
            if (!$activeShiftModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal belum dibuat.',
                ], 422);
            }
            
            if ($isYesterdayShift) {
                $tanggal = $yesterday;
            }
        }

        $imageIN = $this->saveImage($request->ImageIN);
        $now     = now()->toDateTimeString();
        $absensi = Absensi::create([
            'RecordOwnerID' => $user->RecordOwnerID,
            'LocationID'    => $request->LocationID,
            'KodeKaryawan'  => $user->username,
            'KoordinatIN'   => $request->KoordinatIN ?? '',
            'ImageIN'       => $imageIN ?? '',
            'Tanggal'       => $tanggal,
            'Shift'         => $request->Shift ?? '',
            'Checkin'       => $request->Checkin,
            'TresholdIn'    => $request->TresholdIn ?? '0',
            'AppVersion'    => $request->AppVersion ?? '',
            'CreatedOn'     => $now
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check in berhasil.',
            'data'    => $absensi,
        ], 201);
    }

    // POST /api/absensi/checkout
    public function checkout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'CheckOut'     => 'required|date_format:Y-m-d H:i:s',
            'KoordinatOUT' => 'nullable|string',
            'ImageOUT'     => 'nullable|string',
            'TresholdOut'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user  = $request->user();

        // Validasi Face Recognition
        $partner = \App\Models\Tcompany::find($user->RecordOwnerID);
        if ($partner && $partner->AllowFaceRecognition == 1) {
            if (empty($request->ImageOUT)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Absensi ditolak. Foto absensi (wajah) wajib diisi karena verifikasi wajah diaktifkan.',
                ], 422);
            }
        }

        $today = now()->toDateString();

        $absensi = Absensi::where('KodeKaryawan', $user->username)
                           ->where('RecordOwnerID', $user->RecordOwnerID)
                           ->where('Tanggal', $today)
                           ->first();

        if (!$absensi) {
            return response()->json([
                'success' => false,
                'message' => 'Data absensi tidak ditemukan. Lakukan check-in terlebih dahulu.',
            ], 404);
        }

        $imageOUT = $this->saveImage($request->ImageOUT);
        $absensi->update([
            'KoordinatOUT' => $request->KoordinatOUT ?? '',
            'ImageOUT'     => $imageOUT ?? '',
            'CheckOut'     => $request->CheckOut,
            'TresholdOut'  => $request->TresholdOut ?? '0',
            'UpdatedOn'    => now()->toDateTimeString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check out berhasil.',
            'data'    => $absensi->fresh(),
        ]);
    }

    // GET /api/absensi/monthly-stats
    public function monthlyStats(Request $request): JsonResponse
    {
        $user       = $request->user();
        $now        = Carbon::now();
        $bulan      = (int) $now->month;
        $tahun      = (int) $now->year;
        $startDate  = Carbon::create($tahun, $bulan, 1)->toDateString();
        $endDate    = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();

        // Jumlah hari absensi masuk bulan ini
        $jumlahAbsensi = Absensi::where('KodeKaryawan', $user->username)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->whereBetween('Tanggal', [$startDate, $endDate])
            ->count();

        // Jumlah pengajuan izin yang disetujui (Approval = 1) dengan rentang tanggal izin overlap bulan ini
        $jumlahIzin = PengajuanIzin::where('KodeKaryawan', $user->username)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->where('Approval', 1)
            ->where('TglIzinAwal', '<=', $endDate)
            ->where('TglIzinAkhir', '>=', $startDate)
            ->count();

        // Jumlah pengajuan cuti yang disetujui (Approval = 1) dengan rentang tanggal cuti overlap bulan ini
        $jumlahCuti = PengajuanCuti::where('KodeKaryawan', $user->username)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->where('Approval', 1)
            ->where('TglCutiAwal', '<=', $endDate)
            ->where('TglCutiAkhir', '>=', $startDate)
            ->count();

        // Jumlah hari jadwal kerja (shiftid != -1 berarti hari kerja, bukan libur)
        $totalHariKerja = JadwalKerja::where('KodeKaryawan', $user->username)
            ->where('RecordOwnerID', $user->RecordOwnerID)
            ->where('Bulan', $bulan)
            ->where('Tahun', $tahun)
            ->where('shiftid', '!=', -1)
            ->count();

        // Tidak absen = jadwal kerja - (absensi + izin + cuti), minimal 0
        $jumlahOff = max(0, $totalHariKerja - $jumlahAbsensi - $jumlahIzin - $jumlahCuti);

        return response()->json([
            'success' => true,
            'data'    => [
                'bulan'          => $bulan,
                'tahun'          => $tahun,
                'jumlah_absensi' => $jumlahAbsensi,
                'jumlah_izin'    => $jumlahIzin,
                'jumlah_cuti'    => $jumlahCuti,
                'jumlah_off'     => $jumlahOff,
                'total_hari_kerja' => $totalHariKerja,
            ],
        ]);
    }
}