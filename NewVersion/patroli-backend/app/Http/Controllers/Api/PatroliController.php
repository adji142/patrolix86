<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patroli;
use App\Models\Tcheckpoint;
use App\Models\Tsecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PatroliController extends Controller
{
    /**
     * POST /api/patroli
     *
     * Simpan record patroli dari mobile.
     * Body: KodeCheckPoint, Koordinat, Image (base64), Catatan?, Shift?, ShiftJadwal?
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'KodeCheckPoint'  => 'required|string|max:55',
            'Koordinat'       => 'required|string|max:100',
            'Image'           => 'required|string',
            'TanggalPatroli'  => 'required|date_format:Y-m-d H:i:s',
            'Catatan'         => 'nullable|string|max:255',
            'Shift'           => 'nullable|integer',
            'ShiftJadwal'     => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user          = $request->user();
        $nik           = $user->username;
        $recordOwnerID = $user->RecordOwnerID;

        $security   = Tsecurity::where('NIK', $nik)
            ->where('RecordOwnerID', $recordOwnerID)
            ->first();
        $locationID = $security?->LocationID ?? $user->AreaUser;

        // Validasi checkpoint milik lokasi user
        $checkpoint = Tcheckpoint::where('KodeCheckPoint', $request->KodeCheckPoint)
            ->where('LocationID', $locationID)
            ->where('RecordOwnerID', $recordOwnerID)
            ->first();

        if (!$checkpoint) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau bukan milik lokasi Anda.',
            ], 404);
        }

        // Decode dan simpan gambar sebagai file
        $base64Data = $request->Image;
        if (str_contains($base64Data, ',')) {
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
        }
        $imageFileName = 'mob_' . uniqid() . '.jpg';
        $dir           = public_path('patroli');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . '/' . $imageFileName, base64_decode($base64Data));

        $patroli = Patroli::create([
            'RecordOwnerID'  => $recordOwnerID,
            'KodeCheckPoint' => $request->KodeCheckPoint,
            'LocationID'     => $locationID,
            'TanggalPatroli' => $request->TanggalPatroli,
            'KodeKaryawan'   => $nik,
            'Koordinat'      => $request->Koordinat,
            'Image'          => $imageFileName,
            'Catatan'        => $request->Catatan,
            'Rank'           => 0,
            'Shift'          => $request->Shift ?? 0,
            'ShiftJadwal'    => $request->ShiftJadwal ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data patroli berhasil disimpan.',
            'data'    => [
                'id'             => $patroli->id,
                'NamaCheckPoint' => $checkpoint->NamaCheckPoint,
                'TanggalPatroli' => $patroli->TanggalPatroli,
                'ImageUrl'       => url('patroli/' . $imageFileName),
            ],
        ], 201);
    }
}