<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tsecurity;
use App\Models\User;
use App\Models\UserLokasi;
use App\Traits\FiltersByUserLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SecurityController extends Controller
{
    use FiltersByUserLocation;

    public function index(Request $request)
    {
        $recordOwnerID = $request->user()->RecordOwnerID;

        $query = Tsecurity::with('lokasi:id,NamaArea')
            ->where('RecordOwnerID', $recordOwnerID)
            ->select(['NIK', 'NamaSecurity', 'JoinDate', 'LocationID', 'Status', 'RecordOwnerID'])
            ->selectRaw('IF(Image IS NOT NULL AND Image != "", 1, 0) AS has_image');

        $allowedIds = $this->getAllowedLocationIds($request);
        if ($allowedIds !== null) {
            $query->whereIn('LocationID', $allowedIds);
        }

        if ($request->filled('LocationID')) {
            $query->where('LocationID', $request->LocationID);
        }

        if ($request->input('Status') !== null && $request->input('Status') !== '') {
            $query->where('Status', $request->Status);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
        ]);
    }

    public function store(Request $request)
    {
        $recordOwnerID = $request->user()->RecordOwnerID;

        $validator = Validator::make($request->all(), [
            'NIK'          => 'required|string|max:50',
            'NamaSecurity' => 'required|string|max:255',
            'JoinDate'     => 'required|date',
            'LocationID'   => 'required',
            'Status'       => 'required|in:0,1',
            'Image'        => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $exists = Tsecurity::where('NIK', $request->NIK)
            ->where('RecordOwnerID', $recordOwnerID)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'NIK sudah terdaftar di sistem.',
                'errors'  => ['NIK' => ['NIK sudah terdaftar.']],
            ], 422);
        }

        return DB::transaction(function () use ($request, $recordOwnerID) {
            $security = Tsecurity::create([
                'NIK'           => $request->NIK,
                'NamaSecurity'  => $request->NamaSecurity,
                'JoinDate'      => $request->JoinDate,
                'LocationID'    => $request->LocationID,
                'Status'        => $request->Status,
                'Image'         => $request->Image ?? '',
                'RecordOwnerID' => $recordOwnerID,
            ]);

            $this->syncUser($request->NIK, $request->NamaSecurity, $request->LocationID, $recordOwnerID);

            return response()->json([
                'success' => true,
                'message' => 'Data security berhasil disimpan.',
                'data'    => $security,
            ], 201);
        });
    }

    // Digunakan saat edit — mengembalikan data lengkap termasuk Image
    public function show(Request $request, $nik)
    {
        $security = Tsecurity::where('NIK', $nik)
            ->where('RecordOwnerID', $request->user()->RecordOwnerID)
            ->first();

        if (!$security) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $security,
        ]);
    }

    public function update(Request $request, $nik)
    {
        $security = Tsecurity::where('NIK', $nik)
            ->where('RecordOwnerID', $request->user()->RecordOwnerID)
            ->first();

        if (!$security) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'NamaSecurity' => 'required|string|max:255',
            'JoinDate'     => 'required|date',
            'LocationID'   => 'required',
            'Status'       => 'required|in:0,1',
            'Image'        => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        return DB::transaction(function () use ($request, $security, $nik) {
            $updateData = [
                'NamaSecurity' => $request->NamaSecurity,
                'JoinDate'     => $request->JoinDate,
                'LocationID'   => $request->LocationID,
                'Status'       => $request->Status,
            ];

            // Hanya timpa foto jika ada yang baru dikirim
            if ($request->filled('Image')) {
                $updateData['Image'] = $request->Image;
            }

            $security->update($updateData);

            $this->syncUser($nik, $request->NamaSecurity, $request->LocationID, $security->RecordOwnerID);

            return response()->json([
                'success' => true,
                'message' => 'Data security berhasil diperbarui.',
                'data'    => $security,
            ]);
        });
    }

    public function destroy(Request $request, $nik)
    {
        $security = Tsecurity::where('NIK', $nik)
            ->where('RecordOwnerID', $request->user()->RecordOwnerID)
            ->first();

        if (!$security) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $security->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data security berhasil dihapus.',
        ]);
    }

    // Buat user baru jika belum ada, atau update AreaUser jika sudah ada;
    // selalu sinkronisasi user_lokasi dengan lokasi terbaru
    private function syncUser(string $nik, string $nama, $locationID, string $recordOwnerID): void
    {
        $user = User::where('username', $nik)
            ->where('RecordOwnerID', $recordOwnerID)
            ->first();

        if (!$user) {
            $user = User::create([
                'username'        => $nik,
                'nama'            => $nama,
                'TempPassword'    => $nik,
                'LaravelPassword' => Hash::make($nik),
                'RecordOwnerID'   => $recordOwnerID,
                'AreaUser'        => $locationID,
                'HakAkses'        => 'security',
                'createdon'       => now(),
            ]);
        } else {
            $user->update(['AreaUser' => $locationID]);
        }

        // Sync user_lokasi — security hanya memiliki satu lokasi
        UserLokasi::where('user_id', $user->id)->delete();
        UserLokasi::create([
            'user_id'       => $user->id,
            'location_id'   => $locationID,
            'RecordOwnerID' => $recordOwnerID,
        ]);
    }
}