<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReviewAbsensiController extends Controller
{
    public function summary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tgl_awal'    => 'required|date',
            'tgl_akhir'   => 'required|date|after_or_equal:tgl_awal',
            'location_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $recordOwnerID = $request->user()->RecordOwnerID;
        $tglAwal       = $request->tgl_awal;
        $tglAkhir      = $request->tgl_akhir;

        // Base: absensi — semua yang check-in, lalu pair dengan jadwal
        $query = DB::table('absensi as a')
            ->leftJoin('tsecurity as s', function ($j) {
                $j->on('a.KodeKaryawan', '=', 's.NIK')
                  ->on('a.RecordOwnerID', '=', 's.RecordOwnerID');
            })
            ->leftJoin('jadwalkerja as jk', function ($j) {
                $j->on('a.KodeKaryawan', '=', 'jk.KodeKaryawan')
                  ->on('a.Tanggal', '=', 'jk.Tanggal')
                  ->on('a.RecordOwnerID', '=', 'jk.RecordOwnerID');
            })
            ->leftJoin('tshift as ts', 'jk.shiftid', '=', 'ts.id')
            ->selectRaw("
                a.KodeKaryawan AS NIK,
                MAX(s.NamaSecurity) AS NamaSecurity,
                GROUP_CONCAT(DISTINCT ts.NamaShift ORDER BY ts.NamaShift SEPARATOR ', ') AS Jadwal,
                SUM(CASE WHEN a.Checkin IS NOT NULL AND a.Checkin != '' THEN 1 ELSE 0 END) AS Masuk,
                SUM(CASE WHEN jk.StatusKehadiran = 'OFF' THEN 1 ELSE 0 END) AS OFF,
                SUM(CASE WHEN jk.StatusKehadiran = 'IZIN' THEN 1 ELSE 0 END) AS IZIN,
                SUM(CASE WHEN jk.StatusKehadiran = 'CUTI' THEN 1 ELSE 0 END) AS CUTI,
                SUM(CASE WHEN jk.id IS NULL THEN 1 ELSE 0 END) AS TanpaJadwal
            ")
            ->whereBetween('a.Tanggal', [$tglAwal, $tglAkhir])
            ->where('a.RecordOwnerID', $recordOwnerID)
            ->groupBy('a.KodeKaryawan');

        if ($request->filled('location_id')) {
            $query->where('a.LocationID', $request->location_id);
        }

        $results = $query->orderBy('NIK')->get();

        return response()->json([
            'success' => true,
            'data'    => $results,
        ]);
    }

    public function detail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tgl_awal'    => 'required|date',
            'tgl_akhir'   => 'required|date|after_or_equal:tgl_awal',
            'location_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $recordOwnerID = $request->user()->RecordOwnerID;
        $tglAwal       = $request->tgl_awal;
        $tglAkhir      = $request->tgl_akhir;

        // Dari jadwalkerja: semua entri terjadwal + data absensi jika ada
        $fromJadwal = DB::table('jadwalkerja as jk')
            ->leftJoin('tsecurity as s', function ($j) {
                $j->on('jk.KodeKaryawan', '=', 's.NIK')
                  ->on('jk.RecordOwnerID', '=', 's.RecordOwnerID');
            })
            ->leftJoin('tshift as ts', 'jk.shiftid', '=', 'ts.id')
            ->leftJoin('absensi as a', function ($j) {
                $j->on('jk.KodeKaryawan', '=', 'a.KodeKaryawan')
                  ->on('jk.Tanggal', '=', 'a.Tanggal')
                  ->on('jk.RecordOwnerID', '=', 'a.RecordOwnerID');
            })
            ->leftJoin('tlokasipatroli as l', function ($j) {
                $j->on('a.LocationID', '=', 'l.id')
                  ->on('a.RecordOwnerID', '=', 'l.RecordOwnerID');
            })
            ->selectRaw("
                a.id AS AbsensiID,
                jk.Tanggal,
                jk.KodeKaryawan AS NIK,
                s.NamaSecurity,
                ts.NamaShift AS Jadwal,
                a.Checkin AS JamCheckin,
                a.CheckOut AS JamCheckout,
                l.NamaArea AS NamaLokasi,
                CASE WHEN a.Checkin IS NOT NULL AND a.Checkin != '' THEN 1 ELSE 0 END AS Masuk,
                CASE WHEN jk.StatusKehadiran = 'OFF' THEN 1 ELSE 0 END AS IsOFF,
                CASE WHEN jk.StatusKehadiran = 'IZIN' THEN jk.KeteranganStatusKehadiran ELSE NULL END AS KeteranganIZIN,
                CASE WHEN jk.StatusKehadiran = 'CUTI' THEN jk.KeteranganStatusKehadiran ELSE NULL END AS KeteranganCUTI
            ")
            ->whereBetween('jk.Tanggal', [$tglAwal, $tglAkhir])
            ->where('jk.RecordOwnerID', $recordOwnerID);

        // Dari absensi tanpa jadwalkerja
        $fromAbsensi = DB::table('absensi as a')
            ->leftJoin('tsecurity as s', function ($j) {
                $j->on('a.KodeKaryawan', '=', 's.NIK')
                  ->on('a.RecordOwnerID', '=', 's.RecordOwnerID');
            })
            ->leftJoin('tlokasipatroli as l', function ($j) {
                $j->on('a.LocationID', '=', 'l.id')
                  ->on('a.RecordOwnerID', '=', 'l.RecordOwnerID');
            })
            ->selectRaw("
                a.id AS AbsensiID,
                a.Tanggal,
                a.KodeKaryawan AS NIK,
                s.NamaSecurity,
                NULL AS Jadwal,
                a.Checkin AS JamCheckin,
                a.CheckOut AS JamCheckout,
                l.NamaArea AS NamaLokasi,
                CASE WHEN a.Checkin IS NOT NULL AND a.Checkin != '' THEN 1 ELSE 0 END AS Masuk,
                0 AS IsOFF,
                NULL AS KeteranganIZIN,
                NULL AS KeteranganCUTI
            ")
            ->whereBetween('a.Tanggal', [$tglAwal, $tglAkhir])
            ->where('a.RecordOwnerID', $recordOwnerID)
            ->whereNotExists(function ($q) use ($recordOwnerID) {
                $q->from('jadwalkerja as jk2')
                  ->whereColumn('jk2.KodeKaryawan', 'a.KodeKaryawan')
                  ->whereColumn('jk2.Tanggal', 'a.Tanggal')
                  ->where('jk2.RecordOwnerID', $recordOwnerID);
            });

        if ($request->filled('location_id')) {
            $fromJadwal->where('s.LocationID', $request->location_id);
            $fromAbsensi->where('a.LocationID', $request->location_id);
        }

        $results = $fromJadwal->unionAll($fromAbsensi)
            ->orderBy('Tanggal')
            ->orderBy('NIK')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $results,
        ]);
    }

    public function showRecord(Request $request, $id)
    {
        $recordOwnerID = $request->user()->RecordOwnerID;

        $absensi = DB::table('absensi as a')
            ->leftJoin('tsecurity as s', function ($j) {
                $j->on('a.KodeKaryawan', '=', 's.NIK')
                  ->on('a.RecordOwnerID', '=', 's.RecordOwnerID');
            })
            ->leftJoin('tlokasipatroli as l', function ($j) {
                $j->on('a.LocationID', '=', 'l.id')
                  ->on('a.RecordOwnerID', '=', 'l.RecordOwnerID');
            })
            ->leftJoin('jadwalkerja as jk', function ($j) {
                $j->on('a.KodeKaryawan', '=', 'jk.KodeKaryawan')
                  ->on('a.Tanggal', '=', 'jk.Tanggal')
                  ->on('a.RecordOwnerID', '=', 'jk.RecordOwnerID');
            })
            ->leftJoin('tshift as ts', 'jk.shiftid', '=', 'ts.id')
            ->select(
                'a.id',
                'a.Tanggal',
                'a.KodeKaryawan as NIK',
                's.NamaSecurity',
                'a.Checkin as JamCheckin',
                'a.CheckOut as JamCheckout',
                'a.KoordinatIN',
                'a.ImageIN',
                'ts.NamaShift as Jadwal',
                'l.NamaArea as NamaLokasi',
                'l.Latitude as LokasiLatitude',
                'l.Longitude as LokasiLongitude'
            )
            ->where('a.id', $id)
            ->where('a.RecordOwnerID', $recordOwnerID)
            ->first();

        if (!$absensi) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $imageBaseUrl   = config('app.legacy_asset_url', 'http://localhost/patrolix86');
        $absensi->ImageUrl = $absensi->ImageIN
            ? $imageBaseUrl . '/Assets/images/Absensi/' . $absensi->ImageIN
            : null;

        $absensi->JarakMeter = null;
        if ($absensi->KoordinatIN && $absensi->LokasiLatitude && $absensi->LokasiLongitude) {
            $parts = explode(',', $absensi->KoordinatIN);
            if (count($parts) >= 2) {
                $checkinLat = (float) trim($parts[0]);
                $checkinLng = (float) trim($parts[1]);
                $absensi->JarakMeter = $this->haversineDistance(
                    (float) $absensi->LokasiLatitude,
                    (float) $absensi->LokasiLongitude,
                    $checkinLat,
                    $checkinLng
                );
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $absensi,
        ]);
    }

    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R    = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return round($R * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
    }
}