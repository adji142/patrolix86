<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReviewBukuTamuController extends Controller
{
    public function index(Request $request)
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

        $query = DB::table('guestlog as g')
            ->leftJoin('tlokasipatroli as l', function ($j) {
                $j->on('g.LocationID', '=', 'l.id')
                  ->on('g.RecordOwnerID', '=', 'l.RecordOwnerID');
            })
            ->select(
                'g.id',
                'g.Tanggal',
                'g.TglMasuk',
                'g.TglKeluar',
                'g.NamaTamu',
                'g.NamaYangDicari',
                'g.Tujuan',
                'g.Keterangan',
                'g.ImageIn',
                'g.ImageOut',
                'g.LocationID',
                'l.NamaArea as NamaLokasi'
            )
            ->whereBetween('g.Tanggal', [$request->tgl_awal, $request->tgl_akhir])
            ->where('g.RecordOwnerID', $recordOwnerID);

        if ($request->filled('location_id')) {
            $query->where('g.LocationID', $request->location_id);
        }

        $results = $query->orderBy('g.TglMasuk', 'desc')->get()->map(function ($item) {
            $item->ImageInUrl  = $item->ImageIn ? url('guestlog/' . $item->ImageIn) : null;
            $item->ImageOutUrl = $item->ImageOut ? url('guestlog/' . $item->ImageOut) : null;
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
            'NamaTamu'       => 'required|string|max:255',
            'NamaYangDicari' => 'required|string|max:255',
            'Tujuan'         => 'required|string|max:255',
            'Tanggal'        => 'required|date',
            'TglMasuk'       => 'required|date',
            'TglKeluar'      => 'nullable|date',
            'LocationID'     => 'nullable|integer',
            'Keterangan'     => 'nullable|string|max:255',
            'ImageInBase64'  => 'nullable|string',
            'ImageOutBase64' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }
        $recordOwnerID = $request->user()->RecordOwnerID;

        $imageIn = $this->saveImage($request->ImageInBase64, 'in_');
        $imageOut = $this->saveImage($request->ImageOutBase64, 'out_');

        $id = DB::table('guestlog')->insertGetId([
            'Tanggal'        => $request->Tanggal,
            'TglMasuk'       => $request->TglMasuk,
            'TglKeluar'      => $request->TglKeluar ?: null,
            'NamaTamu'       => $request->NamaTamu,
            'NamaYangDicari' => $request->NamaYangDicari,
            'Tujuan'         => $request->Tujuan,
            'Keterangan'     => $request->Keterangan ?: null,
            'LocationID'     => $request->LocationID ?: null,
            'RecordOwnerID'  => $recordOwnerID,
            'ImageIn'        => $imageIn ?? '',
            'ImageOut'       => $imageOut ?? '',
            'CreatedAt'      => now(),
            'LastUpdatedAt'  => now(),
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

        $record = DB::table('guestlog')
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
            'NamaTamu'       => 'required|string|max:255',
            'NamaYangDicari' => 'required|string|max:255',
            'Tujuan'         => 'required|string|max:255',
            'Tanggal'        => 'required|date',
            'TglMasuk'       => 'required|date',
            'TglKeluar'      => 'nullable|date',
            'LocationID'     => 'nullable|integer',
            'Keterangan'     => 'nullable|string|max:255',
            'ImageInBase64'  => 'nullable|string',
            'ImageOutBase64' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }


        $updateData = [
            'Tanggal'        => $request->Tanggal,
            'TglMasuk'       => $request->TglMasuk,
            'TglKeluar'      => $request->TglKeluar ?: null,
            'NamaTamu'       => $request->NamaTamu,
            'NamaYangDicari' => $request->NamaYangDicari,
            'Tujuan'         => $request->Tujuan,
            'Keterangan'     => $request->Keterangan ?: null,
            'LocationID'     => $request->LocationID ?: null,
            'LastUpdatedAt'  => now(),
        ];

        if ($request->filled('ImageInBase64')) {
            $updateData['ImageIn'] = $this->saveImage($request->ImageInBase64, 'in_');
        }

        if ($request->filled('ImageOutBase64')) {
            $updateData['ImageOut'] = $this->saveImage($request->ImageOutBase64, 'out_');
        }

        DB::table('guestlog')
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

        $record = DB::table('guestlog')
            ->where('id', $id)
            ->where('RecordOwnerID', $recordOwnerID)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        DB::table('guestlog')
            ->where('id', $id)
            ->where('RecordOwnerID', $recordOwnerID)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus.',
        ]);
    }

    private function saveImage(?string $base64, string $prefix = ''): ?string
    {
        if (!$base64) return null;

        $data = $base64;
        if (str_contains($data, ',')) {
            $data = substr($data, strpos($data, ',') + 1);
        }

        $fileName = $prefix . uniqid() . '.jpg';
        $dir      = public_path('guestlog');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . '/' . $fileName, base64_decode($data));

        return $fileName;
    }
}