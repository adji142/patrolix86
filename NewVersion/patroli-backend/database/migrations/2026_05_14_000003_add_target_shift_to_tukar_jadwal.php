<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tukar_jadwal', function (Blueprint $table) {
            $table->unsignedBigInteger('TargetShiftID')->nullable()->after('idJadwalBaru');
            $table->boolean('IsToOff')->default(false)->after('TargetShiftID');
        });
    }

    public function down(): void
    {
        Schema::table('tukar_jadwal', function (Blueprint $table) {
            $table->dropColumn(['TargetShiftID', 'IsToOff']);
        });
    }
};
