<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReviewPatroliController extends Controller
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

        $query = DB::table('patroli as p')
            ->leftJoin('tsecurity as s', function ($join) {
                $join->on('p.KodeKaryawan', '=', 's.NIK')
                     ->on('p.RecordOwnerID', '=', 's.RecordOwnerID');
            })
            ->select(
                DB::raw("DATE(p.TanggalPatroli) as Tanggal"),
                'p.KodeKaryawan as NIK',
                's.NamaSecurity',
                DB::raw("COUNT(p.id) as JumlahPatroli")
            )
            ->whereRaw("DATE(p.TanggalPatroli) BETWEEN ? AND ?", [
                $request->tgl_awal,
                $request->tgl_akhir,
            ])
            ->where('p.RecordOwnerID', $recordOwnerID)
            ->groupBy(DB::raw("DATE(p.TanggalPatroli)"), 'p.KodeKaryawan', 's.NamaSecurity')
            ->orderByRaw("DATE(p.TanggalPatroli)")
            ->orderBy('p.KodeKaryawan');

        if ($request->filled('location_id')) {
            $query->where('p.LocationID', $request->location_id);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
        ]);
    }

    public function detail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tgl_awal'       => 'required|date',
            'tgl_akhir'      => 'required|date|after_or_equal:tgl_awal',
            'location_id'    => 'nullable|integer',
            'kode_karyawan'  => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $recordOwnerID = $request->user()->RecordOwnerID;

        $query = DB::table('patroli as p')
            ->leftJoin('tsecurity as s', function ($join) {
                $join->on('p.KodeKaryawan', '=', 's.NIK')
                     ->on('p.RecordOwnerID', '=', 's.RecordOwnerID');
            })
            ->leftJoin('tcheckpoint as cp', function ($join) {
                $join->on('p.KodeCheckPoint', '=', 'cp.KodeCheckPoint')
                     ->on('p.RecordOwnerID', '=', 'cp.RecordOwnerID')
                     ->on('p.LocationID', '=', 'cp.LocationID');
            })
            ->leftJoin('tlokasipatroli as l', function ($join) {
                $join->on('p.LocationID', '=', 'l.id')
                     ->on('p.RecordOwnerID', '=', 'l.RecordOwnerID');
            })
            ->select(
                'p.id',
                DB::raw("DATE_FORMAT(p.TanggalPatroli, '%d/%m/%Y %H:%i:%S') as TanggalJam"),
                DB::raw("DATE_FORMAT(p.TanggalPatroli, '%Y-%m-%d') as TanggalSort"),
                'p.KodeKaryawan as NIK',
                's.NamaSecurity',
                'cp.NamaCheckPoint',
                'p.Koordinat',
                'p.Image',
                'p.Catatan',
                'l.NamaArea as NamaLokasi'
            )
            ->whereRaw("DATE(p.TanggalPatroli) BETWEEN ? AND ?", [
                $request->tgl_awal,
                $request->tgl_akhir,
            ])
            ->where('p.RecordOwnerID', $recordOwnerID)
            ->orderBy('p.TanggalPatroli');

        if ($request->filled('location_id')) {
            $query->where('p.LocationID', $request->location_id);
        }

        if ($request->filled('kode_karyawan')) {
            $query->where('p.KodeKaryawan', $request->kode_karyawan);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
        ]);
    }

    public function showRecord(Request $request, $id)
    {
        $recordOwnerID = $request->user()->RecordOwnerID;

        $patroli = DB::table('patroli as p')
            ->leftJoin('tsecurity as s', function ($join) {
                $join->on('p.KodeKaryawan', '=', 's.NIK')
                     ->on('p.RecordOwnerID', '=', 's.RecordOwnerID');
            })
            ->leftJoin('tcheckpoint as cp', function ($join) {
                $join->on('p.KodeCheckPoint', '=', 'cp.KodeCheckPoint')
                     ->on('p.RecordOwnerID', '=', 'cp.RecordOwnerID')
                     ->on('p.LocationID', '=', 'cp.LocationID');
            })
            ->leftJoin('tlokasipatroli as l', function ($join) {
                $join->on('p.LocationID', '=', 'l.id')
                     ->on('p.RecordOwnerID', '=', 'l.RecordOwnerID');
            })
            ->select(
                'p.id',
                DB::raw("DATE_FORMAT(p.TanggalPatroli, '%d/%m/%Y %H:%i:%S') as TanggalJam"),
                'p.KodeKaryawan as NIK',
                's.NamaSecurity',
                'cp.NamaCheckPoint',
                'p.Koordinat',
                'p.Image',
                'p.Catatan',
                'l.NamaArea as NamaLokasi',
                'l.Latitude as LokasiLatitude',
                'l.Longitude as LokasiLongitude'
            )
            ->where('p.id', $id)
            ->where('p.RecordOwnerID', $recordOwnerID)
            ->first();

        if (!$patroli) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $imageBaseUrl = config('app.legacy_asset_url', 'http://localhost/patrolix86');
        $patroli->ImageUrl = $patroli->Image
            ? $imageBaseUrl . '/Assets/images/patroli/' . $patroli->Image
            : null;

        return response()->json([
            'success' => true,
            'data'    => $patroli,
        ]);
    }
}