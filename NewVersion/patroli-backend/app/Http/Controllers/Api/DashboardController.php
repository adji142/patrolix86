<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard/kpi?location_id=
     * KPI cards untuk hari ini, difilter berdasarkan lokasi (opsional).
     */
    public function kpi(Request $request): JsonResponse
    {
        $recordOwnerID = $request->user()->RecordOwnerID;
        $today         = Carbon::today()->toDateString();
        $locationId    = $request->filled('location_id') ? (int) $request->location_id : null;

        // ── 1. Total Security Aktif ───────────────────────────────────────────
        $securityQuery = DB::table('tsecurity')
            ->where('RecordOwnerID', $recordOwnerID)
            ->where('Status', 1);

        if ($locationId) {
            $securityQuery->where('LocationID', $locationId);
        }
        $totalSecurity = $securityQuery->count();

        // ── 2. Sudah Absen Masuk Hari Ini ────────────────────────────────────
        $absensiQuery = DB::table('absensi')
            ->where('RecordOwnerID', $recordOwnerID)
            ->where('Tanggal', $today)
            ->whereNotNull('Checkin')
            ->where('Checkin', '!=', '');

        if ($locationId) {
            $absensiQuery->where('LocationID', $locationId);
        }
        $sudahAbsen = $absensiQuery->count();

        // ── 3. Sudah Check-Out Hari Ini ───────────────────────────────────────
        $checkoutQuery = DB::table('absensi')
            ->where('RecordOwnerID', $recordOwnerID)
            ->where('Tanggal', $today)
            ->whereNotNull('CheckOut')
            ->where('CheckOut', '!=', '');

        if ($locationId) {
            $checkoutQuery->where('LocationID', $locationId);
        }
        $sudahCheckout = $checkoutQuery->count();

        // ── 4. Belum Absen Hari Ini ───────────────────────────────────────────
        // Security yang terjadwal masuk hari ini (shiftid != -1) tapi belum check-in
        $jadwalHariIniQuery = DB::table('jadwalkerja as jk')
            ->join('tsecurity as s', function ($j) use ($recordOwnerID) {
                $j->on('jk.KodeKaryawan', '=', 's.NIK')
                  ->on('jk.RecordOwnerID', '=', 's.RecordOwnerID');
            })
            ->where('jk.RecordOwnerID', $recordOwnerID)
            ->where('jk.Tanggal', $today)
            ->where('jk.shiftid', '!=', -1)
            ->where('s.Status', 1)
            ->whereNull('jk.StatusKehadiran')
            ->whereNotExists(function ($q) use ($recordOwnerID, $today) {
                $q->from('absensi as a')
                  ->whereColumn('a.KodeKaryawan', 'jk.KodeKaryawan')
                  ->where('a.RecordOwnerID', $recordOwnerID)
                  ->where('a.Tanggal', $today)
                  ->whereNotNull('a.Checkin')
                  ->where('a.Checkin', '!=', '');
            });

        if ($locationId) {
            $jadwalHariIniQuery->where('s.LocationID', $locationId);
        }
        $belumAbsen = $jadwalHariIniQuery->count();

        // ── 5. Patroli Berlangsung Hari Ini ───────────────────────────────────
        // Patroli yang dibuat hari ini (belum ada checkout / masih progress)
        $patroliQuery = DB::table('patroli')
            ->where('RecordOwnerID', $recordOwnerID)
            ->where('TanggalPatroli', $today);

        if ($locationId) {
            $patroliQuery->where('LocationID', $locationId);
        }
        $patroliBerlangsung = $patroliQuery->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_security'     => $totalSecurity,
                'sudah_absen'        => $sudahAbsen,
                'sudah_checkout'     => $sudahCheckout,
                'belum_absen'        => $belumAbsen,
                'patroli_berlangsung'=> $patroliBerlangsung,
                'tanggal'            => $today,
            ],
        ]);
    }
}
