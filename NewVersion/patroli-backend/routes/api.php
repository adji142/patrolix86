<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\CheckpointController;
use App\Http\Controllers\Api\JadwalKerjaController;
use App\Http\Controllers\Api\LokasiPatroliController;
use App\Http\Controllers\Api\ReviewAbsensiController;
use App\Http\Controllers\Api\ReviewPatroliController;
use App\Http\Controllers\Api\SecurityController;
use App\Http\Controllers\Api\TShiftController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    // TEMPORARY — hapus setelah migrasi password selesai
    // Route::post('generate-laravel-password', [AuthController::class, 'generateLaravelPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('lokasi-patroli', LokasiPatroliController::class);
    Route::apiResource('master-security', SecurityController::class)
        ->parameters(['master-security' => 'nik']);
    Route::apiResource('titik-patroli', CheckpointController::class)
        ->parameters(['titik-patroli' => 'kode']);
    Route::get('tshift', [TShiftController::class, 'index']);
    Route::get('jadwal-kerja', [JadwalKerjaController::class, 'index']);
    Route::post('jadwal-kerja', [JadwalKerjaController::class, 'store']);
    Route::delete('jadwal-kerja/{id}', [JadwalKerjaController::class, 'destroy']);
    Route::get('roles', [UserController::class, 'roles']);
    Route::apiResource('master-user', UserController::class);

    Route::prefix('review-patroli')->group(function () {
        Route::get('summary', [ReviewPatroliController::class, 'summary']);
        Route::get('detail', [ReviewPatroliController::class, 'detail']);
        Route::get('detail/{id}', [ReviewPatroliController::class, 'showRecord']);
    });

    Route::prefix('review-absensi')->group(function () {
        Route::get('summary', [ReviewAbsensiController::class, 'summary']);
        Route::get('detail', [ReviewAbsensiController::class, 'detail']);
        Route::get('record/{id}', [ReviewAbsensiController::class, 'showRecord']);
    });
});
