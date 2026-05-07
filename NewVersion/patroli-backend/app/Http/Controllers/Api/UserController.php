<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\UserLokasi;
use App\Traits\FiltersByUserLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use FiltersByUserLocation;

    public function index(Request $request)
    {
        $recordOwnerID = $request->user()->RecordOwnerID;

        $query = User::with([
            'userLokasi' => fn($q) => $q->with('lokasi:id,NamaArea'),
        ])->where('users.RecordOwnerID', $recordOwnerID);

        $allowedIds = $this->getAllowedLocationIds($request);
        if ($allowedIds !== null) {
            $query->whereHas('userLokasi', function ($q) use ($allowedIds) {
                $q->whereIn('location_id', $allowedIds);
            });
        }

        if ($request->filled('role_id')) {
            $query->join('userrole', 'userrole.userid', '=', 'users.id')
                  ->where('userrole.roleid', $request->role_id)
                  ->select('users.*');
        }

        $users = $query->get();

        // Batch-load roles for all returned users
        $roleMap = DB::table('userrole')
            ->join('roles', 'roles.id', '=', 'userrole.roleid')
            ->whereIn('userrole.userid', $users->pluck('id'))
            ->select('userrole.userid', 'roles.id as role_id', 'roles.rolename')
            ->get()
            ->keyBy('userid');

        $users = $users->map(function ($user) use ($roleMap) {
            $role = $roleMap->get($user->id);
            $user->role_id   = $role?->role_id;
            $user->rolename  = $role?->rolename;
            return $user;
        });

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }

    public function store(Request $request)
    {
        $recordOwnerID = $request->user()->RecordOwnerID;

        $validator = Validator::make($request->all(), [
            'username'     => 'required|string|max:100|unique:users,username',
            'nama'         => 'required|string|max:255',
            'password'     => 'required|string|min:4',
            'role_id'      => 'required|integer|exists:roles,id',
            'location_ids' => 'required|array|min:1',
            'location_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        return DB::transaction(function () use ($request, $recordOwnerID) {
            $role = Role::find($request->role_id);

            $user = User::create([
                'username'       => $request->username,
                'nama'           => $request->nama,
                'TempPassword'   => $request->password,
                'LaravelPassword' => Hash::make($request->password),
                'HakAkses'       => $role->rolename,
                'AreaUser'       => $request->location_ids[0],
                'RecordOwnerID'  => $recordOwnerID,
                'createdon'      => now(),
                'createdby'      => $request->user()->id,
            ]);

            DB::table('userrole')->insert([
                'userid' => $user->id,
                'roleid' => $request->role_id,
            ]);

            foreach ($request->location_ids as $locationId) {
                UserLokasi::create([
                    'user_id'       => $user->id,
                    'location_id'   => $locationId,
                    'RecordOwnerID' => $recordOwnerID,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'User berhasil disimpan.',
                'data'    => $user,
            ], 201);
        });
    }

    public function show(Request $request, $id)
    {
        $recordOwnerID = $request->user()->RecordOwnerID;

        $user = User::with([
            'userLokasi' => fn($q) => $q->with('lokasi:id,NamaArea'),
        ])->where('id', $id)->where('RecordOwnerID', $recordOwnerID)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $role = DB::table('userrole')
            ->join('roles', 'roles.id', '=', 'userrole.roleid')
            ->where('userrole.userid', $user->id)
            ->select('roles.id as role_id', 'roles.rolename')
            ->first();

        $user->role_id  = $role?->role_id;
        $user->rolename = $role?->rolename;

        return response()->json([
            'success' => true,
            'data'    => $user,
        ]);
    }

    public function update(Request $request, $id)
    {
        $recordOwnerID = $request->user()->RecordOwnerID;

        $user = User::where('id', $id)->where('RecordOwnerID', $recordOwnerID)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama'           => 'required|string|max:255',
            'password'       => 'nullable|string|min:4',
            'role_id'        => 'required|integer|exists:roles,id',
            'location_ids'   => 'required|array|min:1',
            'location_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        return DB::transaction(function () use ($request, $user, $recordOwnerID) {
            $role = Role::find($request->role_id);

            $updateData = [
                'username'  => $request->username,
                'nama'      => $request->nama,
                'AreaUser'  => $request->location_ids[0],
            ];

            if ($request->filled('password')) {
                $updateData['TempPassword']    = $request->password;
                $updateData['LaravelPassword'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Sync role
            DB::table('userrole')->where('userid', $user->id)->delete();
            DB::table('userrole')->insert(['userid' => $user->id, 'roleid' => $request->role_id]);

            // Sync locations
            UserLokasi::where('user_id', $user->id)->delete();
            foreach ($request->location_ids as $locationId) {
                UserLokasi::create([
                    'user_id'       => $user->id,
                    'location_id'   => $locationId,
                    'RecordOwnerID' => $recordOwnerID,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diperbarui.',
                'data'    => $user,
            ]);
        });
    }

    public function destroy(Request $request, $id)
    {
        $user = User::where('id', $id)
            ->where('RecordOwnerID', $request->user()->RecordOwnerID)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        DB::transaction(function () use ($user) {
            DB::table('userrole')->where('userid', $user->id)->delete();
            UserLokasi::where('user_id', $user->id)->delete();
            $user->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus.',
        ]);
    }

    public function roles()
    {
        $roles = Role::select('id', 'rolename')->get();
        return response()->json(['success' => true, 'data' => $roles]);
    }
}