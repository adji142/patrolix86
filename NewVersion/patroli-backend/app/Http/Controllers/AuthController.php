<?php

namespace App\Http\Controllers;

use App\Helpers\CIDecryption;
use App\Models\JadwalKerja;
use App\Models\Logdata;
use App\Models\Tcompany;
use App\Models\Tlokasipatroli;
use App\Models\Tsecurity;
use App\Models\Tshift;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as RequestFacade;

class AuthController extends Controller
{
    // -------------------------------------------------------------------------
    // POST /api/auth/login
    // Body: RecordOwnerID, username, password, LoginDate
    // -------------------------------------------------------------------------
    public function login(Request $request): JsonResponse
    {
        $RecordOwnerID = $request->input('RecordOwnerID');
        $username      = $request->input('username');
        $password      = $request->input('password');
        $loginDate     = $request->input('LoginDate', now()->toDateTimeString());

        // 1. Verifikasi Partner
        $partner = Tcompany::find($RecordOwnerID);
        if (!$partner) {
            return $this->failResponse(
                'Partner Tidak Ditemukan, Silahkan Hubungi Operator',
                $RecordOwnerID
            );
        }

        // 2. Cari user
        $user = User::where('username', $username)
                    ->where('RecordOwnerID', $RecordOwnerID)
                    ->first();
        if (!$user) {
            return $this->failResponse(
                'Username Tidak Ditemukan, Silahkan Hubungi Operator',
                $RecordOwnerID
            );
        }

        // 3. Cek masa langganan (EndSubs + ExtraDays harus > sekarang)
        $activeSubs = DB::selectOne("
            SELECT KodePartner FROM tcompany
            WHERE KodePartner = ?
              AND DATE_ADD(EndSubs, INTERVAL ExtraDays DAY) > NOW()
        ", [$RecordOwnerID]);

        if (!$activeSubs) {
            return $this->failResponse(
                'Langganan Telah Habis, Silahkan Melakukan Perpanjangan Langganan',
                $RecordOwnerID
            );
        }

        // 4. Verifikasi password
        if (!empty($user->LaravelPassword)) {
            // Sudah pernah login via Laravel → pakai LaravelPassword (bcrypt)
            if (!\Illuminate\Support\Facades\Hash::check($password, $user->LaravelPassword)) {
                return $this->failResponse('Password Tidak Sesuai, Coba Lagi', $RecordOwnerID);
            }
        } else {
            // Pertama kali login via Laravel → decrypt dari CI3, lalu migrate ke bcrypt
            $decryptedPwd = CIDecryption::decrypt($user->password, config('app.ci_encryption_key'));

            if ($decryptedPwd === false || $decryptedPwd !== $password) {
                return $this->failResponse('Password Tidak Sesuai, Coba Lagi', $RecordOwnerID);
            }

            // Simpan plain text & Laravel hash (one-time migration per user)
            $user->TempPassword    = $decryptedPwd;
            $user->LaravelPassword = \Illuminate\Support\Facades\Hash::make($password);
            $user->save();
        }
        // 5. Generate Sanctum token
        $token = $user->createToken('mobile')->plainTextToken;

        // 6. Tulis log
        $this->writeLog($RecordOwnerID, 'Login', json_encode([
            'success'  => true,
            'username' => $username,
        ]));

        return response()->json([
            'success'              => true,
            'message'              => '',
            'username'             => $user->username,
            'unique_id'            => $user->id,
            'RecordOwnerID'        => $user->RecordOwnerID,
            'NamaPartner'          => $partner->NamaPartner,
            'LocationID'           => $user->AreaUser,
            'NamaUser'             => $user->nama,
            'icon'                 => $partner->icon,
            'AllowFaceRecognition' => $partner->AllowFaceRecognition,
            'token'                => $token,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/auth/me  (requires: Authorization: Bearer <token>)
    // -------------------------------------------------------------------------
    public function me(Request $request): JsonResponse
    {
        $user    = $request->user();
        $partner = Tcompany::find($user->RecordOwnerID);

        $role = DB::table('userrole as ur')
                  ->join('roles as r', 'r.id', '=', 'ur.roleid')
                  ->where('ur.userid', $user->id)
                  ->select('r.id', 'r.rolename')
                  ->first();

        // Ambil semua permission berdasarkan role user
        $permissions = DB::table('userrole as a')
                         ->join('permissionrole as b', 'a.roleid', '=', 'b.roleid')
                         ->join('permission as c', 'b.permissionid', '=', 'c.id')
                         ->where('a.userid', $user->id)
                         ->where('c.separator', 0)
                         ->where('c.status', 1)
                         ->select(
                             'c.id',
                             'c.permissionname',
                             'c.LaravelLink as link',
                             'c.LaravelLogo as ico',
                             'c.menusubmenu',
                             DB::raw('CAST(c.multilevel  AS UNSIGNED) AS multilevel'),
                             DB::raw('CAST(c.AllowMobile AS UNSIGNED) AS AllowMobile'),
                             'c.MobileRoute',
                             'c.MobileLogo',
                             'c.order'
                         )
                         ->orderBy('c.order')
                         ->get();

        // Susun menu tree:
        // multilevel=1              → parent (punya children)
        // multilevel=0, menusubmenu → child dari parent
        // multilevel=0, no parent   → menu langsung di level pertama (standalone)
        $tree     = [];  // hasil akhir, indexed by id agar bisa di-lookup
        $children = [];  // child yang butuh di-attach ke parent

        foreach ($permissions as $p) {
            $item = [
                'id'          => $p->id,
                'name'        => $p->permissionname,
                'link'        => $p->link,
                'ico'         => $p->ico,
                'order'       => (int) $p->order,
                'AllowMobile' => (bool) $p->AllowMobile,
                'MobileRoute' => $p->MobileRoute,
                'MobileLogo'  => $p->MobileLogo,
            ];

            if ((int) $p->multilevel === 1) {
                $item['children']    = [];
                $tree[(int) $p->id] = $item;
            } elseif (!empty($p->menusubmenu) && (int) $p->menusubmenu > 0) {
                $children[] = ['parentId' => (int) $p->menusubmenu, 'item' => $item];
            } else {
                $tree[(int) $p->id] = $item;
            }
        }

        foreach ($children as $entry) {
            if (isset($tree[$entry['parentId']])) {
                $tree[$entry['parentId']]['children'][] = $entry['item'];
            }
        }

        // Sort level pertama berdasarkan order (numeric)
        $menus = array_values($tree);
        usort($menus, fn($a, $b) => $a['order'] <=> $b['order']);

        // Lokasi patroli user (single, dari AreaUser)
        $lokasiPatroli = Tlokasipatroli::where('id', $user->AreaUser)
                                        ->where('RecordOwnerID', $user->RecordOwnerID)
                                        ->first();

        // Semua lokasi yang dapat diakses user (dari tabel user_lokasi)
        $lokasiAkses = \App\Models\UserLokasi::with('lokasi')
                        ->where('user_id', $user->id)
                        ->where('RecordOwnerID', $user->RecordOwnerID)
                        ->get()
                        ->map(fn($ul) => $ul->lokasi)
                        ->filter()
                        ->values();

        return response()->json([
            'success'              => true,
            'unique_id'            => $user->id,
            'username'             => $user->username,
            'NamaUser'             => $user->nama,
            'email'                => $user->email,
            'phone'                => $user->phone,
            'RecordOwnerID'        => $user->RecordOwnerID,
            'LocationID'           => $user->AreaUser,
            'HakAkses'             => $user->HakAkses,
            'role'                 => $role,
            'NamaPartner'          => $partner?->NamaPartner,
            'icon'                 => $partner?->icon,
            'AllowFaceRecognition' => $partner?->AllowFaceRecognition,
            'AllowMobile'          => $partner?->AllowMobile,
            'menus'                => $menus,
            'lokasiPatroli'        => $lokasiPatroli,
            'lokasi_akses'         => $lokasiAkses,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /api/auth/logout  (requires: Authorization: Bearer <token>)
    // -------------------------------------------------------------------------
    // GET /api/auth/security  (requires: Authorization: Bearer <token>)
    // -------------------------------------------------------------------------
    public function security(Request $request): JsonResponse
    {
        $user = $request->user();
        $security = Tsecurity::where('NIK', $user->username)
                              ->where('RecordOwnerID', $user->RecordOwnerID)
                              ->first();

        return response()->json([
            'success'      => true,
            'security'     => $security,
            'FotoSecurity' => $security?->Image,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/auth/schedule  (requires: Authorization: Bearer <token>)
    // -------------------------------------------------------------------------
    public function schedule(Request $request): JsonResponse
    {
        $user = $request->user();
        $shifts = Tshift::where('LocationID', $user->AreaUser)
                        ->where('RecordOwnerID', $user->RecordOwnerID)
                        ->get();

        [$currentShift, $isGantiHari, $activeShiftDetails] = $this->getActiveShiftDetails($user, date('Y-m-d H:i:s'));

        return response()->json([
            'success'            => true,
            'Shift'              => $currentShift,
            'isGantiHari'        => $isGantiHari,
            'ActiveShiftDetails' => $activeShiftDetails,
            'JadwalShift'        => $shifts,
        ]);
    }

    // -------------------------------------------------------------------------
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Logout berhasil']);
    }

    // -------------------------------------------------------------------------
    // POST /api/auth/generate-laravel-password  (TEMPORARY — hapus setelah selesai migrasi)
    // -------------------------------------------------------------------------
    public function generateLaravelPassword(): JsonResponse
    {
        $users = DB::table('users')
                   ->whereNotNull('TempPassword')
                   ->where('TempPassword', '!=', '')
                   ->whereNull('LaravelPassword')
                   ->select('id', 'username', 'TempPassword')
                   ->get();

        if ($users->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Semua user sudah punya LaravelPassword, tidak ada yang perlu diproses.',
                'total'   => 0,
            ]);
        }

        $berhasil = 0;
        $gagal    = 0;

        foreach ($users as $user) {
            $updated = DB::table('users')
                         ->where('id', $user->id)
                         ->update([
                             'LaravelPassword' => \Illuminate\Support\Facades\Hash::make($user->TempPassword),
                         ]);

            $updated ? $berhasil++ : $gagal++;
        }

        return response()->json([
            'success'  => true,
            'message'  => "Selesai. Berhasil: {$berhasil}, Gagal: {$gagal} dari {$users->count()} user.",
            'total'    => $users->count(),
            'berhasil' => $berhasil,
            'gagal'    => $gagal,
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    private function getActiveShiftDetails($user, $loginDate = null): array
    {
        $loginTs = $loginDate ? strtotime($loginDate) : time();
        $dateObj = \Carbon\Carbon::createFromTimestamp($loginTs);
        
        $lokasi = Tlokasipatroli::find($user->AreaUser);
        $allowJadwalKerja = $lokasi->AllowJadwalKerja ?? 0;
        
        $activeShiftId = null;
        $isGantiHari = 0;
        $activeShiftModel = null;
        
        if ($allowJadwalKerja == 1) {
            // Cek jadwal hari kemarin untuk shift overlap (GantiHari)
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
                }
            }
            
            // Jika bukan shift kemarin yang overlap, cek jadwal hari ini
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
            
            if ($activeShiftModel) {
                $activeShiftId = $activeShiftModel->id;
                $isGantiHari = $activeShiftModel->GantiHari;
            }
        } else {
            // Realtime fallback logic (dari kode sebelumnya)
            $shifts = Tshift::where('LocationID', $user->AreaUser)
                            ->where('RecordOwnerID', $user->RecordOwnerID)
                            ->get();
            $defTime = strtotime('00:00:01');
            $jamNow  = strtotime($dateObj->format('H:i:s'));
            
            foreach ($shifts as $s) {
                if ((int) $s->GantiHari === 1) {
                    $selesai = strtotime($s->SelesaiBekerja);
                    if ($defTime < $jamNow && $jamNow < $selesai) {
                        $activeShiftId = $s->id;
                        $isGantiHari  = $s->GantiHari;
                        $activeShiftModel = $s;
                    } else {
                        $activeShiftId = $s->id;
                        $isGantiHari  = $s->GantiHari;
                        $activeShiftModel = $s;
                    }
                }
            }
        }
        
        $shiftDetails = null;
        if ($activeShiftModel) {
            $shiftDetails = [
                'NamaShift' => $activeShiftModel->NamaShift,
                'MulaiBekerja' => $activeShiftModel->MulaiBekerja,
                'SelesaiBekerja' => $activeShiftModel->SelesaiBekerja,
            ];
        }
        
        return [$activeShiftId, $isGantiHari, $shiftDetails];
    }

    private function failResponse(string $message, string $recordOwnerID): JsonResponse
    {
        $this->writeLog($recordOwnerID, 'Login', json_encode([
            'success' => false,
            'message' => $message,
        ]));

        return response()->json(['success' => false, 'message' => $message]);
    }

    private function writeLog(string $recordOwnerID, string $event, string $retValue): void
    {
        try {
            Logdata::create([
                'LogDate'       => now(),
                'Event'         => $event,
                'IPAddress'     => RequestFacade::ip() ?? '0.0.0.0',
                'RecordOwnerID' => $recordOwnerID,
                'retValue'      => $retValue,
            ]);
        } catch (\Throwable) {
            // log gagal tidak boleh merusak response utama
        }
    }
}
