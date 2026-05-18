<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tsecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PatroliProgressController extends Controller
{
    /**
     * Ambil NIK, RecordOwnerID, dan LocationID dari user yang sedang login.
     * LocationID diambil dari tsecurity agar konsisten dengan data patroli.
     */
    private function getSecurityContext(Request $request): array
    {
        $user          = $request->user();
        $nik           = $user->username;
        $recordOwnerID = $user->RecordOwnerID;

        $security   = Tsecurity::where('NIK', $nik)
            ->where('RecordOwnerID', $recordOwnerID)
            ->first();

        $locationID = $security?->LocationID ?? $user->AreaUser;

        return compact('nik', 'recordOwnerID', 'locationID');
    }

    /**
     * GET /patroli/progress
     *
     * Progress pos terlewati hari ini untuk security yang login:
     *   - total_checkpoint : total pos di lokasi
     *   - terlewati        : pos unik yang sudah dipatroli hari ini
     *   - persentase       : (terlewati / total) * 100
     */
    public function progress(Request $request)
    {
        ['nik' => $nik, 'recordOwnerID' => $recordOwnerID, 'locationID' => $locationID]
            = $this->getSecurityContext($request);

        $totalCheckpoint = DB::table('tcheckpoint')
            ->where('LocationID', $locationID)
            ->where('RecordOwnerID', $recordOwnerID)
            ->count();

        $today = now()->format('Y-m-d');

        $terlewati = DB::table('patroli')
            ->where('KodeKaryawan', $nik)
            ->where('RecordOwnerID', $recordOwnerID)
            ->where('LocationID', $locationID)
            ->whereDate('TanggalPatroli', $today)
            ->distinct()
            ->count('KodeCheckPoint');

        $persentase = $totalCheckpoint > 0
            ? round(($terlewati / $totalCheckpoint) * 100, 1)
            : 0.0;

        return response()->json([
            'success' => true,
            'data'    => [
                'total_checkpoint' => $totalCheckpoint,
                'terlewati'        => $terlewati,
                'persentase'       => $persentase,
                'tanggal'          => $today,
            ],
        ]);
    }

    /**
     * GET /patroli/history?tanggal=YYYY-MM-DD
     *
     * History patroli untuk security yang login.
     * Default tanggal = hari ini.
     * Auto-scope: hanya data milik security (NIK), lokasi, dan partner yang sama.
     */
    public function history(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        ['nik' => $nik, 'recordOwnerID' => $recordOwnerID, 'locationID' => $locationID]
            = $this->getSecurityContext($request);

        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));

        $records = DB::table('patroli as p')
            ->leftJoin('tcheckpoint as cp', function ($join) {
                $join->on('p.KodeCheckPoint', '=', 'cp.KodeCheckPoint')
                     ->on('p.RecordOwnerID', '=', 'cp.RecordOwnerID')
                     ->on('p.LocationID', '=', 'cp.LocationID');
            })
            ->select(
                'p.id',
                'p.KodeCheckPoint',
                'cp.NamaCheckPoint',
                DB::raw("DATE_FORMAT(p.TanggalPatroli, '%H:%i') as Jam"),
                DB::raw("DATE_FORMAT(p.TanggalPatroli, '%d/%m/%Y %H:%i') as TanggalJam"),
                'p.Koordinat',
                'p.Catatan'
            )
            ->where('p.KodeKaryawan', $nik)
            ->where('p.RecordOwnerID', $recordOwnerID)
            ->where('p.LocationID', $locationID)
            ->whereDate('p.TanggalPatroli', $tanggal)
            ->orderBy('p.TanggalPatroli')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $records,
            'tanggal' => $tanggal,
        ]);
    }
}