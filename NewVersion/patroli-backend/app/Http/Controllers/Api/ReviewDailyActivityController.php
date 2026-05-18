<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReviewDailyActivityController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tgl_awal'        => 'required|date',
            'tgl_akhir'       => 'required|date|after_or_equal:tgl_awal',
            'location_id'     => 'nullable|integer',
            'kode_karyawan'   => 'nullable|string|max:55',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $recordOwnerID = $request->user()->RecordOwnerID;

        $query = DB::table('dailyactivity as d')
            ->leftJoin('tlokasipatroli as l', function ($j) {
                $j->on('d.LocationID', '=', 'l.id')
                  ->on('d.RecordOwnerID', '=', 'l.RecordOwnerID');
            })
            ->select(
                'd.id',
                'd.Tanggal',
                'd.KodeKaryawan',
                'd.NamaKaryawan',
                'd.DeskripsiAktifitas',
                'd.Gambar1',
                'd.Gambar2',
                'd.Gambar3',
                'd.LocationID',
                'l.NamaArea as NamaLokasi'
            )
            ->where('d.RecordOwnerID', $recordOwnerID)
            ->where('d.Tanggal', '>=', $request->tgl_awal . ' 00:00:00')
            ->where('d.Tanggal', '<=', $request->tgl_akhir . ' 23:59:59');

        if ($request->filled('location_id')) {
            $query->where('d.LocationID', $request->location_id);
        }

        if ($request->filled('kode_karyawan')) {
            $query->where('d.KodeKaryawan', $request->kode_karyawan);
        }

        $results = $query->orderBy('Tanggal', 'desc')->get()->map(function ($item) {
            $item->Gambar1Url = $item->Gambar1 ? url('activity/' . $item->Gambar1) : null;
            $item->Gambar2Url = $item->Gambar2 ? url('activity/' . $item->Gambar2) : null;
            $item->Gambar3Url = $item->Gambar3 ? url('activity/' . $item->Gambar3) : null;
            return $item;
        });

        return response()->json([
            'success' => true,
            'data'    => $results,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Tanggal'            => 'required|date',
            'DeskripsiAktifitas' => 'required|string|max:255',
            'LocationID'         => 'required|integer',
            'KodeKaryawan'       => 'required|string|max:55',
            'NamaKaryawan'       => 'nullable|string|max:255',
            'Gambar1Base64'      => 'nullable|string',
            'Gambar2Base64'      => 'nullable|string',
            'Gambar3Base64'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $recordOwnerID = $request->user()->RecordOwnerID;
        $gambar1 = $this->saveImage($request->Gambar1Base64);
        $gambar2 = $this->saveImage($request->Gambar2Base64);
        $gambar3 = $this->saveImage($request->Gambar3Base64);

        $id = DB::table('dailyactivity')->insertGetId([
            'Tanggal'            => $request->Tanggal,
            'DeskripsiAktifitas' => $request->DeskripsiAktifitas,
            'LocationID'         => $request->LocationID,
            'KodeKaryawan'       => $request->KodeKaryawan,
            'NamaKaryawan'       => $request->NamaKaryawan,
            'Gambar1'            => $gambar1 ?? '',
            'Gambar2'            => $gambar2 ?? '',
            'Gambar3'            => $gambar3 ?? '',
            'RecordOwnerID'      => $recordOwnerID,
            'CreatedOn'          => now(),
            'UpdatedOn'          => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan.',
            'data'    => ['id' => $id],
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $recordOwnerID = $request->user()->RecordOwnerID;

        $record = DB::table('dailyactivity')
            ->where('id', $id)
            ->where('RecordOwnerID', $recordOwnerID)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'Tanggal'            => 'required|date',
            'DeskripsiAktifitas' => 'required|string|max:255',
            'LocationID'         => 'required|integer',
            'KodeKaryawan'       => 'required|string|max:55',
            'NamaKaryawan'       => 'nullable|string|max:255',
            'Gambar1Base64'      => 'nullable|string',
            'Gambar2Base64'      => 'nullable|string',
            'Gambar3Base64'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }
        $updateData = [
            'Tanggal'            => $request->Tanggal,
            'DeskripsiAktifitas' => $request->DeskripsiAktifitas,
            'LocationID'         => $request->LocationID,
            'KodeKaryawan'       => $request->KodeKaryawan,
            'NamaKaryawan'       => $request->NamaKaryawan,
            'UpdatedOn'          => now(),
        ];

        if ($request->filled('Gambar1Base64')) {
            $updateData['Gambar1'] = $this->saveImage($request->Gambar1Base64);
        }
        if ($request->filled('Gambar2Base64')) {
            $updateData['Gambar2'] = $this->saveImage($request->Gambar2Base64);
        }
        if ($request->filled('Gambar3Base64')) {
            $updateData['Gambar3'] = $this->saveImage($request->Gambar3Base64);
        }

        DB::table('dailyactivity')
            ->where('id', $id)
            ->where('RecordOwnerID', $recordOwnerID)
            ->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $recordOwnerID = $request->user()->RecordOwnerID;

        $record = DB::table('dailyactivity')
            ->where('id', $id)
            ->where('RecordOwnerID', $recordOwnerID)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        DB::table('dailyactivity')
            ->where('id', $id)
            ->where('RecordOwnerID', $recordOwnerID)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus.',
        ]);
    }

    private function saveImage(?string $base64): ?string
    {
        if (!$base64) return null;

        $data = $base64;
        if (str_contains($data, ',')) {
            $data = substr($data, strpos($data, ',') + 1);
        }

        $fileName = 'act_' . uniqid() . '.jpg';
        $dir      = public_path('activity');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . '/' . $fileName, base64_decode($data));

        return $fileName;
    }
}