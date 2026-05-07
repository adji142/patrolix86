<?php

namespace App\Traits;

use App\Models\UserLokasi;
use Illuminate\Http\Request;

trait FiltersByUserLocation
{
    /**
     * Kembalikan daftar location_id yang boleh diakses user yang login.
     * Null berarti tidak ada batasan (user belum punya user_lokasi → tampilkan semua).
     *
     * @return int[]|null
     */
    protected function getAllowedLocationIds(Request $request): ?array
    {
        $ids = UserLokasi::where('user_id', $request->user()->id)
            ->pluck('location_id')
            ->toArray();

        return empty($ids) ? null : $ids;
    }
}