<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\AbsensiMobileController;
use App\Http\Controllers\Api\CheckpointController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\JadwalKerjaController;
use App\Http\Controllers\Api\LokasiPatroliController;
use App\Http\Controllers\Api\ReviewAbsensiController;
use App\Http\Controllers\Api\ReviewBukuTamuController;
use App\Http\Controllers\Api\ReviewDailyActivityController;
use App\Http\Controllers\Api\PatroliController;
use App\Http\Controllers\Api\PatroliProgressController;
use App\Http\Controllers\Api\ReviewPatroliController;
use App\Http\Controllers\Api\SecurityController;
use App\Http\Controllers\Api\TShiftController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PengajuanIzinController;
use App\Http\Controllers\Api\ReviewPengajuanIzinController;
use App\Http\Controllers\Api\PengajuanCutiController;
use App\Http\Controllers\Api\ReviewPengajuanCutiController;
use App\Http\Controllers\Api\TukarJadwalController;
use App\Http\Controllers\Api\ReviewTukarJadwalController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    // TEMPORARY — hapus setelah migrasi password selesai
    // Route::post('generate-laravel-password', [AuthController::class, 'generateLaravelPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('security', [AuthController::class, 'security']);
        Route::get('schedule', [AuthController::class, 'schedule']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('lokasi-patroli', LokasiPatroliController::class);

    // Dashboard
    Route::get('dashboard/kpi', [DashboardController::class, 'kpi']);

    Route::apiResource('master-security', SecurityController::class)
        ->parameters(['master-security' => 'nik']);
    Route::apiResource('titik-patroli', CheckpointController::class)
        ->parameters(['titik-patroli' => 'kode']);
    Route::apiResource('tshift', TShiftController::class);
    Route::get('jadwal-kerja', [JadwalKerjaController::class, 'index']);
    Route::post('jadwal-kerja', [JadwalKerjaController::class, 'store']);
    Route::delete('jadwal-kerja/{id}', [JadwalKerjaController::class, 'destroy']);
    Route::get('roles', [UserController::class, 'roles']);
    Route::apiResource('master-user', UserController::class);

    Route::prefix('patroli')->group(function () {
        Route::post('/', [PatroliController::class, 'store']);
        Route::get('progress', [PatroliProgressController::class, 'progress']);
        Route::get('history', [PatroliProgressController::class, 'history']);
    });

    Route::prefix('review-patroli')->group(function () {
        Route::get('summary', [ReviewPatroliController::class, 'summary']);
        Route::get('detail', [ReviewPatroliController::class, 'detail']);
        Route::get('detail/{id}', [ReviewPatroliController::class, 'showRecord']);
    });

    Route::get('review-daily-activity', [ReviewDailyActivityController::class, 'index']);
    Route::post('review-daily-activity', [ReviewDailyActivityController::class, 'store']);
    Route::put('review-daily-activity/{id}', [ReviewDailyActivityController::class, 'update']);
    Route::delete('review-daily-activity/{id}', [ReviewDailyActivityController::class, 'destroy']);

    Route::get('review-bukutamu', [ReviewBukuTamuController::class, 'index']);
    Route::post('review-bukutamu', [ReviewBukuTamuController::class, 'store']);
    Route::put('review-bukutamu/{id}', [ReviewBukuTamuController::class, 'update']);
    Route::delete('review-bukutamu/{id}', [ReviewBukuTamuController::class, 'destroy']);

    Route::prefix('absensi')->group(function () {
        Route::get('today', [AbsensiMobileController::class, 'today']);
        Route::get('monthly-stats', [AbsensiMobileController::class, 'monthlyStats']);
        Route::post('checkin', [AbsensiMobileController::class, 'checkin']);
        Route::post('checkout', [AbsensiMobileController::class, 'checkout']);
    });

    Route::prefix('review-absensi')->group(function () {
        Route::get('summary', [ReviewAbsensiController::class, 'summary']);
        Route::get('detail', [ReviewAbsensiController::class, 'detail']);
        Route::get('karyawan', [ReviewAbsensiController::class, 'karyawan']);
        Route::get('record/{id}', [ReviewAbsensiController::class, 'showRecord']);
        Route::patch('record/{id}/keterangan', [ReviewAbsensiController::class, 'updateKeterangan']);
    });

    // Pengajuan Izin (mobile: submit & riwayat)
    Route::get('pengajuan-izin', [PengajuanIzinController::class, 'index']);
    Route::post('pengajuan-izin', [PengajuanIzinController::class, 'store']);
    Route::get('pengajuan-izin/{id}', [PengajuanIzinController::class, 'show']);

    // Review Pengajuan Izin (web: list & approve/reject)
    Route::prefix('review-pengajuan-izin')->group(function () {
        Route::get('/', [ReviewPengajuanIzinController::class, 'index']);
        Route::get('{id}', [ReviewPengajuanIzinController::class, 'show']);
        Route::patch('{id}/approve', [ReviewPengajuanIzinController::class, 'approve']);
    });

    // Pengajuan Cuti (mobile: submit & riwayat)
    Route::get('pengajuan-cuti/kategori', [PengajuanCutiController::class, 'kategori']);
    Route::get('pengajuan-cuti', [PengajuanCutiController::class, 'index']);
    Route::post('pengajuan-cuti', [PengajuanCutiController::class, 'store']);
    Route::get('pengajuan-cuti/{id}', [PengajuanCutiController::class, 'show']);

    // Review Pengajuan Cuti (web: list & approve/reject)
    Route::prefix('review-pengajuan-cuti')->group(function () {
        Route::get('/', [ReviewPengajuanCutiController::class, 'index']);
        Route::get('{id}', [ReviewPengajuanCutiController::class, 'show']);
        Route::patch('{id}/approve', [ReviewPengajuanCutiController::class, 'approve']);
    });

    // Tukar Jadwal (mobile)
    Route::get('tukar-jadwal', [TukarJadwalController::class, 'index']);
    Route::post('tukar-jadwal', [TukarJadwalController::class, 'store']);

    // Review Tukar Jadwal (web)
    Route::prefix('review-tukar-jadwal')->group(function () {
        Route::get('/', [ReviewTukarJadwalController::class, 'index']);
        Route::patch('{id}/approve', [ReviewTukarJadwalController::class, 'approve']);
    });
});
